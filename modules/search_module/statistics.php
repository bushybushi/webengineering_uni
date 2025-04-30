<?php
require_once '../../config/db_connection.php';

// Get database connection
$conn = require '../../config/db_connection.php';

// Calculate statistics
try {
    // Total declarations (active officials)
    $stmt = $conn->query("SELECT COUNT(*) as total FROM declarations");
    $totalDeclarations = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total unique political affiliations
    $stmt = $conn->query("SELECT COUNT(DISTINCT p.name) as total FROM parties p 
                         INNER JOIN personal_data pd ON pd.party_id = p.id 
                         WHERE p.name IS NOT NULL");
    $totalParties = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Declarations by political party
    $stmt = $conn->query("SELECT p.name as political_affiliation, COUNT(*) as count 
                         FROM declarations d 
                         INNER JOIN personal_data pd ON d.id = pd.declaration_id 
                         INNER JOIN parties p ON pd.party_id = p.id 
                         WHERE p.name IS NOT NULL 
                         GROUP BY p.name 
                         ORDER BY count DESC");
    $partyData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format party data for chart
    $partyLabels = array_map(function($item) { return $item['political_affiliation']; }, $partyData);
    $partyCounts = array_map(function($item) { return $item['count']; }, $partyData);

    // Calculate submission rate (assuming all records are submissions)
    $submissionRate = 100; // Since we're counting from submitted records

    // Asset Value Distribution
    $stmt = $conn->query("SELECT 
        CASE 
            WHEN CAST(REPLACE(REPLACE(amount, '€', ''), ',', '') AS DECIMAL(10,2)) < 10000 THEN 'Λιγότερο από €10,000'
            WHEN CAST(REPLACE(REPLACE(amount, '€', ''), ',', '') AS DECIMAL(10,2)) < 50000 THEN '€10,000 - €50,000'
            WHEN CAST(REPLACE(REPLACE(amount, '€', ''), ',', '') AS DECIMAL(10,2)) < 100000 THEN '€50,000 - €100,000'
            ELSE 'Περισσότερο από €100,000'
        END as value_range,
        COUNT(*) as count
        FROM liquid_assets 
        WHERE amount IS NOT NULL 
        GROUP BY value_range
        ORDER BY 
            CASE value_range
                WHEN 'Under €10,000' THEN 1
                WHEN '€10,000 - €50,000' THEN 2
                WHEN '€50,000 - €100,000' THEN 3
                ELSE 4
            END");
    $valueDistribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $valueLabels = array_map(function($item) { return $item['value_range']; }, $valueDistribution);
    $valueCounts = array_map(function($item) { return $item['count']; }, $valueDistribution);

} catch(PDOException $e) {
    // Handle any database errors
    error_log("Database Error: " . $e->getMessage());
    // Set default values in case of error
    $totalDeclarations = 0;
    $totalParties = 0;
    $partyLabels = [];
    $partyCounts = [];
    $submissionRate = 0;
    $valueLabels = [];
    $valueCounts = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics & Analysis - ΠΟΘΕΝ ΕΣΧΕΣ</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css"/>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom CSS -->
    <link href="../../assets/css/style.css" rel="stylesheet">
    <style>
        .lang-btn {
            width: 32px;
            height: 32px;
            padding: 0;
            border-radius: 50%;
            border: none;
            background: #e9ecef;
            color: #000000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .lang-btn:hover {
            background: #dee2e6;
            transform: scale(1.05);
        }
        .lang-btn.active {
            background: #000000;
            color: white;
        }
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 2rem;
        }
        .stats-summary {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-item {
            text-align: center;
            padding: 1rem;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #ED9635;
        }
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        /* Custom button styles */
        .btn-compare {
            background-color: #ED9635;
            border-color: #ED9635;
            color: white;
        }
        .btn-compare:hover {
            background-color: #d67b1f;
            border-color: #d67b1f;
            color: white;
        }
        .btn-compare:disabled {
            background-color: #ED9635;
            border-color: #ED9635;
            color: white;
            opacity: 1;
        }
        .search-container {
            position: relative;
        }
        
        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }
        
        .search-result-item {
            padding: 8px 12px;
            cursor: pointer;
        }
        
        .search-result-item:hover {
            background-color: #f8f9fa;
        }
        
        .selected-politicians {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .selected-politician {
            background-color: #ED9635;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .selected-politician button {
            background: none;
            border: none;
            color: white;
            padding: 0;
            font-size: 1.2rem;
            cursor: pointer;
        }
        
        .selected-politician button:hover {
            color: #f8f9fa;
        }
    </style>
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
                        <a class="nav-link" href="./search.php">Αναζήτηση</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="./statistics.php">Στατιστικά</a>
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
                                <li><a class="dropdown-item" href="../login_module/login.php"><i class="bi bi-box-arrow-in-right"></i> Σύνδεση</a></li>
                                <li><a class="dropdown-item" href="../login_module/register.php"><i class="bi bi-person-plus"></i> Εγγραφή</a></li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Mobile Menu (Offcanvas) -->
            <div class="offcanvas offcanvas-end d-lg-none" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
                <div class="offcanvas-header border-bottom">
                    <h5 class="offcanvas-title" id="mobileMenuLabel">Menu</h5>
                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 mb-2" href="../../index.php">
                                <i class="bi bi-house"></i> Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 mb-2" href="./search.php">
                                <i class="bi bi-search"></i> Search
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active d-flex align-items-center gap-2 mb-2" href="./statistics.php">
                                <i class="bi bi-graph-up"></i> Statistics
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 mb-3" href="../submit_module/declaration-form.php">
                                <i class="bi bi-file-earmark-text"></i> Submit
                            </a>
                        </li>
                        <li class="nav-item border-top pt-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-translate"></i>
                                <span class="fw-medium">Language</span>
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
                                <span class="fw-medium">Account</span>
                            </div>
                            <div class="d-flex flex-column gap-2">
                                <a href="../login_module/login.html" class="nav-link py-2">
                                    <i class="bi bi-box-arrow-in-right me-2"></i> Login
                                </a>
                                <a href="../login_module/register.php" class="nav-link py-2">
                                    <i class="bi bi-person-plus me-2"></i> Register
                                </a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Add padding-top to account for fixed navbar -->
    <div class="pt-5">
        <!-- Main Content -->
        <main class="container my-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Στατιστικά & Ανάλυση</h1>
            </div>

            <!-- Summary Stats -->
            <div class="stats-summary">
                <div class="row">
                    <div class="col-md-3">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $submissionRate; ?>%</div>
                            <div class="stat-label">Ποσοστό Υποβολής</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $totalDeclarations; ?></div>
                            <div class="stat-label">Σύνολο Δηλώσεων</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $totalDeclarations; ?></div>
                            <div class="stat-label">Ενεργοί</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $totalParties; ?></div>
                            <div class="stat-label">Πολιτικά Κόμματα</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="row">
                <!-- Declarations by Party -->
                <div class="col-lg-6">
                    <div class="card feature-card">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Δηλώσεις ανά Πολιτικό Κόμμα</h5>
                            <div class="chart-container">
                                <canvas id="partyChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Asset Value Distribution -->
                <div class="col-lg-6">
                    <div class="card feature-card">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Κατανομή Αξίας Περιουσίας</h5>
                            <div class="chart-container">
                                <canvas id="valueDistributionChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Comparison Section -->
        <div class="container mb-5">
            <h2 class="mb-4">Σύγκριση Δηλώσεων</h2>
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Πρώτο Πρόσωπο</h5>
                            <select class="form-select mb-3" id="person1">
                                <option value="">Επιλέξτε πρόσωπο...</option>
                                <?php
                                $stmt = $conn->query("SELECT d.id, pd.full_name as name FROM declarations d 
                                                     INNER JOIN personal_data pd ON d.id = pd.declaration_id 
                                                     ORDER BY pd.full_name");
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='{$row['id']}'>{$row['name']}</option>";
                                }
                                ?>
                            </select>
                            <div id="declaration1"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Δεύτερο Πρόσωπο</h5>
                            <select class="form-select mb-3" id="person2">
                                <option value="">Επιλέξτε πρόσωπο...</option>
                                <?php
                                $stmt = $conn->query("SELECT d.id, pd.full_name as name FROM declarations d 
                                                     INNER JOIN personal_data pd ON d.id = pd.declaration_id 
                                                     ORDER BY pd.full_name");
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='{$row['id']}'>{$row['name']}</option>";
                                }
                                ?>
                            </select>
                            <div id="declaration2"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12 text-center">
                    <button id="compareBtn" class="btn btn-compare me-2" disabled>Σύγκριση Δηλώσεων</button>
                    <button id="clearBtn" class="btn btn-secondary">Καθαρισμός</button>
                </div>
            </div>
        </div>

        <!-- Financial Comparison Chart Section -->
        <div class="container mb-5">
            <h2 class="mb-4">Οικονομική Σύγκριση</h2>
            
            <!-- Search and Selection Area -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="search-container">
                                <input type="text" class="form-control" id="politicianSearch" placeholder="Αναζήτηση πολιτικών...">
                                <div id="searchResults" class="search-results"></div>
                            </div>
                            <div id="selectedPoliticians" class="selected-politicians mt-3">
                                <!-- Selected politicians will appear here -->
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <button id="clearSelection" class="btn btn-secondary">Καθαρισμός Όλων</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart Area -->
            <div class="card">
                <div class="card-body">
                    <canvas id="financialChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-light py-4 mt-auto">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-12 col-md-6 text-center text-md-start mb-3 mb-md-0">
                    <p class="mb-0">&copy; 2025 Πόθεν Εσχες © all rights reserved.</p>
                </div>
                <div class="col-12 col-md-6 text-center text-md-end">
                    <div class="d-flex justify-content-center justify-content-md-end gap-3">
                        <a href="about.php" class="text-decoration-none">Ποιοι είμαστε</a>
                        <a href="contact.php" class="text-decoration-none">Επικοινωνία</a>
                        <a href="privacy.php" class="text-decoration-none">Πολιτική Απορρήτου</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../../assets/js/main.js"></script>
    <script>
        // Party Chart
        const partyCtx = document.getElementById('partyChart').getContext('2d');
        new Chart(partyCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($partyLabels); ?>,
                datasets: [{
                    label: 'Αριθμός Δηλώσεων',
                    data: <?php echo json_encode($partyCounts); ?>,
                    backgroundColor: [
                        '#ED9635',
                        '#d67b1f',
                        '#f0a85a',
                        '#ffc107',
                        '#6c757d',
                        '#495057',
                        '#343a40'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Δηλώσεις ανά Πολιτικό Κόμμα'
                    },
                    legend: {
                        labels: {
                            font: {
                                size: 12
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Αριθμός Δηλώσεων'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Πολιτικά Κόμματα'
                        }
                    }
                }
            }
        });

        // Value Distribution Chart
        const valueCtx = document.getElementById('valueDistributionChart').getContext('2d');
        new Chart(valueCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($valueLabels); ?>,
                datasets: [{
                    label: 'Αριθμός Περιουσιακών Στοιχείων',
                    data: <?php echo json_encode($valueCounts); ?>,
                    backgroundColor: '#ED9635'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Κατανομή Αξίας Περιουσίας'
                    },
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Αριθμός Περιουσιακών Στοιχείων'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Εύρος Αξίας'
                        }
                    }
                }
            }
        });

        const person1Select = document.getElementById('person1');
        const person2Select = document.getElementById('person2');
        const compareBtn = document.getElementById('compareBtn');
        const clearBtn = document.getElementById('clearBtn');

        // Function to check if both people are selected
        function checkSelections() {
            compareBtn.disabled = !(person1Select.value && person2Select.value);
        }

        // Add event listeners to both selects
        person1Select.addEventListener('change', checkSelections);
        person2Select.addEventListener('change', checkSelections);

        // Add click event to compare button
        compareBtn.addEventListener('click', function() {
            if (person1Select.value && person2Select.value) {
                // Load first declaration
                fetch(`../submit_module/view-declaration.php?id=${person1Select.value}`)
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('declaration1').innerHTML = html;
                    });

                // Load second declaration
                fetch(`../submit_module/view-declaration.php?id=${person2Select.value}`)
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('declaration2').innerHTML = html;
                        // Hide compare button after loading declarations
                        compareBtn.style.display = 'none';
                    });
            }
        });

        // Add click event to clear button
        clearBtn.addEventListener('click', function() {
            // Reset selects
            person1Select.value = '';
            person2Select.value = '';
            
            // Clear declarations
            document.getElementById('declaration1').innerHTML = '';
            document.getElementById('declaration2').innerHTML = '';
            
            // Show compare button again
            compareBtn.style.display = 'inline-block';
            compareBtn.disabled = true;
        });

        // Financial Chart Code
        const ctx = document.getElementById('financialChart').getContext('2d');
        let financialChart = null;
        let selectedPoliticians = new Map();

        // Function to fetch financial data
        async function fetchFinancialData(politicianIds) {
            try {
                const response = await fetch('./get_financial_data.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ ids: politicianIds })
                });
                
                if (!response.ok) {
                    throw new Error(`Σφάλμα HTTP! κατάσταση: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('Ληφθέντα δεδομένα:', data);
                
                if (data.error) {
                    console.error('Σφάλμα διακομιστή:', data.error);
                    return null;
                }
                
                return data;
            } catch (error) {
                console.error('Σφάλμα κατά την ανάκτηση οικονομικών δεδομένων:', error);
                return null;
            }
        }

        // Function to update chart
        function updateChart(data) {
            console.log('Ενημέρωση γραφήματος με δεδομένα:', data);
            
            if (!data || data.length === 0) {
                if (financialChart) {
                    financialChart.destroy();
                }
                
                financialChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: [],
                        datasets: [
                            {
                                label: 'Αξία Ακινήτων (€)',
                                data: [],
                                backgroundColor: 'rgba(237, 150, 53, 0.8)',
                                borderColor: 'rgba(237, 150, 53, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Αξία Μετοχών (€)',
                                data: [],
                                backgroundColor: 'rgba(214, 123, 31, 0.8)',
                                borderColor: 'rgba(214, 123, 31, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Αξία Καταθέσεων (€)',
                                data: [],
                                backgroundColor: 'rgba(240, 168, 90, 0.8)',
                                borderColor: 'rgba(240, 168, 90, 1)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            x: {
                                stacked: false,
                                title: {
                                    display: true,
                                    text: 'Πολιτικοί'
                                }
                            },
                            y: {
                                stacked: false,
                                title: {
                                    display: true,
                                    text: 'Αξία (€)'
                                },
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            title: {
                                display: true,
                                text: 'Οικονομική Σύγκριση'
                            }
                        }
                    }
                });
                return;
            }

            if (financialChart) {
                financialChart.destroy();
            }

            const labels = data.map(item => item.name);
            const realEstateData = data.map(item => parseFloat(item.real_estate) || 0);
            const stocksData = data.map(item => parseFloat(item.stocks) || 0);
            const depositsData = data.map(item => parseFloat(item.deposits) || 0);

            financialChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Αξία Ακινήτων (€)',
                            data: realEstateData,
                            backgroundColor: 'rgba(237, 150, 53, 0.8)',
                            borderColor: 'rgba(237, 150, 53, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Αξία Μετοχών (€)',
                            data: stocksData,
                            backgroundColor: 'rgba(214, 123, 31, 0.8)',
                            borderColor: 'rgba(214, 123, 31, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Αξία Καταθέσεων (€)',
                            data: depositsData,
                            backgroundColor: 'rgba(240, 168, 90, 0.8)',
                            borderColor: 'rgba(240, 168, 90, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            stacked: false,
                            title: {
                                display: true,
                                text: 'Πολιτικοί'
                            }
                        },
                        y: {
                            stacked: false,
                            title: {
                                display: true,
                                text: 'Αξία (€)'
                            },
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Οικονομική Σύγκριση'
                        }
                    }
                }
            });
        }

        // Search functionality
        const searchInput = document.getElementById('politicianSearch');
        const searchResults = document.getElementById('searchResults');
        const selectedPoliticiansContainer = document.getElementById('selectedPoliticians');

        searchInput.addEventListener('input', async function() {
            const query = this.value.trim();
            if (query.length < 2) {
                searchResults.style.display = 'none';
                return;
            }

            try {
                const response = await fetch(`./search_politicians.php?q=${encodeURIComponent(query)}`);
                const data = await response.json();
                
                searchResults.innerHTML = '';
                data.forEach(politician => {
                    if (!selectedPoliticians.has(politician.id)) {
                        const div = document.createElement('div');
                        div.className = 'search-result-item';
                        div.textContent = politician.name;
                        div.addEventListener('click', () => addPolitician(politician));
                        searchResults.appendChild(div);
                    }
                });
                
                searchResults.style.display = data.length > 0 ? 'block' : 'none';
            } catch (error) {
                console.error('Error searching politicians:', error);
            }
        });

        function addPolitician(politician) {
            if (!selectedPoliticians.has(politician.id)) {
                selectedPoliticians.set(politician.id, politician);
                updateSelectedPoliticiansList();
                updateChartWithSelectedPoliticians();
            }
            searchInput.value = '';
            searchResults.style.display = 'none';
        }

        function removePolitician(id) {
            selectedPoliticians.delete(id);
            updateSelectedPoliticiansList();
            updateChartWithSelectedPoliticians();
        }

        function updateSelectedPoliticiansList() {
            selectedPoliticiansContainer.innerHTML = '';
            selectedPoliticians.forEach((politician, id) => {
                const div = document.createElement('div');
                div.className = 'selected-politician';
                div.innerHTML = `
                    ${politician.name}
                    <button onclick="removePolitician(${id})">&times;</button>
                `;
                selectedPoliticiansContainer.appendChild(div);
            });
        }

        async function updateChartWithSelectedPoliticians() {
            console.log('Selected politicians:', Array.from(selectedPoliticians.keys())); // Debug log
            const ids = Array.from(selectedPoliticians.keys());
            if (ids.length > 0) {
                const data = await fetchFinancialData(ids);
                console.log('Chart data:', data); // Debug log
                if (data) {
                    updateChart(data);
                }
            } else {
                updateChart([]);
            }
        }

        // Event listener for clear button
        document.getElementById('clearSelection').addEventListener('click', function() {
            selectedPoliticians.clear();
            updateSelectedPoliticiansList();
            updateChart([]);
        });

        // Close search results when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });

        // Initialize empty chart
        updateChart([]);
    </script>
</body>
</html> 
