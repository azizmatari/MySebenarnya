<?php

namespace App\Http\Controllers\module1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\module1\Agency;

class RegisterAgencyController extends Controller
{
    // Show the registration form
    public function showRegisterForm()
    {
        return view('module1.RegisterAgencyView');
    }

    // Handle the registration POST
    public function register(Request $request)
    {
        $request->validate([
            'agency_name' => 'required|string|max:50',
            'agencyUsername' => 'required|string|max:20|unique:agency,agencyUsername',
            'agencyPassword' => 'required|string|min:6|confirmed',
            // 'mcmcId' is not needed in validation anymore
        ]);

        Agency::create([
            'agency_name' => $request->agency_name,
            'agencyUsername' => $request->agencyUsername,
            'agencyPassword' => Hash::make($request->agencyPassword),
            'mcmcId' => session('user_id'), // Set from session
        ]);

        return redirect()->route('register.agency.view')->with('success', 'Agency registered successfully!');
    }
}