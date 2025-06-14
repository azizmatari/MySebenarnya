<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MCMC - Inquiry Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    @include('layouts.sidebarMcmc')
    
    <div class="main-content">
        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <!-- Page Header -->
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-0">
                                        <i class="material-icons" style="vertical-align: middle; margin-right: 10px;">assignment_ind</i>
                                        Inquiry Details - #{{ str_pad($inquiry->inquiryId, 4, '0', STR_PAD_LEFT) }}
                                    </h3>
                                </div>
                                <a href="{{ route('mcmc.inquiries') }}" class="btn btn-light">
                                    <i class="material-icons" style="vertical-align: middle; margin-right: 5px;">arrow_back</i>
                                    Back to List
                                </a>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Left Column - Inquiry Information -->
                            <div class="col-md-8">
                                <!-- Basic Information -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <i class="material-icons" style="vertical-align: middle; margin-right: 8px;">info</i>
                                            Basic Information
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label text-muted"><strong>Title:</strong></label>
                                                    <p>{{ $inquiry->title }}</p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label text-muted"><strong>Status:</strong></label>
                                                    <p>
                                                        @if($inquiry->final_status === 'Under Investigation')
                                                            <span class="badge bg-info">{{ $inquiry->final_status }}</span>
                                                        @elseif($inquiry->final_status === 'Rejected')
                                                            <span class="badge bg-danger">{{ $inquiry->final_status }}</span>
                                                        @else
                                                            <span class="badge bg-secondary">{{ $inquiry->final_status }}</span>
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label text-muted"><strong>Submission Date:</strong></label>
                                                    <p>{{ \Carbon\Carbon::parse($inquiry->submission_date)->format('F d, Y') }}</p>
                                                </div>
                                                @if($inquiry->assignDate)
                                                    <div class="mb-3">
                                                        <label class="form-label text-muted"><strong>Assignment Date:</strong></label>
                                                        <p>{{ \Carbon\Carbon::parse($inquiry->assignDate)->format('F d, Y') }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label text-muted"><strong>Description:</strong></label>
                                            <div class="border rounded p-3 bg-light">
                                                {{ $inquiry->description }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Evidence Section -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <i class="material-icons" style="vertical-align: middle; margin-right: 8px;">attach_file</i>
                                            Supporting Evidence
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        @if(count($evidence_files) > 0 || $inquiry->evidenceUrl)
                                            <!-- File Evidence -->
                                            @if(count($evidence_files) > 0)
                                                <div class="mb-3">
                                                    <h6>Uploaded Files:</h6>
                                                    <div class="row">
                                                        @foreach($evidence_files as $file)
                                                            <div class="col-md-4 mb-2">
                                                                <div class="card">
                                                                    <div class="card-body text-center p-3">
                                                                        @if($file['type'] === 'image')
                                                                            <i class="material-icons text-success" style="font-size: 32px;">image</i>
                                                                        @elseif($file['type'] === 'document')
                                                                            <i class="material-icons text-primary" style="font-size: 32px;">description</i>
                                                                        @elseif($file['type'] === 'video')
                                                                            <i class="material-icons text-warning" style="font-size: 32px;">videocam</i>
                                                                        @else
                                                                            <i class="material-icons text-secondary" style="font-size: 32px;">insert_drive_file</i>
                                                                        @endif
                                                                        <div class="mt-2">
                                                                            <small class="d-block">{{ $file['name'] }}</small>
                                                                            <a href="{{ $file['url'] }}" class="btn btn-sm btn-outline-primary mt-1" target="_blank">
                                                                                <i class="material-icons" style="font-size: 14px;">visibility</i>
                                                                                View
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- URL Evidence -->
                                            @if($inquiry->evidenceUrl)
                                                <div class="mb-3">
                                                    <h6>Evidence Links:</h6>
                                                    <div class="border rounded p-3 bg-light">
                                                        <a href="{{ $inquiry->evidenceUrl }}" target="_blank" class="text-decoration-none">
                                                            <i class="material-icons" style="vertical-align: middle; margin-right: 5px;">link</i>
                                                            {{ $inquiry->evidenceUrl }}
                                                        </a>
                                                    </div>
                                                </div>
                                            @endif
                                        @else
                                            <div class="text-center text-muted py-3">
                                                <i class="material-icons" style="font-size: 48px;">folder_open</i>
                                                <p class="mt-2">No evidence files or links provided</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Status History -->
                                @if($statusHistory && count($statusHistory) > 0)
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="mb-0">
                                                <i class="material-icons" style="vertical-align: middle; margin-right: 8px;">history</i>
                                                Status History
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="timeline">
                                                @foreach($statusHistory as $history)
                                                    <div class="timeline-item mb-3">
                                                        <div class="d-flex">
                                                            <div class="flex-shrink-0">
                                                                @if($history->status === 'Under Investigation')
                                                                    <span class="badge bg-info rounded-pill">{{ $history->status }}</span>
                                                                @elseif($history->status === 'Rejected')
                                                                    <span class="badge bg-danger rounded-pill">{{ $history->status }}</span>
                                                                @elseif($history->status === 'Assigned to Agency')
                                                                    <span class="badge bg-warning rounded-pill">{{ $history->status }}</span>
                                                                @else
                                                                    <span class="badge bg-secondary rounded-pill">{{ $history->status }}</span>
                                                                @endif
                                                            </div>
                                                            <div class="flex-grow-1 ms-3">
                                                                <div class="border-start ps-3">
                                                                    @if($history->status_comment)
                                                                        <p class="mb-1">{{ $history->status_comment }}</p>
                                                                    @endif
                                                                    @if($history->agencyName)
                                                                        <small class="text-muted">Agency: {{ $history->agency_name }}</small>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Right Column - User Information & Actions -->
                            <div class="col-md-4">
                                <!-- User Information -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <i class="material-icons" style="vertical-align: middle; margin-right: 8px;">person</i>
                                            User Information
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label text-muted"><strong>Name:</strong></label>
                                            <p>{{ $inquiry->user_name ?? 'Anonymous User' }}</p>
                                        </div>
                                        @if($inquiry->user_email)
                                            <div class="mb-3">
                                                <label class="form-label text-muted"><strong>Email:</strong></label>
                                                <p>
                                                    <a href="mailto:{{ $inquiry->user_email }}">{{ $inquiry->user_email }}</a>
                                                </p>
                                            </div>
                                        @endif
                                        @if($inquiry->user_contact)
                                            <div class="mb-3">
                                                <label class="form-label text-muted"><strong>Contact:</strong></label>
                                                <p>{{ $inquiry->user_contact }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Assignment Information -->
                                @if($inquiry->agencyName || $inquiry->mcmcComments)
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="mb-0">
                                                <i class="material-icons" style="vertical-align: middle; margin-right: 8px;">assignment_ind</i>
                                                Assignment Details
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            @if($inquiry->agencyName)
                                                <div class="mb-3">
                                                    <label class="form-label text-muted"><strong>Assigned Agency:</strong></label>
                                                    <p>{{ $inquiry->agency_name }}</p>
                                                </div>
                                            @endif
                                            @if($inquiry->mcmcComments)
                                                <div class="mb-3">
                                                    <label class="form-label text-muted"><strong>MCMC Comments:</strong></label>
                                                    <div class="border rounded p-3 bg-light">
                                                        {{ $inquiry->mcmcComments }}
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                <!-- Action Buttons -->
                                @if(!$inquiry->isRejected)
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0">
                                                <i class="material-icons" style="vertical-align: middle; margin-right: 8px;">settings</i>
                                                Actions
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            @if($inquiry->agencyName)
                                                <!-- Already assigned, show update options -->
                                                <button type="button" class="btn btn-warning w-100 mb-2" 
                                                        onclick="showUpdateAssignmentModal()">
                                                    <i class="material-icons" style="vertical-align: middle; margin-right: 5px;">edit</i>
                                                    Update Assignment
                                                </button>
                                            @else
                                                <!-- Not assigned, show validate option -->
                                                <button type="button" class="btn btn-success w-100 mb-2" 
                                                        onclick="showValidateModal({{ $inquiry->inquiryId }}, '{{ addslashes($inquiry->title) }}')">
                                                    <i class="material-icons" style="vertical-align: middle; margin-right: 5px;">check</i>
                                                    Validate & Assign
                                                </button>
                                            @endif
                                            
                                            <button type="button" class="btn btn-danger w-100" 
                                                    onclick="showRejectModal({{ $inquiry->inquiryId }}, '{{ addslashes($inquiry->title) }}')">
                                                <i class="material-icons" style="vertical-align: middle; margin-right: 5px;">block</i>
                                                Reject Inquiry
                                            </button>
                                        </div>
                                    </div>
                                @else
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <i class="material-icons text-danger" style="font-size: 48px;">block</i>
                                            <h5 class="text-danger mt-2">Inquiry Rejected</h5>
                                            <p class="text-muted">This inquiry has been rejected and cannot be processed further.</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include the same modals from MCMCInquiryList.blade.php -->
    <!-- Validate Modal -->
    <div class="modal fade" id="validateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('mcmc.validate.inquiry') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Validate Inquiry</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="inquiry_id" value="{{ $inquiry->inquiryId }}">
                        
                        <div class="mb-3">
                            <label for="agency_id" class="form-label">Assign to Agency</label>
                            <select name="agency_id" id="agency_id" class="form-select" required>
                                <option value="">Select Agency...</option>                                @foreach($agencies as $agency)
                                    <option value="{{ $agency->agencyId }}">{{ $agency->agency_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="mcmc_comments" class="form-label">MCMC Comments</label>
                            <textarea name="mcmc_comments" id="mcmc_comments" class="form-control" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Validate & Assign</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('mcmc.reject.inquiry') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Reject Inquiry</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="inquiry_id" value="{{ $inquiry->inquiryId }}">
                        
                        <div class="alert alert-warning">
                            <strong>Warning:</strong> This action will mark the inquiry as rejected.
                        </div>

                        <div class="mb-3">
                            <label for="rejection_reason" class="form-label">Rejection Reason</label>
                            <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject Inquiry</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showValidateModal(inquiryId, inquiryTitle) {
            new bootstrap.Modal(document.getElementById('validateModal')).show();
        }

        function showRejectModal(inquiryId, inquiryTitle) {
            new bootstrap.Modal(document.getElementById('rejectModal')).show();
        }

        function showUpdateAssignmentModal() {
            // Show update assignment modal (similar to validate modal)
            showValidateModal({{ $inquiry->inquiryId }}, '{{ addslashes($inquiry->title) }}');
        }
    </script>
</body>
</html>