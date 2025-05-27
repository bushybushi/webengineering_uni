<?php
//CODE CREATED BY JORGOS XIDIAS AND TEAM
//AI HAS BEEN USED TO ADD GOOD COMMENTS AND TO BEAUTIFY THE CODE

// --------------------------------------------------------
// DATABASE CONNECTION 
// Connect to the database using the configuration file
// --------------------------------------------------------
$pdo = require_once "../../config/db_connection.php";

// --------------------------------------------------------
// SESSION MANAGEMENT
// Start session and check if user is logged in
// --------------------------------------------------------
session_start();

// Redirect to login page if user is not logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    // Redirect to login page if not logged in
    header("location: ../login_module/login.php");
    exit;
}

// Use the ID of the logged-in user from the session
$user_id = $_SESSION["id"];

// --------------------------------------------------------
// INITIALIZE VARIABLES
// Set up variables for form data and messages
// --------------------------------------------------------
$first_name = $last_name = $email = $role = "";
$first_name_err = $last_name_err = $email_err = $password_err = $new_password_err = $confirm_password_err = $current_password_err = "";
$success_message = "";

// --------------------------------------------------------
// FETCH USER DATA
// Retrieve current user information from database
// --------------------------------------------------------
$sql = "SELECT first_name, last_name, email, role FROM users WHERE id = :id";
if($stmt = $pdo->prepare($sql)){
    $stmt->bindParam(":id", $user_id, PDO::PARAM_INT);
    if($stmt->execute()){
        if($stmt->rowCount() == 1){
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $first_name = $row["first_name"];
            $last_name = $row["last_name"];
            $email = $row["email"];
            $role = $row["role"];
        } else {
            die('Ο χρήστης δεν βρέθηκε.');
        }
    } else {
        die('Σφάλμα βάσης δεδομένων.');
    }
}

// --------------------------------------------------------
// FORM PROCESSING
// Handle form submission for profile deletion
// --------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_profile'])) {
    // Instead of deleting, mark the account as suspended (value 2)
    $sql = "UPDATE users SET is_suspended = 2 WHERE id = :id";
    if($stmt = $pdo->prepare($sql)){
        $stmt->bindParam(":id", $user_id, PDO::PARAM_INT);
        if($stmt->execute()){
            // Destroy session and redirect to home page
            session_start();
            $_SESSION = array();
            session_destroy();
            
            // Redirect to home page after successful "deletion"
            header("Location: ../../index.php");
            exit();
        } else {
            $success_message = "Σφάλμα κατά τη διαγραφή. Δοκιμάστε ξανά.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Προφίλ Χρήστη</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../../assets/images/iconlogo.png">
    <!-- Bootstrap CSS for styling and layout -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons for UI elements -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Flag Icons for language selection -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css"/>
    <!-- Custom CSS -->
    <link href="../../assets/css/style.css" rel="stylesheet">
    <style>
        /* Custom styles for profile page */
        body { background: #fff; }
        .profile-wrapper { max-width: 500px; margin: 40px auto 0 auto; background: #fff; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.07); padding: 32px 32px 24px 32px; }
        .profile-avatar { width: 96px; height: 96px; border-radius: 50%; background: #fffbe6; display: flex; align-items: center; justify-content: center; font-size: 3rem; color: #ED9635; margin: 0 auto 16px auto; border: 3px solid #ED9635; }
        .profile-title { font-weight: 700; font-size: 2.2rem; color: #222; text-align: center; }
        .profile-desc { text-align: center; color: #888; margin-bottom: 32px; }
        .btn-edit { background: linear-gradient(90deg, #ED9635 60%, #f0a85a 100%); color: #fff; border: none; border-radius: 8px; font-size: 1.1rem; font-weight: 500; padding: 12px 0; width: 100%; transition: box-shadow 0.2s; box-shadow: 0 2px 8px rgba(237,150,53,0.08); }
        .btn-edit:hover { background: linear-gradient(90deg, #f0a85a 60%, #ED9635 100%); color: #fff; box-shadow: 0 4px 16px rgba(237,150,53,0.15); }
        .btn-del { background: linear-gradient(90deg, #e74c3c 60%, #ff7675 100%); color: #fff; border: none; border-radius: 8px; font-size: 1.1rem; font-weight: 500; padding: 12px 0; width: 100%; transition: box-shadow 0.2s; box-shadow: 0 2px 8px rgba(231,76,60,0.08); margin-top: 10px; }
        .btn-del:hover { background: linear-gradient(90deg, #ff7675 60%, #e74c3c 100%); color: #fff; box-shadow: 0 4px 16px rgba(231,76,60,0.15); }
        .navbar { margin-bottom: 0; }
        .bottom-space { height: 60px; }
    </style>
</head>
<body>
    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
        <div class="container">
            <!-- Logo and brand name -->
            <a class="navbar-brand d-flex align-items-center" href="../../index.php">
                <img src="../../assets/images/logo.jpg" alt="ΠΟΘΕΝ ΕΣΧΕΣ Logo" height="40" class="me-3">
                <span class="fw-bold">ΠΟΘΕΝ ΕΣΧΕΣ</span>
            </a>
            <!-- Mobile Toggle Button -->
            <button class="navbar-toggler border-0 d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Navigation links -->
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="../../index.php">Αρχική</a></li>
                    <li class="nav-item"><a class="nav-link" href="../search_module/search.php">Αναζήτηση</a></li>
                    <li class="nav-item"><a class="nav-link" href="../search_module/statistics.php">Στατιστικά</a></li>
                    <?php if ($role === 'Politician' || $role === 'Admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="../submit_module/declaration-form.php">Υποβολή</a></li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <div class="dropdown">
                            <button class="profile-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                                    <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person"></i> Το προφίλ μου</a></li>
                                    <?php if(isset($_SESSION["role"]) && ($_SESSION["role"] === "Politician" || $_SESSION["role"] === "Admin")): ?>
                                    <li><a class="dropdown-item" href="../submit_module/my-declarations.php"><i class="bi bi-file-earmark-text"></i> Οι Δηλώσεις μου</a></li>
                                    <?php endif; ?>
                                    <li><a class="dropdown-item" href="../submit_module/favorites.php"><i class="bi bi-heart"></i> Αγαπημένα</a></li>
                                    <li><a class="dropdown-item" href="../api_module/api_documentation.php"><i class="bi bi-code"></i> API Documentation</a></li>
                                    <?php if(isset($_SESSION["role"]) && $_SESSION["role"] === "Admin"): ?>
                                    <li><a class="dropdown-item" href="../admin_module/dashboard.php"><i class="bi bi-speedometer2"></i> Admin Dashboard</a></li>
                                    <?php endif; ?>
                                    <li><a class="dropdown-item" href="../login_module/logout.php"><i class="bi bi-box-arrow-right"></i> Αποσύνδεση</a></li>
                                <?php else: ?>
                                    <li><a class="dropdown-item" href="../login_module/login.php"><i class="bi bi-box-arrow-in-right"></i> Είσοδος</a></li>
                                    <li><a class="dropdown-item" href="../login_module/register.php"><i class="bi bi-person-plus"></i> Εγγραφή</a></li>
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
                        <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Politician')): ?>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 mb-3" href="../submit_module/declaration-form.php">
                                <i class="bi bi-file-earmark-text"></i> Υποβολή
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item border-top pt-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-person-circle"></i>
                                <span class="fw-medium">Λογαριασμός</span>
                            </div>
                            <div class="d-flex flex-column gap-2">
                                <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                                <a href="profile.php" class="nav-link py-2">
                                    <i class="bi bi-person"></i> Το προφίλ μου
                                </a>
                                <?php if(isset($_SESSION["role"]) && ($_SESSION["role"] === "Politician" || $_SESSION["role"] === "Admin")): ?>
                                <a href="../submit_module/my-declarations.php" class="nav-link py-2">
                                    <i class="bi bi-file-earmark-text"></i> Οι Δηλώσεις μου
                                </a>
                                <?php endif; ?>
                                <a href="../submit_module/favorites.php" class="nav-link py-2">
                                    <i class="bi bi-heart"></i> Αγαπημένα
                                </a>
                                <?php if(isset($_SESSION["role"]) && $_SESSION["role"] === "Admin"): ?>
                                <a href="../admin_module/dashboard.php" class="nav-link py-2">
                                    <i class="bi bi-speedometer2"></i> Admin Dashboard
                                </a>
                                <?php endif; ?>
                                <a href="../api_module/api_documentation.php" class="nav-link py-2">
                                    <i class="bi bi-code"></i> API Documentation
                                </a>
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
    
    <!-- Main Content Container -->
    <div class="container" style="padding-top: 100px;">
        <!-- Profile Card -->
        <div class="profile-wrapper">
            <!-- Profile Avatar -->
            <div class="profile-avatar">
                <i class="bi bi-person"></i>
            </div>
            <!-- Profile Header -->
            <div class="profile-title">Προφίλ</div>
            <div class="profile-desc">Τα στοιχεία του λογαριασμού σας.</div>
            
            <!-- Success Message Display -->
            <?php if ($success_message): ?>
                <div class="alert alert-success text-center"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <!-- Profile Information Form -->
            <form method="post" autocomplete="off">
                <!-- First Name Field -->
                <div class="mb-3">
                    <label class="form-label">Όνομα</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($first_name); ?>" readonly>
                </div>
                <!-- Last Name Field -->
                <div class="mb-3">
                    <label class="form-label">Επώνυμο</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($last_name); ?>" readonly>
                </div>
                <!-- Email Field -->
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" readonly>
                </div>
                <!-- Role Field -->
                <div class="mb-3">
                    <label class="form-label">Ρόλος</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($role); ?>" readonly>
                </div>
                <!-- Action Buttons -->
                <div class="d-grid gap-2 mt-4">
                    <a href="edit_profile.php" class="btn btn-edit">Επεξεργασία</a>
                    <button type="button" class="btn btn-del" id="deleteBtn">Διαγραφή Λογαριασμού</button>
                </div>
                <div class="bottom-space"></div>
                
                <!-- Hidden Field for Delete Action -->
                <input type="hidden" name="delete_profile" id="deleteProfileInput" value="">
            </form>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Επιβεβαίωση Διαγραφής</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Είστε σίγουρος ότι θέλετε να διαγράψετε τον λογαριασμό σας;
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ακύρωση</button>
                    <button type="button" class="btn btn-del" id="confirmDeleteBtn">Διαγραφή</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- JavaScript for Delete Confirmation -->
    <script>
        // Delete confirmation
        const deleteBtn = document.getElementById('deleteBtn');
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        const deleteProfileInput = document.getElementById('deleteProfileInput');
        
        deleteBtn.addEventListener('click', () => deleteModal.show());
        confirmDeleteBtn.addEventListener('click', () => {
            deleteProfileInput.value = '1';
            document.querySelector('form').submit();
        });
    </script>

</body>
</html> 