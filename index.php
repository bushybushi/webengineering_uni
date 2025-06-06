<?php
session_start();
require_once 'config/db_connection.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ΠΟΘΕΝ ΕΣΧΕΣ - Asset Declaration System</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/iconlogo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <!-- Flag Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css"/>
    <!-- Custom CSS -->
    <link href="./assets/css/style.css" rel="stylesheet">

</head>
<body>
       <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="./index.php">
                <img src="./assets/images/logo.jpg" alt="ΠΟΘΕΝ ΕΣΧΕΣ Logo" height="40" class="me-3">
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
                        <a class="nav-link" href="./index.php">Αρχική</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="./modules/search_module/search.php">Αναζήτηση</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="./modules/search_module/statistics.php">Στατιστικά</a>
                    </li>
                    <li class="nav-item">
                        <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Politician')): ?>
                            <a class="nav-link" href="./modules/submit_module/declaration-form.php">Υποβολή</a>
                        <?php endif; ?>
                    </li>
                    <li class="nav-item">
                        <div class="dropdown">
                            <button class="profile-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <?php if (isset($_SESSION['id'])): ?>
                                    <li>
                                        <a class="dropdown-item" href="./modules/profile_module/profile.php">
                                            <i class="bi bi-person"></i> Το προφίλ μου
                                        </a>
                                    </li>
                                <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Politician')): ?>
                                    <li>
                                        <a class="dropdown-item" href="modules/submit_module/my-declarations.php">
                                            <i class="bi bi-file-earmark-text"></i> Οι Δηλώσεις μου
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    <li>
                                        <a class="dropdown-item" href="./modules/submit_module/favorites.php">
                                            <i class="bi bi-heart"></i> Αγαπημένα
                                        </a>
                                    </li>
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
                                    <li>
                                        <a class="dropdown-item" href="./modules/admin_module/dashboard.php">
                                            <i class="bi bi-speedometer2"></i> Admin Dashboard
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    <li>
                                        <a class="dropdown-item" href="./modules/api_module/api_documentation.php">
                                            <i class="bi bi-code-square"></i> API Documentation
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="./modules/login_module/logout.php">
                                            <i class="bi bi-box-arrow-right"></i> Αποσύνδεση
                                        </a>
                                    </li>
                                <?php else: ?>
                                    <li>
                                        <a class="dropdown-item" href="./modules/login_module/login.php">
                                            <i class="bi bi-box-arrow-in-right"></i> Σύνδεση
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="./modules/login_module/register.php">
                                            <i class="bi bi-person-plus"></i> Εγγραφή
                                        </a>
                                    </li>
                                 <li>
                                        <a class="dropdown-item" href="./modules/api_module/api_documentation.php">
                                            <i class="bi bi-code-square"></i> API Documentation
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Mobile Menu (Offcanvas) -->
            <div class="offcanvas offcanvas-end d-lg-none" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
                <div class="offcanvas-header border-bottom">
                    <h5 class="offcanvas-title" id="mobileMenuLabel">Μενού</h5>
                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 mb-2" href="./index.php">
                                <i class="bi bi-house"></i> Αρχική
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 mb-2" href="./modules/search_module/search.php">
                                <i class="bi bi-search"></i> Αναζήτηση
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 mb-2" href="./modules/search_module/statistics.php">
                                <i class="bi bi-graph-up"></i> Στατιστικά
                            </a>
                        </li>
                        <li class="nav-item">
                            <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Politician')): ?>
                                <a class="nav-link d-flex align-items-center gap-2 mb-3" href="./modules/submit_module/declaration-form.php">
                                    <i class="bi bi-file-earmark-text"></i> Υποβολή
                                </a>
                            <?php endif; ?>
                        </li>
                        <li class="nav-item border-top pt-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-person-circle"></i>
                                <span class="fw-medium">Λογαριασμός</span>
                            </div>
                            <div class="d-flex flex-column gap-2">
                                <?php if (isset($_SESSION['id'])): ?>
                                    <a href="./modules/profile_module/profile.php" class="nav-link py-2">
                                        <i class="bi bi-person"></i> Το προφίλ μου
                                    </a>
                                 <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Politician')): ?>
                                        <a class="dropdown-item" href="modules/submit_module/my-declarations.php">
                                            <i class="bi bi-file-earmark-text"></i> Οι Δηλώσεις μου
                                        </a>
                                    <?php endif; ?>
                                        <a class="dropdown-item" href="./modules/favorites_module/favorites.php">
                                            <i class="bi bi-heart"></i> Αγαπημένα
                                        </a>
                                   
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
                                    <a href="./modules/admin_module/dashboard.php" class="nav-link py-2">
                                        <i class="bi bi-speedometer2"></i> Admin Dashboard
                                    </a>
                                    <?php endif; ?>
                                    <a href="./modules/api_module/api_documentation.php" class="nav-link py-2">
                                        <i class="bi bi-code-square"></i> API Documentation
                                    </a>
                                    <a href="./modules/login_module/logout.php" class="nav-link py-2">
                                        <i class="bi bi-box-arrow-right"></i> Αποσύνδεση
                                    </a>
                                <?php else: ?>
                                    <a href="./modules/login_module/login.php" class="nav-link py-2">
                                        <i class="bi bi-box-arrow-in-right me-2"></i> Σύνδεση
                                    </a>
                                    <a href="./modules/login_module/register.php" class="nav-link py-2">
                                        <i class="bi bi-person-plus me-2"></i> Εγγραφή
                                    </a>
                                 <li>
                                        <a class="dropdown-item" href="./modules/api_module/api_documentation.php">
                                            <i class="bi bi-code-square"></i> API Documentation
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <video autoplay muted loop class="hero-video">
            <source src="assets/images/background.mp4" type="video/mp4">
        </video>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 col-md-8">
                    <h1 class="hero-title">Παρακολούθησε τις Δηλώσεις Πόθεν Έσχες</h1>
                    <div class="search-box-container mb-4">
                        <form action="./modules/search_module/search.php" method="GET" class="d-flex gap-2">
                            <input type="text" class="form-control form-control-lg" name="search" placeholder="Αναζήτηση Δηλώσεων . . ." aria-label="Αναζήτηση Δηλώσεων">
                            <button type="submit" class="btn btn-light btn-lg px-4"><i class="bi bi-search"></i></button>
                        </form>
                    </div>
                    <div class="d-flex flex-column flex-sm-row gap-3">
                        <a href="./modules/login_module/register.php" class="btn btn-light btn-lg px-4">Εγγραφή</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="container mb-5">
        <!-- Stats Section -->
        <div class="row g-4 mb-5">
            <?php
            // Get counts from database
            $declarations_count = $pdo->query("SELECT COUNT(*) FROM declarations")->fetchColumn();
            $parties_count = $pdo->query("SELECT COUNT(*) FROM parties")->fetchColumn();
            $users_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
            ?>
            <div class="col-md-4">
                <div class="stats-card text-center h-100">
                    <div class="stat-number"><?php echo number_format($declarations_count); ?></div>
                    <div class="stat-label">Δηλώσεις</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card text-center h-100">
                    <div class="stat-number"><?php echo number_format($parties_count); ?></div>
                    <div class="stat-label">Πολιτικά Κόμματα</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card text-center h-100">
                    <div class="stat-number"><?php echo number_format($users_count); ?></div>
                    <div class="stat-label">Χρήστες</div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-search feature-icon"></i>
                        <h5 class="card-title">Δημόσια Αναζήτηση</h5>
                        <p class="card-text">Αναζητήστε δηλώσεις με βάση το όνομα, το πολιτικό κόμμα ή τη θέση. Αποκτήστε πλήρη πληροφόρηση για την περιουσιακή κατάσταση των Δημοσίων Αξιωματούχων.</p>
                        <a href="./modules/search_module/search.php" class="btn btn-outline-primary" style="color: #ED9635; border-color: #ED9635;">Αναζήτησε Τώρα</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-file-earmark-text feature-icon"></i>
                        <h5 class="card-title">Υποβολή Δήλωσης</h5>
                        <p class="card-text">Οι Δημοσίων Αξιωματούχοι μπορούν να υποβάλουν τις δηλώσεις τους μέσω της ασφαλούς μας πλατφόρμας. Παρακολούθηση κατάστασης και ειδοποιήσεις.</p>
                        <a href="./modules/submit_module/declaration-form.php" class="btn btn-outline-primary" style="color: #ED9635; border-color: #ED9635;">Υποβολή Δήλωσης</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-graph-up feature-icon"></i>
                        <h5 class="card-title">Στατιστικά & Ανάλυση</h5>
                        <p class="card-text">Δείτε συγκεντρωτικά δεδομένα και στατιστικά στοιχεία για τις δηλώσεις ανά έτος, κόμμα και θέση.</p>
                        <a href="./modules/search_module/statistics.php" class="btn btn-outline-primary" style="color: #ED9635; border-color: #ED9635;">Προβολή Στατιστικών</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Latest Declarations -->
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card feature-card h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Πρόσφατες Δηλώσεις</h5>
                        <?php
                        // Get latest declarations
                        $latest_query = "SELECT d.id, d.title, d.submission_date, pd.office, sp.year as submission_year
                                        FROM declarations d
                                        JOIN personal_data pd ON d.id = pd.declaration_id
                                        JOIN submission_periods sp ON d.submission_period_id = sp.id
                                        ORDER BY d.submission_date DESC
                                        LIMIT 5";

                        try {
                            $latest_stmt = $pdo->prepare($latest_query);
                            $latest_stmt->execute();
                            $latest_declarations = $latest_stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            if (count($latest_declarations) > 0) {
                                foreach ($latest_declarations as $declaration) {
                                    // Format date
                                    $formatted_date = date('d/m/Y', strtotime($declaration['submission_date']));
                                    ?>
                                    <div class="declaration-item">
                                        <a href="./modules/submit_module/view-declaration.php?id=<?php echo $declaration['id']; ?>" class="text-decoration-none text-dark">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; background-color: #e9ecef; color: #495057; font-weight: bold;">
                                                    <?php echo mb_substr($declaration['title'], 0, 1); ?>
                                                </div>
                                                <div class="ms-3">
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($declaration['title']); ?></h6>
                                                    <small class="text-muted">
                                                        <?php 
                                                        echo htmlspecialchars($declaration['office'] ?? 'Μέλος του Κοινοβουλίου');
                                                        echo ' - ' . $formatted_date;
                                                        echo ' (' . htmlspecialchars($declaration['submission_year']) . ')';
                                                        ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <?php
                                }
                            } else {
                                echo '<div class="text-center text-muted">Δεν υπάρχουν πρόσφατες δηλώσεις</div>';
                            }
                        } catch (PDOException $e) {
                            echo '<div class="alert alert-danger">Σφάλμα κατά την ανάκτηση των πρόσφατων δηλώσεων</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card feature-card h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Χρειάζεστε Βοήθεια?</h5>
                        <p class="card-text">Χρειάζεστε βοήθεια με τις δηλώσεις; Μάθετε περισσότερα για το σύστημα και τις λειτουργίες του εδώ.</p>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-primary" style="color: #ED9635; border-color: #ED9635;" data-bs-toggle="modal" data-bs-target="#manualModal">
                                <i class="bi bi-info-circle"></i> Μάθε Περισσότερα
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-light py-4 mt-auto border-top">
        <div class="container">
            <div class="row justify-content-center align-items-center">
                
                <div class="col-12 text-center mb-2">
                    <div class="mb-2">
                        <img src="assets/images/iconlogo.png" alt="Πόθεν Έσχες Logo" style="height: 42px; width: 42px; object-fit: contain;" />
                    </div>
                    <a href="#" class="text-decoration-none fw-medium" style="color: #ED9635;" data-bs-toggle="modal" data-bs-target="#aboutUsModal">
                        <i class="bi bi-person-badge me-1"></i>Ποιοι είμαστε
                    </a>
                </div>
                <div class="col-12 text-center mb-2">
                    <span class="fw-bold small" style="color: #ED9635; font-size: 0.95rem;"><a href="#" style="text-decoration: none; color: #ED9635;">Πόθεν Έσχες</a></span>
                    <span class="text-muted small">&copy; 2025. All rights reserved.</span>
                </div>
            </div>
        </div>
    </footer>

    <?php include 'includes/about-us-modal.php'; ?>
    <?php include 'includes/manual-modal.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="js/main.js"></script>
</body>
</html> 
