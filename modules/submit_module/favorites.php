<?php
session_start();
require_once '../../config/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: ../../index.php");
    exit();
}

// Get favorite declarations
$stmt = $pdo->prepare("
    SELECT d.*, pd.full_name, pd.office, p.name as party_name, f.id as favorite_id
    FROM declarations d
    JOIN personal_data pd ON d.id = pd.declaration_id
    LEFT JOIN parties p ON pd.party_id = p.id
    JOIN favorites f ON d.id = f.declaration_id
    WHERE f.user_id = ?
    ORDER BY d.submission_date DESC
");
$stmt->execute([$_SESSION['id']]);
$favorites = $stmt->fetchAll();

// Get followed politicians
$stmt = $pdo->prepare("
    SELECT pd.*, p.name as party_name, f.id as follow_id,
           (SELECT COUNT(*) FROM declarations d WHERE d.id IN (
               SELECT declaration_id FROM personal_data WHERE full_name = pd.full_name
           )) as declaration_count
    FROM personal_data pd
    LEFT JOIN parties p ON pd.party_id = p.id
    JOIN follows f ON pd.id = f.politician_id
    WHERE f.user_id = ?
    ORDER BY pd.full_name
");
$stmt->execute([$_SESSION['id']]);
$followed_politicians = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Αγαπημένες Δηλώσεις - ΠΟΘΕΝ ΕΣΧΕΣ</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../../assets/images/iconlogo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Flag Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css"/>
    <!-- Custom CSS -->
    <link href="../../assets/css/style.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
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
                        <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Politician')): ?>
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
                                <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Politician')): ?>
                                    <li>
                                        <a class="dropdown-item" href="my-declarations.php">
                                            <i class="bi bi-file-earmark-text"></i> Οι Δηλώσεις μου
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    <li>
                                        <a class="dropdown-item" href="../submit_module/favorites.php">
                                            <i class="bi bi-heart"></i> Αγαπημένα
                                        </a>
                                    </li>
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
                                    <li>
                                        <a class="dropdown-item" href="../admin_module/dashboard.php">
                                            <i class="bi bi-speedometer2"></i> Admin Dashboard
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Public' || $_SESSION['role'] === 'Politician')): ?>
                                    <li>
                                        <a class="dropdown-item" href="../api_module/api_documentation.php">
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
                            <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Politician')): ?>
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
                                <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Politician')): ?>
                                        <a class="dropdown-item" href="my-declarations.php">
                                            <i class="bi bi-file-earmark-text"></i> Οι Δηλώσεις μου
                                        </a>
                                    <?php endif; ?>
                                        <a class="dropdown-item" href="../submit_module/favorites.php">
                                            <i class="bi bi-heart"></i> Αγαπημένα
                                        </a>
                                   
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
                                    <a href="../admin_module/dashboard.php" class="nav-link py-2">
                                        <i class="bi bi-speedometer2"></i> Admin Dashboard
                                    </a>
                                    <?php endif; ?>
                                    <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Public' || $_SESSION['role'] === 'Politician')): ?>
                                    <a href="../api_module/api_documentation.php" class="nav-link py-2">
                                        <i class="bi bi-code-square"></i> API Documentation
                                    </a>
                                    <?php endif; ?>
                                    <a href="../login_module/logout.php" class="nav-link py-2">
                                        <i class="bi bi-box-arrow-right"></i> Αποσύνδεση
                                    </a>
                                <?php else: ?>
                                    <a href="../login_module/login.php" class="nav-link py-2">
                                        <i class="bi bi-box-arrow-in-right me-2"></i> Σύνδεση
                                    </a>
                                    <a href="../login_module/register.php" class="nav-link py-2">
                                        <i class="bi bi-person-plus me-2"></i> Εγγραφή
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
        <div class="container mt-5">
            <h1 class="mb-4">Αγαπημένα</h1>
            
            <!-- Tabs -->
            <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="favorites-tab" data-bs-toggle="tab" data-bs-target="#favorites" type="button" role="tab">
                        <i class="fas fa-star"></i> Δηλώσεις στα Αγαπημένα
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="followed-tab" data-bs-toggle="tab" data-bs-target="#followed" type="button" role="tab">
                        <i class="fas fa-user-friends"></i> Πολιτικοί που ακολουθώ
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="myTabContent">
                <!-- Favorites Tab -->
                <div class="tab-pane fade show active" id="favorites" role="tabpanel">
                    <?php if (empty($favorites)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Δεν έχετε καμία δήλωση στα αγαπημένα.
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($favorites as $favorite): ?>
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($favorite['full_name']); ?></h5>
                                            <h6 class="card-subtitle mb-2 text-muted"><?php echo htmlspecialchars($favorite['party_name']); ?></h6>
                                            <p class="card-text">
                                                <small class="text-muted">
                                                    Υποβλήθηκε: <?php echo date('d/m/Y', strtotime($favorite['submission_date'])); ?>
                                                </small>
                                            </p>
                                            <a href="view-declaration.php?id=<?php echo $favorite['id']; ?>" class="btn btn-primary">
                                                Προβολή Δήλωσης
                                            </a>
                                            <button class="btn btn-outline-danger remove-favorite" data-declaration-id="<?php echo $favorite['id']; ?>">
                                                <i class="fas fa-star"></i> Αφαίρεση
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Followed Politicians Tab -->
                <div class="tab-pane fade" id="followed" role="tabpanel">
                    <?php if (empty($followed_politicians)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Δεν ακολουθείτε κανέναν πολιτικό.
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($followed_politicians as $politician): ?>
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($politician['full_name']); ?></h5>
                                            <h6 class="card-subtitle mb-2 text-muted"><?php echo htmlspecialchars($politician['party_name']); ?></h6>
                                            <p class="card-text">
                                                <small class="text-muted">
                                                    Δηλώσεις: <?php echo $politician['declaration_count']; ?>
                                                </small>
                                            </p>
                                            <a href="../search_module/search.php?search=<?php echo urlencode($politician['full_name']); ?>" class="btn btn-primary">
                                                Προβολή Δηλώσεων
                                            </a>
                                            <button class="btn btn-outline-danger unfollow-politician" data-politician-id="<?php echo $politician['id']; ?>">
                                                <i class="fas fa-user-minus"></i> Αφαίρεση
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

     <!-- Footer -->
    <footer class="bg-light py-4 mt-auto border-top">
        <div class="container">
            <div class="row justify-content-center align-items-center">
                
                <div class="col-12 text-center mb-2">
                    <div class="mb-2">
                        <img src="../../assets/images/iconlogo.png" alt="Πόθεν Έσχες Logo" style="height: 42px; width: 42px; object-fit: contain;" />
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

    <?php include '../../includes/about-us-modal.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Remove favorite
            $('.remove-favorite').click(function() {
                const button = $(this);
                const declarationId = button.data('declaration-id');
                
                $.post('remove-favorite.php', { declaration_id: declarationId }, function(response) {
                    if (response.success) {
                        button.closest('.col-md-6').fadeOut();
                    } else {
                        alert('Error removing favorite');
                    }
                });
            });

            // Unfollow politician
            $('.unfollow-politician').click(function() {
                const button = $(this);
                const politicianId = button.data('politician-id');
                
                $.post('unfollow.php', { politician_id: politicianId }, function(response) {
                    if (response.success) {
                        button.closest('.col-md-6').fadeOut();
                    } else {
                        alert('Error unfollowing politician');
                    }
                });
            });
        });
    </script>
</body>
</html> 
