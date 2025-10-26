<?php
    include 'includes/config.php';
    $active_page = 'contact';
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
    <title>Contact Us - Bente Sais Lomihan</title>
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
                        <li class="nav-item"><a class="nav-link" href="about.php">About us</a></li>
                        <li class="nav-item"><a class="nav-link" href="gallery.php">Gallery</a></li>
                        <li class="nav-item"><a class="nav-link active" href="contact.php">Contact</a></li>
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
                            <button class="btn btn-theme rounded-pill px-4" data-bs-toggle="modal"
                                    data-bs-target="#loginModal">
                                Login
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                </div>
        </div>
    </nav>


    <main class="container py-5">
        <div class="text-center py-5 fade-in">
            <h1 class="fw-bold">Get In Touch</h1>
            <p class="text-muted fs-5">We'd love to hear from you!</p>
        </div>

        <div class="row g-5">
            <!-- Form Column -->
            <div class="col-lg-7 fade-in-left">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-4 p-md-5">
                        <h3 class="fw-bold mb-4">Send us a Message</h3>
                        
                        <!-- Form Messages -->
                        <div id="formMessages" class="mb-3"></div>

                        <!-- Contact Form -->
                        <form id="contactForm" novalidate>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="contactName" name="contactName" placeholder="Your Name" required>
                                <label for="contactName">Name</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="contactEmail" name="contactEmail" placeholder="name@example.com" required>
                                <label for="contactEmail">Email address</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="contactSubject" name="contactSubject" placeholder="e.g., Catering Inquiry" required>
                                <label for="contactSubject">Subject</label>
                            </div>
                            <div class="form-floating mb-3">
                                <textarea class="form-control" id="contactMessage" name="contactMessage" placeholder="Your Message" style="height: 150px" required></textarea>
                                <label for="contactMessage">Message</label>
                            </div>
                            <button type="submit" id="submitBtn" class="btn btn-theme w-100 py-3 rounded-pill fw-bold">
                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                Send Message
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Contact Info Column -->
            <div class="col-lg-5 fade-in-right">
                <h3 class="fw-bold mb-4">Contact Information</h3>
                <ul class="list-unstyled contact-info-list fs-5">
                    <li class="d-flex mb-3">
                        <i class="bi bi-geo-alt-fill"></i>
                        <!-- UPDATED ADDRESS -->
                        <span>F. Alix St, Nasugbu, Batangas, Philippines</span>
                    </li>
                    <li class="d-flex mb-3">
                        <i class="bi bi-telephone-fill"></i>
                        <!-- UPDATED PHONE -->
                        <span>0917 123 4567 (Example)</span>
                    </li>
                    <li class="d-flex mb-3">
                        <i class="bi bi-envelope-fill"></i>
                        <span>info@bentesais.com</span>
                    </li>
                    <li class="d-flex mb-3">
                        <i class="bi bi-clock-fill"></i>
                        <span>Mon - Sun | 10:00 AM - 10:00 PM</span>
                    </li>
                </ul>

                <h4 class="fw-bold mt-5 mb-3">Find Us Here</h4>
                <div class="ratio ratio-16x9 rounded overflow-hidden shadow-sm">
                    <!-- CORRECTED GOOGLE MAPS EMBED CODE -->
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3870.781896172986!2d120.6258135758509!3d14.067379986345645!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33bd97a50b59e595%3A0xb22497f3b410de6e!2sBente%20Sais%20Lomi%20House%20-%20Nasugbu!5e0!3m2!1sen!2sph!4v1729535712345!5m2!1sen!2sph" 
                        width="600" 
                        height="450" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </div>
    </main>


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

    <?php include 'modals.php'; ?>
    <script src="bootstrapfile/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const contactForm = document.getElementById("contactForm");
            const submitBtn = document.getElementById("submitBtn");
            const formMessages = document.getElementById("formMessages");
            const spinner = submitBtn.querySelector(".spinner-border");

            contactForm.addEventListener("submit", (e) => {
                e.preventDefault();
                
                submitBtn.disabled = true;
                spinner.classList.remove("d-none");
                formMessages.innerHTML = "";

                const formData = new FormData(contactForm);

                fetch("submit_contact.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        formMessages.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                        contactForm.reset();
                    } else {
                        formMessages.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    formMessages.innerHTML = `<div class="alert alert-danger">An unexpected error occurred. Please try again.</div>`;
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    spinner.classList.add("d-none");
                });
            });
        });
    </script>
    
    <script src="assets/js/script.js"></script> </body>
</body>

</html>
