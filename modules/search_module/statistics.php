<?php
require_once '../../config/db_connection.php';

// Get database connection
$conn = require '../../config/db_connection.php';

// Calculate statistics
try {
    // Total declarations (active officials)
    $stmt = $conn->query("SELECT COUNT(*) as total FROM people");
    $totalDeclarations = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total unique political affiliations
    $stmt = $conn->query("SELECT COUNT(DISTINCT political_affiliation) as total FROM people WHERE political_affiliation IS NOT NULL");
    $totalParties = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Declarations by political party
    $stmt = $conn->query("SELECT political_affiliation, COUNT(*) as count FROM people WHERE political_affiliation IS NOT NULL GROUP BY political_affiliation ORDER BY count DESC");
    $partyData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format party data for chart
    $partyLabels = array_map(function($item) { return $item['political_affiliation']; }, $partyData);
    $partyCounts = array_map(function($item) { return $item['count']; }, $partyData);

    // Calculate submission rate (assuming all records are submissions)
    $submissionRate = 100; // Since we're counting from submitted records

    // Asset Value Distribution
    $stmt = $conn->query("SELECT 
        CASE 
            WHEN CAST(REPLACE(REPLACE(amount, '€', ''), ',', '') AS DECIMAL(10,2)) < 10000 THEN 'Under €10,000'
            WHEN CAST(REPLACE(REPLACE(amount, '€', ''), ',', '') AS DECIMAL(10,2)) < 50000 THEN '€10,000 - €50,000'
            WHEN CAST(REPLACE(REPLACE(amount, '€', ''), ',', '') AS DECIMAL(10,2)) < 100000 THEN '€50,000 - €100,000'
            ELSE 'Over €100,000'
        END as value_range,
        COUNT(*) as count
        FROM liquid_assets 
        WHERE amount IS NOT NULL 
        GROUP BY value_range
        ORDER BY 
            CASE value_range
                WHEN 'Under €10,000' THEN 1
                WHEN '€10,000 - €50,000' THEN 2
                WHEN '€50,000 - €100,000' THEN 3
                ELSE 4
            END");
    $valueDistribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $valueLabels = array_map(function($item) { return $item['value_range']; }, $valueDistribution);
    $valueCounts = array_map(function($item) { return $item['count']; }, $valueDistribution);

} catch(PDOException $e) {
    // Handle any database errors
    error_log("Database Error: " . $e->getMessage());
    // Set default values in case of error
    $totalDeclarations = 0;
    $totalParties = 0;
    $partyLabels = [];
    $partyCounts = [];
    $submissionRate = 0;
    $valueLabels = [];
    $valueCounts = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics & Analysis - ΠΟΘΕΝ ΕΣΧΕΣ</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css"/>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom CSS -->
    <link href="../../assets/css/style.css" rel="stylesheet">
    <style>
        .lang-btn {
            width: 32px;
            height: 32px;
            padding: 0;
            border-radius: 50%;
            border: none;
            background: #e9ecef;
            color: #000000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .lang-btn:hover {
            background: #dee2e6;
            transform: scale(1.05);
        }
        .lang-btn.active {
            background: #000000;
            color: white;
        }
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 2rem;
        }
        .stats-summary {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-item {
            text-align: center;
            padding: 1rem;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #ED9635;
        }
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="../../index.php">
                <img src="../../assets/images/logo.jpg" alt="ΠΟΘΕΝ ΕΣΧΕΣ Logo" height="40" class="me-3">
                <span class="fw-bold">ΠΟΘΕΝ ΕΣΧΕΣ</span>
            </a>
            
            <!-- Mobile Toggle Button -->
            <button class="navbar-toggler border-0 d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Desktop Menu -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="../../index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="./search.php">Search</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="./statistics.php">Statistics</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../submit_module/declaration-form.php">Submit</a>
                    </li>
                    <li class="nav-item">
                        <div class="dropdown">
                            <button class="lang-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-translate"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="?lang=en"><span class="fi fi-gb"></span> English</a></li>
                                <li><a class="dropdown-item" href="?lang=el"><span class="fi fi-gr"></span> Ελληνικά</a></li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <div class="dropdown">
                            <button class="profile-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="../login_module/login.php"><i class="bi bi-box-arrow-in-right"></i> Login</a></li>
                                <li><a class="dropdown-item" href="../login_module/register.php"><i class="bi bi-person-plus"></i> Register</a></li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Mobile Menu (Offcanvas) -->
            <div class="offcanvas offcanvas-end d-lg-none" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
                <div class="offcanvas-header border-bottom">
                    <h5 class="offcanvas-title" id="mobileMenuLabel">Menu</h5>
                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 mb-2" href="../../index.php">
                                <i class="bi bi-house"></i> Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 mb-2" href="./search.php">
                                <i class="bi bi-search"></i> Search
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active d-flex align-items-center gap-2 mb-2" href="./statistics.php">
                                <i class="bi bi-graph-up"></i> Statistics
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 mb-3" href="../submit_module/declaration-form.php">
                                <i class="bi bi-file-earmark-text"></i> Submit
                            </a>
                        </li>
                        <li class="nav-item border-top pt-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-translate"></i>
                                <span class="fw-medium">Language</span>
                            </div>
                            <div class="d-flex flex-column gap-2">
                                <a href="?lang=en" class="nav-link py-2">
                                    <span class="fi fi-gb"></span> English
                                </a>
                                <a href="?lang=el" class="nav-link py-2">
                                    <span class="fi fi-gr"></span> Ελληνικά
                                </a>
                            </div>
                        </li>
                        <li class="nav-item border-top pt-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-person-circle"></i>
                                <span class="fw-medium">Account</span>
                            </div>
                            <div class="d-flex flex-column gap-2">
                                <a href="../login_module/login.html" class="nav-link py-2">
                                    <i class="bi bi-box-arrow-in-right me-2"></i> Login
                                </a>
                                <a href="../login_module/register.php" class="nav-link py-2">
                                    <i class="bi bi-person-plus me-2"></i> Register
                                </a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Add padding-top to account for fixed navbar -->
    <div class="pt-5">
        <!-- Main Content -->
        <main class="container my-5">
            <h1 class="mb-4">Statistics & Analysis</h1>

            <!-- Summary Stats -->
            <div class="stats-summary">
                <div class="row">
                    <div class="col-md-3">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $submissionRate; ?>%</div>
                            <div class="stat-label">Submission Rate</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $totalDeclarations; ?></div>
                            <div class="stat-label">Total Declarations</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $totalDeclarations; ?></div>
                            <div class="stat-label">Active Officials</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $totalParties; ?></div>
                            <div class="stat-label">Political Parties</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="row">
                <!-- Declarations by Party -->
                <div class="col-lg-6">
                    <div class="card feature-card">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Declarations by Political Party</h5>
                            <div class="chart-container">
                                <canvas id="partyChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Asset Value Distribution -->
                <div class="col-lg-6">
                    <div class="card feature-card">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Asset Value Distribution</h5>
                            <div class="chart-container">
                                <canvas id="valueDistributionChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Footer -->
    <footer class="bg-light py-4 mt-auto">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-12 col-md-6 text-center text-md-start mb-3 mb-md-0">
                    <p class="mb-0">&copy; 2025 Asset Declaration System. All rights reserved.</p>
                </div>
                <div class="col-12 col-md-6 text-center text-md-end">
                    <div class="d-flex justify-content-center justify-content-md-end gap-3">
                        <a href="about.php" class="text-decoration-none">About</a>
                        <a href="contact.php" class="text-decoration-none">Contact</a>
                        <a href="privacy.php" class="text-decoration-none">Privacy Policy</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../../assets/js/main.js"></script>
    <script>
        // Party Chart
        const partyCtx = document.getElementById('partyChart').getContext('2d');
        new Chart(partyCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($partyLabels); ?>,
                datasets: [{
                    label: 'Number of Declarations',
                    data: <?php echo json_encode($partyCounts); ?>,
                    backgroundColor: [
                        '#ED9635',
                        '#d67b1f',
                        '#f0a85a',
                        '#ffc107',
                        '#6c757d',
                        '#495057',
                        '#343a40'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Value Distribution Chart
        const valueCtx = document.getElementById('valueDistributionChart').getContext('2d');
        new Chart(valueCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($valueLabels); ?>,
                datasets: [{
                    label: 'Number of Assets',
                    data: <?php echo json_encode($valueCounts); ?>,
                    backgroundColor: '#ED9635'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
</body>
</html> 
