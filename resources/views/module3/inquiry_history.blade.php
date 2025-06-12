<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Inquiry History - MySebenarnya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }

        .main-content {
            margin-left: 260px;
            margin-top: 50px;
            padding: 20px;
            min-height: 100vh;
        }

        .page-header {
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 600;
            color: #333;
            margin: 0;
        }

        .page-description {
            color: #6c757d;
            font-size: 1rem;
            margin-top: 5px;
        }

        .placeholder-container {
            text-align: center;
            padding: 80px 0;
        }

        .placeholder-icon {
            font-size: 5rem;
            color: #dee2e6;
            margin-bottom: 20px;
        }

        .placeholder-text {
            font-size: 1.5rem;
            color: #6c757d;
            margin-bottom: 10px;
        }

        .placeholder-subtext {
            color: #adb5bd;
            max-width: 500px;
            margin: 0 auto 30px;
        }
    </style>
</head>

<body>
    <!-- Include Sidebar -->
    @include('layouts.sidebarPublic')

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">My Inquiry History</h1>
                <p class="page-description">Track the status of your submitted inquiries</p>
            </div>

            <!-- Placeholder content - will be replaced with actual inquiry history -->
            <div class="placeholder-container">
                <i class="fas fa-history placeholder-icon"></i>
                <h2 class="placeholder-text">Coming Soon</h2>
                <p class="placeholder-subtext">
                    This feature is currently under development. You will soon be able to view and track all your submitted inquiries here.
                </p>
                <a href="{{ route('module3.status') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>