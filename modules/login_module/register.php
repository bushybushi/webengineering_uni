<?php
// Include config file
$pdo = require_once "../../config/db_connection.php";

// Define variables and initialize with empty values
$first_name = $last_name = $email = $password = $confirm_password = $role = "";
$title = $office = $address = $dob = $id_number = $marital_status = $num_of_dependents = $political_affiliation = "";
$first_name_err = $last_name_err = $email_err = $password_err = $confirm_password_err = $role_err = "";
$front_photo_err = $back_photo_err = "";

// Sanitization functions
function sanitizeString($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function sanitizeEmail($input) {
    return filter_var(trim($input), FILTER_SANITIZE_EMAIL);
}

function sanitizeRole($input) {
    $allowed_roles = ['Public', 'Politician'];
    $input = trim($input);
    return in_array($input, $allowed_roles) ? $input : '';
}

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // Validate and sanitize first name
    if(empty(trim($_POST["first_name"]))){
        $first_name_err = "Παρακαλώ εισάγετε το μικρό σας όνομα.";
    } else{
        $first_name = sanitizeString($_POST["first_name"]);
    }
    
    // Validate and sanitize last name
    if(empty(trim($_POST["last_name"]))){
        $last_name_err = "Παρακαλώ εισάγετε το επώνυμο σας.";
    } else{
        $last_name = sanitizeString($_POST["last_name"]);
    }
    
    // Validate and sanitize email
    if(empty(trim($_POST["email"]))){
        $email_err = "Παρακαλώ εισάγετε ένα email.";
    } else{
        $email = sanitizeEmail($_POST["email"]);
        // Prepare a select statement
        $sql = "SELECT id FROM users WHERE email = :email AND is_suspended != 2";
        
        if($stmt = $pdo->prepare($sql)){
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            
            if($stmt->execute()){
                if($stmt->rowCount() > 0){
                    $email_err = "Αυτό το email χρησιμοποιείται ήδη.";
                }
            } else{
                echo "Ωχ! Κάτι πήγε στραβά. Παρακαλώ δοκιμάστε ξανά αργότερα.";
            }
        }
    }
    
    // Validate password (no sanitization needed as it will be hashed)
    if(empty(trim($_POST["password"]))){
        $password_err = "Παρακαλώ εισάγετε έναν κωδικό.";     
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "Ο κωδικός πρέπει να έχει τουλάχιστον 6 χαρακτήρες.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Παρακαλώ επιβεβαιώστε τον κωδικό.";     
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Οι κωδικοί δεν ταιριάζουν.";
        }
    }
    
    // Validate and sanitize role
    if(empty(trim($_POST["role"]))){
        $role_err = "Παρακαλώ επιλέξτε έναν ρόλο.";
    } else{
        $role = sanitizeRole($_POST["role"]);
        if(empty($role)) {
            $role_err = "Μη έγκυρος ρόλος.";
        }
    }

    // Get and sanitize additional fields
    $title = isset($_POST["title"]) ? sanitizeString($_POST["title"]) : "";
    $office = isset($_POST["office"]) ? sanitizeString($_POST["office"]) : "";
    $address = isset($_POST["address"]) ? sanitizeString($_POST["address"]) : "";
    $dob = isset($_POST["dob"]) ? sanitizeString($_POST["dob"]) : null;
    $id_number = isset($_POST["id_number"]) ? sanitizeString($_POST["id_number"]) : "";
    $marital_status = isset($_POST["marital_status"]) ? sanitizeString($_POST["marital_status"]) : "";
    $num_of_dependents = isset($_POST["num_of_dependents"]) ? (int)filter_var($_POST["num_of_dependents"], FILTER_SANITIZE_NUMBER_INT) : 0;
    $political_affiliation = isset($_POST["political_affiliation"]) ? sanitizeString($_POST["political_affiliation"]) : "";
    
    // Validate ID photos for politicians
    if($role === "Politician") {
        if(!isset($_FILES["front_photo"]) || $_FILES["front_photo"]["error"] == UPLOAD_ERR_NO_FILE) {
            $front_photo_err = "Παρακαλώ ανεβάστε τη μπροστινή πλευρά της ταυτότητάς σας.";
        }
        if(!isset($_FILES["back_photo"]) || $_FILES["back_photo"]["error"] == UPLOAD_ERR_NO_FILE) {
            $back_photo_err = "Παρακαλώ ανεβάστε την πίσω πλευρά της ταυτότητάς σας.";
        }
    }
    
    // Check input errors before inserting in database
    if(empty($first_name_err) && empty($last_name_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err) && empty($role_err) && 
       ($role !== "Politician" || (empty($front_photo_err) && empty($back_photo_err)))) {
        
        try {
            $pdo->beginTransaction();
            
            // Insert into users table
            $sql = "INSERT INTO users (first_name, last_name, email, password, role, verification_status) 
                    VALUES (:first_name, :last_name, :email, :password, :role, :verification_status)";
            
            $stmt = $pdo->prepare($sql);
            
            // Set parameters
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            $verification_status = ($role === "Politician") ? "pending" : NULL;
            
            $stmt->bindParam(":first_name", $first_name);
            $stmt->bindParam(":last_name", $last_name);
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":password", $param_password);
            $stmt->bindParam(":role", $role);
            $stmt->bindParam(":verification_status", $verification_status);
            
            $stmt->execute();
            $user_id = $pdo->lastInsertId();
            
            // Handle ID photos for politicians
            if($role === "Politician") {
                $upload_dir = "../../uploads/id_photos/";
                if(!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $front_photo_name = $user_id . "_front_" . time() . ".jpg";
                $back_photo_name = $user_id . "_back_" . time() . ".jpg";
                
                move_uploaded_file($_FILES["front_photo"]["tmp_name"], $upload_dir . $front_photo_name);
                move_uploaded_file($_FILES["back_photo"]["tmp_name"], $upload_dir . $back_photo_name);
                
                $sql = "INSERT INTO politician_id_photos (user_id, front_photo_path, back_photo_path) 
                        VALUES (:user_id, :front_photo_path, :back_photo_path)";
                
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(":user_id", $user_id);
                $stmt->bindParam(":front_photo_path", $front_photo_name);
                $stmt->bindParam(":back_photo_path", $back_photo_name);
                $stmt->execute();
            }
            
            $pdo->commit();
            
            // Redirect to login page
            header("location: login.php");
            exit();
            
        } catch(PDOException $e) {
            $pdo->rollBack();
            echo "Κάτι πήγε στραβά. Παρακαλώ δοκιμάστε ξανά αργότερα.";
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
    <title>Register - ΠΟΘΕΝ ΕΣΧΕΣ</title>
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
                            <h2 class="fw-bold">Δημιουργία Λογαριασμού</h2>
                            <p class="text-muted">Γίνετε μέλος του ΠΟΘΕΝ ΕΣΧΕΣ για να υποβάλετε τις δηλώσεις περιουσίας σας</p>
                        </div>

                        <form action="/modules/login_module/register.php" method="post" class="needs-validation" novalidate enctype="multipart/form-data">
                            <div class="row g-3">
                                <!-- Basic Information -->
                                <div class="col-md-6">
                                    <label class="form-label">Όνομα</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input type="text" name="first_name" class="form-control <?php echo (!empty($first_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $first_name; ?>" required>
                                        <div class="invalid-feedback"><?php echo $first_name_err; ?></div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Επώνυμο</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input type="text" name="last_name" class="form-control <?php echo (!empty($last_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $last_name; ?>" required>
                                        <div class="invalid-feedback"><?php echo $last_name_err; ?></div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Διεύθυνση Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                        <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>" required>
                                        <div class="invalid-feedback"><?php echo $email_err; ?></div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Κωδικός</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                        <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" required>
                                        <div class="invalid-feedback"><?php echo $password_err; ?></div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Επιβεβαίωση Κωδικού</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                        <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" required>
                                        <div class="invalid-feedback"><?php echo $confirm_password_err; ?></div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Θέση/Ρόλος</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-briefcase"></i></span>
                                        <select name="role" class="form-select <?php echo (!empty($role_err)) ? 'is-invalid' : ''; ?>" required onchange="toggleIdPhotos(this.value)">
                                            <option value="">Επιλέξτε θέση</option>
                                            <option value="Public" <?php echo ($role == "Public") ? 'selected' : ''; ?>>Δημόσιος</option>
                                            <option value="Politician" <?php echo ($role == "Politician") ? 'selected' : ''; ?>>Πολιτικός</option>
                                        </select>
                                        <div class="invalid-feedback"><?php echo $role_err; ?></div>
                                    </div>
                                </div>

                                <!-- ID Photos for Politicians -->
                                <div id="idPhotosSection" style="display: none;">
                                    <div class="col-md-6">
                                        <label class="form-label">Μπροστινή Πλευρά Ταυτότητας</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-card-image"></i></span>
                                            <input type="file" name="front_photo" class="form-control <?php echo (!empty($front_photo_err)) ? 'is-invalid' : ''; ?>" accept="image/*">
                                            <div class="invalid-feedback"><?php echo $front_photo_err; ?></div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Πίσω Πλευρά Ταυτότητας</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-card-image"></i></span>
                                            <input type="file" name="back_photo" class="form-control <?php echo (!empty($back_photo_err)) ? 'is-invalid' : ''; ?>" accept="image/*">
                                            <div class="invalid-feedback"><?php echo $back_photo_err; ?></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="terms" required>
                                        <label class="form-check-label" for="terms">
                                            Συμφωνώ με τους <a href="terms.php" class="text-decoration-none">Όρους Χρήσης</a> και την <a href="privacy.php" class="text-decoration-none">Πολιτική Απορρήτου</a>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-warning text-dark w-100 mt-4 mb-3">
                                <i class="bi bi-person-plus"></i> Δημιουργία Λογαριασμού
                            </button>

                            <div class="text-center">
                                <p class="mb-0">Έχετε ήδη λογαριασμό; <a href="login.php" class="text-decoration-none">Σύνδεση</a></p>
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

    <script>
    function toggleIdPhotos(role) {
        const idPhotosSection = document.getElementById('idPhotosSection');
        if (role === 'Politician') {
            idPhotosSection.style.display = 'block';
        } else {
            idPhotosSection.style.display = 'none';
        }
    }

    // Call on page load to set initial state
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.querySelector('select[name="role"]');
        toggleIdPhotos(roleSelect.value);
    });
    </script>
</body>
</html> 
