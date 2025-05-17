<?php
// Initialize the session
session_start();

// Check if the user is already logged in, if yes then redirect him to welcome page
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: ../../index.php");
    exit;
}

// Include config file
$pdo = require_once "../../config/db_connection.php";

// Define variables and initialize with empty values
$email = $password = "";
$email_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Check if email is empty
    if(empty(trim($_POST["email"]))){
        $email_err = "Παρακαλώ εισάγετε το email σας.";
    } else{
        $email = trim($_POST["email"]);
    }
    
    // Check if password is empty
    if(empty(trim($_POST["password"]))){
        $password_err = "Παρακαλώ εισάγετε τον κωδικό σας.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate credentials
    if(empty($email_err) && empty($password_err)){
        // Prepare a select statement
        $sql = "SELECT id, first_name, last_name, email, password, role, verification_status FROM users WHERE email = :email";
        
        if($stmt = $pdo->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);
            
            // Set parameters
            $param_email = trim($_POST["email"]);
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Check if email exists, if yes then verify password
                if($stmt->rowCount() == 1){
                    if($row = $stmt->fetch()){
                        $id = $row["id"];
                        $first_name = $row["first_name"];
                        $last_name = $row["last_name"];
                        $email = $row["email"];
                        $hashed_password = $row["password"];
                        $role = $row["role"];
                        $verification_status = $row["verification_status"];
                        
                        if(password_verify($password, $hashed_password)){
                            // Check if politician is verified
                            if($role === "Politician" && $verification_status === "pending") {
                                $login_err = "Ο λογαριασμός σας βρίσκεται σε εκκρεμότητα έγκρισης. Παρακαλώ περιμένετε την έγκριση από τον διαχειριστή.";
                            } else if($role === "Politician" && $verification_status === "rejected") {
                                $login_err = "Ο λογαριασμός σας έχει απορριφθεί. Παρακαλώ επικοινωνήστε με τον διαχειριστή για περισσότερες πληροφορίες.";
                            } else {
                                // Password is correct, start a new session
                                session_start();
                                
                                // Store data in session variables
                                $_SESSION["loggedin"] = true;
                                $_SESSION["id"] = $id;
                                $_SESSION["first_name"] = $first_name;
                                $_SESSION["last_name"] = $last_name;
                                $_SESSION["email"] = $email;
                                $_SESSION["role"] = $role;
                                
                                // Redirect user to welcome page
                                header("location: ../welcome.php");
                                exit();
                            }
                        } else {
                            // Password is not valid
                            $login_err = "Μη έγκυρο email ή κωδικός.";
                        }
                    }
                } else {
                    // Email doesn't exist
                    $login_err = "Μη έγκυρο email ή κωδικός.";
                }
            } else {
                echo "Ωχ! Κάτι πήγε στραβά. Παρακαλώ δοκιμάστε ξανά αργότερα.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ΠΟΘΕΝ ΕΣΧΕΣ</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Main Content -->
    <main class="container d-flex align-items-center justify-content-center min-vh-100 py-5">
        <div class="row w-100 justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card feature-card shadow-sm">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <img src="../../assets/images/logo.jpg" alt="ΠΟΘΕΝ ΕΣΧΕΣ Logo" height="60" class="mb-3">
                            <h2 class="fw-bold">Σύνδεση</h2>
                            <p class="text-muted">Καλώς ήρθατε στο ΠΟΘΕΝ ΕΣΧΕΣ</p>
                        </div>

                        <?php 
                        if(!empty($login_err)){
                            echo '<div class="alert alert-danger">' . $login_err . '</div>';
                        }        
                        ?>

                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label class="form-label">Διεύθυνση Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>" required>
                                    <div class="invalid-feedback"><?php echo $email_err; ?></div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Κωδικός</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" required>
                                    <div class="invalid-feedback"><?php echo $password_err; ?></div>
                                </div>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember">
                                <label class="form-check-label" for="remember">Να με θυμάσαι</label>
                            </div>

                            <button type="submit" class="btn btn-warning text-dark w-100 mb-3">
                                <i class="bi bi-box-arrow-in-right"></i> Σύνδεση
                            </button>

                            <div class="text-center">
                                <p class="mb-0">Δεν έχετε λογαριασμό; <a href="register.php" class="text-decoration-none">Εγγραφείτε</a></p>
                            </div>
                            <div class="text-center">
                                <p class="mb-0"><a href="forgot-password.php" class="text-decoration-none">Ξεχάσατε τον κωδικό σας;</a></p>
                            </div>
                            <div class="text-center">
                                <p class="mb-0"><a href="../../index.php" class="text-decoration-none">Επιστροφή στην Αρχική</a></p>
                            </div>
                        </form>
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
