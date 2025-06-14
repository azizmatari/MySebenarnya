<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Inquiry Assignment Reporting - MCMC Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
        }

        .stats-card:hover {
            transform: translateY(-2px);
        }

        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stats-label {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .table-container {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .export-buttons {
            margin-bottom: 20px;
        }

        .btn-export {
            margin-right: 10px;
            margin-bottom: 10px;
        }

        .loading-spinner {
            text-align: center;
            padding: 40px;
        }

        .performance-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .performance-excellent {
            background-color: #d4edda;
            color: #155724;
        }

        .performance-good {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .performance-average {
            background-color: #fff3cd;
            color: #856404;
        }

        .performance-poor {
            background-color: #f8d7da;
            color: #721c24;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }

            .dashboard-header {
                padding: 20px;
            }

            .stats-card {
                padding: 20px;
            }

            .chart-container,
            .table-container {
                padding: 15px;
            }
        }
    </style>
</head>

<body>
    <!-- Include MCMC Sidebar -->
    @include('layouts.sidebarMcmc')

    <!-- Main Content -->
    <div class="main-content">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <h1 class="mb-0">
                <i class="fas fa-chart-bar me-3"></i>Inquiry Assignment Reporting
            </h1>
            <p class="mb-0 mt-2">Generate comprehensive reports on inquiry assignments and agency performance</p>
        </div>

        <!-- Overall Statistics -->
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="stats-card">
                    <div class="stats-icon" style="background-color: #e3f2fd; color: #1976d2;">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stats-number text-primary">{{ $overallStats->total_inquiries ?? 0 }}</div>
                    <div class="stats-label">Total Inquiries</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stats-card">
                    <div class="stats-icon" style="background-color: #e8f5e9; color: #388e3c;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stats-number text-success">{{ $overallStats->assigned_inquiries ?? 0 }}</div>
                    <div class="stats-label">Assigned Inquiries</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stats-card">
                    <div class="stats-icon" style="background-color: #fff3e0; color: #f57c00;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stats-number text-warning">{{ $overallStats->under_investigation ?? 0 }}</div>
                    <div class="stats-label">Under Investigation</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stats-card">
                    <div class="stats-icon" style="background-color: #fce4ec; color: #c2185b;">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="stats-number text-danger">{{ $overallStats->total_agencies ?? 0 }}</div>
                    <div class="stats-label">Active Agencies</div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-card">
            <h5 class="mb-4">
                <i class="fas fa-filter me-2"></i>Report Filters
            </h5>
            <form id="filterForm">
                <div class="row">
                    <div class="col-md-3">
                        <label for="startDate" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="startDate" name="start_date">
                    </div>
                    <div class="col-md-3">
                        <label for="endDate" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="endDate" name="end_date">
                    </div>
                    <div class="col-md-3">
                        <label for="agencySelect" class="form-label">Agency</label>
                        <select class="form-select" id="agencySelect" name="agency_id">
                            <option value="">All Agencies</option>
                            @foreach($agencies as $agency)
                                <option value="{{ $agency->agencyId }}">{{ $agency->agency_name }} ({{ $agency->agencyType }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="yearSelect" class="form-label">Year</label>
                        <select class="form-select" id="yearSelect" name="year">
                            @for($year = date('Y'); $year >= 2020; $year--)
                                <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="button" class="btn btn-primary" onclick="applyFilters()">
                            <i class="fas fa-search me-2"></i>Apply Filters
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="resetFilters()">
                            <i class="fas fa-refresh me-2"></i>Reset
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Export Buttons -->
        <div class="export-buttons">
            <button class="btn btn-success btn-export" onclick="exportReport('pdf', 'agency_assignments')">
                <i class="fas fa-file-pdf me-2"></i>Export Agency Report (PDF)
            </button>
            <button class="btn btn-info btn-export" onclick="exportReport('excel', 'agency_assignments')">
                <i class="fas fa-file-excel me-2"></i>Export Agency Report (Excel)
            </button>
            <button class="btn btn-warning btn-export" onclick="exportReport('pdf', 'agency_performance')">
                <i class="fas fa-chart-line me-2"></i>Export Performance Report (PDF)
            </button>
            <button class="btn btn-dark btn-export" onclick="exportReport('excel', 'agency_performance')">
                <i class="fas fa-chart-bar me-2"></i>Export Performance Report (Excel)
            </button>
        </div>

        <!-- Charts Row -->
        <div class="row">
            <div class="col-md-6">
                <div class="chart-container">
                    <h5 class="mb-4">
                        <i class="fas fa-chart-pie me-2"></i>Inquiries by Agency Type
                    </h5>
                    <canvas id="agencyTypeChart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <h5 class="mb-4">
                        <i class="fas fa-chart-line me-2"></i>Monthly Inquiry Trends
                    </h5>
                    <canvas id="trendsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Agency Assignment Report -->
        <div class="table-container">
            <h5 class="mb-4">
                <i class="fas fa-building me-2"></i>Agency Assignment Summary
            </h5>
            <div id="agencyReportContainer">
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p>Loading agency report...</p>
                </div>
            </div>
        </div>

        <!-- Agency Performance Report -->
        <div class="table-container">
            <h5 class="mb-4">
                <i class="fas fa-chart-bar me-2"></i>Agency Performance Analysis
            </h5>
            <div id="performanceReportContainer">
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p>Loading performance report...</p>
                </div>
            </div>
        </div>

        <!-- Filtered Inquiries -->
        <div class="table-container">
            <h5 class="mb-4">
                <i class="fas fa-filter me-2"></i>Filtered Inquiries
            </h5>
            <div id="filteredInquiriesContainer">
                <div class="text-center py-4">
                    <i class="fas fa-info-circle fa-2x text-muted mb-3"></i>
                    <p class="text-muted">Apply filters above to view specific inquiries</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Global variables
        let agencyTypeChart = null;
        let trendsChart = null;

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadAgencyReport();
            loadPerformanceReport();
            loadTrendsChart();
            loadAgencyTypeChart();
        });

        // Load agency assignment report
        async function loadAgencyReport() {
            try {
                const response = await fetch('/reports/agency-assignments');
                const result = await response.json();

                if (result.success) {
                    displayAgencyReport(result.data);
                } else {
                    showError('agencyReportContainer', 'Failed to load agency report');
                }
            } catch (error) {
                console.error('Error loading agency report:', error);
                showError('agencyReportContainer', 'Error loading agency report');
            }
        }

        // Display agency report
        function displayAgencyReport(data) {
            const container = document.getElementById('agencyReportContainer');
            
            if (data.length === 0) {
                container.innerHTML = '<div class="text-center py-4"><p class="text-muted">No agency assignments found</p></div>';
                return;
            }

            let html = `
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Agency Name</th>
                                <th>Type</th>
                                <th>Total Assignments</th>
                                <th>Active Inquiries</th>
                                <th>Completed Inquiries</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            data.forEach(agency => {
                const statusClass = agency.total_assignments > 0 ? 'text-success' : 'text-muted';
                const statusText = agency.total_assignments > 0 ? 'Active' : 'No Assignments';
                
                html += `
                    <tr>
                        <td><strong>${escapeHtml(agency.agency_name)}</strong></td>
                        <td><span class="badge bg-secondary">${escapeHtml(agency.agencyType)}</span></td>
                        <td><span class="badge bg-primary">${agency.total_assignments}</span></td>
                        <td><span class="badge bg-warning">${agency.active_inquiries}</span></td>
                        <td><span class="badge bg-success">${agency.completed_inquiries}</span></td>
                        <td><span class="${statusClass}">${statusText}</span></td>
                    </tr>
                `;
            });

            html += '</tbody></table></div>';
            container.innerHTML = html;
        }

        // Load performance report
        async function loadPerformanceReport() {
            try {
                const response = await fetch('/reports/agency-performance');
                const result = await response.json();

                if (result.success) {
                    displayPerformanceReport(result.data);
                } else {
                    showError('performanceReportContainer', 'Failed to load performance report');
                }
            } catch (error) {
                console.error('Error loading performance report:', error);
                showError('performanceReportContainer', 'Error loading performance report');
            }
        }

        // Display performance report
        function displayPerformanceReport(data) {
            const container = document.getElementById('performanceReportContainer');
            
            if (data.length === 0) {
                container.innerHTML = '<div class="text-center py-4"><p class="text-muted">No performance data available</p></div>';
                return;
            }

            let html = `
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Agency Name</th>
                                <th>Type</th>
                                <th>Total Assigned</th>
                                <th>Completed</th>
                                <th>Pending</th>
                                <th>Completion Rate</th>
                                <th>Avg Days</th>
                                <th>Performance</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            data.forEach(agency => {
                const completionRate = parseFloat(agency.completion_rate) || 0;
                const avgDays = agency.avg_completion_days ? Math.round(agency.avg_completion_days) : 'N/A';
                
                let performanceClass = 'performance-poor';
                let performanceText = 'Needs Improvement';
                
                if (completionRate >= 80) {
                    performanceClass = 'performance-excellent';
                    performanceText = 'Excellent';
                } else if (completionRate >= 60) {
                    performanceClass = 'performance-good';
                    performanceText = 'Good';
                } else if (completionRate >= 40) {
                    performanceClass = 'performance-average';
                    performanceText = 'Average';
                }
                
                html += `
                    <tr>
                        <td><strong>${escapeHtml(agency.agency_name)}</strong></td>
                        <td><span class="badge bg-secondary">${escapeHtml(agency.agencyType)}</span></td>
                        <td><span class="badge bg-primary">${agency.total_assigned}</span></td>
                        <td><span class="badge bg-success">${agency.completed}</span></td>
                        <td><span class="badge bg-warning">${agency.pending}</span></td>
                        <td><strong>${completionRate.toFixed(1)}%</strong></td>
                        <td>${avgDays}</td>
                        <td><span class="performance-badge ${performanceClass}">${performanceText}</span></td>
                    </tr>
                `;
            });

            html += '</tbody></table></div>';
            container.innerHTML = html;
        }

        // Load trends chart
        async function loadTrendsChart() {
            try {
                const year = document.getElementById('yearSelect').value;
                const response = await fetch(`/reports/inquiry-trends?year=${year}`);
                const result = await response.json();

                if (result.success) {
                    displayTrendsChart(result.data);
                }
            } catch (error) {
                console.error('Error loading trends chart:', error);
            }
        }

        // Display trends chart
        function displayTrendsChart(data) {
            const ctx = document.getElementById('trendsChart').getContext('2d');
            
            if (trendsChart) {
                trendsChart.destroy();
            }

            const months = data.map(item => item.month_name || `Month ${item.month}`);
            const totalInquiries = data.map(item => item.total_inquiries);
            const assignedInquiries = data.map(item => item.assigned_inquiries);

            trendsChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Total Inquiries',
                        data: totalInquiries,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.1
                    }, {
                        label: 'Assigned Inquiries',
                        data: assignedInquiries,
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Load agency type chart
        async function loadAgencyTypeChart() {
            try {
                const response = await fetch('/reports/agency-assignments');
                const result = await response.json();

                if (result.success) {
                    displayAgencyTypeChart(result.data);
                }
            } catch (error) {
                console.error('Error loading agency type chart:', error);
            }
        }

        // Display agency type chart
        function displayAgencyTypeChart(data) {
            const ctx = document.getElementById('agencyTypeChart').getContext('2d');
            
            if (agencyTypeChart) {
                agencyTypeChart.destroy();
            }

            // Group by agency type
            const typeData = {};
            data.forEach(agency => {
                if (!typeData[agency.agencyType]) {
                    typeData[agency.agencyType] = 0;
                }
                typeData[agency.agencyType] += parseInt(agency.total_assignments);
            });

            const labels = Object.keys(typeData);
            const values = Object.values(typeData);
            const colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'];

            agencyTypeChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: colors.slice(0, labels.length),
                        hoverBackgroundColor: colors.slice(0, labels.length)
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    }
                }
            });
        }

        // Apply filters
        async function applyFilters() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            const agencyId = document.getElementById('agencySelect').value;

            // Show loading
            document.getElementById('filteredInquiriesContainer').innerHTML = `
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p>Loading filtered inquiries...</p>
                </div>
            `;

            try {
                const params = new URLSearchParams();
                if (startDate) params.append('start_date', startDate);
                if (endDate) params.append('end_date', endDate);
                if (agencyId) params.append('agency_id', agencyId);

                const response = await fetch(`/reports/filtered-inquiries?${params.toString()}`);
                const result = await response.json();

                if (result.success) {
                    displayFilteredInquiries(result.data);
                } else {
                    showError('filteredInquiriesContainer', 'Failed to load filtered inquiries');
                }
            } catch (error) {
                console.error('Error loading filtered inquiries:', error);
                showError('filteredInquiriesContainer', 'Error loading filtered inquiries');
            }
        }

        // Display filtered inquiries
        function displayFilteredInquiries(data) {
            const container = document.getElementById('filteredInquiriesContainer');
            
            if (data.length === 0) {
                container.innerHTML = '<div class="text-center py-4"><p class="text-muted">No inquiries found with the selected filters</p></div>';
                return;
            }

            let html = `
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Submission Date</th>
                                <th>Agency</th>
                                <th>Assignment Date</th>
                                <th>Applicant</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            data.forEach(inquiry => {
                const statusClass = getStatusClass(inquiry.final_status);
                const assignDate = inquiry.assignDate ? new Date(inquiry.assignDate).toLocaleDateString() : 'Not Assigned';
                const submissionDate = new Date(inquiry.submission_date).toLocaleDateString();
                
                html += `
                    <tr>
                        <td><strong>#${inquiry.inquiryId}</strong></td>
                        <td>${escapeHtml(inquiry.title)}</td>
                        <td><span class="badge ${statusClass}">${inquiry.final_status || 'Pending'}</span></td>
                        <td>${submissionDate}</td>
                        <td>${escapeHtml(inquiry.agency_name || 'Not Assigned')}</td>
                        <td>${assignDate}</td>
                        <td>${escapeHtml(inquiry.applicant_name || 'Anonymous')}</td>
                    </tr>
                `;
            });

            html += '</tbody></table></div>';
            container.innerHTML = html;
        }

        // Reset filters
        function resetFilters() {
            document.getElementById('filterForm').reset();
            document.getElementById('filteredInquiriesContainer').innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-info-circle fa-2x text-muted mb-3"></i>
                    <p class="text-muted">Apply filters above to view specific inquiries</p>
                </div>
            `;
        }

        // Export report
        function exportReport(format, reportType) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = format === 'pdf' ? '/reports/export-pdf' : '/reports/export-excel';
            
            // Add CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken.getAttribute('content');
                form.appendChild(csrfInput);
            }
            
            // Add report type
            const reportTypeInput = document.createElement('input');
            reportTypeInput.type = 'hidden';
            reportTypeInput.name = 'report_type';
            reportTypeInput.value = reportType;
            form.appendChild(reportTypeInput);
            
            // Add filter values
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            const agencyId = document.getElementById('agencySelect').value;
            
            if (startDate) {
                const startDateInput = document.createElement('input');
                startDateInput.type = 'hidden';
                startDateInput.name = 'start_date';
                startDateInput.value = startDate;
                form.appendChild(startDateInput);
            }
            
            if (endDate) {
                const endDateInput = document.createElement('input');
                endDateInput.type = 'hidden';
                endDateInput.name = 'end_date';
                endDateInput.value = endDate;
                form.appendChild(endDateInput);
            }
            
            if (agencyId) {
                const agencyInput = document.createElement('input');
                agencyInput.type = 'hidden';
                agencyInput.name = 'agency_id';
                agencyInput.value = agencyId;
                form.appendChild(agencyInput);
            }
            
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }

        // Helper functions
        function showError(containerId, message) {
            const container = document.getElementById(containerId);
            container.innerHTML = `
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ${message}
                </div>
            `;
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function getStatusClass(status) {
            switch (status) {
                case 'Under Investigation':
                    return 'bg-warning';
                case 'True':
                    return 'bg-success';
                case 'Fake':
                    return 'bg-danger';
                default:
                    return 'bg-secondary';
            }
        }

        // Update trends chart when year changes
        document.getElementById('yearSelect').addEventListener('change', function() {
            loadTrendsChart();
        });
    </script>
</body>

</html>
