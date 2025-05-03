<!-- filepath: c:\xampp\htdocs\webengineering_uni\webengineering_uni\modules\admin_module\manage_submissions.php -->
<?php
$conn = include '../../config/db_connection.php';
session_start();

// Handle declaration actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $declaration_id = $_POST['declaration_id'];
        $status = $_POST['status'];
        $stmt = $conn->prepare("UPDATE declarations SET status = ? WHERE id = ?");
        $stmt->execute([$status, $declaration_id]);
    }
}

// Fetch all declarations with related data
$declarations = $conn->query("
    SELECT d.*, 
           pd.full_name as person_name, 
           p.name as party_name,
           sp.year as submission_year
    FROM declarations d 
    LEFT JOIN personal_data pd ON d.id = pd.declaration_id
    LEFT JOIN parties p ON pd.party_id = p.id
    LEFT JOIN submission_periods sp ON d.submission_period_id = sp.id
    ORDER BY d.submission_date DESC
");
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
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mt-5 pt-5">
        <h1 class="text-center mb-4">Διαχείριση Δηλώσεων</h1>
        
        <!-- Declarations Table -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Όνομα</th>
                                <th>Κόμμα</th>
                                <th>Έτος</th>
                                <th>Ημερομηνία Υποβολής</th>
                                <th>Ενέργειες</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $declarations->fetch(PDO::FETCH_ASSOC)) { ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= $row['person_name'] ?></td>
                                    <td><?= $row['party_name'] ?></td>
                                    <td><?= $row['submission_year'] ?></td>
                                    <td><?= $row['submission_date'] ?></td>
                                    <td>
                                        <a href="view_declaration.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm">
                                            <i class="bi bi-eye"></i> Προβολή
                                        </a>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="declaration_id" value="<?= $row['id'] ?>">
                                            <input type="hidden" name="status" value="Approved">
                                            <button type="submit" name="update_status" class="btn btn-success btn-sm">
                                                <i class="bi bi-check-circle"></i> Έγκριση
                                            </button>
                                        </form>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="declaration_id" value="<?= $row['id'] ?>">
                                            <input type="hidden" name="status" value="Rejected">
                                            <button type="submit" name="update_status" class="btn btn-danger btn-sm">
                                                <i class="bi bi-x-circle"></i> Απόρριψη
                                            </button>
                                        </form>
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