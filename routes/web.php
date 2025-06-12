<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SharedControllers\DashboardController;
use App\Http\Controllers\module1\UserProfileController;
use App\Http\Controllers\module1\UserAuthController;
use App\Http\Controllers\module3\StatusController;

// ==================
// Dashboard Routes
// ==================
Route::get('/dashboard/mcmc', [DashboardController::class, 'mcmcDashboard'])->name('mcmc.dashboard');
Route::get('/dashboard/user', [DashboardController::class, 'userDashboard'])->name('user.dashboard');
Route::get('/dashboard/agency', [DashboardController::class, 'agencyDashboard'])->name('agency.dashboard');



// module 1

// ==================
// User Profile Route
// ==================
Route::get('/user/profile', [UserProfileController::class, 'view'])->name('user.profile');

// ==================
// Logout
// ==================
Route::post('/logout', [UserAuthController::class, 'logout'])->name('logout');

// ==================
// Default Welcome Page
// ==================
Route::get('/', function () {
    // TEMPORARY: Bypass login for testing - UNCOMMENT BELOW WHEN READY
    /*
    if (session()->has('user_id')) {
        // Check user role and redirect accordingly
        if (session('role') === 'public') {
            return redirect()->route('module3.status');
        }
        return redirect()->route('user.dashboard');
    }
    return redirect()->route('login');
    */

    // TEMPORARY: Direct redirect to inquiry status page for testing
    return redirect()->route('module3.status');
});

// ==================
// Login & Register Routes (TEMPORARILY DISABLED)
// ==================

// TEMPORARY: Login page shows message instead of actual login
Route::get('/login', function () {
    return response()->view('temp_message', [
        'title' => 'Login Temporarily Disabled',
        'message' => 'Login functionality is temporarily disabled for testing. You will be redirected to the inquiry status page.',
        'redirect_url' => route('module3.status'),
        'redirect_text' => 'Go to Inquiry Status Page'
    ]);
})->name('login');

// COMMENTED OUT: Actual login functionality
/*
Route::get('/login', function () {
    return view('module1.UserLoginView');
})->name('login');

Route::post('/login', [UserAuthController::class, 'loginPublic'])->name('login.submit');
*/

Route::get('/register', function () {
    return view('module1.UserRegisterView');
})->name('register.view');

Route::post('/register', [UserAuthController::class, 'registerPublic'])->name('register.submit');

// ==================
// Forgot Password View
// =================
Route::get('/forgot-password', function () {
    return view('module1.ForgotPasswordView');
})->name('forgot.password');











// ==================
// Module 3 - Status Routes
// ==================

// Display inquiry status page
Route::get('/module3/status', [StatusController::class, 'index'])->name('module3.status');

// Essential AJAX routes for inquiry data
Route::get('/module3/status/get-inquiries', [StatusController::class, 'getInquiries'])->name('module3.status.inquiries');
Route::get('/module3/status/statistics', [StatusController::class, 'getStatistics'])->name('module3.status.statistics');
