<!-- filepath: c:\xampp\htdocs\webengineering_uni\webengineering_uni\modules\admin_module\manage_users.php -->
<?php
$conn = include '../../config/db_connection.php';
session_start();

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
    }
    
    if (isset($_POST['add_user'])) {
        // Validate input
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $role = trim($_POST['role']);
        
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() == 0) {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$first_name, $last_name, $email, $hashed_password, $role]);
        }
    }
}

// Fetch all users
$stmt = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Διαχείριση Χρηστών - ΠΟΘΕΝ ΕΣΧΕΣ</title>
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
        <h1 class="text-center mb-4">Διαχείριση Χρηστών</h1>
        
        <!-- Users Table -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Όνομα</th>
                                <th>Email</th>
                                <th>Ρόλος</th>
                                <th>Ενέργειες</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $row) { ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= $row['first_name'] . ' ' . $row['last_name'] ?></td>
                                    <td><?= $row['email'] ?></td>
                                    <td><?= $row['role'] ?></td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                            <button type="submit" name="delete_user" class="btn btn-danger btn-sm">
                                                <i class="bi bi-trash"></i> Διαγραφή
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

        <!-- Add User Form -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="card-title mb-4">Προσθήκη Νέου Χρήστη</h2>
                <form method="POST" class="needs-validation" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Position/Role</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-briefcase"></i></span>
                                <select name="role" class="form-select" required>
                                    <option value="">Select position</option>
                                    <option value="Public">Public</option>
                                    <option value="Politician">Politician</option>
                                    <option value="Admin">Admin</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <button type="submit" name="add_user" class="btn btn-warning text-dark w-100 mt-4 mb-3">
                        <i class="bi bi-person-plus"></i> Add User
                    </button>
                </form>
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