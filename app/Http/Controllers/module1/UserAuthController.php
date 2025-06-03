<?php

namespace App\Http\Controllers\module1;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserAuthController extends Controller
{
    public function loginPublic(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    $user = DB::table('public_users')->where('userEmail', $request->email)->first();

    if ($user && Hash::check($request->password, $user->userPassword)) {
        session([
            'user_id' => $user->userId,
            'username' => $user->userName,
            'role' => 'public'
        ]);
        return redirect()->route('user.dashboard');
    }

    return back()->with('error', 'Invalid credentials');
}

    public function logout(Request $request)
{
    $request->session()->flush();
    return redirect('/login');
}
public function registerPublic(Request $request)
{
    // 1. Validate form input
    $request->validate([
        'userName' => 'required|string|max:255',
        'userEmail' => 'required|email|unique:public_users,userEmail',
        'userUsername' => 'required|string|max:255|unique:public_users,userUsername',
        'userPassword' => 'required|string|min:6|confirmed',
    ]);

    // 2. Insert user into database
    DB::table('public_users')->insert([
        'userName' => $request->userName,
        'userEmail' => $request->userEmail,
        'userUsername' => $request->userUsername,
        'userPassword' => Hash::make($request->userPassword),
        'userContact_number' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // 3. Auto-login after register
    $user = DB::table('public_users')->where('userEmail', $request->userEmail)->first();

    session([
        'user_id' => $user->userId,
        'username' => $user->userName,
        'role' => 'public'
    ]);

    // 4. Redirect to dashboard
    return redirect()->route('user.dashboard');
}




}


