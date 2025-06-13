<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Inquiries - MySebenarnya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    @include('layouts.sidebarPublic')
    
    <!-- Main content area with proper positioning -->
    <div class="main-content">
        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h3 class="mb-0">
                                    <i class="material-icons" style="vertical-align: middle; margin-right: 10px;">list_alt</i>
                                    My Inquiries
                                </h3>
                            </div>
                            <div class="card-body">                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <p class="text-muted mb-0">
                                        View all your previously submitted news verification inquiries and their current status.
                                    </p>
                                </div>                                @if($inquiries && $inquiries->count() > 0)
                                    <div class="row">
                                        @foreach($inquiries as $inquiry)
                                            @if(($inquiry->status === 'completed' && $inquiry->result) || $inquiry->status === 'rejected')
                                            <div class="col-12 mb-3">
                                                <div class="card border-left-primary shadow h-100 py-2">
                                                    <div class="card-body">
                                                        <div class="row no-gutters align-items-center">
                                                            <div class="col mr-2">
                                                                <div class="d-flex justify-content-between align-items-start">
                                                                    <div>                                                                        <h5 class="font-weight-bold text-primary mb-1">
                                                                            {{ $inquiry->title }}
                                                                        </h5>
                                                                        
                                                                        <!-- Show admin response preview or status info -->
                                                                        @if($inquiry->status === 'completed' && $inquiry->admin_response)
                                                                            <p class="text-muted small mb-2">
                                                                                <i class="material-icons" style="font-size: 14px; vertical-align: middle;">admin_panel_settings</i>
                                                                                <strong>Result:</strong> {{ Str::limit($inquiry->admin_response, 100) }}
                                                                            </p>
                                                                        @elseif($inquiry->status === 'rejected' && $inquiry->admin_response)
                                                                            <p class="text-danger small mb-2">
                                                                                <i class="material-icons" style="font-size: 14px; vertical-align: middle;">info</i>
                                                                                <strong>Reason:</strong> {{ Str::limit($inquiry->admin_response, 100) }}
                                                                            </p>
                                                                        @elseif($inquiry->status === 'in_progress')
                                                                            <p class="text-info small mb-2">
                                                                                <i class="material-icons" style="font-size: 14px; vertical-align: middle;">pending</i>
                                                                                Our team is currently verifying this claim. Please check back later.
                                                                            </p>
                                                                        @elseif($inquiry->status === 'pending')
                                                                            <p class="text-warning small mb-2">
                                                                                <i class="material-icons" style="font-size: 14px; vertical-align: middle;">schedule</i>
                                                                                Your inquiry is in queue for review. Estimated processing time: 2-3 business days.
                                                                            </p>
                                                                        @else
                                                                            <p class="text-muted small mb-2">
                                                                                <i class="material-icons" style="font-size: 14px; vertical-align: middle;">description</i>
                                                                                {{ Str::limit($inquiry->description, 100) }}
                                                                            </p>
                                                                        @endif
                                                                        <p class="text-muted small mb-2">
                                                                            <i class="material-icons" style="font-size: 14px; vertical-align: middle;">schedule</i>
                                                                            Submitted: {{ \Carbon\Carbon::parse($inquiry->submission_date)->format('M d, Y \a\t g:i A') }}
                                                                        </p>
                                                                        <p class="text-muted small mb-0">
                                                                            <strong>ID:</strong> #{{ str_pad($inquiry->id, 4, '0', STR_PAD_LEFT) }}
                                                                        </p>
                                                                    </div>                                                                    <div class="text-right">                                                                        <div class="d-flex flex-column align-items-end gap-2">
                                                                            @if($inquiry->status === 'completed' && $inquiry->result)
                                                                                @if($inquiry->result === 'true')
                                                                                    <span class="badge bg-success text-white px-3 py-2">
                                                                                        <i class="material-icons" style="font-size: 14px; vertical-align: middle;">verified</i>
                                                                                        TRUE
                                                                                    </span>
                                                                                @else
                                                                                    <span class="badge bg-danger text-white px-3 py-2">
                                                                                        <i class="material-icons" style="font-size: 14px; vertical-align: middle;">error</i>
                                                                                        FAKE
                                                                                    </span>
                                                                                @endif
                                                                            @elseif($inquiry->status === 'rejected')
                                                                                <span class="badge bg-warning text-dark px-3 py-2">
                                                                                    <i class="material-icons" style="font-size: 14px; vertical-align: middle;">cancel</i>
                                                                                    REJECTED
                                                                                </span>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row mt-2">
                                                            <div class="col-12">
                                                                <div class="btn-group w-100" role="group">
                                                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="viewDetails({{ $inquiry->id }})">
                                                                        <i class="material-icons" style="font-size: 16px; vertical-align: middle;">visibility</i>
                                                                        View Details
                                                                    </button>
                                                                    <button type="button" class="btn btn-outline-info btn-sm" onclick="viewEvidence({{ $inquiry->id }})">
                                                                        <i class="material-icons" style="font-size: 16px; vertical-align: middle;">attach_file</i>
                                                                        Evidence
                                                                    </button>
                                                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="viewHistory({{ $inquiry->id }})">
                                                                        <i class="material-icons" style="font-size: 16px; vertical-align: middle;">history</i>
                                                                        Status History
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                        @endforeach
                                    </div>

                                    <!-- Pagination if needed -->
                                    @if($inquiries->count() >= 10)
                                        <div class="d-flex justify-content-center mt-4">
                                            <nav aria-label="Inquiries pagination">
                                                <ul class="pagination">
                                                    <li class="page-item disabled">
                                                        <span class="page-link">Previous</span>
                                                    </li>
                                                    <li class="page-item active">
                                                        <span class="page-link">1</span>
                                                    </li>
                                                    <li class="page-item disabled">
                                                        <span class="page-link">Next</span>
                                                    </li>
                                                </ul>
                                            </nav>
                                        </div>
                                    @endif

                                @else
                                    <!-- Empty State -->
                                    <div class="text-center py-5">
                                        <div class="empty-state">
                                            <i class="material-icons text-muted" style="font-size: 80px;">inbox</i>                            <h4 class="text-muted mt-3">No Inquiries Yet</h4>
                            <p class="text-muted mb-4">You haven't submitted any news verification inquiries yet.</p>
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

    <style>
        /* Ensure proper spacing for sidebar layout */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .main-content {
            margin-left: 250px; /* Account for sidebar width */
            padding-top: 70px;  /* Account for top bar height */
            min-height: 100vh;
        }
        
        .content {
            padding: 30px;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            border-radius: 10px 10px 0 0 !important;
            padding: 1.5rem;
        }
        
        .border-left-primary {
            border-left: 4px solid #007bff !important;
        }
        
        .badge {
            font-size: 0.875rem;
            border-radius: 6px;
        }
        
        .btn-group .btn {
            flex: 1;
        }
        
        .empty-state {
            max-width: 400px;
            margin: 0 auto;
        }
        
        .material-icons {
            font-size: 24px;
        }
        
        /* Status-specific styling */
        .status-pending { border-left-color: #ffc107 !important; }
        .status-in-progress { border-left-color: #17a2b8 !important; }
        .status-completed { border-left-color: #28a745 !important; }
        .status-rejected { border-left-color: #dc3545 !important; }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding-top: 60px;
            }
            
            .content {
                padding: 15px;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            .btn-group .btn {
                margin-bottom: 0.25rem;
            }
        }    </style>
    
    <!-- Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailsModalLabel">
                        <i class="material-icons" style="vertical-align: middle; margin-right: 8px;">visibility</i>
                        Inquiry Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="detailsModalBody">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Evidence Modal -->
    <div class="modal fade" id="evidenceModal" tabindex="-1" aria-labelledby="evidenceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="evidenceModalLabel">
                        <i class="material-icons" style="vertical-align: middle; margin-right: 8px;">attach_file</i>
                        Evidence Files
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="evidenceModalBody">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Status History Modal -->
    <div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="historyModalLabel">
                        <i class="material-icons" style="vertical-align: middle; margin-right: 8px;">history</i>
                        Status History
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="historyModalBody">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>    <script>
        // Store inquiry data for modals - ensure we get the database data
        @if(isset($inquiriesForJs))
            const inquiries = @json($inquiriesForJs);
        @else
            // Fallback: convert Laravel collection to array
            const inquiries = @json($inquiries->toArray());
        @endif
        
        console.log('Loaded inquiries from database:', inquiries);
        console.log('Number of inquiries:', inquiries.length);
        
        // Debug: Check status history data
        if (inquiries.length > 0) {
            inquiries.forEach((inquiry, index) => {
                console.log(`Inquiry ${index + 1} (ID: ${inquiry.id}) status history:`, inquiry.status_history);
            });
        }
        
        function viewDetails(inquiryId) {
            console.log('ViewDetails called with ID:', inquiryId);
            const inquiry = inquiries.find(i => i.id == inquiryId);
            console.log('Found inquiry for details:', inquiry);
            
            if (!inquiry) {
                console.error('Inquiry not found for ID:', inquiryId);
                alert('Inquiry not found. ID: ' + inquiryId);
                return;
            }
            
            const modalBody = document.getElementById('detailsModalBody');
            modalBody.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary">Basic Information</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted">Inquiry ID:</td>
                                <td><strong>#${String(inquiry.id).padStart(4, '0')}</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Status:</td>
                                <td><span class="badge bg-${getStatusColor(inquiry.status)}">${inquiry.status.replace('_', ' ').toUpperCase()}</span></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Submitted:</td>
                                <td>${new Date(inquiry.submission_date).toLocaleDateString('en-US', {
                                    year: 'numeric', month: 'short', day: 'numeric',
                                    hour: '2-digit', minute: '2-digit'
                                })}</td>
                            </tr>                            ${inquiry.result ? `
                            <tr>
                                <td class="text-muted">Verification Result:</td>
                                <td><span class="badge bg-${inquiry.result === 'true' ? 'success' : 'danger'}">${inquiry.result === 'true' ? 'TRUE' : 'FAKE'}</span></td>
                            </tr>
                            ` : inquiry.status === 'rejected' ? `
                            <tr>
                                <td class="text-muted">Verification Result:</td>
                                <td><span class="badge bg-warning text-dark">REJECTED</span></td>
                            </tr>
                            ` : ''}
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary">Content Details</h6>
                        <div class="bg-light p-3 rounded">
                            <h6>${inquiry.title}</h6>
                            <p class="text-muted mb-0">${inquiry.description}</p>
                        </div>
                    </div>
                </div>
                ${inquiry.admin_response ? `
                <div class="row mt-4">
                    <div class="col-12">
                        <h6 class="text-primary">Official Response</h6>
                        <div class="alert alert-${inquiry.status === 'rejected' ? 'warning' : 'info'} mb-0">
                            <i class="material-icons" style="vertical-align: middle; margin-right: 8px;">admin_panel_settings</i>
                            ${inquiry.admin_response}
                        </div>
                    </div>
                </div>
                ` : ''}
            `;
            
            new bootstrap.Modal(document.getElementById('detailsModal')).show();
        }
          function viewEvidence(inquiryId) {
            console.log('ViewEvidence called with ID:', inquiryId);
            const inquiry = inquiries.find(i => i.id == inquiryId);
            console.log('Found inquiry for evidence:', inquiry);
            
            if (!inquiry) {
                console.error('Inquiry not found for ID:', inquiryId);
                alert('Inquiry not found. ID: ' + inquiryId);
                return;
            }
            
            const modalBody = document.getElementById('evidenceModalBody');
            
            // Check if evidence files exist and have content
            if (!inquiry.evidence_files || inquiry.evidence_files.length === 0) {
                let noEvidenceHtml = `
                    <div class="text-center py-5">
                        <i class="material-icons text-muted" style="font-size: 64px;">attach_file</i>
                        <h5 class="mt-3 text-muted">No Evidence Files</h5>
                        <p class="text-muted">No evidence files were submitted for this inquiry.</p>
                `;
                
                // Check if there's an evidence URL
                if (inquiry.evidence_url && inquiry.evidence_url.trim()) {
                    noEvidenceHtml += `
                        <div class="mt-4">
                            <h6 class="text-primary mb-3">
                                <i class="material-icons me-2" style="vertical-align: middle;">link</i>
                                Evidence Link Available
                            </h6>
                            <div class="card border-primary">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="material-icons text-primary me-3" style="font-size: 24px;">language</i>
                                        <div class="flex-grow-1">
                                            <a href="${inquiry.evidence_url}" target="_blank" class="text-decoration-none">
                                                <strong>${inquiry.evidence_url}</strong>
                                            </a>
                                            <br>
                                            <small class="text-muted">Original news source link</small>
                                        </div>
                                        <a href="${inquiry.evidence_url}" target="_blank" class="btn btn-outline-primary btn-sm">
                                            <i class="material-icons me-1" style="font-size: 16px;">open_in_new</i>
                                            Open Link
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }
                
                noEvidenceHtml += `</div>`;
                modalBody.innerHTML = noEvidenceHtml;
                new bootstrap.Modal(document.getElementById('evidenceModal')).show();
                return;
            }
            
            let evidenceHtml = `
                <div class="text-center py-3 mb-4">
                    <i class="material-icons text-primary" style="font-size: 48px;">attach_file</i>
                    <h6 class="mt-3 text-primary">Evidence Files</h6>
                    <p class="text-muted">The following files were submitted as evidence for inquiry #${String(inquiryId).padStart(4, '0')}:</p>
                </div>
                <div class="row">
            `;
            
            // Process each evidence file from the database
            inquiry.evidence_files.forEach((file, index) => {
                console.log('Processing file:', file);
                
                // Handle different file type formats
                let fileType = 'document';
                if (file.type) {
                    fileType = file.type.toLowerCase();
                } else if (file.name) {
                    const extension = file.name.split('.').pop().toLowerCase();
                    if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(extension)) {
                        fileType = 'image';
                    } else if (extension === 'pdf') {
                        fileType = 'pdf';
                    } else if (['mp4', 'avi', 'mov', 'wmv', 'flv'].includes(extension)) {
                        fileType = 'video';
                    } else if (['mp3', 'wav', 'aac', 'flac'].includes(extension)) {
                        fileType = 'audio';
                    }
                }
                
                const iconClass = fileType === 'image' ? 'image' : 
                                 fileType === 'pdf' ? 'picture_as_pdf' : 
                                 fileType === 'video' ? 'videocam' :
                                 fileType === 'audio' ? 'audiotrack' : 'description';
                const iconColor = fileType === 'image' ? 'text-primary' : 
                                 fileType === 'pdf' ? 'text-danger' : 
                                 fileType === 'video' ? 'text-success' :
                                 fileType === 'audio' ? 'text-warning' : 'text-info';
                
                // Create URL for viewing the evidence file
                const fileUrl = `/inquiry/${inquiryId}/evidence/${encodeURIComponent(file.name)}`;
                
                evidenceHtml += `
                    <div class="col-md-6 mb-3">
                        <div class="card border shadow-sm" style="cursor: pointer;" onclick="viewEvidenceFile('${fileUrl}', '${file.name}', '${fileType}')">
                            <div class="card-body d-flex align-items-center">
                                <i class="material-icons ${iconColor} me-3" style="font-size: 32px;">${iconClass}</i>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">${file.name || 'Unknown file'}</h6>
                                    <small class="text-muted">${fileType.toUpperCase()} • ${file.size || 'Unknown size'}</small>
                                    <br>
                                    <small class="text-primary">
                                        <i class="material-icons" style="font-size: 12px; vertical-align: middle;">visibility</i>
                                        Click to view file
                                    </small>
                                </div>
                                <div class="dropdown" onclick="event.stopPropagation();">
                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="material-icons" style="font-size: 16px;">more_vert</i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="${fileUrl}" target="_blank">
                                            <i class="material-icons me-2" style="font-size: 16px;">visibility</i>View
                                        </a></li>
                                        <li><a class="dropdown-item" href="${fileUrl}" download>
                                            <i class="material-icons me-2" style="font-size: 16px;">download</i>Download
                                        </a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            evidenceHtml += `</div>`;
            
            // Add evidence URL section if it exists
            if (inquiry.evidence_url && inquiry.evidence_url.trim()) {
                evidenceHtml += `
                    <div class="mt-4">
                        <h6 class="text-primary mb-3">
                            <i class="material-icons me-2" style="vertical-align: middle;">link</i>
                            Evidence Link
                        </h6>
                        <div class="card border-primary">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <i class="material-icons text-primary me-3" style="font-size: 24px;">language</i>
                                    <div class="flex-grow-1">
                                        <a href="${inquiry.evidence_url}" target="_blank" class="text-decoration-none">
                                            <strong>${inquiry.evidence_url}</strong>
                                        </a>
                                        <br>
                                        <small class="text-muted">Original news source link</small>
                                    </div>
                                    <a href="${inquiry.evidence_url}" target="_blank" class="btn btn-outline-primary btn-sm">
                                        <i class="material-icons me-1" style="font-size: 16px;">open_in_new</i>
                                        Open Link
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }            
            evidenceHtml += `
            `;
            
            modalBody.innerHTML = evidenceHtml;
            new bootstrap.Modal(document.getElementById('evidenceModal')).show();
        }
        
        // Function to handle evidence file viewing
        function viewEvidenceFile(fileUrl, fileName, fileType) {
            console.log('Opening evidence file:', fileName, 'Type:', fileType, 'URL:', fileUrl);
            
            // For images, try to show preview in modal
            if (fileType === 'image') {
                showImagePreview(fileUrl, fileName);
            } else if (fileType === 'pdf') {
                // For PDFs, show in a dedicated viewer modal
                showPdfPreview(fileUrl, fileName);
            } else {}{
                // For other files, open in new tab
                window.open(fileUrl, '_blank');
            }

        }
        
        // Function to show image preview
        function showImagePreview(imageUrl, fileName) {
            const imageModal = `
                <div class="modal fade" id="imagePreviewModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class="material-icons me-2">image</i>${fileName}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-center">
                                <img src="${imageUrl}" class="img-fluid" alt="${fileName}" style="max-height: 70vh; max-width: 100%;">
                            </div>
                            <div class="modal-footer">
                                <a href="${imageUrl}" download class="btn btn-primary">
                                    <i class="material-icons me-2">download</i>Download
                                </a>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Remove existing modal if present
            const existingModal = document.getElementById('imagePreviewModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            // Add modal to body and show
            document.body.insertAdjacentHTML('beforeend', imageModal);
            new bootstrap.Modal(document.getElementById('imagePreviewModal')).show();
        }          function viewHistory(inquiryId) {
            console.log('ViewHistory called with ID:', inquiryId);
            const inquiry = inquiries.find(i => i.id == inquiryId);
            console.log('Found inquiry for history:', inquiry);
            
            if (!inquiry) {
                console.error('Inquiry not found for ID:', inquiryId);
                alert('Inquiry not found. ID: ' + inquiryId);
                return;
            }
            
            const modalBody = document.getElementById('historyModalBody');
            
            // Create logical status history based on final result
            let logicalHistory = [];
            
            // Always start with submission
            logicalHistory.push({
                status: 'submitted',
                title: 'Inquiry Submitted',
                description: 'Your inquiry has been received and is in the queue for verification.',
                date: inquiry.created_at || inquiry.submission_date,
                color: 'bg-primary'
            });
            
            // Add review started step
            logicalHistory.push({
                status: 'review_started',
                title: 'Review Started',
                description: 'Our verification team has begun analyzing your inquiry and evidence.',
                date: inquiry.updated_at || inquiry.created_at,
                color: 'bg-info'
            });
            
            // Add investigation step
            logicalHistory.push({
                status: 'under_investigation',
                title: 'Under Investigation',
                description: 'The news claim is being thoroughly investigated using fact-checking methods.',
                date: inquiry.updated_at || inquiry.created_at,
                color: 'bg-warning'
            });
            
            // Add final result based on inquiry status and result
            if (inquiry.status === 'rejected') {
                logicalHistory.push({
                    status: 'rejected',
                    title: 'Inquiry Rejected',
                    description: inquiry.admin_response || 'Your inquiry was rejected due to insufficient evidence or policy violations.',
                    date: inquiry.completion_date || inquiry.updated_at,
                    color: 'bg-danger'
                });
            } else if (inquiry.status === 'completed' && inquiry.result) {
                if (inquiry.result === 'true') {
                    logicalHistory.push({
                        status: 'completed_true',
                        title: 'Verified as TRUE',
                        description: inquiry.admin_response || 'The news claim has been verified as factually correct.',
                        date: inquiry.completion_date || inquiry.updated_at,
                        color: 'bg-success'
                    });
                } else if (inquiry.result === 'false') {
                    logicalHistory.push({
                        status: 'completed_false',
                        title: 'Verified as FAKE',
                        description: inquiry.admin_response || 'The news claim has been verified as false or misleading.',
                        date: inquiry.completion_date || inquiry.updated_at,
                        color: 'bg-danger'
                    });
                }
            }
            
            let historyHtml = '<div class="timeline">';
            
            // Process each logical history step
            logicalHistory.forEach((historyItem, index) => {
                // Format the date
                let formattedDate = 'Unknown Date';
                if (historyItem.date) {
                    try {
                        formattedDate = new Date(historyItem.date).toLocaleDateString('en-US', {
                            year: 'numeric', 
                            month: 'short', 
                            day: 'numeric', 
                            hour: '2-digit', 
                            minute: '2-digit'
                        });
                    } catch (e) {
                        console.error('Error formatting date:', historyItem.date, e);
                        formattedDate = historyItem.date; // Use as-is if formatting fails
                    }
                }
                
                historyHtml += `
                    <div class="timeline-item">
                        <div class="timeline-marker ${historyItem.color}"></div>
                        <div class="timeline-content">
                            <h6>${historyItem.title}</h6>
                            <p class="text-muted mb-1">${historyItem.description}</p>
                            <small class="text-muted">${formattedDate}</small>
                        </div>
                    </div>
                `;            });
            
            historyHtml += '</div>';
            
            modalBody.innerHTML = historyHtml + `
                <style>
                    .timeline { position: relative; padding-left: 30px; }
                    .timeline-item { position: relative; margin-bottom: 20px; }
                    .timeline-marker { 
                        position: absolute; left: -38px; top: 5px; 
                        width: 16px; height: 16px; border-radius: 50%; 
                    }
                    .timeline-item:not(:last-child):before {
                        content: ''; position: absolute; left: -31px; top: 21px; 
                        width: 2px; height: 100%; background: #dee2e6;
                    }
                    .timeline-content h6 { margin-bottom: 8px; }
                </style>
            `;
            
            new bootstrap.Modal(document.getElementById('historyModal')).show();
        }
          function getStatusColor(status) {
            if (status === 'rejected') return 'warning';
            return 'secondary';
        }
        
        // Track user activity for auto-refresh
        document.addEventListener('click', function() {
            localStorage.setItem('lastActivity', Date.now());
        });
    </script>
</body>
</html>
