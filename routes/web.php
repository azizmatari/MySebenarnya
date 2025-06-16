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

// Display inquiry status page
Route::get('/module3/status', [StatusController::class, 'index'])->name('module3.status');

// AJAX endpoint to get inquiries data
Route::get('/module3/status/get-inquiries', [StatusController::class, 'getInquiries'])->name('module3.status.get-inquiries');

// MCMC assignment routes (moved to module3)
Route::get('/module3/mcmc/assign/{inquiryId}', [assignController::class, 'showAssignmentForm'])->name('module3.mcmc.assign');

// Process MCMC assignment
Route::post('/module3/mcmc/assign', [assignController::class, 'processAssignment'])->name('module3.mcmc.process.assign');

// AJAX endpoint to get agencies by type
Route::post('/module3/mcmc/agencies-by-type', [assignController::class, 'getAgenciesByType'])->name('module3.mcmc.agencies.by.type');



// ==================
// Module 2 - MCMC Routes (for reviewing and validating inquiries)
// ==================

// MCMC view new inquiries for review
Route::get('/mcmc/inquiries', [MCMCController::class, 'viewNewInquiries'])->name('mcmc.inquiries');

// MCMC validate inquiry (assign to agency)
Route::post('/mcmc/inquiries/validate', [MCMCController::class, 'validateInquiry'])->name('mcmc.validate.inquiry');

// MCMC reject inquiry
Route::post('/mcmc/inquiries/reject', [MCMCController::class, 'rejectInquiry'])->name('mcmc.reject.inquiry');

// MCMC view inquiry details
Route::get('/mcmc/inquiries/{inquiryId}', [MCMCController::class, 'viewInquiryDetails'])->name('mcmc.inquiry.details');

// Create test data for MCMC
Route::get('/mcmc/create-test-data', [MCMCController::class, 'createMCMCTestData'])->name('mcmc.create.test.data');


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


// ==================
// Module 3 - Report Routes (MCMC Reporting System)
// ==================

// Main report dashboard
Route::get('/reports', [App\Http\Controllers\module3\ReportController::class, 'index'])->name('reports.index');

// AJAX endpoints for reports
Route::get('/reports/agency-assignments', [App\Http\Controllers\module3\ReportController::class, 'getAgencyAssignmentReport'])->name('reports.agency.assignments');
Route::get('/reports/filtered-inquiries', [App\Http\Controllers\module3\ReportController::class, 'getFilteredInquiries'])->name('reports.filtered.inquiries');
Route::get('/reports/inquiry-trends', [App\Http\Controllers\module3\ReportController::class, 'getInquiryTrends'])->name('reports.inquiry.trends');
Route::get('/reports/agency-performance', [App\Http\Controllers\module3\ReportController::class, 'getAgencyPerformance'])->name('reports.agency.performance');

// Export routes
Route::post('/reports/export-pdf', [App\Http\Controllers\module3\ReportController::class, 'exportToPDF'])->name('reports.export.pdf');
Route::post('/reports/export-excel', [App\Http\Controllers\module3\ReportController::class, 'exportToExcel'])->name('reports.export.excel');

// Temporary route to check database schema
Route::get('/check-schema', function () {
    $schema = DB::select('SHOW COLUMNS FROM agency');
    return response()->json($schema);
});
