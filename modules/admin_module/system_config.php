<!-- filepath: c:\xampp\htdocs\webengineering_uni\webengineering_uni\modules\admin_module\system_config.php -->
<?php
include '../../config/db_connection.php';
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: ../login_module/login.php');
    exit();
}

// Fetch and update system configurations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $party = $_POST['party'];
    $query = "INSERT INTO political_parties (name) VALUES ('$party')";
    mysqli_query($conn, $query);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configure System</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <h1>Configure System</h1>
    <form method="POST">
        <h2>Add Political Party</h2>
        <input type="text" name="party" placeholder="Party Name" required>
        <button type="submit">Add Party</button>
    </form>
</body>
</html>