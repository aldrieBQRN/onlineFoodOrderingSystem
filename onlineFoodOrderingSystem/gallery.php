<?php
    include 'includes/config.php';
    $active_page = 'gallery';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/bootstrapfile/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>Gallery - Bente Sais Lomihan</title>
    <style>
        .gallery-hero {
            position: relative;
            background: url('uploads/products/68dfa41bec1d2_1759487003.jpg') center/cover no-repeat;
            color: white;
            padding: 100px 0 60px;
            margin-top: -80px;
            padding-top: 160px;
            min-height: 40vh;
        }

        .gallery-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1;
        }

        .gallery-hero .container {
            position: relative;
            z-index: 2;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .gallery-item {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            aspect-ratio: 1;
        }

        .gallery-item:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .gallery-item:hover img {
            transform: scale(1.1);
        }

        .gallery-item-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, transparent 100%);
            padding: 1.5rem;
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }

        .gallery-item:hover .gallery-item-overlay {
            transform: translateY(0);
        }

        .gallery-item-title {
            color: white;
            font-weight: 600;
            margin: 0;
            font-size: 1.1rem;
        }

        .gallery-item-category {
            color: #32cd32;
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Modal styles */
        .gallery-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.95);
            z-index: 9999;
            overflow: auto;
            animation: fadeIn 0.3s ease;
        }

        .gallery-modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .gallery-modal-content {
            position: relative;
            max-width: 90%;
            max-height: 90vh;
            margin: auto;
        }

        .gallery-modal-content img {
            width: 100%;
            height: auto;
            max-height: 85vh;
            object-fit: contain;
            border-radius: 8px;
        }

        .gallery-modal-close {
            position: absolute;
            top: -40px;
            right: 0;
            color: white;
            font-size: 2rem;
            cursor: pointer;
            background: rgba(255, 255, 255, 0.1);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s ease;
        }

        .gallery-modal-close:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .gallery-modal-info {
            text-align: center;
            color: white;
            margin-top: 1rem;
        }

        .gallery-modal-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            color: white;
            font-size: 3rem;
            cursor: pointer;
            background: rgba(255, 255, 255, 0.1);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s ease;
        }

        .gallery-modal-nav:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .gallery-modal-nav.prev {
            left: -60px;
        }

        .gallery-modal-nav.next {
            right: -60px;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @media (max-width: 768px) {
            .gallery-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 1rem;
            }

            .gallery-modal-nav {
                display: none;
            }

            .gallery-modal-content {
                max-width: 95%;
            }
        }
    </style>
</head>
<body>

    <nav class="navbar fixed-top navbar-expand-lg">
        <div class="container py-3">
            <a class="navbar-brand fw-bold fs-3" href="index.php">
                <span style="color: #32cd32;">Quick</span>Crave
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title fw-bold" id="offcanvasNavbarLabel">BENTESAIS</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body d-flex flex-column flex-lg-row align-items-lg-center">
                    <div class="d-lg-none w-100 mb-3 pb-3 border-bottom">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <div class="dropdown">
                                <a class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center w-100" href="#" id="offcanvasUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-person-circle me-2"></i> <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                                </a>
                                <ul class="dropdown-menu w-100" aria-labelledby="offcanvasUserDropdown">
                                    <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                                    <li><a class="dropdown-item" href="my_orders.php">My Orders</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="actions/logout.php">Logout</a></li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <button class="btn btn-theme rounded-pill w-100 py-2" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="offcanvas">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Login
                            </button>
                        <?php endif; ?>
                    </div>

                    <ul class="navbar-nav justify-content-center flex-grow-1">
                        <li class="nav-item"><a class="nav-link" href="about.php">About us</a></li>
                        <li class="nav-item"><a class="nav-link active" href="gallery.php">Gallery</a></li>
                        <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php">Menu</a></li>
                    </ul>

                    <div class="ms-auto d-none d-lg-block">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <div class="dropdown">
                                <a class="btn dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                    <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                                    <li><a class="dropdown-item" href="my_orders.php"><i class="bi bi-bag-check me-2"></i>My Orders</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="actions/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <button class="btn btn-theme rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#loginModal">
                                Login
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <header class="gallery-hero">
        <div class="container text-center">
            <h1 class="display-3 fw-bold mb-3">Our Gallery</h1>
            <p class="lead">Delicious moments captured in time</p>
        </div>
    </header>

    <main class="container py-5">
        <!-- Gallery Grid -->
        <div class="gallery-grid" id="galleryGrid">
            <!-- Featured Items Only - 4 Photos -->
            <div class="gallery-item fade-in" data-category="lomi">
                <img src="uploads/products/68dfa41bec1d2_1759487003.jpg" alt="Special Lomi">
                <div class="gallery-item-overlay">
                    <p class="gallery-item-category">Lomi Specials</p>
                    <h3 class="gallery-item-title">Special Lomi</h3>
                </div>
            </div>

            <div class="gallery-item fade-in" data-category="meals">
                <img src="uploads/products/68dfa41bec1d2_1759487003.jpg" alt="Tapsilog">
                <div class="gallery-item-overlay">
                    <p class="gallery-item-category">Rice Meals</p>
                    <h3 class="gallery-item-title">Tapsilog</h3>
                </div>
            </div>

            <div class="gallery-item fade-in" data-category="snacks">
                <img src="uploads/products/68dfa41bec1d2_1759487003.jpg" alt="Fresh Lumpia">
                <div class="gallery-item-overlay">
                    <p class="gallery-item-category">Snacks</p>
                    <h3 class="gallery-item-title">Fresh Lumpia</h3>
                </div>
            </div>

            <div class="gallery-item fade-in" data-category="restaurant">
                <img src="uploads/products/68dfa41bec1d2_1759487003.jpg" alt="Restaurant">
                <div class="gallery-item-overlay">
                    <p class="gallery-item-category">Our Place</p>
                    <h3 class="gallery-item-title">Cozy Dining Area</h3>
                </div>
            </div>
        </div>
    </main>

    <!-- Gallery Modal -->
    <div class="gallery-modal" id="galleryModal">
        <div class="gallery-modal-content">
            <span class="gallery-modal-close" id="modalClose">&times;</span>
            <span class="gallery-modal-nav prev" id="modalPrev">&#10094;</span>
            <img src="" alt="" id="modalImage">
            <span class="gallery-modal-nav next" id="modalNext">&#10095;</span>
            <div class="gallery-modal-info">
                <h4 id="modalTitle"></h4>
                <p id="modalCategory"></p>
            </div>
        </div>
    </div>

    <footer class="text-center py-4 mt-5">
        <div class="container">
            <p class="mb-2">&copy; 2025 Bente Sais Lomihan. All Rights Reserved.</p>
            <div>
                <a href="https://www.facebook.com/ajharafoodhaus" class="me-3"><i class="bi bi-facebook"></i></a>
                <a href="#" class="me-3"><i class="bi bi-instagram"></i></a>
                <a href="#"><i class="bi bi-twitter"></i></a>
            </div>
        </div>
    </footer>

    <?php include 'includes/modals.php'; ?>
    <script src="assets/bootstrapfile/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/script.js"></script>
    
    <script>
        // Gallery modal
        const modal = document.getElementById('galleryModal');
        const modalImage = document.getElementById('modalImage');
        const modalTitle = document.getElementById('modalTitle');
        const modalCategory = document.getElementById('modalCategory');
        const modalClose = document.getElementById('modalClose');
        const modalPrev = document.getElementById('modalPrev');
        const modalNext = document.getElementById('modalNext');
        
        let currentIndex = 0;
        let visibleItems = [];

        function updateVisibleItems() {
            visibleItems = Array.from(galleryItems).filter(item => 
                item.style.display !== 'none'
            );
        }

        galleryItems.forEach((item, index) => {
            item.addEventListener('click', () => {
                updateVisibleItems();
                currentIndex = visibleItems.indexOf(item);
                openModal(item);
            });
        });

        function openModal(item) {
            const img = item.querySelector('img');
            const title = item.querySelector('.gallery-item-title').textContent;
            const category = item.querySelector('.gallery-item-category').textContent;
            
            modalImage.src = img.src;
            modalTitle.textContent = title;
            modalCategory.textContent = category;
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            modal.classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        function showNext() {
            currentIndex = (currentIndex + 1) % visibleItems.length;
            openModal(visibleItems[currentIndex]);
        }

        function showPrev() {
            currentIndex = (currentIndex - 1 + visibleItems.length) % visibleItems.length;
            openModal(visibleItems[currentIndex]);
        }

        modalClose.addEventListener('click', closeModal);
        modalNext.addEventListener('click', showNext);
        modalPrev.addEventListener('click', showPrev);

        // Close modal on outside click
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal();
            }
        });

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (!modal.classList.contains('active')) return;
            
            if (e.key === 'Escape') closeModal();
            if (e.key === 'ArrowRight') showNext();
            if (e.key === 'ArrowLeft') showPrev();
        });

        // Scroll animations
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
    </script>
</body>
</html>