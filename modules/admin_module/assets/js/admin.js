// filepath: c:\xampp\htdocs\webengineering_uni\modules/admin_module/admin_dashboard.php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="#">Admin Dashboard</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="admin_profile.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h1 class="text-center">Admin Dashboard</h1>
        <div class="row mt-4">
            <div class="col-md-3">
                <a href="manage_users.php" class="btn btn-primary btn-block">Manage Users</a>
            </div>
            <div class="col-md-3">
                <a href="manage_submissions.php" class="btn btn-primary btn-block">Manage Submissions</a>
            </div>
            <div class="col-md-3">
                <a href="system_configuration.php" class="btn btn-primary btn-block">System Configuration</a>
            </div>
            <div class="col-md-3">
                <a href="generate_reports.php" class="btn btn-primary btn-block">Generate Reports</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>