<?php
// Include config file
$pdo = require_once "../../config/db_connection.php";

// Initialize variables
$verification_status = "";
$verification_message = "";

// Check if token is provided
if(isset($_GET['token'])) {
    $token = trim($_GET['token']);
    
    // Prepare a select statement
    $sql = "SELECT id, email, verification_expires FROM users WHERE verification_token = :token AND email_verified = 0";
    
    if($stmt = $pdo->prepare($sql)) {
        $stmt->bindParam(":token", $token, PDO::PARAM_STR);
        
        if($stmt->execute()) {
            if($stmt->rowCount() == 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Check if token has expired
                if(strtotime($user['verification_expires']) > time()) {
                    // Update user's email verification status
                    $update_sql = "UPDATE users SET email_verified = 1, verification_token = NULL, verification_expires = NULL WHERE id = :id";
                    
                    if($update_stmt = $pdo->prepare($update_sql)) {
                        $update_stmt->bindParam(":id", $user['id'], PDO::PARAM_INT);
                        
                        if($update_stmt->execute()) {
                            $verification_status = "success";
                            $verification_message = "Το email σας έχει επιβεβαιωθεί επιτυχώς! Μπορείτε τώρα να συνδεθείτε.";
                        } else {
                            $verification_status = "error";
                            $verification_message = "Κάτι πήγε στραβά. Παρακαλώ δοκιμάστε ξανά αργότερα.";
                        }
                    }
                } else {
                    $verification_status = "error";
                    $verification_message = "Ο σύνδεσμος επαλήθευσης έχει λήξει. Παρακαλώ ζητήστε νέο σύνδεσμο επαλήθευσης.";
                }
            } else {
                $verification_status = "error";
                $verification_message = "Μη έγκυρος σύνδεσμος επαλήθευσης.";
            }
        } else {
            $verification_status = "error";
            $verification_message = "Κάτι πήγε στραβά. Παρακαλώ δοκιμάστε ξανά αργότερα.";
        }
    }
} else {
    $verification_status = "error";
    $verification_message = "Δεν παρέχεται σύνδεσμος επαλήθευσης.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - ΠΟΘΕΝ ΕΣΧΕΣ</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../../assets/images/iconlogo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <main class="container d-flex align-items-center justify-content-center min-vh-100 py-5">
        <div class="row w-100 justify-content-center">
            <div class="col-md-6">
                <div class="card feature-card shadow-sm">
                    <div class="card-body p-4 text-center">
                        <img src="../../assets/images/logo.jpg" alt="ΠΟΘΕΝ ΕΣΧΕΣ Logo" height="60" class="mb-3">
                        <h2 class="fw-bold mb-4">Επαλήθευση Email</h2>
                        
                        <?php if($verification_status == "success"): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <?php echo $verification_message; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-circle-fill me-2"></i>
                                <?php echo $verification_message; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mt-4">
                            <a href="login.php" class="btn btn-warning text-dark">
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                Σύνδεση
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 