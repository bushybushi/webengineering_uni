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
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    // Redirect to login page if not logged in
    header("location: ../login_module/login.php");
    exit;
}

// --------------------------------------------------------
// PROCESS ACCOUNT DELETION REQUEST
// Handle form submission to mark account as deleted
// --------------------------------------------------------
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Instead of deleting, mark the account as suspended (value 2)
    $sql = "UPDATE users SET is_suspended = 2 WHERE id = :id";
    
    if($stmt = $pdo->prepare($sql)){
        // Bind the user ID from session
        $stmt->bindParam(":id", $_SESSION["id"], PDO::PARAM_INT);
        
        if($stmt->execute()){
            // Clear all session variables
            $_SESSION = array();
            
            // Destroy the session
            session_destroy();
            
            // Redirect to login page after successful "deletion"
            header("location: ../login_module/login.php");
            exit();
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Account</title>
    <!-- Bootstrap CSS for styling and layout -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* Custom styles for delete account page */
        body{ font: 14px sans-serif; }
        .wrapper{ width: 360px; padding: 20px; margin: 0 auto; }
    </style>
</head>
<body>
    <!-- Delete Account Form Container -->
    <div class="wrapper">
        <h2>Delete Account</h2>
        <!-- Warning message about account deletion -->
        <p>Are you sure you want to delete your account? This action cannot be undone.</p>
        
        <!-- Delete Account Form -->
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <!-- Delete account button -->
                <input type="submit" class="btn btn-danger" value="Delete Account">
                <!-- Cancel button to return to profile -->
                <a href="profile.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>    
</body>
</html> 