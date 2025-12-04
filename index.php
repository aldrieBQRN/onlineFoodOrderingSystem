<?php
    include 'includes/config.php';
    $is_home = true; // Flag to indicate this is the home page
   
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="assets/bootstrapfile/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>Quick Crave Café - Home</title>
    <style>
        /* Hero Section */
        .home-hero {
            position: relative;
            /* TODO: Replace with a coffee shop background image */
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), 
                        url('uploads/products/ChickenLomi640-1.jpg') center/cover no-repeat;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.7);
            overflow: hidden;
            margin-top: -80px;
            padding-top: 120px;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            max-width: 800px;
            padding: 0 20px;
        }

        .hero-badge {
            display: inline-block;
            background: rgba(50, 205, 50, 0.2);
            color: #32cd32;
            padding: 8px 20px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 20px;
            border: 1px solid rgba(50, 205, 50, 0.3);
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .hero-subtitle {
            font-size: 1.3rem;
            font-weight: 300;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .hero-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* Featured Categories */
        .categories-section {
            padding: 80px 0;
            background: #f8f9fa;
        }

        .category-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            height: 100%;
        }

        .category-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .category-image {
            height: 200px;
            overflow: hidden;
        }

        .category-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .category-card:hover .category-image img {
            transform: scale(1.1);
        }

        .category-content {
            padding: 25px;
            text-align: center;
        }

        .category-icon {
            font-size: 2.5rem;
            color: #32cd32;
            margin-bottom: 15px;
        }

        /* Special Offers */
        .offers-section {
            padding: 80px 0;
            background: linear-gradient(135deg, #32cd32 0%, #228b22 100%);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .offers-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="%23ffffff" opacity="0.05"><polygon points="1000,100 1000,0 0,100"></polygon></svg>');
            background-size: cover;
        }

        .offer-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            height: 100%;
        }

        .offer-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.15);
        }

        .offer-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            opacity: 0.9;
        }

        /* Testimonials */
        .testimonials-section {
            padding: 80px 0;
            background: #fff;
        }

        .testimonial-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid #f0f0f0;
            height: 100%;
            position: relative;
        }

        .testimonial-card::before {
            content: '"';
            position: absolute;
            top: 20px;
            right: 30px;
            font-size: 4rem;
            color: #32cd32;
            opacity: 0.2;
            font-family: serif;
        }

        .testimonial-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
        }

        .testimonial-rating {
            color: #ffc107;
            margin-bottom: 15px;
        }

        /* CTA Section */
        .cta-section {
            padding: 100px 0;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            text-align: center;
        }

        .cta-card {
            background: white;
            border-radius: 20px;
            padding: 60px 40px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 0 auto;
        }

        /* Enhanced Responsive Design */
        @media (max-width: 1200px) {
            .hero-title {
                font-size: 3rem;
            }
            
            .category-content {
                padding: 20px;
            }
        }

        @media (max-width: 992px) {
            .hero-title {
                font-size: 2.8rem;
            }
            
            .hero-subtitle {
                font-size: 1.2rem;
            }
            
            .categories-section,
            .offers-section,
            .testimonials-section {
                padding: 60px 0;
            }
            
            .category-image {
                height: 180px;
            }
            
            .category-icon,
            .offer-icon {
                font-size: 2.2rem;
            }
            
            .cta-section {
                padding: 80px 0;
            }
            
            .cta-card {
                padding: 50px 30px;
            }
        }

        @media (max-width: 768px) {
            .home-hero {
                min-height: 80vh;
                padding-top: 100px;
                margin-top: -70px;
            }
            
            .hero-title {
                font-size: 2.2rem;
                margin-bottom: 15px;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
                margin-bottom: 25px;
            }
            
            .hero-buttons {
                flex-direction: column;
                align-items: center;
                gap: 12px;
            }
            
            .hero-buttons .btn {
                width: 100%;
                max-width: 280px;
                margin: 0 auto;
            }
            
            .categories-section,
            .offers-section,
            .testimonials-section {
                padding: 50px 0;
            }
            
            .category-card,
            .offer-card,
            .testimonial-card {
                margin-bottom: 20px;
            }
            
            .category-image {
                height: 160px;
            }
            
            .category-content,
            .offer-card,
            .testimonial-card {
                padding: 20px;
            }
            
            .category-icon,
            .offer-icon {
                font-size: 2rem;
                margin-bottom: 15px;
            }
            
            .testimonial-card::before {
                font-size: 3rem;
                top: 15px;
                right: 20px;
            }
            
            .testimonial-avatar {
                width: 50px;
                height: 50px;
            }
            
            .cta-section {
                padding: 60px 0;
            }
            
            .cta-card {
                padding: 40px 25px;
                border-radius: 16px;
            }
            
            .display-5 {
                font-size: 2rem;
            }
            
            .lead {
                font-size: 1rem;
            }
        }

        @media (max-width: 576px) {
            .home-hero {
                min-height: 70vh;
                padding-top: 90px;
                margin-top: -60px;
            }
            
            .hero-content {
                padding: 0 15px;
            }
            
            .hero-title {
                font-size: 1.8rem;
            }
            
            .hero-subtitle {
                font-size: 1rem;
            }
            
            .hero-badge {
                font-size: 0.8rem;
                padding: 6px 15px;
                margin-bottom: 15px;
            }
            
            .categories-section,
            .offers-section,
            .testimonials-section {
                padding: 40px 0;
            }
            
            .category-image {
                height: 140px;
            }
            
            .category-content,
            .offer-card,
            .testimonial-card {
                padding: 15px;
            }
            
            .category-icon,
            .offer-icon {
                font-size: 1.8rem;
            }
            
            .testimonial-card::before {
                font-size: 2.5rem;
                top: 10px;
                right: 15px;
            }
            
            .testimonial-avatar {
                width: 45px;
                height: 45px;
                margin-right: 10px;
            }
            
            .cta-section {
                padding: 50px 0;
            }
            
            .cta-card {
                padding: 30px 20px;
                margin: 0 15px;
            }
            
            .display-5 {
                font-size: 1.8rem;
            }
            
            .container {
                padding-left: 15px;
                padding-right: 15px;
            }
            
            .btn-lg {
                padding: 0.75rem 1.5rem;
                font-size: 1rem;
            }
        }

        @media (max-width: 400px) {
            .hero-title {
                font-size: 1.6rem;
            }
            
            .hero-subtitle {
                font-size: 0.95rem;
            }
            
            .hero-buttons .btn {
                font-size: 0.9rem;
                padding: 0.7rem 1.2rem;
            }
            
            .category-content h4,
            .offer-card h4,
            .testimonial-card h5 {
                font-size: 1.1rem;
            }
            
            .d-flex.gap-3 {
                flex-direction: column;
                align-items: center;
            }
            
            .d-flex.gap-3 .btn {
                width: 100%;
                max-width: 250px;
            }
        }

        /* Touch device optimizations */
        @media (hover: none) and (pointer: coarse) {
            .category-card:hover {
                transform: none;
            }
            
            .offer-card:hover {
                transform: none;
                background: rgba(255, 255, 255, 0.1);
            }
            
            .category-card:hover .category-image img {
                transform: none;
            }
        }

        /* High DPI screen optimizations */
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .home-hero {
                /* TODO: Replace with a high-res coffee shop background image */
                background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), 
                            url('uploads/products/ChickenLomi640-1.jpg') center/cover no-repeat;
            }
        }
    </style>
</head>

<body>

    <?php include 'includes/header.php'; ?>

    <section class="home-hero">
        <div class="hero-content">
            <span class="hero-badge">Since 2008</span>
            <h1 class="hero-title">Wake Up to the Perfect Brew</h1>
            <p class="hero-subtitle">Experience artisanal coffee, freshly baked pastries, and the cozy atmosphere of Quick Crave Café.</p>
            <div class="hero-buttons">
                <a href="menu.php" class="btn btn-theme btn-lg rounded-pill px-4 py-2">
                    <i class="bi bi-cup-hot me-2"></i> Order Now
                </a>
                <a href="about.php" class="btn btn-outline-light btn-lg rounded-pill px-4 py-2">
                    <i class="bi bi-play-circle me-2"></i> Our Story
                </a>
            </div>
        </div>
    </section>

    <section class="categories-section" id="menu">
        <div class="container">
            <div class="text-center mb-5 fade-in">
                <span class="text-uppercase fw-bold badge bg-primary bg-opacity-10 text-primary px-3 py-2 mb-3 d-inline-block">Our Menu</span>
                <h2 class="fw-bold display-5 mb-3">Featured Categories</h2>
                <p class="lead text-muted">Discover our daily roasted blends and treats</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4 col-sm-6 col-12 fade-in">
                    <div class="category-card">
                        <div class="category-image">
                            <img src="uploads/products/ChickenLomi640-1.jpg" alt="Signature Coffee">
                        </div>
                        <div class="category-content">
                            <i class="bi bi-cup-hot category-icon"></i>
                            <h4 class="fw-bold mb-3">Signature Coffee</h4>
                            <p class="text-muted">From robust espressos to creamy lattes, brewed from the finest locally sourced beans.</p>
                            <a href="menu.php" class="btn btn-outline-primary rounded-pill mt-2">View All</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 col-sm-6 col-12 fade-in" style="transition-delay: 0.1s;">
                    <div class="category-card">
                        <div class="category-image">
                            <img src="uploads/products/ChickenLomi640-1.jpg" alt="Pastries">
                        </div>
                        <div class="category-content">
                            <i class="bi bi-cookie category-icon"></i>
                            <h4 class="fw-bold mb-3">Fresh Pastries</h4>
                            <p class="text-muted">Croissants, muffins, and cakes baked fresh every morning to pair perfectly with your cup.</p>
                            <a href="menu.php" class="btn btn-outline-primary rounded-pill mt-2">View All</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 col-sm-6 col-12 fade-in" style="transition-delay: 0.2s;">
                    <div class="category-card">
                        <div class="category-image">
                            <img src="uploads/products/ChickenLomi640-1.jpg" alt="Frappes & Teas">
                        </div>
                        <div class="category-content">
                            <i class="bi bi-cup-straw category-icon"></i>
                            <h4 class="fw-bold mb-3">Frappes & Teas</h4>
                            <p class="text-muted">Cool down with our refreshing blended frappes or relax with our premium tea selection.</p>
                            <a href="menu.php" class="btn btn-outline-primary rounded-pill mt-2">View All</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-5">
                <a href="menu.php" class="btn btn-theme rounded-pill px-4 py-2">
                    <i class="bi bi-menu-button-wide me-2"></i> View Full Menu
                </a>
            </div>
        </div>
    </section>

    <section class="offers-section">
        <div class="container">
            <div class="text-center mb-5 fade-in">
                <span class="text-uppercase fw-bold badge bg-white bg-opacity-20 text-black px-3 py-2 mb-3 d-inline-block">Limited Time</span>
                <h2 class="fw-bold display-5 mb-3 text-white">Cafe Specials</h2>
                <p class="lead text-white opacity-75">Start your day right with these deals</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4 col-sm-6 col-12 fade-in">
                    <div class="offer-card">
                        <i class="bi bi-sun offer-icon"></i>
                        <h4 class="fw-bold mb-3">Morning Kickstart</h4>
                        <p class="mb-3 opacity-90">Get a free muffin with any Grande sized coffee purchase before 10 AM.</p>
                        <span class="badge bg-white text-success px-3 py-2">Save ₱45</span>
                    </div>
                </div>
                
                <div class="col-md-4 col-sm-6 col-12 fade-in" style="transition-delay: 0.1s;">
                    <div class="offer-card">
                        <i class="bi bi-truck offer-icon"></i>
                        <h4 class="fw-bold mb-3">Office Delivery</h4>
                        <p class="mb-3 opacity-90">Free delivery for bulk coffee orders (min. ₱500) within the business district.</p>
                        <span class="badge bg-white text-success px-3 py-2">No Minimum</span>
                    </div>
                </div>
                
                <div class="col-md-4 col-sm-6 col-12 fade-in" style="transition-delay: 0.2s;">
                    <div class="offer-card">
                        <i class="bi bi-gift offer-icon"></i>
                        <h4 class="fw-bold mb-3">Bean Rewards</h4>
                        <p class="mb-3 opacity-90">Collect beans with every purchase! 10 beans = 1 Free Coffee of your choice.</p>
                        <span class="badge bg-white text-success px-3 py-2">Join Now</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="testimonials-section">
        <div class="container">
            <div class="text-center mb-5 fade-in">
                <span class="text-uppercase fw-bold badge bg-primary bg-opacity-10 text-primary px-3 py-2 mb-3 d-inline-block">Testimonials</span>
                <h2 class="fw-bold display-5 mb-3">Coffee Talk</h2>
                <p class="lead text-muted">What our regulars are sipping</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4 col-sm-6 col-12 fade-in">
                    <div class="testimonial-card">
                        <div class="d-flex align-items-center mb-4">
                            <img src="uploads/profile/staff.jpg" alt="Customer" class="testimonial-avatar">
                            <div>
                                <h5 class="fw-bold mb-0">Maria Santos</h5>
                                <p class="text-muted mb-0">Daily Regular</p>
                            </div>
                        </div>
                        <div class="testimonial-rating">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <p class="text-muted">"The Caramel Macchiato here is hands down the best in town. Not too sweet, perfect espresso shot, and the foam is just right. My go-to morning spot!"</p>
                    </div>
                </div>
                
                <div class="col-md-4 col-sm-6 col-12 fade-in" style="transition-delay: 0.1s;">
                    <div class="testimonial-card">
                        <div class="d-flex align-items-center mb-4">
                            <img src="uploads/profile/staff.jpg" alt="Customer" class="testimonial-avatar">
                            <div>
                                <h5 class="fw-bold mb-0">Juan Dela Cruz</h5>
                                <p class="text-muted mb-0">Remote Worker</p>
                            </div>
                        </div>
                        <div class="testimonial-rating">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-half"></i>
                        </div>
                        <p class="text-muted">"Great atmosphere for working! The WiFi is fast, and their Ham & Cheese Croissant is the perfect snack to keep me going. Staff is super friendly too."</p>
                    </div>
                </div>
                
                <div class="col-md-4 col-sm-6 col-12 fade-in" style="transition-delay: 0.2s;">
                    <div class="testimonial-card">
                        <div class="d-flex align-items-center mb-4">
                            <img src="uploads/profile/staff.jpg" alt="Customer" class="testimonial-avatar">
                            <div>
                                <h5 class="fw-bold mb-0">Ana Reyes</h5>
                                <p class="text-muted mb-0">Lifestyle Blogger</p>
                            </div>
                        </div>
                        <div class="testimonial-rating">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <p class="text-muted">"I love the aesthetic of Quick Crave Café. It's so cozy and Instagrammable. Plus, their Matcha Latte is authentic and delicious. Highly recommended!"</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="cta-section">
        <div class="container">
            <div class="cta-card fade-in">
                <span class="text-uppercase fw-bold badge bg-primary bg-opacity-10 text-primary px-3 py-2 mb-3 d-inline-block">Craving Coffee?</span>
                <h2 class="fw-bold display-5 mb-3">Your Daily Dose of Happiness</h2>
                <p class="lead text-muted mb-4">Join hundreds of coffee lovers who start their day with Quick Crave. Freshly brewed, served with a smile.</p>
                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    <a href="menu.php" class="btn btn-theme rounded-pill btn-lg px-4 py-2">
                        <i class="bi bi-cup-hot me-2"></i> Order Now
                    </a>
                    <a href="contact.php" class="btn btn-outline-primary rounded-pill btn-lg px-4 py-2">
                        <i class="bi bi-telephone me-2"></i> Contact Us
                    </a>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <?php include 'includes/modals.php'; ?>
    <script src="assets/bootstrapfile/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/script.js"></script>
    
    <script>
        // Scroll animations
        document.addEventListener('DOMContentLoaded', function() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, { threshold: 0.1 });

            document.querySelectorAll('.fade-in').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(el);
            });
        });
    </script>
</body>

</html>