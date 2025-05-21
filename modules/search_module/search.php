<?php
require_once '../../config/db_connection.php';
session_start();

// Get database connection
$pdo = require '../../config/db_connection.php';

// Get search parameters
$search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$year = isset($_GET['year']) ? htmlspecialchars($_GET['year']) : '';
$position = isset($_GET['position']) ? htmlspecialchars($_GET['position']) : '';

// Base query
$query = "SELECT d.*, pd.full_name, pd.office, p.name as party_name, sp.year as submission_year, d.image_url 
          FROM declarations d 
          LEFT JOIN personal_data pd ON d.id = pd.declaration_id 
          LEFT JOIN parties p ON pd.party_id = p.id 
          LEFT JOIN submission_periods sp ON d.submission_period_id = sp.id 
          WHERE d.status = 'Approved'";
$params = array();

// Add search conditions
if (!empty($search)) {
    $query .= " AND (pd.full_name LIKE ? OR pd.office LIKE ? OR d.title LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if (!empty($year)) {
    $query .= " AND sp.year = ?";
    $params[] = $year;
}

if (!empty($position)) {
    $query .= " AND pd.office = ?";
    $params[] = $position;
}

// Prepare and execute query
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$declarations = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalResults = count($declarations);

// Get unique years for dropdown
$yearQuery = "SELECT DISTINCT year FROM submission_periods WHERE is_active = 1 ORDER BY year DESC";
$yearStmt = $pdo->query($yearQuery);
$years = $yearStmt->fetchAll(PDO::FETCH_COLUMN);

// Get unique positions for dropdown
$positionQuery = "SELECT DISTINCT office FROM personal_data WHERE office IS NOT NULL ORDER BY office";
$positionStmt = $pdo->query($positionQuery);
$positions = $positionStmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Declarations - Asset Declaration System</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../../assets/images/iconlogo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css"/>
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../../assets/css/style.css" rel="stylesheet">
</head>
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

    
element.style {
}
div.dataTables_wrapper div.dataTables_paginate ul.pagination {
    margin: 2px 0;
    white-space: nowrap;
    justify-content: flex-end;
}
.pagination {
    --bs-pagination-padding-x: 0.75rem;
    --bs-pagination-padding-y: 0.375rem;
    --bs-pagination-font-size: 1rem;
    --bs-pagination-color: #ffc107;
    --bs-pagination-bg: var(--bs-body-bg);
    --bs-pagination-border-width: var(--bs-border-width);
    --bs-pagination-border-color: var(--bs-border-color);
    --bs-pagination-border-radius: var(--bs-border-radius);
    --bs-pagination-hover-color: #ffc107;
    --bs-pagination-hover-bg: var(--bs-tertiary-bg);
    --bs-pagination-hover-border-color: var(--bs-border-color);
    --bs-pagination-focus-color: #ffc107;
    --bs-pagination-focus-bg: var(--bs-secondary-bg);
    --bs-pagination-focus-box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    --bs-pagination-active-color: #fff;
    --bs-pagination-active-bg: #ffc107;
    --bs-pagination-active-border-color: #ffc107;
    --bs-pagination-disabled-color: var(--bs-secondary-color);
    --bs-pagination-disabled-bg: var(--bs-secondary-bg);
    --bs-pagination-disabled-border-color: var(--bs-border-color);
    display: flex
;
    padding-left: 0;
    list-style: none;
}

</style>
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
        <!-- Main Content -->
        <main class="container my-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Αναζήτηση Δηλώσεων Περιουσίας</h1>
                <div class="text-muted">
                    Βρέθηκαν <?php echo $totalResults; ?> δήλωση/εις
                </div>
            </div>

            <!-- Search Form -->
            <div class="card feature-card mb-4">
                <div class="card-body">
                    <form class="row g-3" method="GET">
                        <div class="col-md-6">
                            <label class="form-label">Αναζήτηση</label>
                            <input type="text" name="search" class="form-control" placeholder="Αναζήτηση με όνομα" value="<?php echo $search; ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Έτος</label>
                            <select name="year" class="form-select">
                                <option value="">Όλα τα Έτη</option>
                                <?php foreach ($years as $yearOption): ?>
                                    <option value="<?php echo $yearOption; ?>" <?php echo $year == $yearOption ? 'selected' : ''; ?>>
                                        <?php echo $yearOption; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Θέση</label>
                            <select name="position" class="form-select">
                                <option value="">Όλες οι Θέσεις</option>
                                <?php foreach ($positions as $positionOption): ?>
                                    <option value="<?php echo $positionOption; ?>" <?php echo $position == $positionOption ? 'selected' : ''; ?>>
                                        <?php echo $positionOption; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-warning text-dark">
                                <i class="bi bi-search"></i> Αναζήτηση
                            </button>
                            <a href="search.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Καθαρισμός Φίλτρων
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Search Results -->
            <div class="card feature-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="declarationsTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40%">Στοιχεία Προσώπου</th>
                                    <th style="width: 20%">Τίτλος</th>
                                    <th style="width: 15%">Έτος Υποβολής</th>
                                    <th style="width: 15%">Κομματική ένταξη</th>
                                    <th style="width: 10%">Ενέργειες</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($totalResults > 0): ?>
                                    <?php foreach ($declarations as $declaration): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <?php if (!empty($declaration['image_url'])): ?>
                                                            <img src="<?php echo htmlspecialchars($declaration['image_url']); ?>" 
                                                                 alt="<?php echo htmlspecialchars($declaration['full_name']); ?>" 
                                                                 class="avatar-circle"
                                                                 style="width: 45px; height: 45px; object-fit: cover;">
                                                        <?php else: ?>
                                                            <div class="avatar-circle" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; background-color: #e9ecef; color: #495057; font-weight: bold;">
                                                                <?php echo mb_substr($declaration['full_name'], 0, 1); ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="ms-3">
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($declaration['full_name']); ?></h6>
                                                        <small class="text-muted">
                                                            <i class="bi bi-building"></i> <?php echo htmlspecialchars($declaration['office']); ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning text-dark title-badge" data-title="<?php echo htmlspecialchars($declaration['title']); ?>" style="cursor: pointer;"><?php echo htmlspecialchars($declaration['title']); ?></span>
                                            </td>
                                            <td>
                                                <strong><?php echo $declaration['submission_year']; ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning text-dark political-badge" data-party="<?php echo htmlspecialchars($declaration['party_name']); ?>"><?php echo htmlspecialchars($declaration['party_name']); ?></span>
                                            </td>
                                            <td>
                                                <a href="../submit_module/view-declaration.php?id=<?php echo $declaration['id']; ?>" class="btn btn-sm btn-warning text-dark" title="Προβολή Λεπτομερειών Δήλωσης">
                                                    <i class="bi bi-eye"></i> Προβολή
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>  
                                        <td></td>
                                        <td class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bi bi-search"></i>
                                                <p class="mb-0">Δεν βρέθηκαν δηλώσεις που να ταιριάζουν με τα κριτήρια αναζήτησής σας.</p>
                                            </div>
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery (required for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <!-- Custom JS -->
    <script src="../../assets/js/main.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#declarationsTable').DataTable({
                order: [], // No default sorting
                pageLength: 10,
                responsive: true,
                dom: '<"row"<"col-12"l>>rtip', // Remove search field (f) from dom
                language: {
                    search: "", // Remove the "Quick Search:" label
                    lengthMenu: "Εμφάνιση _MENU_ εγγραφών ανά σελίδα",
                    info: "Εμφάνιση _START_ έως _END_ από _TOTAL_ εγγραφές",
                    infoEmpty: "Εμφάνιση 0 έως 0 από 0 εγγραφές",
                    infoFiltered: "(φιλτραρισμένες από _MAX_ συνολικές εγγραφές)",
                    zeroRecords: "Δεν βρέθηκαν εγγραφές που να ταιριάζουν",
                    paginate: {
                        first: "Πρώτη",
                        last: "Τελευταία",
                        next: "Επόμενη",
                        previous: "Προηγούμενη"
                    }
                },
                columnDefs: [
                    {
                        targets: [0, 2], // Person Details (0) and Submission Year (2)
                        orderable: true
                    },
                    {
                        targets: [1, 3, 4], // Title (1), Party (3), and Actions (4)
                        orderable: false
                    }
                ]
            });

            // Add click handler for political badges
            $(document).on('click', '.political-badge', function() {
                const partyName = $(this).data('party');
                // Update the search input
                $('input[name="search"]').val(partyName);
                // Filter the table
                table.search(partyName).draw();
            });

            // Add click handler for title badges
            $(document).on('click', '.title-badge', function() {
                const title = $(this).data('title');
                // Update the search input
                $('input[name="search"]').val(title);
                // Filter the table
                table.search(title).draw();
            });
        });
    </script>
</body>
</html> 
