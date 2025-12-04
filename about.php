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
    <title>About Us - Quick Crave Café</title>
    <style>
        .about-hero {
            position: relative;
            /* TODO: Replace with a coffee shop background image */
            background: url('uploads/products/ChickenLomi640-1.jpg') center/cover no-repeat;
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

    <?php include 'includes/header.php'; ?>


    <header class="about-hero">
        <div class="about-hero-overlay"></div>
        <div class="container text-center">
            <h1 class="display-3 fw-bold fade-in">Our Brewing Story</h1>
            <p class="lead fw-light fade-in" style="transition-delay: 0.2s;">Discover the passion behind every cup at Quick Crave</p>
        </div>
    </header>

    <main class="py-5">
        <section class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card border-0 shadow-lg fade-in">
                        <div class="card-body p-5">
                            <div class="text-center mb-5">
                                <span class="text-uppercase fw-bold badge bg-primary bg-opacity-10 text-primary px-3 py-2 mb-3 d-inline-block">From Bean to Cup</span>
                                <h2 class="fw-bold display-5 my-3">The Art of Quick Crave</h2>
                            </div>
                            <div class="row align-items-center">
                                <div class="col-lg-6">
                                    <p class="lead text-muted mb-4">
                                        "Quick Crave" started with a simple vision: to bring the warmth of artisanal coffee to our community. It began with a passion for sourcing the finest beans and a dedication to the craft of roasting.
                                    </p>
                                    <p class="text-muted">
                                        From a small corner café, we've grown into a beloved daily stop for coffee lovers. Our mission remains simple: to serve the perfect brew—rich, aromatic, and made with precision. We are "Quick Crave," a place where great conversations start over a great cup of coffee.
                                    </p>
                                </div>
                                <div class="col-lg-6 text-center">
                                    <div class="ratio ratio-1x1 rounded-3 overflow-hidden shadow">
                                        <img src="uploads/products/ChickenLomi640-1.jpg" alt="Coffee Brewing" class="img-fluid" style="object-fit: cover;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="container-fluid py-5" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
            <div class="container">
                <div class="row g-4 text-center">
                    <div class="col-md-3 fade-in">
                        <div class="p-4">
                            <h2 class="fw-bold text-primary display-4">15+</h2>
                            <p class="text-muted fw-semibold">Years Brewing</p>
                        </div>
                    </div>
                    <div class="col-md-3 fade-in" style="transition-delay: 0.1s;">
                        <div class="p-4">
                            <h2 class="fw-bold text-primary display-4">50K+</h2>
                            <p class="text-muted fw-semibold">Cups Served</p>
                        </div>
                    </div>
                    <div class="col-md-3 fade-in" style="transition-delay: 0.2s;">
                        <div class="p-4">
                            <h2 class="fw-bold text-primary display-4">20+</h2>
                            <p class="text-muted fw-semibold">Coffee Blends</p>
                        </div>
                    </div>
                    <div class="col-md-3 fade-in" style="transition-delay: 0.3s;">
                        <div class="p-4">
                            <h2 class="fw-bold text-primary display-4">Daily</h2>
                            <p class="text-muted fw-semibold">Fresh Pastries</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="container-fluid py-5">
            <div class="container">
                <div class="text-center mb-5 fade-in">
                    <span class="text-uppercase fw-bold badge bg-primary bg-opacity-10 text-primary px-3 py-2 mb-3 d-inline-block">Our Promise</span>
                    <h2 class="fw-bold display-5 mb-3">Our Core Values</h2>
                    <p class="lead text-muted">What makes our coffee different</p>
                </div>
                <div class="row g-4 justify-content-center">
                    <div class="col-md-4 d-flex align-items-stretch">
                        <div class="value-card fade-in" style="transition-delay: 0.2s;">
                            <i class="bi bi-cup-hot"></i>
                            <h4 class="fw-semibold my-3">Premium Beans</h4>
                            <p class="text-muted">We source only the highest quality Arabica and Robusta beans from sustainable farms, ensuring a rich and complex flavor profile in every sip.</p>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-stretch">
                        <div class="value-card fade-in" style="transition-delay: 0.4s;">
                            <i class="bi bi-fire"></i>
                            <h4 class="fw-semibold my-3">Master Roasting</h4>
                            <p class="text-muted">Freshness is key. Our beans are roasted in small batches to unlock their full potential, delivering the freshest and most aromatic experience.</p>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-stretch">
                        <div class="value-card fade-in" style="transition-delay: 0.6s;">
                            <i class="bi bi-people-fill"></i>
                            <h4 class="fw-semibold my-3">Community Hub</h4>
                            <p class="text-muted">We are more than a coffee shop; we are a workspace, a meeting place, and a relaxation spot. We strive to create a warm atmosphere for everyone.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="container py-5 my-5">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6">
                    <div class="ratio ratio-16x9 rounded-3 overflow-hidden shadow-lg fade-in-left">
                        <img src="uploads/products/ChickenLomi640-1.jpg" alt="Cafe Interior" class="img-fluid" style="object-fit: cover;">
                    </div>
                </div>
                <div class="col-lg-6 fade-in-right" style="transition-delay: 0.2s;">
                    <span class="text-uppercase fw-bold badge bg-primary bg-opacity-10 text-primary px-3 py-2 mb-3 d-inline-block">Our Commitment</span>
                    <h2 class="fw-bold display-5 my-3">More Than Just Coffee</h2>
                    <p class="lead text-muted mb-4">
                        While we are famous for our espresso, our passion extends to our selection of teas, frappes, and freshly baked pastries. We are committed to providing a cozy escape from the daily grind.
                    </p>
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill text-primary me-2"></i>
                                <span class="fw-semibold">Expert Baristas</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill text-primary me-2"></i>
                                <span class="fw-semibold">Cozy Ambiance</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill text-primary me-2"></i>
                                <span class="fw-semibold">Free Wi-Fi</span>
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
                        Our team is dedicated to crafting the perfect drink, serving with a smile, and making your day a little brighter.
                    </p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="menu.php" class="btn btn-theme rounded-pill btn-lg px-4">
                            View Menu <i class="bi bi-arrow-right-short"></i>
                        </a>
                        <a href="contact.php" class="btn btn-outline-primary rounded-pill btn-lg px-4">
                            Contact Us <i class="bi bi-telephone"></i>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="container-fluid py-5" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
            <div class="container">
                <div class="text-center mb-5 fade-in">
                    <span class="text-uppercase fw-bold badge bg-primary bg-opacity-10 text-primary px-3 py-2 mb-3 d-inline-block">Meet Us</span>
                    <h2 class="fw-bold display-5 mb-3">Our Barista Team</h2>
                    <p class="lead text-muted">The artists behind your favorite drinks</p>
                </div>
                <div class="row g-4 justify-content-center">
                    <div class="col-md-4 fade-in">
                        <div class="card border-0 shadow-sm h-100 text-center">
                            <div class="card-body p-4">
                                <div class="ratio ratio-1x1 rounded-circle overflow-hidden mx-auto mb-3" style="max-width: 120px;">
                                    <img src="uploads/profile/staff.jpg" alt="Head Barista" class="img-fluid" style="object-fit: cover;">
                                </div>
                                <h5 class="fw-bold mb-1">Juan Dela Cruz</h5>
                                <p class="text-primary mb-3">Head Barista</p>
                                <p class="text-muted small">With over 10 years of experience, Juan has mastered the art of latte design and espresso extraction.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 fade-in" style="transition-delay: 0.2s;">
                        <div class="card border-0 shadow-sm h-100 text-center">
                            <div class="card-body p-4">
                                <div class="ratio ratio-1x1 rounded-circle overflow-hidden mx-auto mb-3" style="max-width: 120px;">
                                    <img src="uploads/profile/staff.jpg" alt="Café Manager" class="img-fluid" style="object-fit: cover;">
                                </div>
                                <h5 class="fw-bold mb-1">Maria Santos</h5>
                                <p class="text-primary mb-3">Café Manager</p>
                                <p class="text-muted small">Maria ensures the vibe is always relaxing and that every customer leaves with a smile and a great cup.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 fade-in" style="transition-delay: 0.4s;">
                        <div class="card border-0 shadow-sm h-100 text-center">
                            <div class="card-body p-4">
                                <div class="ratio ratio-1x1 rounded-circle overflow-hidden mx-auto mb-3" style="max-width: 120px;">
                                    <img src="uploads/profile/staff.jpg" alt="Pastry Chef" class="img-fluid" style="object-fit: cover;">
                                </div>
                                <h5 class="fw-bold mb-1">Pedro Reyes</h5>
                                <p class="text-primary mb-3">Pastry Chef</p>
                                <p class="text-muted small">Pedro pairs our coffee with delightful, freshly baked pastries and cakes every single morning.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>

    <?php include 'includes/modals.php'; ?> 
    <script src="assets/bootstrapfile/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/script.js"></script> 
</body>

</html>