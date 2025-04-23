<?php
// Database connection
require_once '../../config/db_connection.php';

// Get declaration ID from URL
$declaration_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch person's information
$stmt = $conn->prepare("SELECT * FROM people WHERE id = ?");
$stmt->execute([$declaration_id]);
$person = $stmt->fetch();

// Fetch properties
$stmt = $conn->prepare("SELECT * FROM properties WHERE person_id = ?");
$stmt->execute([$declaration_id]);
$properties = $stmt->fetchAll();

// Fetch liquid assets
$stmt = $conn->prepare("SELECT * FROM liquid_assets WHERE person_id = ?");
$stmt->execute([$declaration_id]);
$liquid_assets = $stmt->fetchAll();

// Fetch vehicles
$stmt = $conn->prepare("SELECT * FROM vehicles WHERE person_id = ?");
$stmt->execute([$declaration_id]);
$vehicles = $stmt->fetchAll();

// If no declaration found, redirect to search page
if (!$person) {
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
                    <h5 class="card-title mb-0">Personal Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <p class="mb-1"><strong>Name:</strong></p>
                            <p><?php echo htmlspecialchars($person['name']); ?></p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><strong>Title:</strong></p>
                            <p><?php echo htmlspecialchars($person['title']); ?></p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><strong>Role:</strong></p>
                            <p><?php echo htmlspecialchars($person['office']); ?></p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><strong>ID Number:</strong></p>
                            <p><?php echo htmlspecialchars($person['id_number']); ?></p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><strong>Date of Birth:</strong></p>
                            <p><?php echo htmlspecialchars($person['dob']); ?></p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><strong>Marital Status:</strong></p>
                            <p><?php echo htmlspecialchars($person['marital_status']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Properties -->
            <div class="card feature-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Real Estate Properties</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($properties)): ?>
                        <p class="text-muted">No properties declared.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Location</th>
                                        <th>Topographic Data</th>
                                        <th>Acquisition Method</th>
                                        <th>Acquisition Year</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($properties as $property): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($property['type']); ?></td>
                                        <td><?php echo htmlspecialchars($property['location']); ?></td>
                                        <td><?php echo htmlspecialchars($property['topographic_data']); ?></td>
                                        <td><?php echo htmlspecialchars($property['acquisition_method']); ?></td>
                                        <td><?php echo htmlspecialchars($property['acquisition_year']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Financial Assets -->
            <div class="card feature-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Financial Assets</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($liquid_assets)): ?>
                        <p class="text-muted">No financial assets declared.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Asset Type</th>
                                        <th>Description</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($liquid_assets as $asset): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($asset['asset_type']); ?></td>
                                        <td><?php echo htmlspecialchars($asset['description']); ?></td>
                                        <td>
                                            <?php 
                                            if (!empty($asset['amount'])) {
                                                // Check if amount contains a currency symbol
                                                if (strpos($asset['amount'], '€') !== false) {
                                                    echo htmlspecialchars($asset['amount']);
                                                } else {
                                                    echo '€' . htmlspecialchars($asset['amount']);
                                                }
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
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
                    <h5 class="card-title mb-0">Vehicles</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($vehicles)): ?>
                        <p class="text-muted">No vehicles declared.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Description</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($vehicles as $vehicle): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($vehicle['description']); ?></td>
                                        <td>€<?php echo htmlspecialchars($vehicle['value']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
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
</body>
</html> 