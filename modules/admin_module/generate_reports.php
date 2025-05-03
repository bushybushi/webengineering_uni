<!-- filepath: c:\xampp\htdocs\webengineering_uni\webengineering_uni\modules\admin_module\generate_reports.php -->
<?php
$conn = include '../../config/db_connection.php';
session_start();

// Fetch statistics
$stmt = $conn->query("SELECT COUNT(*) AS total FROM declarations");
$total_declarations = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $conn->query("SELECT COUNT(*) AS total FROM declarations WHERE status = 'Approved'");
$approved_declarations = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $conn->query("SELECT COUNT(*) AS total FROM declarations WHERE status = 'Rejected'");
$rejected_declarations = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $conn->query("SELECT COUNT(*) AS total FROM declarations WHERE status = 'Pending'");
$pending_declarations = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Fetch declarations by year
$declarations_by_year = $conn->query("
    SELECT sp.year, COUNT(d.id) as count 
    FROM declarations d 
    JOIN submission_periods sp ON d.submission_period_id = sp.id 
    GROUP BY sp.year 
    ORDER BY sp.year DESC
");

// Fetch declarations by party
$declarations_by_party = $conn->query("
    SELECT p.name as party_name, COUNT(d.id) as count 
    FROM declarations d 
    JOIN personal_data pd ON d.id = pd.declaration_id
    JOIN parties p ON pd.party_id = p.id 
    GROUP BY p.name 
    ORDER BY count DESC
");

// Fetch people without declarations
$people_without_declarations = $conn->query("
    SELECT pd.full_name, p.name as party_name
    FROM personal_data pd
    JOIN parties p ON pd.party_id = p.id
    LEFT JOIN declarations d ON pd.declaration_id = d.id
    WHERE d.id IS NULL
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
                        <h5 class="card-title">Συνολικές Δηλώσεις</h5>
                        <h2 class="mb-0"><?= $total_declarations ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-check-circle feature-icon mb-3" style="font-size: 2rem; color: #28a745;"></i>
                        <h5 class="card-title">Εγκεκριμένες</h5>
                        <h2 class="mb-0"><?= $approved_declarations ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-x-circle feature-icon mb-3" style="font-size: 2rem; color: #dc3545;"></i>
                        <h5 class="card-title">Απορριφθείσες</h5>
                        <h2 class="mb-0"><?= $rejected_declarations ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-clock feature-icon mb-3" style="font-size: 2rem; color: #ffc107;"></i>
                        <h5 class="card-title">Σε Εκκρεμότητα</h5>
                        <h2 class="mb-0"><?= $pending_declarations ?></h2>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Charts -->
        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Δηλώσεις ανά Έτος</h5>
                        <canvas id="declarationsByYearChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Δηλώσεις ανά Κόμμα</h5>
                        <canvas id="declarationsByPartyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- People Without Declarations -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Άτομα χωρίς Δηλώσεις</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Όνομα</th>
                                <th>Κόμμα</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($person = $people_without_declarations->fetch(PDO::FETCH_ASSOC)) { ?>
                                <tr>
                                    <td><?= $person['full_name'] ?></td>
                                    <td><?= $person['party_name'] ?></td>
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
        // Declarations by Year Chart
        const declarationsByYearCtx = document.getElementById('declarationsByYearChart').getContext('2d');
        new Chart(declarationsByYearCtx, {
            type: 'bar',
            data: {
                labels: [<?php 
                    while ($row = $declarations_by_year->fetch(PDO::FETCH_ASSOC)) {
                        echo "'" . $row['year'] . "',";
                    }
                ?>],
                datasets: [{
                    label: 'Δηλώσεις',
                    data: [<?php 
                        $declarations_by_year->execute();
                        while ($row = $declarations_by_year->fetch(PDO::FETCH_ASSOC)) {
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

        // Declarations by Party Chart
        const declarationsByPartyCtx = document.getElementById('declarationsByPartyChart').getContext('2d');
        new Chart(declarationsByPartyCtx, {
            type: 'pie',
            data: {
                labels: [<?php 
                    while ($row = $declarations_by_party->fetch(PDO::FETCH_ASSOC)) {
                        echo "'" . $row['party_name'] . "',";
                    }
                ?>],
                datasets: [{
                    data: [<?php 
                        $declarations_by_party->execute();
                        while ($row = $declarations_by_party->fetch(PDO::FETCH_ASSOC)) {
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