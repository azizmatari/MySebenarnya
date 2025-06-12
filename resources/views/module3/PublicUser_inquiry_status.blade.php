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
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-nav a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            transition: all 0.3s ease;
        }

        .sidebar-nav a:hover {
            background: rgba(255,255,255,0.1);
            padding-left: 10px;
        }

        .sidebar-nav a.disabled {
            color: rgba(255,255,255,0.5);
            cursor: not-allowed;
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 0;
        }

        .inquiry-card {
            border-left: 4px solid #ffc107;
            transition: all 0.3s ease;
        }

        .inquiry-card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .status-badge {
            background: linear-gradient(45deg, #ffc107, #ffeb3b);
            color: #333;
            font-weight: bold;
        }

        .agency-tag {
            background: #e3f2fd;
            color: #1976d2;
            border-radius: 15px;
            padding: 3px 10px;
            font-size: 0.85em;
        }

        .header-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
        }

        .inquiry-counter {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            padding: 1rem;
        }
    </style>
</head>

<body class="bg-light">
    <!-- Include Sidebar -->
    @include('layouts.sidebarPublic')

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header Section -->
        <div class="header-section">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="mb-2">
                            <i class="fas fa-search me-3"></i>
                            Active Inquiries Status
                        </h1>
                        <p class="mb-0 opacity-75">Monitor inquiries currently under investigation</p>
                    </div>
                    <div class="col-md-4">
                        <div class="inquiry-counter text-center">
                            <h3 class="mb-1" id="inquiry-count">0</h3>
                            <small>Active Inquiries</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Container -->
        <div class="container my-5" id="inquiry-container">
            <!-- Content will be loaded here by JavaScript -->
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Load inquiries when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadInquiries();
        });

        // Function to load inquiries via AJAX
        function loadInquiries() {
            fetch('/module3/status/get-inquiries')
                .then(response => response.json())
                .then(data => {
                    displayInquiries(data);
                })
                .catch(error => {
                    console.error('Error loading inquiries:', error);
                    showNoInquiries();
                });
        }

        // Function to display inquiries
        function displayInquiries(inquiries) {
            const container = document.getElementById('inquiry-container');
            const countElement = document.getElementById('inquiry-count');

            countElement.textContent = inquiries.length;

            if (inquiries.length === 0) {
                showNoInquiries();
                return;
            }

            let html = '<div class="row">';

            inquiries.forEach(inquiry => {
                html += `
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card inquiry-card h-100">
                            <div class="card-header bg-white border-bottom">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h6 class="card-title mb-1 fw-bold text-primary">
                                        #${inquiry.inquiryId} - ${inquiry.title}
                                    </h6>
                                    <span class="badge status-badge">
                                        <i class="fas fa-magnifying-glass me-1"></i>
                                        ${inquiry.final_status}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <div class="mb-3">
                                    <h6 class="text-muted mb-2">
                                        <i class="fas fa-file-text me-2"></i>Description:
                                    </h6>
                                    <p class="text-dark">${truncateText(inquiry.description, 20)}</p>
                                </div>

                                <div class="mb-3">
                                    <h6 class="text-muted mb-2">
                                        <i class="fas fa-user me-2"></i>Applicant:
                                    </h6>
                                    <p class="text-dark">${inquiry.applicant_name}</p>
                                </div>

                                <div class="mb-3">
                                    <h6 class="text-muted mb-2">
                                        <i class="fas fa-building me-2"></i>Assigned Agency:
                                    </h6>
                                    <span class="agency-tag">${inquiry.agency_name}</span>
                                </div>
                            </div>

                            <div class="card-footer bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        Applied: ${formatDate(inquiry.submission_date)}
                                    </small>
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewDetails(${inquiry.inquiryId})">
                                        <i class="fas fa-eye me-1"></i>View
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            html += '</div>';

            // Add statistics
            html += generateStatistics(inquiries);

            container.innerHTML = html;

            // Add animations
            addAnimations();
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

        // Function to generate statistics
        function generateStatistics(inquiries) {
            const agencies = [...new Set(inquiries.map(i => i.agency_name))];
            const recentInquiries = inquiries.filter(i => {
                const submissionDate = new Date(i.submission_date);
                const weekAgo = new Date();
                weekAgo.setDate(weekAgo.getDate() - 7);
                return submissionDate > weekAgo;
            });

            return `
                <div class="row mt-5">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-chart-bar me-2"></i>
                                    Summary Statistics
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <div class="border-end">
                                            <h3 class="text-warning">${inquiries.length}</h3>
                                            <p class="text-muted mb-0">Under Investigation</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="border-end">
                                            <h3 class="text-info">${agencies.length}</h3>
                                            <p class="text-muted mb-0">Agencies Involved</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <h3 class="text-success">${recentInquiries.length}</h3>
                                        <p class="text-muted mb-0">This Week</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Helper functions
        function truncateText(text, length) {
            return text.length > length ? text.substring(0, length) + '...' : text;
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        }

        function addAnimations() {
            const cards = document.querySelectorAll('.inquiry-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        }

        function viewDetails(inquiryId) {
            // You can implement this to show inquiry details in a modal or navigate to another page
            alert('View details for Inquiry #' + inquiryId);
            // Example: window.location.href = 'inquiry_details.php?id=' + inquiryId;
        }

        // Auto-refresh every 30 seconds to show real-time updates
        setInterval(function() {
            loadInquiries();
        }, 30000);
    </script>
</body>

</html>
