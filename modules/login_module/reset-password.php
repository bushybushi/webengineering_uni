<?php
date_default_timezone_set('Europe/Athens');
// Include config file
$pdo = require_once "../../config/db_connection.php";

// Define variables and initialize with empty values
$new_password = $confirm_password = "";
$new_password_err = $confirm_password_err = "";
$token_err = "";

// Check if token is provided
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST["token"] ?? '';
    // re-validate the token
    $sql = "SELECT id FROM users WHERE reset_token = :token AND reset_expires > NOW() AND reset_used = 0";
    if($stmt = $pdo->prepare($sql)){
        $stmt->bindParam(":token", $token, PDO::PARAM_STR);
        if($stmt->execute()){
            if($stmt->rowCount() == 0){
                $token_err = "Μη έγκυρος ή ληγμένος σύνδεσμος επαναφοράς κωδικού.";
            }
        } else {
            echo "Ωχ! Κάτι πήγε στραβά. Παρακαλώ δοκιμάστε ξανά αργότερα.";
        }
    }
} else if(!isset($_GET["token"]) || empty($_GET["token"])){
    $token_err = "Μη έγκυρος σύνδεσμος επαναφοράς κωδικού.";
} else {
    $token = $_GET["token"];
    // Verify token exists and is not expired
    $sql = "SELECT id FROM users WHERE reset_token = :token AND reset_expires > NOW() AND reset_used = 0";
    if($stmt = $pdo->prepare($sql)){
        $stmt->bindParam(":token", $token, PDO::PARAM_STR);
        if($stmt->execute()){
            if($stmt->rowCount() == 0){
                $token_err = "Μη έγκυρος ή ληγμένος σύνδεσμος επαναφοράς κωδικού.";
            }
        } else {
            echo "Ωχ! Κάτι πήγε στραβά. Παρακαλώ δοκιμάστε ξανά αργότερα.";
        }
    }
}

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST" && empty($token_err)){
    
    // Validate new password
    if(empty(trim($_POST["new_password"]))){
        $new_password_err = "Παρακαλώ εισάγετε τον νέο κωδικό.";     
    } elseif(strlen(trim($_POST["new_password"])) < 6){
        $new_password_err = "Ο κωδικός πρέπει να έχει τουλάχιστον 6 χαρακτήρες.";
    } else{
        $new_password = trim($_POST["new_password"]);
    }
    
    // Validate confirm password
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Παρακαλώ επιβεβαιώστε τον κωδικό.";
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($new_password_err) && ($new_password != $confirm_password)){
            $confirm_password_err = "Οι κωδικοί δεν ταιριάζουν.";
        }
    }
    
    // Check input errors before updating the database
    if(empty($new_password_err) && empty($confirm_password_err)){
        // Prepare an update statement
        $sql = "UPDATE users SET password = :password, reset_used = 1 WHERE reset_token = :token";
        
        if($stmt = $pdo->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $param_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt->bindParam(":password", $param_password, PDO::PARAM_STR);
            $stmt->bindParam(":token", $token, PDO::PARAM_STR);
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Password updated successfully. Destroy the session, and redirect to login page
                session_start();
                session_destroy();
                header("location: login.php");
                exit();
            } else{
                echo "Ωχ! Κάτι πήγε στραβά. Παρακαλώ δοκιμάστε ξανά αργότερα.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<!-- Favicon -->
    <link rel="icon" type="image/png" href="../../assets/images/iconlogo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - ΠΟΘΕΝ ΕΣΧΕΣ</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Main Content -->
    <main class="container d-flex align-items-center justify-content-center min-vh-100 py-5">
        <div class="row w-100 justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card feature-card shadow-sm">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <img src="assets/images/logo.jpg" alt="ΠΟΘΕΝ ΕΣΧΕΣ Logo" height="60" class="mb-3">
                            <h2 class="fw-bold">Επαναφορά Κωδικού</h2>
                            <p class="text-muted">Εισάγετε τον νέο σας κωδικό</p>
                        </div>

                        <?php if(!empty($token_err)): ?>
                            <div class="alert alert-danger"><?php echo $token_err; ?></div>
                            <div class="text-center">
                                <p class="mb-0"><a href="forgot-password.php" class="text-decoration-none">Ζητήστε νέο σύνδεσμο επαναφοράς κωδικού</a></p>
                            </div>
                        <?php else: ?>
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="needs-validation" novalidate>
                                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                                <div class="mb-3">
                                    <label class="form-label">Νέος Κωδικός</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                        <input type="password" name="new_password" class="form-control <?php echo (!empty($new_password_err)) ? 'is-invalid' : ''; ?>" required>
                                        <div class="invalid-feedback"><?php echo $new_password_err; ?></div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Επιβεβαίωση Κωδικού</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                        <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" required>
                                        <div class="invalid-feedback"><?php echo $confirm_password_err; ?></div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-warning text-dark w-100 mb-3">
                                    <i class="bi bi-key"></i> Επαναφορά Κωδικού
                                </button>

                                <div class="text-center">
                                    <p class="mb-0"><a href="login.php" class="text-decoration-none">Επιστροφή στην Σύνδεση</a></p>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
</body>
</html> 
