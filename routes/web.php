<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SharedControllers\DashboardController;
use App\Http\Controllers\module1\UserProfileController;
use App\Http\Controllers\module1\UserAuthController;
use App\Http\Controllers\module3\StatusController;
use App\Http\Controllers\module2\InquiryController;

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
            return redirect()->route('inquiry.public');
        }
        return redirect()->route('user.dashboard');
    }
    return redirect()->route('login');
    */

    // TEMPORARY: Direct redirect to inquiry status page for testing
    return redirect()->route('inquiry.public');
});

// ==================
// Login & Register Routes (TEMPORARILY DISABLED)
// ==================

// TEMPORARY: Login page shows message instead of actual login
Route::get('/login', function () {
    return response()->view('temp_message', [
        'title' => 'Login Temporarily Disabled',
        'message' => 'Login functionality is temporarily disabled for testing. You will be redirected to the inquiry status page.',
        'redirect_url' => route('inquiry.public'),
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
// Module 2 & 3 - Inquiry Routes
// ==================

// Display inquiry status page - using StatusController for compatibility
Route::get('/module3/status', [StatusController::class, 'index'])->name('module3.status');

// Essential AJAX routes for inquiry data - using StatusController for compatibility
Route::get('/module3/status/get-inquiries', [StatusController::class, 'getInquiries'])->name('module3.status.inquiries');
Route::get('/module3/status/statistics', [StatusController::class, 'getStatistics'])->name('module3.status.statistics');

// ==================
// Module 2 - Inquiry Routes
// ==================

// Create new inquiry form
Route::get('/inquiry/create', [InquiryController::class, 'create'])->name('inquiry.create');

// Submit new inquiry
Route::post('/inquiry/store', [InquiryController::class, 'store'])->name('inquiry.store');

// Inquiry success page
Route::get('/inquiry/success', [InquiryController::class, 'success'])->name('inquiry.success');

// View inquiry history (user's own inquiries)
Route::get('/inquiry/history', [InquiryController::class, 'index'])->name('inquiry.history');

// View public inquiries
Route::get('/inquiry/public', [InquiryController::class, 'publicInquiries'])->name('inquiry.public');
