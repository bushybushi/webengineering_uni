<?php
// Database connection
require_once '../../config/db_connection.php';

// Initialize variables
$success_message = '';
$error_message = '';
$validation_errors = [];
$field_errors = []; // New array to store field-specific errors

// Fetch parties for dropdown
$parties = [];
try {
    $stmt = $pdo->query("SELECT id, name FROM parties ORDER BY id");
    $parties = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error fetching parties: " . $e->getMessage();
}

// Ensure database connection is established
if (!isset($pdo)) {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=pothen_esxes", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        $error_message = "Database connection failed: " . $e->getMessage();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($pdo)) {
    try {
        $pdo->beginTransaction();

        // Insert declaration (empty for now as per requirements)
        $stmt = $pdo->prepare("INSERT INTO declarations (submission_date, title) VALUES (CURDATE(), :title)");
        $stmt->execute([
            ':title' => $_POST['full_name']
        ]);
        $declaration_id = $pdo->lastInsertId();

        // Insert personal data
        $stmt = $pdo->prepare("INSERT INTO personal_data (declaration_id, full_name, office, address, dob, id_number, marital_status, dependants, party_id) 
                              VALUES (:declaration_id, :full_name, :office, :address, :dob, :id_number, :marital_status, :dependants, :party_id)");
        
        $stmt->execute([
            ':declaration_id' => $declaration_id,
            ':full_name' => $_POST['full_name'],
            ':office' => $_POST['office'],
            ':address' => $_POST['address'],
            ':dob' => $_POST['dob'] ?: null,
            ':id_number' => $_POST['id_number'],
            ':marital_status' => $_POST['marital_status'],
            ':dependants' => $_POST['dependants'],
            ':party_id' => $_POST['party_id']
        ]);

        // Insert properties
        if (isset($_POST['properties'])) {
            $stmt = $pdo->prepare("INSERT INTO properties (declaration_id, location, type, area, topographic_data, rights_burdens, acquisition_mode, acquisition_date, acquisition_value, current_value) 
                                  VALUES (:declaration_id, :location, :type, :area, :topographic_data, :rights_burdens, :acquisition_mode, :acquisition_date, :acquisition_value, :current_value)");
            foreach ($_POST['properties'] as $property) {
                $stmt->execute([
                    ':declaration_id' => $declaration_id,
                    ':location' => $property['location'],
                    ':type' => $property['type'],
                    ':area' => $property['area'],
                    ':topographic_data' => $property['topographic_data'],
                    ':rights_burdens' => $property['rights_burdens'],
                    ':acquisition_mode' => $property['acquisition_mode'],
                    ':acquisition_date' => $property['acquisition_date'],
                    ':acquisition_value' => $property['acquisition_value'],
                    ':current_value' => $property['current_value']
                ]);
            }
        }

        // Insert vehicles
        if (isset($_POST['vehicles'])) {
            $stmt = $pdo->prepare("INSERT INTO vehicles (declaration_id, brand, manu_year, value) 
                                  VALUES (:declaration_id, :brand, :manu_year, :value)");
            foreach ($_POST['vehicles'] as $vehicle) {
                $stmt->execute([
                    ':declaration_id' => $declaration_id,
                    ':brand' => $vehicle['brand'],
                    ':manu_year' => $vehicle['manu_year'],
                    ':value' => $vehicle['value']
                ]);
            }
        }

        // Insert liquid assets
        if (isset($_POST['liquid_assets'])) {
            $stmt = $pdo->prepare("INSERT INTO liquid_assets (declaration_id, type, description, amount) 
                                  VALUES (:declaration_id, :type, :description, :amount)");
            foreach ($_POST['liquid_assets'] as $asset) {
                $stmt->execute([
                    ':declaration_id' => $declaration_id,
                    ':type' => $asset['type'],
                    ':description' => $asset['description'],
                    ':amount' => $asset['amount']
                ]);
            }
        }

        // Insert deposits
        if (isset($_POST['deposits'])) {
            $stmt = $pdo->prepare("INSERT INTO deposits (declaration_id, bank_name, amount) 
                                  VALUES (:declaration_id, :bank_name, :amount)");
            foreach ($_POST['deposits'] as $deposit) {
                $stmt->execute([
                    ':declaration_id' => $declaration_id,
                    ':bank_name' => $deposit['bank_name'],
                    ':amount' => $deposit['amount']
                ]);
            }
        }

        // Insert insurance
        if (isset($_POST['insurance'])) {
            $stmt = $pdo->prepare("INSERT INTO insurance (declaration_id, insurance_name, contract_num, earnings) 
                                  VALUES (:declaration_id, :insurance_name, :contract_num, :earnings)");
            foreach ($_POST['insurance'] as $insurance) {
                $stmt->execute([
                    ':declaration_id' => $declaration_id,
                    ':insurance_name' => $insurance['insurance_name'],
                    ':contract_num' => $insurance['contract_num'],
                    ':earnings' => $insurance['earnings']
                ]);
            }
        }

        // Insert debts
        if (isset($_POST['debts'])) {
            $stmt = $pdo->prepare("INSERT INTO debts (declaration_id, creditor_name, type, amount) 
                                  VALUES (:declaration_id, :creditor_name, :type, :amount)");
            foreach ($_POST['debts'] as $debt) {
                $stmt->execute([
                    ':declaration_id' => $declaration_id,
                    ':creditor_name' => $debt['creditor_name'],
                    ':type' => $debt['type'],
                    ':amount' => $debt['amount']
                ]);
            }
        }

        // Insert business participations
        if (isset($_POST['business'])) {
            $stmt = $pdo->prepare("INSERT INTO bussiness (declaration_id, business_name, business_type, participation_type) 
                                  VALUES (:declaration_id, :business_name, :business_type, :participation_type)");
            foreach ($_POST['business'] as $business) {
                $stmt->execute([
                    ':declaration_id' => $declaration_id,
                    ':business_name' => $business['business_name'],
                    ':business_type' => $business['business_type'],
                    ':participation_type' => $business['participation_type']
                ]);
            }
        }

        // Insert differences
        if (isset($_POST['differences'])) {
            $stmt = $pdo->prepare("INSERT INTO differences (declaration_id, content) 
                                  VALUES (:declaration_id, :content)");
            $stmt->execute([
                ':declaration_id' => $declaration_id,
                ':content' => $_POST['differences']
            ]);
        }

        // Insert previous incomes
        if (isset($_POST['previous_incomes'])) {
            $stmt = $pdo->prepare("INSERT INTO previous_incomes (declaration_id, html_content) 
                                  VALUES (:declaration_id, :html_content)");
            $stmt->execute([
                ':declaration_id' => $declaration_id,
                ':html_content' => $_POST['previous_incomes']
            ]);
        }

        $pdo->commit();
        $success_message = "Η δήλωση υποβλήθηκε επιτυχώς!";
    } catch (PDOException $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error_message = "Σφάλμα κατά την υποβολή της δήλωσης: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Declaration Form - ΠΟΘΕΝ ΕΣΧΕΣ</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css"/>
    <!-- CKEditor -->
    <script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>
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
        .remove-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .remove-btn:hover {
            background: #c82333;
            transform: scale(1.1);
        }
        .entry-container {
            position: relative;
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
                        <a class="nav-link active" href="./declaration-form.php">Υποβολή</a>
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
                <h1>Υποβολή Δήλωσης</h1>
                <div>
                    <a href="../../index.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Πίσω στην Αρχική
                    </a>
                </div>
            </div>

            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert" id="successAlert">
                    <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="container py-5">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="card shadow-lg">
                            <div class="card-body p-5">
                                <form method="POST" class="needs-validation" novalidate>
                                    <!-- Personal Information -->
                                    <div class="mb-5">
                                        <h4 class="mb-4">1. Προσωπικά Στοιχεία</h4>
                                        <div class="row border rounded p-3 mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Ονοματεπώνυμο *</label>
                                                <input type="text" name="full_name" class="form-control <?php echo isset($field_errors['full_name']) ? 'is-invalid' : ''; ?>" 
                                                       value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" required>
                                                <div class="invalid-feedback">
                                                    Παρακαλώ εισάγετε το ονοματεπώνυμο
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Ιδιοτήτα/Αξίωμα *</label>
                                                <select name="office" class="form-select <?php echo isset($field_errors['office']) ? 'is-invalid' : ''; ?>" required>
                                                    <option value="">Επιλέξτε Ιδιοτήτα/Αξίωμα</option>
                                                    <option value="Πρόεδρος της Δημοκρατίας" <?php echo (isset($_POST['office']) && $_POST['office'] == 'Πρόεδρος της Δημοκρατίας') ? 'selected' : ''; ?>>Πρόεδρος της Δημοκρατίας</option>
                                                    <option value="Πρόεδρος της Βουλής των Αντιπροσώπων" <?php echo (isset($_POST['office']) && $_POST['office'] == 'Πρόεδρος της Βουλής των Αντιπροσώπων') ? 'selected' : ''; ?>>Πρόεδρος της Βουλής των Αντιπροσώπων</option>
                                                    <option value="Υπουργοί" <?php echo (isset($_POST['office']) && $_POST['office'] == 'Υπουργοί') ? 'selected' : ''; ?>>Υπουργός</option>
                                                    <option value="Βουλευτές" <?php echo (isset($_POST['office']) && $_POST['office'] == 'Βουλευτές') ? 'selected' : ''; ?>>Βουλευτής</option>
                                                    <option value="Ευρωβουλευτές" <?php echo (isset($_POST['office']) && $_POST['office'] == 'Ευρωβουλευτές') ? 'selected' : ''; ?>>Ευρωβουλευτής</option>
                                                    <option value="Υφυπουργοί" <?php echo (isset($_POST['office']) && $_POST['office'] == 'Υφυπουργοί') ? 'selected' : ''; ?>>Υφυπουργός</option>
                                                    <option value="Τέως Πρόεδρος της Δημοκρατίας" <?php echo (isset($_POST['office']) && $_POST['office'] == 'Τέως Πρόεδρος της Δημοκρατίας') ? 'selected' : ''; ?>>Τέως Πρόεδρος της Δημοκρατίας</option>
                                                    <option value="Τέως Πρόεδρος της Βουλής των Αντιπροσώπων" <?php echo (isset($_POST['office']) && $_POST['office'] == 'Τέως Πρόεδρος της Βουλής των Αντιπροσώπων') ? 'selected' : ''; ?>>Τέως Πρόεδρος της Βουλής των Αντιπροσώπων</option>
                                                    <option value="Τέως Υπουργοί" <?php echo (isset($_POST['office']) && $_POST['office'] == 'Τέως Υπουργοί') ? 'selected' : ''; ?>>Τέως Υπουργός</option>
                                                    <option value="Τέως Βουλευτές" <?php echo (isset($_POST['office']) && $_POST['office'] == 'Τέως Βουλευτές') ? 'selected' : ''; ?>>Τέως Βουλευτής</option>
                                                    <option value="Τέως Ευρωβουλευτές" <?php echo (isset($_POST['office']) && $_POST['office'] == 'Τέως Ευρωβουλευτές') ? 'selected' : ''; ?>>Τέως Ευρωβουλευτής</option>
                                                    <option value="Τέως Υφυπουργοί" <?php echo (isset($_POST['office']) && $_POST['office'] == 'Τέως Υφυπουργοί') ? 'selected' : ''; ?>>Τέως Υφυπουργός</option>
                                                </select>
                                                <div class="invalid-feedback">
                                                    Παρακαλώ επιλέξτε ιδιοτήτα/αξίωμα
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label">Διεύθυνση </label>
                                                <textarea name="address" class="form-control" rows="3"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Ημερομηνία Γέννησης *</label>
                                                <input type="date" name="dob" class="form-control <?php echo isset($field_errors['dob']) ? 'is-invalid' : ''; ?>" 
                                                       value="<?php echo isset($_POST['dob']) ? htmlspecialchars($_POST['dob']) : ''; ?>"
                                                       min="1900-01-01" max="<?php echo date('Y-m-d'); ?>" required>
                                                <div class="invalid-feedback">
                                                    Παρακαλώ εισάγετε έγκυρη ημερομηνία γέννησης
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Αριθμος ταυτότητας</label>
                                                <input type="text" name="id_number" class="form-control" 
                                                       value="<?php echo isset($_POST['id_number']) ? htmlspecialchars($_POST['id_number']) : ''; ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Οικογενειακή Κατάσταση *</label>
                                                <select name="marital_status" class="form-select <?php echo isset($field_errors['marital_status']) ? 'is-invalid' : ''; ?>" required>
                                                    <option value="">Επιλέξτε Κατάσταση</option>
                                                    <option value="Άγαμος/η" <?php echo (isset($_POST['marital_status']) && $_POST['marital_status'] == 'Άγαμος/η') ? 'selected' : ''; ?>>Άγαμος/η</option>
                                                    <option value="Έγγαμος/η" <?php echo (isset($_POST['marital_status']) && $_POST['marital_status'] == 'Έγγαμος/η') ? 'selected' : ''; ?>>Έγγαμος/η</option>
                                                    <option value="Διαζευγμένος/η" <?php echo (isset($_POST['marital_status']) && $_POST['marital_status'] == 'Διαζευγμένος/η') ? 'selected' : ''; ?>>Διαζευγμένος/η</option>
                                                    <option value="Άλλο" <?php echo (isset($_POST['marital_status']) && $_POST['marital_status'] == 'Άλλο') ? 'selected' : ''; ?>>Άλλο</option>
                                                </select>
                                                <div class="invalid-feedback">
                                                    Παρακαλώ επιλέξτε οικογενειακή κατάσταση
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Αριθμος ανηλίκων τεκνών *</label>
                                                <input type="number" name="dependants" class="form-control <?php echo isset($field_errors['dependants']) ? 'is-invalid' : ''; ?>" 
                                                       value="<?php echo isset($_POST['dependants']) ? htmlspecialchars($_POST['dependants']) : ''; ?>"
                                                       min="0" required>
                                                <div class="invalid-feedback">
                                                    Παρακαλώ εισάγετε αριθμό ανηλίκων τεκνών
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Πολιτικό Κόμμα/Παράταξη</label>
                                                <select name="party_id" class="form-select">
                                                    <option value="">Επιλέξτε Κόμμα</option>
                                                    <?php foreach ($parties as $party): ?>
                                                        <option value="<?php echo htmlspecialchars($party['id']); ?>">
                                                            <?php echo htmlspecialchars($party['name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Real Estate Properties -->
                                    <div class="mb-5">
                                        <h4 class="mb-4">2. Ακίνητη Ιδιοκτησία</h4>
                                        <div id="properties-container">
                                            <div class="property-entry entry-container border rounded p-3 mb-3">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Είδος</label>
                                                        <select name="properties[0][type]" class="form-select property-type" required>
                                                            <option value="">Επιλέξτε</option>
                                                            <option value="Σπίτι">Σπίτι</option>
                                                            <option value="Διαμέρισμα">Διαμέρισμα</option>
                                                            <option value="Οικόπεδο">Οικόπεδο</option>
                                                            <option value="Χωράφι">Χωράφι</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Τοποθεσία</label>
                                                        <textarea name="properties[0][location]" class="form-control" rows="2" required></textarea>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Εκταση (m²)</label>
                                                        <input type="number" name="properties[0][area]" class="form-control" step="0.01" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Τοπογραφικά Στοιχεία</label>
                                                        <input type="text" name="properties[0][topographic_data]" class="form-control">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Εμπράγματα δικαιώματα και βάρη επ' αυτής</label>
                                                        <textarea name="properties[0][rights_burdens]" class="form-control" rows="2"></textarea>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Τρόπος απόκτησης</label>
                                                        <input type="text" name="properties[0][acquisition_mode]" class="form-control" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Χρόνος απόκτησης</label>
                                                        <select name="properties[0][acquisition_date]" class="form-select" required>
                                                            <option value="">Επιλέξτε Χρόνο</option>
                                                            <?php
                                                            $currentYear = date('Y');
                                                            for ($year = $currentYear; $year >= 1900; $year--) {
                                                                echo "<option value='$year'>$year</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Αξία απόκτησης (€)</label>
                                                        <input type="text" name="properties[0][acquisition_value]" class="form-control" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Τρέχουσα αξία (€)</label>
                                                        <input type="text" name="properties[0][current_value]" class="form-control" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-warning" onclick="addPropertyEntry()">
                                            <i class="bi bi-plus-circle"></i> Προσθήκη Άλλης Ιδιοκτησίας
                                        </button>
                                    </div>

                                    <!-- Vehicles -->
                                    <div class="mb-5">
                                        <h4 class="mb-4">3. Μηχανοκίνητα μεταφορικά μέσα</h4>
                                        <div id="vehicles-container">
                                            <div class="vehicle-entry entry-container border rounded p-3 mb-3">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Μάρκα</label>
                                                        <input type="text" name="vehicles[0][brand]" class="form-control" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Χρονολογία παραγωγής</label>
                                                        <select name="vehicles[0][manu_year]" class="form-select" required>
                                                            <option value="">Επιλέξτε Χρόνο</option>
                                                            <?php
                                                            $currentYear = date('Y');
                                                            for ($year = $currentYear; $year >= 1900; $year--) {
                                                                echo "<option value='$year'>$year</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Αξία (€)</label>
                                                        <input type="number" name="vehicles[0][value]" class="form-control" step="0.01" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-warning" onclick="addVehicleEntry()">
                                            <i class="bi bi-plus-circle"></i> Προσθήκη Άλλου Μηχανοκίνητου Μεταφορικού Μέσου
                                        </button>
                                    </div>

                                    <!-- Liquid Assets -->
                                    <div class="mb-5">
                                        <h4 class="mb-4">4.  Εισοδήματα και περιουσιακά στοιχεία σε κινητές αξίες και τίτλους</h4>
                                        <div id="liquid-assets-container">
                                            <div class="liquid-asset-entry entry-container border rounded p-3 mb-3">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Είδος Κινητής Αξίας</label>
                                                        <select name="liquid_assets[0][type]" class="form-select" required>
                                                            <option value="">Επιλέξτε Είδος</option>
                                                            <option value="Χρεόγραφα">Χρεόγραφα</option>
                                                            <option value="Χρεωστικά Ομόλογα">Χρεωστικά Ομόλογα</option>
                                                            <option value="Ομολογίες">Ομολογίες</option>
                                                            <option value="Τίτλοι">Τίτλοι</option>
                                                            <option value="Μετοχές">Μετοχές</option>
                                                            <option value="Μερίσματα">Μερίσματα</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Περιγραφή</label>
                                                        <textarea name="liquid_assets[0][description]" class="form-control" rows="2" required></textarea>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <label class="form-label">Αριθμός σε Κατοχή</label>
                                                        <input type="text" name="liquid_assets[0][amount]" class="form-control" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-warning" onclick="addLiquidAssetEntry()">
                                            <i class="bi bi-plus-circle"></i> Προσθήκη Άλλης Κινητής Αξίας
                                        </button>
                                    </div>

                                    <!-- Bank Deposits -->
                                    <div class="mb-5">
                                        <h4 class="mb-4">5. Καταθέσεις σε Τράπεζες</h4>
                                        <div id="deposits-container">
                                            <div class="deposit-entry entry-container border rounded p-3 mb-3">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Όνομα Τράπεζας</label>
                                                        <input type="text" name="deposits[0][bank_name]" class="form-control" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Ποσό Κατάθεσης (€)</label>
                                                        <input type="number" name="deposits[0][amount]" class="form-control" step="0.01" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-warning" onclick="addDepositEntry()">
                                            <i class="bi bi-plus-circle"></i> Προσθήκη Άλλης Κατάθεσης σε Τράπεζα
                                        </button>
                                    </div>

                                    <!-- Insurance -->
                                    <div class="mb-5">
                                        <h4 class="mb-4">6. Ασφαλιστικά Συμβόλαια</h4>
                                        <div id="insurance-container">
                                            <div class="insurance-entry entry-container border rounded p-3 mb-3">
                                                <div class="row g-3">
                                                    <div class="col-md-4">
                                                        <label class="form-label">Όνομα Ασφαλιστικής Εταιρείας</label>
                                                        <input type="text" name="insurance[0][insurance_name]" class="form-control" required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">Αριθμός Συμβολαίου</label>
                                                        <input type="text" name="insurance[0][contract_num]" class="form-control" required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">Εισοδήματα (€)</label>
                                                        <input type="number" name="insurance[0][earnings]" class="form-control" step="0.01" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-warning" onclick="addInsuranceEntry()">
                                            <i class="bi bi-plus-circle"></i> Προσθήκη Άλλου Ασφαλιστικού Συμβολαίου
                                        </button>
                                    </div>

                                    <!-- Debts -->
                                    <div class="mb-5">
                                        <h4 class="mb-4">7. Χρέη</h4>
                                        <div id="debts-container">
                                            <div class="debt-entry entry-container border rounded p-3 mb-3">
                                                <div class="row g-3">
                                                    <div class="col-md-4">
                                                        <label class="form-label">Όνομα Πιστωτή</label>
                                                        <input type="text" name="debts[0][creditor_name]" class="form-control" required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">Είδος Χρέους</label>
                                                        <input type="text" name="debts[0][type]" class="form-control" required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">Ποσό Χρέους (€)</label>
                                                        <input type="number" name="debts[0][amount]" class="form-control" step="0.01" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-warning" onclick="addDebtEntry()">
                                            <i class="bi bi-plus-circle"></i> Προσθήκη Άλλου Χρέους
                                        </button>
                                    </div>

                                    <!-- Business Participations -->
                                    <div class="mb-5">
                                        <h4 class="mb-4">8. Συμμετοχές σε Επιχειρήσεις</h4>
                                        <div id="business-container">
                                            <div class="business-entry entry-container border rounded p-3 mb-3">
                                                <div class="row g-3">
                                                    <div class="col-md-4">
                                                        <label class="form-label">Όνομα Επιχειρήσης</label>
                                                        <input type="text" name="business[0][business_name]" class="form-control" required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">Είδος Επιχειρήσης</label>
                                                        <input type="text" name="business[0][business_type]" class="form-control" required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">Είδος Συμμετοχής</label>
                                                        <input type="text" name="business[0][participation_type]" class="form-control" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-warning" onclick="addBusinessEntry()">
                                            <i class="bi bi-plus-circle"></i> Προσθήκη Άλλης Επιχειρήσης
                                        </button>
                                    </div>

                                    <!-- Differences -->
                                    <div class="mb-5">
                                        <h4 class="mb-4">9. Διαφορές στα περιουσιακά στοιχεία</h4>
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label">Παρακαλούμε να εξηγήσετε οποιεσδήποτε διαφορές στα περιουσιακά στοιχεία σας</label>
                                                <textarea name="differences" class="form-control" rows="4"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Previous Incomes -->
                                    <div class="mb-5">
                                        <h4 class="mb-4">10. Εισοδήματα προηγούμενων ετών</h4>
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label">Παρακαλούμε καταχωρήστε τα εισοδήματα των προηγούμενων ετών</label>
                                                <textarea id="previous_incomes" name="previous_incomes" class="form-control" rows="4"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Declaration -->
                                    <div class="mb-5">
                                        <h4 class="mb-4">11. Δήλωση</h4>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" required>
                                            <label class="form-check-label">
                                                Θέλω να δηλώσω ότι όλα τα παραπάνω περιουσιακά στοιχεία είναι αληθινά και ακριβή.
                                            </label>
                                        </div>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" required>
                                            <label class="form-check-label">
                                                Ενημερώνομαι ότι η παράδοση ψευδών πληροφοριών μπορεί να οδηγήσει σε νομικές συνέπειες.
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-warning btn-lg text-dark">Αποθήκευση</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
    <!-- Custom JS -->
    <script>
        // Function to handle property type selection
        function handlePropertyTypeChange(selectElement) {
            // This function is no longer needed as we removed the "Άλλο" option
        }

        // Add event listeners to existing property type selects
        document.addEventListener('DOMContentLoaded', function() {
            // This event listener is no longer needed as we removed the "Άλλο" option
        });

        // Modify addPropertyEntry function to include the event listener
        function addPropertyEntry() {
            const container = document.getElementById('properties-container');
            const index = container.children.length;
            const currentYear = new Date().getFullYear();
            let yearOptions = '<option value="">Επιλέξτε Χρόνο</option>';
            for (let year = currentYear; year >= 1900; year--) {
                yearOptions += `<option value="${year}">${year}</option>`;
            }
            
            const template = `
                <div class="property-entry entry-container border rounded p-3 mb-3">
                    <button type="button" class="remove-btn" onclick="this.parentElement.remove()">
                        <i class="bi bi-x"></i>
                    </button>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Είδος</label>
                            <select name="properties[${index}][type]" class="form-select property-type" required>
                                <option value="">Επιλέξτε</option>
                                <option value="Σπίτι">Σπίτι</option>
                                <option value="Διαμέρισμα">Διαμέρισμα</option>
                                <option value="Οικόπεδο">Οικόπεδο</option>
                                <option value="Χωράφι">Χωράφι</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Τοποθεσία</label>
                            <textarea name="properties[${index}][location]" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Εκταση (m²)</label>
                            <input type="number" name="properties[${index}][area]" class="form-control" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Τοπογραφικά Στοιχεία</label>
                            <input type="text" name="properties[${index}][topographic_data]" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Εμπράγματα δικαιώματα και βάρη επ' αυτής</label>
                            <textarea name="properties[${index}][rights_burdens]" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Τρόπος απόκτησης</label>
                            <input type="text" name="properties[${index}][acquisition_mode]" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Χρόνος απόκτησης</label>
                            <select name="properties[${index}][acquisition_date]" class="form-select" required>
                                ${yearOptions}
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Αξία απόκτησης (€)</label>
                            <input type="text" name="properties[${index}][acquisition_value]" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Τρέχουσα αξία (€)</label>
                            <input type="text" name="properties[${index}][current_value]" class="form-control" required>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', template);
        }

        // Function to add new vehicle entry
        function addVehicleEntry() {
            const container = document.getElementById('vehicles-container');
            const index = container.children.length;
            const currentYear = new Date().getFullYear();
            let yearOptions = '<option value="">Επιλέξτε Χρόνο</option>';
            for (let year = currentYear; year >= 1900; year--) {
                yearOptions += `<option value="${year}">${year}</option>`;
            }
            
            const template = `
                <div class="vehicle-entry entry-container border rounded p-3 mb-3">
                    <button type="button" class="remove-btn" onclick="this.parentElement.remove()">
                        <i class="bi bi-x"></i>
                    </button>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Μάρκα</label>
                            <input type="text" name="vehicles[${index}][brand]" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Χρονολογία παραγωγής</label>
                            <select name="vehicles[${index}][manu_year]" class="form-select" required>
                                ${yearOptions}
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Αξία (€)</label>
                            <input type="number" name="vehicles[${index}][value]" class="form-control" step="0.01" required>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', template);
        }

        // Function to add new liquid asset entry
        function addLiquidAssetEntry() {
            const container = document.getElementById('liquid-assets-container');
            const index = container.children.length;
            const template = `
                <div class="liquid-asset-entry entry-container border rounded p-3 mb-3">
                    <button type="button" class="remove-btn" onclick="this.parentElement.remove()">
                        <i class="bi bi-x"></i>
                    </button>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Είδος Κινητής Αξίας</label>
                            <select name="liquid_assets[${index}][type]" class="form-select" required>
                                <option value="">Επιλέξτε Είδος</option>
                                <option value="Χρεόγραφα">Χρεόγραφα</option>
                                <option value="Χρεωστικά Ομόλογα">Χρεωστικά Ομόλογα</option>
                                <option value="Ομολογίες">Ομολογίες</option>
                                <option value="Τίτλοι">Τίτλοι</option>
                                <option value="Μετοχές">Μετοχές</option>
                                <option value="Μερίσματα">Μερίσματα</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Περιγραφή</label>
                            <textarea name="liquid_assets[${index}][description]" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Αριθμός σε Κατοχή</label>
                            <input type="text" name="liquid_assets[${index}][amount]" class="form-control" required>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', template);
        }

        // Function to add new deposit entry
        function addDepositEntry() {
            const container = document.getElementById('deposits-container');
            const index = container.children.length;
            const template = `
                <div class="deposit-entry entry-container border rounded p-3 mb-3">
                    <button type="button" class="remove-btn" onclick="this.parentElement.remove()">
                        <i class="bi bi-x"></i>
                    </button>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Όνομα Τράπεζας</label>
                            <input type="text" name="deposits[${index}][bank_name]" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ποσό Κατάθεσης (€)</label>
                            <input type="number" name="deposits[${index}][amount]" class="form-control" step="0.01" required>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', template);
        }

        // Function to add new insurance entry
        function addInsuranceEntry() {
            const container = document.getElementById('insurance-container');
            const index = container.children.length;
            const template = `
                <div class="insurance-entry entry-container border rounded p-3 mb-3">
                    <button type="button" class="remove-btn" onclick="this.parentElement.remove()">
                        <i class="bi bi-x"></i>
                    </button>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Όνομα Ασφαλιστικής Εταιρείας</label>
                            <input type="text" name="insurance[${index}][insurance_name]" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Αριθμός Συμβολαίου</label>
                            <input type="text" name="insurance[${index}][contract_num]" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Εισοδήματα (€)</label>
                            <input type="number" name="insurance[${index}][earnings]" class="form-control" step="0.01" required>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', template);
        }

        // Function to add new debt entry
        function addDebtEntry() {
            const container = document.getElementById('debts-container');
            const index = container.children.length;
            const template = `
                <div class="debt-entry entry-container border rounded p-3 mb-3">
                    <button type="button" class="remove-btn" onclick="this.parentElement.remove()">
                        <i class="bi bi-x"></i>
                    </button>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Όνομα Πιστωτή</label>
                            <input type="text" name="debts[${index}][creditor_name]" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Είδος Χρέους</label>
                            <input type="text" name="debts[${index}][type]" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ποσό Χρέους (€)</label>
                            <input type="number" name="debts[${index}][amount]" class="form-control" step="0.01" required>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', template);
        }

        // Function to add new business entry
        function addBusinessEntry() {
            const container = document.getElementById('business-container');
            const index = container.children.length;
            const template = `
                <div class="business-entry entry-container border rounded p-3 mb-3">
                    <button type="button" class="remove-btn" onclick="this.parentElement.remove()">
                        <i class="bi bi-x"></i>
                    </button>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Όνομα Επιχειρήσης</label>
                            <input type="text" name="business[${index}][business_name]" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Είδος Επιχειρήσης</label>
                            <input type="text" name="business[${index}][business_type]" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Είδος Συμμετοχής</label>
                            <input type="text" name="business[${index}][participation_type]" class="form-control" required>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', template);
        }

        // Auto-dismiss success message after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const successAlert = document.getElementById('successAlert');
            if (successAlert) {
                setTimeout(function() {
                    const alert = new bootstrap.Alert(successAlert);
                    alert.close();
                }, 5000); // 5000 milliseconds = 5 seconds
            }
        });
    </script>
</body>
</html> 
