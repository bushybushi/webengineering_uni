<?php
//CODE CREATED BY JORGOS XIDIAS AND TEAM
//AI HAS BEEN USED TO ADD GOOD COMMENTS AND TO BEAUTIFY THE CODE
// Connect to the database
$pdo = require_once "../../config/db_connection.php";

// Check if user is logged in
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    // Redirect to login page if not logged in
    header("location: ../login_module/login.php");
    exit;
}

// Process form submission
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Prepare SQL statement to delete user
    $sql = "DELETE FROM users WHERE id = :id";
    
    if($stmt = $pdo->prepare($sql)){
        // Bind the user ID from session
        $stmt->bindParam(":id", $_SESSION["id"], PDO::PARAM_INT);
        
        if($stmt->execute()){
            // Clear all session variables
            $_SESSION = array();
            
            // Destroy the session
            session_destroy();
            
            // Redirect to login page after successful deletion
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
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body{ font: 14px sans-serif; }
        .wrapper{ width: 360px; padding: 20px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Delete Account</h2>
        <!-- Warning message about account deletion -->
        <p>Are you sure you want to delete your account? This action cannot be undone.</p>
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