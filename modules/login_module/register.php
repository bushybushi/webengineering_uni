<?php
// Include config file
$pdo = require_once "db_connection.php";

// Define variables and initialize with empty values
$first_name = $last_name = $email = $password = $confirm_password = $role = "";
$title = $office = $address = $dob = $id_number = $marital_status = $num_of_dependents = $political_affiliation = "";
$first_name_err = $last_name_err = $email_err = $password_err = $confirm_password_err = $role_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // Validate first name
    if(empty(trim($_POST["first_name"]))){
        $first_name_err = "Please enter your first name.";
    } else{
        $first_name = trim($_POST["first_name"]);
    }
    
    // Validate last name
    if(empty(trim($_POST["last_name"]))){
        $last_name_err = "Please enter your last name.";
    } else{
        $last_name = trim($_POST["last_name"]);
    }
    
    // Validate email
    if(empty(trim($_POST["email"]))){
        $email_err = "Please enter an email.";
    } else{
        // Prepare a select statement
        $sql = "SELECT id FROM users WHERE email = :email";
        
        if($stmt = $pdo->prepare($sql)){
            $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);
            $param_email = trim($_POST["email"]);
            
            if($stmt->execute()){
                if($stmt->rowCount() == 1){
                    $email_err = "This email is already taken.";
                } else{
                    $email = trim($_POST["email"]);
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
    }
    
    // Validate password
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter a password.";     
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "Password must have at least 6 characters.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Please confirm password.";     
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Password did not match.";
        }
    }
    
    // Validate role
    if(empty(trim($_POST["role"]))){
        $role_err = "Please select a role.";
    } else{
        $role = trim($_POST["role"]);
    }

    // Get additional fields
    $title = isset($_POST["title"]) ? trim($_POST["title"]) : "";
    $office = isset($_POST["office"]) ? trim($_POST["office"]) : "";
    $address = isset($_POST["address"]) ? trim($_POST["address"]) : "";
    $dob = isset($_POST["dob"]) ? trim($_POST["dob"]) : null;
    $id_number = isset($_POST["id_number"]) ? trim($_POST["id_number"]) : "";
    $marital_status = isset($_POST["marital_status"]) ? trim($_POST["marital_status"]) : "";
    $num_of_dependents = isset($_POST["num_of_dependents"]) ? (int)trim($_POST["num_of_dependents"]) : 0;
    $political_affiliation = isset($_POST["political_affiliation"]) ? trim($_POST["political_affiliation"]) : "";
    
    // Check input errors before inserting in database
    if(empty($first_name_err) && empty($last_name_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err) && empty($role_err)){
        
        try {
            // Start transaction
            $pdo->beginTransaction();
            
            // First, insert into people table
            $sql = "INSERT INTO people (name, title, office, address, dob, id_number, marital_status, num_of_dependents, date_of_submission, political_affiliation) 
                    VALUES (:name, :title, :office, :address, :dob, :id_number, :marital_status, :num_of_dependents, :date, :political_affiliation)";
            
            $stmt = $pdo->prepare($sql);
            
            // Set parameters
            $param_name = $first_name . " " . $last_name;
            $param_date = date("Y-m-d");
            
            $stmt->bindParam(":name", $param_name);
            $stmt->bindParam(":title", $title);
            $stmt->bindParam(":office", $office);
            $stmt->bindParam(":address", $address);
            $stmt->bindParam(":dob", $dob);
            $stmt->bindParam(":id_number", $id_number);
            $stmt->bindParam(":marital_status", $marital_status);
            $stmt->bindParam(":num_of_dependents", $num_of_dependents);
            $stmt->bindParam(":date", $param_date);
            $stmt->bindParam(":political_affiliation", $political_affiliation);
            
            $stmt->execute();
            $person_id = $pdo->lastInsertId();
            
            // Now insert into users table
            $sql = "INSERT INTO users (first_name, last_name, email, password, role, person_id) 
                    VALUES (:first_name, :last_name, :email, :password, :role, :person_id)";
            
            $stmt = $pdo->prepare($sql);
            
            // Set parameters
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt->bindParam(":first_name", $first_name);
            $stmt->bindParam(":last_name", $last_name);
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":password", $param_password);
            $stmt->bindParam(":role", $role);
            $stmt->bindParam(":person_id", $person_id);
            
            $stmt->execute();
            
            // Commit transaction
            $pdo->commit();
            
            // Redirect to login page
            header("location: login.php");
            exit();
            
        } catch(PDOException $e) {
            // Rollback transaction on error
            $pdo->rollBack();
            echo "Something went wrong. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - ΠΟΘΕΝ ΕΣΧΕΣ</title>
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
                            <h2 class="fw-bold">Create Account</h2>
                            <p class="text-muted">Join ΠΟΘΕΝ ΕΣΧΕΣ to submit your asset declarations</p>
                        </div>

                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="needs-validation" novalidate>
                            <div class="row g-3">
                                <!-- Basic Information -->
                                <div class="col-md-6">
                                    <label class="form-label">First Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input type="text" name="first_name" class="form-control <?php echo (!empty($first_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $first_name; ?>" required>
                                        <div class="invalid-feedback"><?php echo $first_name_err; ?></div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Last Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input type="text" name="last_name" class="form-control <?php echo (!empty($last_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $last_name; ?>" required>
                                        <div class="invalid-feedback"><?php echo $last_name_err; ?></div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                        <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>" required>
                                        <div class="invalid-feedback"><?php echo $email_err; ?></div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                        <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" required>
                                        <div class="invalid-feedback"><?php echo $password_err; ?></div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Confirm Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                        <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" required>
                                        <div class="invalid-feedback"><?php echo $confirm_password_err; ?></div>
                                    </div>
                                </div>

                                <!-- Additional Information -->
                                <div class="col-12">
                                    <label class="form-label">Title</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                        <input type="text" name="title" class="form-control" value="<?php echo $title; ?>">
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Office</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-building"></i></span>
                                        <input type="text" name="office" class="form-control" value="<?php echo $office; ?>">
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                        <textarea name="address" class="form-control"><?php echo $address; ?></textarea>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Date of Birth</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                                        <input type="date" name="dob" class="form-control" value="<?php echo $dob; ?>">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">ID Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                        <input type="text" name="id_number" class="form-control" value="<?php echo $id_number; ?>">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Marital Status</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-people"></i></span>
                                        <select name="marital_status" class="form-select">
                                            <option value="">Select status</option>
                                            <option value="Single" <?php echo ($marital_status == "Single") ? 'selected' : ''; ?>>Single</option>
                                            <option value="Married" <?php echo ($marital_status == "Married") ? 'selected' : ''; ?>>Married</option>
                                            <option value="Divorced" <?php echo ($marital_status == "Divorced") ? 'selected' : ''; ?>>Divorced</option>
                                            <option value="Widowed" <?php echo ($marital_status == "Widowed") ? 'selected' : ''; ?>>Widowed</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Number of Dependents</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-people-fill"></i></span>
                                        <input type="number" name="num_of_dependents" class="form-control" value="<?php echo $num_of_dependents; ?>" min="0">
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Political Affiliation</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-flag"></i></span>
                                        <input type="text" name="political_affiliation" class="form-control" value="<?php echo $political_affiliation; ?>">
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Position/Role</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-briefcase"></i></span>
                                        <select name="role" class="form-select <?php echo (!empty($role_err)) ? 'is-invalid' : ''; ?>" required>
                                            <option value="">Select position</option>
                                            <option value="Public" <?php echo ($role == "Public") ? 'selected' : ''; ?>>Public</option>
                                            <option value="Politician" <?php echo ($role == "Politician") ? 'selected' : ''; ?>>Politician</option>
                                        </select>
                                        <div class="invalid-feedback"><?php echo $role_err; ?></div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="terms" required>
                                        <label class="form-check-label" for="terms">
                                            I agree to the <a href="terms.php" class="text-decoration-none">Terms of Service</a> and <a href="privacy.php" class="text-decoration-none">Privacy Policy</a>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-warning text-dark w-100 mt-4 mb-3">
                                <i class="bi bi-person-plus"></i> Create Account
                            </button>

                            <div class="text-center">
                                <p class="mb-0">Already have an account? <a href="login.php" class="text-decoration-none">Sign in</a></p>
                            </div>
                            <div class="text-center">
                                <p class="mb-0"><a href="index.php" class="text-decoration-none">Back to Home</a></p>
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
