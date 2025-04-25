<?php
require_once '../../config/db_connection.php';

// Get database connection
$conn = require '../../config/db_connection.php';

// Get search parameters
$search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$year = isset($_GET['year']) ? htmlspecialchars($_GET['year']) : '';
$position = isset($_GET['position']) ? htmlspecialchars($_GET['position']) : '';

// Base query
$query = "SELECT * FROM people WHERE 1=1";
$params = array();

// Add search conditions
if (!empty($search)) {
    $query .= " AND (name LIKE ? OR office LIKE ? OR title LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if (!empty($year)) {
    $query .= " AND YEAR(date_of_submission) = ?";
    $params[] = $year;
}

if (!empty($position)) {
    $query .= " AND office = ?";
    $params[] = $position;
}

// Prepare and execute query
$stmt = $conn->prepare($query);
$stmt->execute($params);
$declarations = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalResults = count($declarations);

// Get unique years for dropdown
$yearQuery = "SELECT DISTINCT YEAR(date_of_submission) as year FROM people WHERE date_of_submission IS NOT NULL ORDER BY year DESC";
$yearStmt = $conn->query($yearQuery);
$years = $yearStmt->fetchAll(PDO::FETCH_COLUMN);

// Get unique positions for dropdown
$positionQuery = "SELECT DISTINCT office FROM people WHERE office IS NOT NULL ORDER BY office";
$positionStmt = $conn->query($positionQuery);
$positions = $positionStmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Declarations - Asset Declaration System</title>
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

        /* DataTables Pagination Styling */
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.5rem 1rem;
        margin: 0 0.25rem;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        background: #fff;
        color: #000 !important;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #e9ecef;
        border-color: #dee2e6;
        color: #000 !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #ffc107 !important;
        border-color: #ffc107;
        color: #000 !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        background: #e9ecef;
        border-color: #dee2e6;
        color: #6c757d !important;
        cursor: not-allowed;
    }
    
</style>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="../../index.html">
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
                        <a class="nav-link" href="../../index.html">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="./search.php">Search</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="./statistics.php">Statistics</a>
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
                <!-- ... existing mobile menu code ... -->
            </div>
        </div>
    </nav>

    <!-- Add padding-top to account for fixed navbar -->
    <div class="pt-5">
        <!-- Main Content -->
        <main class="container my-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Search Asset Declarations</h1>
                <div class="text-muted">
                    Found <?php echo $totalResults; ?> declaration(s)
                </div>
            </div>

            <!-- Search Form -->
            <div class="card feature-card mb-4">
                <div class="card-body">
                    <form class="row g-3" method="GET">
                        <div class="col-md-6">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Search by name, position, or email" value="<?php echo $search; ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Year</label>
                            <select name="year" class="form-select">
                                <option value="">All Years</option>
                                <?php foreach ($years as $yearOption): ?>
                                    <option value="<?php echo $yearOption; ?>" <?php echo $year == $yearOption ? 'selected' : ''; ?>>
                                        <?php echo $yearOption; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Position</label>
                            <select name="position" class="form-select">
                                <option value="">All Positions</option>
                                <?php foreach ($positions as $positionOption): ?>
                                    <option value="<?php echo $positionOption; ?>" <?php echo $position == $positionOption ? 'selected' : ''; ?>>
                                        <?php echo $positionOption; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-warning text-dark">
                                <i class="bi bi-search"></i> Search
                            </button>
                            <a href="search.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Clear Filters
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Search Results -->
            <div class="card feature-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40%">Person Details</th>
                                    <th style="width: 20%">Title</th>
                                    <th style="width: 15%">Submission Date</th>
                                    <th style="width: 15%">Political Affiliation</th>
                                    <th style="width: 10%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($declarations as $declaration): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <div class="avatar-circle"><?php echo substr($declaration['name'], 0, 1); ?></div>
                                                </div>
                                                <div class="ms-3">
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($declaration['name']); ?></h6>
                                                    <small class="text-muted">
                                                        <i class="bi bi-building"></i> <?php echo htmlspecialchars($declaration['office']); ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning text-dark"><?php echo htmlspecialchars($declaration['title']); ?></span>
                                        </td>
                                        <td>
                                            <strong><?php echo date('Y', strtotime($declaration['date_of_submission'])); ?></strong>
                                            <div class="small text-muted">
                                                Submitted: <?php echo date('d/m/Y', strtotime($declaration['date_of_submission'])); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning text-dark"><?php echo htmlspecialchars($declaration['political_affiliation']); ?></span>
                                        </td>
                                        <td>
                                            <a href="../submit_module/view-declaration.php?id=<?php echo $declaration['id']; ?>" class="btn btn-sm btn-warning text-dark" title="View Declaration Details">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if ($totalResults === 0): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bi bi-search"></i>
                                                <p class="mb-0">No declarations found matching your search criteria.</p>
                                            </div>
                                        </td>
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
    <!-- jQuery (required for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <!-- Custom JS -->
    <script src="../../assets/js/main.js"></script>
    <script>
        $(document).ready(function() {
            $('.table').DataTable({
                order: [], // No default sorting
                pageLength: 10,
                dom: '<"row"<"col-12"l>>rtip', // Remove search field (f) from dom
                language: {
                    search: "", // Remove the "Quick Search:" label
                    lengthMenu: "Show _MENU_ entries per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    infoFiltered: "(filtered from _MAX_ total entries)",
                    zeroRecords: "No matching records found",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                },
                columnDefs: [
                    {
                        targets: [0, 2], // Person Details (0) and Submission Date (2)
                        orderable: true,
                        orderSequence: ['asc', 'desc', null] // Use null instead of empty string
                    },
                    {
                        targets: [1, 3, 4], // Title, Political Affiliation, and Actions
                        orderable: false
                    }
                ]
            });
        });
    </script>
</body>
</html> 
