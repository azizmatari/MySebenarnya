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
use App\Http\Controllers\module1\UserReportController;
use App\Http\Controllers\SharedControllers\ReassignmentController;


// ==================
// Protected Routes (require login)
// ==================

Route::middleware(['session.auth'])->group(function () {
    // Dashboard Routes
    Route::get('/mcmc/reports', [UserReportController::class, 'dashboardReports'])
        ->name('mcmc.reports');
    Route::get('/dashboard/mcmc', [DashboardController::class, 'mcmcDashboard'])
        ->name('mcmc.dashboard');
    Route::get('/dashboard/public', [DashboardController::class, 'userDashboard'])
        ->name('public.dashboard');
    Route::get('/dashboard/agency', [DashboardController::class, 'agencyDashboard'])
        ->name('agency.dashboard');

    // User Profile Routes
    Route::get('/user/profile', [UserProfileController::class, 'edit'])
        ->name('user.profile');
    Route::post('/user/profile', [UserProfileController::class, 'update'])
        ->name('user.profile.update');
    
    // Agency Profile Routes 
    Route::get('/agency/profile', [UserProfileController::class, 'edit'])
        ->name('agency.profile');
    Route::post('/agency/profile', [UserProfileController::class, 'update'])
        ->name('agency.profile.update');
    
    // Logout Route
    Route::post('/logout', [UserAuthController::class, 'logout'])
        ->name('logout');
});

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


// ==================
// Module 4 - Status Routes
// ==================
// ==================
// Module 4 - 
// ==================

// View inquiry history (user's own inquiries)
Route::get('/inquiry/history', [InquiryController::class, 'index'])->name('inquiry.history');

// View public inquiries (completed inquiries for public viewing)
Route::get('/inquiry/public', [InquiryController::class, 'publicInquiries'])->name('inquiry.public');



// AJAX Route for real-time status updates
Route::get('/inquiry/{inquiryId}/status', [DashboardController::class, 'getInquiryStatus'])->name('inquiry.status');

// Route for getting full status history for user dashboard
Route::get('/inquiry/{inquiryId}/history/full', [DashboardController::class, 'getFullInquiryHistory'])->name('inquiry.history.full');

// Route for downloading supporting documents securely
Route::get('/download/supporting-document/{filename}', [DashboardController::class, 'downloadSupportingDocument'])->name('download.supporting.document');

// Debug route to check file locations
Route::get('/debug/check-files', function () {
    $files = [];

    // Check storage directory
    $storageDir = storage_path('app/public/supporting_documents');
    if (is_dir($storageDir)) {
        $files['storage_files'] = array_diff(scandir($storageDir), ['.', '..']);
    }

    // Check public directory
    $publicDir = public_path('storage/supporting_documents');
    if (is_dir($publicDir)) {
        $files['public_files'] = array_diff(scandir($publicDir), ['.', '..']);
    }

    // Check database records
    $files['database_records'] = \App\Models\SharedModels\InquiryStatusHistory::whereNotNull('supporting_document')
        ->pluck('supporting_document')
        ->toArray();

    return response()->json($files);
});

// Test route to verify file upload
Route::post('/debug/test-upload', function (\Illuminate\Http\Request $request) {
    if ($request->hasFile('test_file')) {
        $file = $request->file('test_file');
        $filename = 'test_' . time() . '_' . $file->getClientOriginalName();

        // Store file
        $path = $file->storeAs('supporting_documents', $filename, 'public');
        $fullPath = storage_path('app/public/' . $path);

        return response()->json([
            'success' => true,
            'filename' => $filename,
            'path' => $path,
            'full_path' => $fullPath,
            'file_exists' => file_exists($fullPath),
            'file_size' => file_exists($fullPath) ? filesize($fullPath) : 0,
            'original_size' => $file->getSize()
        ]);
    }

    return response()->json(['error' => 'No file uploaded']);
});

// ==================
// MCMC Dashboard AJAX Routes for Real-time Monitoring
// ==================

// Get real-time inquiry updates for MCMC monitoring
Route::get('/dashboard/mcmc/activities', [DashboardController::class, 'getInquiryUpdates'])->name('mcmc.activities');

// Get filtered inquiries for MCMC dashboard
Route::get('/dashboard/mcmc/filter', [DashboardController::class, 'getFilteredInquiries'])->name('mcmc.filter');

// Generate agency performance reports
Route::post('/dashboard/mcmc/report', [DashboardController::class, 'generateAgencyReport'])->name('mcmc.report');

// ==================
// Report Download Routes
// ==================

// Download generated PDF report
Route::get('/reports/download/pdf/{filename}', [DashboardController::class, 'downloadPDFReport'])->name('download.pdf.report');

// Download generated Excel report  
Route::get('/reports/download/excel/{filename}', [DashboardController::class, 'downloadExcelReport'])->name('download.excel.report');

// Get real-time status updates for specific inquiry (detailed view)
Route::get('/dashboard/inquiry/{inquiryId}/status', [DashboardController::class, 'getInquiryStatus'])->name('dashboard.inquiry.status');

// ==================
// Agency Dashboard AJAX Routes
// ==================

// Update inquiry status by agency
Route::post('/agency/inquiry/{inquiryId}/status', [DashboardController::class, 'updateInquiryStatus'])->name('agency.inquiry.status.update');

// Get detailed inquiry information for agency
Route::get('/agency/inquiry/{inquiryId}/details', [DashboardController::class, 'getAgencyInquiryDetails'])->name('agency.inquiry.details');

// Get agency dashboard statistics (AJAX)
Route::get('/agency/dashboard/stats', [DashboardController::class, 'getAgencyStats'])->name('agency.dashboard.stats');

// Get real-time status updates for specific inquiry (detailed view)
Route::get('/dashboard/inquiry/{inquiryId}/status', [DashboardController::class, 'getInquiryStatus'])->name('dashboard.inquiry.status');

// ==================
// Agency Reassignment Routes (Protected)
// ==================

Route::middleware(['session.auth'])->group(function () {
    // Agency reassignment requests management
    Route::get('/agency/reassignment-requests', [ReassignmentController::class, 'index'])->name('agency.reassignment.requests');
    Route::get('/agency/reassignment-requests/{id}', [ReassignmentController::class, 'show'])->name('agency.reassignment.show');
    
    // MCMC reassignment management (for MCMC staff)
    Route::get('/mcmc/reassignment-requests', [ReassignmentController::class, 'mcmcIndex'])->name('mcmc.reassignment.index');
    Route::post('/mcmc/reassignment-requests/{id}/approve', [ReassignmentController::class, 'approve'])->name('mcmc.reassignment.approve');
    Route::post('/mcmc/reassignment-requests/{id}/reject', [ReassignmentController::class, 'reject'])->name('mcmc.reassignment.reject');
    
    // Agency Assignment Management Routes
    Route::get('/agency/assignment/management', [ReassignmentController::class, 'index'])->name('agency.assignment.management');
    Route::post('/agency/assignment/{assignmentId}/accept', [ReassignmentController::class, 'accept'])->name('agency.assignment.accept');
    Route::post('/agency/assignment/{assignmentId}/reassignment', [ReassignmentController::class, 'requestReassignment'])->name('agency.assignment.reassignment');
});


// ==================
// Debug Routes (REMOVE IN PRODUCTION)
// ==================
Route::get('/debug/session', function () {
    return [
        'session_data' => session()->all(),
        'user_id' => session('user_id'),
        'user_id' => session('user_id'),
        'role' => session('role')
    ];
});

Route::get('/debug/set-agency-session', function () {
    // Set a test agency session for debugging
    session([
        'agency_id' => 1,
        'user_id' => 1,
        'username' => 'Test Agency',
        'role' => 'agency'
    ]);
    return redirect('/debug/session');
});

Route::get('/debug/agencies', function () {
    return \App\Models\module1\Agency::all(['agencyId', 'agency_name', 'agencyUsername']);
});

Route::get('/debug/create-test-agency', function () {
    $agency = \App\Models\module1\Agency::create([
        'agency_name' => 'Test Agency',
        'agencyUsername' => 'testagency',
        'agencyPassword' => \Illuminate\Support\Facades\Hash::make('password'),
        'mcmcId' => 1,
        'agencyType' => 'Police'
    ]);

    return [
        'message' => 'Test agency created successfully',
        'agency' => $agency,
        'login_instructions' => [
            'username' => 'testagency',
            'password' => 'password',
            'role' => 'agency'
        ]
    ];
});

Route::get('/debug/test-login', function () {
    // Simulate agency login
    $agency = \App\Models\module1\Agency::where('agencyUsername', 'testagency')->first();

    if ($agency && \Illuminate\Support\Facades\Hash::check('password', $agency->agencyPassword)) {
        session([
            'agency_id' => $agency->agencyId,
            'user_id' => $agency->agencyId,
            'username' => $agency->agency_name,
            'role' => 'agency'
        ]);

        return [
            'message' => 'Login simulation successful',
            'session_data' => session()->all(),
            'redirect_url' => route('agency.dashboard')
        ];
    }

    return ['error' => 'Login failed'];
});

Route::get('/debug/test-status-update', function () {
    return view('test-status-update');
});

// Debug route for status update
Route::post('/debug/agency/inquiry/{inquiryId}/status', [DashboardController::class, 'debugUpdateInquiryStatus']);
