<!-- filepath: c:\xampp\htdocs\webengineering_uni\webengineering_uni\modules\admin_module\system_config.php -->
<?php
include '../../config/db_connection.php';
session_start();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_party'])) {
        $party = $_POST['party'];
        $query = "INSERT INTO political_parties (name) VALUES ('$party')";
        mysqli_query($conn, $query);
    } elseif (isset($_POST['add_position'])) {
        $position = $_POST['position'];
        $query = "INSERT INTO positions (name) VALUES ('$position')";
        mysqli_query($conn, $query);
    } elseif (isset($_POST['add_person'])) {
        $name = $_POST['name'];
        $party_id = $_POST['party_id'];
        $position_id = $_POST['position_id'];
        $query = "INSERT INTO people (name, party_id, position_id) VALUES ('$name', $party_id, $position_id)";
        mysqli_query($conn, $query);
    }
}

// Fetch existing data
$parties = mysqli_query($conn, "SELECT * FROM political_parties");
$positions = mysqli_query($conn, "SELECT * FROM positions");
$people = mysqli_query($conn, "SELECT p.*, pp.name as party_name, pos.name as position_name 
                             FROM people p 
                             JOIN political_parties pp ON p.party_id = pp.id 
                             JOIN positions pos ON p.position_id = pos.id");
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
        <h1 class="text-center mb-4">Ρυθμίσεις Συστήματος</h1>
        
        <!-- Political Parties Section -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="card-title mb-4">Πολιτικά Κόμματα</h2>
                <form method="POST" class="mb-4">
                    <div class="mb-3">
                        <label for="party" class="form-label">Όνομα Κόμματος</label>
                        <input type="text" class="form-control" id="party" name="party" required>
                    </div>
                    <button type="submit" name="add_party" class="btn btn-primary" style="background-color: #ED9635; border-color: #ED9635;">
                        <i class="bi bi-plus-circle"></i> Προσθήκη Κόμματος
                    </button>
                </form>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Όνομα</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($party = mysqli_fetch_assoc($parties)) { ?>
                                <tr>
                                    <td><?= $party['id'] ?></td>
                                    <td><?= $party['name'] ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Positions Section -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="card-title mb-4">Θέσεις</h2>
                <form method="POST" class="mb-4">
                    <div class="mb-3">
                        <label for="position" class="form-label">Όνομα Θέσης</label>
                        <input type="text" class="form-control" id="position" name="position" required>
                    </div>
                    <button type="submit" name="add_position" class="btn btn-primary" style="background-color: #ED9635; border-color: #ED9635;">
                        <i class="bi bi-plus-circle"></i> Προσθήκη Θέσης
                    </button>
                </form>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Όνομα</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($position = mysqli_fetch_assoc($positions)) { ?>
                                <tr>
                                    <td><?= $position['id'] ?></td>
                                    <td><?= $position['name'] ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- People Section -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="card-title mb-4">Άτομα</h2>
                <form method="POST" class="mb-4">
                    <div class="mb-3">
                        <label for="name" class="form-label">Όνομα</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="party_id" class="form-label">Κόμμα</label>
                        <select class="form-select" id="party_id" name="party_id" required>
                            <?php mysqli_data_seek($parties, 0); while ($party = mysqli_fetch_assoc($parties)) { ?>
                                <option value="<?= $party['id'] ?>"><?= $party['name'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="position_id" class="form-label">Θέση</label>
                        <select class="form-select" id="position_id" name="position_id" required>
                            <?php mysqli_data_seek($positions, 0); while ($position = mysqli_fetch_assoc($positions)) { ?>
                                <option value="<?= $position['id'] ?>"><?= $position['name'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <button type="submit" name="add_person" class="btn btn-primary" style="background-color: #ED9635; border-color: #ED9635;">
                        <i class="bi bi-plus-circle"></i> Προσθήκη Ατόμου
                    </button>
                </form>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Όνομα</th>
                                <th>Κόμμα</th>
                                <th>Θέση</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($person = mysqli_fetch_assoc($people)) { ?>
                                <tr>
                                    <td><?= $person['id'] ?></td>
                                    <td><?= $person['name'] ?></td>
                                    <td><?= $person['party_name'] ?></td>
                                    <td><?= $person['position_name'] ?></td>
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