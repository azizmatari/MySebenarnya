@include('layouts.sidebarAgency')

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    .dashboard-content {
        margin-left: 250px;
        min-height: 100vh;
        background-color: #f8f9fa;
    }
    
    .status-badge {
        font-size: 0.75rem;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
    }
    
    .card-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    
    .assignment-card {
        border-left: 4px solid #007bff;
        margin-bottom: 1rem;
    }
    
    .btn-action {
        margin: 0.25rem;
    }
</style>

<div class="dashboard-content">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-exchange-alt me-2"></i>Assignment Management
                </h1>
                <p class="text-muted">Review and manage inquiry assignments from MCMC</p>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm card-hover">
                    <div class="card-body text-center">
                        <div class="display-4 text-warning mb-2">{{ $pendingAssignments->count() }}</div>
                        <h6 class="card-title text-muted">Pending Assignments</h6>
                        <p class="small text-muted">Require your review and decision</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm card-hover">
                    <div class="card-body text-center">
                        <div class="display-4 text-success mb-2">{{ $acceptedAssignments->count() }}</div>
                        <h6 class="card-title text-muted">Accepted Assignments</h6>
                        <p class="small text-muted">Currently active in your dashboard</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Assignments -->
        @if($pendingAssignments->count() > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-clock me-2"></i>Pending Assignments - Action Required
                        </h5>
                    </div>
                    <div class="card-body">
                        @foreach($pendingAssignments as $assignment)
                        <div class="assignment-card card mb-3">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h6 class="card-title mb-2">
                                            <strong>Case ID:</strong> {{ $assignment->inquiry->inquiryId }}
                                        </h6>
                                        <p class="card-text mb-2">
                                            <strong>Title:</strong> {{ $assignment->inquiry->title ?? 'N/A' }}
                                        </p>
                                        <p class="card-text mb-2">
                                            <strong>Description:</strong> 
                                            {{ Str::limit($assignment->inquiry->description ?? 'No description available', 150) }}
                                        </p>
                                        <p class="card-text mb-2">
                                            <strong>Assigned by:</strong> {{ $assignment->mcmcStaff->mcmcName ?? 'MCMC Staff' }}
                                        </p>
                                        <p class="card-text mb-0">
                                            <strong>Assignment Date:</strong> 
                                            {{ $assignment->assignDate ? $assignment->assignDate->format('M d, Y') : 'N/A' }}
                                        </p>
                                        @if($assignment->mcmcComments)
                                        <div class="mt-2">
                                            <strong>MCMC Comments:</strong>
                                            <div class="alert alert-info alert-sm mt-1">
                                                {{ $assignment->mcmcComments }}
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <div class="d-flex flex-column gap-2">
                                            <button class="btn btn-success btn-action" 
                                                    onclick="acceptAssignment({{ $assignment->assignmentId }})">
                                                <i class="fas fa-check me-1"></i>Accept Assignment
                                            </button>
                                            <button class="btn btn-warning btn-action" 
                                                    onclick="requestReassignment({{ $assignment->assignmentId }})">
                                                <i class="fas fa-exchange-alt me-1"></i>Request Reassignment
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle fa-2x mb-3"></i>
                    <h5>No Pending Assignments</h5>
                    <p class="mb-0">You currently have no pending assignments from MCMC.</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Recent Accepted Assignments -->
        @if($acceptedAssignments->count() > 0)
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-check-circle me-2"></i>Recently Accepted Assignments
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Case ID</th>
                                        <th>Title</th>
                                        <th>Assigned Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($acceptedAssignments as $assignment)
                                    <tr>
                                        <td><strong>{{ $assignment->inquiry->inquiryId }}</strong></td>
                                        <td>{{ Str::limit($assignment->inquiry->title ?? 'N/A', 40) }}</td>
                                        <td>{{ $assignment->assignDate ? $assignment->assignDate->format('M d, Y') : 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-success">Accepted</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('agency.dashboard') }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye me-1"></i>View in Dashboard
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Reassignment Request Modal -->
<div class="modal fade" id="reassignmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Reassignment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="reassignmentForm">
                    <div class="mb-3">
                        <label class="form-label">Reason for Reassignment <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="reassignment_reason" rows="4" 
                                  placeholder="Please explain why this inquiry should be reassigned to another agency..."
                                  required></textarea>
                        <div class="form-text">
                            Please provide a detailed explanation for why this inquiry falls outside your agency's jurisdiction.
                        </div>
                    </div>
                    <input type="hidden" name="assignment_id" id="reassignmentAssignmentId">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="submitReassignmentRequest()">
                    <i class="fas fa-paper-plane me-1"></i>Submit Request
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Get CSRF token
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                  '{{ csrf_token() }}';

function acceptAssignment(assignmentId) {
    if (!confirm('Are you sure you want to accept this assignment? You will be responsible for investigating this case.')) {
        return;
    }

    fetch(`/agency/assignment/${assignmentId}/accept`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Assignment accepted successfully! Redirecting to dashboard...');
            window.location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to accept assignment'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while accepting the assignment.');
    });
}

function requestReassignment(assignmentId) {
    document.getElementById('reassignmentAssignmentId').value = assignmentId;
    new bootstrap.Modal(document.getElementById('reassignmentModal')).show();
}

function submitReassignmentRequest() {
    const form = document.getElementById('reassignmentForm');
    const formData = new FormData(form);
    const assignmentId = formData.get('assignment_id');
    const reason = formData.get('reassignment_reason');

    if (!reason || reason.trim().length < 10) {
        alert('Please provide a detailed reason for reassignment (at least 10 characters).');
        return;
    }

    fetch(`/agency/assignment/${assignmentId}/reassignment`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            reassignment_reason: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Reassignment request submitted successfully! MCMC will review your request.');
            bootstrap.Modal.getInstance(document.getElementById('reassignmentModal')).hide();
            window.location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to submit reassignment request'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while submitting the reassignment request.');
    });
}
</script>
