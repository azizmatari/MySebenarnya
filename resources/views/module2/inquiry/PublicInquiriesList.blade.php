<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Public Inquiries - MySebenarnya</title>
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
                        <div class="card">                            <div class="card-header bg-primary text-white">
                                <h3 class="mb-0">
                                    <i class="material-icons" style="vertical-align: middle; margin-right: 10px;">public</i>
                                    Public Inquiries
                                </h3>
                            </div>
                            <div class="card-body">                                <!-- Search and Filter Section -->
                                <div class="row mb-4">
                                    <div class="col-md-8">
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="material-icons" style="font-size: 18px;">search</i>
                                            </span>
                                            <input type="text" id="searchInput" class="form-control" placeholder="Search inquiries by title or description...">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <select id="resultFilter" class="form-select">
                                            <option value="">All Results</option>
                                            <option value="true">True</option>
                                            <option value="false">Fake</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Statistics Cards -->                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <div class="card bg-info text-white">
                                            <div class="card-body text-center">
                                                <i class="material-icons" style="font-size: 32px;">assignment</i>
                                                <h4 class="mt-2">{{ $publicInquiries->count() }}</h4>
                                                <p class="mb-0">Total Inquiries</p>
                                            </div>
                                        </div>
                                    </div>                                    <div class="col-md-4">
                                        <div class="card bg-success text-white">
                                            <div class="card-body text-center">
                                                <i class="material-icons" style="font-size: 32px;">verified</i>
                                                <h4 class="mt-2">{{ $publicInquiries->where('result', 'true')->count() }}</h4>
                                                <p class="mb-0">True</p>
                                            </div>
                                        </div>
                                    </div><div class="col-md-4">
                                        <div class="card bg-danger text-white">
                                            <div class="card-body text-center">
                                                <i class="material-icons" style="font-size: 32px;">error</i>
                                                <h4 class="mt-2">{{ $publicInquiries->where('result', 'false')->count() }}</h4>
                                                <p class="mb-0">Fake</p>
                                            </div>                                        </div>
                                    </div>
                                </div>

                                <!-- Inquiries List -->
                                <div id="inquiriesList">
                                    @if($publicInquiries && $publicInquiries->count() > 0)
                                        <div class="row">
                                            @foreach($publicInquiries as $inquiry)                                                <div class="col-12 mb-3 inquiry-item" 
                                                     data-result="{{ $inquiry->result }}"
                                                     data-search="{{ strtolower($inquiry->title . ' ' . $inquiry->description) }}">
                                                    <div class="card border-left-{{ $inquiry->result === 'true' ? 'success' : 'danger' }} shadow h-100 py-2">
                                                        <div class="card-body">
                                                            <div class="row no-gutters align-items-center">
                                                                <div class="col mr-2">
                                                                    <div class="d-flex justify-content-between align-items-start">
                                                                        <div class="flex-grow-1">                                                                            <div class="d-flex align-items-center mb-2">
                                                                                <h5 class="font-weight-bold text-primary mb-0 me-3">
                                                                                    {{ $inquiry->title }}
                                                                                </h5>
                                                                            </div>
                                                                            
                                                                            <p class="text-muted mb-2">
                                                                                {{ Str::limit($inquiry->description, 150) }}
                                                                            </p>
                                                                                                              <div class="row text-muted small">                                                <div class="col-md-6">
                                                    <i class="material-icons" style="font-size: 14px; vertical-align: middle;">person</i>
                                                    <strong>By:</strong> {{ $inquiry->anonymized_user }}
                                                </div>
                                            </div>
                                                                            
                                                                            <div class="row text-muted small mt-1">
                                                                                <div class="col-md-6">
                                                                                    <i class="material-icons" style="font-size: 14px; vertical-align: middle;">schedule</i>
                                                                                    <strong>Submitted:</strong> {{ \Carbon\Carbon::parse($inquiry->submission_date)->format('M d, Y') }}
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    <i class="material-icons" style="font-size: 14px; vertical-align: middle;">check_circle</i>
                                                                                    <strong>Completed:</strong> {{ \Carbon\Carbon::parse($inquiry->completion_date)->format('M d, Y') }}
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        
                                                                        <div class="text-right">
                                                                            <div class="d-flex flex-column align-items-end gap-2">                                                                @if($inquiry->result === 'true')
                                                                    <span class="badge bg-success text-white px-3 py-2">
                                                                        <i class="material-icons" style="font-size: 14px; vertical-align: middle;">verified</i>
                                                                        True
                                                                    </span>
                                                                @elseif($inquiry->result === 'false')
                                                                    <span class="badge bg-danger text-white px-3 py-2">
                                                                        <i class="material-icons" style="font-size: 14px; vertical-align: middle;">error</i>
                                                                        Fake
                                                                    </span>
                                                                @endif
                                                                                
                                                                                <span class="badge bg-primary text-white px-2 py-1 small">
                                                                                    ID: #{{ str_pad($inquiry->id, 4, '0', STR_PAD_LEFT) }}
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Admin Response Preview -->
                                                            @if($inquiry->admin_response)
                                                                <div class="row mt-3">
                                                                    <div class="col-12">
                                                                        <div class="alert alert-{{ $inquiry->result === 'true' ? 'success' : 'warning' }} mb-0">
                                                                            <div class="d-flex justify-content-between align-items-start">                                                                                <div class="flex-grow-1">
                                                                                    <strong>
                                                                                        <i class="material-icons" style="font-size: 16px; vertical-align: middle; margin-right: 5px;">admin_panel_settings</i>
                                                                                        Official Response:
                                                                                    </strong>
                                                                                    <p class="mb-0 mt-1">{{ Str::limit($inquiry->admin_response, 200) }}</p>
                                                                                </div>
                                                                                <div class="d-flex gap-2 ms-3">
                                                                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="viewFullResponse({{ $inquiry->id }})">
                                                                                        <i class="material-icons" style="font-size: 14px; vertical-align: middle;">visibility</i>
                                                                                        View Full
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <!-- Empty State -->
                                        <div class="text-center py-5">
                                            <div class="empty-state">
                                                <i class="material-icons text-muted" style="font-size: 80px;">public_off</i>
                                                <h4 class="text-muted mt-3">No Public Inquiries Found</h4>
                                                <p class="text-muted mb-4">There are currently no public inquiries to display.</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <!-- No Results Message -->
                                <div id="noResults" class="text-center py-5" style="display: none;">
                                    <i class="material-icons text-muted" style="font-size: 80px;">search_off</i>
                                    <h4 class="text-muted mt-3">No Matching Inquiries</h4>
                                    <p class="text-muted mb-4">Try adjusting your search criteria or filters.</p>
                                    <button class="btn btn-primary" onclick="clearFilters()">
                                        <i class="material-icons" style="vertical-align: middle; margin-right: 8px;">clear</i>
                                        Clear Filters
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Full Response Modal -->
    <div class="modal fade" id="responseModal" tabindex="-1" aria-labelledby="responseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="responseModalLabel">
                        <i class="material-icons" style="vertical-align: middle; margin-right: 8px;">admin_panel_settings</i>
                        Official Response
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="responseModalBody">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>    </div>

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
        
        .border-left-success {
            border-left: 4px solid #28a745 !important;
        }
        
        .border-left-danger {
            border-left: 4px solid #dc3545 !important;
        }
        
        .badge {
            font-size: 0.875rem;
            border-radius: 6px;
        }
        
        .empty-state {
            max-width: 400px;
            margin: 0 auto;
        }
        
        .material-icons {
            font-size: 24px;
        }
        
        .inquiry-item {
            transition: opacity 0.3s ease;
        }
        
        .inquiry-item.hidden {
            display: none !important;
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding-top: 60px;
            }
            
            .content {
                padding: 15px;
            }
            
            .row .col-md-3, .row .col-md-4, .row .col-md-6 {
                margin-bottom: 10px;
            }
        }
    </style>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Store inquiry data for modals
        const publicInquiries = @json($publicInquiries);
          // Search and filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const resultFilter = document.getElementById('resultFilter');
            
            searchInput.addEventListener('input', filterInquiries);
            resultFilter.addEventListener('change', filterInquiries);
        });
        
        function filterInquiries() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            const result = document.getElementById('resultFilter').value;
            
            const inquiryItems = document.querySelectorAll('.inquiry-item');
            let visibleCount = 0;
            
            inquiryItems.forEach(item => {
                const itemSearch = item.dataset.search;
                const itemResult = item.dataset.result;
                
                const matchesSearch = !search || itemSearch.includes(search);
                const matchesResult = !result || itemResult === result;
                
                if (matchesSearch && matchesResult) {
                    item.style.display = 'block';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });
            
            // Show/hide no results message
            const noResults = document.getElementById('noResults');
            if (visibleCount === 0) {
                noResults.style.display = 'block';
            } else {
                noResults.style.display = 'none';
            }
        }
        
        function clearFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('resultFilter').value = '';
            filterInquiries();
        }
        
        function viewFullResponse(inquiryId) {
            const inquiry = publicInquiries.find(i => i.id === inquiryId);
            if (!inquiry) return;
            
            const modalBody = document.getElementById('responseModalBody');
            modalBody.innerHTML = `
                <div class="row">
                    <div class="col-12">
                        <h6 class="text-primary">Inquiry Details</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted" style="width: 150px;">Inquiry ID:</td>
                                <td><strong>#${String(inquiry.id).padStart(4, '0')}</strong></td>
                            </tr>                            <tr>
                                <td class="text-muted">Title:</td>
                                <td><strong>${inquiry.title}</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Verification Result:</td>
                                <td><span class="badge bg-${inquiry.result === 'true' ? 'success' : 'danger'}">${inquiry.result === 'true' ? 'TRUE' : 'FAKE'}</span></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <h6 class="text-primary">Full Official Response</h6>
                        <div class="alert alert-${inquiry.result === 'true' ? 'success' : 'warning'}">
                            <i class="material-icons" style="vertical-align: middle; margin-right: 8px;">admin_panel_settings</i>
                            <strong>Response from Verification Team:</strong>
                            <p class="mt-2 mb-0">${inquiry.admin_response}</p>
                        </div>                    </div>
                </div>
            `;
              new bootstrap.Modal(document.getElementById('responseModal')).show();}
  
        
        // Search and Filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const resultFilter = document.getElementById('resultFilter');
            const inquiryItems = document.querySelectorAll('.inquiry-item');
            
            function filterInquiries() {
                const searchTerm = searchInput.value.toLowerCase();
                const resultValue = resultFilter.value;
                
                inquiryItems.forEach(item => {
                    const searchText = item.dataset.search || '';
                    const itemResult = item.dataset.result || '';
                    
                    const matchesSearch = !searchTerm || searchText.includes(searchTerm);
                    const matchesResult = !resultValue || itemResult === resultValue;
                    
                    if (matchesSearch && matchesResult) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
                
                // Update results count
                updateResultsCount();
            }
            
            function updateResultsCount() {
                const visibleItems = document.querySelectorAll('.inquiry-item[style*="block"], .inquiry-item:not([style*="none"])').length;
                const totalItems = inquiryItems.length;
                
                // You can add a results counter here if needed
                console.log(`Showing ${visibleItems} of ${totalItems} inquiries`);
            }
            
            // Add event listeners
            searchInput.addEventListener('input', filterInquiries);
            resultFilter.addEventListener('change', filterInquiries);
            
            // Initial filter
            filterInquiries();
        });
        
        // Auto-refresh functionality
        let lastActivity = Date.now();
        document.addEventListener('click', function() {
            lastActivity = Date.now();
        });
        
        // Refresh page if idle for more than 10 minutes
        setInterval(function() {
            if (Date.now() - lastActivity > 600000) {
                location.reload();
            }
        }, 60000);
    </script>
</body>
</html>
