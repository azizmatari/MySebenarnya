@include('layouts.sidebarMcmc')

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MCMC Dashboard - Real-time Monitoring</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- PDF Generation Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        .dashboard-content {
            margin-top: 80px;
            margin-left: 260px;
            padding: 20px;
            min-height: calc(100vh - 80px);
            background-color: #f8f9fa;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .stats-card.success {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .stats-card.warning {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }
        
        .stats-card.danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #feca57 100%);
        }
        
        .stats-card.info {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            color: #333;
        }
        
        .card-modern {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        
        .card-header-modern {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
            border: none;
        }
        
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 15px;
            font-weight: 600;
            font-size: 0.75em;
            white-space: nowrap;
            display: inline-block;
            margin: 2px;
            min-width: fit-content;
            text-align: center;
            line-height: 1.2;
        }
        
        /* Prevent status badge overlap in table cells */
        #inquiriesTable td {
            vertical-align: middle;
            padding: 12px 8px;
        }
        
        /* Specific styling for status column */
        #inquiriesTable td:nth-child(3) {
            min-width: 120px;
            padding: 10px;
        }
        
        /* Ensure proper spacing in activity items */
        .activity-item .status-badge {
            margin-left: 5px;
            vertical-align: middle;
        }
        
        .real-time-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            background-color: #28a745;
            border-radius: 50%;
            animation: pulse 2s infinite;
            margin-right: 5px;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            margin: 20px 0;
        }
        
        .activity-item {
            padding: 15px;
            border-left: 4px solid #007bff;
            margin-bottom: 10px;
            background: white;
            border-radius: 0 10px 10px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .agency-performance-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .agency-performance-card:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        
        .btn-modern {
            border-radius: 25px;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .progress-modern {
            height: 8px;
            border-radius: 10px;
            background-color: #e9ecef;
        }
        
        /* Notification Bar Styles */
        .notification-item {
            transition: all 0.3s ease;
        }
        
        .notification-item:hover {
            background-color: #f8f9fa !important;
        }
        
        .notification-item .badge {
            animation: pulse-badge 2s infinite;
        }
        
        @keyframes pulse-badge {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border: none;
        }
        
        .completion-badge {
            animation: bounce 0.5s ease-out;
        }
        
        @keyframes bounce {
            0% { transform: scale(0.8); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .progress-modern .progress-bar {
            border-radius: 10px;
        }
    </style>
</head>
<body>

<div class="dashboard-content">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <span class="real-time-indicator"></span>
                MCMC Real-time Monitoring Dashboard
            </h1>
            <p class="text-muted mb-0">Monitor agency investigation progress and generate performance reports</p>
        </div>
        <div>
            <button class="btn btn-primary btn-modern me-2" id="refreshDashboard">
                <i class="fas fa-sync-alt me-2"></i>Refresh
            </button>
            <button class="btn btn-success btn-modern" data-bs-toggle="modal" data-bs-target="#reportModal">
                <i class="fas fa-chart-line me-2"></i>Generate Report
            </button>
        </div>
    </div>

    <!-- MCMC Completion Notifications Bar -->
    @if($completedNotifications && $completedNotifications->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-left: 5px solid #28a745 !important;">
                <div class="d-flex align-items-center">
                    <div class="position-relative me-3">
                        <i class="fas fa-check-circle text-success" style="font-size: 1.3rem;"></i>
                        @if($newCompletionCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark" style="font-size: 0.6rem;">
                            {{ $newCompletionCount > 9 ? '9+' : $newCompletionCount }}
                        </span>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="alert-heading mb-2 d-flex align-items-center">
                            <i class="fas fa-clipboard-check me-2"></i>
                            Recent Inquiry Completions by Agencies
                            @if($newCompletionCount > 0)
                            <span class="badge bg-warning text-dark ms-2">{{ $newCompletionCount }} New</span>
                            @endif
                            <small class="text-muted ms-auto">
                                {{ $completionStats['agencies_active'] }} agencies • 
                                <span class="text-success">{{ $completionStats['verified_count'] }} verified</span> •
                                <span class="text-danger">{{ $completionStats['fake_count'] }} fake</span> •
                                <span class="text-secondary">{{ $completionStats['rejected_count'] }} rejected</span>
                            </small>
                        </h6>
                        <div class="notification-content">
                            @foreach($completedNotifications->take(4) as $completion)
                            <div class="notification-item mb-2 pb-2 {{ !$loop->last ? 'border-bottom' : '' }} {{ $completion->is_new ?? false ? 'bg-light rounded p-2' : '' }}" data-status-id="{{ $completion->status_id }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        @if($completion->is_new ?? false)
                                        <span class="badge bg-warning text-dark me-2" style="font-size: 0.7rem;">NEW</span>
                                        @endif
                                        <strong class="text-primary">Case #{{ $completion->inquiry->inquiryId }}</strong>
                                        <span class="mx-2">•</span>
                                        <span class="badge bg-{{ 
                                            $completion->status === 'True' ? 'success' : 
                                            ($completion->status === 'Fake' ? 'danger' : 'secondary') 
                                        }}">
                                            {{ $completion->status === 'True' ? 'VERIFIED TRUE' : 
                                               ($completion->status === 'Fake' ? 'IDENTIFIED FAKE' : 'REJECTED') }}
                                        </span>
                                        @if($completion->agency)
                                        <span class="mx-2">•</span>
                                        <span class="text-success fw-bold">{{ $completion->agency->agencyName }}</span>
                                        @endif
                                        <br>
                                        <small class="text-muted">
                                            {{ Str::limit($completion->inquiry->title ?? 'Untitled', 70) }}
                                        </small>
                                        @if($completion->comments)
                                        <br>
                                        <small class="text-info">
                                            <i class="fas fa-file-alt me-1"></i>{{ Str::limit($completion->comments, 90) }}
                                        </small>
                                        @endif
                                    </div>
                                    <div class="d-flex gap-1 ms-2">
                                        <button class="btn btn-sm btn-outline-primary" 
                                                onclick="viewCompletedInquiry('{{ $completion->inquiry->inquiryId }}')"
                                                title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success" 
                                                onclick="markAsReviewed('{{ $completion->status_id }}')"
                                                title="Mark as Reviewed">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            @if($completedNotifications->count() > 4)
                            <div class="text-center mt-2">
                                <button class="btn btn-sm btn-link text-decoration-none" onclick="showAllCompletions()">
                                    <i class="fas fa-chevron-down me-1"></i>
                                    View {{ $completedNotifications->count() - 4 }} more completions
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Statistics Cards Row -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card">
                <div class="d-flex justify-content-between">
                    <div>
                        <h3 class="mb-1">{{ $totalInquiries }}</h3>
                        <p class="mb-0">Total Inquiries</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clipboard-list fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card warning">
                <div class="d-flex justify-content-between">
                    <div>
                        <h3 class="mb-1">{{ $totalPending }}</h3>
                        <p class="mb-0">Under Investigation</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-hourglass-half fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card success">
                <div class="d-flex justify-content-between">
                    <div>
                        <h3 class="mb-1">{{ $totalResolved }}</h3>
                        <p class="mb-0">Resolved Cases</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-check-circle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card info">
                <div class="d-flex justify-content-between">
                    <div>
                        <h3 class="mb-1">{{ $totalAgencies }}</h3>
                        <p class="mb-0">Active Agencies</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-building fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="filter-section">
        <h5 class="mb-3"><i class="fas fa-filter me-2"></i>Dashboard Filters</h5>
        <div class="row">
            <div class="col-md-3">
                <label class="form-label">Filter by Status</label>
                <select class="form-select" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="Under Investigation">Under Investigation</option>
                    <option value="True">True</option>
                    <option value="Fake">Fake</option>
                    <option value="Rejected">Rejected</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Filter by Agency</label>
                <select class="form-select" id="agencyFilter">
                    <option value="">All Agencies</option>
                    @foreach($agencyStats as $agency)
                        <option value="{{ $agency->agencyId }}">{{ $agency->agency_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Date From</label>
                <input type="date" class="form-control" id="dateFromFilter">
            </div>
            <div class="col-md-2">
                <label class="form-label">Date To</label>
                <input type="date" class="form-control" id="dateToFilter">
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button class="btn btn-primary btn-modern me-2" id="applyFilters">
                        <i class="fas fa-search me-2"></i>Apply
                    </button>
                </div>
            </div>
            <div class="col-md-1">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button class="btn btn-secondary btn-modern" id="clearFilters">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Analytics Row -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card card-modern">
                <div class="card-header card-header-modern">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Status Distribution</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-modern">
                <div class="card-header card-header-modern">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Inquiry Trends (7 Days)</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="trendsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Agency Performance and Recent Activities Row -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card card-modern">
                <div class="card-header card-header-modern d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-building me-2"></i>Agency Performance Overview</h5>
                    <button class="btn btn-light btn-sm" id="refreshAgencyStats">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="row" id="agencyPerformanceContainer">
                        @foreach($agencyPerformance as $agency)
                        <div class="col-md-6 mb-3">
                            <div class="card agency-performance-card h-100" data-agency-id="{{ $agency['agency_name'] }}">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $agency['agency_name'] }}</h6>
                                    <small class="text-muted">{{ $agency['agency_type'] }}</small>
                                    
                                    <div class="mt-3">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Resolution Rate</span>
                                            <span class="fw-bold">{{ $agency['resolution_rate'] }}%</span>
                                        </div>
                                        <div class="progress progress-modern mb-3">
                                            <div class="progress-bar bg-success" style="width: {{ $agency['resolution_rate'] }}%"></div>
                                        </div>
                                        
                                        <div class="row text-center">
                                            <div class="col-4">
                                                <div class="fw-bold text-primary">{{ $agency['total_assigned'] }}</div>
                                                <small class="text-muted">Assigned</small>
                                            </div>
                                            <div class="col-4">
                                                <div class="fw-bold text-success">{{ $agency['total_resolved'] }}</div>
                                                <small class="text-muted">Resolved</small>
                                            </div>
                                            <div class="col-4">
                                                <div class="fw-bold text-warning">{{ $agency['pending_count'] }}</div>
                                                <small class="text-muted">Pending</small>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-3">
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                Avg Resolution: {{ $agency['avg_resolution_days'] }} days
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card card-modern">
                <div class="card-header card-header-modern d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Activities</h5>
                    <span class="badge bg-light text-dark">Live</span>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <div id="recentActivitiesContainer">
                        @foreach($recentActivities as $activity)
                        <div class="activity-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $activity->inquiry->title ?? 'Unknown Inquiry' }}</h6>
                                    <p class="mb-1 text-muted small">
                                        Status updated to: 
                                        <span class="status-badge bg-{{ $activity->status === 'Under Investigation' ? 'warning' : ($activity->status === 'True' ? 'success' : ($activity->status === 'Fake' ? 'danger' : 'secondary')) }}">
                                            {{ $activity->status }}
                                        </span>
                                    </p>
                                    <small class="text-muted">
                                        <i class="fas fa-building me-1"></i>
                                        {{ $activity->agency->agency_name ?? 'Unknown Agency' }}
                                    </small>
                                    @if($activity->status_comment)
                                    <p class="mb-0 mt-2 small text-dark">
                                        <i class="fas fa-comment me-1"></i>
                                        {{ $activity->status_comment }}
                                    </p>
                                    @endif
                                </div>
                                <small class="text-muted">
                                    {{ $activity->updated_at ? \Carbon\Carbon::parse($activity->updated_at)->diffForHumans() : 'Recently' }}
                                </small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Inquiries Table -->
    <div class="card card-modern">
        <div class="card-header card-header-modern d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-table me-2"></i>All Inquiries - Real-time View</h5>
            <div>
                <button class="btn btn-light btn-sm me-2" id="refreshInquiriesTable">
                    <i class="fas fa-sync-alt"></i>
                </button>
                <button class="btn btn-light btn-sm" id="exportInquiries">
                    <i class="fas fa-download me-2"></i>Export
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="inquiriesTable">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Agency</th>
                            <th>Submitted</th>
                            <th>Assigned</th>
                            <th>Progress</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="inquiriesTableBody">
                        @foreach($allInquiries as $inquiry)
                        <tr data-inquiry-id="{{ $inquiry->inquiryId }}">
                            <td><strong>#{{ $inquiry->inquiryId }}</strong></td>
                            <td>
                                <div class="text-truncate" style="max-width: 200px;" title="{{ $inquiry->title }}">
                                    {{ $inquiry->title }}
                                </div>
                            </td>
                            <td>
                                @php
                                    $status = $inquiry->getCurrentStatus();
                                    $statusClass = match($status) {
                                        'Under Investigation' => 'warning',
                                        'True' => 'success',
                                        'Fake' => 'danger',
                                        'Rejected' => 'secondary',
                                        'Reassigned' => 'info',
                                        default => 'info'
                                    };
                                @endphp
                                <span class="status-badge bg-{{ $statusClass }}">{{ $status }}</span>
                            </td>
                            <td>
                                @if($inquiry->currentAssignment && $inquiry->currentAssignment->agency)
                                    <div>
                                        <strong>{{ $inquiry->currentAssignment->agency->agency_name }}</strong>
                                        <br><small class="text-muted">{{ $inquiry->currentAssignment->agency->agencyType }}</small>
                                    </div>
                                @else
                                    <span class="text-muted">Not Assigned</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ \Carbon\Carbon::parse($inquiry->submission_date)->format('M j, Y') }}</small>
                            </td>
                            <td>
                                @if($inquiry->currentAssignment)
                                    <small>{{ \Carbon\Carbon::parse($inquiry->currentAssignment->assignDate)->format('M j, Y') }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $totalSteps = 4; // New, Assigned, Under Investigation, Resolved
                                    $currentStep = match($status) {
                                        'New' => 1,
                                        'Assigned', 'Under Investigation' => 2,
                                        'True', 'Fake', 'Rejected' => 4,
                                        default => 1
                                    };
                                    $progressPercent = ($currentStep / $totalSteps) * 100;
                                @endphp
                                <div class="progress progress-modern">
                                    <div class="progress-bar bg-{{ $statusClass }}" style="width: {{ $progressPercent }}%"></div>
                                </div>
                                <small class="text-muted">{{ $currentStep }}/{{ $totalSteps }} steps</small>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary view-details" data-inquiry-id="{{ $inquiry->inquiryId }}">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @if($status === 'Under Investigation')
                                <button class="btn btn-sm btn-outline-warning track-progress" data-inquiry-id="{{ $inquiry->inquiryId }}">
                                    <i class="fas fa-clock"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Report Generation Modal -->
<div class="modal fade" id="reportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-chart-line me-2"></i>Generate Agency Performance Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="reportForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="reportStartDate" name="start_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" id="reportEndDate" name="end_date" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Agency (Optional)</label>
                            <select class="form-select" name="agency_id">
                                <option value="">All Agencies</option>
                                @foreach($agencyStats as $agency)
                                    <option value="{{ $agency->agencyId }}">{{ $agency->agency_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Inquiry Category (Optional)</label>
                            <input type="text" class="form-control" name="inquiry_category" placeholder="e.g., scam, fraud">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Report Format</label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="format" id="formatJson" value="json" checked>
                                    <label class="form-check-label" for="formatJson">
                                        <i class="fas fa-code me-2"></i>JSON Data
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="format" id="formatPdf" value="pdf">
                                    <label class="form-check-label" for="formatPdf">
                                        <i class="fas fa-file-pdf me-2"></i>PDF Report
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="format" id="formatExcel" value="excel">
                                    <label class="form-check-label" for="formatExcel">
                                        <i class="fas fa-file-excel me-2"></i>Excel Report
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-modern" id="generateReport">
                    <i class="fas fa-download me-2"></i>Generate Report
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Inquiry Details Modal -->
<div class="modal fade" id="inquiryDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Inquiry Details & Status History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="inquiryDetailsContent">
                <!-- Dynamic content loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    // Set default dates (last 30 days)
    const today = new Date();
    const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, today.getDate());
    
    $('#reportStartDate').val(lastMonth.toISOString().split('T')[0]);
    $('#reportEndDate').val(today.toISOString().split('T')[0]);
    
    // Initialize charts
    initializeCharts();
    
    // Set up real-time updates
    setInterval(refreshRecentActivities, 30000); // Refresh every 30 seconds
    
    // Event handlers
    $('#refreshDashboard').click(function() {
        location.reload();
    });
    
    $('#applyFilters').click(function() {
        console.log('Apply Filters button clicked');
        applyDashboardFilters();
    });
    
    $('#clearFilters').click(function() {
        console.log('Clear Filters button clicked');
        clearDashboardFilters();
    });
    
    $('#generateReport').click(function() {
        console.log('Generate Report button clicked');
        generateAgencyReport();
    });
    
    $('#refreshAgencyStats').click(function() {
        refreshAgencyPerformance();
    });
    
    $('#refreshInquiriesTable').click(function() {
        refreshInquiriesTable();
    });
    
    $('.view-details').click(function() {
        const inquiryId = $(this).data('inquiry-id');
        viewInquiryDetails(inquiryId);
    });
    
    $('.agency-performance-card').click(function() {
        const agencyName = $(this).data('agency-id');
        filterByAgency(agencyName);
    });
});

function initializeCharts() {
    // Status Distribution Pie Chart
    const statusData = @json($statusDistribution);
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(statusData),
            datasets: [{
                data: Object.values(statusData),
                backgroundColor: [
                    '#28a745', '#ffc107', '#dc3545', '#6c757d', '#17a2b8'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    
    // Inquiry Trends Line Chart
    const trendsData = @json($inquiryTrends);
    const trendsCtx = document.getElementById('trendsChart').getContext('2d');
    
    new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: trendsData.map(item => item.date),
            datasets: [{
                label: 'New Inquiries',
                data: trendsData.map(item => item.count),
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function refreshRecentActivities() {
    $.get('/dashboard/mcmc/activities', function(data) {
        const container = $('#recentActivitiesContainer');
        container.empty();
        
        data.forEach(function(activity) {
            const statusClass = getStatusClass(activity.new_status);
            const html = `
                <div class="activity-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">${activity.inquiry_title}</h6>
                            <p class="mb-1 text-muted small">
                                Status updated to: 
                                <span class="status-badge bg-${statusClass}">${activity.new_status}</span>
                            </p>
                            <small class="text-muted">
                                <i class="fas fa-building me-1"></i>${activity.agency_name}
                            </small>
                            ${activity.status_comment ? `<p class="mb-0 mt-2 small text-dark"><i class="fas fa-comment me-1"></i>${activity.status_comment}</p>` : ''}
                        </div>
                        <small class="text-muted">${formatTimeAgo(activity.updated_at)}</small>
                    </div>
                </div>
            `;
            container.append(html);
        });
    }).fail(function() {
        console.log('Failed to refresh activities');
    });
}

function applyDashboardFilters() {
    console.log('applyDashboardFilters function called');
    
    const filters = {
        status: $('#statusFilter').val(),
        agency_id: $('#agencyFilter').val(),
        date_from: $('#dateFromFilter').val(),
        date_to: $('#dateToFilter').val()
    };
    
    console.log('Filter values:', filters);
    
    // Show loading state
    $('#applyFilters').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Filtering...');
    
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    $.get('/dashboard/mcmc/filter', filters, function(response) {
        console.log('Filter response:', response);
        
        if (response.success) {
            updateInquiriesTable(response.data);
            if (response.total !== undefined) {
                console.log(`Filtered results: ${response.total} inquiries found`);
            }
        } else {
            console.error('Filter failed:', response.error);
            alert('Filter failed: ' + response.error);
        }
        
        $('#applyFilters').prop('disabled', false).html('<i class="fas fa-search me-2"></i>Apply');
    }).fail(function(xhr, status, error) {
        console.error('Filter request failed:', xhr.responseText);
        alert('Failed to apply filters. Error: ' + error);
        $('#applyFilters').prop('disabled', false).html('<i class="fas fa-search me-2"></i>Apply');
    });
}

function generateAgencyReport() {
    console.log('generateAgencyReport function called');
    
    // Validate that form exists
    if ($('#reportForm').length === 0) {
        alert('Report form not found!');
        return;
    }
    
    // Check if dates are filled
    const startDate = $('#reportStartDate').val();
    const endDate = $('#reportEndDate').val();
    
    if (!startDate || !endDate) {
        alert('Please select both start and end dates.');
        return;
    }
    
    // Get selected format
    const format = $('input[name="format"]:checked').val();
    console.log('Selected format:', format);
    
    $('#generateReport').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Generating...');
    
    // For Excel, use form submission for direct download
    if (format === 'excel') {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/dashboard/mcmc/report';
        form.target = '_blank';
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = $('meta[name="csrf-token"]').attr('content');
        form.appendChild(csrfToken);
        
        // Add form data
        const formData = $('#reportForm').serializeArray();
        formData.forEach(item => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = item.name;
            input.value = item.value;
            form.appendChild(input);
        });
        
        // Submit form
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
        
        setTimeout(() => {
            alert('Excel report is being downloaded!');
            $('#reportModal').modal('hide');
        }, 500);
        
    } else {
        // For PDF and JSON, use AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        const requestData = $('#reportForm').serialize();
        
        $.ajax({
            url: '/dashboard/mcmc/report',
            method: 'POST',
            data: requestData,
            dataType: 'json',
            success: function(response) {
                console.log('Report generation successful:', response);
                
                if (format === 'pdf' && response.success) {
                    // Generate PDF with charts
                    generatePDFWithCharts(response.report_data, response.charts_data, response.summary);
                } else {
                    // JSON format
                    alert('JSON report generated successfully! Check browser console for detailed data.');
                    if (response.summary) {
                        alert(`Report Summary:\n- Total Agencies: ${response.summary.total_agencies}\n- Date Range: ${response.summary.date_range}\n- Format: ${response.summary.format}`);
                    }
                }
                
                $('#reportModal').modal('hide');
            },
            error: function(xhr, status, error) {
                console.error('Report generation failed:', xhr.responseText);
                alert('Failed to generate report. Check browser console for details.');
            }
        });
    }
    
    // Re-enable button
    setTimeout(() => {
        $('#generateReport').prop('disabled', false).html('<i class="fas fa-download me-2"></i>Generate Report');
    }, 1000);
}

function viewInquiryDetails(inquiryId) {
    $.get(`/dashboard/inquiry/${inquiryId}/status`, function(data) {
        const content = `
            <div class="row">
                <div class="col-md-8">
                    <h6>Current Status: <span class="status-badge bg-${data.status_color}">${data.status}</span></h6>
                    <div class="mt-4">
                        <h6>Status History</h6>
                        <div class="timeline">
                            ${data.history.map(h => `
                                <div class="timeline-item mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6>${h.status}</h6>
                                            <p class="mb-1">${h.comment || 'No comment provided'}</p>
                                            <small class="text-muted">
                                                <i class="fas fa-building me-1"></i>Agency: ${h.agency}
                                                <br>
                                                <i class="fas fa-clock me-1"></i>${h.date}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('#inquiryDetailsContent').html(content);
        $('#inquiryDetailsModal').modal('show');
    });
}

function refreshAgencyPerformance() {
    // Add loading state
    $('#refreshAgencyStats').html('<i class="fas fa-spinner fa-spin"></i>');
    
    setTimeout(function() {
        $('#refreshAgencyStats').html('<i class="fas fa-sync-alt"></i>');
        // In a real implementation, this would make an AJAX call to refresh data
        location.reload();
    }, 1000);
}

function refreshInquiriesTable() {
    $('#refreshInquiriesTable').html('<i class="fas fa-spinner fa-spin"></i>');
    
    setTimeout(function() {
        $('#refreshInquiriesTable').html('<i class="fas fa-sync-alt"></i>');
        location.reload();
    }, 1000);
}

function updateInquiriesTable(inquiries) {
    const tbody = $('#inquiriesTableBody');
    tbody.empty();
    
    inquiries.forEach(function(inquiry) {
        const statusClass = getStatusClass(inquiry.current_status);
        const row = `
            <tr data-inquiry-id="${inquiry.inquiryId}">
                <td><strong>#${inquiry.inquiryId}</strong></td>
                <td>
                    <div class="text-truncate" style="max-width: 200px;" title="${inquiry.title}">
                        ${inquiry.title}
                    </div>
                </td>
                <td><span class="status-badge bg-${statusClass}">${inquiry.current_status}</span></td>
                <td>
                    ${inquiry.agency ? `
                        <div>
                            <strong>${inquiry.agency.agency_name}</strong>
                            <br><small class="text-muted">${inquiry.agency.agencyType}</small>
                        </div>
                    ` : '<span class="text-muted">Not Assigned</span>'}
                </td>
                <td><small>${formatDate(inquiry.submission_date)}</small></td>
                <td><small>${inquiry.assign_date ? formatDate(inquiry.assign_date) : '-'}</small></td>
                <td>
                    <div class="progress progress-modern">
                        <div class="progress-bar bg-${statusClass}" style="width: ${getProgressPercent(inquiry.current_status)}%"></div>
                    </div>
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-primary view-details" data-inquiry-id="${inquiry.inquiryId}">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
    
    // Reattach event handlers
    $('.view-details').click(function() {
        const inquiryId = $(this).data('inquiry-id');
        viewInquiryDetails(inquiryId);
    });
}

function getStatusClass(status) {
    switch(status) {
        case 'Under Investigation': return 'warning';
        case 'True': return 'success';
        case 'Fake': return 'danger';
        case 'Rejected': return 'secondary';
        case 'Reassigned': return 'info';
        default: return 'info';
    }
}

function getProgressPercent(status) {
    switch(status) {
        case 'New': return 25;
        case 'Assigned': case 'Under Investigation': return 50;
        case 'True': case 'Fake': case 'Rejected': return 100;
        default: return 25;
    }
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}

function formatTimeAgo(dateString) {
    const diff = Date.now() - new Date(dateString).getTime();
    const minutes = Math.floor(diff / 60000);
    if (minutes < 60) return `${minutes}m ago`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours}h ago`;
    const days = Math.floor(hours / 24);
    return `${days}d ago`;
}

function filterByAgency(agencyName) {
    // This would filter the dashboard by the selected agency
    console.log('Filtering by agency:', agencyName);
    // Implementation would update filters and refresh data
}

function clearDashboardFilters() {
    console.log('clearDashboardFilters function called');
    
    // Reset all filter inputs
    $('#statusFilter').val('');
    $('#agencyFilter').val('');
    $('#dateFromFilter').val('');
    $('#dateToFilter').val('');
    
    // Reload the page to show all data
    location.reload();
}

// Generate PDF with charts and graphs
async function generatePDFWithCharts(reportData, chartsData, summary) {
    try {
        console.log('Generating PDF with charts...');
        
        // Initialize jsPDF
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('p', 'mm', 'a4');
        
        // PDF dimensions
        const pageWidth = pdf.internal.pageSize.getWidth();
        const pageHeight = pdf.internal.pageSize.getHeight();
        let yPosition = 20;
        
        // Header
        pdf.setFontSize(20);
        pdf.setFont(undefined, 'bold');
        pdf.text('AGENCY PERFORMANCE REPORT', pageWidth / 2, yPosition, { align: 'center' });
        yPosition += 15;
        
        // Date range and generated info
        pdf.setFontSize(12);
        pdf.setFont(undefined, 'normal');
        pdf.text(`Date Range: ${summary.date_range}`, 20, yPosition);
        yPosition += 7;
        pdf.text(`Generated: ${summary.generated_at}`, 20, yPosition);
        yPosition += 7;
        pdf.text(`Total Agencies: ${summary.total_agencies}`, 20, yPosition);
        yPosition += 15;
        
        // Summary Statistics
        if (chartsData.summary_stats) {
            pdf.setFontSize(14);
            pdf.setFont(undefined, 'bold');
            pdf.text('SUMMARY STATISTICS', 20, yPosition);
            yPosition += 10;
            
            pdf.setFontSize(11);
            pdf.setFont(undefined, 'normal');
            const stats = chartsData.summary_stats;
            pdf.text(`Total Inquiries Assigned: ${stats.total_assigned}`, 20, yPosition);
            yPosition += 6;
            pdf.text(`Total Inquiries Resolved: ${stats.total_resolved}`, 20, yPosition);
            yPosition += 6;
            pdf.text(`Total Pending: ${stats.total_pending}`, 20, yPosition);
            yPosition += 6;
            pdf.text(`Average Resolution Rate: ${stats.avg_resolution_rate}%`, 20, yPosition);
            yPosition += 15;
        }
        
        // Create charts and add to PDF
        if (chartsData.agency_performance && chartsData.agency_performance.labels.length > 0) {
            // Create a temporary canvas for charts
            const canvas = document.createElement('canvas');
            canvas.width = 600;
            canvas.height = 400;
            const ctx = canvas.getContext('2d');
            
            // Chart 1: Agency Performance Bar Chart
            const chart1 = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartsData.agency_performance.labels,
                    datasets: [
                        {
                            label: 'Assigned',
                            data: chartsData.agency_performance.assigned,
                            backgroundColor: 'rgba(54, 162, 235, 0.8)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Resolved',
                            data: chartsData.agency_performance.resolved,
                            backgroundColor: 'rgba(75, 192, 192, 0.8)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: false,
                    animation: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Agency Performance: Assigned vs Resolved'
                        },
                        legend: {
                            display: true
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            
            // Wait for chart to render
            await new Promise(resolve => setTimeout(resolve, 1000));
            
            // Convert chart to image and add to PDF
            const chartImage = canvas.toDataURL('image/png');
            pdf.text('AGENCY PERFORMANCE CHART', 20, yPosition);
            yPosition += 10;
            pdf.addImage(chartImage, 'PNG', 20, yPosition, 160, 80);
            yPosition += 90;
            
            // Destroy chart
            chart1.destroy();
            
            // Check if we need a new page
            if (yPosition > pageHeight - 60) {
                pdf.addPage();
                yPosition = 20;
            }
            
            // Chart 2: Resolution Rate Pie Chart (if we have multiple agencies)
            if (chartsData.agency_performance.labels.length > 1) {
                const chart2 = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: chartsData.agency_performance.labels,
                        datasets: [{
                            data: chartsData.agency_performance.rates,
                            backgroundColor: [
                                '#FF6384',
                                '#36A2EB',
                                '#FFCE56',
                                '#4BC0C0',
                                '#9966FF',
                                '#FF9F40',
                                '#FF6384',
                                '#C9CBCF'
                            ],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: false,
                        animation: false,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Resolution Rates by Agency (%)'
                            },
                            legend: {
                                display: true,
                                position: 'right'
                            }
                        }
                    }
                });
                
                // Wait for chart to render
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                // Add to PDF
                const chartImage2 = canvas.toDataURL('image/png');
                pdf.text('RESOLUTION RATES BREAKDOWN', 20, yPosition);
                yPosition += 10;
                pdf.addImage(chartImage2, 'PNG', 20, yPosition, 160, 80);
                yPosition += 90;
                
                // Destroy chart
                chart2.destroy();
            }
        }
        
        // Check if we need a new page for the table
        if (yPosition > pageHeight - 100) {
            pdf.addPage();
            yPosition = 20;
        }
        
        // Detailed Agency Data Table
        pdf.setFontSize(14);
        pdf.setFont(undefined, 'bold');
        pdf.text('DETAILED AGENCY DATA', 20, yPosition);
        yPosition += 10;
        
        // Table headers
        pdf.setFontSize(9);
        pdf.setFont(undefined, 'bold');
        const colWidths = [40, 25, 25, 25, 30, 35];
        const headers = ['Agency Name', 'Type', 'Assigned', 'Resolved', 'Rate (%)', 'Pending'];
        let xPos = 20;
        
        headers.forEach((header, index) => {
            pdf.text(header, xPos, yPosition);
            xPos += colWidths[index];
        });
        yPosition += 7;
        
        // Table data
        pdf.setFont(undefined, 'normal');
        reportData.forEach(agency => {
            if (yPosition > pageHeight - 20) {
                pdf.addPage();
                yPosition = 20;
                // Redraw headers on new page
                pdf.setFont(undefined, 'bold');
                let xPos2 = 20;
                headers.forEach((header, index) => {
                    pdf.text(header, xPos2, yPosition);
                    xPos2 += colWidths[index];
                });
                yPosition += 7;
                pdf.setFont(undefined, 'normal');
            }
            
            xPos = 20;
            const data = [
                (agency.agency_name || 'Unknown').substring(0, 18),
                (agency.agency_type || 'N/A').substring(0, 10),
                String(agency.total_assigned || 0),
                String(agency.total_resolved || 0),
                String(agency.resolution_rate || 0),
                String(agency.pending_count || 0)
            ];
            
            data.forEach((cell, index) => {
                pdf.text(cell, xPos, yPosition);
                xPos += colWidths[index];
            });
            yPosition += 6;
        });
        
        // Footer
        const totalPages = pdf.internal.getNumberOfPages();
        for (let i = 1; i <= totalPages; i++) {
            pdf.setPage(i);
            pdf.setFontSize(8);
            pdf.text(`Page ${i} of ${totalPages}`, pageWidth - 30, pageHeight - 10);
            pdf.text('Generated by MCMC Dashboard System', 20, pageHeight - 10);
        }
        
        // Save the PDF
        const fileName = `agency-performance-report-${new Date().toISOString().slice(0,10)}.pdf`;
        pdf.save(fileName);
        
        alert('PDF report with charts has been generated and downloaded successfully!');
        
    } catch (error) {
        console.error('Error generating PDF:', error);
        alert('Error generating PDF: ' + error.message);
    }
}

// MCMC Completion Notification Functions
function viewCompletedInquiry(inquiryId) {
    // Scroll to the inquiry in the monitoring table
    const inquiryRow = document.querySelector(`tr[data-inquiry-id="${inquiryId}"]`);
    if (inquiryRow) {
        inquiryRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Highlight the inquiry row temporarily
        inquiryRow.style.backgroundColor = '#d1ecf1';
        inquiryRow.style.border = '2px solid #17a2b8';
        
        setTimeout(() => {
            inquiryRow.style.backgroundColor = '';
            inquiryRow.style.border = '';
        }, 4000);
        
        // Show success message
        showToast('Inquiry located in monitoring table', 'success');
    } else {
        // If not found in current view, show info message
        showToast('Inquiry may be filtered out of current view. Try clearing filters.', 'info');
    }
}

function markAsReviewed(statusId) {
    // Mark the notification as reviewed
    const notificationItem = document.querySelector(`[data-status-id="${statusId}"]`);
    if (notificationItem) {
        notificationItem.style.opacity = '0.6';
        notificationItem.style.textDecoration = 'line-through';
        
        // Remove NEW badge if present
        const newBadge = notificationItem.querySelector('.badge:contains("NEW")');
        if (newBadge) {
            newBadge.remove();
        }
        
        showToast('Completion notification marked as reviewed', 'success');
    }
    
    // TODO: Could add AJAX call here to store reviewed status in database if needed
}

function showAllCompletions() {
    // Toggle to show all completion notification items
    const notificationItems = document.querySelectorAll('.notification-item');
    const showMoreBtn = event.target.closest('button');
    const hiddenItems = Array.from(notificationItems).slice(4);
    
    hiddenItems.forEach(item => {
        if (item.style.display === 'none' || !item.style.display) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
    
    // Toggle button text
    const icon = showMoreBtn.querySelector('i');
    const text = showMoreBtn.childNodes[1];
    
    if (icon.classList.contains('fa-chevron-down')) {
        icon.classList.replace('fa-chevron-down', 'fa-chevron-up');
        text.textContent = ' Show less';
    } else {
        icon.classList.replace('fa-chevron-up', 'fa-chevron-down');
        const hiddenCount = hiddenItems.length;
        text.textContent = ` View ${hiddenCount} more completions`;
    }
}

function showToast(message, type = 'info') {
    // Create and show a toast notification
    const toastContainer = document.getElementById('toastContainer') || createToastContainer();
    
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-${type === 'success' ? 'check' : 'info'}-circle me-2"></i>
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Remove toast element after it's hidden
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}
</script>

</body>
</html>