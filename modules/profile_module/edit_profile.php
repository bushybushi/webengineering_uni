<?php
//CODE CREATED BY JORGOS XIDIAS AND TEAM
//AI HAS BEEN USED TO ADD GOOD COMMENTS AND TO BEAUTIFY THE CODE
// Connect to the database
$pdo = require_once "../../config/db_connection.php";

// Start session to access logged-in user information
session_start();

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    // Redirect to login page if not logged in
    header("location: ../login_module/login.php");
    exit;
}

// Use the ID of the logged-in user from the session
$user_id = $_SESSION["id"];

// Initialize variables for form data and error messages
$first_name = $last_name = $email = $role = "";
$first_name_err = $last_name_err = $email_err = $success_message = $password_err = "";

// Fetch user's current information from database
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

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle profile update
    if (isset($_POST['save_profile'])) {
        // Validate first name
        if(empty(trim($_POST["first_name"]))) {
            $first_name_err = "Συμπληρώστε το όνομα.";
        } else {
            $first_name = trim($_POST["first_name"]);
        }

        // Validate last name
        if(empty(trim($_POST["last_name"]))) {
            $last_name_err = "Συμπληρώστε το επώνυμο.";
        } else {
            $last_name = trim($_POST["last_name"]);
        }

        // Validate email
        if(empty(trim($_POST["email"]))) {
            $email_err = "Συμπληρώστε το email.";
        } else {
            // Check if email is in valid format
            if (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
                $email_err = "Μη έγκυρη μορφή email.";
            } else {
                // Check if email is already in use by another active or admin-suspended user (not deleted)
                $sql = "SELECT id, is_suspended FROM users WHERE email = :email AND id != :id";
                if($stmt = $pdo->prepare($sql)){
                    $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);
                    $stmt->bindParam(":id", $user_id, PDO::PARAM_INT);
                    $param_email = trim($_POST["email"]);
                    if($stmt->execute()){
                        $emailInUse = false;
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            // Only consider the email as "in use" if the account isn't deleted (is_suspended != 2)
                            if ($row['is_suspended'] != 2) {
                                $emailInUse = true;
                                break;
                            }
                        }
                        
                        if($emailInUse){
                            $email_err = "Αυτό το email χρησιμοποιείται ήδη.";
                        } else{
                            $email = trim($_POST["email"]);
                        }
                    }
                }
            }
        }

        // Validate new password if provided
        if (!empty(trim($_POST["new_password"]))) {
            // Check password length
            if (strlen(trim($_POST["new_password"])) < 6) {
                $password_err = "Ο κωδικός πρέπει να έχει τουλάχιστον 6 χαρακτήρες.";
            } 
            // Check if passwords match
            elseif (trim($_POST["new_password"]) !== trim($_POST["confirm_password"])) {
                $password_err = "Οι κωδικοί δεν ταιριάζουν.";
            } else {
                $new_password = trim($_POST["new_password"]);
            }
        }

        // If no errors, update the database
        if(empty($first_name_err) && empty($last_name_err) && empty($email_err) && empty($password_err)){
            // Build SQL query based on whether password is being updated
            $sql = "UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email";
            if (!empty($new_password)) {
                $sql .= ", password = :password";
            }
            $sql .= " WHERE id = :id";
            
            if($stmt = $pdo->prepare($sql)){
                // Bind parameters for the update
                $stmt->bindParam(":first_name", $first_name, PDO::PARAM_STR);
                $stmt->bindParam(":last_name", $last_name, PDO::PARAM_STR);
                $stmt->bindParam(":email", $email, PDO::PARAM_STR);
                if (!empty($new_password)) {
                    // Hash the new password before storing
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt->bindParam(":password", $hashed_password, PDO::PARAM_STR);
                }
                $stmt->bindParam(":id", $user_id, PDO::PARAM_INT);
                
                // Execute the update
                if($stmt->execute()){
                    // Redirect to profile page on success
                    header("Location: profile.php");
                    exit();
                } else{
                    $success_message = "Σφάλμα κατά την ενημέρωση. Δοκιμάστε ξανά.";
                }
            }
        }
    } 
    // Handle account deletion
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
                echo "<script>window.location.href='../../index.php';</script>";
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css"/>
    <link href="../../assets/css/style.css" rel="stylesheet">
    <style>
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
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="../../index.php">
                <img src="../../assets/images/logo.jpg" alt="ΠΟΘΕΝ ΕΣΧΕΣ Logo" height="40" class="me-3">
                <span class="fw-bold">ΠΟΘΕΝ ΕΣΧΕΣ</span>
            </a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="../../index.php">Αρχική</a></li>
                    <li class="nav-item"><a class="nav-link" href="../search_module/search.php">Αναζήτηση</a></li>
                    <li class="nav-item"><a class="nav-link" href="../search_module/statistics.php">Στατιστικά</a></li>
                    <li class="nav-item"><a class="nav-link" href="../submit_module/declaration-form.php">Υποβολή</a></li>
                    <li class="nav-item">
                        <div class="dropdown">
                            <button class="profile-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                                    <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person"></i> Προφίλ</a></li>
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
        </div>
    </nav>
    <div class="container" style="padding-top: 100px;">
        <div class="profile-wrapper">
            <div class="profile-avatar">
                <i class="bi bi-person"></i>
            </div>
            <div class="profile-title">Επεξεργασία Προφίλ</div>
            <div class="profile-desc">Επεξεργαστείτε τα στοιχεία του λογαριασμού σας.</div>
            <?php if ($success_message): ?>
                <div class="alert alert-success text-center"><?php echo $success_message; ?></div>
            <?php endif; ?>
            <form id="editProfileForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . '?id=' . $user_id); ?>" method="post" autocomplete="off">
                <div class="mb-3">
                    <label class="form-label">Όνομα</label>
                    <input type="text" name="first_name" class="form-control <?php echo (!empty($first_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($first_name); ?>">
                    <span class="invalid-feedback"><?php echo $first_name_err; ?></span>
                </div>
                <div class="mb-3">
                    <label class="form-label">Επώνυμο</label>
                    <input type="text" name="last_name" class="form-control <?php echo (!empty($last_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($last_name); ?>">
                    <span class="invalid-feedback"><?php echo $last_name_err; ?></span>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($email); ?>">
                    <span class="invalid-feedback"><?php echo $email_err; ?></span>
                </div>
                <div class="mb-3">
                    <label class="form-label">Νέος Κωδικός</label>
                    <input type="password" name="new_password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                    <span class="invalid-feedback"><?php echo $password_err; ?></span>
                </div>
                <div class="mb-3">
                    <label class="form-label">Επιβεβαίωση Κωδικού</label>
                    <input type="password" name="confirm_password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Ρόλος</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($role); ?>" disabled>
                </div>
                <div class="d-grid gap-2 mt-4">
                    <button type="submit" name="save_profile" class="btn btn-save">Αποθήκευση</button>
                    <button type="button" class="btn btn-del" id="deleteBtn">Διαγραφή Λογαριασμού</button>
                </div>
                <div class="bottom-space"></div>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Delete confirmation
        const deleteBtn = document.getElementById('deleteBtn');
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        const deleteProfileInput = document.getElementById('deleteProfileInput');
        const editProfileForm = document.getElementById('editProfileForm');
        deleteBtn.addEventListener('click', function() {
            deleteModal.show();
        });
        confirmDeleteBtn.addEventListener('click', function() {
            deleteProfileInput.value = '1';
            editProfileForm.submit();
        });
    </script>
</body>
</html> 