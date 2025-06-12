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
            margin-bottom: 15px;
            transition: box-shadow 0.2s ease;
        }

        .inquiry-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .inquiry-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .inquiry-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }

        .inquiry-body {
            padding: 20px;
        }

        .inquiry-details {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-bottom: 15px;
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
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #f1f3f4;
        }

        .evidence-section {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #f1f3f4;
        }

        .evidence-link {
            color: #4f46e5;
            text-decoration: none;
            font-size: 0.9rem;
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
            position: fixed !important;
            top: 85px !important;
            right: 50px !important;
            z-index: 9999 !important;
            background: linear-gradient(45deg, #007bff, #0056b3);
            border: none;
            border-radius: 25px;
            padding: 12px 24px;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
            transition: all 0.3s ease;
        }

        .add-inquiry-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 123, 255, 0.4);
            color: white;
            background: linear-gradient(45deg, #0056b3, #004085);
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
    </style>
</head>

<body> <!-- Include Sidebar -->
    @include('layouts.sidebarPublic')

    <!-- Add New Inquiry Button -->
    <a href="{{ route('inquiry.create') }}" class="add-inquiry-btn">
        <i class="fas fa-plus"></i>
        Add New Inquiry
    </a>

    <!-- Main Content -->
    <div class="main-content">
        <div class="content-wrapper">
            <!-- Content Container -->
            <div class="container-fluid" id="inquiry-container">
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p>Loading inquiries...</p>
                </div>
            </div>
        </div>
    </div> <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Application configuration
        const API_ENDPOINTS = {
            inquiries: '/module3/status/get-inquiries',
            statistics: '/module3/status/statistics'
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
        } // Function to display inquiries
        function displayInquiries(inquiries) {
            const container = document.getElementById('inquiry-container');

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
            let evidenceSection;
            if (inquiry.evidence_url) {
                evidenceSection = `
                    <div class="evidence-section">
                        <div class="detail-label">Evidence:</div>
                        <a href="${escapeHtml(inquiry.evidence_url)}" target="_blank" class="evidence-link">
                            <i class="fas fa-external-link-alt me-1"></i>View Evidence
                        </a>
                    </div>`;
            } else {
                evidenceSection = `
                    <div class="evidence-section">
                        <div class="detail-label">Evidence:</div>
                        <div class="detail-value text-muted">
                            <i class="fas fa-times-circle me-1"></i>N/A
                        </div>
                    </div>`;
            }

            return `
                <div class="inquiry-card">
                    <div class="inquiry-header">
                        <h5 class="inquiry-title">
                            <i class="fas fa-file-alt me-2"></i>
                            ${escapeHtml(inquiry.title)}
                        </h5>
                        <span class="badge status-badge ${statusClass}">
                            <i class="fas fa-clock me-1"></i>
                            ${escapeHtml(inquiry.final_status)}
                        </span>
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
                                    ${escapeHtml(inquiry.agency_name)}
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
                                ${escapeHtml(inquiry.description)}
                            </div>
                        </div>
                        
                        ${evidenceSection}
                    </div>
                </div>
            `;
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
            alert(`View details for Inquiry #${inquiryId}`);
        }

        function addNewInquiry() {
            // TODO: Implement add new inquiry functionality
            alert('Add New Inquiry functionality will be implemented here');
        }
    </script>
</body>

</html>