<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ΠΟΘΕΝ ΕΣΧΕΣ - Asset Declaration System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Flag Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css"/>
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
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
            <a class="navbar-brand d-flex align-items-center" href="index.html">
                <img src="assets/images/logo.jpg" alt="ΠΟΘΕΝ ΕΣΧΕΣ Logo" height="40" class="me-3">
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
                        <a class="nav-link" href="index.html">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="search.html">Search</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="modules/search_module/statistics.php">Statistics</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="declaration-form.html">Submit</a>
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
                                <li><a class="dropdown-item" href="modules/login_module/login.php"><i class="bi bi-box-arrow-in-right"></i> Login</a></li>
                                <li><a class="dropdown-item" href="modules/login_module/register.php"><i class="bi bi-person-plus"></i> Register</a></li>
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
                            <a class="nav-link d-flex align-items-center gap-2 mb-2" href="index.html">
                                <i class="bi bi-house"></i> Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 mb-2" href="search.html">
                                <i class="bi bi-search"></i> Search
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 mb-2" href="statistics.html">
                                <i class="bi bi-graph-up"></i> Statistics
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 mb-3" href="declaration-form.html">
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
                                <a href="module/login_module/login.php" class="nav-link py-2">
                                    <i class="bi bi-box-arrow-in-right me-2"></i> Login
                                </a>
                                <a href="module/login_module/register.php" class="nav-link py-2">
                                    <i class="bi bi-person-plus me-2"></i> Register
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
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 col-md-8">
                    <h1 class="hero-title">Monitor Asset Declarations</h1>
                    <p class="hero-subtitle">Subtitle</p>
                    <div class="d-flex flex-column flex-sm-row gap-3">
                        <a href="search.html" class="btn btn-light btn-lg px-4">Search Declarations</a>
                        <a href="register.html" class="btn btn-outline-light btn-lg px-4">Get Started</a>
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
                    <div class="stat-label">Public Officials</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card text-center h-100">
                    <div class="stat-number">8</div>
                    <div class="stat-label">Political Parties</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card text-center h-100">
                    <div class="stat-number">2025</div>
                    <div class="stat-label">Latest Declarations</div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-search feature-icon"></i>
                        <h5 class="card-title">Public Search</h5>
                        <p class="card-text">Text</p>
                        <a href="search.html" class="btn btn-outline-primary" style="color: #ED9635; border-color: #ED9635;">Search Now</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-file-earmark-text feature-icon"></i>
                        <h5 class="card-title">Declaration Submission</h5>
                        <p class="card-text">Text</p>
                        <a href="declaration-form.html" class="btn btn-outline-primary" style="color: #ED9635; border-color: #ED9635;">Submit Declaration</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-graph-up feature-icon"></i>
                        <h5 class="card-title">Statistics & Analysis</h5>
                        <p class="card-text">Text</p>
                        <a href="statistics.html" class="btn btn-outline-primary" style="color: #ED9635; border-color: #ED9635;">View Statistics</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Latest Declarations -->
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card feature-card h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Recent Declarations</h5>
                        <div class="declaration-item">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle">A</div>
                                <div class="ms-3">
                                    <h6 class="mb-1">Name</h6>
                                    <small class="text-muted">Member of Parliament - 15/03/2024</small>
                                </div>
                            </div>
                        </div>
                        <div class="declaration-item">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle">M</div>
                                <div class="ms-3">
                                    <h6 class="mb-1">Name</h6>
                                    <small class="text-muted">Minister - 14/03/2024</small>
                                </div>
                            </div>
                        </div>
                        <div class="declaration-item">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle">G</div>
                                <div class="ms-3">
                                    <h6 class="mb-1">Name</h6>
                                    <small class="text-muted">Deputy Minister - 13/03/2024</small>
                                </div>
                            </div>
                        </div>
                        <div class="declaration-item">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle">E</div>
                                <div class="ms-3">
                                    <h6 class="mb-1">Name</h6>
                                    <small class="text-muted">Member of Parliament - 12/03/2024</small>
                                </div>
                            </div>
                        </div>
                        <div class="declaration-item">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle">N</div>
                                <div class="ms-3">
                                    <h6 class="mb-1">Name</h6>
                                    <small class="text-muted">Minister - 11/03/2024</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card feature-card h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Need Help?</h5>
                        <p class="card-text">Get assistance with declarations or learn more about the system's features and requirements.</p>
                        <div class="d-grid gap-2">
                            <a href="contact.html" class="btn btn-primary" style="background-color: #ED9635; border-color: #ED9635;">
                                <i class="bi bi-envelope"></i> Contact Support
                            </a>
                            <a href="about.html" class="btn btn-outline-primary" style="color: #ED9635; border-color: #ED9635;">
                                <i class="bi bi-info-circle"></i> Learn More
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
                    <p class="mb-0">&copy; 2025 Asset Declaration System. All rights reserved.</p>
                </div>
                <div class="col-12 col-md-6 text-center text-md-end">
                    <div class="d-flex justify-content-center justify-content-md-end gap-3">
                        <a href="about.html" class="text-decoration-none">About</a>
                        <a href="contact.html" class="text-decoration-none">Contact</a>
                        <a href="privacy.html" class="text-decoration-none">Privacy Policy</a>
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
