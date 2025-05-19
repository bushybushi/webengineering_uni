<?php
session_start();
if (!isset($_SESSION['id']) || !in_array($_SESSION['role'], ['Admin', 'Public', 'Politician'])) {
    header('Location: ../login_module/login.php');
    exit;
}

// Get database connection
$pdo = require '../../config/db_connection.php';

// Get unique parties
$partiesQuery = "SELECT DISTINCT name FROM parties WHERE name IS NOT NULL AND name != '' ORDER BY name";
$partiesStmt = $pdo->query($partiesQuery);
$parties = $partiesStmt->fetchAll(PDO::FETCH_COLUMN);

// Get unique positions
$positionsQuery = "SELECT DISTINCT office FROM personal_data WHERE office IS NOT NULL ORDER BY office";
$positionsStmt = $pdo->query($positionsQuery);
$positions = $positionsStmt->fetchAll(PDO::FETCH_COLUMN);

// Get unique years
$yearsQuery = "SELECT DISTINCT year FROM submission_periods ORDER BY year DESC";
$yearsStmt = $pdo->query($yearsQuery);
$years = $yearsStmt->fetchAll(PDO::FETCH_COLUMN);

// Get all declaration IDs with names
$idsQuery = "SELECT d.id, pd.full_name 
             FROM declarations d 
             LEFT JOIN personal_data pd ON d.id = pd.declaration_id 
             WHERE d.status = 'Approved' 
             ORDER BY pd.full_name";
$idsStmt = $pdo->query($idsQuery);
$politicians = $idsStmt->fetchAll(PDO::FETCH_ASSOC);

// Get API keys from database
$apiKeysQuery = "SELECT role, key_value FROM api_keys";
$apiKeysStmt = $pdo->query($apiKeysQuery);
$apiKeys = $apiKeysStmt->fetchAll(PDO::FETCH_ASSOC);

// Check if user is admin
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'Admin';

// Get the appropriate API key based on user role
$userRole = $_SESSION['role'];
$apiKey = '';
foreach ($apiKeys as $key) {
    if ($key['role'] === strtolower($userRole)) {
        $apiKey = $key['key_value'];
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation - Δηλώσεις Περιουσιακής Κατάστασης</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .api-card {
            transition: transform 0.2s;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .api-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .copy-btn {
            cursor: pointer;
            transition: all 0.2s;
        }
        .copy-btn:hover {
            background-color: #ffc107;
            color: white;
        }
        .url-display {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            font-family: monospace;
            word-break: break-all;
            border: 1px solid #dee2e6;
        }
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .select2-container {
            width: 100% !important;
        }
        .card-title {
            color: #ffc107;
            font-weight: 600;
        }
        .card-text {
            color: #6c757d;
        }
        .form-select-lg {
            padding: 0.75rem 1rem;
            font-size: 1rem;
            border-radius: 0.375rem;
            border: 1px solid #ced4da;
            background-color: #fff;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        .form-select-lg:focus {
            border-color: #ffc107;
            box-shadow: 0 0 0 0.25rem rgba(255, 193, 7, 0.25);
        }
        .form-label {
            font-size: 1rem;
            margin-bottom: 0.5rem;
            color: #212529;
        }
        .select2-container--bootstrap-5 .select2-selection {
            padding: 0.75rem 1rem;
            font-size: 1rem;
            border-radius: 0.375rem;
            border: 1px solid #ced4da;
            background-color: #fff;
            min-height: 42px;
        }
        .select2-container--bootstrap-5 .select2-selection--single {
            display: flex;
            align-items: center;
        }
        .select2-container--bootstrap-5 .select2-selection__rendered {
            line-height: normal;
            padding: 0;
            color: #212529;
        }
        .select2-container--bootstrap-5 .select2-selection__placeholder {
            color: #6c757d;
        }
        .select2-container--bootstrap-5.select2-container--focus .select2-selection {
            border-color: #ffc107;
            box-shadow: 0 0 0 0.25rem rgba(255, 193, 7, 0.25);
        }
        .select2-container--bootstrap-5 .select2-results__option {
            padding: 0.5rem 1rem;
            font-size: 1rem;
        }
        .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background-color: #ffc107;
            color: #000;
        }
        .btn-outline-primary {
         --bs-btn-color: #ffc107;
        --bs-btn-border-color: #ffc107;
        --bs-btn-active-bg: #ffc107;
        }
        .btn{
            --bs-btn-bg: #ffc107;
            color:rgb(0, 0, 0);
        }
        .method-content {
            transition: all 0.3s ease;
        }
        .btn-group .btn {
            min-width: 100px;
        }
        .btn-group .btn i {
            width: 20px;
        }
        .method-buttons {
            display: flex;
            gap: 10px;
        }

        .method-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 110px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .method-btn i {
            font-size: 14px;
        }

        .method-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .method-btn.active {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .get-btn {
            background-color: #28a745;
        }

        .post-btn {
            background-color: #ffc107;
            color: #000;
        }

        .put-btn {
            background-color: #0d6efd;
        }

        .patch-btn {
            background-color: #6f42c1;
        }

        .delete-btn {
            background-color: #dc3545;
        }

        .get-btn:hover, .get-btn.active {
            background-color: #218838;
        }

        .post-btn:hover, .post-btn.active {
            background-color: #e0a800;
        }

        .put-btn:hover, .put-btn.active {
            background-color: #0b5ed7;
        }

        .patch-btn:hover, .patch-btn.active {
            background-color: #5a32a3;
        }

        .delete-btn:hover, .delete-btn.active {
            background-color: #bb2d3b;
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
                        <a class="nav-link" href="../../index.php">Αρχική</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../search_module/search.php">Αναζήτηση</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../search_module/statistics.php">Στατιστικά</a>
                    </li>
                    <li class="nav-item">
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Public'): ?>
                            <a class="nav-link" href="../../index.php">Υποβολή</a>
                        <?php else: ?>
                            <a class="nav-link" href="../submit_module/declaration-form.php">Υποβολή</a>
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
                                        <a class="dropdown-item" href="../profile_module/profile.php">
                                            <i class="bi bi-person"></i> Το προφίλ μου
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="../favorites_module/favorites.php">
                                            <i class="bi bi-heart"></i> Αγαπημένα
                                        </a>
                                    </li>
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
                                    <li>
                                        <a class="dropdown-item" href="../admin_module/dashboard.php">
                                            <i class="bi bi-speedometer2"></i> Admin Dashboard
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="../search_module/api_documentation.php">
                                            <i class="bi bi-code-square"></i> API Documentation
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    <li>
                                        <a class="dropdown-item" href="../login_module/logout.php">
                                            <i class="bi bi-box-arrow-right"></i> Αποσύνδεση
                                        </a>
                                    </li>
                                <?php else: ?>
                                    <li>
                                        <a class="dropdown-item" href="../login_module/login.php">
                                            <i class="bi bi-box-arrow-in-right"></i> Σύνδεση
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="../login_module/register.php">
                                            <i class="bi bi-person-plus"></i> Εγγραφή
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
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Public'): ?>
                                <a class="nav-link d-flex align-items-center gap-2 mb-3" href="../../index.php">
                                    <i class="bi bi-file-earmark-text"></i> Υποβολή
                                </a>
                            <?php else: ?>
                                <a class="nav-link d-flex align-items-center gap-2 mb-3" href="../submit_module/declaration-form.php">
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
                                    <a href="../profile_module/profile.php" class="nav-link py-2">
                                        <i class="bi bi-person"></i> Το προφίλ μου
                                    </a>
                                    <a href="../favorites_module/favorites.php" class="nav-link py-2">
                                        <i class="bi bi-heart"></i> Αγαπημένα
                                    </a>
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
                                    <a href="../admin_module/dashboard.php" class="nav-link py-2">
                                        <i class="bi bi-speedometer2"></i> Admin Dashboard
                                    </a>
                                    <a href="../search_module/api_documentation.php" class="nav-link py-2">
                                        <i class="bi bi-code-square"></i> API Documentation
                                    </a>
                                    <?php endif; ?>
                                    <a href="../login_module/logout.php" class="nav-link py-2">
                                        <i class="bi bi-box-arrow-right"></i> Αποσύνδεση
                                    </a>
                                <?php else: ?>
                                    <a href="../login_module/login.php" class="nav-link py-2">
                                        <i class="bi bi-box-arrow-in-right"></i> Σύνδεση
                                    </a>
                                    <a href="../login_module/register.php" class="nav-link py-2">
                                        <i class="bi bi-person-plus"></i> Εγγραφή
                                    </a>
                                <?php endif; ?>
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>API Documentation</h1>
                <div class="method-buttons">
                    <button type="button" class="method-btn get-btn" data-method="get">
                        <i class="fas fa-arrow-down"></i>
                        <span>GET</span>
                    </button>
                    <?php if ($isAdmin): ?>
                    <button type="button" class="method-btn post-btn" data-method="post">
                        <i class="fas fa-plus"></i>
                        <span>POST</span>
                    </button>
                    <button type="button" class="method-btn put-btn" data-method="put">
                        <i class="fas fa-edit"></i>
                        <span>PUT</span>
                    </button>
                    <button type="button" class="method-btn patch-btn" data-method="patch">
                        <i class="fas fa-pen"></i>
                        <span>PATCH</span>
                    </button>
                    <button type="button" class="method-btn delete-btn" data-method="delete">
                        <i class="fas fa-trash"></i>
                        <span>DELETE</span>
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Method Tabs -->
            <div class="method-tabs mb-4">
                <!-- GET Tab -->
                <div class="method-content" id="get-content">
                    <!-- Search All Politicians -->
                    <div class="card api-card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-search me-2"></i>
                                Αναζήτηση Όλων των Πολιτικών
                            </h5>
                            <p class="card-text">Επιστρέφει λίστα με όλους τους πολιτικούς που έχουν υποβάλλει δήλωση.</p>
                            <div class="mb-3">
                                <h6 class="fw-bold">Headers:</h6>
                                <pre class="bg-light p-2 rounded">
Content-Type: application/json
X-API-Key: <?php echo htmlspecialchars($apiKey); ?></pre>
                            </div>
                            <div class="url-display mb-2" id="all-politicians-url">
                                <?php echo "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/api.php"; ?>
                            </div>
                            <button class="btn btn-outline-primary copy-btn" data-url="<?php echo "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/api.php"; ?>">
                                <i class="fas fa-copy me-2"></i>Αντιγραφή URL
                            </button>
                        </div>
                    </div>

                    <!-- Search by Party -->
                    <div class="card api-card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-users me-2"></i>
                                Αναζήτηση ανά Κόμμα
                            </h5>
                            <p class="card-text">Επιστρέφει λίστα με πολιτικούς συγκεκριμένου κόμματος.</p>
                            <div class="mb-3">
                                <h6 class="fw-bold">Headers:</h6>
                                <pre class="bg-light p-2 rounded">
Content-Type: application/json
X-API-Key: <?php echo htmlspecialchars($apiKey); ?></pre>
                            </div>
                            <div class="form-group mb-4">
                                <label for="party-input" class="form-label fw-bold mb-2">Επιλέξτε κόμμα:</label>
                                <select class="form-select form-select-lg" id="party-input">
                                    <option value="">Επιλέξτε κόμμα...</option>
                                    <?php foreach ($parties as $party): ?>
                                        <option value="<?php echo htmlspecialchars($party); ?>"><?php echo htmlspecialchars($party); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="url-display mb-3" id="party-url">
                                <?php echo "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/api.php?party="; ?>
                            </div>
                            <button class="btn btn-outline-primary copy-btn" id="party-copy-btn">
                                <i class="fas fa-copy me-2"></i>Αντιγραφή URL
                            </button>
                        </div>
                    </div>

                    <!-- Search by Year -->
                    <div class="card api-card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-calendar me-2"></i>
                                Αναζήτηση ανά Έτος
                            </h5>
                            <p class="card-text">Επιστρέφει δηλώσεις συγκεκριμένου έτους.</p>
                            <div class="mb-3">
                                <h6 class="fw-bold">Headers:</h6>
                                <pre class="bg-light p-2 rounded">
Content-Type: application/json
X-API-Key: <?php echo htmlspecialchars($apiKey); ?></pre>
                            </div>
                            <div class="form-group">
                                <label for="year-input">Επιλέξτε έτος:</label>
                                <select class="form-select" id="year-input">
                                    <option value="">Επιλέξτε έτος...</option>
                                    <?php foreach ($years as $year): ?>
                                        <option value="<?php echo htmlspecialchars($year); ?>"><?php echo htmlspecialchars($year); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="url-display mb-2" id="year-url">
                                <?php echo "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/api.php?year="; ?>
                            </div>
                            <button class="btn btn-outline-primary copy-btn" id="year-copy-btn">
                                <i class="fas fa-copy me-2"></i>Αντιγραφή URL
                            </button>
                        </div>
                    </div>

                    <!-- Search by Position -->
                    <div class="card api-card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-user-tie me-2"></i>
                                Αναζήτηση ανά Αξίωμα
                            </h5>
                            <p class="card-text">Επιστρέφει πολιτικούς με συγκεκριμένο αξίωμα.</p>
                            <div class="mb-3">
                                <h6 class="fw-bold">Headers:</h6>
                                <pre class="bg-light p-2 rounded">
Content-Type: application/json
X-API-Key: <?php echo htmlspecialchars($apiKey); ?></pre>
                            </div>
                            <div class="form-group">
                                <label for="position-input">Επιλέξτε αξίωμα:</label>
                                <select class="form-select" id="position-input">
                                    <option value="">Επιλέξτε αξίωμα...</option>
                                    <?php foreach ($positions as $position): ?>
                                        <option value="<?php echo htmlspecialchars($position); ?>"><?php echo htmlspecialchars($position); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="url-display mb-2" id="position-url">
                                <?php echo "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/api.php?position="; ?>
                            </div>
                            <button class="btn btn-outline-primary copy-btn" id="position-copy-btn">
                                <i class="fas fa-copy me-2"></i>Αντιγραφή URL
                            </button>
                        </div>
                    </div>

                    <!-- Combined Search -->
                    <div class="card api-card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-filter me-2"></i>
                                Συνδυασμένη Αναζήτηση
                            </h5>
                            <p class="card-text">Επιστρέφει αποτελέσματα με βάση συνδυασμό κριτηρίων (κόμμα, έτος, αξίωμα).</p>
                            <div class="mb-3">
                                <h6 class="fw-bold">Headers:</h6>
                                <pre class="bg-light p-2 rounded">
Content-Type: application/json
X-API-Key: <?php echo htmlspecialchars($apiKey); ?></pre>
                            </div>
                            <div class="form-group mb-3">
                                <label for="combined-party" class="form-label">Επιλέξτε κόμμα:</label>
                                <select class="form-select" id="combined-party">
                                    <option value="">Επιλέξτε κόμμα...</option>
                                    <?php foreach ($parties as $party): ?>
                                        <option value="<?php echo htmlspecialchars($party); ?>"><?php echo htmlspecialchars($party); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label for="combined-year" class="form-label">Επιλέξτε έτος:</label>
                                <select class="form-select" id="combined-year">
                                    <option value="">Επιλέξτε έτος...</option>
                                    <?php foreach ($years as $year): ?>
                                        <option value="<?php echo htmlspecialchars($year); ?>"><?php echo htmlspecialchars($year); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label for="combined-position" class="form-label">Επιλέξτε αξίωμα:</label>
                                <select class="form-select" id="combined-position">
                                    <option value="">Επιλέξτε αξίωμα...</option>
                                    <?php foreach ($positions as $position): ?>
                                        <option value="<?php echo htmlspecialchars($position); ?>"><?php echo htmlspecialchars($position); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="url-display mb-2" id="combined-url">
                                <?php echo "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/api.php"; ?>
                            </div>
                            <button class="btn btn-outline-primary copy-btn" id="combined-copy-btn">
                                <i class="fas fa-copy me-2"></i>Αντιγραφή URL
                            </button>
                        </div>
                    </div>

                    <!-- Get Specific Politician -->
                    <div class="card api-card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-user me-2"></i>
                                Λήψη Στοιχείων Συγκεκριμένου Πολιτικού
                            </h5>
                            <p class="card-text">Επιστρέφει αναλυτικά στοιχεία συγκεκριμένου πολιτικού.</p>
                            <div class="mb-3">
                                <h6 class="fw-bold">Headers:</h6>
                                <pre class="bg-light p-2 rounded">
Content-Type: application/json
X-API-Key: <?php echo htmlspecialchars($apiKey); ?></pre>
                            </div>
                            <div class="form-group">
                                <label for="id-input">Επιλέξτε πολιτικό:</label>
                                <select class="form-select" id="id-input">
                                    <option value="">Επιλέξτε πολιτικό...</option>
                                    <?php foreach ($politicians as $politician): ?>
                                        <option value="<?php echo htmlspecialchars($politician['id']); ?>">
                                            <?php echo htmlspecialchars($politician['full_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="url-display mb-2" id="id-url">
                                <?php echo "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/api.php?id="; ?>
                            </div>
                            <button class="btn btn-outline-primary copy-btn" id="id-copy-btn">
                                <i class="fas fa-copy me-2"></i>Αντιγραφή URL
                            </button>
                        </div>
                    </div>
                </div>

                <?php if ($isAdmin): ?>
                <!-- POST Tab -->
                <div class="method-content d-none" id="post-content">
                    <div class="card api-card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-plus-circle me-2"></i>
                                Προσθήκη Νέας Δήλωσης
                            </h5>
                            <p class="card-text">Προσθήκη νέας δήλωσης περιουσιακής κατάστασης.</p>
                            <div class="url-display mb-2">
                                <?php echo "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/api.php"; ?>
                            </div>
                            <div class="mb-3">
                                <h6 class="fw-bold">Method: POST</h6>
                                <h6 class="fw-bold">Headers:</h6>
                                <pre class="bg-light p-2 rounded">
Content-Type: application/json
X-API-Key: <?php echo htmlspecialchars($apiKey); ?></pre>
                                <h6 class="fw-bold">Request Body:</h6>
                                <pre class="bg-light p-2 rounded">
{
    "title": "Δήλωση Περιουσιακής Κατάστασης",
    "personal_data": {
        "full_name": "Όνομα Επώνυμο",
        "office": "Αξίωμα",
        "address": "Διεύθυνση",
        "dob": "YYYY-MM-DD",
        "id_number": "Αριθμός Ταυτότητας",
        "marital_status": "Οικογενειακή Κατάσταση",
        "dependants": 0,
        "party_id": 1
    },
    "properties": [
        {
            "type": "Κύρια Κατοικία",
            "location": "Τοποθεσία",
            "area": 100,
            "topographic_data": "Δεδομένα",
            "rights_burdens": "Δικαιώματα/Βάρη",
            "acquisition_mode": "Τρόπος απόκτησης",
            "acquisition_date": "YYYY-MM-DD",
            "acquisition_value": 100000,
            "current_value": 150000
        }
    ],
    "vehicles": [
        {
            "type": "Αυτοκίνητο",
            "brand": "Μάρκα",
            "manu_year": 2020,
            "value": 20000
        }
    ],
    "liquid_assets": [
        {
            "type": "Μετρητά",
            "description": "Περιγραφή",
            "amount": 5000
        }
    ],
    "deposits": [
        {
            "bank_name": "Τράπεζα",
            "amount": 10000
        }
    ],
    "insurance": [
        {
            "insurance_name": "Εταιρεία",
            "contract_num": "123456",
            "earnings": 5000
        }
    ],
    "debts": [
        {
            "creditor_name": "Πιστωτής",
            "type": "Δάνειο",
            "amount": 15000
        }
    ],
    "business": [
        {
            "business_name": "Όνομα Εταιρείας",
            "business_type": "Τύπος",
            "participation_type": "Τύπος συμμετοχής"
        }
    ],
    "differences": {
        "content": "Περιεχόμενο διαφορών"
    },
    "previous_incomes": {
        "html_content": "Περιεχόμενο προηγούμενων εισοδημάτων"
    }
}</pre>
                            </div>
                            <button class="btn btn-outline-primary copy-btn" data-url="<?php echo "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/api.php"; ?>">
                                <i class="fas fa-copy me-2"></i>Αντιγραφή URL
                            </button>
                        </div>
                    </div>
                </div>

                <!-- PUT Tab -->
                <div class="method-content d-none" id="put-content">
                    <div class="card api-card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-edit me-2"></i>
                                Ενημέρωση Δήλωσης
                            </h5>
                            <p class="card-text">Πλήρης ενημέρωση υπάρχουσας δήλωσης περιουσιακής κατάστασης.</p>
                            <div class="url-display mb-2">
                                <?php echo "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/api.php?id="; ?>
                            </div>
                            <div class="mb-3">
                                <h6 class="fw-bold">Method: PUT</h6>
                                <h6 class="fw-bold">Headers:</h6>
                                <pre class="bg-light p-2 rounded">
Content-Type: application/json
X-API-Key: <?php echo htmlspecialchars($apiKey); ?></pre>
                                <h6 class="fw-bold">URL Parameters:</h6>
                                <ul>
                                    <li><code>id</code>: Το ID της δήλωσης προς ενημέρωση</li>
                                </ul>
                                <h6 class="fw-bold">Request Body:</h6>
                                <pre class="bg-light p-2 rounded">
{
    "title": "Δήλωση Περιουσιακής Κατάστασης",
    "personal_data": {
        "full_name": "Όνομα Επώνυμο",
        "office": "Αξίωμα",
        "address": "Διεύθυνση",
        "dob": "YYYY-MM-DD",
        "id_number": "Αριθμός Ταυτότητας",
        "marital_status": "Οικογενειακή Κατάσταση",
        "dependants": 0,
        "party_id": 1
    },
    "properties": [
        {
            "type": "Κύρια Κατοικία",
            "location": "Τοποθεσία",
            "area": 100,
            "topographic_data": "Δεδομένα",
            "rights_burdens": "Δικαιώματα/Βάρη",
            "acquisition_mode": "Τρόπος απόκτησης",
            "acquisition_date": "YYYY-MM-DD",
            "acquisition_value": 100000,
            "current_value": 150000
        }
    ],
    "vehicles": [
        {
            "type": "Αυτοκίνητο",
            "brand": "Μάρκα",
            "manu_year": 2020,
            "value": 20000
        }
    ],
    "liquid_assets": [
        {
            "type": "Μετρητά",
            "description": "Περιγραφή",
            "amount": 5000
        }
    ],
    "deposits": [
        {
            "bank_name": "Τράπεζα",
            "amount": 10000
        }
    ],
    "insurance": [
        {
            "insurance_name": "Εταιρεία",
            "contract_num": "123456",
            "earnings": 5000
        }
    ],
    "debts": [
        {
            "creditor_name": "Πιστωτής",
            "type": "Δάνειο",
            "amount": 15000
        }
    ],
    "business": [
        {
            "business_name": "Όνομα Εταιρείας",
            "business_type": "Τύπος",
            "participation_type": "Τύπος συμμετοχής"
        }
    ],
    "differences": {
        "content": "Περιεχόμενο διαφορών"
    },
    "previous_incomes": {
        "html_content": "Περιεχόμενο προηγούμενων εισοδημάτων"
    }
}</pre>
                                <h6 class="fw-bold">Response:</h6>
                                <pre class="bg-light p-2 rounded">
{
    "status": "success",
    "message": "Declaration updated successfully",
    "declaration_id": 123
}</pre>
                            </div>
                            <button class="btn btn-outline-primary copy-btn" data-url="<?php echo "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/api.php?id="; ?>">
                                <i class="fas fa-copy me-2"></i>Αντιγραφή URL
                            </button>
                        </div>
                    </div>
                </div>

                <!-- PATCH Tab -->
                <div class="method-content d-none" id="patch-content">
                    <div class="card api-card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-pen me-2"></i>
                                Μερική Ενημέρωση Δήλωσης
                            </h5>
                            <p class="card-text">Μερική ενημέρωση συγκεκριμένων πεδίων μιας δήλωσης.</p>
                            <div class="url-display mb-2">
                                <?php echo "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/api.php?id="; ?>
                            </div>
                            <div class="mb-3">
                                <h6 class="fw-bold">Method: PATCH</h6>
                                <h6 class="fw-bold">Headers:</h6>
                                <pre class="bg-light p-2 rounded">
Content-Type: application/json
X-API-Key: <?php echo htmlspecialchars($apiKey); ?></pre>
                                <h6 class="fw-bold">URL Parameters:</h6>
                                <ul>
                                    <li><code>id</code>: Το ID της δήλωσης προς ενημέρωση</li>
                                </ul>
                                <h6 class="fw-bold">Request Body:</h6>
                                <p class="text-muted">Μπορείτε να συμπεριλάβετε μόνο τα πεδία που θέλετε να ενημερώσετε.</p>
                                <pre class="bg-light p-2 rounded">
{
    "title": "Νέος Τίτλος Δήλωσης",
    "personal_data": {
        "office": "Νέο Αξίωμα",
        "address": "Νέα Διεύθυνση"
    },
    "properties": [
        {
            "type": "Νέος Τύπος",
            "location": "Νέα Τοποθεσία",
            "area": 150
        }
    ],
    "vehicles": [
        {
            "brand": "Νέα Μάρκα",
            "value": 25000
        }
    ]
}</pre>
                                <h6 class="fw-bold">Response:</h6>
                                <pre class="bg-light p-2 rounded">
{
    "status": "success",
    "message": "Declaration partially updated successfully",
    "declaration_id": 123
}</pre>
                            </div>
                            <button class="btn btn-outline-primary copy-btn" data-url="<?php echo "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/api.php?id="; ?>">
                                <i class="fas fa-copy me-2"></i>Αντιγραφή URL
                            </button>
                        </div>
                    </div>
                </div>

                <!-- DELETE Tab -->
                <div class="method-content d-none" id="delete-content">
                    <!-- Delete by Party -->
                    <div class="card api-card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-users me-2"></i>
                                Διαγραφή ανά Κόμμα
                            </h5>
                            <p class="card-text">Διαγραφή δηλώσεων συγκεκριμένου κόμματος.</p>
                            <div class="mb-3">
                                <h6 class="fw-bold">Headers:</h6>
                                <pre class="bg-light p-2 rounded">
Content-Type: application/json
X-API-Key: <?php echo htmlspecialchars($apiKey); ?></pre>
                            </div>
                            <div class="form-group mb-4">
                                <label for="delete-party-input" class="form-label fw-bold mb-2">Επιλέξτε κόμμα:</label>
                                <select class="form-select form-select-lg" id="delete-party-input">
                                    <option value="">Επιλέξτε κόμμα...</option>
                                    <?php foreach ($parties as $party): ?>
                                        <option value="<?php echo htmlspecialchars($party); ?>"><?php echo htmlspecialchars($party); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="url-display mb-3" id="delete-party-url">
                                <?php echo "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/api.php?party="; ?>
                            </div>
                            <button class="btn btn-outline-primary copy-btn" id="delete-party-copy-btn">
                                <i class="fas fa-copy me-2"></i>Αντιγραφή URL
                            </button>
                        </div>
                    </div>

                    <!-- Delete by Year -->
                    <div class="card api-card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-calendar me-2"></i>
                                Διαγραφή ανά Έτος
                            </h5>
                            <p class="card-text">Διαγραφή δηλώσεων συγκεκριμένου έτους.</p>
                            <div class="mb-3">
                                <h6 class="fw-bold">Headers:</h6>
                                <pre class="bg-light p-2 rounded">
Content-Type: application/json
X-API-Key: <?php echo htmlspecialchars($apiKey); ?></pre>
                            </div>
                            <div class="form-group">
                                <label for="delete-year-input" class="form-label fw-bold mb-2">Επιλέξτε έτος:</label>
                                <select class="form-select form-select-lg" id="delete-year-input">
                                    <option value="">Επιλέξτε έτος...</option>
                                    <?php foreach ($years as $year): ?>
                                        <option value="<?php echo htmlspecialchars($year); ?>"><?php echo htmlspecialchars($year); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="url-display mb-2" id="delete-year-url">
                                <?php echo "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/api.php?year="; ?>
                            </div>
                            <button class="btn btn-outline-primary copy-btn" id="delete-year-copy-btn">
                                <i class="fas fa-copy me-2"></i>Αντιγραφή URL
                            </button>
                        </div>
                    </div>

                    <!-- Delete by Position -->
                    <div class="card api-card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-user-tie me-2"></i>
                                Διαγραφή ανά Αξίωμα
                            </h5>
                            <p class="card-text">Διαγραφή δηλώσεων πολιτικών με συγκεκριμένο αξίωμα.</p>
                            <div class="mb-3">
                                <h6 class="fw-bold">Headers:</h6>
                                <pre class="bg-light p-2 rounded">
Content-Type: application/json
X-API-Key: <?php echo htmlspecialchars($apiKey); ?></pre>
                            </div>
                            <div class="form-group">
                                <label for="delete-position-input" class="form-label fw-bold mb-2">Επιλέξτε αξίωμα:</label>
                                <select class="form-select form-select-lg" id="delete-position-input">
                                    <option value="">Επιλέξτε αξίωμα...</option>
                                    <?php foreach ($positions as $position): ?>
                                        <option value="<?php echo htmlspecialchars($position); ?>"><?php echo htmlspecialchars($position); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="url-display mb-2" id="delete-position-url">
                                <?php echo "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/api.php?position="; ?>
                            </div>
                            <button class="btn btn-outline-primary copy-btn" id="delete-position-copy-btn">
                                <i class="fas fa-copy me-2"></i>Αντιγραφή URL
                            </button>
                        </div>
                    </div>

                    <!-- Combined Delete -->
                    <div class="card api-card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-filter me-2"></i>
                                Συνδυασμένη Διαγραφή
                            </h5>
                            <p class="card-text">Διαγραφή δηλώσεων με βάση συνδυασμό κριτηρίων (κόμμα, έτος, αξίωμα).</p>
                            <div class="mb-3">
                                <h6 class="fw-bold">Headers:</h6>
                                <pre class="bg-light p-2 rounded">
Content-Type: application/json
X-API-Key: <?php echo htmlspecialchars($apiKey); ?></pre>
                            </div>
                            <div class="form-group mb-3">
                                <label for="delete-combined-party" class="form-label fw-bold mb-2">Επιλέξτε κόμμα:</label>
                                <select class="form-select form-select-lg" id="delete-combined-party">
                                    <option value="">Επιλέξτε κόμμα...</option>
                                    <?php foreach ($parties as $party): ?>
                                        <option value="<?php echo htmlspecialchars($party); ?>"><?php echo htmlspecialchars($party); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label for="delete-combined-year" class="form-label fw-bold mb-2">Επιλέξτε έτος:</label>
                                <select class="form-select form-select-lg" id="delete-combined-year">
                                    <option value="">Επιλέξτε έτος...</option>
                                    <?php foreach ($years as $year): ?>
                                        <option value="<?php echo htmlspecialchars($year); ?>"><?php echo htmlspecialchars($year); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label for="delete-combined-position" class="form-label fw-bold mb-2">Επιλέξτε αξίωμα:</label>
                                <select class="form-select form-select-lg" id="delete-combined-position">
                                    <option value="">Επιλέξτε αξίωμα...</option>
                                    <?php foreach ($positions as $position): ?>
                                        <option value="<?php echo htmlspecialchars($position); ?>"><?php echo htmlspecialchars($position); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="url-display mb-2" id="delete-combined-url">
                                <?php echo "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/api.php"; ?>
                            </div>
                            <button class="btn btn-outline-primary copy-btn" id="delete-combined-copy-btn">
                                <i class="fas fa-copy me-2"></i>Αντιγραφή URL
                            </button>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <style>
                .method-content {
                    transition: all 0.3s ease;
                }
                .btn-group .btn {
                    min-width: 100px;
                }
                .btn-group .btn i {
                    width: 20px;
                }
            </style>

            <script>
                // Add this to your existing JavaScript
                document.addEventListener('DOMContentLoaded', function() {
                    const methodButtons = document.querySelectorAll('[data-method]');
                    const methodContents = document.querySelectorAll('.method-content');

                    methodButtons.forEach(button => {
                        button.addEventListener('click', function() {
                            const method = this.dataset.method;
                            
                            // Hide all contents
                            methodContents.forEach(content => {
                                content.classList.add('d-none');
                            });
                            
                            // Show selected content
                            document.getElementById(`${method}-content`).classList.remove('d-none');
                            
                            // Update button states
                            methodButtons.forEach(btn => {
                                btn.classList.remove('active');
                            });
                            this.classList.add('active');
                        });
                    });

                    // Show GET content by default
                    document.querySelector('[data-method="get"]').click();
                });
            </script>
        </main>
    </div>

    <!-- Footer -->
    <footer class="bg-light py-4 mt-auto">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-12 col-md-6 text-center text-md-start mb-3 mb-md-0">
                    <p class="mb-0">&copy; 2025 Πόθεν Εσχες © all rights reserved.</p>
                </div>
                <div class="col-12 col-md-6 text-center text-md-end">
                    <div class="d-flex justify-content-center justify-content-md-end gap-3">
                        <a href="about.php" class="text-decoration-none">Ποιοι είμαστε</a>
                        <a href="contact.php" class="text-decoration-none">Επικοινωνία</a>
                        <a href="privacy.php" class="text-decoration-none">Πολιτική Απορρήτου</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Toast Notification -->
    <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                URL αντιγράφηκε επιτυχώς!
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            const toast = new bootstrap.Toast(document.querySelector('.toast'));
            const baseUrl = "http://" + window.location.host + "<?php echo dirname($_SERVER['PHP_SELF']); ?>/api.php";

            // Initialize Select2 for better dropdown experience
            $('.form-select').select2({
                theme: 'bootstrap-5',
                width: '100%',
                language: {
                    noResults: function() {
                        return "Δεν βρέθηκαν αποτελέσματα";
                    },
                    searching: function() {
                        return "Αναζήτηση...";
                    },
                    inputTooLong: function(args) {
                        return "Παρακαλώ διαγράψτε " + (args.input.length - args.maximum) + " χαρακτήρες";
                    },
                    inputTooShort: function(args) {
                        return "Παρακαλώ εισάγετε " + (args.minimum - args.input.length) + " ή περισσότερους χαρακτήρες";
                    },
                    loadingMore: function() {
                        return "Φόρτωση περισσότερων αποτελεσμάτων...";
                    },
                    maximumSelected: function(args) {
                        return "Μπορείτε να επιλέξετε μόνο " + args.maximum + " στοιχεία";
                    },
                    errorLoading: function() {
                        return "Δεν ήταν δυνατή η φόρτωση των αποτελεσμάτων";
                    }
                }
            });

            // Function to update URL display
            function updateUrlDisplay(elementId, url) {
                document.getElementById(elementId).textContent = url;
            }

            // Function to copy URL
            function copyUrl(url) {
                navigator.clipboard.writeText(url).then(() => {
                    toast.show();
                });
            }

            // Party search
            $('#party-input').on('select2:select', function(e) {
                const url = `${baseUrl}?party=${encodeURIComponent(e.target.value)}`;
                updateUrlDisplay('party-url', url);
                $('#party-copy-btn').data('url', url);
            });
            $('#party-copy-btn').on('click', function() {
                copyUrl($(this).data('url'));
            });

            // Year search
            $('#year-input').on('select2:select', function(e) {
                const url = `${baseUrl}?year=${e.target.value}`;
                updateUrlDisplay('year-url', url);
                $('#year-copy-btn').data('url', url);
            });
            $('#year-copy-btn').on('click', function() {
                copyUrl($(this).data('url'));
            });

            // Position search
            $('#position-input').on('select2:select', function(e) {
                const url = `${baseUrl}?position=${encodeURIComponent(e.target.value)}`;
                updateUrlDisplay('position-url', url);
                $('#position-copy-btn').data('url', url);
            });
            $('#position-copy-btn').on('click', function() {
                copyUrl($(this).data('url'));
            });

            // ID search
            $('#id-input').on('select2:select', function(e) {
                const url = `${baseUrl}?id=${e.target.value}`;
                updateUrlDisplay('id-url', url);
                $('#id-copy-btn').data('url', url);
            });
            $('#id-copy-btn').on('click', function() {
                copyUrl($(this).data('url'));
            });

            // Delete search
            $('#delete-id-input').on('select2:select', function(e) {
                const url = `${baseUrl}?id=${e.target.value}`;
                updateUrlDisplay('delete-url', url);
                $('#delete-copy-btn').data('url', url);
            });
            $('#delete-copy-btn').on('click', function() {
                copyUrl($(this).data('url'));
            });

            // Combined search
            function updateCombinedUrl() {
                const params = new URLSearchParams();
                const party = $('#combined-party').val();
                const year = $('#combined-year').val();
                const position = $('#combined-position').val();

                if (party) params.append('party', party);
                if (year) params.append('year', year);
                if (position) params.append('position', position);
                
                const url = `${baseUrl}?${params.toString()}`;
                updateUrlDisplay('combined-url', url);
                $('#combined-copy-btn').data('url', url);
            }

            $('#combined-party, #combined-year, #combined-position').on('select2:select', updateCombinedUrl);

            $('#combined-copy-btn').on('click', function() {
                copyUrl($(this).data('url'));
            });

            // Delete by Party
            $('#delete-party-input').on('select2:select', function(e) {
                const url = `${baseUrl}?party=${encodeURIComponent(e.target.value)}`;
                updateUrlDisplay('delete-party-url', url);
                $('#delete-party-copy-btn').data('url', url);
            });
            $('#delete-party-copy-btn').on('click', function() {
                copyUrl($(this).data('url'));
            });

            // Delete by Year
            $('#delete-year-input').on('select2:select', function(e) {
                const url = `${baseUrl}?year=${e.target.value}`;
                updateUrlDisplay('delete-year-url', url);
                $('#delete-year-copy-btn').data('url', url);
            });
            $('#delete-year-copy-btn').on('click', function() {
                copyUrl($(this).data('url'));
            });

            // Delete by Position
            $('#delete-position-input').on('select2:select', function(e) {
                const url = `${baseUrl}?position=${encodeURIComponent(e.target.value)}`;
                updateUrlDisplay('delete-position-url', url);
                $('#delete-position-copy-btn').data('url', url);
            });
            $('#delete-position-copy-btn').on('click', function() {
                copyUrl($(this).data('url'));
            });

            // Combined Delete
            function updateCombinedDeleteUrl() {
                const params = new URLSearchParams();
                const party = $('#delete-combined-party').val();
                const year = $('#delete-combined-year').val();
                const position = $('#delete-combined-position').val();

                if (party) params.append('party', party);
                if (year) params.append('year', year);
                if (position) params.append('position', position);
                
                const url = `${baseUrl}?${params.toString()}`;
                updateUrlDisplay('delete-combined-url', url);
                $('#delete-combined-copy-btn').data('url', url);
            }

            $('#delete-combined-party, #delete-combined-year, #delete-combined-position').on('select2:select', updateCombinedDeleteUrl);

            $('#delete-combined-copy-btn').on('click', function() {
                copyUrl($(this).data('url'));
            });

            // All politicians search
            $('[data-url="' + baseUrl + '"]').on('click', function() {
                copyUrl(baseUrl);
            });
        });
    </script>
</body>
</html> 