<?php
   include 'includes/config.php';
    // Set active class for navigation
    $active_page = 'about';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="assets/bootstrapfile/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>About Us - Bente Sais Lomihan</title>
    <style>
        .about-hero {
            position: relative;
            background: url('uploads/products/68dfa41bec1d2_1759487003.jpg') center/cover no-repeat;
            min-height: 60vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.7);
            overflow: hidden;
            margin-top: -80px;
        }

        .about-hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1;
        }

        .about-hero .container {
            position: relative;
            z-index: 2;
            padding-top: 120px;
        }
    </style>
</head>

<body>

    <nav class="navbar fixed-top navbar-expand-lg">
        <div class="container py-3">
            <a class="navbar-brand fw-bold fs-3" href="index.php">
                <span style="color: #32cd32;">Quick</span>Crave
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar"
                aria-controls="offcanvasNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar"
                aria-labelledby="offcanvasNavbarLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title fw-bold" id="offcanvasNavbarLabel">BENTESAIS</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>

                <div class="offcanvas-body d-flex flex-column flex-lg-row align-items-lg-center">

                    <div class="d-lg-none w-100 mb-3 pb-3 border-bottom">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <div class="dropdown">
                                <a class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center w-100" href="#"
                                id="offcanvasUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-person-circle me-2"></i>
                                    <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                                </a>
                                <ul class="dropdown-menu w-100" aria-labelledby="offcanvasUserDropdown">
                                    <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                                    <li><a class="dropdown-item" href="my_orders.php">My Orders</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <button class="btn btn-theme rounded-pill w-100 py-2" data-bs-toggle="modal"
                                    data-bs-target="#loginModal" data-bs-dismiss="offcanvas">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Login
                            </button>
                        <?php endif; ?>
                    </div>

                    <ul class="navbar-nav justify-content-center flex-grow-1">
                        <li class="nav-item"><a class="nav-link active" href="about.php">About us</a></li>
                        <li class="nav-item"><a class="nav-link" href="gallery.php">Gallery</a></li>
                        <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php">Menu</a></li>
                    </ul>

                    <div class="ms-auto d-none d-lg-block">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <div class="dropdown">
                                <a class="btn dropdown-toggle d-flex align-items-center" href="#"
                                id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-person-circle me-1"></i>
                                    <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                    <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                                    <li><a class="dropdown-item" href="my_orders.php"><i class="bi bi-bag-check me-2"></i>My Orders</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="actions/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <button class="btn btn-theme rounded-pill px-3" data-bs-toggle="modal"
                                    data-bs-target="#loginModal">
                                Login
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                </div>
        </div>
    </nav>


    <header class="about-hero">
        <div class="about-hero-overlay"></div>
        <div class="container text-center">
            <h1 class="display-3 fw-bold fade-in">Our Story</h1>
            <p class="lead fw-light fade-in" style="transition-delay: 0.2s;">Discover the heart behind Bente Sais Lomihan</p>
        </div>
    </header>

    <main class="py-5">
        <!-- Story Section -->
        <section class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card border-0 shadow-lg fade-in">
                        <div class="card-body p-5">
                            <div class="text-center mb-5">
                                <span class="text-uppercase fw-bold badge bg-primary bg-opacity-10 text-primary px-3 py-2 mb-3 d-inline-block">Humble Beginnings</span>
                                <h2 class="fw-bold display-5 my-3">The Legend of Bente Sais</h2>
                            </div>
                            <div class="row align-items-center">
                                <div class="col-lg-6">
                                    <p class="lead text-muted mb-4">
                                        "Bente Sais" isn't just a name; it's our heritage. It started in a small kitchen, born from a family recipe passed down through generations. Our founder, passionate about authentic Filipino flavors, dreamt of sharing the perfect bowl of Lomi—rich, hearty, and made with love.
                                    </p>
                                    <p class="text-muted">
                                        From those humble beginnings, we've grown into a beloved community spot, but our core mission remains the same: to serve comfort in every bowl and treat every guest like family. We are "Bente Sais," a place where good food and good company come together.
                                    </p>
                                </div>
                                <div class="col-lg-6 text-center">
                                    <div class="ratio ratio-1x1 rounded-3 overflow-hidden shadow">
                                        <img src="uploads/products/68dfa41bec1d2_1759487003.jpg" alt="Traditional Cooking" class="img-fluid" style="object-fit: cover;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="container-fluid py-5" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
            <div class="container">
                <div class="row g-4 text-center">
                    <div class="col-md-3 fade-in">
                        <div class="p-4">
                            <h2 class="fw-bold text-primary display-4">15+</h2>
                            <p class="text-muted fw-semibold">Years of Service</p>
                        </div>
                    </div>
                    <div class="col-md-3 fade-in" style="transition-delay: 0.1s;">
                        <div class="p-4">
                            <h2 class="fw-bold text-primary display-4">50K+</h2>
                            <p class="text-muted fw-semibold">Happy Customers</p>
                        </div>
                    </div>
                    <div class="col-md-3 fade-in" style="transition-delay: 0.2s;">
                        <div class="p-4">
                            <h2 class="fw-bold text-primary display-4">100+</h2>
                            <p class="text-muted fw-semibold">Menu Items</p>
                        </div>
                    </div>
                    <div class="col-md-3 fade-in" style="transition-delay: 0.3s;">
                        <div class="p-4">
                            <h2 class="fw-bold text-primary display-4">24/7</h2>
                            <p class="text-muted fw-semibold">Customer Support</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Values Section -->
        <section class="container-fluid py-5">
            <div class="container">
                <div class="text-center mb-5 fade-in">
                    <span class="text-uppercase fw-bold badge bg-primary bg-opacity-10 text-primary px-3 py-2 mb-3 d-inline-block">Our Promise</span>
                    <h2 class="fw-bold display-5 mb-3">Our Core Values</h2>
                    <p class="lead text-muted">What makes us different</p>
                </div>
                <div class="row g-4 justify-content-center">
                    <div class="col-md-4 d-flex align-items-stretch">
                        <div class="value-card fade-in" style="transition-delay: 0.2s;">
                            <i class="bi bi-egg-fried"></i>
                            <h4 class="fw-semibold my-3">Authentic Flavor</h4>
                            <p class="text-muted">We honor tradition. Our recipes are authentic, using time-tested techniques and the finest local ingredients to bring you the true taste of home.</p>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-stretch">
                        <div class="value-card fade-in" style="transition-delay: 0.4s;">
                            <i class="bi bi-gem"></i>
                            <h4 class="fw-semibold my-3">Quality Ingredients</h4>
                            <p class="text-muted">Freshness is our promise. We partner with local suppliers to source the best produce and meats, ensuring every dish is fresh, wholesome, and delicious.</p>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-stretch">
                        <div class="value-card fade-in" style="transition-delay: 0.6s;">
                            <i class="bi bi-people-fill"></i>
                            <h4 class="fw-semibold my-3">Community First</h4>
                            <p class="text-muted">We are more than a restaurant; we are a neighbor. We strive to be a warm, welcoming space for families, friends, and our entire community to gather.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Commitment Section -->
        <section class="container py-5 my-5">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6">
                    <div class="ratio ratio-16x9 rounded-3 overflow-hidden shadow-lg fade-in-left">
                        <img src="uploads/products/68dfa41bec1d2_1759487003.jpg" alt="Restaurant Interior" class="img-fluid" style="object-fit: cover;">
                    </div>
                </div>
                <div class="col-lg-6 fade-in-right" style="transition-delay: 0.2s;">
                    <span class="text-uppercase fw-bold badge bg-primary bg-opacity-10 text-primary px-3 py-2 mb-3 d-inline-block">Our Commitment</span>
                    <h2 class="fw-bold display-5 my-3">More Than Just Lomi</h2>
                    <p class="lead text-muted mb-4">
                        While our name celebrates our famous Lomi, our passion extends to every dish on our menu. We are committed to providing an exceptional dining experience, whether you're joining us in-house or ordering online.
                    </p>
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill text-primary me-2"></i>
                                <span class="fw-semibold">Fast Service</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill text-primary me-2"></i>
                                <span class="fw-semibold">Friendly Staff</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill text-primary me-2"></i>
                                <span class="fw-semibold">Fresh Ingredients</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill text-primary me-2"></i>
                                <span class="fw-semibold">Online Ordering</span>
                            </div>
                        </div>
                    </div>
                    <p class="text-muted mb-4">
                        Our team is dedicated to fast service, friendly smiles, and food that warms the soul. Thank you for letting us be a part of your day.
                    </p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="index.php" class="btn btn-theme rounded-pill btn-lg px-4">
                            View Our Menu <i class="bi bi-arrow-right-short"></i>
                        </a>
                        <a href="contact.php" class="btn btn-outline-primary rounded-pill btn-lg px-4">
                            Contact Us <i class="bi bi-telephone"></i>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Team Section -->
        <section class="container-fluid py-5" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
            <div class="container">
                <div class="text-center mb-5 fade-in">
                    <span class="text-uppercase fw-bold badge bg-primary bg-opacity-10 text-primary px-3 py-2 mb-3 d-inline-block">Meet Us</span>
                    <h2 class="fw-bold display-5 mb-3">Our Passionate Team</h2>
                    <p class="lead text-muted">The people behind your favorite dishes</p>
                </div>
                <div class="row g-4 justify-content-center">
                    <div class="col-md-4 fade-in">
                        <div class="card border-0 shadow-sm h-100 text-center">
                            <div class="card-body p-4">
                                <div class="ratio ratio-1x1 rounded-circle overflow-hidden mx-auto mb-3" style="max-width: 120px;">
                                    <img src="assets/images/chef-placeholder.jpg" alt="Head Chef" class="img-fluid" style="object-fit: cover;">
                                </div>
                                <h5 class="fw-bold mb-1">Juan Dela Cruz</h5>
                                <p class="text-primary mb-3">Head Chef</p>
                                <p class="text-muted small">With over 15 years of experience, Chef Juan brings traditional Filipino flavors to life with a modern touch.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 fade-in" style="transition-delay: 0.2s;">
                        <div class="card border-0 shadow-sm h-100 text-center">
                            <div class="card-body p-4">
                                <div class="ratio ratio-1x1 rounded-circle overflow-hidden mx-auto mb-3" style="max-width: 120px;">
                                    <img src="assets/images/manager-placeholder.jpg" alt="Restaurant Manager" class="img-fluid" style="object-fit: cover;">
                                </div>
                                <h5 class="fw-bold mb-1">Maria Santos</h5>
                                <p class="text-primary mb-3">Restaurant Manager</p>
                                <p class="text-muted small">Maria ensures every guest feels at home and receives the exceptional service we're known for.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 fade-in" style="transition-delay: 0.4s;">
                        <div class="card border-0 shadow-sm h-100 text-center">
                            <div class="card-body p-4">
                                <div class="ratio ratio-1x1 rounded-circle overflow-hidden mx-auto mb-3" style="max-width: 120px;">
                                    <img src="assets/images/sous-chef-placeholder.jpg" alt="Sous Chef" class="img-fluid" style="object-fit: cover;">
                                </div>
                                <h5 class="fw-bold mb-1">Pedro Reyes</h5>
                                <p class="text-primary mb-3">Sous Chef</p>
                                <p class="text-muted small">Pedro's expertise in traditional cooking methods ensures the authenticity of every dish we serve.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="text-center py-4 mt-auto">
        <div class="container">
            <p class="mb-2">&copy; 2025 Bente Sais Lomihan. All Rights Reserved.</p>
            <div>
                <a href="#" class="me-3"><i class="bi bi-facebook"></i></a>
                <a href="#" class="me-3"><i class="bi bi-instagram"></i></a>
                <a href="#"><i class="bi bi-twitter"></i></a>
            </div>
        </div>
    </footer>

    <?php include 'includes/modals.php'; ?> 
    <script src="assets/bootstrapfile/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/script.js"></script> 
</body>

</html>