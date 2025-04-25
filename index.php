<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ΠΟΘΕΝ ΕΣΧΕΣ - Asset Declaration System</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/iconlogo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Flag Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css"/>
    <!-- Custom CSS -->
    <link href="./assets/css/style.css" rel="stylesheet">
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
            <a class="navbar-brand d-flex align-items-center" href="./index.php">
                <img src="./assets/images/logo.jpg" alt="ΠΟΘΕΝ ΕΣΧΕΣ Logo" height="40" class="me-3">
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
                        <a class="nav-link" href="./index.php">Αρχική</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="./modules/search_module/search.php">Αναζήτηση</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="./modules/search_module/statistics.php">Στατιστικά</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="./modules/submit_module/declaration-form.php">Υποβολή</a>
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
                                <li><a class="dropdown-item" href="./modules/login_module/login.php"><i class="bi bi-box-arrow-in-right"></i> Σύνδεση</a></li>
                                <li><a class="dropdown-item" href="./modules/login_module/register.php"><i class="bi bi-person-plus"></i> Εγγραφή</a></li>
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
                            <a class="nav-link d-flex align-items-center gap-2 mb-2" href="./index.php">
                                <i class="bi bi-house"></i> Αρχική
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 mb-2" href="./modules/search_module/search.php">
                                <i class="bi bi-search"></i> Αναζήτηση
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 mb-2" href="./modules/search_module/statistics.php">
                                <i class="bi bi-graph-up"></i> Στατιστικά
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 mb-3" href="./modules/submit_module/declaration-form.php">
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
                                <a href="./modules/login_module/login.php" class="nav-link py-2">
                                    <i class="bi bi-box-arrow-in-right me-2"></i> Σύνδεση
                                </a>
                                <a href="./modules/login_module/register.php" class="nav-link py-2">
                                    <i class="bi bi-person-plus me-2"></i> Εγγραφή
                                </a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <video autoplay muted loop class="hero-video">
            <source src="assets/images/background.mp4" type="video/mp4">
        </video>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 col-md-8">
                    <h1 class="hero-title">Παρακολούθησε τις Δηλώσεις Πόθεν Έσχες</h1>
                    <div class="search-box-container mb-4">
                        <form id="searchForm" action="./modules/search_module/search.php" method="GET" class="d-flex gap-2">
                            <input type="text" class="form-control form-control-lg" name="q" placeholder="Αναζήτηση Δηλώσεων . . ." aria-label="Αναζήτηση Δηλώσεων">
                            <button type="submit" form="searchForm" class="btn btn-light btn-lg px-4"><i class="bi bi-search"></i></button>
                        </form>
                    </div>
                    <div class="d-flex flex-column flex-sm-row gap-3">
                        <a href="./modules/login_module/register.php" class="btn btn-light btn-lg px-4">Εγγραφή</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="container mb-5">
        <!-- Stats Section -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="stats-card text-center h-100">
                    <div class="stat-number">500+</div>
                    <div class="stat-label">Δημοσίοι Αξιωματούχοι</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card text-center h-100">
                    <div class="stat-number">8</div>
                    <div class="stat-label">Πολιτικά Κόμματα</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card text-center h-100">
                    <div class="stat-number">2025</div>
                    <div class="stat-label">Τελευταίες Δηλώσεις</div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-search feature-icon"></i>
                        <h5 class="card-title">Δημόσια Αναζήτηση</h5>
                        <p class="card-text">Αναζητήστε δηλώσεις με βάση το όνομα, το πολιτικό κόμμα ή τη θέση. Αποκτήστε πλήρη πληροφόρηση για την περιουσιακή κατάσταση των Δημοσίων Αξιωματούχων.</p>
                        <a href="./modules/search_module/search.php" class="btn btn-outline-primary" style="color: #ED9635; border-color: #ED9635;">Αναζήτησε Τώρα</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-file-earmark-text feature-icon"></i>
                        <h5 class="card-title">Υποβολή Δήλωσης</h5>
                        <p class="card-text">Οι δημόσιοι λειτουργοί μπορούν να υποβάλουν τις δηλώσεις τους μέσω της ασφαλούς μας πλατφόρμας. Παρακολούθηση κατάστασης και ειδοποιήσεις.</p>
                        <a href="./modules/submit_module/declaration-form.php" class="btn btn-outline-primary" style="color: #ED9635; border-color: #ED9635;">Υποβολή Δήλωσης</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-graph-up feature-icon"></i>
                        <h5 class="card-title">Στατιστικά & Ανάλυση</h5>
                        <p class="card-text">Δείτε συγκεντρωτικά δεδομένα και στατιστικά στοιχεία για τις δηλώσεις ανά έτος, κόμμα και θέση.</p>
                        <a href="./modules/search_module/statistics.php" class="btn btn-outline-primary" style="color: #ED9635; border-color: #ED9635;">Προβολή Στατιστικών</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Latest Declarations -->
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card feature-card h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Πρόσφατες Δηλώσεις</h5>
                        <div class="declaration-item">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle">A</div>
                                <div class="ms-3">
                                    <h6 class="mb-1">Ανδρέας Αθανασιάδης</h6>
                                    <small class="text-muted">Μελος του Κοινοβουλίου - 15/03/2024</small>
                                </div>
                            </div>
                        </div>
                        <div class="declaration-item">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle">M</div>
                                <div class="ms-3">
                                    <h6 class="mb-1">Μαρία Μιχαηλίδου</h6>
                                    <small class="text-muted">Υπουργός - 14/03/2024</small>
                                </div>
                            </div>
                        </div>
                        <div class="declaration-item">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle">G</div>
                                <div class="ms-3">
                                    <h6 class="mb-1">Γεώργιος Γεωργίου</h6>
                                    <small class="text-muted">Αναπληρωτής Υπουργός - 13/03/2024</small>
                                </div>
                            </div>
                        </div>
                        <div class="declaration-item">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle">E</div>
                                <div class="ms-3">
                                    <h6 class="mb-1">Ελένη Παπαδοπούλου</h6>
                                    <small class="text-muted">Μελος του Κοινοβουλίου - 12/03/2024</small>
                                </div>
                            </div>
                        </div>
                        <div class="declaration-item">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle">N</div>
                                <div class="ms-3">
                                    <h6 class="mb-1">Νίκος Νικολαίου</h6>
                                    <small class="text-muted">Υπουργός - 11/03/2024</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card feature-card h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Χρειάζεστε Βοήθεια?</h5>
                        <p class="card-text">Χρειάζεστε βοήθεια με τις δηλώσεις; Μάθετε περισσότερα για το σύστημα και τις λειτουργίες του εδώ.</p>
                        <div class="d-grid gap-2">
                            <a href="contact.html" class="btn btn-primary" style="background-color: #ED9635; border-color: #ED9635;">
                                <i class="bi bi-envelope"></i> Επικοινωνία
                            </a>
                            <a href="about.html" class="btn btn-outline-primary" style="color: #ED9635; border-color: #ED9635;">
                                <i class="bi bi-info-circle"></i> Μάθε Περισσότερα
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-light py-4 mt-auto">
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
    <!-- Custom JS -->
    <script src="js/main.js"></script>
</body>
</html> 
