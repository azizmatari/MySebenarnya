<?php

namespace App\Http\Controllers\module1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\module1\PublicUser;
use App\Models\module1\MCMC_Staff;
use App\Models\module1\Agency;

class UserAuthController extends Controller
{
    // 1. Public User Registration
    public function registerPublic(Request $request)
    {
        $request->validate([
            'userName' => 'required|string|max:255',
            'userEmail' => 'required|email|unique:publicuser,userEmail',
            'userPassword' => 'required|string|min:6|confirmed',
        ]);

        $user = PublicUser::create([
            'userName' => $request->userName,
            'userEmail' => $request->userEmail,
            'userPassword' => Hash::make($request->userPassword),
            'userContact_number' => null,
        ]);

        session([
            'user_id' => $user->userId,
            'username' => $user->userName,
            'role' => 'public'
        ]);

        return redirect()->route('public.dashboard');
    }

    // 2. Login for all users (public, mcmc staff, agency)
    public function login(Request $request)
    {
        // Check the selected role and attempt login accordingly
        if ($request->role === 'public') {
            // For Public User: find by email
            $request->validate([
                'userEmail' => 'required|email',
                'password' => 'required|string'
            ]);
            $user = PublicUser::where('userEmail', $request->userEmail)->first();
            if ($user && Hash::check($request->password, $user->userPassword)) {
                session([
                    'user_id' => $user->userId,
                    'username' => $user->userName,
                    'role' => 'public'
                ]);
                return redirect()->route('public.dashboard');
            }
        } else if ($request->role === 'mcmc') {
            // For MCMC Staff: find by username
            $request->validate([
                'userUsername' => 'required|string',
                'password' => 'required|string'
            ]);
            $user = MCMC_Staff::where('mcmcUsername', $request->userUsername)->first();

            // Debug logging for MCMC staff login
            \Log::info('--- MCMC Staff Login Attempt ---');
            \Log::info('Username entered: ' . $request->userUsername);
            \Log::info('Password entered: ' . $request->password);
            \Log::info('User found: ' . ($user ? 'YES' : 'NO'));
            \Log::info('Password in DB: ' . ($user ? $user->mcmcPassword : 'NO USER'));

            if ($user && Hash::check($request->password, $user->mcmcPassword)) {
                session([
                    'user_id' => $user->mcmcId,
                    'username' => $user->mcmcName,
                    'role' => 'mcmc'
                ]);
                \Log::info('MCMC login success!');
                return redirect()->route('mcmc.dashboard');
            } else {
                \Log::info('MCMC login failed: Invalid credentials or password mismatch.');
            }
        } else if ($request->role === 'agency') {
            // For Agency: find by username
            $request->validate([
                'userUsername' => 'required|string',
                'password' => 'required|string'
            ]);
            $user = Agency::where('agencyUsername', $request->userUsername)->first();
            if ($user && Hash::check($request->password, $user->agencyPassword)) {
                session([
                    'user_id' => $user->agencyId,
                    'username' => $user->agency_name,
                    'role' => 'agency'
                ]);
                return redirect()->route('agency.dashboard');
            }
        }

        return back()->with('error', 'Invalid credentials');
    }

    // 3. Logout for all users
    public function logout(Request $request)
    {
        $request->session()->flush();
        return redirect()->route('login');
    }
}