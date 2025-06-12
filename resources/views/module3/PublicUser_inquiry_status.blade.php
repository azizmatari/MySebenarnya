<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Inquiries - Status Tracking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
            overflow-y: auto;
        }

        .main-content {
            margin-left: 260px;
            margin-top: 50px;
            padding: 20px;
            min-height: 100vh;
        }

        .inquiry-card {
            background: white;
            border: 1px solid #e1e5e9;
            border-left: 4px solid #6366f1;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: all 0.2s ease;
            overflow: hidden;
        }

        .inquiry-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateY(-1px);
        }

        .inquiry-header {
            background: #f8fafc;
            padding: 16px 24px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .inquiry-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .inquiry-body {
            padding: 24px;
        }

        .inquiry-details {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 24px;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .inquiry-details {
                grid-template-columns: 1fr;
                gap: 16px;
            }
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .detail-value {
            font-size: 0.875rem;
            color: #334155;
            line-height: 1.5;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .description-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }

        .evidence-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }

        .evidence-link {
            color: #6366f1;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .evidence-link:hover {
            color: #4f46e5;
            text-decoration: underline;
        }

        .status-badge {
            background-color: #10b981;
            color: white;
            font-weight: 500;
            border-radius: 6px;
            padding: 4px 12px;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .status-badge.pending {
            background-color: #f59e0b;
        }

        .status-badge.under-investigation {
            background-color: #10b981;
        }

        .add-inquiry-btn {
            margin-top: 80px;
            margin-right: 40px;
            background-color: #007bff;
            color: white;
            border: 1px solid #007bff;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            position: fixed;
            top: 20px;
            right: 30px;
            z-index: 1000;
        }

        .add-inquiry-btn:hover {
            background-color: #0056b3;
            color: white;
            text-decoration: none;
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

        .inquiry-actions {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            text-align: right;
        }

        .view-btn {
            background-color: #007bff;
            border-color: #007bff;
            color: white;
            font-size: 0.875rem;
            padding: 6px 12px;
            border-radius: 4px;
        }

        .view-btn:hover {
            background-color: #0056b3;
            border-color: #0056b3;
            color: white;
        }

        .description-truncated {
            display: inline;
        }

        .description-full {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            border-left: 4px solid #007bff;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .modal .detail-group {
            margin-bottom: 15px;
        }

        .modal .detail-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }

        .modal .detail-content {
            color: #6c757d;
            font-size: 0.95rem;
        }

        .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        .modal-title {
            color: #495057;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <!-- Include Sidebar -->
    @include('layouts.sidebarPublic')
    <!-- Add New Inquiry Button -->
    <a href="{{ route('inquiry.create') }}" class="btn btn-primary">
    <i class="material-icons" style="vertical-align: middle; margin-right: 8px;">add_circle</i>
    Submit New Inquiry
    </a>

    <!-- Main Content -->
    <div class="main-content">
        <div class="content-wrapper">
            <!-- Content Container -->
            <div class="container-fluid" id="inquiry-container">
                <div class="loading-spinner" id="loading-spinner">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p>Loading inquiries...</p>
                </div>

                <!-- Error Message Template -->
                <div class="error-message" id="error-message" style="display: none;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <span id="error-text"></span>
                    <button class="btn btn-sm btn-outline-danger ms-2" onclick="loadInquiries()">
                        <i class="fas fa-redo me-1"></i>Retry
                    </button>
                </div>

                <!-- No Inquiries Template -->
                <div class="text-center py-5" id="no-inquiries" style="display: none;">
                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No Active Inquiries</h4>
                    <p class="text-muted">All inquiries have been processed or there are no new inquiries to display.</p>
                </div>
                <!-- Inquiries List Container -->
                <div id="inquiries-list"></div>
            </div>
        </div>
    </div>

    <!-- Inquiry Card Template (Hidden) -->
    <template id="inquiry-card-template">
        <div class="inquiry-card">
            <div class="inquiry-header">
                <h5 class="inquiry-title">
                    <i class="fas fa-file-alt me-2"></i>
                    <span class="title-text"></span>
                </h5>
                <span class="badge status-badge">
                    <i class="fas fa-clock me-1"></i>
                    <span class="status-text"></span>
                </span>
            </div>

            <div class="inquiry-body">
                <div class="inquiry-details">
                    <div class="detail-item">
                        <div class="detail-label">Applied Date:</div>
                        <div class="detail-value">
                            <i class="fas fa-calendar me-1"></i>
                            <span class="applied-date"></span>
                        </div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">Assigned Agency:</div>
                        <div class="detail-value">
                            <i class="fas fa-building me-1"></i>
                            <span class="agency-name"></span>
                        </div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">Assignment Date:</div>
                        <div class="detail-value">
                            <i class="fas fa-calendar-check me-1"></i>
                            <span class="assignment-date"></span>
                        </div>
                    </div>
                </div>

                <div class="description-section">
                    <div class="detail-label">Description:</div>
                    <div class="detail-value">
                        <span class="description-text"></span>
                    </div>
                </div>
                <div class="evidence-section">
                    <div class="detail-label">Evidence:</div>
                    <div class="evidence-content">
                        <!-- Evidence link or N/A will be inserted here -->
                    </div>
                </div>

                <div class="inquiry-actions">
                    <button type="button" class="btn btn-primary btn-sm view-btn" onclick="viewInquiryDetails(this)">
                        <i class="fas fa-eye me-1"></i>View Details
                    </button>
                </div>
            </div>
        </div>
    </template>

    <!-- Modal for Inquiry Details -->
    <div class="modal fade" id="inquiryModal" tabindex="-1" aria-labelledby="inquiryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="inquiryModalLabel">
                        <i class="fas fa-file-alt me-2"></i>Inquiry Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-group">
                                <label class="detail-label">Title:</label>
                                <div class="detail-content" id="modal-title"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-group">
                                <label class="detail-label">Status:</label>
                                <div class="detail-content">
                                    <span class="badge" id="modal-status"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="detail-group">
                                <label class="detail-label">Applied Date:</label>
                                <div class="detail-content" id="modal-applied-date"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-group">
                                <label class="detail-label">Assignment Date:</label>
                                <div class="detail-content" id="modal-assignment-date"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="detail-group">
                                <label class="detail-label">Assigned Agency:</label>
                                <div class="detail-content" id="modal-agency"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-group">
                                <label class="detail-label">Applicant:</label>
                                <div class="detail-content" id="modal-applicant"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="detail-group">
                                <label class="detail-label">Description:</label>
                                <div class="detail-content description-full" id="modal-description"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="detail-group">
                                <label class="detail-label">Evidence:</label>
                                <div class="detail-content" id="modal-evidence"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Application configuration
        const API_ENDPOINTS = {
            inquiries: '/module3/status/get-inquiries',
            statistics: '/module3/status/statistics'
        };

        // DOM elements
        const elements = {
            container: document.getElementById('inquiry-container'),
            loadingSpinner: document.getElementById('loading-spinner'),
            errorMessage: document.getElementById('error-message'),
            errorText: document.getElementById('error-text'),
            noInquiries: document.getElementById('no-inquiries'),
            inquiriesList: document.getElementById('inquiries-list'),
            template: document.getElementById('inquiry-card-template')
        };

        // Load inquiries when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadInquiries();
        });

        // Function to load inquiries via AJAX
        async function loadInquiries() {
            try {
                showLoading();
                const response = await fetch(API_ENDPOINTS.inquiries);

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                displayInquiries(data.inquiries || data);
            } catch (error) {
                console.error('Error loading inquiries:', error);
                showError('Failed to load inquiries. Please try again later.');
            }
        }

        // Function to display inquiries
        function displayInquiries(inquiries) {
            hideAllViews();

            if (inquiries.length === 0) {
                showNoInquiries();
                return;
            }

            // Clear previous inquiries
            elements.inquiriesList.innerHTML = '';

            // Create inquiry cards
            inquiries.forEach(inquiry => {
                const card = createInquiryCard(inquiry);
                elements.inquiriesList.appendChild(card);
            });

            // Show inquiries list
            elements.inquiriesList.style.display = 'block';
        } // Function to create inquiry card from template
        function createInquiryCard(inquiry) {
            // Clone the template
            const template = elements.template.content.cloneNode(true);

            // Get the card element
            const card = template.querySelector('.inquiry-card');

            // Store inquiry data on the card element for modal use
            card.setAttribute('data-inquiry', JSON.stringify(inquiry));

            // Populate title
            template.querySelector('.title-text').textContent = inquiry.title;

            // Populate and style status badge
            const statusBadge = template.querySelector('.status-badge');
            const statusText = template.querySelector('.status-text');
            statusText.textContent = inquiry.final_status;

            // Add status class for styling
            const statusClass = inquiry.final_status.toLowerCase().replace(' ', '-');
            statusBadge.classList.add(statusClass);

            // Populate dates and agency
            template.querySelector('.applied-date').textContent = formatDate(inquiry.submission_date);
            template.querySelector('.agency-name').textContent = inquiry.agency_name;
            template.querySelector('.assignment-date').textContent = formatDate(inquiry.assignment_date || inquiry.submission_date);

            // Populate description with truncation
            const descriptionElement = template.querySelector('.description-text');
            const fullDescription = inquiry.description || '';
            const truncatedDescription = truncateText(fullDescription, 50);
            descriptionElement.textContent = truncatedDescription;

            // Handle evidence section
            const evidenceContent = template.querySelector('.evidence-content');
            if (inquiry.evidence_url) {
                evidenceContent.innerHTML = `
                    <a href="${escapeHtml(inquiry.evidence_url)}" target="_blank" class="evidence-link">
                        <i class="fas fa-external-link-alt me-1"></i>View Evidence
                    </a>
                `;
            } else {
                evidenceContent.innerHTML = `
                    <div class="detail-value text-muted">
                        <i class="fas fa-times-circle me-1"></i>N/A
                    </div>
                `;
            }

            return template;
        }

        // Function to show loading state
        function showLoading() {
            hideAllViews();
            elements.loadingSpinner.style.display = 'block';
        }

        // Function to show error message
        function showError(message) {
            hideAllViews();
            elements.errorText.textContent = message;
            elements.errorMessage.style.display = 'block';
        }

        // Function to show no inquiries message
        function showNoInquiries() {
            hideAllViews();
            elements.noInquiries.style.display = 'block';
        }

        // Function to hide all views
        function hideAllViews() {
            elements.loadingSpinner.style.display = 'none';
            elements.errorMessage.style.display = 'none';
            elements.noInquiries.style.display = 'none';
            elements.inquiriesList.style.display = 'none';
        } // Helper functions
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

        function truncateText(text, maxLength) {
            if (!text) return '';
            if (text.length <= maxLength) return text;
            return text.substring(0, maxLength) + '...';
        }

        // Function to handle view inquiry details button click
        function viewInquiryDetails(button) {
            // Find the inquiry card parent element
            const inquiryCard = button.closest('.inquiry-card');

            // Get inquiry data from the card
            const inquiryData = JSON.parse(inquiryCard.getAttribute('data-inquiry'));

            // Populate modal with inquiry details
            populateModal(inquiryData);

            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('inquiryModal'));
            modal.show();
        }

        function populateModal(inquiry) {
            // Basic information
            document.getElementById('modal-title').textContent = inquiry.title || 'N/A';
            document.getElementById('modal-applied-date').textContent = formatDate(inquiry.submission_date);
            document.getElementById('modal-assignment-date').textContent = formatDate(inquiry.assignment_date || inquiry.submission_date);
            document.getElementById('modal-agency').textContent = inquiry.agency_name || 'Unknown Agency';
            document.getElementById('modal-applicant').textContent = inquiry.applicant_name || 'Unknown User';

            // Full description
            document.getElementById('modal-description').textContent = inquiry.description || 'No description available';

            // Status badge
            const statusElement = document.getElementById('modal-status');
            statusElement.textContent = inquiry.final_status || 'Unknown';
            statusElement.className = 'badge status-badge ' + (inquiry.final_status || '').toLowerCase().replace(' ', '-');

            // Evidence
            const evidenceElement = document.getElementById('modal-evidence');
            if (inquiry.evidence_url) {
                evidenceElement.innerHTML = `
                    <a href="${escapeHtml(inquiry.evidence_url)}" target="_blank" class="evidence-link">
                        <i class="fas fa-external-link-alt me-1"></i>View Evidence
                    </a>
                `;
            } else {
                evidenceElement.innerHTML = `
                    <span class="text-muted">
                        <i class="fas fa-times-circle me-1"></i>No evidence available
                    </span>
                `;
            }
        }

        function viewDetails(inquiryId) {
            alert(`View details for Inquiry #${inquiryId}`);
        }

        function openAddInquiryModal() {
            alert('Add New Inquiry functionality will be implemented here');
        }
    </script>
</body>

</html>