<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MCMC - New Inquiries Review</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        html,
        body {
            margin: 0;
            padding: 0;
            height: auto;
            overflow-x: hidden;
            overflow-y: auto;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-content {
            margin-left: 260px;
            margin-top: 0;
            padding: 20px;
            min-height: 100vh;
        }

        .inquiry-card {
            background: white;
            border: 1px solid #e9ecef;
            border-left: 4px solid #dc3545; /* Red for pending MCMC review */
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: box-shadow 0.2s ease;
            width: 100%;
        }

        .inquiry-card.assigned {
            border-left-color: #ffc107; /* Yellow for assigned */
        }

        .inquiry-card.rejected {
            border-left-color: #6c757d; /* Gray for rejected */
        }

        .inquiry-card:first-child {
            margin-top: 50px;
        }

        .inquiry-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .inquiry-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .inquiry-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2d3748;
            margin: 0;
        }

        .inquiry-id {
            font-size: 0.9rem;
            color: #4a5568;
            font-weight: 500;
        }

        .inquiry-body {
            padding: 20px;
        }

        .inquiry-description {
            color: #4a5568;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .inquiry-details {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 20px;
        }

        .detail-group {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .detail-value {
            font-size: 0.95rem;
            color: #4a5568;
        }

        .status-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #f1f3f4;
        }

        .evidence-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #f1f3f4;
        }

        .evidence-items-container {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .evidence-item {
            flex: 1;
            min-width: 200px;
        }

        .evidence-link {
            color: #4f46e5;
            text-decoration: none;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
        }

        .evidence-link:hover {
            text-decoration: underline;
        }

        .status-badge {
            background: linear-gradient(45deg, #dc3545, #e74c3c);
            color: white;
            font-weight: bold;
            border-radius: 12px;
            padding: 6px 12px;
            font-size: 0.8rem;
        }

        .status-badge.pending {
            background: linear-gradient(45deg, #dc3545, #e74c3c);
        }

        .status-badge.under-investigation {
            background: linear-gradient(45deg, #17a2b8, #20c997);
            color: white;
        }

        .status-badge.assigned {
            background: linear-gradient(45deg, #ffc107, #ffeb3b);
            color: #333;
        }

        .status-badge.rejected {
            background: linear-gradient(45deg, #6c757d, #495057);
            color: white;
        }

        .mcmc-actions {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #f1f3f4;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .page-header {
            margin-bottom: 30px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2d3748;
            margin: 0;
            display: flex;
            align-items: center;
        }

        .stats-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-bottom: 20px;
        }

        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stats-label {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .loading-spinner {
            text-align: center;
            padding: 2rem;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 5px;
            margin: 1rem 0;
        }        .alert-dismissible {
            margin-bottom: 20px;
        }

        /* Modal specific styles */
        .modal-xl {
            max-width: 1200px;
        }

        .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }

        .modal .card {
            border: 1px solid #dee2e6;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .modal .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }

            .inquiry-details {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .inquiry-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .inquiry-card {
                margin-bottom: 15px;
            }

            .inquiry-body {
                padding: 15px;
            }

            .evidence-items-container {
                flex-direction: column;
                gap: 1rem;
            }

            .evidence-item {
                min-width: auto;
            }

            .mcmc-actions {
                flex-direction: column;
            }
        }

        @media (max-width: 992px) {
            .inquiry-details {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>

<body>
    <!-- Include Sidebar -->
    @include('layouts.sidebarMcmc')

    <!-- Main Content -->
    <div class="main-content">
        <div class="content-wrapper">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-clipboard-list me-3"></i>New Inquiries for Review
                </h1>
                <p class="mb-0 text-muted">Review and validate inquiries before forwarding to agencies</p>
            </div>

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Statistics Cards -->            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="stats-card">
                        <div class="stats-number text-danger">{{ $inquiries->where('is_pending', true)->count() }}</div>
                        <div class="stats-label">Pending Review</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
                        <div class="stats-number text-info">{{ $inquiries->count() }}</div>
                        <div class="stats-label">Total Inquiries</div>
                    </div>
                </div>                <div class="col-md-4">
                    <div class="stats-card">
                        <div class="stats-number text-warning">{{ $inquiries->where('is_assigned', true)->count() }}</div>
                        <div class="stats-label">Assigned</div>
                    </div>
                </div>
            </div><!-- Content Container -->
            <div class="container-fluid" id="inquiry-container">
                @if($inquiries && $inquiries->count() > 0)
                    @foreach($inquiries as $inquiry)
                        @if(!$inquiry->is_rejected)
                        <div class="inquiry-card {{ $inquiry->is_assigned ? 'assigned' : '' }}">
                            <div class="inquiry-body">
                                <div class="inquiry-header">
                                    <div>
                                        <h3 class="inquiry-title">{{ $inquiry->title }}</h3>
                                        <p class="inquiry-id">Inquiry #{{ str_pad($inquiry->id, 4, '0', STR_PAD_LEFT) }}</p>
                                    </div>                                    <div class="text-end">
                                        @if($inquiry->is_pending)
                                            <span class="status-badge pending">
                                                <i class="fas fa-clock me-1"></i>Pending Review
                                            </span>
                                        @elseif($inquiry->is_assigned)
                                            <span class="status-badge assigned">
                                                <i class="fas fa-check me-1"></i>Assigned
                                            </span>
                                        @else
                                            <span class="status-badge under-investigation">
                                                <i class="fas fa-search me-1"></i>Under Investigation
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="inquiry-description">
                                    {{ $inquiry->description }}
                                </div>

                                <div class="inquiry-details">
                                    <div class="detail-group">
                                        <div class="detail-label">
                                            <i class="fas fa-user me-1"></i>Submitted By
                                        </div>
                                        <div class="detail-value">
                                            <strong>{{ $inquiry->user_info->name }}</strong><br>
                                            <small class="text-muted">{{ $inquiry->user_info->email }}</small>
                                            @if($inquiry->user_info->contact !== 'N/A')
                                                <br><small class="text-muted">{{ $inquiry->user_info->contact }}</small>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="detail-group">
                                        <div class="detail-label">
                                            <i class="fas fa-calendar me-1"></i>Submission Date
                                        </div>
                                        <div class="detail-value">
                                            {{ \Carbon\Carbon::parse($inquiry->submission_date)->format('F d, Y') }}
                                            <br><small class="text-muted">{{ \Carbon\Carbon::parse($inquiry->submission_date)->diffForHumans() }}</small>
                                        </div>
                                    </div>

                                    <div class="detail-group">
                                        <div class="detail-label">
                                            <i class="fas fa-paperclip me-1"></i>Evidence
                                        </div>
                                        <div class="detail-value">
                                            @if($inquiry->evidence_count > 0)
                                                <span class="badge bg-primary">{{ $inquiry->evidence_count }} Evidence(s)</span>
                                            @else
                                                <span class="text-muted">No evidence provided</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                @if($inquiry->evidence_count > 0)
                                    <div class="evidence-section">
                                        <div class="detail-label">
                                            <i class="fas fa-folder-open me-1"></i>Supporting Evidence
                                        </div>
                                        <div class="evidence-items-container">
                                            @if(count($inquiry->evidence_files) > 0)
                                                <div class="evidence-item">
                                                    <strong>Evidence Files:</strong><br>
                                                    @foreach($inquiry->evidence_files as $file)
                                                        <a href="{{ $file['url'] }}" target="_blank" class="evidence-link d-block">
                                                            <i class="fas fa-file me-1"></i>{{ $file['name'] }}
                                                        </a>
                                                    @endforeach
                                                </div>
                                            @endif

                                            @if($inquiry->evidence_url)
                                                <div class="evidence-item">
                                                    <strong>Evidence Link:</strong><br>
                                                    <a href="{{ $inquiry->evidence_url }}" target="_blank" class="evidence-link">
                                                        <i class="fas fa-external-link-alt me-1"></i>View Evidence Link
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                @if($inquiry->mcmc_comments)
                                    <div class="status-section">
                                        <div class="detail-label">
                                            <i class="fas fa-comment me-1"></i>MCMC Comments
                                        </div>
                                        <div class="detail-value">
                                            <div class="alert alert-info mb-0">
                                                {{ $inquiry->mcmc_comments }}
                                            </div>
                                        </div>
                                    </div>
                                @endif                                <!-- MCMC Actions -->
                                <div class="mcmc-actions">                                    @if(!$inquiry->is_assigned)                                        <a href="{{ route('module3.mcmc.assign', $inquiry->id) }}" class="btn btn-success">
                                            <i class="fas fa-check me-1"></i>Validate & Assign
                                        </a>                                    @else
                                        <a href="{{ route('module3.mcmc.assign', $inquiry->id) }}" class="btn btn-warning">
                                            <i class="fas fa-edit me-1"></i>Update Assignment
                                        </a>
                                    @endif
                                    
                                    <button type="button" class="btn btn-danger" 
                                            onclick="showRejectModal({{ $inquiry->id }}, '{{ addslashes($inquiry->title) }}')">
                                        <i class="fas fa-times me-1"></i>Reject Inquiry
                                    </button>
                                    
                                    <button type="button" class="btn btn-info" 
                                            onclick="viewInquiryDetails({{ $inquiry->id }})">
                                        <i class="fas fa-eye me-1"></i>View Details
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                @else                    <div class="text-center py-5">
                        <i class="fas fa-clipboard-check text-muted" style="font-size: 80px;"></i>
                        <h4 class="text-muted mt-3">No Inquiries Found</h4>
                        <p class="text-muted mb-4">All inquiries have been reviewed and processed.</p>
                    </div>
                @endif
            </div>
        </div>    </div>

    <!-- View Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-eye me-2"></i>Inquiry Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailsModalBody">
                    <!-- Content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>    <!-- Reject Inquiry Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('mcmc.reject.inquiry') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-times-circle me-2"></i>Reject Inquiry
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="inquiry_id" id="reject_inquiry_id">
                        
                        <div class="mb-3">
                            <label class="form-label"><strong>Inquiry:</strong></label>
                            <p id="reject_inquiry_title" class="text-muted"></p>
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Warning:</strong> This action will mark the inquiry as rejected and it will not be processed further.
                        </div>

                        <div class="mb-3">
                            <label for="rejection_reason" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="4" 
                                      placeholder="Explain why this inquiry is being rejected..." required></textarea>
                            <div class="form-text">This reason will be visible to the user who submitted the inquiry.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times me-2"></i>Reject Inquiry
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>    <script>
        // Show reject modal
        function showRejectModal(inquiryId, inquiryTitle) {
            document.getElementById('reject_inquiry_id').value = inquiryId;
            document.getElementById('reject_inquiry_title').textContent = inquiryTitle;
            document.getElementById('rejection_reason').value = '';
            new bootstrap.Modal(document.getElementById('rejectModal')).show();
        }        // View inquiry details (show in modal on same page)
        function viewInquiryDetails(inquiryId) {
            const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
            const modalBody = document.getElementById('detailsModalBody');
            
            // Show loading state
            modalBody.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading inquiry details...</p>
                </div>
            `;
            
            modal.show();
            
            // Find the inquiry data from the current page
            const inquiryCards = document.querySelectorAll('.inquiry-card');
            let selectedInquiry = null;
            
            // Find the inquiry by ID from the displayed cards
            inquiryCards.forEach(card => {
                const cardTitle = card.querySelector('.inquiry-title').textContent;
                const cardId = card.querySelector('.inquiry-id').textContent;
                if (cardId.includes('#' + String(inquiryId).padStart(4, '0'))) {
                    selectedInquiry = {
                        id: inquiryId,
                        title: cardTitle,
                        description: card.querySelector('.inquiry-description').textContent.trim(),
                        user: card.querySelector('.detail-value').innerHTML,
                        date: card.querySelectorAll('.detail-value')[1].innerHTML,
                        evidence: card.querySelectorAll('.detail-value')[2].innerHTML,
                        status: card.querySelector('.status-badge').textContent.trim(),
                        evidenceSection: card.querySelector('.evidence-section'),
                        commentsSection: card.querySelector('.status-section')
                    };
                }
            });
            
            if (selectedInquiry) {
                // Build the detailed view HTML
                let detailsHTML = `
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-info-circle me-2"></i>Basic Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label text-muted"><strong>Inquiry ID:</strong></label>
                                                <p>#${String(selectedInquiry.id).padStart(4, '0')}</p>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-muted"><strong>Title:</strong></label>
                                                <p>${selectedInquiry.title}</p>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-muted"><strong>Status:</strong></label>
                                                <p>
                                                    <span class="badge bg-${getStatusColor(selectedInquiry.status)}">${selectedInquiry.status}</span>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label text-muted"><strong>Submission Date:</strong></label>
                                                <div>${selectedInquiry.date}</div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-muted"><strong>Evidence Count:</strong></label>
                                                <div>${selectedInquiry.evidence}</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label text-muted"><strong>Description:</strong></label>
                                        <div class="border rounded p-3 bg-light">
                                            ${selectedInquiry.description}
                                        </div>
                                    </div>
                                </div>
                            </div>
                `;
                
                // Add evidence section if exists
                if (selectedInquiry.evidenceSection) {
                    detailsHTML += `
                        <div class="card mt-3">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-paperclip me-2"></i>Supporting Evidence
                                </h5>
                            </div>
                            <div class="card-body">
                                ${selectedInquiry.evidenceSection.innerHTML}
                            </div>
                        </div>
                    `;
                }
                
                // Add comments section if exists
                if (selectedInquiry.commentsSection) {
                    detailsHTML += `
                        <div class="card mt-3">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-comments me-2"></i>MCMC Comments
                                </h5>
                            </div>
                            <div class="card-body">
                                ${selectedInquiry.commentsSection.innerHTML}
                            </div>
                        </div>
                    `;
                }
                
                detailsHTML += `
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-user me-2"></i>User Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    ${selectedInquiry.user}
                                </div>
                            </div>
                            
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-cogs me-2"></i>Quick Actions
                                    </h5>
                                </div>                                <div class="card-body">
                                    <a href="{{ url('/module3/mcmc/assign') }}/${selectedInquiry.id}" class="btn btn-success btn-sm w-100 mb-2">
                                        <i class="fas fa-check me-1"></i>Validate & Assign
                                    </a>
                                    <button type="button" class="btn btn-danger btn-sm w-100" 
                                            onclick="showRejectModal(${selectedInquiry.id}, '${selectedInquiry.title.replace(/'/g, "\\'")}')">
                                        <i class="fas fa-times me-1"></i>Reject Inquiry
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                modalBody.innerHTML = detailsHTML;
            } else {
                modalBody.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Unable to load inquiry details. Please try again.
                    </div>
                `;
            }
        }
        
        // Helper function to get status color
        function getStatusColor(status) {
            const statusLower = status.toLowerCase();
            if (statusLower.includes('pending')) return 'danger';
            if (statusLower.includes('assigned')) return 'warning';
            if (statusLower.includes('rejected')) return 'secondary';
            if (statusLower.includes('investigation')) return 'info';
            return 'primary';
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>