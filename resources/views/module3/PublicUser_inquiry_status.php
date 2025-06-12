<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "MySebenarnya"; // Match your .env file

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Query to get inquiries with "Under Investigation" status and agency information
$sql = "SELECT 
            i.inquiryId,
            i.title,
            i.description,
            i.final_status,
            i.submission_date,
            a.agency_name,
            pu.userName as applicant_name
        FROM inquiry i
        INNER JOIN agency a ON i.agencyId = a.agencyId
        INNER JOIN publicuser pu ON i.userId = pu.userId
        WHERE i.final_status = 'Under Investigation'
        ORDER BY i.submission_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$inquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to truncate text
function truncateText($text, $length = 20) {
    if (strlen($text) > $length) {
        return substr($text, 0, $length) . '...';
    }
    return $text;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Inquiries - Status Tracking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .inquiry-card {
            border-left: 4px solid #ffc107;
            transition: all 0.3s ease;
        }
        .inquiry-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
            padding: 1rem;
        }
    </style>
</head>
<body class="bg-light">
    
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
                        <h3 class="mb-1"><?php echo count($inquiries); ?></h3>
                        <small>Active Inquiries</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-5">
        <?php if (empty($inquiries)): ?>
            <!-- No Inquiries Message -->
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No Active Inquiries</h4>
                <p class="text-muted">All inquiries have been processed or there are no new inquiries to display.</p>
            </div>
        <?php else: ?>
            <!-- Inquiries List -->
            <div class="row">
                <?php foreach ($inquiries as $inquiry): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card inquiry-card h-100">
                            <div class="card-header bg-white border-bottom">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h6 class="card-title mb-1 fw-bold text-primary">
                                        #<?php echo $inquiry['inquiryId']; ?> - <?php echo htmlspecialchars($inquiry['title']); ?>
                                    </h6>
                                    <span class="badge status-badge">
                                        <i class="fas fa-magnifying-glass me-1"></i>
                                        <?php echo $inquiry['final_status']; ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <div class="mb-3">
                                    <h6 class="text-muted mb-2">
                                        <i class="fas fa-file-text me-2"></i>Description:
                                    </h6>
                                    <p class="text-dark">
                                        <?php echo htmlspecialchars(truncateText($inquiry['description'], 20)); ?>
                                    </p>
                                </div>

                                <div class="mb-3">
                                    <h6 class="text-muted mb-2">
                                        <i class="fas fa-user me-2"></i>Applicant:
                                    </h6>
                                    <p class="text-dark"><?php echo htmlspecialchars($inquiry['applicant_name']); ?></p>
                                </div>

                                <div class="mb-3">
                                    <h6 class="text-muted mb-2">
                                        <i class="fas fa-building me-2"></i>Assigned Agency:
                                    </h6>
                                    <span class="agency-tag">
                                        <?php echo htmlspecialchars($inquiry['agency_name']); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="card-footer bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        Applied: <?php echo date('M d, Y', strtotime($inquiry['submission_date'])); ?>
                                    </small>
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewDetails(<?php echo $inquiry['inquiryId']; ?>)">
                                        <i class="fas fa-eye me-1"></i>View
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Summary Statistics -->
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
                                        <h3 class="text-warning"><?php echo count($inquiries); ?></h3>
                                        <p class="text-muted mb-0">Under Investigation</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border-end">
                                        <h3 class="text-info">
                                            <?php 
                                            $agencies = array_unique(array_column($inquiries, 'agency_name'));
                                            echo count($agencies); 
                                            ?>
                                        </h3>
                                        <p class="text-muted mb-0">Agencies Involved</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <h3 class="text-success">
                                        <?php 
                                        $recentInquiries = array_filter($inquiries, function($inquiry) {
                                            return strtotime($inquiry['submission_date']) > strtotime('-7 days');
                                        });
                                        echo count($recentInquiries); 
                                        ?>
                                    </h3>
                                    <p class="text-muted mb-0">This Week</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewDetails(inquiryId) {
            // You can implement this to show inquiry details in a modal or navigate to another page
            alert('View details for Inquiry #' + inquiryId);
            // Example: window.location.href = 'inquiry_details.php?id=' + inquiryId;
        }

        // Auto-refresh every 30 seconds to show real-time updates
        setInterval(function() {
            // Uncomment the line below to enable auto-refresh
            // location.reload();
        }, 30000);

        // Add fade-in animation
        document.addEventListener('DOMContentLoaded', function() {
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
        });
    </script>
</body>
</html>

<?php
// Close database connection
$pdo = null;
?>