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
    <main class="container mt-5 pt-5">
        <!-- Back Button -->
        <div class="mb-4">
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Επιστροφή στο Dashboard
            </a>
        </div>
        
        <h1 class="text-center mb-4">Ρυθμίσεις Συστήματος</h1>
        
        <!-- Political Parties Section -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="card-title mb-4">Πολιτικά Κόμματα</h2>
                
                <!-- Party Search -->
                <form method="GET" class="mb-4">
                    <div class="input-group">
                        <input type="text" class="form-control" name="party_search" placeholder="Αναζήτηση κόμματος..." value="<?= htmlspecialchars($party_search) ?>">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-search"></i> Αναζήτηση
                        </button>
                        <?php if (!empty($party_search)): ?>
                            <a href="system_config.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Καθαρισμός
                            </a>
                        <?php endif; ?>
                    </div>
                </form>

                <!-- Add Party Form -->
                <form method="POST" class="mb-4">
                    <div class="mb-3">
                        <label for="party" class="form-label">Όνομα Κόμματος</label>
                        <input type="text" class="form-control" id="party" name="party" required>
                    </div>
                    <button type="submit" name="add_party" class="btn btn-primary" style="background-color: #ED9635; border-color: #ED9635;">
                        <i class="bi bi-plus-circle"></i> Προσθήκη Κόμματος
                    </button>
                </form>
                
                <!-- Parties Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Όνομα</th>
                                <th>Κατάσταση</th>
                                <th>Ενέργειες</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($parties as $party): ?>
                                <tr>
                                    <td><?= $party['id'] ?></td>
                                    <td><?= htmlspecialchars($party['name']) ?></td>
                                    <td>
                                        <?php if ($party['is_used']): ?>
                                            <span class="badge bg-success">Σε χρήση</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Δεν χρησιμοποιείται</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-warning" onclick="editParty(<?= $party['id'] ?>, '<?= htmlspecialchars($party['name']) ?>')">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <?php if (!$party['is_used']): ?>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteParty(<?= $party['id'] ?>, '<?= htmlspecialchars($party['name']) ?>')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-danger" disabled title="Δεν μπορείτε να διαγράψετε ένα κόμμα που χρησιμοποιείται σε δηλώσεις">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Submission Periods Section -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="card-title mb-4">Περίοδοι Υποβολής</h2>

                <!-- Add Period Form -->
                <form method="POST" class="mb-4">
                    <div class="mb-3">
                        <label for="year" class="form-label">Έτος</label>
                        <input type="number" class="form-control" id="year" name="year" required>
                    </div>
                    <button type="submit" name="add_period" class="btn btn-primary" style="background-color: #ED9635; border-color: #ED9635;">
                        <i class="bi bi-plus-circle"></i> Προσθήκη Έτους
                    </button>
                </form>
                
                <!-- Periods Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Έτος</th>
                                <th>Κατάσταση</th>
                                <th>Ενέργειες</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($periods as $period): ?>
                                <tr>
                                    <td><?= $period['id'] ?></td>
                                    <td><?= $period['year'] ?></td>
                                    <td>
                                        <?php if ($period['is_used']): ?>
                                            <span class="badge bg-success">Σε χρήση</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Δεν χρησιμοποιείται</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-warning" onclick="editPeriod(<?= $period['id'] ?>, '<?= $period['year'] ?>')">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <?php if (!$period['is_used']): ?>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="deletePeriod(<?= $period['id'] ?>, '<?= $period['year'] ?>')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-danger" disabled title="Δεν μπορείτε να διαγράψετε μια περίοδο που χρησιμοποιείται σε δηλώσεις">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Edit Party Modal -->
    <div class="modal fade" id="editPartyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Επεξεργασία Κόμματος</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="party_id" id="edit_party_id">
                        <div class="mb-3">
                            <label for="party_name" class="form-label">Όνομα Κόμματος</label>
                            <input type="text" class="form-control" id="party_name" name="party_name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ακύρωση</button>
                        <button type="submit" name="edit_party" class="btn btn-warning">Αποθήκευση</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Party Modal -->
    <div class="modal fade" id="deletePartyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Διαγραφή Κόμματος</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Είστε σίγουροι ότι θέλετε να διαγράψετε το κόμμα <span id="delete_party_name"></span>;</p>
                </div>
                <div class="modal-footer">
                    <form method="POST">
                        <input type="hidden" name="party_id" id="delete_party_id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ακύρωση</button>
                        <button type="submit" name="delete_party" class="btn btn-danger">Διαγραφή</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Period Modal -->
    <div class="modal fade" id="editPeriodModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Επεξεργασία Έτους</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="period_id" id="edit_period_id">
                        <div class="mb-3">
                            <label for="edit_year" class="form-label">Έτος</label>
                            <input type="number" class="form-control" id="edit_year" name="year" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ακύρωση</button>
                        <button type="submit" name="edit_period" class="btn btn-warning">Αποθήκευση</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Period Modal -->
    <div class="modal fade" id="deletePeriodModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Διαγραφή Έτους</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Είστε σίγουροι ότι θέλετε να διαγράψετε το έτος <span id="delete_period_year"></span>;</p>
                </div>
                <div class="modal-footer">
                    <form method="POST">
                        <input type="hidden" name="period_id" id="delete_period_id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ακύρωση</button>
                        <button type="submit" name="delete_period" class="btn btn-danger">Διαγραφή</button>
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Party functions
        function editParty(id, name) {
            document.getElementById('edit_party_id').value = id;
            document.getElementById('party_name').value = name;
            new bootstrap.Modal(document.getElementById('editPartyModal')).show();
        }

        function deleteParty(id, name) {
            document.getElementById('delete_party_id').value = id;
            document.getElementById('delete_party_name').textContent = name;
            new bootstrap.Modal(document.getElementById('deletePartyModal')).show();
        }

        // Period functions
        function editPeriod(id, year) {
            document.getElementById('edit_period_id').value = id;
            document.getElementById('edit_year').value = year;
            new bootstrap.Modal(document.getElementById('editPeriodModal')).show();
        }

        function deletePeriod(id, year) {
            document.getElementById('delete_period_id').value = id;
            document.getElementById('delete_period_year').textContent = year;
            new bootstrap.Modal(document.getElementById('deletePeriodModal')).show();
        }
    </script>
</body>
</html>