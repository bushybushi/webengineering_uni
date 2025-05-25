<?php
// Start session to access user data and manage authentication state
session_start();
// Include database connection configuration
$pdo = require_once '../../config/db_connection.php';

// Get declaration ID from URL parameter, default to 0 if not set
$declaration_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch personal data and party information for the declaration
$stmt = $pdo->prepare("
    SELECT pd.*, p.name as party_name 
    FROM personal_data pd 
    LEFT JOIN parties p ON pd.party_id = p.id 
    WHERE pd.declaration_id = ?
");
$stmt->execute([$declaration_id]);
$personal_data = $stmt->fetch();

// Redirect to search page if no declaration is found
if (!$personal_data) {
    header("Location: ../search_module/search.php");
    exit();
}

// Check if the current user has favorited this declaration
$is_favorited = false;
if (isset($_SESSION['id'])) {
    $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND declaration_id = ?");
    $stmt->execute([$_SESSION['id'], $declaration_id]);
    $is_favorited = $stmt->rowCount() > 0;
}

// Check if the current user is following this politician
$is_following = false;
if (isset($_SESSION['id'])) {
    $stmt = $pdo->prepare("SELECT id FROM follows WHERE user_id = ? AND politician_id = ?");
    $stmt->execute([$_SESSION['id'], $personal_data['id']]);
    $is_following = $stmt->rowCount() > 0;
}

// Fetch all properties associated with this declaration
$stmt = $pdo->prepare("SELECT * FROM properties WHERE declaration_id = ?");
$stmt->execute([$declaration_id]);
$properties = $stmt->fetchAll();

// Fetch all vehicles associated with this declaration
$stmt = $pdo->prepare("SELECT * FROM vehicles WHERE declaration_id = ?");
$stmt->execute([$declaration_id]);
$vehicles = $stmt->fetchAll();

// Fetch all liquid assets associated with this declaration
$stmt = $pdo->prepare("SELECT * FROM liquid_assets WHERE declaration_id = ?");
$stmt->execute([$declaration_id]);
$liquid_assets = $stmt->fetchAll();

// Fetch all bank deposits associated with this declaration
$stmt = $pdo->prepare("SELECT * FROM deposits WHERE declaration_id = ?");
$stmt->execute([$declaration_id]);
$deposits = $stmt->fetchAll();

// Fetch all insurance policies associated with this declaration
$stmt = $pdo->prepare("SELECT * FROM insurance WHERE declaration_id = ?");
$stmt->execute([$declaration_id]);
$insurance = $stmt->fetchAll();

// Fetch all debts associated with this declaration
$stmt = $pdo->prepare("SELECT * FROM debts WHERE declaration_id = ?");
$stmt->execute([$declaration_id]);
$debts = $stmt->fetchAll();

// Fetch all business participations associated with this declaration
$stmt = $pdo->prepare("SELECT * FROM bussiness WHERE declaration_id = ?");
$stmt->execute([$declaration_id]);
$business = $stmt->fetchAll();

// Fetch differences in assets for this declaration
$stmt = $pdo->prepare("SELECT * FROM differences WHERE declaration_id = ?");
$stmt->execute([$declaration_id]);
$differences = $stmt->fetch();

// Fetch previous years' income information
$stmt = $pdo->prepare("SELECT * FROM previous_incomes WHERE declaration_id = ?");
$stmt->execute([$declaration_id]);
$previous_incomes = $stmt->fetch();

// Fetch other income sources
$stmt = $pdo->prepare("SELECT * FROM other_incomes WHERE declaration_id = ?");
$stmt->execute([$declaration_id]);
$other_incomes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags and character encoding -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Declaration - Asset Declaration System</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../../assets/images/iconlogo.png">
    
    <!-- External CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css"/>
    
    <!-- Custom CSS -->
    <link href="../../assets/css/style.css" rel="stylesheet">
    
    <!-- Custom styles for favorite and follow buttons -->
    <style>
        .favorite-btn, .follow-btn {
            transition: all 0.3s ease;
            min-width: 40px;
        }
        .favorite-btn.active {
            background-color: #dc3545;
            color: white;
        }
        .follow-btn.active {
            background-color: #198754;
            color: white;
        }
        @media (max-width: 576px) {
            .btn-group {
                width: 100%;
            }
            .btn-group .btn {
                flex: 1;
                margin: 2px;
            }
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
                        <a class="nav-link" href="../search_module/search.php">Αναζήτηση</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../search_module/statistics.php">Στατιστικά</a>
                    </li>
                    <li class="nav-item">
                        <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Politician')): ?>
                            <a class="nav-link" href="../submit_module/declaration-form.php">Υποβολή</a>
                        <?php endif; ?>
                    </li>
                    <li class="nav-item">
                        <div class="dropdown">
                            <button class="profile-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <?php if (isset($_SESSION['id'])): ?>
                                    <li>
                                        <a class="dropdown-item" href="../profile_module/profile.php">
                                            <i class="bi bi-person"></i> Το προφίλ μου
                                        </a>
                                    </li>
                                <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Politician')): ?>
                                    <li>
                                        <a class="dropdown-item" href="my-declarations.php">
                                            <i class="bi bi-file-earmark-text"></i> Οι Δηλώσεις μου
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    <li>
                                        <a class="dropdown-item" href="../submit_module/favorites.php">
                                            <i class="bi bi-heart"></i> Αγαπημένα
                                        </a>
                                    </li>
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
                                    <li>
                                        <a class="dropdown-item" href="../admin_module/dashboard.php">
                                            <i class="bi bi-speedometer2"></i> Admin Dashboard
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Public' || $_SESSION['role'] === 'Politician')): ?>
                                    <li>
                                        <a class="dropdown-item" href="../api_module/api_documentation.php">
                                            <i class="bi bi-code-square"></i> API Documentation
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    <li>
                                        <a class="dropdown-item" href="../login_module/logout.php">
                                            <i class="bi bi-box-arrow-right"></i> Αποσύνδεση
                                        </a>
                                    </li>
                                <?php else: ?>
                                    <li>
                                        <a class="dropdown-item" href="../login_module/login.php">
                                            <i class="bi bi-box-arrow-in-right"></i> Σύνδεση
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="../login_module/register.php">
                                            <i class="bi bi-person-plus"></i> Εγγραφή
                                        </a>
                                    </li>
                                <?php endif; ?>
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
                            <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Politician')): ?>
                                <a class="nav-link d-flex align-items-center gap-2 mb-3" href="../submit_module/declaration-form.php">
                                    <i class="bi bi-file-earmark-text"></i> Υποβολή
                                </a>
                            <?php endif; ?>
                        </li>
                        <li class="nav-item border-top pt-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-person-circle"></i>
                                <span class="fw-medium">Λογαριασμός</span>
                            </div>
                            <div class="d-flex flex-column gap-2">
                                <?php if (isset($_SESSION['id'])): ?>
                                    <a href="../profile_module/profile.php" class="nav-link py-2">
                                        <i class="bi bi-person"></i> Το προφίλ μου
                                    </a>
                                <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Politician')): ?>
                                        <a class="dropdown-item" href="my-declarations.php">
                                            <i class="bi bi-file-earmark-text"></i> Οι Δηλώσεις μου
                                        </a>
                                    <?php endif; ?>
                                        <a class="dropdown-item" href="../submit_module/favorites.php">
                                            <i class="bi bi-heart"></i> Αγαπημένα
                                        </a>
                                   
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
                                    <a href="../admin_module/dashboard.php" class="nav-link py-2">
                                        <i class="bi bi-speedometer2"></i> Admin Dashboard
                                    </a>
                                    <?php endif; ?>
                                    <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Public' || $_SESSION['role'] === 'Politician')): ?>
                                    <a href="../api_module/api_documentation.php" class="nav-link py-2">
                                        <i class="bi bi-code-square"></i> API Documentation
                                    </a>
                                    <?php endif; ?>
                                    <a href="../login_module/logout.php" class="nav-link py-2">
                                        <i class="bi bi-box-arrow-right"></i> Αποσύνδεση
                                    </a>
                                <?php else: ?>
                                    <a href="../login_module/login.php" class="nav-link py-2">
                                        <i class="bi bi-box-arrow-in-right me-2"></i> Σύνδεση
                                    </a>
                                    <a href="../login_module/register.php" class="nav-link py-2">
                                        <i class="bi bi-person-plus me-2"></i> Εγγραφή
                                    </a>
                                <?php endif; ?>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <div class="pt-5">
        <main class="container my-5">
            <!-- Header Section with Action Buttons -->
            <div class="row mb-5">
                <div class="col-12">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                        <h1 class="mb-3 mb-md-0">Προβολή Δήλωσης</h1>
                        <div class="btn-group flex-wrap">
                            <?php if (isset($_SESSION['id'])): ?>
                                <button class="btn btn-outline-danger favorite-btn <?php echo $is_favorited ? 'active' : ''; ?>" 
                                        data-declaration-id="<?php echo $declaration_id; ?>">
                                    <i class="fas fa-heart"></i>
                                    <span class="d-none d-sm-inline favorite-text"><?php echo $is_favorited ? 'Αφαιρέστε από Αγαπημένα' : 'Προσθήκη στα Αγαπημένα'; ?></span>
                                </button>
                                <button class="btn btn-outline-success follow-btn <?php echo $is_following ? 'active' : ''; ?>"
                                        data-politician-id="<?php echo $personal_data['id']; ?>">
                                    <i class="fas fa-user-plus"></i>
                                    <span class="d-none d-sm-inline follow-text"><?php echo $is_following ? 'Ακολουθείτε' : 'Ακολουθήστε'; ?></span>
                                </button>
                            <?php endif; ?>
                            <a href="../search_module/search.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> <span class="d-none d-sm-inline">Πίσω στις Δηλώσεις</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Personal Information Section -->
            <div class="card feature-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">1. Προσωπικά Στοιχεία</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Ονοματεπώνυμο:</strong></p>
                            <p><?php echo !empty($personal_data['full_name']) ? htmlspecialchars($personal_data['full_name']) : '-'; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Ιδιοτήτα/Αξίωμα:</strong></p>
                            <p><?php echo !empty($personal_data['office']) ? htmlspecialchars($personal_data['office']) : '-'; ?></p>
                        </div>
                        <div class="col-md-12">
                            <p class="mb-1"><strong>Διεύθυνση:</strong></p>
                            <p><?php echo !empty($personal_data['address']) ? htmlspecialchars($personal_data['address']) : '-'; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Ημερομηνία Γέννησης:</strong></p>
                            <p><?php echo !empty($personal_data['dob']) ? htmlspecialchars($personal_data['dob']) : '-'; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Αριθμός Ταυτότητας:</strong></p>
                            <p><?php echo !empty($personal_data['id_number']) ? htmlspecialchars($personal_data['id_number']) : '-'; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Οικογενειακή Κατάσταση:</strong></p>
                            <p><?php echo !empty($personal_data['marital_status']) ? htmlspecialchars($personal_data['marital_status']) : '-'; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Αριθμός Ανηλίκων Τεκνών:</strong></p>
                            <p><?php echo !empty($personal_data['dependants']) ? htmlspecialchars($personal_data['dependants']) : '-'; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Πολιτικό Κόμμα/Παράταξη:</strong></p>
                            <p><?php echo !empty($personal_data['party_name']) ? htmlspecialchars($personal_data['party_name']) : '-'; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Properties Section -->
            <div class="card feature-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">2. Ακίνητη Ιδιοκτησία (<?php echo count($properties); ?>)</h5>
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
                                        <td><?php echo !empty($property['type']) ? htmlspecialchars($property['type']) : '-'; ?></td>
                                        <td><?php echo !empty($property['location']) ? htmlspecialchars($property['location']) : '-'; ?></td>
                                        <td><?php echo !empty($property['area']) ? htmlspecialchars($property['area']) : '-'; ?></td>
                                        <td><?php echo !empty($property['topographic_data']) ? htmlspecialchars($property['topographic_data']) : '-'; ?></td>
                                        <td><?php echo !empty($property['rights_burdens']) ? htmlspecialchars($property['rights_burdens']) : '-'; ?></td>
                                        <td><?php echo !empty($property['acquisition_mode']) ? htmlspecialchars($property['acquisition_mode']) : '-'; ?></td>
                                        <td><?php echo !empty($property['acquisition_date']) ? htmlspecialchars($property['acquisition_date']) : '-'; ?></td>
                                        <td><?php echo !empty($property['acquisition_value']) ? number_format($property['acquisition_value'], 2) : '-'; ?></td>
                                        <td><?php echo !empty($property['current_value']) ? number_format($property['current_value'], 2) : '-'; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Vehicles Section -->
            <div class="card feature-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">3. Μηχανοκίνητα Μεταφορικά Μέσα (<?php echo count($vehicles); ?>)</h5>
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
                                        <td><?php echo !empty($vehicle['brand']) ? htmlspecialchars($vehicle['brand']) : '-'; ?></td>
                                        <td><?php echo !empty($vehicle['type']) ? htmlspecialchars($vehicle['type']) : '-'; ?></td>
                                        <td><?php echo !empty($vehicle['manu_year']) ? htmlspecialchars($vehicle['manu_year']) : '-'; ?></td>
                                        <td><?php echo !empty($vehicle['value']) ? number_format($vehicle['value'], 2) : '-'; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Liquid Assets Section -->
            <div class="card feature-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">4. Εισοδήματα και Περιουσιακά Στοιχεία σε Κινητές Αξίες και Τίτλους (<?php echo count($liquid_assets); ?>)</h5>
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
                                        <td><?php echo !empty($asset['type']) ? htmlspecialchars($asset['type']) : '-'; ?></td>
                                        <td><?php echo !empty($asset['description']) ? htmlspecialchars($asset['description']) : '-'; ?></td>
                                        <td><?php echo !empty($asset['amount']) ? htmlspecialchars($asset['amount']) : '-'; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Bank Deposits Section -->
            <div class="card feature-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">5. Καταθέσεις σε Τράπεζες (<?php echo count($deposits); ?>)</h5>
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
                                        <td><?php echo !empty($deposit['bank_name']) ? htmlspecialchars($deposit['bank_name']) : '-'; ?></td>
                                        <td><?php echo !empty($deposit['amount']) ? number_format($deposit['amount'], 2) : '-'; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Insurance Policies Section -->
            <div class="card feature-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">6. Ασφαλιστικά Συμβόλαια (<?php echo count($insurance); ?>)</h5>
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
                                        <td><?php echo !empty($ins['insurance_name']) ? htmlspecialchars($ins['insurance_name']) : '-'; ?></td>
                                        <td><?php echo !empty($ins['contract_num']) ? htmlspecialchars($ins['contract_num']) : '-'; ?></td>
                                        <td><?php echo !empty($ins['earnings']) ? number_format($ins['earnings'], 2) : '-'; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Debts Section -->
            <div class="card feature-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">7. Χρέη (<?php echo count($debts); ?>)</h5>
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
                                        <td><?php echo !empty($debt['creditor_name']) ? htmlspecialchars($debt['creditor_name']) : '-'; ?></td>
                                        <td><?php echo !empty($debt['type']) ? htmlspecialchars($debt['type']) : '-'; ?></td>
                                        <td><?php echo !empty($debt['amount']) ? number_format($debt['amount'], 2) : '-'; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Business Participations Section -->
            <div class="card feature-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">8. Συμμετοχές σε Επιχειρήσεις (<?php echo count($business); ?>)</h5>
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
                                        <td><?php echo !empty($biz['business_name']) ? htmlspecialchars($biz['business_name']) : '-'; ?></td>
                                        <td><?php echo !empty($biz['business_type']) ? htmlspecialchars($biz['business_type']) : '-'; ?></td>
                                        <td><?php echo !empty($biz['participation_type']) ? htmlspecialchars($biz['participation_type']) : '-'; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Other Incomes Section -->
            <div class="card feature-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">9. Άλλα Εισοδήματα (<?php echo count($other_incomes); ?>)</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($other_incomes)): ?>
                        <p class="text-muted">Δεν έχουν δηλωθεί άλλα εισοδήματα.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Τύπος Εισοδήματος</th>
                                        <th>Ποσό (€)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($other_incomes as $income): ?>
                                    <tr>
                                        <td><?php echo !empty($income['type']) ? htmlspecialchars($income['type']) : '-'; ?></td>
                                        <td><?php echo !empty($income['amount']) ? number_format($income['amount'], 2) : '-'; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Asset Differences Section -->
            <div class="card feature-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">10. Διαφορές στα Περιουσιακά Στοιχεία</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($differences['content'])): ?>
                        <p class="text-muted">Δεν έχουν δηλωθεί διαφορές στα περιουσιακά στοιχεία.</p>
                    <?php else: ?>
                        <p><?php echo nl2br(htmlspecialchars($differences['content'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Previous Incomes Section -->
            <div class="card feature-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">11. Εισοδήματα Προηγούμενων Ετών</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($previous_incomes['html_content'])): ?>
                        <p class="text-muted">Δεν έχουν δηλωθεί εισοδήματα προηγούμενων ετών.</p>
                    <?php else: ?>
                        <?php echo $previous_incomes['html_content']; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Footer Section -->
    <footer class="bg-light py-4 mt-auto border-top">
        <div class="container">
            <div class="row justify-content-center align-items-center">
                
                <div class="col-12 text-center mb-2">
                    <div class="mb-2">
                        <img src="../../assets/images/iconlogo.png" alt="Πόθεν Έσχες Logo" style="height: 42px; width: 42px; object-fit: contain;" />
                    </div>
                    <a href="#" class="text-decoration-none fw-medium" style="color: #ED9635;" data-bs-toggle="modal" data-bs-target="#aboutUsModal">
                        <i class="bi bi-person-badge me-1"></i>Ποιοι είμαστε
                    </a>
                </div>
                <div class="col-12 text-center mb-2">
                    <span class="fw-bold small" style="color: #ED9635; font-size: 0.95rem;"><a href="#" style="text-decoration: none; color: #ED9635;">Πόθεν Έσχες</a></span>
                    <span class="text-muted small">&copy; 2025. All rights reserved.</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- About Us Modal -->
    <?php include '../../includes/about-us-modal.php'; ?>

    <!-- External JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript for Interactive Features -->
    <script>
        $(document).ready(function() {
            // Handle favorite button functionality
            $('.favorite-btn').click(function() {
                const button = $(this);
                const declarationId = button.data('declaration-id');
                const isFavorited = button.hasClass('active');
                
                $.post(isFavorited ? 'remove-favorite.php' : 'add-favorite.php', 
                    { declaration_id: declarationId }, 
                    function(response) {
                        if (response.success) {
                            button.toggleClass('active');
                            button.find('.favorite-text').text(
                                button.hasClass('active') ? 'Αφαιρέθηκε από Αγαπημένα' : 'Προσθήκη στα Αγαπημένα'
                            );
                        } else {
                            alert('Error: ' + (response.message || 'Unknown error'));
                        }
                    }
                );
            });

            // Handle follow button functionality
            $('.follow-btn').click(function() {
                const button = $(this);
                const politicianId = button.data('politician-id');
                const isFollowing = button.hasClass('active');
                
                $.post(isFollowing ? 'unfollow.php' : 'follow.php', 
                    { politician_id: politicianId }, 
                    function(response) {
                        if (response.success) {
                            button.toggleClass('active');
                            button.find('.follow-text').text(
                                button.hasClass('active') ? 'Ακολουθείτε' : 'Ακολουθήστε'
                            );
                        } else {
                            alert('Error: ' + (response.message || 'Unknown error'));
                        }
                    }
                );
            });
        });
    </script>
</body>
</html> 
