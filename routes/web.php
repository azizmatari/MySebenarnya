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
// User Profile Routes (Public User & Agency)
// ==================

// Public User Profile
Route::get('/user/profile', [UserProfileController::class, 'edit'])->name('user.profile');
Route::post('/user/profile', [UserProfileController::class, 'update'])->name('user.profile.update');

// Agency Profile
Route::get('/agency/profile', [UserProfileController::class, 'edit'])->name('agency.profile');
Route::post('/agency/profile', [UserProfileController::class, 'update'])->name('agency.profile.update');

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

// Show the Register Agency form
Route::get('/register-agency', [RegisterAgencyController::class, 'showRegisterForm'])->name('register.agency.view');
// Handle the Register Agency POST
Route::post('/register-agency', [RegisterAgencyController::class, 'register'])->name('register.agency.submit');

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
// Module 3 - Status Routes
// ==================

// Display inquiry status page
Route::get('/module3/status', [StatusController::class, 'index'])->name('module3.status');

// Essential AJAX routes for inquiry data
Route::get('/module3/status/get-inquiries', [StatusController::class, 'getInquiries'])->name('module3.status.inquiries');
Route::get('/module3/status/statistics', [StatusController::class, 'getStatistics'])->name('module3.status.statistics');

// Debug route for testing
Route::get('/test/status/debug', [StatusController::class, 'debugInquiries'])->name('test.status.debug');


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

// View public inquiries (completed inquiries for public viewing)
Route::get('/inquiry/public', [InquiryController::class, 'publicInquiries'])->name('inquiry.public');

// View evidence file for user's own inquiry
Route::get('/inquiry/{inquiryId}/evidence/{filename}', [InquiryController::class, 'viewEvidenceFile'])->name('inquiry.evidence.view');

// Test route for debugging inquiry list
Route::get('/test/inquiry', [InquiryController::class, 'index'])->name('test.inquiry');

// Test route for modal functionality
Route::get('/test/inquiry/modals', [InquiryController::class, 'testInquiryModals'])->name('test.inquiry.modals');

// View evidence file for user's own inquiry
Route::get('/inquiry/{inquiryId}/evidence/{filename}', [InquiryController::class, 'viewEvidenceFile'])->name('inquiry.evidence.view');

// Test route for debugging inquiry list
Route::get('/test/inquiry', [InquiryController::class, 'index'])->name('test.inquiry');

// Test route for modal functionality
Route::get('/test/inquiry/modals', [InquiryController::class, 'testInquiryModals'])->name('test.inquiry.modals');