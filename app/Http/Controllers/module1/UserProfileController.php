<?php

namespace App\Http\Controllers\module1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\module1\PublicUser;
use App\Models\module1\Agency;

class UserProfileController extends Controller
{
    // Show the edit profile form for both user types
    public function edit()
    {
        $role = session('role');
        $userId = session('user_id');

        if ($role === 'public') {
            $user = PublicUser::findOrFail($userId);
            return view('module1.UserProfileView', compact('user'));
        } else        if ($role === 'agency') {
            $agency = Agency::findOrFail($userId);
            $firstLogin = session('first_login', $agency->first_login);
            Log::info('Agency profile accessed. First login: ' . ($firstLogin ? 'YES' : 'NO'));
            return view('module1.AgencyProfileView', compact('agency', 'firstLogin'));
        } else {
            abort(403, 'Unauthorized');
        }
    }

    // Handle profile update for both user types
    public function update(Request $request)
    {
        $role = session('role');
        $userId = session('user_id');

        if ($role === 'public') {
            $user = PublicUser::findOrFail($userId);

            $request->validate([
                'userName' => 'required|string|max:255',
                'userContact_number' => 'nullable|string|max:30',
                'profile_picture' => 'nullable|image|max:2048',
                'current_password' => 'nullable|string',
                'new_password' => 'nullable|string|min:6|confirmed',
            ]);

            $user->userName = $request->userName;
            $user->userContact_number = $request->userContact_number;

            // Handle profile picture upload
            if ($request->hasFile('profile_picture')) {
                if ($user->profile_picture) {
                    Storage::delete('public/' . $user->profile_picture);
                }
                $path = $request->file('profile_picture')->store('profile_pictures', 'public');
                $user->profile_picture = $path;
            }

            // Handle password change
            if ($request->filled('current_password') && $request->filled('new_password')) {
                if (Hash::check($request->current_password, $user->userPassword)) {
                    $user->userPassword = Hash::make($request->new_password);
                } else {
                    return back()->withErrors(['current_password' => 'Current password is incorrect.']);
                }
            }

            $user->save();
            // Update session with new name and profile picture
            session(['username' => $user->userName]);
            session(['profile_picture' => $user->profile_picture]);
            return back()->with('success', 'Profile updated successfully.');
        } elseif ($role === 'agency') {
            $agency = Agency::findOrFail($userId);

            $request->validate([
                'agency_name' => 'required|string|max:255',
                'agencyContact' => 'nullable|string|max:30',
                'profile_picture' => 'nullable|image|max:2048',
                'current_password' => 'nullable|string',
                'new_password' => 'nullable|string|min:6|confirmed',
            ]);

            $agency->agency_name = $request->agency_name;
            $agency->agencyContact = $request->agencyContact;

            // Handle profile picture upload
            if ($request->hasFile('profile_picture')) {
                if ($agency->profile_picture) {
                    Storage::delete('public/' . $agency->profile_picture);
                }
                $path = $request->file('profile_picture')->store('profile_pictures', 'public');
                $agency->profile_picture = $path;
            }            // Handle password change
            if ($request->filled('current_password') && $request->filled('new_password')) {
                Log::info('Password change requested for agency ID: ' . $agency->agencyId);
                Log::info('First login status before: ' . ($agency->first_login ? 'YES' : 'NO'));

                // Verify current password
                if (!Hash::check($request->current_password, $agency->agencyPassword)) {
                    Log::info('Password change failed: current password incorrect');
                    return back()->withErrors(['current_password' => 'Current password is incorrect.']);
                }
                
                // Set new password
                $agency->agencyPassword = Hash::make($request->new_password);
                
                // Check if this is first login
                $wasFirstLogin = $agency->first_login;
                
                // After password change on first login, set flag to false
                if ($wasFirstLogin) {
                    Log::info('Setting first_login flag to false after password change');
                    $agency->first_login = false;
                }
            }

            // Save changes
            $agency->save();
            Log::info('Agency profile updated. First login status: ' . ($agency->first_login ? 'YES' : 'NO'));

            // Update session data
            session(['username' => $agency->agency_name]);
            session(['profile_picture' => $agency->profile_picture]);

            // If this was the first login and password was changed, redirect to dashboard
            if (isset($wasFirstLogin) && $wasFirstLogin) {
                Log::info('First login completed - redirecting to dashboard');
                return redirect()->route('agency.dashboard')
                    ->with('success', 'Password changed successfully. Welcome to your dashboard!');
            }

            // For regular updates, return to the profile page
            Log::info('Regular profile update - staying on profile page');
            return back()->with('success', 'Profile updated successfully.');
        } else {
            abort(403, 'Unauthorized');
        }
    }
}
