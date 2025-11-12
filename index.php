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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>Quick Crave Lomihan - Home</title>
    <style>
        /* Hero Section */
        .home-hero {
            position: relative;
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
                background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), 
                            url('uploads/products/ChickenLomi640-1.jpg') center/cover no-repeat;
            }
        }
    </style>
</head>

<body>

    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="home-hero">
        <div class="hero-content">
            <span class="hero-badge">Since 2008</span>
            <h1 class="hero-title">Authentic Filipino Flavors in Every Bowl</h1>
            <p class="hero-subtitle">Experience the rich, hearty taste of traditional Lomi and more at Quick Crave Lomihan</p>
            <div class="hero-buttons">
                <a href="#menu" class="btn btn-theme btn-lg rounded-pill px-4 py-2">
                    <i class="bi bi-utensils me-2"></i> Order Now
                </a>
                <a href="about.php" class="btn btn-outline-light btn-lg rounded-pill px-4 py-2">
                    <i class="bi bi-play-circle me-2"></i> Our Story
                </a>
            </div>
        </div>
    </section>

    <!-- Featured Categories -->
    <section class="categories-section" id="menu">
        <div class="container">
            <div class="text-center mb-5 fade-in">
                <span class="text-uppercase fw-bold badge bg-primary bg-opacity-10 text-primary px-3 py-2 mb-3 d-inline-block">Our Specialties</span>
                <h2 class="fw-bold display-5 mb-3">Featured Categories</h2>
                <p class="lead text-muted">Discover our most popular dishes</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4 col-sm-6 col-12 fade-in">
                    <div class="category-card">
                        <div class="category-image">
                            <img src="uploads/products/ChickenLomi640-1.jpg" alt="Lomi Specials">
                        </div>
                        <div class="category-content">
                            <i class="bi bi-egg-fried category-icon"></i>
                            <h4 class="fw-bold mb-3">Lomi Specials</h4>
                            <p class="text-muted">Our signature Lomi bowls, rich in flavor and made with love using traditional recipes.</p>
                            <a href="index.php?category=lomi" class="btn btn-outline-primary rounded-pill mt-2">View All</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 col-sm-6 col-12 fade-in" style="transition-delay: 0.1s;">
                    <div class="category-card">
                        <div class="category-image">
                            <img src="uploads/products/ChickenLomi640-1.jpg" alt="Rice Meals">
                        </div>
                        <div class="category-content">
                            <i class="bi bi-egg category-icon"></i>
                            <h4 class="fw-bold mb-3">Rice Meals</h4>
                            <p class="text-muted">Hearty Filipino rice dishes perfect for any time of day, from breakfast to dinner.</p>
                            <a href="index.php?category=rice" class="btn btn-outline-primary rounded-pill mt-2">View All</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 col-sm-6 col-12 fade-in" style="transition-delay: 0.2s;">
                    <div class="category-card">
                        <div class="category-image">
                            <img src="uploads/products/ChickenLomi640-1.jpg" alt="Appetizers">
                        </div>
                        <div class="category-content">
                            <i class="bi bi-cup-straw category-icon"></i>
                            <h4 class="fw-bold mb-3">Appetizers & Snacks</h4>
                            <p class="text-muted">Perfect starters and light bites to complement your main course.</p>
                            <a href="index.php?category=snacks" class="btn btn-outline-primary rounded-pill mt-2">View All</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-5">
                <a href="index.php" class="btn btn-theme rounded-pill px-4 py-2">
                    <i class="bi bi-menu-button-wide me-2"></i> View Full Menu
                </a>
            </div>
        </div>
    </section>

    <!-- Special Offers -->
    <section class="offers-section">
        <div class="container">
            <div class="text-center mb-5 fade-in">
                <span class="text-uppercase fw-bold badge bg-white bg-opacity-20 text-black px-3 py-2 mb-3 d-inline-block">Limited Time</span>
                <h2 class="fw-bold display-5 mb-3 text-white">Special Offers</h2>
                <p class="lead text-white opacity-75">Don't miss out on these exclusive deals</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4 col-sm-6 col-12 fade-in">
                    <div class="offer-card">
                        <i class="bi bi-percent offer-icon"></i>
                        <h4 class="fw-bold mb-3">Family Bundle</h4>
                        <p class="mb-3 opacity-90">Get 20% off when you order our special family bundle. Perfect for 4-5 people!</p>
                        <span class="badge bg-white text-success px-3 py-2">Save ₱200</span>
                    </div>
                </div>
                
                <div class="col-md-4 col-sm-6 col-12 fade-in" style="transition-delay: 0.1s;">
                    <div class="offer-card">
                        <i class="bi bi-truck offer-icon"></i>
                        <h4 class="fw-bold mb-3">Free Delivery</h4>
                        <p class="mb-3 opacity-90">Enjoy free delivery on orders over ₱500 within Nasugbu area. Limited time only!</p>
                        <span class="badge bg-white text-success px-3 py-2">No Minimum</span>
                    </div>
                </div>
                
                <div class="col-md-4 col-sm-6 col-12 fade-in" style="transition-delay: 0.2s;">
                    <div class="offer-card">
                        <i class="bi bi-gift offer-icon"></i>
                        <h4 class="fw-bold mb-3">Loyalty Rewards</h4>
                        <p class="mb-3 opacity-90">Earn points with every purchase and redeem for free items on your next visit.</p>
                        <span class="badge bg-white text-success px-3 py-2">Join Now</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials-section">
        <div class="container">
            <div class="text-center mb-5 fade-in">
                <span class="text-uppercase fw-bold badge bg-primary bg-opacity-10 text-primary px-3 py-2 mb-3 d-inline-block">Testimonials</span>
                <h2 class="fw-bold display-5 mb-3">What Our Customers Say</h2>
                <p class="lead text-muted">Real feedback from our valued customers</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4 col-sm-6 col-12 fade-in">
                    <div class="testimonial-card">
                        <div class="d-flex align-items-center mb-4">
                            <img src="uploads/profile/staff.jpg" alt="Customer" class="testimonial-avatar">
                            <div>
                                <h5 class="fw-bold mb-0">Maria Santos</h5>
                                <p class="text-muted mb-0">Regular Customer</p>
                            </div>
                        </div>
                        <div class="testimonial-rating">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <p class="text-muted">"The Special Lomi here is the best I've ever tasted! The broth is rich and flavorful, and the ingredients are always fresh. My family and I come here every weekend."</p>
                    </div>
                </div>
                
                <div class="col-md-4 col-sm-6 col-12 fade-in" style="transition-delay: 0.1s;">
                    <div class="testimonial-card">
                        <div class="d-flex align-items-center mb-4">
                            <img src="uploads/profile/staff.jpg" alt="Customer" class="testimonial-avatar">
                            <div>
                                <h5 class="fw-bold mb-0">Juan Dela Cruz</h5>
                                <p class="text-muted mb-0">First-time Visitor</p>
                            </div>
                        </div>
                        <div class="testimonial-rating">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-half"></i>
                        </div>
                        <p class="text-muted">"I was recommended by a friend and I'm so glad I tried this place. The Tapsilog was amazing and the service was fast and friendly. Will definitely be back!"</p>
                    </div>
                </div>
                
                <div class="col-md-4 col-sm-6 col-12 fade-in" style="transition-delay: 0.2s;">
                    <div class="testimonial-card">
                        <div class="d-flex align-items-center mb-4">
                            <img src="uploads/profile/staff.jpg" alt="Customer" class="testimonial-avatar">
                            <div>
                                <h5 class="fw-bold mb-0">Ana Reyes</h5>
                                <p class="text-muted mb-0">Food Blogger</p>
                            </div>
                        </div>
                        <div class="testimonial-rating">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <p class="text-muted">"As a food blogger, I've tried many Lomi places, but Quick Crave stands out. The authentic taste and cozy atmosphere make it my top recommendation in Nasugbu."</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-card fade-in">
                <span class="text-uppercase fw-bold badge bg-primary bg-opacity-10 text-primary px-3 py-2 mb-3 d-inline-block">Ready to Order?</span>
                <h2 class="fw-bold display-5 mb-3">Experience the Taste of Tradition</h2>
                <p class="lead text-muted mb-4">Join hundreds of satisfied customers who have made Quick Crave their go-to spot for authentic Filipino comfort food.</p>
                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    <a href="index.php" class="btn btn-theme rounded-pill btn-lg px-4 py-2">
                        <i class="bi bi-utensils me-2"></i> Order Now
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