<!-- filepath: c:\xampp\htdocs\webengineering_uni\webengineering_uni\modules\admin_module\dashboard.php -->
<?php
// Include necessary files
include '../../config/db_connection.php';
session_start();

// Fetch some basic statistics for the dashboard
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users"))['total'];
$total_submissions = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM submissions"))['total'];
$total_parties = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM political_parties"))['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ΠΟΘΕΝ ΕΣΧΕΣ</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../../assets/images/iconlogo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="../../index.php">
                <img src="../../assets/images/logo.jpg" alt="ΠΟΘΕΝ ΕΣΧΕΣ Logo" height="40" class="me-3">
                <span class="fw-bold">ΠΟΘΕΝ ΕΣΧΕΣ</span>
            </a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="../../index.php">Αρχική</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../search_module/search.php">Αναζήτηση</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../search_module/statistics.php">Στατιστικά</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../submit_module/declaration-form.php">Υποβολή</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mt-5 pt-5">
        <h1 class="text-center mb-4">Admin Dashboard</h1>
        
        <!-- Quick Stats -->
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-people feature-icon mb-3" style="font-size: 2rem; color: #ED9635;"></i>
                        <h5 class="card-title">Χρήστες</h5>
                        <h2 class="mb-0"><?= $total_users ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-file-text feature-icon mb-3" style="font-size: 2rem; color: #28a745;"></i>
                        <h5 class="card-title">Υποβολές</h5>
                        <h2 class="mb-0"><?= $total_submissions ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-building feature-icon mb-3" style="font-size: 2rem; color: #dc3545;"></i>
                        <h5 class="card-title">Κόμματα</h5>
                        <h2 class="mb-0"><?= $total_parties ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-graph-up feature-icon mb-3" style="font-size: 2rem; color: #ffc107;"></i>
                        <h5 class="card-title">Αναφορές</h5>
                        <h2 class="mb-0">4</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Actions -->
        <div class="row g-4">
            <div class="col-md-3">
                <a href="manage_users.php" class="text-decoration-none">
                    <div class="card shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-people feature-icon mb-3" style="font-size: 3rem; color: #ED9635;"></i>
                            <h5 class="card-title">Διαχείριση Χρηστών</h5>
                            <p class="card-text">Διαχειριστείτε τους χρήστες του συστήματος</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="manage_submissions.php" class="text-decoration-none">
                    <div class="card shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-file-text feature-icon mb-3" style="font-size: 3rem; color: #28a745;"></i>
                            <h5 class="card-title">Διαχείριση Υποβολών</h5>
                            <p class="card-text">Διαχειριστείτε τις υποβολές δηλώσεων</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="system_config.php" class="text-decoration-none">
                    <div class="card shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-gear feature-icon mb-3" style="font-size: 3rem; color: #dc3545;"></i>
                            <h5 class="card-title">Ρυθμίσεις Συστήματος</h5>
                            <p class="card-text">Ρυθμίστε τις παραμέτρους του συστήματος</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="generate_reports.php" class="text-decoration-none">
                    <div class="card shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-graph-up feature-icon mb-3" style="font-size: 3rem; color: #ffc107;"></i>
                            <h5 class="card-title">Αναφορές</h5>
                            <p class="card-text">Δείτε αναφορές και στατιστικά στοιχεία</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-light py-4 mt-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-12 col-md-6 text-center text-md-start mb-3 mb-md-0">
                    <p class="mb-0"> 2025 Πόθεν Εσχες &copy; all rights reserved.</p>
                </div>
                <div class="col-12 col-md-6 text-center text-md-end">
                    <div class="d-flex justify-content-center justify-content-md-end gap-3">
                        <a href="about.html" class="text-decoration-none">Ποιοι είμαστε</a>
                        <a href="contact.html" class="text-decoration-none">Επικοινωνία</a>
                        <a href="privacy.html" class="text-decoration-none">Πολιτική Απορρήτου</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>