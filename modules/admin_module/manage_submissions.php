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
            $stmt = $conn->prepare("DELETE FROM declarations WHERE id = ?");
            $stmt->execute([$declaration_id]);
        }
    }
    
    // Handle bulk actions
    if (isset($_POST['bulk_action']) && isset($_POST['selected_declarations'])) {
        $selected_ids = $_POST['selected_declarations'];
        $action = $_POST['bulk_action'];
        
        if ($action === 'approve') {
            $stmt = $conn->prepare("UPDATE declarations SET status = 'Approved' WHERE id IN (" . implode(',', array_fill(0, count($selected_ids), '?')) . ")");
            $stmt->execute($selected_ids);
        } elseif ($action === 'reject' || $action === 'delete') {
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
    <title>Διαχείριση Δηλώσεων - ΠΟΘΕΝ ΕΣΧΕΣ</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../../assets/images/iconlogo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Σε Εκκρεμότητα</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="../submit_module/view-declaration.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm">
                                                    <i class="bi bi-eye"></i> Προβολή
                                                </a>
                                                <?php if ($row['status'] !== 'Approved'): ?>
                                                    <button type="button" class="btn btn-success btn-sm" onclick="confirmAction(<?= $row['id'] ?>, 'approve')">
                                                        <i class="bi bi-check-circle"></i> Έγκριση
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmAction(<?= $row['id'] ?>, 'delete')">
                                                        <i class="bi bi-x-circle"></i> Απόρριψη
                                                    </button>
                                                <?php else: ?>
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
                case 'delete':
                    actionText = 'διαγραφή';
                    break;
            }
            
            message.textContent = `Είστε σίγουροι ότι θέλετε να προχωρήσετε στην ${actionText} αυτής της δήλωσης;`;
            document.getElementById('declarationId').value = id;
            document.getElementById('actionStatus').value = action === 'delete' ? 'Rejected' : 'Approved';
            
            modal.show();
        }
    </script>
</body>
</html>