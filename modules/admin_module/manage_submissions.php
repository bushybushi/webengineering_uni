<!-- filepath: c:\xampp\htdocs\webengineering_uni\webengineering_uni\modules\admin_module\manage_submissions.php -->
<?php
$conn = include '../../config/db_connection.php';
session_start();

// Handle submission actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $submission_id = $_POST['submission_id'];
        $status = $_POST['status'];
        $stmt = $conn->prepare("UPDATE submissions SET status = ? WHERE id = ?");
        $stmt->execute([$status, $submission_id]);
    }
}

// Fetch all submissions with related data
$submissions = $conn->query("
    SELECT s.*, p.name as person_name, pp.name as party_name, pos.name as position_name 
    FROM submissions s 
    JOIN people p ON s.person_id = p.id 
    JOIN political_parties pp ON p.party_id = pp.id 
    JOIN positions pos ON p.position_id = pos.id 
    ORDER BY s.date_of_submission DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Διαχείριση Υποβολών - ΠΟΘΕΝ ΕΣΧΕΣ</title>
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
        <h1 class="text-center mb-4">Διαχείριση Υποβολών</h1>
        
        <!-- Submissions Table -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Όνομα</th>
                                <th>Ημερομηνία Υποβολής</th>
                                <th>Κατάσταση</th>
                                <th>Ενέργειες</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($submissions)) { ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= $row['person_name'] ?></td>
                                    <td><?= $row['date_of_submission'] ?></td>
                                    <td>
                                        <span class="badge <?= $row['status'] === 'Approved' ? 'bg-success' : ($row['status'] === 'Rejected' ? 'bg-danger' : 'bg-warning') ?>">
                                            <?= $row['status'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline me-2">
                                            <input type="hidden" name="submission_id" value="<?= $row['id'] ?>">
                                            <input type="hidden" name="status" value="Approved">
                                            <button type="submit" name="update_status" class="btn btn-success btn-sm">
                                                <i class="bi bi-check-circle"></i> Έγκριση
                                            </button>
                                        </form>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="submission_id" value="<?= $row['id'] ?>">
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