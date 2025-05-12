<!-- filepath: c:\xampp\htdocs\webengineering_uni\webengineering_uni\modules\admin_module\system_config.php -->
<?php
$conn = include '../../config/db_connection.php';
session_start();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_party'])) {
        $party = $_POST['party'];
        $stmt = $conn->prepare("INSERT INTO parties (name) VALUES (?)");
        $stmt->execute([$party]);
    } elseif (isset($_POST['add_period'])) {
        $year = $_POST['year'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $stmt = $conn->prepare("INSERT INTO submission_periods (year, start_date, end_date, is_active) VALUES (?, ?, ?, ?)");
        $stmt->execute([$year, $start_date, $end_date, $is_active]);
    }
}

// Fetch existing data
$parties = $conn->query("SELECT * FROM parties ORDER BY name");
$periods = $conn->query("SELECT * FROM submission_periods ORDER BY year DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ρυθμίσεις Συστήματος - ΠΟΘΕΝ ΕΣΧΕΣ</title>
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
            
            <!-- Mobile Toggle Button -->
            <button class="navbar-toggler border-0 d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Desktop Menu -->
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
                                <li>
                                    <a class="dropdown-item" href="../profile_module/profile.php">
                                        <i class="bi bi-person"></i> Το προφίλ μου
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="../favorites_module/favorites.php">
                                        <i class="bi bi-heart"></i> Αγαπημένα
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="../admin_module/dashboard.php">
                                        <i class="bi bi-speedometer2"></i> Admin Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="../login_module/logout.php">
                                        <i class="bi bi-box-arrow-right"></i> Αποσύνδεση
                                    </a>
                                </li>
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
                            <a class="nav-link d-flex align-items-center gap-2 mb-2" href="../../index.php">
                                <i class="bi bi-house"></i> Αρχική
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 mb-2" href="../search_module/search.php">
                                <i class="bi bi-search"></i> Αναζήτηση
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 mb-2" href="../search_module/statistics.php">
                                <i class="bi bi-graph-up"></i> Στατιστικά
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 mb-3" href="../submit_module/declaration-form.php">
                                <i class="bi bi-file-earmark-text"></i> Υποβολή
                            </a>
                        </li>
                        <li class="nav-item border-top pt-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-translate"></i>
                                <span class="fw-medium">Γλώσσα</span>
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
                                <span class="fw-medium">Λογαριασμός</span>
                            </div>
                            <div class="d-flex flex-column gap-2">
                                <a href="../profile_module/profile.php" class="nav-link py-2">
                                    <i class="bi bi-person"></i> Το προφίλ μου
                                </a>
                                <a href="../favorites_module/favorites.php" class="nav-link py-2">
                                    <i class="bi bi-heart"></i> Αγαπημένα
                                </a>
                                <a href="../admin_module/dashboard.php" class="nav-link py-2">
                                    <i class="bi bi-speedometer2"></i> Admin Dashboard
                                </a>
                                <a href="../login_module/logout.php" class="nav-link py-2">
                                    <i class="bi bi-box-arrow-right"></i> Αποσύνδεση
                                </a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mt-5 pt-5">
        <h1 class="text-center mb-4">Ρυθμίσεις Συστήματος</h1>
        
        <!-- Political Parties Section -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="card-title mb-4">Πολιτικά Κόμματα</h2>
                <form method="POST" class="mb-4">
                    <div class="mb-3">
                        <label for="party" class="form-label">Όνομα Κόμματος</label>
                        <input type="text" class="form-control" id="party" name="party" required>
                    </div>
                    <button type="submit" name="add_party" class="btn btn-primary" style="background-color: #ED9635; border-color: #ED9635;">
                        <i class="bi bi-plus-circle"></i> Προσθήκη Κόμματος
                    </button>
                </form>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Όνομα</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($party = $parties->fetch(PDO::FETCH_ASSOC)) { ?>
                                <tr>
                                    <td><?= $party['id'] ?></td>
                                    <td><?= $party['name'] ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Submission Periods Section -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="card-title mb-4">Περίοδοι Υποβολής</h2>
                <form method="POST" class="mb-4">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="year" class="form-label">Έτος</label>
                                <input type="number" class="form-control" id="year" name="year" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="start_date" class="form-label">Ημερομηνία Έναρξης</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="end_date" class="form-label">Ημερομηνία Λήξης</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active">
                                    <label class="form-check-label" for="is_active">
                                        Ενεργή Περίοδος
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="submit" name="add_period" class="btn btn-primary" style="background-color: #ED9635; border-color: #ED9635;">
                        <i class="bi bi-plus-circle"></i> Προσθήκη Περιόδου
                    </button>
                </form>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Έτος</th>
                                <th>Έναρξη</th>
                                <th>Λήξη</th>
                                <th>Κατάσταση</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($period = $periods->fetch(PDO::FETCH_ASSOC)) { ?>
                                <tr>
                                    <td><?= $period['id'] ?></td>
                                    <td><?= $period['year'] ?></td>
                                    <td><?= $period['start_date'] ?></td>
                                    <td><?= $period['end_date'] ?></td>
                                    <td>
                                        <span class="badge <?= $period['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= $period['is_active'] ? 'Ενεργή' : 'Ανενεργή' ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
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