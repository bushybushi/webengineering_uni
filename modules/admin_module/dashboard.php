<!-- filepath: c:\xampp\htdocs\webengineering_uni\webengineering_uni\modules\admin_module\dashboard.php -->
<?php
// Include necessary files and authentication checks
include '../../config/db_connection.php';
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: ../login_module/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <div class="dashboard">
        <h1>Admin Dashboard</h1>
        <div class="buttons">
            <a href="manage_users.php" class="btn">Manage Users</a>
            <a href="manage_submissions.php" class="btn">Manage Submissions</a>
            <a href="system_config.php" class="btn">Configure System</a>
            <a href="generate_reports.php" class="btn">Reports</a>
        </div>
    </div>
</body>
</html>