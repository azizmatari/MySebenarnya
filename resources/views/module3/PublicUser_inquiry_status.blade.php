<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Inquiries - Status Tracking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
            border-left: 4px solid #4f46e5;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: box-shadow 0.2s ease;
            width: 100%;
        }

        .inquiry-card:first-child {
            margin-top: 50px;
        }

        .inquiry-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .inquiry-header {
            background: #f8f9fa;
            padding: 20px 25px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .inquiry-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }

        .inquiry-body {
            padding: 25px;
        }

        .inquiry-details {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 25px;
            margin-bottom: 20px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .detail-value {
            font-size: 0.95rem;
            color: #2c3e50;
            line-height: 1.4;
        }

        .description-section {
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
            background: linear-gradient(45deg, #ffc107, #ffeb3b);
            color: #333;
            font-weight: bold;
            border-radius: 12px;
            padding: 6px 12px;
            font-size: 0.8rem;
        }

        .status-badge.pending {
            background: linear-gradient(45deg, #ffc107, #ffeb3b);
        }

        .status-badge.under-investigation {
            background: linear-gradient(45deg, #17a2b8, #20c997);
            color: white;
        }

        .add-inquiry-btn {
            position: absolute;
            right: 20px;
            top: -58px;
            z-index: 10;
            background-color: #007bff;
            color: white;
            border: 1px solid #007bff;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .add-inquiry-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 123, 255, 0.4);
            color: white;
            background: linear-gradient(45deg, #0056b3, #004085);
        }

        .page-header {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }

        .page-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }

        .inquiries-wrapper {
            position: relative;
            margin-top: 70px;
        }

        .agency-tag {
            background: #e3f2fd;
            color: #1976d2;
            border-radius: 15px;
            padding: 3px 10px;
            font-size: 0.85em;
            display: inline-block;
        }

        .header-section {
            display: none;
        }

        .inquiry-counter {
            display: none;
        }

        .content-wrapper {
            margin-top: 70px;
        }

        .card-header {
            background: white !important;
            border-bottom: 1px solid #dee2e6;
        }

        .card-footer {
            background: #f8f9fa !important;
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
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }

            .page-header {
                margin-bottom: 20px;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .add-inquiry-btn {
                position: static;
                width: 100%;
                justify-content: center;
                margin-bottom: 20px;
            }

            .inquiries-wrapper {
                margin-top: 20px;
            }

            .inquiry-card:first-child {
                margin-top: 0;
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
        }

        @media (max-width: 992px) {
            .inquiry-details {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>

<body> <!-- Include Sidebar -->
    @include('layouts.sidebarPublic')

    <!-- Main Content -->
    <div class="main-content">
        <div class="content-wrapper">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-list-alt me-3"></i>Active Inquiries
                </h1>
            </div>

            <!-- Inquiries Wrapper with Add Button -->
            <div class="inquiries-wrapper">
                <a href="{{ route('inquiry.create') }}" class="add-inquiry-btn">
                    <i class="fas fa-plus"></i>
                    Add New Inquiry
                </a>

                <!-- Content Container -->
                <div class="container-fluid" id="inquiry-container">
                    <div class="loading-spinner">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p>Loading inquiries...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for viewing full inquiry details -->
    <div class="modal fade" id="inquiryModal" tabindex="-1" aria-labelledby="inquiryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="inquiryModalLabel">
                        <i class="fas fa-file-alt me-2"></i>Inquiry Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalBody">
                    <!-- Content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Application configuration
        const API_ENDPOINTS = {
            inquiries: '/module3/status/get-inquiries',
            statistics: '/module3/status/statistics'
        };

        // Global variable to store current inquiries for modal access
        let currentInquiries = [];

        // Load inquiries when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadInquiries();
        });

        // Function to load inquiries via AJAX
        async function loadInquiries() {
            try {
                showLoading();
                console.log('Loading inquiries from:', API_ENDPOINTS.inquiries);
                const response = await fetch(API_ENDPOINTS.inquiries);

                console.log('Response status:', response.status);
                console.log('Response ok:', response.ok);

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                console.log('Received data:', data);
                displayInquiries(data.inquiries || data);
            } catch (error) {
                console.error('Error loading inquiries:', error);
                showError('Failed to load inquiries. Please try again later.');
            }
        } // Function to display inquiries
        function displayInquiries(inquiries) {
            const container = document.getElementById('inquiry-container');

            // Store inquiries globally for modal access
            currentInquiries = inquiries;

            if (inquiries.length === 0) {
                showNoInquiries();
                return;
            }

            let html = '';
            inquiries.forEach(inquiry => {
                html += createInquiryCard(inquiry);
            });

            container.innerHTML = html;
        } // Function to create inquiry card HTML
        function createInquiryCard(inquiry) {
            const statusClass = inquiry.final_status.toLowerCase().replace(' ', '-');

            // Handle evidence section
            const template = document.createElement('div');
            template.innerHTML = `
                <div class="inquiry-card">
                    <div class="inquiry-header">
                        <h5 class="inquiry-title">
                            <i class="fas fa-file-alt me-2"></i>
                            ${escapeHtml(inquiry.title)}
                        </h5>
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge status-badge ${statusClass}">
                                <i class="fas fa-clock me-1"></i>
                                ${escapeHtml(inquiry.final_status)}
                            </span>
                            <button class="btn btn-outline-primary btn-sm" onclick="viewDetails(${inquiry.inquiryId})">
                                <i class="fas fa-eye me-1"></i>View
                            </button>
                        </div>
                    </div>
                    
                    <div class="inquiry-body">
                        <div class="inquiry-details">
                            <div class="detail-item">
                                <div class="detail-label">Applied Date:</div>
                                <div class="detail-value">
                                    <i class="fas fa-calendar me-1"></i>
                                    ${formatDate(inquiry.submission_date)}
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label">Assigned Agency:</div>
                                <div class="detail-value">
                                    <i class="fas fa-building me-1"></i>
                                    <span class="agency-tag">${escapeHtml(inquiry.agency_name)}</span>
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label">Assignment Date:</div>
                                <div class="detail-value">
                                    <i class="fas fa-calendar-check me-1"></i>
                                    ${formatDate(inquiry.assignment_date || inquiry.submission_date)}
                                </div>
                            </div>
                        </div>
                        
                        <div class="description-section">
                            <div class="detail-label">Description:</div>
                            <div class="detail-value">
                                <span class="description-text">${truncateText(escapeHtml(inquiry.description), 50)}</span>
                            </div>
                        </div>
                        
                        <div class="evidence-section">
                            <div class="detail-label">Evidence:</div>
                            <div class="evidence-content">
                                <div class="detail-value text-muted">
                                    <i class="fas fa-times-circle me-1"></i>N/A
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;            // Handle evidence section
            const evidenceContent = template.querySelector('.evidence-content');
            let evidenceHtml = '';

            // Create a container for evidence items to display side by side
            const hasEvidenceUrl = inquiry.evidence_url;
            const hasEvidenceFile = inquiry.evidence_file_url;

            if (hasEvidenceUrl || hasEvidenceFile) {
                evidenceHtml = '<div class="evidence-items-container d-flex gap-4">';
                
                // Check for evidence URL (link)
                if (hasEvidenceUrl) {
                    evidenceHtml += `
                        <div class="evidence-item">
                            <strong>Evidence Link:</strong><br>
                            <a href="${escapeHtml(inquiry.evidence_url)}" target="_blank" class="evidence-link">
                                <i class="fas fa-external-link-alt me-1"></i>View Evidence Link
                            </a>
                        </div>
                    `;
                }
                
                // Check for evidence file URL (image/file)
                if (hasEvidenceFile) {
                    evidenceHtml += `
                        <div class="evidence-item">
                            <strong>Evidence File:</strong><br>
                            <a href="${escapeHtml(inquiry.evidence_file_url)}" target="_blank" class="evidence-link">
                                <i class="fas fa-file-image me-1"></i>View Evidence File
                            </a>
                        </div>
                    `;
                }
                
                evidenceHtml += '</div>';
            } else {
                // If no evidence, show N/A
                evidenceHtml = `
                    <div class="detail-value text-muted">
                        <i class="fas fa-times-circle me-1"></i>N/A
                    </div>
                `;
            }

            evidenceContent.innerHTML = evidenceHtml;

            return template.innerHTML;
        }

        // Function to show loading state
        function showLoading() {
            const container = document.getElementById('inquiry-container');
            container.innerHTML = `
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p>Loading inquiries...</p>
                </div>
            `;
        } // Function to show error message
        function showError(message) {
            const container = document.getElementById('inquiry-container');
            container.innerHTML = `
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ${escapeHtml(message)}
                    <button class="btn btn-sm btn-outline-danger ms-2" onclick="loadInquiries()">
                        <i class="fas fa-redo me-1"></i>Retry
                    </button>
                </div>
            `;
        }

        // Function to show no inquiries message
        function showNoInquiries() {
            const container = document.getElementById('inquiry-container');
            container.innerHTML = `
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No Active Inquiries</h4>
                    <p class="text-muted">All inquiries have been processed or there are no new inquiries to display.</p>
                </div>
            `;
        }

        // Helper functions
        function truncateText(text, length) {
            if (!text) return '';
            return text.length > length ? text.substring(0, length) + '...' : text;
        }

        function formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function viewDetails(inquiryId) {
            // Find the inquiry data from the loaded inquiries
            const inquiry = currentInquiries.find(inq => inq.inquiryId == inquiryId);

            if (!inquiry) {
                alert('Inquiry details not found.');
                return;
            }

            // Populate modal with full inquiry details
            const modalBody = document.getElementById('modalBody');
            modalBody.innerHTML = `
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Inquiry ID:</strong><br>
                        <span class="text-muted">#${inquiry.inquiryId}</span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Status:</strong><br>
                        <span class="badge status-badge ${inquiry.final_status.toLowerCase().replace(' ', '-')}">${escapeHtml(inquiry.final_status)}</span>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <strong>Applied Date:</strong><br>
                        <span class="text-muted"><i class="fas fa-calendar me-1"></i>${formatDate(inquiry.submission_date)}</span>
                    </div>
                    <div class="col-md-4 mb-3">
                        <strong>Assigned Agency:</strong><br>
                        <span class="agency-tag">${escapeHtml(inquiry.agency_name)}</span>
                    </div>
                    <div class="col-md-4 mb-3">
                        <strong>Assignment Date:</strong><br>
                        <span class="text-muted"><i class="fas fa-calendar-check me-1"></i>${formatDate(inquiry.assignment_date || inquiry.submission_date)}</span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <strong>Title:</strong><br>
                    <span class="text-muted">${escapeHtml(inquiry.title)}</span>
                </div>
                
                <div class="mb-3">
                    <strong>Full Description:</strong><br>
                    <div class="p-3 bg-light rounded">
                        ${escapeHtml(inquiry.description)}
                    </div>
                </div>
                
                <div class="mb-3">
                    <strong>Evidence:</strong><br>
                    <div id="modalEvidenceSection">
                        <!-- Evidence content will be populated here -->
                    </div>
                </div>
            `;            // Handle evidence section in modal
            const modalEvidenceSection = document.getElementById('modalEvidenceSection');
            let evidenceHtml = '';

            // Create a container for evidence items to display side by side
            const hasEvidenceUrl = inquiry.evidence_url;
            const hasEvidenceFile = inquiry.evidence_file_url;

            if (hasEvidenceUrl || hasEvidenceFile) {
                evidenceHtml = '<div class="evidence-items-container d-flex gap-4">';
                
                // Check for evidence URL (link)
                if (hasEvidenceUrl) {
                    evidenceHtml += `
                        <div class="evidence-item">
                            <strong>Evidence Link:</strong><br>
                            <a href="${escapeHtml(inquiry.evidence_url)}" target="_blank" class="evidence-link">
                                <i class="fas fa-external-link-alt me-1"></i>View Evidence Link
                            </a>
                        </div>
                    `;
                }
                
                // Check for evidence file URL (image/file)
                if (hasEvidenceFile) {
                    evidenceHtml += `
                        <div class="evidence-item">
                            <strong>Evidence File:</strong><br>
                            <a href="${escapeHtml(inquiry.evidence_file_url)}" target="_blank" class="evidence-link">
                                <i class="fas fa-file-image me-1"></i>View Evidence File
                            </a>
                        </div>
                    `;
                }
                
                evidenceHtml += '</div>';
            } else {
                // If no evidence, show N/A
                evidenceHtml = `
                    <div class="text-muted">
                        <i class="fas fa-times-circle me-1"></i>No evidence provided
                    </div>
                `;
            }

            modalEvidenceSection.innerHTML = evidenceHtml;

            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('inquiryModal'));
            modal.show();
        }

        function addNewInquiry() {
            // TODO: Implement add new inquiry functionality
            alert('Add New Inquiry functionality will be implemented here');
        }
    </script>
</body>

</html>