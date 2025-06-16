<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SharedControllers\DashboardController;
use App\Http\Controllers\module1\UserProfileController;
use App\Http\Controllers\module1\UserAuthController;
use App\Http\Controllers\module3\StatusController;
use App\Http\Controllers\module3\assignController;
use App\Http\Controllers\module2\InquiryController;
use App\Http\Controllers\module2\MCMCController;
use App\Http\Controllers\module1\RegisterAgencyController;
use App\Http\Controllers\module1\UserReportController;;

// ==================
// Dashboard Routes
// ==================
Route::get('/mcmc/reports', [UserReportController::class, 'dashboardReports'])
    ->name('mcmc.reports');
Route::get('/dashboard/mcmc', [DashboardController::class, 'mcmcDashboard'])->name('mcmc.dashboard');
Route::get('/dashboard/public', [DashboardController::class, 'userDashboard'])->name('public.dashboard');
Route::get('/dashboard/agency', [DashboardController::class, 'agencyDashboard'])->name('agency.dashboard');

/*====================
    module 1 - routes
=====================*/

// ==================
// User Profile Routes (Public User & Agency)
// ==================
Route::get('/user/profile', [UserProfileController::class, 'edit'])->name('user.profile');
Route::post('/user/profile', [UserProfileController::class, 'update'])->name('user.profile.update');
Route::get('/agency/profile', [UserProfileController::class, 'edit'])->name('agency.profile');
Route::post('/agency/profile', [UserProfileController::class, 'update'])->name('agency.profile.update');

// ==================
// Logout
// ==================
Route::post('/logout', [UserAuthController::class, 'logout'])->name('logout');

// ==================
// Registration & Login
// ==================
Route::get('/register', function () {
    return view('module1.UserRegisterView');
})->name('register.view');
Route::post('/register', [UserAuthController::class, 'registerPublic'])->name('register.submit');
Route::get('/register-agency', [RegisterAgencyController::class, 'showRegisterForm'])->name('register.agency.view');
Route::post('/register-agency', [RegisterAgencyController::class, 'register'])->name('register.agency.submit');
// Commented out - routes for dynamic agency types (for future use)
// Route::post('/register-agency/add-type', [RegisterAgencyController::class, 'addAgencyType'])->name('add.agency.type');
// Route::post('/register-agency/reset-types', [RegisterAgencyController::class, 'resetAgencyTypes'])->name('reset.agency.types');

// Login (All Users)
Route::get('/login', function () {
    return view('module1.UserLoginView');
})->name('login');
Route::post('/login', [UserAuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [UserAuthController::class, 'logout'])->name('logout');

Route::get('/forgot-password', function () {
    return view('module1.ForgotPasswordView');
})->name('forgot.password');

// ==================
// Default Welcome Page
// ==================
Route::get('/', function () {
    return redirect()->route('login');
});

// =====================================
// Module 1 Reports (User List + Charts)
// =====================================
// Module 1 Reports (User List + Charts)
Route::get('/user-reports', [UserReportController::class, 'index'])->name('user.reports.index');
Route::get('/user-reports/charts', [UserReportController::class, 'charts'])->name('user.reports.charts');
Route::get('/user-reports/export-excel/{mode}', [UserReportController::class, 'exportExcel'])->name('user.reports.export.excel');
Route::post('/user-reports/agency/{id}/update-type', [UserReportController::class, 'updateAgencyType'])->name('user.reports.agency.update.type');
Route::post('/user-reports/agency/{id}/delete', [UserReportController::class, 'deleteAgency'])->name('user.reports.agency.delete');



//END Of module 1 routes
//END Of module 1 routes
//END Of module 1 routes
//END Of module 1 routes
//END Of module 1 routes
//END Of module 1 routes

// ==================
// Module 2 - Routes
// ==================


// ==================
// Module 3 - Status Routes
// ==================
