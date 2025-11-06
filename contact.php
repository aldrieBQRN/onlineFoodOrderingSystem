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
    <title>Contact Us</title>
</head>

<body>

    <?php include 'includes/header.php'; ?>

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


    <?php include 'includes/footer.php'; ?>

    <?php include 'includes/modals.php'; ?>
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
