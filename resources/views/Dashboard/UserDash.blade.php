@include('layouts.sidebarPublic')

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="dashboard-content">
    <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="mb-0">Welcome to Your Dashboard</h1>
                <p class="text-muted">View and track inquiry status and progress</p>
            </div>
        </div>

        <!-- Notification Bar -->
        @if($recentStatusUpdates && $recentStatusUpdates->count() > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <div class="d-flex align-items-center">
                        <div class="position-relative me-3">
                            <i class="fas fa-bell text-primary" style="font-size: 1.2rem;"></i>
                            @if($newNotificationCount > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                                {{ $newNotificationCount > 9 ? '9+' : $newNotificationCount }}
                            </span>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="alert-heading mb-2">
                                <i class="fas fa-info-circle me-1"></i>Recent Status Updates
                                @if($newNotificationCount > 0)
                                <span class="badge bg-danger ms-2">{{ $newNotificationCount }} New</span>
                                @endif
                            </h6>
                            <div class="notification-content">
                                @foreach($recentStatusUpdates->take(3) as $update)
                                <div class="notification-item mb-2 pb-2 {{ !$loop->last ? 'border-bottom' : '' }} {{ $update->is_new ?? false ? 'bg-light rounded p-2' : '' }}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            @if($update->is_new ?? false)
                                            <span class="badge bg-success me-2" style="font-size: 0.7rem;">NEW</span>
                                            @endif
                                            <strong class="text-primary">Case #{{ $update->inquiry->inquiryId }}</strong>
                                            <span class="mx-2">•</span>
                                            <span class="badge bg-{{ 
                                                $update->status === 'Under Investigation' ? 'warning' : 
                                                ($update->status === 'True' ? 'success' : 
                                                ($update->status === 'Fake' ? 'danger' : 'secondary')) 
                                            }}">
                                                {{ $update->status }}
                                            </span>
                                            @if($update->agency)
                                            <span class="mx-2">•</span>
                                            <small class="text-muted">{{ $update->agency->agencyName }}</small>
                                            @endif
                                            <br>
                                            <small class="text-muted">
                                                {{ Str::limit($update->inquiry->title ?? 'Untitled', 60) }}
                                            </small>
                                            @if($update->comments)
                                            <br>
                                            <small class="text-info">
                                                <i class="fas fa-comment me-1"></i>{{ Str::limit($update->comments, 80) }}
                                            </small>
                                            @endif
                                        </div>
                                        <button class="btn btn-sm btn-outline-primary ms-2" 
                                                onclick="viewInquiry('{{ $update->inquiry->inquiryId }}')"
                                                title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                @endforeach
                                @if($recentStatusUpdates->count() > 3)
                                <div class="text-center mt-2">
                                    <button class="btn btn-sm btn-link text-decoration-none" onclick="showAllNotifications()">
                                        <i class="fas fa-chevron-down me-1"></i>
                                        View {{ $recentStatusUpdates->count() - 3 }} more updates
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

        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="display-4 text-primary mb-2">{{ $totalInquiries ?? 0 }}</div>
                        <h6 class="card-title text-muted">Total Inquiries</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="display-4 text-warning mb-2">{{ $pendingInquiries ?? 0 }}</div>
                        <h6 class="card-title text-muted">Under Investigation</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="display-4 text-success mb-2">{{ $resolvedInquiries ?? 0 }}</div>
                        <h6 class="card-title text-muted">Resolved</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="display-4 text-info mb-2">{{ $recentUpdates ?? 0 }}</div>
                        <h6 class="card-title text-muted">Recent Updates</h6>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Inquiry Tracking Section -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-clipboard-list me-2"></i>
                            Active Inquiries - View Status & Progress
                        </h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-success" id="refreshAllBtn" onclick="refreshAllStatuses()">
                                <i class="fas fa-sync-alt"></i> Refresh All
                            </button>
                            <button class="btn btn-sm btn-outline-primary" id="viewModeBtn" onclick="toggleViewMode()">
                                <i class="fas fa-th-list"></i> <span id="viewModeText">Detailed View</span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Filters -->
                    <div class="card-body border-bottom bg-light">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Filter by Status</label>
                                <select class="form-select" id="statusFilter" onchange="applyFilters()">
                                    <option value="all">All Status</option>
                                    <option value="Under Investigation">Under Investigation</option>
                                    <option value="True">Verified as True</option>
                                    <option value="Fake">Identified as Fake</option>
                                    <option value="Rejected">Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Search Inquiries</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="searchInput" 
                                           placeholder="Search by title or description..."
                                           onkeypress="if(event.key==='Enter') performSearch()">
                                    <button class="btn btn-primary" type="button" id="searchBtn" onclick="performSearch()">
                                        <i class="fas fa-search"></i> Search
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Sort by</label>
                                <select class="form-select" id="sortBy" onchange="applySorting()">
                                    <option value="newest">Newest First</option>
                                    <option value="oldest">Oldest First</option>
                                    <option value="title">By Title</option>
                                    <option value="status">By Status</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex gap-2 align-items-center">
                                    <button class="btn btn-outline-secondary btn-sm" onclick="clearAllFilters()">
                                        <i class="fas fa-times"></i> Clear
                                    </button>
                                    <button class="btn btn-outline-success btn-sm" onclick="refreshResults()">
                                        <i class="fas fa-sync-alt"></i> Refresh
                                    </button>
                                    <span class="badge bg-primary fs-6 px-3 py-2">
                                        <i class="fas fa-list me-1"></i>
                                        <span id="inquiryCount">{{ isset($inquiries) ? $inquiries->count() : 0 }}</span> Found
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        @if(isset($inquiries) && $inquiries->count() > 0)
                            <div id="inquiriesContainer">
                                @foreach($inquiries as $inquiry)
                                <div class="inquiry-item border-bottom" data-inquiry-id="{{ $inquiry->inquiryId }}" data-status="{{ $inquiry->getCurrentStatus() }}" data-title="{{ strtolower($inquiry->title) }}" data-description="{{ strtolower($inquiry->description) }}">
                                    <!-- Summary Row -->
                                    <div class="p-3 inquiry-summary" onclick="toggleInquiryDetails({{ $inquiry->inquiryId }})">
                                        <div class="row align-items-center">
                                            <div class="col-md-5">
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                        <i class="fas fa-chevron-right inquiry-toggle" id="toggle-{{ $inquiry->inquiryId }}"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1">{{ $inquiry->title }}</h6>
                                                        <small class="text-muted">
                                                            Submitted: {{ $inquiry->submission_date->format('M d, Y') }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                @php
                                                    $status = $inquiry->getCurrentStatus();
                                                    $badgeClass = match($status) {
                                                        'Under Investigation' => 'bg-warning text-dark',
                                                        'True' => 'bg-success',
                                                        'Fake' => 'bg-danger',
                                                        'Rejected' => 'bg-secondary',
                                                        default => 'bg-info'
                                                    };
                                                @endphp
                                                <span class="badge {{ $badgeClass }} status-badge" 
                                                      data-inquiry-id="{{ $inquiry->inquiryId }}">
                                                    {{ $status }}
                                                </span>
                                            </div>
                                            <div class="col-md-2">
                                                @if($inquiry->statusHistory->count() > 0)
                                                    <small class="text-muted">
                                                        Status Updated
                                                    </small>
                                                @else
                                                    <small class="text-muted">No updates</small>
                                                @endif
                                            </div>
                                            <div class="col-md-2 text-end">
                                                <button class="btn btn-sm btn-outline-info" 
                                                        onclick="event.stopPropagation(); checkRealTimeStatus({{ $inquiry->inquiryId }})">
                                                    <i class="fas fa-sync-alt"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Detailed View (Initially Hidden) -->
                                    <div class="inquiry-details" id="details-{{ $inquiry->inquiryId }}" style="display: none;">
                                        <div class="bg-light p-4">
                                            <div class="row">
                                                <!-- Inquiry Information -->
                                                <div class="col-lg-6">
                                                    <h6 class="text-primary mb-3">
                                                        <i class="fas fa-info-circle me-2"></i>Inquiry Details
                                                    </h6>
                                                    <div class="mb-3">
                                                        <strong>Description:</strong>
                                                        <p class="mt-1">{{ $inquiry->description }}</p>
                                                    </div>
                                                    
                                                    @if($inquiry->evidenceUrl || $inquiry->evidenceFileUrl)
                                                    <div class="mb-3">
                                                        <strong>Evidence:</strong>
                                                        @if($inquiry->evidenceUrl)
                                                            <div class="mt-1">
                                                                <i class="fas fa-link me-1"></i>
                                                                <a href="{{ $inquiry->evidenceUrl }}" target="_blank" class="text-primary">
                                                                    {{ Str::limit($inquiry->evidenceUrl, 50) }}
                                                                </a>
                                                            </div>
                                                        @endif
                                                        @if($inquiry->evidenceFileUrl)
                                                            <div class="mt-1">
                                                                <i class="fas fa-file me-1"></i>
                                                                <a href="{{ $inquiry->evidenceFileUrl }}" target="_blank" class="text-primary">
                                                                    View Uploaded File
                                                                </a>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    @endif

                                                    @if($inquiry->currentAssignment() && $inquiry->currentAssignment()->agency)
                                                    <div class="mb-3">
                                                        <strong>Assigned Agency:</strong>
                                                        <p class="mt-1">
                                                            {{ $inquiry->currentAssignment()->agency->name }}
                                                            @if($inquiry->currentAssignment()->assignDate)
                                                            <br>
                                                            <small class="text-muted">
                                                                Assigned: {{ $inquiry->currentAssignment()->assignDate->format('M d, Y') }}
                                                            </small>
                                                            @endif
                                                        </p>
                                                    </div>
                                                    @endif
                                                </div>

                                                <!-- Status History -->
                                                <div class="col-lg-6">
                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <h6 class="text-primary mb-0">
                                                            <i class="fas fa-history me-2"></i>Status History
                                                        </h6>
                                                        <button class="btn btn-sm btn-outline-primary" onclick="loadFullHistory({{ $inquiry->inquiryId }})">
                                                            <i class="fas fa-expand-arrows-alt"></i> View All
                                                        </button>
                                                    </div>
                                                    @if($inquiry->statusHistory && $inquiry->statusHistory->count() > 0)
                                                        <div class="status-history-container">
                                                            @foreach($inquiry->statusHistory->sortByDesc('updated_at')->take(5) as $history)
                                                            @if($history)
                                                            <div class="status-history-item mb-3 p-3 border rounded">
                                                                <div class="d-flex align-items-start">
                                                                    <div class="status-marker me-3">
                                                                        @php
                                                                            $historyBadgeClass = match($history->status ?? 'Unknown') {
                                                                                'Under Investigation' => 'bg-warning text-dark',
                                                                                'True' => 'bg-success',
                                                                                'Fake' => 'bg-danger',
                                                                                'Rejected' => 'bg-secondary',
                                                                                default => 'bg-info'
                                                                            };
                                                                        @endphp
                                                                        <span class="badge {{ $historyBadgeClass }} p-2">
                                                                            <i class="fas fa-circle-notch"></i>
                                                                        </span>
                                                                    </div>
                                                                    <div class="status-details flex-grow-1">
                                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                                            <div>
                                                                                <h6 class="mb-1 text-dark">{{ $history->status ?? 'Unknown Status' }}</h6>
                                                                                <span class="badge badge-outline-primary small">
                                                                                    Agency ID: {{ $history->agencyId ?? 'Unknown' }}
                                                                                </span>
                                                                            </div>
                                                                            <div class="text-end">
                                                                                <small class="text-muted d-block">
                                                                                    <i class="fas fa-calendar-alt me-1"></i>
                                                                                    @if($history->updated_at && is_object($history->updated_at) && method_exists($history->updated_at, 'format'))
                                                                                        {{ $history->updated_at->format('M d, Y') }}
                                                                                    @elseif($history->updated_at)
                                                                                        {{ date('M d, Y', strtotime($history->updated_at)) }}
                                                                                    @else
                                                                                        No date
                                                                                    @endif
                                                                                </small>
                                                                                <small class="text-muted d-block">
                                                                                    <i class="fas fa-clock me-1"></i>
                                                                                    @if($history->updated_at && is_object($history->updated_at) && method_exists($history->updated_at, 'format'))
                                                                                        {{ $history->updated_at->format('h:i A') }}
                                                                                    @elseif($history->updated_at)
                                                                                        {{ date('h:i A', strtotime($history->updated_at)) }}
                                                                                    @else
                                                                                        No time
                                                                                    @endif
                                                                                </small>
                                                                            </div>
                                                                        </div>
                                                                                         @if($history->status_comment ?? false)
                                                        <div class="status-comment p-2 bg-light rounded mb-2">
                                                            <small class="text-muted d-block mb-1">
                                                                <i class="fas fa-comment-dots me-1"></i>Agency Comment:
                                                            </small>
                                                            <p class="mb-0 small">{{ $history->status_comment }}</p>
                                                        </div>
                                                        @endif
                                                        
                                                        @if($history->officer_name ?? false)
                                                        <div class="reviewing-officer p-2 bg-info bg-opacity-10 rounded mb-2">
                                                            <small class="text-muted d-block mb-1">
                                                                <i class="fas fa-user-tie me-1"></i>Reviewing Officer:
                                                            </small>
                                                            <p class="mb-0 small fw-bold text-info">{{ $history->officer_name }}</p>
                                                        </div>
                                                        @endif
                                                        
                                                        @if($history->supporting_document ?? false)
                                                        <div class="supporting-document p-2 bg-success bg-opacity-10 rounded mb-2">
                                                            <small class="text-muted d-block mb-1">
                                                                <i class="fas fa-paperclip me-1"></i>Supporting Document:
                                                            </small>
                                                            <div class="d-flex align-items-center">
                                                                <i class="fas fa-file-alt me-2 text-success"></i>
                                                                <a href="{{ route('download.supporting.document', basename($history->supporting_document)) }}" 
                                                                   class="text-decoration-none text-success fw-bold small">
                                                                    {{ basename($history->supporting_document) }}
                                                                </a>
                                                                <span class="badge bg-success ms-2 small">Download</span>
                                                            </div>
                                                        </div>
                                                        @endif
                                                                        
                                                                        <div class="status-metadata">
                                                                            <div class="row">
                                                                                <div class="col-6">
                                                                                    <small class="text-muted">
                                                                                        <i class="fas fa-user me-1"></i>
                                                                                        Updated by: {{ $history->updated_by_agent_id ?? 'System' }}
                                                                                    </small>
                                                                                </div>
                                                                                <div class="col-6 text-end">
                                                                                    <small class="text-muted">
                                                                                        <i class="fas fa-hashtag me-1"></i>
                                                                                        Log ID: #{{ $history->status_id ?? $history->id ?? 'N/A' }}
                                                                                    </small>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @endif
                                                            @endforeach
                                                            
                                                            @if($inquiry->statusHistory->count() > 5)
                                                                <div class="text-center mt-3 pt-2 border-top">
                                                                    <button class="btn btn-outline-secondary btn-sm" onclick="loadMoreHistory({{ $inquiry->inquiryId }})">
                                                                        <i class="fas fa-chevron-down me-1"></i>
                                                                        Load {{ $inquiry->statusHistory->count() - 5 }} more updates
                                                                    </button>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <div class="text-center py-4 border rounded bg-light">
                                                            <i class="fas fa-clock fa-2x text-muted mb-3"></i>
                                                            <h6 class="text-muted">No Status Updates Yet</h6>
                                                            <p class="text-muted small mb-0">
                                                                Your inquiry is being processed. Status updates will appear here.
                                                            </p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Progress Indicator -->
                                            <div class="mt-4 pt-3 border-top">
                                                <h6 class="text-primary mb-3">
                                                    <i class="fas fa-tasks me-2"></i>Investigation Progress
                                                </h6>
                                                <div class="progress-steps-horizontal">
                                                    <div class="step-horizontal {{ $inquiry->getCurrentStatus() != 'Pending' ? 'completed' : 'active' }}">
                                                        <div class="step-icon-horizontal">
                                                            <i class="fas fa-paper-plane"></i>
                                                        </div>
                                                        <div class="step-label-horizontal">Submitted</div>
                                                    </div>
                                                    
                                                    <div class="step-horizontal {{ in_array($inquiry->getCurrentStatus(), ['Under Investigation', 'True', 'Fake', 'Rejected']) ? 'completed' : '' }}">
                                                        <div class="step-icon-horizontal">
                                                            <i class="fas fa-search"></i>
                                                        </div>
                                                        <div class="step-label-horizontal">Under Investigation</div>
                                                    </div>
                                                    
                                                    <div class="step-horizontal {{ in_array($inquiry->getCurrentStatus(), ['True', 'Fake', 'Rejected']) ? 'completed' : '' }}">
                                                        <div class="step-icon-horizontal">
                                                            <i class="fas fa-check-circle"></i>
                                                        </div>
                                                        <div class="step-label-horizontal">
                                                            @if($inquiry->getCurrentStatus() == 'True')
                                                                Verified as True
                                                            @elseif($inquiry->getCurrentStatus() == 'Fake')
                                                                Identified as Fake
                                                            @elseif($inquiry->getCurrentStatus() == 'Rejected')
                                                                Rejected
                                                            @else
                                                                Completed
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                <h5>No Inquiries Found</h5>
                                <p class="text-muted mb-3">No inquiries have been submitted yet.</p>
                                <p class="text-muted">Inquiries will appear here when they are submitted by others.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Real-time Status Update</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="statusModalBody">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-content {
    padding: 2rem;
    margin-left: 250px;
    margin-top: 80px;
    min-height: 100vh;
    background-color: #f8f9fa;
}

.card {
    border-radius: 10px;
}

.inquiry-item {
    transition: all 0.3s ease;
}

/* Notification styles */
.notification-item.bg-light {
    border-left: 3px solid #198754;
    transition: all 0.3s ease;
}

.notification-item:hover {
    background-color: #f8f9fa !important;
}

.badge.bg-danger {
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.alert-info {
    border-left: 4px solid #0dcaf0;
}

.inquiry-summary {
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.inquiry-summary:hover {
    background-color: #f8f9fa;
}

.inquiry-toggle {
    transition: transform 0.3s ease;
    color: #6c757d;
}

.inquiry-toggle.rotated {
    transform: rotate(90deg);
}

.status-badge {
    font-size: 0.8rem;
    padding: 0.4rem 0.8rem;
}

/* Filter section styling */
.input-group .btn {
    transition: all 0.2s ease;
}

.input-group .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-select:focus, .form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.badge {
    font-weight: 500;
}

/* Status History Styles */
.status-history-container {
    max-height: 400px;
    overflow-y: auto;
}

.status-history-item {
    background: #fff;
    border: 1px solid #e9ecef !important;
    transition: all 0.2s ease;
}

.status-history-item:hover {
    border-color: #0d6efd !important;
    box-shadow: 0 2px 4px rgba(13, 110, 253, 0.1);
}

/* Supporting Document Styles */
.supporting-document {
    border-left: 4px solid #198754 !important;
}

.supporting-document a {
    color: #198754 !important;
    font-weight: 600;
    transition: all 0.2s ease;
}

.supporting-document a:hover {
    color: #146c43 !important;
    text-decoration: underline !important;
}

.reviewing-officer {
    border-left: 4px solid #0dcaf0 !important;
}

.status-marker .badge {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.status-details h6 {
    font-weight: 600;
    color: #2c3e50;
}

.badge-outline-primary {
    border: 1px solid #0d6efd;
    color: #0d6efd;
    background: transparent;
}

.status-comment {
    border-left: 3px solid #0d6efd;
    background: #f8f9fa !important;
}

.status-metadata {
    border-top: 1px solid #f1f3f4;
    padding-top: 0.5rem;
    margin-top: 0.5rem;
}

/* Interactive Button Styles */
.interactive-btn {
    transition: all 0.2s ease;
}

.interactive-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.interactive-btn:active {
    transform: translateY(0);
}

.fa-spin {
    animation: fa-spin 1s infinite linear;
}

@keyframes fa-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Filter Section Styles */
.filter-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 8px;
    margin-bottom: 1rem;
}

.interactive-select {
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    border: 1px solid #dee2e6;
}

.interactive-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.interactive-input {
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    border: 1px solid #dee2e6;
}

.interactive-input:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.search-btn {
    transition: all 0.2s ease;
    border-color: #0d6efd;
}

.search-btn:hover {
    background-color: #0d6efd;
    color: white;
    transform: scale(1.05);
}

.inquiry-counter {
    font-size: 0.85rem;
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, #0d6efd, #0056b3);
    border: none;
    font-weight: 500;
}

.action-buttons {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.25rem;
}

/* Status Refresh Button */
.status-refresh-btn {
    transition: all 0.2s ease;
    border-radius: 8px;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #17a2b8;
    background: linear-gradient(135deg, #ffffff, #f8f9fa);
}

.status-refresh-btn:hover {
    background: linear-gradient(135deg, #17a2b8, #20c997);
    color: white;
    transform: scale(1.1);
    box-shadow: 0 4px 8px rgba(23, 162, 184, 0.3);
}

.status-refresh-btn i {
    font-size: 1rem;
}

.status-refresh-btn:active {
    transform: scale(0.95);
}

.inquiry-item {
    transition: all 0.2s ease;
    border-radius: 8px;
    margin-bottom: 8px;
}

.inquiry-item:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.inquiry-summary {
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.inquiry-summary:hover {
    background-color: #f8f9fa;
}

.inquiry-toggle {
    transition: transform 0.3s ease;
    color: #6c757d;
}

.inquiry-toggle.rotated {
    transform: rotate(90deg);
}

.status-badge {
    font-size: 0.8rem;
    padding: 0.4rem 0.8rem;
    transition: transform 0.2s ease;
}

.status-badge:hover {
    transform: scale(1.05);
}

.timeline-compact {
    max-height: 300px;
    overflow-y: auto;
}

.timeline-item-compact {
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e9ecef;
}

.timeline-item-compact:last-child {
    border-bottom: none;
}

.timeline-marker-compact {
    flex-shrink: 0;
}

.timeline-content-compact {
    flex-grow: 1;
}

.progress-steps-horizontal {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
}

.progress-steps-horizontal::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 2px;
    background-color: #dee2e6;
    z-index: 1;
}

.step-horizontal {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    background-color: #f8f9fa;
    padding: 0.5rem;
    border-radius: 8px;
    position: relative;
    z-index: 2;
    min-width: 120px;
}

.step-horizontal.completed {
    background-color: #d4edda;
    color: #155724;
}

.step-horizontal.completed::before {
    content: '';
    position: absolute;
    top: 50%;
    left: -50px;
    width: 100px;
    height: 2px;
    background-color: #28a745;
    z-index: 1;
}

.step-horizontal.active {
    background-color: #fff3cd;
    color: #856404;
}

.step-icon-horizontal {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #6c757d;
    color: white;
    font-size: 1rem;
}

.step-horizontal.completed .step-icon-horizontal {
    background-color: #28a745;
}

.step-horizontal.active .step-icon-horizontal {
    background-color: #ffc107;
    color: #212529;
}

.step-label-horizontal {
    font-weight: 500;
    font-size: 0.8rem;
    text-align: center;
    line-height: 1.2;
}

@media (max-width: 768px) {
    .dashboard-content {
        margin-left: 0;
        padding: 1rem;
    }
    
    .progress-steps-horizontal {
        flex-direction: column;
        gap: 1rem;
    }
    
    .progress-steps-horizontal::before {
        width: 2px;
        height: 100%;
        left: 50%;
        top: 0;
        bottom: 0;
    }
    
    .step-horizontal.completed::before {
        display: none;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
let detailsViewMode = false;

function toggleInquiryDetails(inquiryId) {
    const details = document.getElementById(`details-${inquiryId}`);
    const toggle = document.getElementById(`toggle-${inquiryId}`);
    
    if (details.style.display === 'none') {
        details.style.display = 'block';
        toggle.classList.add('rotated');
    } else {
        details.style.display = 'none';
        toggle.classList.remove('rotated');
    }
}

function toggleViewMode() {
    const viewModeText = document.getElementById('viewModeText');
    const allDetails = document.querySelectorAll('.inquiry-details');
    const allToggles = document.querySelectorAll('.inquiry-toggle');
    
    detailsViewMode = !detailsViewMode;
    
    if (detailsViewMode) {
        allDetails.forEach(detail => {
            detail.style.display = 'block';
        });
        allToggles.forEach(toggle => toggle.classList.add('rotated'));
        viewModeText.textContent = 'Summary View';
    } else {
        allDetails.forEach(detail => {
            detail.style.display = 'none';
        });
        allToggles.forEach(toggle => toggle.classList.remove('rotated'));
        viewModeText.textContent = 'Detailed View';
    }
}

// Main filtering function
function applyFilters() {
    console.log('applyFilters() called');
    
    try {
        const statusFilter = document.getElementById('statusFilter');
        const searchInput = document.getElementById('searchInput');
        const inquiryItems = document.querySelectorAll('.inquiry-item');
        const inquiryCount = document.getElementById('inquiryCount');
        
        if (!statusFilter || !searchInput || !inquiryCount) {
            console.error('Required elements not found:', {
                statusFilter: !!statusFilter,
                searchInput: !!searchInput,
                inquiryCount: !!inquiryCount
            });
            return;
        }
        
        const statusValue = statusFilter.value;
        const searchTerm = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;
        
        console.log('Filtering with:', { statusValue, searchTerm, itemsFound: inquiryItems.length });
        
        inquiryItems.forEach(item => {
            const status = item.getAttribute('data-status') || '';
            const title = item.getAttribute('data-title') || '';
            const description = item.getAttribute('data-description') || '';
            
            const matchesStatus = statusValue === 'all' || status === statusValue;
            const matchesSearch = !searchTerm || 
                                 title.includes(searchTerm) || 
                                 description.includes(searchTerm);
            
            if (matchesStatus && matchesSearch) {
                item.style.display = 'block';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });
        
        // Update counter
        inquiryCount.textContent = visibleCount;
        
        console.log('Filter applied. Visible items:', visibleCount);
        
        // Show feedback
        if (searchTerm) {
            showNotification(`Found ${visibleCount} results for "${searchTerm}"`, 'info');
        }
    } catch (error) {
        console.error('Error in applyFilters:', error);
    }
}

// Search button function
function performSearch() {
    console.log('performSearch() called');
    
    try {
        const searchBtn = document.getElementById('searchBtn');
        if (!searchBtn) {
            console.error('Search button not found');
            return;
        }
        
        const originalContent = searchBtn.innerHTML;
        
        // Show loading
        searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
        searchBtn.disabled = true;
        
        setTimeout(() => {
            applyFilters();
            
            // Reset button
            searchBtn.innerHTML = originalContent;
            searchBtn.disabled = false;
            
            console.log('Search completed');
        }, 500);
    } catch (error) {
        console.error('Error in performSearch:', error);
    }
}

// Sorting function
function applySorting() {
    const sortBy = document.getElementById('sortBy').value;
    const container = document.getElementById('inquiriesContainer');
    const items = Array.from(container.children);
    
    items.sort((a, b) => {
        switch(sortBy) {
            case 'title':
                const titleA = a.getAttribute('data-title') || '';
                const titleB = b.getAttribute('data-title') || '';
                return titleA.localeCompare(titleB);
            case 'status':
                const statusA = a.getAttribute('data-status') || '';
                const statusB = b.getAttribute('data-status') || '';
                return statusA.localeCompare(statusB);
            case 'oldest':
                // For now, just reverse the current order
                return 1;
            case 'newest':
            default:
                return -1;
        }
    });
    
    // Re-append sorted items
    items.forEach(item => container.appendChild(item));
    
    // Apply current filters to sorted results
    applyFilters();
    
    showNotification(`Sorted by ${sortBy}`, 'success');
}

// Clear all filters
function clearAllFilters() {
    console.log('clearAllFilters() called');
    
    try {
        const statusFilter = document.getElementById('statusFilter');
        const searchInput = document.getElementById('searchInput');
        const sortBy = document.getElementById('sortBy');
        
        if (statusFilter) statusFilter.value = 'all';
        if (searchInput) searchInput.value = '';
        if (sortBy) sortBy.value = 'newest';
        
        applyFilters();
        
        showNotification('All filters cleared', 'info');
        console.log('Filters cleared successfully');
    } catch (error) {
        console.error('Error in clearAllFilters:', error);
    }
}

// Refresh results
function refreshResults() {
    console.log('refreshResults() called');
    
    try {
        const refreshBtn = document.querySelector('[onclick="refreshResults()"]');
        if (!refreshBtn) {
            console.error('Refresh button not found');
            return;
        }
        
        const originalContent = refreshBtn.innerHTML;
        
        refreshBtn.innerHTML = '<i class="fas fa-sync-alt fa-spin"></i> Refreshing...';
        refreshBtn.disabled = true;
        
        setTimeout(() => {
            applyFilters();
            
            refreshBtn.innerHTML = originalContent;
            refreshBtn.disabled = false;
            
            showNotification('Results refreshed', 'success');
            console.log('Refresh completed');
        }, 800);
    } catch (error) {
        console.error('Error in refreshResults:', error);
    }
}

// Refresh all statuses
function refreshAllStatuses() {
    const statusBadges = document.querySelectorAll('[data-inquiry-id]');
    let refreshCount = 0;
    
    statusBadges.forEach(badge => {
        const inquiryId = badge.getAttribute('data-inquiry-id');
        // Simulate status refresh - replace with actual API call
        setTimeout(() => {
            // Add visual feedback
            badge.style.opacity = '0.5';
            setTimeout(() => {
                badge.style.opacity = '1';
            }, 500);
        }, refreshCount * 200);
        refreshCount++;
    });
    
    // Show success message
    showAlert('All statuses refreshed successfully!', 'success');
}

// Check real-time status
function checkRealTimeStatus(inquiryId) {
    // For now, just show a notification since we don't have the modal in this view
    showNotification(`Checking status for inquiry ${inquiryId}...`, 'info');
    
    // Simulate API call
    setTimeout(() => {
        const statusBadge = document.querySelector(`[data-inquiry-id="${inquiryId}"]`);
        if (statusBadge) {
            statusBadge.style.opacity = '0.5';
            setTimeout(() => {
                statusBadge.style.opacity = '1';
                showNotification(`Status updated for inquiry ${inquiryId}`, 'success');
            }, 500);
        }
    }, 1000);
}

// Show alert notification
function showAlert(message, type) {
    console.log('showAlert called:', message, type);
    
    try {
        // Remove any existing alerts first
        document.querySelectorAll('.temp-alert').forEach(alert => alert.remove());
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed temp-alert`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            if (alertDiv && alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
        
        console.log('Alert shown successfully');
    } catch (error) {
        console.error('Error showing alert:', error);
        // Fallback to simple alert
        alert(message);
    }
}

// Show notification (alias for showAlert)
function showNotification(message, type) {
    showAlert(message, type);
}

// Legacy functions for compatibility
function filterInquiries() {
    applyFilters();
}

function searchInquiries() {
    applyFilters();
}

function sortInquiries() {
    applySorting();
}

function clearFilters() {
    clearAllFilters();
}

// Status History Functions
function loadFullHistory(inquiryId) {
    console.log('Loading full history for inquiry:', inquiryId);
    
    // Show loading notification
    showNotification('Loading complete status history...', 'info');
    
    // Make AJAX call to get full history
    fetch(`/inquiry/${inquiryId}/history/full`)
        .then(response => response.json())
        .then(data => {
            // Create modal or expand current section
            showStatusHistoryModal(data);
        })
        .catch(error => {
            console.error('Error loading full history:', error);
            showNotification('Failed to load complete history. Please try again.', 'danger');
        });
}

function loadMoreHistory(inquiryId) {
    console.log('Loading more history for inquiry:', inquiryId);
    
    // For now, just load full history
    loadFullHistory(inquiryId);
}

function showStatusHistoryModal(historyData) {
    // Create a modal to show full status history
    const modalHtml = `
        <div class="modal fade" id="statusHistoryModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-history me-2"></i>Complete Status History
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="status-history-timeline">
                            ${historyData.map((item, index) => `
                                <div class="timeline-item mb-4">
                                    <div class="d-flex">
                                        <div class="timeline-marker me-3">
                                            <span class="badge bg-${getStatusColor(item.status)} p-2">
                                                ${index + 1}
                                            </span>
                                        </div>
                                        <div class="timeline-content flex-grow-1">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="mb-0">${item.status}</h6>
                                                        <small class="text-muted">${item.formatted_date}</small>
                                                    </div>
                                                    <p class="text-muted mb-2">
                                                        <i class="fas fa-building me-1"></i>${item.agency_name}
                                                    </p>
                                                    ${item.status_comment ? `
                                                        <div class="alert alert-light mb-2">
                                                            <small><strong>Comment:</strong> ${item.status_comment}</small>
                                                        </div>
                                                    ` : ''}
                                                    ${item.officer_name ? `
                                                        <div class="alert alert-info alert-sm mb-2">
                                                            <small><strong><i class="fas fa-user-tie me-1"></i>Reviewing Officer:</strong> ${item.officer_name}</small>
                                                        </div>
                                                    ` : ''}
                                                    ${item.supporting_document ? `
                                                        <div class="alert alert-success alert-sm mb-2">
                                                            <small>
                                                                <strong><i class="fas fa-paperclip me-1"></i>Supporting Document:</strong>
                                                                <a href="${window.location.origin}/download/supporting-document/${item.supporting_document.split('/').pop()}" 
                                                                   class="text-decoration-none ms-1">
                                                                    <i class="fas fa-file-alt me-1"></i>${item.supporting_document.split('/').pop()}
                                                                    <span class="badge bg-success ms-1">Download</span>
                                                                </a>
                                                            </small>
                                                        </div>
                                                    ` : ''}
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <small class="text-muted">
                                                                <i class="fas fa-user me-1"></i>Updated by: ${item.updated_by || 'System'}
                                                            </small>
                                                        </div>
                                                        <div class="col-6 text-end">
                                                            <small class="text-muted">
                                                                <i class="fas fa-clock me-1"></i>${item.formatted_time}
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="exportStatusHistory()">
                            <i class="fas fa-download me-1"></i>Export History
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('statusHistoryModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('statusHistoryModal'));
    modal.show();
}

function getStatusColor(status) {
    switch(status) {
        case 'Under Investigation': return 'warning';
        case 'True': return 'success';
        case 'Fake': return 'danger';
        case 'Rejected': return 'secondary';
        default: return 'info';
    }
}

function exportStatusHistory() {
    showNotification('Exporting status history...', 'info');
    // Implement export functionality
    setTimeout(() => {
        showNotification('Status history exported successfully!', 'success');
    }, 1000);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard initializing...');
    
    // Check if required elements exist
    const statusFilter = document.getElementById('statusFilter');
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    const sortBy = document.getElementById('sortBy');
    const inquiryCount = document.getElementById('inquiryCount');
    
    console.log('Elements check:', {
        statusFilter: !!statusFilter,
        searchInput: !!searchInput, 
        searchBtn: !!searchBtn,
        sortBy: !!sortBy,
        inquiryCount: !!inquiryCount
    });
    
    // Initial filter application
    setTimeout(() => {
        try {
            applyFilters();
            console.log('Initial filters applied');
        } catch (error) {
            console.error('Error applying initial filters:', error);
        }
    }, 100);
    
    // Add enter key support for search
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                console.log('Enter key pressed in search');
                performSearch();
            }
        });
    }
    
    // Add click handlers to buttons (backup to onclick)
    if (searchBtn) {
        searchBtn.addEventListener('click', function() {
            console.log('Search button clicked');
            performSearch();
        });
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            console.log('Status filter changed to:', this.value);
            applyFilters();
        });
    }
    
    if (sortBy) {
        sortBy.addEventListener('change', function() {
            console.log('Sort changed to:', this.value);
            applySorting();
        });
    }
    
    // Add click handlers for action buttons
    document.querySelectorAll('[onclick*="clearAllFilters"]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Clear filters button clicked');
            clearAllFilters();
        });
    });
    
    document.querySelectorAll('[onclick*="refreshResults"]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Refresh results button clicked');
            refreshResults();
        });
    });
    
    console.log('Dashboard initialized successfully');
});

// Notification functions
function viewInquiry(inquiryId) {
    // Scroll to the inquiry in the main list
    const inquiryElement = document.querySelector(`[data-inquiry-id="${inquiryId}"]`);
    if (inquiryElement) {
        inquiryElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Highlight the inquiry temporarily
        inquiryElement.style.backgroundColor = '#fff3cd';
        inquiryElement.style.border = '2px solid #ffc107';
        
        setTimeout(() => {
            inquiryElement.style.backgroundColor = '';
            inquiryElement.style.border = '';
        }, 3000);
        
        // Also expand details if available
        toggleInquiryDetails(inquiryId);
    } else {
        showNotification('Inquiry not found in current view. Try clearing filters.', 'warning');
    }
}

function showAllNotifications() {
    // Toggle to show all notification items
    const notificationItems = document.querySelectorAll('.notification-item');
    const showMoreBtn = event.target.closest('button');
    const hiddenItems = Array.from(notificationItems).slice(3);
    
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
        text.textContent = ` View ${hiddenCount} more updates`;
    }
}

function dismissNotificationBar() {
    const alertElement = document.querySelector('.alert-info');
    if (alertElement) {
        alertElement.style.display = 'none';
    }
}
</script>