@include('layouts.sidebarAgency')

<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="dashboard-content" style="margin-left: 250px; padding-top: 70px; min-height: 100vh; background-color: #f8f9fa;">
    <div class="dashboard-header">
        <h1>Agency Dashboard</h1>
        <div class="agency-info">
            <span class="agency-name">{{ $agency->agency_name }}</span>
            <span class="last-updated">Last Updated: <span id="lastUpdated">{{ now()->format('M j, Y g:i A') }}</span></span>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card total-assigned">
            <div class="stat-icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number" id="totalAssigned">{{ $totalAssigned }}</div>
                <div class="stat-label">Total Assigned</div>
            </div>
        </div>

        <div class="stat-card under-investigation">
            <div class="stat-icon">
                <i class="fas fa-search"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number" id="underInvestigation">{{ $underInvestigation }}</div>
                <div class="stat-label">Under Investigation</div>
            </div>
        </div>

        <div class="stat-card resolved">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number" id="resolved">{{ $resolved }}</div>
                <div class="stat-label">Resolved</div>
            </div>
        </div>

        <div class="stat-card resolution-rate">
            <div class="stat-icon">
                <i class="fas fa-percentage"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number" id="resolutionRate">{{ $totalAssigned > 0 ? round(($resolved / $totalAssigned) * 100, 1) : 0 }}%</div>
                <div class="stat-label">Resolution Rate</div>
            </div>
        </div>
    </div>

    <!-- Status Distribution Chart -->
    <div class="chart-container">
        <h3>Investigation Status Distribution</h3>
        <div class="chart-wrapper">
            <canvas id="statusChart" width="400" height="200"></canvas>
        </div>
    </div>

    <!-- Controls Section -->
    <div class="controls-section">
        <div class="filter-controls">
            <div class="filter-group">
                <label for="statusFilter">Filter by Status:</label>
                <select id="statusFilter" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="Under Investigation">Under Investigation</option>
                    <option value="True">Verified as True</option>
                    <option value="Fake">Identified as Fake</option>
                    <option value="Rejected">Rejected</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="dateFilter">Filter by Date:</label>
                <select id="dateFilter" class="form-control">
                    <option value="">All Time</option>
                    <option value="today">Today</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                </select>
            </div>

            <button class="btn btn-secondary" onclick="resetFilters()">
                <i class="fas fa-refresh"></i> Reset Filters
            </button>
        </div>

        <div class="action-controls">
            <button class="btn btn-primary" onclick="refreshDashboard()">
                <i class="fas fa-refresh"></i> Refresh Data
            </button>
        </div>
    </div>

    <!-- Main Content Tabs -->
    <div class="dashboard-tabs">
        <div class="tab-buttons">
            <button class="tab-btn active" onclick="showTab('pending')">
                <i class="fas fa-hourglass-half"></i> Pending Cases ({{ $underInvestigation }})
            </button>
            <button class="tab-btn" onclick="showTab('all')">
                <i class="fas fa-list"></i> All Assigned Cases ({{ $totalAssigned }})
            </button>
            <button class="tab-btn" onclick="showTab('resolved')">
                <i class="fas fa-check"></i> Resolved Cases ({{ $resolved }})
            </button>
            <button class="tab-btn" onclick="showTab('activity')">
                <i class="fas fa-history"></i> Recent Activity
            </button>
        </div>

        <!-- Pending Cases Tab -->
        <div id="pendingTab" class="tab-content active">
            <div class="section-header">
                <h3>Cases Requiring Investigation</h3>
                <div class="section-info">
                    <span class="badge badge-warning">{{ $underInvestigation }} pending</span>
                </div>
            </div>
            
            <div class="inquiries-table-container">
                <table class="inquiries-table" id="pendingInquiriesTable">
                    <thead>
                        <tr>
                            <th>Inquiry ID</th>
                            <th>Title</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th>Assigned Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingInquiries as $inquiry)
                        <tr data-inquiry-id="{{ $inquiry->inquiryId }}">
                            <td>{{ $inquiry->inquiryId }}</td>
                            <td class="inquiry-title">{{ $inquiry->title }}</td>
                            <td>{{ \Carbon\Carbon::parse($inquiry->submission_date)->format('M j, Y') }}</td>
                            <td>
                                <span class="status-badge status-{{ strtolower(str_replace(' ', '-', $inquiry->getCurrentStatus())) }}">
                                    {{ $inquiry->getCurrentStatus() }}
                                </span>
                            </td>
                            <td>{{ $inquiry->currentAssignment ? \Carbon\Carbon::parse($inquiry->currentAssignment->assignDate)->format('M j, Y') : 'N/A' }}</td>
                            <td class="action-buttons">
                                <button class="btn btn-sm btn-info" onclick="viewInquiryDetails({{ $inquiry->inquiryId }})">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button class="btn btn-sm btn-primary" onclick="updateStatus({{ $inquiry->inquiryId }})">
                                    <i class="fas fa-edit"></i> Update Status
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- All Cases Tab -->
        <div id="allTab" class="tab-content">
            <div class="section-header">
                <h3>All Assigned Cases</h3>
                <div class="section-info">
                    <span class="badge badge-info">{{ $totalAssigned }} total</span>
                </div>
            </div>
            
            <div class="inquiries-table-container">
                <table class="inquiries-table" id="allInquiriesTable">
                    <thead>
                        <tr>
                            <th>Inquiry ID</th>
                            <th>Title</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assignedInquiries as $inquiry)
                        <tr data-inquiry-id="{{ $inquiry->inquiryId }}">
                            <td>{{ $inquiry->inquiryId }}</td>
                            <td class="inquiry-title">{{ $inquiry->title }}</td>
                            <td>{{ \Carbon\Carbon::parse($inquiry->submission_date)->format('M j, Y') }}</td>
                            <td>
                                <span class="status-badge status-{{ strtolower(str_replace(' ', '-', $inquiry->getCurrentStatus())) }}">
                                    {{ $inquiry->getCurrentStatus() }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $lastUpdate = $inquiry->statusHistory->first();
                                @endphp
                                {{ $lastUpdate ? $lastUpdate->updated_at->format('M j, Y g:i A') : 'N/A' }}
                            </td>
                            <td class="action-buttons">
                                <button class="btn btn-sm btn-info" onclick="viewInquiryDetails({{ $inquiry->inquiryId }})">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button class="btn btn-sm btn-primary" onclick="updateStatus({{ $inquiry->inquiryId }})">
                                    <i class="fas fa-edit"></i> Update Status
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Resolved Cases Tab -->
        <div id="resolvedTab" class="tab-content">
            <div class="section-header">
                <h3>Resolved Cases</h3>
                <div class="section-stats">
                    <span class="badge badge-success">{{ $verified }} Verified</span>
                    <span class="badge badge-danger">{{ $fake }} Fake</span>
                    <span class="badge badge-secondary">{{ $rejected }} Rejected</span>
                </div>
            </div>
            
            <div class="inquiries-table-container">
                <table class="inquiries-table" id="resolvedInquiriesTable">
                    <thead>
                        <tr>
                            <th>Inquiry ID</th>
                            <th>Title</th>
                            <th>Final Status</th>
                            <th>Resolved Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assignedInquiries->filter(function($inquiry) { return in_array($inquiry->getCurrentStatus(), ['True', 'Fake', 'Rejected']); }) as $inquiry)
                        <tr data-inquiry-id="{{ $inquiry->inquiryId }}">
                            <td>{{ $inquiry->inquiryId }}</td>
                            <td class="inquiry-title">{{ $inquiry->title }}</td>
                            <td>
                                <span class="status-badge status-{{ strtolower(str_replace(' ', '-', $inquiry->getCurrentStatus())) }}">
                                    {{ $inquiry->getCurrentStatus() === 'True' ? 'Verified as True' : ($inquiry->getCurrentStatus() === 'Fake' ? 'Identified as Fake' : 'Rejected') }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $lastUpdate = $inquiry->statusHistory->first();
                                @endphp
                                {{ $lastUpdate ? $lastUpdate->updated_at->format('M j, Y g:i A') : 'N/A' }}
                            </td>
                            <td class="action-buttons">
                                <button class="btn btn-sm btn-info" onclick="viewInquiryDetails({{ $inquiry->inquiryId }})">
                                    <i class="fas fa-eye"></i> View Details
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Activity Tab -->
        <div id="activityTab" class="tab-content">
            <div class="section-header">
                <h3>Recent Status Updates</h3>
                <div class="section-info">
                    <span class="badge badge-info">Last 10 updates</span>
                </div>
            </div>
            
            <div class="activity-timeline">
                @foreach($recentUpdates as $update)
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-{{ $update->status === 'Under Investigation' ? 'search' : ($update->status === 'True' ? 'check' : ($update->status === 'Fake' ? 'times' : 'ban')) }}"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-header">
                            <span class="activity-title">Inquiry #{{ $update->inquiryId }} - {{ $update->inquiry->title }}</span>
                            <span class="activity-time">{{ $update->updated_at->diffForHumans() }}</span>
                        </div>
                        <div class="activity-details">
                            <span class="status-badge status-{{ strtolower(str_replace(' ', '-', $update->status)) }}">
                                {{ $update->status }}
                            </span>
                            @if($update->status_comment)
                            <p class="activity-comment">{{ $update->status_comment }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div id="statusUpdateModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Update Investigation Status</h3>
            <span class="close" onclick="closeModal('statusUpdateModal')">&times;</span>
        </div>
        <div class="modal-body">
            <form id="statusUpdateForm" enctype="multipart/form-data">
                <input type="hidden" id="updateInquiryId" name="inquiryId">
                
                <div class="form-group">
                    <label for="status">Investigation Status *</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="">Select Status</option>
                        <option value="Under Investigation">Under Investigation</option>
                        <option value="True">Verified as True</option>
                        <option value="Fake">Identified as Fake</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="reviewing_officer">Reviewing Officer Name *</label>
                    <input type="text" id="reviewing_officer" name="reviewing_officer" class="form-control" required 
                           placeholder="Enter the name of the reviewing officer">
                </div>

                <div class="form-group">
                    <label for="status_comment">Investigation Notes</label>
                    <textarea id="status_comment" name="status_comment" class="form-control" rows="4" 
                              placeholder="Add any investigation notes, findings, or comments..."></textarea>
                </div>

                <div class="form-group">
                    <label for="supporting_document">Supporting Document</label>
                    <input type="file" id="supporting_document" name="supporting_document" class="form-control" 
                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.txt,.xlsx,.xls">
                    <small class="form-text text-muted">
                        Upload supporting documents (PDF, Word, Images, Excel). Max size: 10MB
                    </small>
                </div>
            </form>
        </div>
        <div class="form-actions">
            <button type="button" class="btn btn-secondary" onclick="closeModal('statusUpdateModal')">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="submitStatusUpdate()">
                <i class="fas fa-save"></i> Update Status
            </button>
        </div>
    </div>
</div>

<!-- Inquiry Details Modal -->
<div id="inquiryDetailsModal" class="modal">
    <div class="modal-content large">
        <div class="modal-header">
            <h3>Inquiry Details</h3>
            <span class="close" onclick="closeModal('inquiryDetailsModal')">&times;</span>
        </div>
        <div class="modal-body" id="inquiryDetailsContent">
            <!-- Content will be loaded dynamically -->
        </div>
    </div>
</div>

<style>
/* Agency Dashboard Styles */
.dashboard-content {
    padding: 20px;
    max-width: 1400px;
    margin: 0 auto;
    margin-left: 250px !important;
    padding-top: 90px !important;
    min-height: 100vh;
    background-color: #f8f9fa;
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #e9ecef;
}

.dashboard-header h1 {
    color: #2c3e50;
    margin: 0;
    font-size: 2.5em;
    font-weight: 600;
}

.agency-info {
    text-align: right;
}

.agency-name {
    display: block;
    font-size: 1.2em;
    font-weight: 600;
    color: #3498db;
    margin-bottom: 5px;
}

.last-updated {
    font-size: 0.9em;
    color: #6c757d;
}

/* Statistics Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.stat-icon {
    font-size: 2.5em;
    margin-right: 20px;
    width: 70px;
    text-align: center;
}

.total-assigned .stat-icon { color: #3498db; }
.under-investigation .stat-icon { color: #f39c12; }
.resolved .stat-icon { color: #27ae60; }
.resolution-rate .stat-icon { color: #9b59b6; }

.stat-number {
    font-size: 2.5em;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 0.9em;
    color: #6c757d;
    font-weight: 500;
}

/* Chart Container */
.chart-container {
    background: white;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.chart-container h3 {
    margin-bottom: 20px;
    color: #2c3e50;
}

.chart-wrapper {
    position: relative;
    height: 300px;
}

/* Controls Section */
.controls-section {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 30px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.filter-controls {
    display: flex;
    gap: 20px;
    align-items: center;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.filter-group label {
    font-size: 0.9em;
    font-weight: 600;
    color: #2c3e50;
}

.form-control {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 0.9em;
}

/* Dashboard Tabs */
.dashboard-tabs {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.tab-buttons {
    display: flex;
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.tab-btn {
    flex: 1;
    padding: 15px 20px;
    border: none;
    background: none;
    cursor: pointer;
    font-weight: 600;
    color: #6c757d;
    transition: all 0.3s ease;
    border-bottom: 3px solid transparent;
}

.tab-btn:hover {
    background: #e9ecef;
    color: #495057;
}

.tab-btn.active {
    color: #3498db;
    background: white;
    border-bottom-color: #3498db;
}

.tab-content {
    display: none;
    padding: 30px;
}

.tab-content.active {
    display: block;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 1px solid #dee2e6;
}

.section-header h3 {
    color: #2c3e50;
    margin: 0;
}

.section-info, .section-stats {
    display: flex;
    gap: 10px;
}

/* Status Badges */
.badge {
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 0.8em;
    font-weight: 600;
}

.badge-info { background: #d1ecf1; color: #0c5460; }
.badge-warning { background: #fff3cd; color: #856404; }
.badge-success { background: #d4edda; color: #155724; }
.badge-danger { background: #f8d7da; color: #721c24; }
.badge-secondary { background: #e2e3e5; color: #383d41; }

.status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: 600;
    text-transform: uppercase;
}

.status-under-investigation { background: #fff3cd; color: #856404; }
.status-true { background: #d4edda; color: #155724; }
.status-fake { background: #f8d7da; color: #721c24; }
.status-rejected { background: #e2e3e5; color: #383d41; }
.status-reassignment-requested { background: #cce7ff; color: #004085; }

/* Tables */
.inquiries-table-container {
    overflow-x: auto;
}

.inquiries-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
}

.inquiries-table th,
.inquiries-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #dee2e6;
}

.inquiries-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #495057;
    position: sticky;
    top: 0;
}

.inquiries-table tr:hover {
    background: #f8f9fa;
}

.inquiry-title {
    max-width: 300px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.btn {
    padding: 6px 12px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.8em;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.3s ease;
}

.btn-sm {
    padding: 4px 8px;
    font-size: 0.75em;
}

.btn-primary { background: #3498db; color: white; }
.btn-info { background: #17a2b8; color: white; }
.btn-secondary { background: #6c757d; color: white; }

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* Activity Timeline */
.activity-timeline {
    max-height: 600px;
    overflow-y: auto;
}

.activity-item {
    display: flex;
    align-items: flex-start;
    padding: 20px;
    border-bottom: 1px solid #e9ecef;
    transition: background 0.3s ease;
}

.activity-item:hover {
    background: #f8f9fa;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #3498db;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    flex-shrink: 0;
}

.activity-content {
    flex: 1;
}

.activity-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.activity-title {
    font-weight: 600;
    color: #2c3e50;
}

.activity-time {
    font-size: 0.8em;
    color: #6c757d;
}

.activity-comment {
    margin: 10px 0 0 0;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 6px;
    font-size: 0.9em;
    color: #495057;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(5px);
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 0;
    border-radius: 12px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: modalSlideIn 0.3s ease;
}

.modal-content.large {
    max-width: 900px;
}

@keyframes modalSlideIn {
    from { opacity: 0; transform: translateY(-50px); }
    to { opacity: 1; transform: translateY(0); }
}

.modal-header {
    padding: 20px 30px;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    color: #2c3e50;
}

.close {
    font-size: 24px;
    cursor: pointer;
    color: #6c757d;
    transition: color 0.3s ease;
}

.close:hover {
    color: #dc3545;
}

.modal-body {
    padding: 30px;
    max-height: calc(90vh - 140px);
    overflow-y: auto;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #2c3e50;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 1em;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 15px;
    padding: 20px 30px;
    border-top: 1px solid #dee2e6;
    background: white;
    border-radius: 0 0 12px 12px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .dashboard-content {
        padding: 10px;
        margin-left: 0 !important;
        padding-top: 70px !important;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .controls-section {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-controls {
        justify-content: center;
    }
    
    .tab-buttons {
        flex-direction: column;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .modal-content {
        width: 95%;
        margin: 10% auto;
    }
    
    .modal-body {
        padding: 20px;
    }
}

/* Loading States */
.loading {
    opacity: 0.6;
    pointer-events: none;
}

.spinner {
    border: 3px solid #f3f3f3;
    border-top: 3px solid #3498db;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    animation: spin 1s linear infinite;
    margin: 20px auto;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Global variables
let currentTab = 'pending';
let statusChart;

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    initializeStatusChart();
    setupEventListeners();
    refreshDashboard();
});

// Chart initialization
function initializeStatusChart() {
    const ctx = document.getElementById('statusChart').getContext('2d');
    const statusData = @json($statusDistribution);
    
    statusChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(statusData),
            datasets: [{
                data: Object.values(statusData),
                backgroundColor: [
                    '#f39c12', // Under Investigation - Orange
                    '#27ae60', // True - Green
                    '#e74c3c', // Fake - Red
                    '#95a5a6', // Rejected - Gray
                    '#3498db'  // Reassigned - Blue
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

// Event listeners
function setupEventListeners() {
    // Filter change listeners
    document.getElementById('statusFilter').addEventListener('change', applyFilters);
    document.getElementById('dateFilter').addEventListener('change', applyFilters);
    
    // Status dropdown change listener to hide/show upload section
    const statusSelect = document.getElementById('status');
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            toggleUploadSection(this.value);
        });
    }
}

// Function to toggle upload section based on status selection
function toggleUploadSection(selectedStatus) {
    // Find upload section by looking for the supporting_document input and getting its parent
    const uploadInput = document.getElementById('supporting_document');
    const uploadGroup = uploadInput ? uploadInput.closest('.form-group') : null;
    
    // Find status comment section
    const statusCommentInput = document.getElementById('status_comment');
    const statusCommentGroup = statusCommentInput ? statusCommentInput.closest('.form-group') : null;
    const statusCommentLabel = statusCommentGroup ? statusCommentGroup.querySelector('label') : null;
    
    if (selectedStatus === 'Rejected') {
        // Hide upload section for rejection
        if (uploadGroup) {
            uploadGroup.style.display = 'none';
        }
        // Update label for status comment to indicate it's for justification
        if (statusCommentLabel) {
            statusCommentLabel.innerHTML = 'Rejection Justification *';
            statusCommentLabel.style.color = '#e74c3c';
        }
        // Make status comment required for rejection
        if (statusCommentInput) {
            statusCommentInput.required = true;
            statusCommentInput.placeholder = 'Please provide a reason for rejecting this case...';
        }
    } else {
        // Show upload section for other statuses
        if (uploadGroup) {
            uploadGroup.style.display = 'block';
        }
        // Reset label for status comment
        if (statusCommentLabel) {
            statusCommentLabel.innerHTML = 'Investigation Notes';
            statusCommentLabel.style.color = '';
        }
        // Make status comment optional for other statuses
        if (statusCommentInput) {
            statusCommentInput.required = false;
            statusCommentInput.placeholder = 'Add any investigation notes, findings, or comments...';
        }
    }
}

// Tab management
function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Remove active class from all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab
    const targetTab = document.getElementById(tabName + 'Tab');
    if (targetTab) {
        targetTab.classList.add('active');
    }
    
    // Find and activate the corresponding button
    const buttons = document.querySelectorAll('.tab-btn');
    buttons.forEach(btn => {
        if (btn.textContent.toLowerCase().includes(tabName) || 
            btn.onclick && btn.onclick.toString().includes(tabName)) {
            btn.classList.add('active');
        }
    });
    
    currentTab = tabName;
    
    // Update sidebar navigation active state
    document.querySelectorAll('.nav-item').forEach(item => {
        const link = item.querySelector('a');
        if (link && link.getAttribute('href').includes('#' + tabName)) {
            item.classList.add('active');
        } else if (!link.getAttribute('href').includes('#')) {
            // Keep dashboard link active if it's not a tab-specific link
            if (item.querySelector('a[href*="dashboard"]') && !item.querySelector('a[href*="#"]')) {
                // Only remove active if we're navigating to a specific tab
                if (tabName !== 'dashboard') {
                    item.classList.remove('active');
                }
            } else {
                item.classList.remove('active');
            }
        } else {
            item.classList.remove('active');
        }
    });
}

// Modal management
function openModal(modalId) {
    document.getElementById(modalId).style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Status update functionality
function updateStatus(inquiryId) {
    document.getElementById('updateInquiryId').value = inquiryId;
    
    // Reset form and upload section visibility
    const form = document.getElementById('statusUpdateForm');
    if (form) {
        form.reset();
        document.getElementById('updateInquiryId').value = inquiryId; // Reset clears this, so set it again
    }
    
    // Reset upload section to visible (default state)
    toggleUploadSection('');
    
    openModal('statusUpdateModal');
}

function submitStatusUpdate() {
    console.log('submitStatusUpdate called');
    
    const form = document.getElementById('statusUpdateForm');
    const formData = new FormData(form);
    const inquiryId = formData.get('inquiryId');
    const status = formData.get('status');
    const statusComment = formData.get('status_comment');
    
    console.log('Form data:', {
        inquiryId: inquiryId,
        status: status,
        reviewing_officer: formData.get('reviewing_officer')
    });
    
    if (!inquiryId) {
        showNotification('Error: No inquiry ID found', 'error');
        return;
    }
    
    // Validate rejection requires justification comment
    if (status === 'Rejected' && (!statusComment || statusComment.trim() === '')) {
        showNotification('Please provide a justification for rejecting this case', 'error');
        return;
    }
    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    formData.append('_token', csrfToken);
    
    // Show loading state
    const submitButton = document.querySelector('.form-actions .btn-primary');
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    submitButton.disabled = true;
    
    console.log('Submitting status update with file upload...');
    
    fetch(`/agency/inquiry/${inquiryId}/status`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: formData // Send FormData directly for file upload
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            closeModal('statusUpdateModal');
            
            // Show different messages based on whether it's a rejection (rebounce)
            if (data.is_rejection) {
                showNotification('Case rejected and returned to MCMC for reassignment!', 'warning');
            } else {
                showNotification('Status updated successfully!', 'success');
            }
            
            // Reload the page to refresh all data and maintain button visibility
            setTimeout(() => {
                window.location.reload();
            }, 1000); // Give user time to see the success message
            
            // Reset form
            document.getElementById('statusUpdateForm').reset();
        } else {
            showNotification(data.error || 'Failed to update status', 'error');
            console.error('Update failed:', data);
        }
    })
    .catch(error => {
        console.error('Network error:', error);
        showNotification('Network error occurred: ' + error.message, 'error');
    })
    .finally(() => {
        // Restore button state
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    });
}

// View inquiry details
function viewInquiryDetails(inquiryId) {
    // Show loading in modal
    document.getElementById('inquiryDetailsContent').innerHTML = '<div class="spinner"></div>';
    openModal('inquiryDetailsModal');
    
    fetch(`/agency/inquiry/${inquiryId}/details`)
    .then(response => response.json())
    .then(data => {
        if (data.inquiry) {
            displayInquiryDetails(data);
        } else {
            document.getElementById('inquiryDetailsContent').innerHTML = 
                '<div class="alert alert-danger">Failed to load inquiry details</div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('inquiryDetailsContent').innerHTML = 
            '<div class="alert alert-danger">Network error occurred</div>';
    });
}

function displayInquiryDetails(data) {
    const inquiry = data.inquiry;
    const statusHistory = data.statusHistory;
    
    let html = `
        <div class="inquiry-details">
            <div class="detail-section">
                <h4>Inquiry Information</h4>
                <div class="detail-grid">
                    <div class="detail-item">
                        <label>Inquiry ID:</label>
                        <span>#${inquiry.inquiryId}</span>
                    </div>
                    <div class="detail-item">
                        <label>Title:</label>
                        <span>${inquiry.title}</span>
                    </div>
                    <div class="detail-item">
                        <label>Submission Date:</label>
                        <span>${new Date(inquiry.submission_date).toLocaleDateString()}</span>
                    </div>
                    <div class="detail-item">
                        <label>Current Status:</label>
                        <span class="status-badge status-${inquiry.status.toLowerCase().replace(' ', '-')}">${inquiry.status}</span>
                    </div>
                </div>
                <div class="detail-item full-width">
                    <label>Description:</label>
                    <p>${inquiry.description}</p>
                </div>
                ${inquiry.evidence_details ? `
                <div class="detail-item full-width">
                    <label>Evidence Details:</label>
                    <p>${inquiry.evidence_details}</p>
                </div>
                ` : ''}
            </div>
            
            <div class="detail-section">
                <h4>Status History</h4>
                <div class="status-timeline">`;
    
    statusHistory.forEach(status => {
        html += `
            <div class="status-item">
                <div class="status-marker">
                    <i class="fas fa-${status.status === 'Under Investigation' ? 'search' : (status.status === 'True' ? 'check' : (status.status === 'Fake' ? 'times' : 'ban'))}"></i>
                </div>
                <div class="status-content">
                    <div class="status-header">
                        <span class="status-badge status-${status.status.toLowerCase().replace(' ', '-')}">${status.status}</span>
                        <span class="status-date">${new Date(status.updated_at).toLocaleString()}</span>
                    </div>                        <div class="status-agency">By: ${status.agency_name}</div>
                        ${status.reviewing_officer ? `<div class="status-officer">Reviewing Officer: ${status.reviewing_officer}</div>` : ''}
                        ${status.status_comment ? `<div class="status-comment">${status.status_comment}</div>` : ''}
                        ${status.supporting_document ? `
                            <div class="status-document">
                                <i class="fas fa-paperclip"></i> 
                                <a href="${status.document_url}" target="_blank" class="document-link">
                                    ${status.document_name}
                                </a>
                                <small class="text-muted">(Supporting Document)</small>
                            </div>
                        ` : ''}
                </div>
            </div>`;
    });
    
    html += `
                </div>
            </div>
        </div>`;
    
    document.getElementById('inquiryDetailsContent').innerHTML = html;
}

// Refresh dashboard data
function refreshDashboard() {
    // Update timestamp
    document.getElementById('lastUpdated').textContent = new Date().toLocaleString();
    
    // Refresh statistics
    fetch('/agency/dashboard/stats')
    .then(response => response.json())
    .then(data => {
        document.getElementById('totalAssigned').textContent = data.total_assigned;
        document.getElementById('underInvestigation').textContent = data.under_investigation;
        document.getElementById('resolved').textContent = data.resolved;
        document.getElementById('resolutionRate').textContent = data.resolution_rate + '%';
        
        // Update chart
        if (statusChart) {
            statusChart.data.datasets[0].data = [
                data.under_investigation,
                data.verified_true,
                data.identified_fake,
                data.rejected
            ];
            statusChart.update();
        }
    })
    .catch(error => {
        console.error('Error refreshing stats:', error);
    });
}

// Filter functionality
function applyFilters() {
    const statusFilter = document.getElementById('statusFilter').value;
    const dateFilter = document.getElementById('dateFilter').value;
    
    // Apply filters to current visible table
    const currentTable = document.querySelector(`#${currentTab}InquiriesTable, #${currentTab}Tab .inquiries-table`);
    if (!currentTable) return;
    
    const rows = currentTable.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        let showRow = true;
        
        // Status filter
        if (statusFilter) {
            const statusBadge = row.querySelector('.status-badge');
            if (statusBadge && !statusBadge.textContent.includes(statusFilter)) {
                showRow = false;
            }
        }
        
        // Date filter
        if (dateFilter && showRow) {
            const dateCell = row.cells[2]; // Assuming date is in 3rd column
            if (dateCell) {
                const rowDate = new Date(dateCell.textContent);
                const now = new Date();
                
                switch (dateFilter) {
                    case 'today':
                        showRow = rowDate.toDateString() === now.toDateString();
                        break;
                    case 'week':
                        const weekAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
                        showRow = rowDate >= weekAgo;
                        break;
                    case 'month':
                        const monthAgo = new Date(now.getFullYear(), now.getMonth() - 1, now.getDate());
                        showRow = rowDate >= monthAgo;
                        break;
                }
            }
        }
        
        row.style.display = showRow ? '' : 'none';
    });
}

function resetFilters() {
    document.getElementById('statusFilter').value = '';
    document.getElementById('dateFilter').value = '';
    applyFilters();
}

// Notification system
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Auto-refresh every 30 seconds
setInterval(refreshDashboard, 30000);
</script>

<style>
/* Additional Modal Styles for Inquiry Details */
.inquiry-details {
    max-height: 70vh;
    overflow-y: auto;
}

.detail-section {
    margin-bottom: 30px;
}

.detail-section h4 {
    color: #2c3e50;
    margin-bottom: 15px;
    padding-bottom: 8px;
    border-bottom: 2px solid #3498db;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
    margin-bottom: 15px;
}

.detail-item {
    display: flex;
    flex-direction: column;
}

.detail-item.full-width {
    grid-column: 1 / -1;
}

.detail-item label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 5px;
    font-size: 0.9em;
}

.detail-item span,
.detail-item p {
    color: #2c3e50;
    margin: 0;
}

.status-timeline {
    position: relative;
}

.status-timeline::before {
    content: '';
    position: absolute;
    left: 20px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.status-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 20px;
    position: relative;
}

.status-marker {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #3498db;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    flex-shrink: 0;
    position: relative;
    z-index: 1;
}

.status-content {
    flex: 1;
    padding: 10px 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 3px solid #3498db;
}

.status-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.status-date {
    font-size: 0.8em;
    color: #6c757d;
}

.status-agency {
    font-size: 0.9em;
    color: #495057;
    font-weight: 600;
    margin-bottom: 8px;
}

.status-officer {
    font-size: 0.8em;
    color: #6c757d;
    font-style: italic;
    margin-bottom: 8px;
}

.status-comment {
    background: white;
    padding: 10px;
    border-radius: 6px;
    font-size: 0.9em;
    color: #2c3e50;
    border: 1px solid #dee2e6;
}

.status-document {
    background: #f8f9fa;
    padding: 8px 12px;
    border-radius: 6px;
    margin-top: 8px;
    border: 1px solid #e9ecef;
}

.document-link {
    color: #3498db;
    text-decoration: none;
    font-weight: 500;
}

.document-link:hover {
    color: #2980b9;
    text-decoration: underline;
}

/* Notification Styles */
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    padding: 15px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-width: 300px;
    z-index: 1100;
    animation: slideInRight 0.3s ease;
    border-left: 4px solid #3498db;
}

.notification-success {
    border-left-color: #27ae60;
}

.notification-error {
    border-left-color: #e74c3c;
}

.notification-content {
    display: flex;
    align-items: center;
    gap: 10px;
}

.notification-content i {
    font-size: 1.2em;
}

.notification-success i {
    color: #27ae60;
}

.notification-error i {
    color: #e74c3c;
}

.notification-close {
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    padding: 5px;
    margin-left: 15px;
}

.notification-close:hover {
    color: #dc3545;
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
</style>