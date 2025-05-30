<!-- filepath: c:\xampp\htdocs\webengineering_uni\webengineering_uni\modules\admin_module\manage_submissions.php -->
<?php
$conn = include '../../config/db_connection.php';
session_start();

// Handle declaration actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $declaration_id = $_POST['declaration_id'];
        $status = $_POST['status'];
        
        if ($status === 'Approved') {
            $stmt = $conn->prepare("UPDATE declarations SET status = 'Approved' WHERE id = ?");
            $stmt->execute([$declaration_id]);
        } elseif ($status === 'Rejected') {
            $stmt = $conn->prepare("UPDATE declarations SET status = 'Rejected' WHERE id = ?");
            $stmt->execute([$declaration_id]);
        }
    }
    
    if (isset($_POST['delete_declaration'])) {
        $declaration_id = $_POST['declaration_id'];
        $stmt = $conn->prepare("DELETE FROM declarations WHERE id = ?");
        $stmt->execute([$declaration_id]);
    }
    
    // Handle bulk actions
    if (isset($_POST['bulk_action']) && isset($_POST['selected_declarations'])) {
        $selected_ids = $_POST['selected_declarations'];
        $action = $_POST['bulk_action'];
        
        if ($action === 'approve') {
            $stmt = $conn->prepare("UPDATE declarations SET status = 'Approved' WHERE id IN (" . implode(',', array_fill(0, count($selected_ids), '?')) . ")");
            $stmt->execute($selected_ids);
        } elseif ($action === 'reject') {
            $stmt = $conn->prepare("UPDATE declarations SET status = 'Rejected' WHERE id IN (" . implode(',', array_fill(0, count($selected_ids), '?')) . ")");
            $stmt->execute($selected_ids);
        } elseif ($action === 'delete') {
            $stmt = $conn->prepare("DELETE FROM declarations WHERE id IN (" . implode(',', array_fill(0, count($selected_ids), '?')) . ")");
            $stmt->execute($selected_ids);
        }
    }
}

// Get search and filter parameters
$search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$year = isset($_GET['year']) ? htmlspecialchars($_GET['year']) : '';
$status = isset($_GET['status']) ? htmlspecialchars($_GET['status']) : '';

// Base query
$query = "SELECT d.*, pd.full_name as person_name, p.name as party_name, sp.year as submission_year 
          FROM declarations d 
          LEFT JOIN personal_data pd ON d.id = pd.declaration_id 
          LEFT JOIN parties p ON pd.party_id = p.id 
          LEFT JOIN submission_periods sp ON d.submission_period_id = sp.id 
          WHERE 1=1";
$params = array();

// Add search conditions
if (!empty($search)) {
    $query .= " AND (pd.full_name LIKE ? OR p.name LIKE ? OR d.title LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if (!empty($year)) {
    $query .= " AND sp.year = ?";
    $params[] = $year;
}

if (!empty($status)) {
    $query .= " AND d.status = ?";
    $params[] = $status;
}

// Add sorting
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'submission_date';
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'desc';
$query .= " ORDER BY $sort_by $sort_order";

// Prepare and execute query
$stmt = $conn->prepare($query);
$stmt->execute($params);
$declarations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique years for dropdown
$yearQuery = "SELECT DISTINCT year FROM submission_periods WHERE is_active = 1 ORDER BY year DESC";
$yearStmt = $conn->query($yearQuery);
$years = $yearStmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Submissions - ΠΟΘΕΝ ΕΣΧΕΣ</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../../assets/images/iconlogo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <!-- Flag Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css"/>
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
                                    <a href="../submit_module/favorites.php" class="nav-link py-2">
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

    <!-- Main Content -->
    <main class="container mt-5 pt-5">
        <!-- Back Button -->
        <div class="mb-4">
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Επιστροφή στο Dashboard
            </a>
        </div>
        
        <h1 class="text-center mb-4">Διαχείριση Δηλώσεων</h1>
        
        <!-- Search and Filter Form -->
        <div class="card feature-card mb-4">
            <div class="card-body">
                <form class="row g-3" method="GET">
                    <div class="col-md-4">
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
                        <label class="form-label">Κατάσταση</label>
                        <select name="status" class="form-select">
                            <option value="">Όλες οι Καταστάσεις</option>
                            <option value="Pending" <?php echo $status == 'Pending' ? 'selected' : ''; ?>>Σε Εκκρεμότητα</option>
                            <option value="Approved" <?php echo $status == 'Approved' ? 'selected' : ''; ?>>Εγκεκριμένες</option>
                            <option value="Rejected" <?php echo $status == 'Rejected' ? 'selected' : ''; ?>>Απορριφθείσες</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-warning text-dark w-100">
                            <i class="bi bi-search"></i> Αναζήτηση
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bulk Actions -->
        <div class="card feature-card mb-4">
            <div class="card-body">
                <form method="POST" id="bulkActionForm">
                    <div class="d-flex gap-2 mb-3">
                        <select name="bulk_action" class="form-select" style="max-width: 200px;">
                            <option value="">Επιλέξτε Ενέργεια</option>
                            <option value="approve">Έγκριση</option>
                            <option value="reject">Απόρριψη</option>
                        </select>
                        <button type="submit" class="btn btn-warning text-dark" id="bulkActionBtn" disabled>
                            Εφαρμογή
                        </button>
                    </div>

                    <!-- Declarations Table -->
                    <div class="table-responsive">
                        <table class="table table-hover" id="declarationsTable">
                            <thead>
                                <tr>
                                    <th>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="selectAll">
                                        </div>
                                    </th>
                                    <th>ID</th>
                                    <th>Όνομα</th>
                                    <th>Κόμμα</th>
                                    <th>Έτος</th>
                                    <th>Ημερομηνία Υποβολής</th>
                                    <th>Κατάσταση</th>
                                    <th>Ενέργειες</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($declarations as $row): ?>
                                    <tr>
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input declaration-checkbox" type="checkbox" name="selected_declarations[]" value="<?= $row['id'] ?>">
                                            </div>
                                        </td>
                                        <td><?= $row['id'] ?></td>
                                        <td><?= $row['person_name'] ?></td>
                                        <td><?= $row['party_name'] ?></td>
                                        <td><?= $row['submission_year'] ?></td>
                                        <td><?= $row['submission_date'] ?></td>
                                        <td>
                                            <?php if ($row['status'] === 'Approved'): ?>
                                                <span class="badge bg-success">Εγκεκριμένη</span>
                                            <?php elseif ($row['status'] === 'Rejected'): ?>
                                                <span class="badge bg-danger">Απορριφθείσα</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Σε Εκκρεμότητα</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="../submit_module/view-declaration.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm">
                                                    <i class="bi bi-eye"></i> Προβολή
                                                </a>
                                                <?php if ($row['status'] === 'Pending'): ?>
                                                    <button type="button" class="btn btn-success btn-sm" onclick="confirmAction(<?= $row['id'] ?>, 'approve')">
                                                        <i class="bi bi-check-circle"></i> Έγκριση
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmAction(<?= $row['id'] ?>, 'reject')">
                                                        <i class="bi bi-x-circle"></i> Απόρριψη
                                                    </button>
                                                <?php elseif ($row['status'] === 'Rejected'): ?>
                                                    <button type="button" class="btn btn-success btn-sm" onclick="confirmAction(<?= $row['id'] ?>, 'approve')">
                                                        <i class="bi bi-check-circle"></i> Έγκριση
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmAction(<?= $row['id'] ?>, 'delete')">
                                                        <i class="bi bi-trash"></i> Διαγραφή
                                                    </button>
                                                <?php else: // Approved ?>
                                                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmAction(<?= $row['id'] ?>, 'reject')">
                                                        <i class="bi bi-x-circle"></i> Απόρριψη
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmAction(<?= $row['id'] ?>, 'delete')">
                                                        <i class="bi bi-trash"></i> Διαγραφή
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Επιβεβαίωση Ενέργειας</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="confirmationMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ακύρωση</button>
                    <form method="POST" id="actionForm">
                        <input type="hidden" name="declaration_id" id="declarationId">
                        <input type="hidden" name="status" id="actionStatus">
                        <button type="submit" name="update_status" class="btn btn-primary">Επιβεβαίωση</button>
                    </form>
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
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#declarationsTable').DataTable({
                order: [[1, 'desc']], // Sort by ID by default
                pageLength: 10,
                dom: '<"row"<"col-12"l>>rtip', // Remove search field (f) from dom
                language: {
                    search: "",
                    lengthMenu: "Εμφάνιση _MENU_ εγγραφών ανά σελίδα",
                    info: "Εμφάνιση _START_ έως _END_ από _TOTAL_ εγγραφές",
                    infoEmpty: "Δεν υπάρχουν εγγραφές",
                    infoFiltered: "(φιλτραρισμένες από _MAX_ συνολικές εγγραφές)",
                    zeroRecords: "Δεν βρέθηκαν εγγραφές",
                    paginate: {
                        first: "Πρώτη",
                        last: "Τελευταία",
                        next: "Επόμενη",
                        previous: "Προηγούμενη"
                    }
                }
            });

            // Handle select all checkbox
            $('#selectAll').change(function() {
                $('.declaration-checkbox').prop('checked', $(this).prop('checked'));
                updateBulkActionButton();
            });

            // Handle individual checkboxes
            $('.declaration-checkbox').change(function() {
                updateBulkActionButton();
                // Update select all checkbox
                $('#selectAll').prop('checked', $('.declaration-checkbox:checked').length === $('.declaration-checkbox').length);
            });

            // Update bulk action button state
            function updateBulkActionButton() {
                $('#bulkActionBtn').prop('disabled', $('.declaration-checkbox:checked').length === 0);
            }

            // Handle bulk action form submission
            $('#bulkActionForm').submit(function(e) {
                if (!confirm('Είστε σίγουροι ότι θέλετε να εκτελέσετε αυτή την ενέργεια στις επιλεγμένες δηλώσεις;')) {
                    e.preventDefault();
                }
            });
        });

        // Confirmation modal for individual actions
        function confirmAction(id, action) {
            const modal = new bootstrap.Modal(document.getElementById('confirmationModal'));
            const message = document.getElementById('confirmationMessage');
            const form = document.getElementById('actionForm');
            
            let actionText = '';
            switch(action) {
                case 'approve':
                    actionText = 'έγκριση';
                    break;
                case 'reject':
                    actionText = 'απόρριψη';
                    break;
                case 'delete':
                    actionText = 'διαγραφή';
                    break;
            }
            
            message.textContent = `Είστε σίγουροι ότι θέλετε να προχωρήσετε στην ${actionText} αυτής της δήλωσης;`;
            document.getElementById('declarationId').value = id;
            
            if (action === 'delete') {
                form.action = 'manage_submissions.php';
                form.innerHTML = `
                    <input type="hidden" name="declaration_id" value="${id}">
                    <button type="submit" name="delete_declaration" class="btn btn-danger">Διαγραφή</button>
                `;
            } else {
                form.action = 'manage_submissions.php';
                form.innerHTML = `
                    <input type="hidden" name="declaration_id" value="${id}">
                    <input type="hidden" name="status" value="${action === 'approve' ? 'Approved' : 'Rejected'}">
                    <button type="submit" name="update_status" class="btn btn-primary">Επιβεβαίωση</button>
                `;
            }
            
            modal.show();
        }
    </script>
</body>
</html>