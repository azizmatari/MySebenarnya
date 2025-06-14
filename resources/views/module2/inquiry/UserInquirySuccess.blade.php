<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inquiry Submitted Successfully - MySebenarnya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    @include('layouts.sidebarPublic')
    
    <!-- Main content area with proper positioning -->
    <div class="main-content">
        <div class="content">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header bg-success text-white text-center">
                                <h3 class="mb-0">
                                    <i class="material-icons" style="vertical-align: middle; margin-right: 10px; font-size: 32px;">check_circle</i>
                                    Inquiry Submitted Successfully!
                                </h3>
                            </div>
                            <div class="card-body text-center">
                                <div class="success-icon mb-4">
                                    <i class="material-icons text-success" style="font-size: 80px;">task_alt</i>
                                </div>
                                
                                <h4 class="text-success mb-3">Thank you for your submission!</h4>
                                
                                <div class="alert alert-info">
                                    <p class="mb-2"><strong>Inquiry ID:</strong> #{{ session('inquiry_id', 'N/A') }}</p>
                                    <p class="mb-0"><strong>Title:</strong> {{ session('title', 'Your Inquiry') }}</p>
                                </div>
                                
                                <div class="row text-start">
                                    <div class="col-md-6 mb-3">
                                        <div class="card border-primary">
                                            <div class="card-body">
                                                <h6 class="card-title text-primary">
                                                    <i class="material-icons" style="vertical-align: middle; margin-right: 8px;">schedule</i>
                                                    What happens next?
                                                </h6>
                                                <ul class="list-unstyled mb-0">
                                                    <li>✓ Your inquiry has been received</li>
                                                    <li>✓ It will be assigned to a verification team</li>
                                                    <li>✓ Our experts will analyze the evidence</li>
                                                    <li>✓ You'll receive updates on the status</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="card border-info">
                                            <div class="card-body">
                                                <h6 class="card-title text-info">
                                                    <i class="material-icons" style="vertical-align: middle; margin-right: 8px;">info</i>
                                                    Important Information
                                                </h6>
                                                <ul class="list-unstyled mb-0">
                                                    <li>• Processing time: 2-5 business days</li>
                                                    <li>• High urgency items are prioritized</li>
                                                    <li>• You can track status on dashboard</li>
                                                    <li>• We'll notify you of any updates</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-center mt-4">
                                    <a href="{{ route('module3.status') }}" class="btn btn-primary btn-lg me-md-2">
                                        <i class="material-icons" style="vertical-align: middle; margin-right: 8px;">dashboard</i>
                                        View Dashboard
                                    </a>
                                    <a href="{{ route('inquiry.create') }}" class="btn btn-outline-primary btn-lg">
                                        <i class="material-icons" style="vertical-align: middle; margin-right: 8px;">add_circle</i>
                                        Submit Another Inquiry
                                    </a>
                                </div>
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
        
        .success-icon {
            animation: bounce 1s ease-in-out;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }
        
        .alert {
            border-radius: 8px;
        }
        
        .material-icons {
            font-size: 24px;
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
        }
    </style>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>