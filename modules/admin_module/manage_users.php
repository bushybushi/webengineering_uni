<!-- filepath: c:\xampp\htdocs\webengineering_uni\webengineering_uni\modules\admin_module\manage_users.php -->
<?php
$conn = include '../../config/db_connection.php';
session_start();

// Fetch all users
$stmt = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
    }
    
    if (isset($_POST['suspend_user'])) {
        $user_id = $_POST['user_id'];
        $stmt = $conn->prepare("UPDATE users SET is_suspended = 1 WHERE id = ?");
        $stmt->execute([$user_id]);
    }
    
    if (isset($_POST['unsuspend_user'])) {
        $user_id = $_POST['user_id'];
        $stmt = $conn->prepare("UPDATE users SET is_suspended = 0 WHERE id = ?");
        $stmt->execute([$user_id]);
    }
    
    if (isset($_POST['approve_politician'])) {
        $user_id = $_POST['user_id'];
        $stmt = $conn->prepare("UPDATE users SET verification_status = 'approved' WHERE id = ?");
        $stmt->execute([$user_id]);
    }
    
    if (isset($_POST['reject_politician'])) {
        $user_id = $_POST['user_id'];
        // First delete the ID photos if they exist
        $stmt = $conn->prepare("SELECT front_photo_path, back_photo_path FROM politician_id_photos WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $photos = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($photos) {
            // Delete the actual files
            if ($photos['front_photo_path'] && file_exists("../../" . $photos['front_photo_path'])) {
                unlink("../../" . $photos['front_photo_path']);
            }
            if ($photos['back_photo_path'] && file_exists("../../" . $photos['back_photo_path'])) {
                unlink("../../" . $photos['back_photo_path']);
            }
            
            // Delete the database records
            $stmt = $conn->prepare("DELETE FROM politician_id_photos WHERE user_id = ?");
            $stmt->execute([$user_id]);
        }
        
        // Finally delete the user
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
    }
    
    if (isset($_POST['edit_user'])) {
        $user_id = $_POST['user_id'];
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $role = trim($_POST['role']);
        $password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);
        
        if (!empty($password)) {
            if ($password === $confirm_password) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, role = ?, password = ? WHERE id = ?");
                $stmt->execute([$first_name, $last_name, $email, $role, $hashed_password, $user_id]);
            }
        } else {
            $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, role = ? WHERE id = ?");
            $stmt->execute([$first_name, $last_name, $email, $role, $user_id]);
        }
    }
    
    if (isset($_POST['add_user'])) {
        // Validate input
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);
        $role = trim($_POST['role']);
        
        if ($password === $confirm_password) {
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
}

// Get sort parameters
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'id';
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'asc';

// Get search parameter
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Modify query based on search and sort
$query = "SELECT * FROM users WHERE (role = 'Politician' AND verification_status = 'approved') OR role != 'Politician'";
if (!empty($search)) {
    $query .= " AND (first_name LIKE :search OR last_name LIKE :search OR email LIKE :search OR role LIKE :search)";
}
$query .= " ORDER BY " . $sort_by . " " . $sort_order;

$stmt = $conn->prepare($query);
if (!empty($search)) {
    $search_param = "%$search%";
    $stmt->bindParam(':search', $search_param);
}
$stmt->execute();
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
        
        <h1 class="text-center mb-4">Διαχείριση Χρηστών</h1>
        
        <!-- Search and Sort Controls -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <form method="GET" class="d-flex gap-2">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" name="search" class="form-control" placeholder="Search users..." value="<?= htmlspecialchars($search) ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">Search</button>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex gap-2 justify-content-md-end">
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    Ταξινόμηση κατά: <?= ucfirst($sort_by) ?> (<?= $sort_order ?>)
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="?sort=id&order=asc&search=<?= urlencode($search) ?>">ID (Αύξουσα)</a></li>
                                    <li><a class="dropdown-item" href="?sort=id&order=desc&search=<?= urlencode($search) ?>">ID (Φθίνουσα)</a></li>
                                    <li><a class="dropdown-item" href="?sort=role&order=asc&search=<?= urlencode($search) ?>">Ρόλος (Αύξουσα)</a></li>
                                    <li><a class="dropdown-item" href="?sort=role&order=desc&search=<?= urlencode($search) ?>">Ρόλος (Φθίνουσα)</a></li>
                                    <li><a class="dropdown-item" href="?sort=first_name&order=asc&search=<?= urlencode($search) ?>">Όνομα (Αύξουσα)</a></li>
                                    <li><a class="dropdown-item" href="?sort=first_name&order=desc&search=<?= urlencode($search) ?>">Όνομα (Φθίνουσα)</a></li>
                                    <li><a class="dropdown-item" href="?sort=is_suspended&order=asc&search=<?= urlencode($search) ?>">Κατάσταση (Αύξουσα)</a></li>
                                    <li><a class="dropdown-item" href="?sort=is_suspended&order=desc&search=<?= urlencode($search) ?>">Κατάσταση (Φθίνουσα)</a></li>
                                </ul>
                            </div>
                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                <i class="bi bi-person-plus"></i> Προσθήκη Χρήστη
                            </button>
                            <?php
                            // Get count of pending politicians
                            $pending_stmt = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'Politician' AND verification_status = 'pending'");
                            $pending_count = $pending_stmt->fetchColumn();
                            ?>
                            <div class="dropdown d-inline-block">
                                <button class="btn btn-primary position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-bell"></i> Ειδοποιήσεις
                                    <?php if ($pending_count > 0): ?>
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                            <?= $pending_count ?>
                                        </span>
                                    <?php endif; ?>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" style="width: 300px;">
                                    <?php
                                    $pending_politicians = $conn->query("SELECT u.*, pip.front_photo_path, pip.back_photo_path 
                                                                        FROM users u 
                                                                        LEFT JOIN politician_id_photos pip ON u.id = pip.user_id 
                                                                        WHERE u.role = 'Politician' AND u.verification_status = 'pending'
                                                                        ORDER BY u.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    if (count($pending_politicians) > 0):
                                        foreach ($pending_politicians as $politician):
                                    ?>
                                        <li>
                                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#verifyPoliticianModal<?= $politician['id'] ?>">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <i class="bi bi-person-circle fs-4"></i>
                                                    </div>
                                                    <div class="flex-grow-1 ms-2">
                                                        <div class="fw-medium"><?= htmlspecialchars($politician['first_name'] . ' ' . $politician['last_name']) ?></div>
                                                        <small class="text-muted"><?= htmlspecialchars($politician['email']) ?></small>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                    <?php 
                                        endforeach;
                                    else:
                                    ?>
                                        <li><span class="dropdown-item text-muted">Δεν υπάρχουν εκκρεμείς αιτήσεις</span></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
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
                                <th>Status</th>
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
                                        <?php if (isset($row['is_suspended']) && $row['is_suspended']) { ?>
                                            <span class="badge bg-danger">Suspended</span>
                                        <?php } else { ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editUserModal<?= $row['id'] ?>">
                                                <i class="bi bi-pencil"></i> Επεξεργασία
                                            </button>
                                            <?php if (isset($row['is_suspended']) && $row['is_suspended']) { ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                                    <button type="submit" name="unsuspend_user" class="btn btn-success btn-sm">
                                                        <i class="bi bi-unlock"></i> Επαναφορά
                                                    </button>
                                                </form>
                                            <?php } else { ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                                    <button type="submit" name="suspend_user" class="btn btn-warning btn-sm">
                                                        <i class="bi bi-lock"></i> Αναστολή
                                                    </button>
                                                </form>
                                            <?php } ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteUserModal<?= $row['id'] ?>">
                                                    <i class="bi bi-trash"></i> Διαγραφή
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Edit User Modal -->
                                <div class="modal fade" id="editUserModal<?= $row['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Επεξεργασία Χρήστη</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST">
                                                <div class="modal-body">
                                                    <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label">Όνομα</label>
                                                        <input type="text" name="first_name" class="form-control" value="<?= $row['first_name'] ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Επώνυμο</label>
                                                        <input type="text" name="last_name" class="form-control" value="<?= $row['last_name'] ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Email</label>
                                                        <input type="email" name="email" class="form-control" value="<?= $row['email'] ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Κωδικός</label>
                                                        <input type="password" name="password" class="form-control" placeholder="Αφήστε κενό για να διατηρηθεί ο τρέχων κωδικός">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Επιβεβαίωση Κωδικού</label>
                                                        <input type="password" name="confirm_password" class="form-control" placeholder="Αφήστε κενό για να διατηρηθεί ο τρέχων κωδικός">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Ρόλος</label>
                                                        <select name="role" class="form-select" required>
                                                            <option value="Public" <?= $row['role'] == 'Public' ? 'selected' : '' ?>>Public</option>
                                                            <option value="Politician" <?= $row['role'] == 'Politician' ? 'selected' : '' ?>>Politician</option>
                                                            <option value="Admin" <?= $row['role'] == 'Admin' ? 'selected' : '' ?>>Admin</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Κλείσιμο</button>
                                                    <button type="submit" name="edit_user" class="btn btn-primary">Αποθήκευση</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Delete User Modal -->
                                <div class="modal fade" id="deleteUserModal<?= $row['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Διαγραφή Χρήστη</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Είστε σίγουροι ότι θέλετε να διαγράψετε τον χρήστη <strong><?= $row['first_name'] . ' ' . $row['last_name'] ?></strong>;</p>
                                                <p class="text-danger mb-0">Αυτή η ενέργεια δεν μπορεί να αναιρεθεί.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <form method="POST">
                                                    <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ακύρωση</button>
                                                    <button type="submit" name="delete_user" class="btn btn-danger">Διαγραφή</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Προσθήκη Νέου Χρήστη</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" class="needs-validation" novalidate>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Όνομα</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Επώνυμο</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Κωδικός</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Επιβεβαίωση Κωδικού</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ρόλος</label>
                            <select name="role" class="form-select" required>
                                <option value="">Επιλέξτε ρόλο</option>
                                <option value="Public">Public</option>
                                <option value="Politician">Politician</option>
                                <option value="Admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Κλείσιμο</button>
                        <button type="submit" name="add_user" class="btn btn-warning">Προσθήκη</button>
                    </div>
                </form>
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

    <?php foreach ($pending_politicians as $politician): ?>
    <!-- Verify Politician Modal -->
    <div class="modal fade" id="verifyPoliticianModal<?= $politician['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Επιβεβαίωση Πολιτικού</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Πληροφορίες Χρήστη</h6>
                            <p><strong>Όνομα:</strong> <?= htmlspecialchars($politician['first_name'] . ' ' . $politician['last_name']) ?></p>
                            <p><strong>Email:</strong> <?= htmlspecialchars($politician['email']) ?></p>
                            <p><strong>Ημερομηνία Εγγραφής:</strong> <?= date('d/m/Y', strtotime($politician['created_at'])) ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Φωτογραφίες Ταυτότητας</h6>
                            <?php if ($politician['front_photo_path'] && $politician['back_photo_path']): ?>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <p class="mb-1">Μπροστινή πλευρά:</p>
                                        <img src="../../<?= htmlspecialchars($politician['front_photo_path']) ?>" 
                                             class="img-fluid rounded" 
                                             alt="Front ID"
                                             style="max-height: 200px; width: auto; object-fit: contain;">
                                    </div>
                                    <div class="col-6">
                                        <p class="mb-1">Πίσω πλευρά:</p>
                                        <img src="../../<?= htmlspecialchars($politician['back_photo_path']) ?>" 
                                             class="img-fluid rounded" 
                                             alt="Back ID"
                                             style="max-height: 200px; width: auto; object-fit: contain;">
                                    </div>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">Δεν έχουν ανεβεί φωτογραφίες ταυτότητας</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="user_id" value="<?= $politician['id'] ?>">
                        <button type="submit" name="reject_politician" class="btn btn-danger" onclick="return confirm('Είστε σίγουροι ότι θέλετε να απορρίψετε και να διαγράψετε αυτόν τον χρήστη;')">
                            <i class="bi bi-x-circle"></i> Απόρριψη
                        </button>
                        <button type="submit" name="approve_politician" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Έγκριση
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php include '../../includes/about-us-modal.php'; ?>
    <?php include '../../includes/manual-modal.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>