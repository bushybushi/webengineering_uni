<?php
// Database connection - using local connection for development
require_once '../../config/db_connection.php';

// Initialize variables
$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();

        // Debug: Log POST data
        error_log("POST Data: " . print_r($_POST, true));

        // Insert person's information
        $stmt = $conn->prepare("INSERT INTO people (name, title, office, id_number, dob, marital_status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['name'],
            $_POST['title'],
            $_POST['office'],
            $_POST['id_number'],
            $_POST['dob'],
            $_POST['marital_status']
        ]);
        $person_id = $conn->lastInsertId();
        
        // Debug: Log person_id
        error_log("Inserted person_id: " . $person_id);

        // Insert properties
        if (isset($_POST['properties'])) {
            $stmt = $conn->prepare("INSERT INTO properties (person_id, type, location, topographic_data, acquisition_method, acquisition_year) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($_POST['properties'] as $property) {
                $stmt->execute([
                    $person_id,
                    $property['type'],
                    $property['location'],
                    $property['topographic_data'],
                    $property['acquisition_method'],
                    $property['acquisition_year']
                ]);
                // Debug: Log property insert
                error_log("Inserted property for person_id: " . $person_id);
            }
        }

        // Insert liquid assets with debugging
        if (isset($_POST['asset_type'])) {
            $stmt = $conn->prepare("INSERT INTO liquid_assets (person_id, asset_type, description, amount) VALUES (?, ?, ?, ?)");
            for ($i = 0; $i < count($_POST['asset_type']); $i++) {
                // Debug: Log each liquid asset before insert
                error_log("Inserting liquid asset: " . print_r([
                    'person_id' => $person_id,
                    'asset_type' => $_POST['asset_type'][$i],
                    'description' => $_POST['asset_description'][$i],
                    'amount' => $_POST['asset_amount'][$i]
                ], true));

                $stmt->execute([
                    $person_id,
                    $_POST['asset_type'][$i],
                    $_POST['asset_description'][$i],
                    $_POST['asset_amount'][$i]
                ]);
                // Debug: Log successful insert
                error_log("Successfully inserted liquid asset for person_id: " . $person_id);
            }
        } else {
            // Debug: Log if no liquid assets were submitted
            error_log("No liquid assets submitted in the form");
        }

        // Insert vehicles
        if (isset($_POST['vehicles'])) {
            $stmt = $conn->prepare("INSERT INTO vehicles (person_id, description, value) VALUES (?, ?, ?)");
            foreach ($_POST['vehicles'] as $vehicle) {
                $stmt->execute([
                    $person_id,
                    $vehicle['description'],
                    $vehicle['value']
                ]);
            }
        }

        $conn->commit();
        $success_message = "Declaration submitted successfully!";
        
        // Debug: Log successful submission
        error_log("Declaration submitted successfully for person_id: " . $person_id);
        
    } catch (PDOException $e) {
        $conn->rollBack();
        $error_message = "Error: " . $e->getMessage();
        
        // Debug: Log error
        error_log("Error submitting declaration: " . $e->getMessage());
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
                        <a class="nav-link" href="../search_module/search.php">Search</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../search_module/statistics.php">Statistics</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="./declaration-form.php">Submit</a>
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
                <h1>Declaration Form</h1>
                <div>
                    <a href="../../index.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Main Page
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
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <div class="container py-5">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="card shadow-lg">
                            <div class="card-body p-5">
                                <form method="POST" class="needs-validation" novalidate>
                                    <!-- Personal Information -->
                                    <div class="mb-5">
                                        <h4 class="mb-4">1. Personal Information</h4>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Full Name</label>
                                                <input type="text" name="name" class="form-control" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Title</label>
                                                <input type="text" name="title" class="form-control" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Role</label>
                                                <input type="text" name="office" class="form-control" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">ID Number</label>
                                                <input type="text" name="id_number" class="form-control" required>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label">Address</label>
                                                <textarea name="address" class="form-control" rows="3" required></textarea>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Date of Birth</label>
                                                <input type="date" name="dob" class="form-control" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Marital Status</label>
                                                <select name="marital_status" class="form-select" required>
                                                    <option value="">Select status</option>
                                                    <option value="Single">Single</option>
                                                    <option value="Married">Married</option>
                                                    <option value="Divorced">Divorced</option>
                                                    <option value="Widowed">Widowed</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Number of Dependents</label>
                                                <input type="number" name="num_of_dependents" class="form-control" min="0" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Political Affiliation</label>
                                                <input type="text" name="political_affiliation" class="form-control" required>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Real Estate Properties -->
                                    <div class="mb-5">
                                        <h4 class="mb-4">2. Real Estate Properties</h4>
                                        <div id="properties-container">
                                            <div class="property-entry border rounded p-3 mb-3">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Property Type</label>
                                                        <select name="properties[0][type]" class="form-select" required>
                                                            <option value="">Select type</option>
                                                            <option>House</option>
                                                            <option>Apartment</option>
                                                            <option>Land</option>
                                                            <option>Commercial Property</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Location</label>
                                                        <textarea name="properties[0][location]" class="form-control" rows="2" required></textarea>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Topographic Data</label>
                                                        <input type="text" name="properties[0][topographic_data]" class="form-control">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Acquisition Method</label>
                                                        <select name="properties[0][acquisition_method]" class="form-select" required>
                                                            <option value="">Select method</option>
                                                            <option>Purchase</option>
                                                            <option>Inheritance</option>
                                                            <option>Gift</option>
                                                            <option>Exchange</option>
                                                            <option>Other</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Acquisition Year</label>
                                                        <input type="text" name="properties[0][acquisition_year]" class="form-control" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-warning" onclick="addPropertyEntry()">
                                            <i class="bi bi-plus-circle"></i> Add Another Property
                                        </button>
                                    </div>

                                    <!-- Financial Assets -->
                                    <div class="mb-5">
                                        <h4 class="mb-4">3. Financial Assets</h4>
                                        <div id="financial-assets-container">
                                            <div class="financial-entry border rounded p-3 mb-3">
                                                <div class="row g-3">
                                                    <div class="col-md-4">
                                                        <label class="form-label">Asset Type</label>
                                                        <select name="asset_type[]" class="form-select" required>
                                                            <option value="">Select type</option>
                                                            <option value="Bank Account">Bank Account</option>
                                                            <option value="Stocks">Stocks</option>
                                                            <option value="Bonds">Bonds</option>
                                                            <option value="Investment Fund">Investment Fund</option>
                                                            <option value="Cryptocurrency">Cryptocurrency</option>
                                                            <option value="Other">Other</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">Description</label>
                                                        <textarea name="asset_description[]" class="form-control" rows="2" required></textarea>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">Amount (€)</label>
                                                        <input type="number" name="asset_amount[]" class="form-control" step="0.01" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-warning" onclick="addFinancialEntry()">
                                            <i class="bi bi-plus-circle"></i> Add Another Financial Asset
                                        </button>
                                    </div>

                                    <!-- Vehicles -->
                                    <div class="mb-5">
                                        <h4 class="mb-4">4. Vehicles</h4>
                                        <div id="vehicles-container">
                                            <div class="vehicle-entry border rounded p-3 mb-3">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Description</label>
                                                        <textarea name="vehicles[0][description]" class="form-control" rows="2" required></textarea>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Value (€)</label>
                                                        <input type="number" name="vehicles[0][value]" class="form-control" step="0.01" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-warning" onclick="addVehicleEntry()">
                                            <i class="bi bi-plus-circle"></i> Add Another Vehicle
                                        </button>
                                    </div>

                                

                                    <!-- Declaration -->
                                    <div class="mb-5">
                                        <h4 class="mb-4">6. Declaration</h4>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" required>
                                            <label class="form-check-label">
                                                I declare that all information provided is true and accurate to the best of my knowledge.
                                            </label>
                                        </div>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" required>
                                            <label class="form-check-label">
                                                I understand that providing false information may result in legal consequences.
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-warning btn-lg text-dark">Submit Declaration</button>
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
        // Function to add new property entry
        function addPropertyEntry() {
            const container = document.getElementById('properties-container');
            const index = container.children.length;
            const template = `
                <div class="property-entry border rounded p-3 mb-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Property Type</label>
                            <select name="properties[${index}][type]" class="form-select" required>
                                <option value="">Select type</option>
                                <option>House</option>
                                <option>Apartment</option>
                                <option>Land</option>
                                <option>Commercial Property</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Location</label>
                            <textarea name="properties[${index}][location]" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Topographic Data</label>
                            <input type="text" name="properties[${index}][topographic_data]" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Acquisition Method</label>
                            <select name="properties[${index}][acquisition_method]" class="form-select" required>
                                <option value="">Select method</option>
                                <option>Purchase</option>
                                <option>Inheritance</option>
                                <option>Gift</option>
                                <option>Exchange</option>
                                <option>Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Acquisition Year</label>
                            <input type="text" name="properties[${index}][acquisition_year]" class="form-control" required>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', template);
        }

        // Function to add new financial asset entry
        function addFinancialEntry() {
            const container = document.getElementById('financial-assets-container');
            const index = container.children.length;
            const template = `
                <div class="financial-entry border rounded p-3 mb-3">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Asset Type</label>
                            <select name="asset_type[]" class="form-select" required>
                                <option value="">Select type</option>
                                <option value="Bank Account">Bank Account</option>
                                <option value="Stocks">Stocks</option>
                                <option value="Bonds">Bonds</option>
                                <option value="Investment Fund">Investment Fund</option>
                                <option value="Cryptocurrency">Cryptocurrency</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Description</label>
                            <textarea name="asset_description[]" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Amount (€)</label>
                            <input type="number" name="asset_amount[]" class="form-control" step="0.01" required>
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
            const template = `
                <div class="vehicle-entry border rounded p-3 mb-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Description</label>
                            <textarea name="vehicles[${index}][description]" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Value (€)</label>
                            <input type="number" name="vehicles[${index}][value]" class="form-control" step="0.01" required>
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
