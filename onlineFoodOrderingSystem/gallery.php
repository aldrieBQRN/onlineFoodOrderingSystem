<?php
    include 'config.php';
    $active_page = 'gallery';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="bootstrapfile/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <title>Gallery - Bente Sais Lomihan</title>
</head>

<body>

    <nav class="navbar fixed-top navbar-expand-lg">
        <div class="container py-3">
            <a class="navbar-brand fw-bold" href="index.php">BENTESAIS</a>

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
                            <a class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center w-100"
                                href="#" id="offcanvasUserDropdown" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="bi bi-person-circle me-2"></i>
                                <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                            </a>
                            <ul class="dropdown-menu w-100" aria-labelledby="offcanvasUserDropdown">
                                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
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
                        <li class="nav-item"><a class="nav-link active" href="gallery.php">Gallery</a></li>
                        <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php">Menu</a></li>
                    </ul>

                    <div class="ms-auto d-none d-lg-block">
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="dropdown">
                            <a class="btn dropdown-toggle d-flex align-items-center" href="#" id="userDropdown"
                                role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle me-1"></i>
                                <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                                <li><a class="dropdown-item" href="my_orders.php">My Orders</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
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

    <main class="container py-5">
        <div class="text-center py-5">
            <h1 class="fw-bold">Our Photo Gallery</h1>
            <p class="text-muted">Draft page content for Gallery.</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <p>This section will feature a collection of high-quality images showcasing the best items on the menu,
                    the restaurant ambiance, and maybe some behind-the-scenes shots.</p>
                <div class="alert alert-info mt-4" role="alert">
                    This is a **draft page**. Image grid and carousel will be implemented here later.
                </div>
                <div class="row mt-5 g-4">
                    <div class="col-md-4">
                        <div class="bg-light p-5 border text-center">Image Placeholder 1</div>
                    </div>
                    <div class="col-md-4">
                        <div class="bg-light p-5 border text-center">Image Placeholder 2</div>
                    </div>
                    <div class="col-md-4">
                        <div class="bg-light p-5 border text-center">Image Placeholder 3</div>
                    </div>
                    <div class="col-md-4">
                        <div class="bg-light p-5 border text-center">Image Placeholder 4</div>
                    </div>
                    <div class="col-md-4">
                        <div class="bg-light p-5 border text-center">Image Placeholder 5</div>
                    </div>
                    <div class="col-md-4">
                        <div class="bg-light p-5 border text-center">Image Placeholder 6</div>
                    </div>
                </div>
            </div>
        </div>
    </main>


    <footer class="text-center py-4 mt-5">
        <div class="container">
            <p class="mb-2">&copy; 2025 Bente Sais Lomihan. All Rights Reserved.</p>
            <div>
                <a href="#" class="me-3"><i class="bi bi-facebook"></i></a>
                <a href="#" class="me-3"><i class="bi bi-instagram"></i></a>
                <a href="#"><i class="bi bi-twitter"></i></a>
            </div>
        </div>
    </footer>

    <?php include 'modals.php'; ?>
    <script src="bootstrapfile/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>

</html>