<!-- filepath: c:\xampp\htdocs\webengineering_uni\webengineering_uni\modules\admin_module\generate_reports.php -->
<?php
include '../../config/db_connection.php';
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: ../login_module/login.php');
    exit();
}

// Fetch statistics
$total_submissions = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM submissions"))['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <h1>Reports</h1>
    <p>Total Submissions: <?= $total_submissions ?></p>
    <!-- Add more statistics and graphs here -->
</body>
</html>