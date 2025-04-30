<!-- filepath: c:\xampp\htdocs\webengineering_uni\webengineering_uni\modules\admin_module\manage_users.php -->
<?php
include '../../config/db_connection.php';
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: ../login_module/login.php');
    exit();
}

// Fetch users
$query = "SELECT * FROM users";
$result = mysqli_query($conn, $query);

// Handle user actions (add, update, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_user'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $query = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')";
        mysqli_query($conn, $query);
    } elseif (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];
        $query = "DELETE FROM users WHERE id = $user_id";
        mysqli_query($conn, $query);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <h1>Manage Users</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['name'] ?></td>
                    <td><?= $row['email'] ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                            <button type="submit" name="delete_user">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    <form method="POST">
        <h2>Add User</h2>
        <input type="text" name="name" placeholder="Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="add_user">Add User</button>
    </form>
</body>
</html>