<?php
//CODE CREATED BY JORGOS XIDIAS AND TEAM
//AI HAS BEEN USED TO ADD GOOD COMMENTS AND TO BEAUTIFY THE CODE
// Connect to the database
$pdo = require_once "../../config/db_connection.php";

// Get user ID from URL parameter or default to first user
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // If no ID provided, get the first user from the database
    $stmt = $pdo->query("SELECT id FROM users ORDER BY id ASC LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && isset($row['id'])) {
        $user_id = (int)$row['id'];
    } else {
        die('Δεν βρέθηκαν χρήστες στη βάση δεδομένων.');
    }
} else {
    $user_id = (int)$_GET['id'];
}

// Initialize variables for form data and error messages
$first_name = $last_name = $email = $role = "";
$first_name_err = $last_name_err = $email_err = $password_err = $new_password_err = $confirm_password_err = $current_password_err = "";
$success_message = "";

// Fetch user's current information from database
$sql = "SELECT first_name, last_name, email, role, password FROM users WHERE id = :id";
if($stmt = $pdo->prepare($sql)){
    $stmt->bindParam(":id", $user_id, PDO::PARAM_INT);
    if($stmt->execute()){
        if($stmt->rowCount() == 1){
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $first_name = $row["first_name"];
            $last_name = $row["last_name"];
            $email = $row["email"];
            $role = $row["role"];
            $hashed_password = $row["password"];
        } else {
            die('Ο χρήστης δεν βρέθηκε.');
        }
    } else {
        die('Σφάλμα βάσης δεδομένων.');
    }
}

// Process form submission
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Handle profile update
    if (isset($_POST['save_profile'])) {
        // Validate first name
        if(empty(trim($_POST["first_name"]))){
            $first_name_err = "Συμπληρώστε το όνομα.";
        } else{
            $first_name = trim($_POST["first_name"]);
        }
        
        // Validate last name
        if(empty(trim($_POST["last_name"]))){
            $last_name_err = "Συμπληρώστε το επώνυμο.";
        } else{
            $last_name = trim($_POST["last_name"]);
        }
        
        // Validate email
        if(empty(trim($_POST["email"]))){
            $email_err = "Συμπληρώστε το email.";
        } else{
            // Check if email is already in use by another user
            $sql = "SELECT id FROM users WHERE email = :email AND id != :id";
            if($stmt = $pdo->prepare($sql)){
                $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);
                $stmt->bindParam(":id", $user_id, PDO::PARAM_INT);
                $param_email = trim($_POST["email"]);
                
                if($stmt->execute()){
                    if($stmt->rowCount() == 1){
                        $email_err = "Αυτό το email χρησιμοποιείται ήδη.";
                    } else{
                        $email = trim($_POST["email"]);
                    }
                }
            }
        }
        
        // If no errors, update the database
        if(empty($first_name_err) && empty($last_name_err) && empty($email_err)){
            $sql = "UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email WHERE id = :id";
            
            if($stmt = $pdo->prepare($sql)){
                // Bind parameters for the update
                $stmt->bindParam(":first_name", $first_name, PDO::PARAM_STR);
                $stmt->bindParam(":last_name", $last_name, PDO::PARAM_STR);
                $stmt->bindParam(":email", $email, PDO::PARAM_STR);
                $stmt->bindParam(":id", $user_id, PDO::PARAM_INT);
                
                // Execute the update
                if($stmt->execute()){
                    $success_message = "Τα στοιχεία ενημερώθηκαν επιτυχώς.";
                } else{
                    $success_message = "Σφάλμα κατά την ενημέρωση. Δοκιμάστε ξανά.";
                }
            }
        }
    } 
    // Handle password change
    elseif (isset($_POST['change_password'])) {
        // Get password fields from form
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validate current password
        if (empty($current_password)) {
            $current_password_err = "Συμπληρώστε τον τρέχοντα κωδικό.";
        } elseif (!password_verify($current_password, $hashed_password)) {
            $current_password_err = "Λάθος τρέχων κωδικός.";
        }

        // Validate new password
        if (empty($new_password)) {
            $new_password_err = "Συμπληρώστε νέο κωδικό.";
        } elseif (strlen($new_password) < 6) {
            $new_password_err = "Ο κωδικός πρέπει να έχει τουλάχιστον 6 χαρακτήρες.";
        }

        // Validate password confirmation
        if ($new_password !== $confirm_password) {
            $confirm_password_err = "Οι κωδικοί δεν ταιριάζουν.";
        }

        // If no errors, update the password
        if (empty($current_password_err) && empty($new_password_err) && empty($confirm_password_err)) {
            $sql = "UPDATE users SET password = :password WHERE id = :id";
            if($stmt = $pdo->prepare($sql)){
                // Hash the new password before storing
                $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt->bindParam(":password", $new_hashed, PDO::PARAM_STR);
                $stmt->bindParam(":id", $user_id, PDO::PARAM_INT);
                if($stmt->execute()){
                    $success_message = "Ο κωδικός ενημερώθηκε επιτυχώς.";
                } else{
                    $success_message = "Σφάλμα κατά την ενημέρωση κωδικού.";
                }
            }
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
        .btn-edit { background: linear-gradient(90deg, #ED9635 60%, #f0a85a 100%); color: #fff; border: none; border-radius: 8px; font-size: 1.1rem; font-weight: 500; padding: 12px 0; width: 100%; transition: box-shadow 0.2s; box-shadow: 0 2px 8px rgba(237,150,53,0.08); }
        .btn-edit:hover { background: linear-gradient(90deg, #f0a85a 60%, #ED9635 100%); color: #fff; box-shadow: 0 4px 16px rgba(237,150,53,0.15); }
        .navbar { margin-bottom: 0; }
        .bottom-space { height: 60px; }
    </style>
</head>
<body>
    <!-- Top Navigation Bar -->
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
                            <button class="lang-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-translate"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="?lang=el"><span class="fi fi-gr"></span> Ελληνικά</a></li>
                                <li><a class="dropdown-item" href="?lang=en"><span class="fi fi-gb"></span> English</a></li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <div class="dropdown">
                            <button class="profile-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="../login_module/login.php"><i class="bi bi-box-arrow-in-right"></i> Είσοδος</a></li>
                                <li><a class="dropdown-item" href="../login_module/register.php"><i class="bi bi-person-plus"></i> Εγγραφή</a></li>
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
            <div class="profile-title">Προφίλ</div>
            <div class="profile-desc">Τα στοιχεία του λογαριασμού σας.</div>
            <?php if ($success_message): ?>
                <div class="alert alert-success text-center"><?php echo $success_message; ?></div>
            <?php endif; ?>
            <form autocomplete="off">
                <div class="mb-3">
                    <label class="form-label">Όνομα</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($first_name); ?>" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Επώνυμο</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($last_name); ?>" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Ρόλος</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($role); ?>" readonly>
                </div>
                <div class="d-grid gap-2 mt-4">
                    <a href="edit_profile.php?id=<?php echo $user_id; ?>" class="btn btn-edit">Επεξεργασία / Διαγραφή</a>
                </div>
                <div class="bottom-space"></div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 