<!-- filepath: c:\xampp\htdocs\webengineering_uni\webengineering_uni\modules\admin_module\generate_reports.php -->
<?php
$conn = include '../../config/db_connection.php';
session_start();

// Fetch total declarations
$stmt = $conn->query("SELECT COUNT(*) AS total FROM declarations WHERE status = 'Approved'");
$total_declarations = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Fetch declarations by year
$declarations_by_year = $conn->query("
    SELECT sp.year, COUNT(d.id) as count 
    FROM declarations d 
    JOIN submission_periods sp ON d.submission_period_id = sp.id 
    WHERE d.status = 'Approved'
    GROUP BY sp.year 
    ORDER BY sp.year DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch declarations by party
$declarations_by_party = $conn->query("
    SELECT p.name as party_name, COUNT(d.id) as count 
    FROM declarations d 
    JOIN personal_data pd ON d.id = pd.declaration_id
    JOIN parties p ON pd.party_id = p.id 
    WHERE d.status = 'Approved'
    GROUP BY p.name 
    ORDER BY count DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch people without declarations
$people_without_declarations = $conn->query("
    SELECT pd.full_name, p.name as party_name
    FROM personal_data pd
    JOIN parties p ON pd.party_id = p.id
    LEFT JOIN declarations d ON pd.declaration_id = d.id AND d.status = 'Approved'
    WHERE d.id IS NULL
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="el">
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
            
            <!-- Mobile Toggle Button -->
            <button class="navbar-toggler border-0 d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Desktop Menu -->
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
                    <li class="nav-item">
                        <div class="dropdown">
                            <button class="lang-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-translate"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="?lang=en"><span class="fi fi-gb"></span> English</a></li>
                                <li><a class="dropdown-item" href="?lang=el"><span class="fi fi-gr"></span> Ελληνικά</a></li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <div class="dropdown">
                            <button class="profile-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="../profile_module/profile.php">
                                        <i class="bi bi-person"></i> Το προφίλ μου
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="../favorites_module/favorites.php">
                                        <i class="bi bi-heart"></i> Αγαπημένα
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="../admin_module/dashboard.php">
                                        <i class="bi bi-speedometer2"></i> Admin Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="../login_module/logout.php">
                                        <i class="bi bi-box-arrow-right"></i> Αποσύνδεση
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Mobile Menu (Offcanvas) -->
            <div class="offcanvas offcanvas-end d-lg-none" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
                <div class="offcanvas-header border-bottom">
                    <h5 class="offcanvas-title" id="mobileMenuLabel">Μενού</h5>
                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 mb-2" href="../../index.php">
                                <i class="bi bi-house"></i> Αρχική
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 mb-2" href="../search_module/search.php">
                                <i class="bi bi-search"></i> Αναζήτηση
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 mb-2" href="../search_module/statistics.php">
                                <i class="bi bi-graph-up"></i> Στατιστικά
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 mb-3" href="../submit_module/declaration-form.php">
                                <i class="bi bi-file-earmark-text"></i> Υποβολή
                            </a>
                        </li>
                        <li class="nav-item border-top pt-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-translate"></i>
                                <span class="fw-medium">Γλώσσα</span>
                            </div>
                            <div class="d-flex flex-column gap-2">
                                <a href="?lang=en" class="nav-link py-2">
                                    <span class="fi fi-gb"></span> English
                                </a>
                                <a href="?lang=el" class="nav-link py-2">
                                    <span class="fi fi-gr"></span> Ελληνικά
                                </a>
                            </div>
                        </li>
                        <li class="nav-item border-top pt-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-person-circle"></i>
                                <span class="fw-medium">Λογαριασμός</span>
                            </div>
                            <div class="d-flex flex-column gap-2">
                                <a href="../profile_module/profile.php" class="nav-link py-2">
                                    <i class="bi bi-person"></i> Το προφίλ μου
                                </a>
                                <a href="../favorites_module/favorites.php" class="nav-link py-2">
                                    <i class="bi bi-heart"></i> Αγαπημένα
                                </a>
                                <a href="../admin_module/dashboard.php" class="nav-link py-2">
                                    <i class="bi bi-speedometer2"></i> Admin Dashboard
                                </a>
                                <a href="../login_module/logout.php" class="nav-link py-2">
                                    <i class="bi bi-box-arrow-right"></i> Αποσύνδεση
                                </a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <div class="d-flex align-items-center mb-4">
            <a href="dashboard.php" class="btn btn-outline-primary me-3">
                <i class="fas fa-arrow-left"></i> Επιστροφή στο Dashboard
            </a>
            <h2>Δημιουργία Αναφορών</h2>
        </div>
        
        <!-- Reports Form -->
        <div class="card">
            <div class="card-body">
                <!-- Quick Stats -->
                <div class="row g-4 mb-5">
                    <div class="col-md-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-file-text feature-icon mb-3" style="font-size: 2rem; color: #ED9635;"></i>
                                <h5 class="card-title">Συνολικές Δηλώσεις</h5>
                                <h2 class="mb-0"><?= $total_declarations ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-calendar-check feature-icon mb-3" style="font-size: 2rem; color: #28a745;"></i>
                                <h5 class="card-title">Ενεργές Περιόδους</h5>
                                <h2 class="mb-0"><?= count($declarations_by_year) ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-building feature-icon mb-3" style="font-size: 2rem; color: #dc3545;"></i>
                                <h5 class="card-title">Κόμματα με Δηλώσεις</h5>
                                <h2 class="mb-0"><?= count($declarations_by_party) ?></h2>
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
                                    <?php foreach ($people_without_declarations as $person) { ?>
                                        <tr>
                                            <td><?= htmlspecialchars($person['full_name']) ?></td>
                                            <td><?= htmlspecialchars($person['party_name']) ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer mt-auto py-3 bg-light">
        <div class="container text-center">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <img src="../../assets/images/logo.png" alt="Logo" height="40">
                </div>
                <div class="col-md-4">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#aboutModal" class="text-decoration-none text-dark">About Us</a>
                </div>
                <div class="col-md-4">
                    <span class="text-muted">&copy; 2025 All rights reserved.</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Charts -->
    <script>
        // Declarations by Year Chart
        const declarationsByYearCtx = document.getElementById('declarationsByYearChart').getContext('2d');
        new Chart(declarationsByYearCtx, {
            type: 'bar',
            data: {
                labels: [<?php 
                    foreach ($declarations_by_year as $row) {
                        echo "'" . $row['year'] . "',";
                    }
                ?>],
                datasets: [{
                    label: 'Δηλώσεις',
                    data: [<?php 
                        foreach ($declarations_by_year as $row) {
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
                    foreach ($declarations_by_party as $row) {
                        echo "'" . addslashes($row['party_name']) . "',";
                    }
                ?>],
                datasets: [{
                    data: [<?php 
                        foreach ($declarations_by_party as $row) {
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