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
// Set up variables for user data and error messages
// --------------------------------------------------------
$first_name = $last_name = $email = $role = "";
$first_name_err = $last_name_err = $email_err = $success_message = $password_err = "";

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
// Handle form submission for profile updates or deletion
// --------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // --------------------------------------------------------
    // PROFILE UPDATE LOGIC
    // Handle validation and updating of profile information
    // --------------------------------------------------------
    if (isset($_POST['save_profile'])) {
        // Set values from POST data
        $first_name = trim($_POST["first_name"]);
        $last_name = trim($_POST["last_name"]);
        $email = trim($_POST["email"]);

        // If no validation errors, update the database
        if(empty($first_name_err) && empty($last_name_err) && empty($email_err) && empty($password_err)){
            // Build SQL query based on whether password is being updated
            $sql = "UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email";
            if (!empty($_POST["new_password"])) {
                $sql .= ", password = :password";
            }
            $sql .= " WHERE id = :id";
            
            if($stmt = $pdo->prepare($sql)){
                // Bind parameters for the update
                $stmt->bindParam(":first_name", $first_name, PDO::PARAM_STR);
                $stmt->bindParam(":last_name", $last_name, PDO::PARAM_STR);
                $stmt->bindParam(":email", $email, PDO::PARAM_STR);
                if (!empty($_POST["new_password"])) {
                    // Hash the new password before storing for security
                    $hashed_password = password_hash($_POST["new_password"], PASSWORD_DEFAULT);
                    $stmt->bindParam(":password", $hashed_password, PDO::PARAM_STR);
                }
                $stmt->bindParam(":id", $user_id, PDO::PARAM_INT);
                
                // Execute the update
                if($stmt->execute()){
                    // Redirect to profile page on success with success parameter
                    header("Location: profile.php?success=1");
                    exit();
                } else{
                    $success_message = "Σφάλμα κατά την ενημέρωση. Δοκιμάστε ξανά.";
                }
            }
        }
    } 
    // --------------------------------------------------------
    // ACCOUNT DELETION LOGIC
    // Mark the account as suspended instead of actually deleting
    // --------------------------------------------------------
    elseif (isset($_POST['delete_profile'])) {
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
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Επεξεργασία Προφίλ</title>
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
        /* Custom styles for profile editing page */
        body { background: #fff; }
        .profile-wrapper { max-width: 500px; margin: 40px auto 0 auto; background: #fff; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.07); padding: 32px 32px 24px 32px; }
        .profile-avatar { width: 96px; height: 96px; border-radius: 50%; background: #fffbe6; display: flex; align-items: center; justify-content: center; font-size: 3rem; color: #ED9635; margin: 0 auto 16px auto; border: 3px solid #ED9635; }
        .profile-title { font-weight: 700; font-size: 2.2rem; color: #222; text-align: center; }
        .profile-desc { text-align: center; color: #888; margin-bottom: 32px; }
        .btn-save { background: linear-gradient(90deg, #ED9635 60%, #f0a85a 100%); color: #fff; border: none; border-radius: 8px; font-size: 1.1rem; font-weight: 500; padding: 12px 0; width: 100%; transition: box-shadow 0.2s; box-shadow: 0 2px 8px rgba(237,150,53,0.08); }
        .btn-save:hover { background: linear-gradient(90deg, #f0a85a 60%, #ED9635 100%); color: #fff; box-shadow: 0 4px 16px rgba(237,150,53,0.15); }
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
        <!-- Profile Editing Form -->
        <div class="profile-wrapper">
            <!-- Profile Avatar -->
            <div class="profile-avatar">
                <i class="bi bi-person"></i>
            </div>
            <!-- Profile Header -->
            <div class="profile-title">Επεξεργασία Προφίλ</div>
            <div class="profile-desc">Επεξεργαστείτε τα στοιχεία του λογαριασμού σας.</div>
            
            
            <!-- Profile Edit Form -->
            <form id="editProfileForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . '?id=' . $user_id); ?>" method="post" autocomplete="off">
                <!-- First Name Field -->
                <div class="mb-3">
                    <label class="form-label">Όνομα</label>
                    <input type="text" name="first_name" id="first_name" class="form-control <?php echo (!empty($first_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($first_name); ?>">
                    <div class="invalid-feedback" id="first_name_error"></div>
                </div>
                
                <!-- Last Name Field -->
                <div class="mb-3">
                    <label class="form-label">Επώνυμο</label>
                    <input type="text" name="last_name" id="last_name" class="form-control <?php echo (!empty($last_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($last_name); ?>">
                    <div class="invalid-feedback" id="last_name_error"></div>
                </div>
                
                <!-- Email Field -->
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($email); ?>">
                    <div class="invalid-feedback" id="email_error"></div>
                </div>
                
                <!-- New Password Field -->
                <div class="mb-3">
                    <label class="form-label">Νέος Κωδικός</label>
                    <input type="password" name="new_password" id="new_password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                    <div class="invalid-feedback" id="password_error"></div>
                </div>
                
                <!-- Confirm Password Field -->
                <div class="mb-3">
                    <label class="form-label">Επιβεβαίωση Κωδικού</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                    <div class="invalid-feedback" id="confirm_password_error"></div>
                </div>
                
                <!-- Role Field (Read-only) -->
                <div class="mb-3">
                    <label class="form-label">Ρόλος</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($role); ?>" disabled>
                </div>
                
                <!-- Action Buttons -->
                <div class="d-grid gap-2 mt-4">
                    <button type="submit" name="save_profile" class="btn btn-save">Αποθήκευση</button>
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
        // Get all form elements
        const form = document.getElementById('editProfileForm');
        const firstNameInput = document.getElementById('first_name');
        const lastNameInput = document.getElementById('last_name');
        const emailInput = document.getElementById('email');
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        
        // Get error message elements
        const firstNameError = document.getElementById('first_name_error');
        const lastNameError = document.getElementById('last_name_error');
        const emailError = document.getElementById('email_error');
        const passwordError = document.getElementById('password_error');
        const confirmPasswordError = document.getElementById('confirm_password_error');

        // Validation functions
        function validateField(input, errorElement, errorMessage, validationFn) {
            input.classList.remove('is-invalid');
            errorElement.textContent = '';
            
            if (!validationFn(input.value)) {
                input.classList.add('is-invalid');
                errorElement.textContent = errorMessage;
                return false;
            }
            return true;
        }

        function validateFirstName() {
            return validateField(
                firstNameInput,
                firstNameError,
                'Συμπληρώστε το όνομα.',
                value => value.trim() !== ''
            );
        }

        function validateLastName() {
            return validateField(
                lastNameInput,
                lastNameError,
                'Συμπληρώστε το επώνυμο.',
                value => value.trim() !== ''
            );
        }

        function validateEmail() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return validateField(
                emailInput,
                emailError,
                !emailInput.value.trim() ? 'Συμπληρώστε το email.' : 'Μη έγκυρη μορφή email.',
                value => value.trim() !== '' && emailRegex.test(value.trim())
            );
        }

        function validatePassword() {
            let isValid = true;
            
            // Reset previous error states
            newPasswordInput.classList.remove('is-invalid');
            confirmPasswordInput.classList.remove('is-invalid');
            passwordError.textContent = '';
            confirmPasswordError.textContent = '';

            if (newPasswordInput.value) {
                if (newPasswordInput.value.length < 6) {
                    newPasswordInput.classList.add('is-invalid');
                    passwordError.textContent = 'Ο κωδικός πρέπει να έχει τουλάχιστον 6 χαρακτήρες.';
                    isValid = false;
                }
                if (newPasswordInput.value !== confirmPasswordInput.value) {
                    confirmPasswordInput.classList.add('is-invalid');
                    confirmPasswordError.textContent = 'Οι κωδικοί δεν ταιριάζουν.';
                    isValid = false;
                }
            }
            
            return isValid;
        }

        function validateForm() {
            return validateFirstName() && 
                   validateLastName() && 
                   validateEmail() && 
                   validatePassword();
        }

        // Add real-time validation for all fields
        [firstNameInput, lastNameInput, emailInput, newPasswordInput, confirmPasswordInput].forEach(input => {
            input.addEventListener('input', () => {
                switch(input) {
                    case firstNameInput:
                        validateFirstName();
                        break;
                    case lastNameInput:
                        validateLastName();
                        break;
                    case emailInput:
                        validateEmail();
                        break;
                    case newPasswordInput:
                    case confirmPasswordInput:
                        validatePassword();
                        break;
                }
            });
        });

        // Form submission validation
        form.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
            }
        });

        // Delete confirmation
        const deleteBtn = document.getElementById('deleteBtn');
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        const deleteProfileInput = document.getElementById('deleteProfileInput');
        
        deleteBtn.addEventListener('click', () => deleteModal.show());
        confirmDeleteBtn.addEventListener('click', () => {
            deleteProfileInput.value = '1';
            form.submit();
        });
    </script>
</body>
</html> 