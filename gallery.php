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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>Gallery - Quick Crave Café</title>
    <style>
        .gallery-hero {
            position: relative;
            /* TODO: Replace with a coffee shop background image */
            background: url('uploads/products/ChickenLomi640-1.jpg') center/cover no-repeat;
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

    <?php include 'includes/header.php'; ?>

    <header class="gallery-hero">
        <div class="container text-center">
            <h1 class="display-3 fw-bold mb-3">Brewing Moments</h1>
            <p class="lead">A glimpse into our coffee culture</p>
        </div>
    </header>

    <main class="container py-5">
        <div class="gallery-grid" id="galleryGrid">
            <div class="gallery-item fade-in" data-category="coffee">
                <img src="uploads/products/ChickenLomi640-1.jpg" alt="Caramel Macchiato">
                <div class="gallery-item-overlay">
                    <p class="gallery-item-category">Signature Coffee</p>
                    <h3 class="gallery-item-title">Caramel Macchiato</h3>
                </div>
            </div>

            <div class="gallery-item fade-in" data-category="pastry">
                <img src="uploads/products/ChickenLomi640-1.jpg" alt="Butter Croissant">
                <div class="gallery-item-overlay">
                    <p class="gallery-item-category">Fresh Pastries</p>
                    <h3 class="gallery-item-title">Butter Croissant</h3>
                </div>
            </div>

            <div class="gallery-item fade-in" data-category="drinks">
                <img src="uploads/products/ChickenLomi640-1.jpg" alt="Matcha Latte">
                <div class="gallery-item-overlay">
                    <p class="gallery-item-category">Frappes & Teas</p>
                    <h3 class="gallery-item-title">Matcha Latte</h3>
                </div>
            </div>

            <div class="gallery-item fade-in" data-category="ambiance">
                <img src="uploads/products/ChickenLomi640-1.jpg" alt="Coffee Bar">
                <div class="gallery-item-overlay">
                    <p class="gallery-item-category">Our Place</p>
                    <h3 class="gallery-item-title">Cozy Coffee Bar</h3>
                </div>
            </div>
        </div>
    </main>

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

    <?php include 'includes/footer.php'; ?>

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
        
        const galleryItems = document.querySelectorAll('.gallery-item'); // Define galleryItems here
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
            if (visibleItems.length === 0) return;
            currentIndex = (currentIndex + 1) % visibleItems.length;
            openModal(visibleItems[currentIndex]);
        }

        function showPrev() {
            if (visibleItems.length === 0) return;
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