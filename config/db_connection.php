<?php
$host = 'websrv1.cut.ac.cy'; 
$dbname = 'cei326omada4';
$username = 'cei326omada4user'; 
$password = 'ykZ8sx]U7RLuftlO';

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);

    // Set PDO error mode to exception for better error handling
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Handle database connection errors
    die("Database connection failed: " . $e->getMessage());
}

// Export the $pdo object for use in other files
return $pdo;
?>
