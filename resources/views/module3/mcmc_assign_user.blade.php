<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MCMC - Assign Inquiry to Agency</title>
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

        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .inquiry-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .form-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-badge.pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-badge.assigned {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .evidence-item {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 10px;
        }

        .evidence-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            margin-right: 12px;
        }

        .evidence-icon.image {
            background-color: #e7f3ff;
            color: #0066cc;
        }

        .evidence-icon.document {
            background-color: #fff2e7;
            color: #cc6600;
        }

        .evidence-icon.video {
            background-color: #f0e7ff;
            color: #6600cc;
        }

        .evidence-icon.other {
            background-color: #e7ffe7;
            color: #00cc00;
        }

        .btn-back {
            background-color: #6c757d;
            border-color: #6c757d;
            color: white;
        }

        .btn-back:hover {
            background-color: #5a6268;
            border-color: #545b62;
            color: white;
        }
    </style>
</head>

<body>
    @include('layouts.sidebarMcmc')

    <div class="main-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-1">
                            <i class="fas fa-user-check me-2"></i>Assign Inquiry to Agency
                        </h2>
                        <p class="mb-0 opacity-75">Review inquiry details and assign to appropriate agency</p>
                    </div>
                    <a href="{{ route('mcmc.inquiries') }}" class="btn btn-back">
                        <i class="fas fa-arrow-left me-1"></i>Back to Inquiries
                    </a>
                </div>
            </div>

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

            <div class="row">
                <!-- Inquiry Details -->
                <div class="col-md-8">
                    <div class="inquiry-card">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>Inquiry Details
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-3"><strong>Inquiry ID:</strong></div>
                                <div class="col-sm-9">#{{ str_pad($inquiry->id, 4, '0', STR_PAD_LEFT) }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-3"><strong>Title:</strong></div>
                                <div class="col-sm-9">{{ $inquiry->title }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-3"><strong>Description:</strong></div>
                                <div class="col-sm-9">{{ $inquiry->description }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-3"><strong>Status:</strong></div>
                                <div class="col-sm-9">
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
                            <div class="row mb-3">
                                <div class="col-sm-3"><strong>Submitted:</strong></div>
                                <div class="col-sm-9">{{ date('M d, Y', strtotime($inquiry->submission_date)) }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-3"><strong>Submitter:</strong></div>
                                <div class="col-sm-9">
                                    <strong>{{ $inquiry->user_info->name }}</strong><br>
                                    <small class="text-muted">{{ $inquiry->user_info->email }}</small><br>
                                    <small class="text-muted">{{ $inquiry->user_info->contact }}</small>
                                </div>
                            </div>

                            @if($inquiry->evidence_count > 0)
                            <div class="row">
                                <div class="col-sm-3"><strong>Evidence Files:</strong></div>
                                <div class="col-sm-9">
                                    @foreach($inquiry->evidence_files as $file)
                                    <div class="evidence-item d-flex align-items-center">
                                        <div class="evidence-icon {{ $file['type'] }}">
                                            @if($file['type'] === 'image')
                                            <i class="fas fa-image"></i>
                                            @elseif($file['type'] === 'document')
                                            <i class="fas fa-file-text"></i>
                                            @elseif($file['type'] === 'video')
                                            <i class="fas fa-video"></i>
                                            @else
                                            <i class="fas fa-file"></i>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold">{{ $file['name'] }}</div>
                                            <small class="text-muted">{{ ucfirst($file['type']) }} file</small>
                                        </div>
                                        <a href="{{ $file['url'] }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </div>
                                    @endforeach

                                    @if($inquiry->evidence_url)
                                    <div class="evidence-item d-flex align-items-center">
                                        <div class="evidence-icon other">
                                            <i class="fas fa-link"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold">Additional Evidence URL</div>
                                            <small class="text-muted">External link</small>
                                        </div>
                                        <a href="{{ $inquiry->evidence_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Assignment Form -->
                <div class="col-md-4">
                    <div class="form-card">
                        <h5 class="mb-4">
                            <i class="fas fa-user-cog me-2"></i>
                            {{ $inquiry->is_assigned ? 'Update Assignment' : 'Assign to Agency' }}
                        </h5>
                        <form action="{{ route('module3.mcmc.process.assign') }}" method="POST">
                            @csrf
                            <input type="hidden" name="inquiry_id" value="{{ $inquiry->id }}">

                            <!-- Agency Type Selection -->
                            <div class="mb-3">
                                <label for="agency_type" class="form-label">Select Agency Type <span class="text-danger">*</span></label>
                                <select name="agency_type" id="agency_type" class="form-select" required>
                                    <option value="">Choose Agency Type...</option>
                                    @foreach($agencyTypes as $type)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Agency Name Selection (Initially Hidden) -->
                            <div class="mb-3" id="agency_select_container" style="display: none;">
                                <label for="agency_id" class="form-label">Select Agency <span class="text-danger">*</span></label>
                                <select name="agency_id" id="agency_id" class="form-select" required>
                                    <option value="">Choose Agency...</option>
                                </select>
                                <div id="agency_loading" class="form-text" style="display: none;">
                                    <i class="fas fa-spinner fa-spin me-1"></i>Loading agencies...
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="mcmc_comments" class="form-label">MCMC Comments <span class="text-danger">*</span></label>
                                <textarea name="mcmc_comments" id="mcmc_comments" class="form-control" rows="4"
                                    placeholder="Enter validation comments and instructions for the agency..." required>{{ $inquiry->mcmc_comments }}</textarea>
                                <div class="form-text">Provide guidance for the agency on how to handle this inquiry.</div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success" id="submit_btn" disabled>
                                    <i class="fas fa-check me-2"></i>
                                    {{ $inquiry->is_assigned ? 'Update Assignment' : 'Assign to Agency' }}
                                </button>
                                <a href="{{ route('mcmc.inquiries') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Cascading dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            const agencyTypeSelect = document.getElementById('agency_type');
            const agencySelectContainer = document.getElementById('agency_select_container');
            const agencySelect = document.getElementById('agency_id');
            const agencyLoading = document.getElementById('agency_loading');
            const submitBtn = document.getElementById('submit_btn');
            const mcmcComments = document.getElementById('mcmc_comments');

            // Function to check if form is ready for submission
            function checkFormReady() {
                const typeSelected = agencyTypeSelect.value !== '';
                const agencySelected = agencySelect.value !== '';
                const commentsEntered = mcmcComments.value.trim() !== '';

                submitBtn.disabled = !(typeSelected && agencySelected && commentsEntered);
            }

            // Handle agency type change
            agencyTypeSelect.addEventListener('change', function() {
                const selectedType = this.value;

                if (selectedType) {
                    // Show loading
                    agencyLoading.style.display = 'block';
                    agencySelectContainer.style.display = 'block';
                    agencySelect.innerHTML = '<option value="">Loading...</option>';
                    agencySelect.disabled = true;

                    // Fetch agencies for selected type
                    fetch('/module3/mcmc/agencies-by-type', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                agency_type: selectedType
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            agencyLoading.style.display = 'none';
                            agencySelect.innerHTML = '<option value="">Choose Agency...</option>';

                            if (data.success && data.agencies) {
                                data.agencies.forEach(agency => {
                                    const option = document.createElement('option');
                                    option.value = agency.agencyId;
                                    option.textContent = agency.agency_name;
                                    agencySelect.appendChild(option);
                                });
                                agencySelect.disabled = false;
                            } else {
                                agencySelect.innerHTML = '<option value="">No agencies found</option>';
                            }

                            checkFormReady();
                        })
                        .catch(error => {
                            console.error('Error fetching agencies:', error);
                            agencyLoading.style.display = 'none';
                            agencySelect.innerHTML = '<option value="">Error loading agencies</option>';
                            checkFormReady();
                        });
                } else {
                    // Hide agency select if no type selected
                    agencySelectContainer.style.display = 'none';
                    agencySelect.innerHTML = '<option value="">Choose Agency...</option>';
                    checkFormReady();
                }
            });

            // Handle agency selection change
            agencySelect.addEventListener('change', checkFormReady);

            // Handle comments change
            mcmcComments.addEventListener('input', checkFormReady);

            // Initial form state check
            checkFormReady();
        });
    </script>
</body>

</html>