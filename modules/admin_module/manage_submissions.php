<!-- filepath: c:\xampp\htdocs\webengineering_uni\webengineering_uni\modules\admin_module\manage_submissions.php -->
<?php
include '../../config/db_connection.php';
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: ../login_module/login.php');
    exit();
}

// Fetch submissions from the database
$query = "SELECT s.id, s.date_of_submission, s.status, p.name AS person_name 
          FROM submissions s 
          JOIN people p ON s.person_id = p.id";
$result = mysqli_query($conn, $query);

// Handle submission actions (approve/reject)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submission_id = $_POST['submission_id'];
    if (isset($_POST['approve'])) {
        $update_query = "UPDATE submissions SET status = 'Approved' WHERE id = $submission_id";
        mysqli_query($conn, $update_query);
    } elseif (isset($_POST['reject'])) {
        $update_query = "UPDATE submissions SET status = 'Rejected' WHERE id = $submission_id";
        mysqli_query($conn, $update_query);
    }
    header('Location: manage_submissions.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Submissions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="./assets/css/style.css" rel="stylesheet">
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
                        <a class="nav-link" href="../login_module/logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h1 class="text-center">Manage Submissions</h1>
        <table class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Person Name</th>
                    <th>Date of Submission</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['person_name'] ?></td>
                        <td><?= $row['date_of_submission'] ?></td>
                        <td><?= $row['status'] ?></td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="submission_id" value="<?= $row['id'] ?>">
                                <button type="submit" name="approve" class="btn btn-success btn-sm">Approve</button>
                            </form>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="submission_id" value="<?= $row['id'] ?>">
                                <button type="submit" name="reject" class="btn btn-danger btn-sm">Reject</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>