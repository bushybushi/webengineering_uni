<?php
// Database connection
$pdo = require_once '../../config/db_connection.php';

// Get declaration ID from URL
$declaration_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch personal data
$stmt = $pdo->prepare("
    SELECT pd.*, p.name as party_name 
    FROM personal_data pd 
    LEFT JOIN parties p ON pd.party_id = p.id 
    WHERE pd.declaration_id = ?
");
$stmt->execute([$declaration_id]);
$personal_data = $stmt->fetch();

// Fetch properties
$stmt = $pdo->prepare("SELECT * FROM properties WHERE declaration_id = ?");
$stmt->execute([$declaration_id]);
$properties = $stmt->fetchAll();

// Fetch vehicles
$stmt = $pdo->prepare("SELECT * FROM vehicles WHERE declaration_id = ?");
$stmt->execute([$declaration_id]);
$vehicles = $stmt->fetchAll();

// Fetch liquid assets
$stmt = $pdo->prepare("SELECT * FROM liquid_assets WHERE declaration_id = ?");
$stmt->execute([$declaration_id]);
$liquid_assets = $stmt->fetchAll();

// Fetch deposits
$stmt = $pdo->prepare("SELECT * FROM deposits WHERE declaration_id = ?");
$stmt->execute([$declaration_id]);
$deposits = $stmt->fetchAll();

// Fetch insurance
$stmt = $pdo->prepare("SELECT * FROM insurance WHERE declaration_id = ?");
$stmt->execute([$declaration_id]);
$insurance = $stmt->fetchAll();

// Fetch debts
$stmt = $pdo->prepare("SELECT * FROM debts WHERE declaration_id = ?");
$stmt->execute([$declaration_id]);
$debts = $stmt->fetchAll();

// Fetch business participations
$stmt = $pdo->prepare("SELECT * FROM bussiness WHERE declaration_id = ?");
$stmt->execute([$declaration_id]);
$business = $stmt->fetchAll();

// Fetch differences
$stmt = $pdo->prepare("SELECT * FROM differences WHERE declaration_id = ?");
$stmt->execute([$declaration_id]);
$differences = $stmt->fetch();

// Fetch previous incomes
$stmt = $pdo->prepare("SELECT * FROM previous_incomes WHERE declaration_id = ?");
$stmt->execute([$declaration_id]);
$previous_incomes = $stmt->fetch();

// If no declaration found, redirect to search page
if (!$personal_data) {
    header("Location: ../search_module/search.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Declaration - Asset Declaration System</title>
      <!-- Favicon -->
      <link rel="icon" type="image/png" href="../../assets/images/iconlogo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css"/>
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
                        <a class="nav-link" href="../../index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="../search_module/search.php">Search</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../search_module/statistics.php">Statistics</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="./declaration-form.php">Submit</a>
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
                                <li><a class="dropdown-item" href="../login_module/login.php"><i class="bi bi-box-arrow-in-right"></i> Login</a></li>
                                <li><a class="dropdown-item" href="../login_module/register.php"><i class="bi bi-person-plus"></i> Register</a></li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Add padding-top to account for fixed navbar -->
    <div class="pt-5">
        <!-- Main Content -->
        <main class="container my-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>View Declaration</h1>
                <div>
                    <a href="../search_module/search.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Declarations
                    </a>
                    <a href="./edit-declaration.php?id=<?php echo $declaration_id; ?>" class="btn btn-warning text-dark">
                        <i class="bi bi-pencil"></i> Edit Declaration
                    </a>
                </div>
            </div>

            <!-- Personal Information -->
            <div class="card feature-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">1. Προσωπικά Στοιχεία</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Ονοματεπώνυμο:</strong></p>
                            <p><?php echo htmlspecialchars($personal_data['full_name']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Ιδιοτήτα/Αξίωμα:</strong></p>
                            <p><?php echo htmlspecialchars($personal_data['office']); ?></p>
                        </div>
                        <div class="col-md-12">
                            <p class="mb-1"><strong>Διεύθυνση:</strong></p>
                            <p><?php echo htmlspecialchars($personal_data['address']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Ημερομηνία Γέννησης:</strong></p>
                            <p><?php echo htmlspecialchars($personal_data['dob']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Αριθμός Ταυτότητας:</strong></p>
                            <p><?php echo htmlspecialchars($personal_data['id_number']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Οικογενειακή Κατάσταση:</strong></p>
                            <p><?php echo htmlspecialchars($personal_data['marital_status']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Αριθμός Ανηλίκων Τεκνών:</strong></p>
                            <p><?php echo htmlspecialchars($personal_data['dependants']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Πολιτικό Κόμμα/Παράταξη:</strong></p>
                            <p><?php echo htmlspecialchars($personal_data['party_name'] ?? '-'); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Properties -->
            <div class="card feature-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">2. Ακίνητη Ιδιοκτησία</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($properties)): ?>
                        <p class="text-muted">Δεν έχουν δηλωθεί ακίνητα.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Είδος</th>
                                        <th>Τοποθεσία</th>
                                        <th>Εκταση (m²)</th>
                                        <th>Τοπογραφικά Στοιχεία</th>
                                        <th>Εμπράγματα Δικαιώματα</th>
                                        <th>Τρόπος Απόκτησης</th>
                                        <th>Χρόνος Απόκτησης</th>
                                        <th>Αξία Απόκτησης (€)</th>
                                        <th>Τρέχουσα Αξία (€)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($properties as $property): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($property['type']); ?></td>
                                        <td><?php echo htmlspecialchars($property['location']); ?></td>
                                        <td><?php echo htmlspecialchars($property['area']); ?></td>
                                        <td><?php echo htmlspecialchars($property['topographic_data']); ?></td>
                                        <td><?php echo htmlspecialchars($property['rights_burdens']); ?></td>
                                        <td><?php echo htmlspecialchars($property['acquisition_mode']); ?></td>
                                        <td><?php echo htmlspecialchars($property['acquisition_date']); ?></td>
                                        <td><?php echo number_format($property['acquisition_value'], 2); ?></td>
                                        <td><?php echo number_format($property['current_value'], 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Vehicles -->
            <div class="card feature-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">3. Μηχανοκίνητα Μεταφορικά Μέσα</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($vehicles)): ?>
                        <p class="text-muted">Δεν έχουν δηλωθεί μηχανοκίνητα μέσα.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Μάρκα</th>
                                        <th>Τύπος</th>
                                        <th>Χρονολογία Παραγωγής</th>
                                        <th>Αξία (€)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($vehicles as $vehicle): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($vehicle['brand']); ?></td>
                                        <td><?php echo htmlspecialchars($vehicle['type']); ?></td>
                                        <td><?php echo htmlspecialchars($vehicle['manu_year']); ?></td>
                                        <td><?php echo number_format($vehicle['value'], 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Liquid Assets -->
            <div class="card feature-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">4. Εισοδήματα και Περιουσιακά Στοιχεία σε Κινητές Αξίες και Τίτλους</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($liquid_assets)): ?>
                        <p class="text-muted">Δεν έχουν δηλωθεί κινητές αξίες.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Είδος</th>
                                        <th>Περιγραφή</th>
                                        <th>Αριθμός σε Κατοχή</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($liquid_assets as $asset): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($asset['type']); ?></td>
                                        <td><?php echo htmlspecialchars($asset['description']); ?></td>
                                        <td><?php echo htmlspecialchars($asset['amount']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Deposits -->
            <div class="card feature-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">5. Καταθέσεις σε Τράπεζες</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($deposits)): ?>
                        <p class="text-muted">Δεν έχουν δηλωθεί καταθέσεις.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Όνομα Τράπεζας</th>
                                        <th>Ποσό Κατάθεσης (€)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($deposits as $deposit): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($deposit['bank_name']); ?></td>
                                        <td><?php echo number_format($deposit['amount'], 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Insurance -->
            <div class="card feature-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">6. Ασφαλιστικά Συμβόλαια</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($insurance)): ?>
                        <p class="text-muted">Δεν έχουν δηλωθεί ασφαλιστικά συμβόλαια.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Όνομα Ασφαλιστικής Εταιρείας</th>
                                        <th>Αριθμός Συμβολαίου</th>
                                        <th>Εισοδήματα (€)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($insurance as $ins): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($ins['insurance_name']); ?></td>
                                        <td><?php echo htmlspecialchars($ins['contract_num']); ?></td>
                                        <td><?php echo number_format($ins['earnings'], 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Debts -->
            <div class="card feature-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">7. Χρέη</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($debts)): ?>
                        <p class="text-muted">Δεν έχουν δηλωθεί χρέη.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Όνομα Πιστωτή</th>
                                        <th>Είδος Χρέους</th>
                                        <th>Ποσό Χρέους (€)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($debts as $debt): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($debt['creditor_name']); ?></td>
                                        <td><?php echo htmlspecialchars($debt['type']); ?></td>
                                        <td><?php echo number_format($debt['amount'], 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Business Participations -->
            <div class="card feature-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">8. Συμμετοχές σε Επιχειρήσεις</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($business)): ?>
                        <p class="text-muted">Δεν έχουν δηλωθεί συμμετοχές σε επιχειρήσεις.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Όνομα Επιχειρήσης</th>
                                        <th>Είδος Επιχειρήσης</th>
                                        <th>Είδος Συμμετοχής</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($business as $biz): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($biz['business_name']); ?></td>
                                        <td><?php echo htmlspecialchars($biz['business_type']); ?></td>
                                        <td><?php echo htmlspecialchars($biz['participation_type']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Differences -->
            <?php if ($differences): ?>
            <div class="card feature-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">9. Διαφορές στα Περιουσιακά Στοιχεία</h5>
                </div>
                <div class="card-body">
                    <p><?php echo nl2br(htmlspecialchars($differences['content'])); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Previous Incomes -->
            <?php if ($previous_incomes): ?>
            <div class="card feature-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">10. Εισοδήματα Προηγούμενων Ετών</h5>
                </div>
                <div class="card-body">
                    <?php echo $previous_incomes['html_content']; ?>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Footer -->
    <footer class="bg-light py-4 mt-auto">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-12 col-md-6 text-center text-md-start mb-3 mb-md-0">
                    <p class="mb-0">&copy; 2025 Asset Declaration System. All rights reserved.</p>
                </div>
                <div class="col-12 col-md-6 text-center text-md-end">
                    <div class="d-flex justify-content-center justify-content-md-end gap-3">
                        <a href="about.php" class="text-decoration-none">About</a>
                        <a href="contact.php" class="text-decoration-none">Contact</a>
                        <a href="privacy.php" class="text-decoration-none">Privacy Policy</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
