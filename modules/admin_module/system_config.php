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
    } elseif (isset($_POST['edit_party'])) {
        $party_id = $_POST['party_id'];
        $party_name = $_POST['party_name'];
        $stmt = $conn->prepare("UPDATE parties SET name = ? WHERE id = ?");
        $stmt->execute([$party_name, $party_id]);
    } elseif (isset($_POST['delete_party'])) {
        $party_id = $_POST['party_id'];
        $stmt = $conn->prepare("DELETE FROM parties WHERE id = ?");
        $stmt->execute([$party_id]);
    } elseif (isset($_POST['add_period'])) {
        $year = $_POST['year'];
        $stmt = $conn->prepare("INSERT INTO submission_periods (year) VALUES (?)");
        $stmt->execute([$year]);
    } elseif (isset($_POST['edit_period'])) {
        $period_id = $_POST['period_id'];
        $year = $_POST['year'];
        $stmt = $conn->prepare("UPDATE submission_periods SET year = ? WHERE id = ?");
        $stmt->execute([$year, $period_id]);
    } elseif (isset($_POST['delete_period'])) {
        $period_id = $_POST['period_id'];
        $stmt = $conn->prepare("DELETE FROM submission_periods WHERE id = ?");
        $stmt->execute([$period_id]);
    }
}

// Get search parameters
$party_search = isset($_GET['party_search']) ? $_GET['party_search'] : '';

// Fetch existing data with search
$party_query = "SELECT p.*, 
                       CASE WHEN COUNT(pd.id) > 0 THEN 1 ELSE 0 END as is_used
                FROM parties p
                LEFT JOIN personal_data pd ON p.id = pd.party_id";
if (!empty($party_search)) {
    $party_query .= " WHERE p.name LIKE ?";
    $party_query .= " GROUP BY p.id";
    $stmt = $conn->prepare($party_query);
    $stmt->execute(["%$party_search%"]);
} else {
    $party_query .= " GROUP BY p.id";
    $stmt = $conn->query($party_query);
}
$parties = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch periods without search
$periods = $conn->query("
    SELECT sp.*, 
           CASE WHEN COUNT(d.id) > 0 THEN 1 ELSE 0 END as is_used
    FROM submission_periods sp
    LEFT JOIN declarations d ON sp.id = d.submission_period_id
    GROUP BY sp.id
    ORDER BY sp.year DESC
")->fetchAll(PDO::FETCH_ASSOC);
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
    <div class="container mt-4">
        <div class="d-flex align-items-center mb-4">
            <a href="dashboard.php" class="btn btn-outline-primary me-3">
                <i class="fas fa-arrow-left"></i> Επιστροφή στο Dashboard
            </a>
            <h2>Ρυθμίσεις Συστήματος</h2>
        </div>
        
        <!-- System Configuration Form -->
        <div class="card">
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="submission_period" class="form-label">Περίοδος Υποβολής</label>
                        <input type="text" class="form-control" id="submission_period" name="submission_period" value="<?php echo htmlspecialchars($config['submission_period']); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="max_submissions" class="form-label">Μέγιστος Αριθμός Υποβολών</label>
                        <input type="number" class="form-control" id="max_submissions" name="max_submissions" value="<?php echo htmlspecialchars($config['max_submissions']); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Αποθήκευση Ρυθμίσεων</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer mt-auto py-3 bg-light">
        <div class="container text-center">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <img src="../../assets/images/logo.png" alt="Logo" height="40">
                </div>
                <div class="col-md-4">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#aboutModal" class="text-decoration-none text-dark">About Us</a>
                </div>
                <div class="col-md-4">
                    <span class="text-muted">&copy; 2025 All rights reserved.</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>