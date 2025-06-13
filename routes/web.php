<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SharedControllers\DashboardController;
use App\Http\Controllers\module1\UserProfileController;
use App\Http\Controllers\module1\UserAuthController;
use App\Http\Controllers\module3\StatusController;
use App\Http\Controllers\module2\InquiryController;
use App\Http\Controllers\module1\RegisterAgencyController;

// ==================
// Dashboard Routes
// ==================
Route::get('/dashboard/mcmc', [DashboardController::class, 'mcmcDashboard'])->name('mcmc.dashboard');
Route::get('/dashboard/public', [DashboardController::class, 'userDashboard'])->name('public.dashboard');
Route::get('/dashboard/agency', [DashboardController::class, 'agencyDashboard'])->name('agency.dashboard');

/*====================
    module 1 - routes
=====================*/

// ==================
// User Profile Route
// ==================
Route::get('/user/profile', [UserProfileController::class, 'view'])->name('user.profile');

// ==================
// Authentication Routes
// ==================

// Registration (Public User)
Route::get('/register', function () {
    return view('module1.UserRegisterView');
})->name('register.view');
Route::post('/register', [UserAuthController::class, 'registerPublic'])->name('register.submit');

// Show the Register Agency form
Route::get('/register-agency', [App\Http\Controllers\module1\RegisterAgencyController::class, 'showRegisterForm'])->name('register.agency.view');
// Handle the Register Agency POST
Route::post('/register-agency', [App\Http\Controllers\module1\RegisterAgencyController::class, 'register'])->name('register.agency.submit');

// Login (All Users)
Route::get('/login', function () {
    return view('module1.UserLoginView');
})->name('login');
Route::post('/login', [UserAuthController::class, 'login'])->name('login.submit');

// Logout (All Users)
Route::post('/logout', [UserAuthController::class, 'logout'])->name('logout');

// ==================
// Forgot Password View
// ==================
Route::get('/forgot-password', function () {
    return view('module1.ForgotPasswordView');
})->name('forgot.password');

// ==================
// Default Welcome Page
// ==================
Route::get('/', function () {
    return redirect()->route('login');
});



//END Of module 1 routes
//END Of module 1 routes
//END Of module 1 routes
//END Of module 1 routes
//END Of module 1 routes
//END Of module 1 routes











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
