<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SharedControllers\DashboardController;
use App\Http\Controllers\module1\UserProfileController;
use App\Http\Controllers\module1\UserAuthController;

// ==================
// Dashboard Routes
// ==================
Route::get('/dashboard/mcmc', [DashboardController::class, 'mcmcDashboard'])->name('mcmc.dashboard');
Route::get('/dashboard/user', [DashboardController::class, 'userDashboard'])->name('user.dashboard');
Route::get('/dashboard/agency', [DashboardController::class, 'agencyDashboard'])->name('agency.dashboard');

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
    if (session()->has('user_id')) {
        return redirect()->route('user.dashboard');
    }
    return redirect()->route('login');
});

// ==================
// Login & Register Routes
// ==================
Route::get('/login', function () {
    return view('module1.UserLoginView');
})->name('login');

Route::post('/login', [UserAuthController::class, 'loginPublic'])->name('login.submit');

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