<!-- filepath: c:\xampp\htdocs\webengineering_uni\webengineering_uni\modules\admin_module\generate_reports.php -->
<?php
include '../../config/db_connection.php';
session_start();

// Fetch statistics
$total_submissions = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM submissions"))['total'];
$approved_submissions = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM submissions WHERE status = 'Approved'"))['total'];
$rejected_submissions = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM submissions WHERE status = 'Rejected'"))['total'];
$pending_submissions = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM submissions WHERE status = 'Pending'"))['total'];

// Fetch submissions by year
$submissions_by_year = mysqli_query($conn, "
    SELECT YEAR(date_of_submission) as year, COUNT(*) as count 
    FROM submissions 
    GROUP BY YEAR(date_of_submission) 
    ORDER BY year DESC
");

// Fetch submissions by party
$submissions_by_party = mysqli_query($conn, "
    SELECT pp.name as party_name, COUNT(s.id) as count 
    FROM submissions s 
    JOIN people p ON s.person_id = p.id 
    JOIN political_parties pp ON p.party_id = pp.id 
    GROUP BY pp.name 
    ORDER BY count DESC
");

// Fetch people without submissions
$people_without_submissions = mysqli_query($conn, "
    SELECT p.name, pp.name as party_name, pos.name as position_name 
    FROM people p 
    JOIN political_parties pp ON p.party_id = pp.id 
    JOIN positions pos ON p.position_id = pos.id 
    LEFT JOIN submissions s ON p.id = s.person_id 
    WHERE s.id IS NULL
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Αναφορές - ΠΟΘΕΝ ΕΣΧΕΣ</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../../assets/images/iconlogo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom CSS -->
    <link href="../../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="../../index.php">
                <img src="../../assets/images/logo.jpg" alt="ΠΟΘΕΝ ΕΣΧΕΣ Logo" height="40" class="me-3">
                <span class="fw-bold">ΠΟΘΕΝ ΕΣΧΕΣ</span>
            </a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="../../index.php">Αρχική</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../search_module/search.php">Αναζήτηση</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../search_module/statistics.php">Στατιστικά</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../submit_module/declaration-form.php">Υποβολή</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mt-5 pt-5">
        <h1 class="text-center mb-4">Αναφορές και Στατιστικά</h1>
        
        <!-- Quick Stats -->
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-file-text feature-icon mb-3" style="font-size: 2rem; color: #ED9635;"></i>
                        <h5 class="card-title">Συνολικές Υποβολές</h5>
                        <h2 class="mb-0"><?= $total_submissions ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-check-circle feature-icon mb-3" style="font-size: 2rem; color: #28a745;"></i>
                        <h5 class="card-title">Εγκεκριμένες</h5>
                        <h2 class="mb-0"><?= $approved_submissions ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-x-circle feature-icon mb-3" style="font-size: 2rem; color: #dc3545;"></i>
                        <h5 class="card-title">Απορριφθείσες</h5>
                        <h2 class="mb-0"><?= $rejected_submissions ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-clock feature-icon mb-3" style="font-size: 2rem; color: #ffc107;"></i>
                        <h5 class="card-title">Σε Εκκρεμότητα</h5>
                        <h2 class="mb-0"><?= $pending_submissions ?></h2>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Charts -->
        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Υποβολές ανά Έτος</h5>
                        <canvas id="submissionsByYearChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Υποβολές ανά Κόμμα</h5>
                        <canvas id="submissionsByPartyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- People Without Submissions -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Άτομα χωρίς Υποβολές</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Όνομα</th>
                                <th>Κόμμα</th>
                                <th>Θέση</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($person = mysqli_fetch_assoc($people_without_submissions)) { ?>
                                <tr>
                                    <td><?= $person['name'] ?></td>
                                    <td><?= $person['party_name'] ?></td>
                                    <td><?= $person['position_name'] ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-light py-4 mt-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-12 col-md-6 text-center text-md-start mb-3 mb-md-0">
                    <p class="mb-0"> 2025 Πόθεν Εσχες &copy; all rights reserved.</p>
                </div>
                <div class="col-12 col-md-6 text-center text-md-end">
                    <div class="d-flex justify-content-center justify-content-md-end gap-3">
                        <a href="about.html" class="text-decoration-none">Ποιοι είμαστε</a>
                        <a href="contact.html" class="text-decoration-none">Επικοινωνία</a>
                        <a href="privacy.html" class="text-decoration-none">Πολιτική Απορρήτου</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Charts -->
    <script>
        // Submissions by Year Chart
        const submissionsByYearCtx = document.getElementById('submissionsByYearChart').getContext('2d');
        new Chart(submissionsByYearCtx, {
            type: 'bar',
            data: {
                labels: [<?php 
                    mysqli_data_seek($submissions_by_year, 0);
                    while ($row = mysqli_fetch_assoc($submissions_by_year)) {
                        echo "'" . $row['year'] . "',";
                    }
                ?>],
                datasets: [{
                    label: 'Υποβολές',
                    data: [<?php 
                        mysqli_data_seek($submissions_by_year, 0);
                        while ($row = mysqli_fetch_assoc($submissions_by_year)) {
                            echo $row['count'] . ",";
                        }
                    ?>],
                    backgroundColor: '#ED9635'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Submissions by Party Chart
        const submissionsByPartyCtx = document.getElementById('submissionsByPartyChart').getContext('2d');
        new Chart(submissionsByPartyCtx, {
            type: 'pie',
            data: {
                labels: [<?php 
                    mysqli_data_seek($submissions_by_party, 0);
                    while ($row = mysqli_fetch_assoc($submissions_by_party)) {
                        echo "'" . $row['party_name'] . "',";
                    }
                ?>],
                datasets: [{
                    data: [<?php 
                        mysqli_data_seek($submissions_by_party, 0);
                        while ($row = mysqli_fetch_assoc($submissions_by_party)) {
                            echo $row['count'] . ",";
                        }
                    ?>],
                    backgroundColor: [
                        '#ED9635',
                        '#28a745',
                        '#dc3545',
                        '#ffc107',
                        '#17a2b8',
                        '#6c757d'
                    ]
                }]
            },
            options: {
                responsive: true
            }
        });
    </script>
</body>
</html>