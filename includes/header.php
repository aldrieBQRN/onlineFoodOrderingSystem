<nav class="navbar fixed-top navbar-expand-lg">
        <div class="container py-3">
            <a class="navbar-brand fw-bold fs-3" href="index.php">
                <span style="color: #32cd32;">BENTE</span>SAIS
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar"
                aria-controls="offcanvasNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar"
                aria-labelledby="offcanvasNavbarLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title fw-bold" id="offcanvasNavbarLabel"> <span style="color: #32cd32;">BENTE</span>SAIS</h5>
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
                                    <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                                    <li><a class="dropdown-item" href="my_orders.php"><i class="bi bi-bag-check me-2"></i>My Orders</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="actions/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li> 
                                </ul>
                            </div>
                        <?php else: ?>
                            <div class="d-grid gap-2">
                                <button class="btn btn-theme rounded-pill w-100 py-2" data-bs-toggle="modal"
                                        data-bs-target="#loginModal" data-bs-dismiss="offcanvas">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> Login
                                </button>
                                <button class="btn btn-outline-theme rounded-pill w-100 py-2" data-bs-toggle="modal"
                                        data-bs-target="#signupModal" data-bs-dismiss="offcanvas">
                                    <i class="bi bi-person-plus me-1"></i> Register
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <ul class="navbar-nav justify-content-center flex-grow-1">
                        <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="about.php">About us</a></li>
                        <li class="nav-item"><a class="nav-link" href="gallery.php">Gallery</a></li>
                        <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                        <li class="nav-item"><a class="nav-link" href="menu.php">Menu</a></li>
                    </ul>

                    <div class="ms-auto d-none d-lg-block">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <div class="dropdown">
                                <a class="nav-link dropdown-toggle d-flex align-items-center fw-medium" href="#"
                                id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-person-circle fs-5 me-2"></i>
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
                            <div class="d-flex align-items-center gap-2">
                                <button class="btn btn-theme rounded-pill px-4" data-bs-toggle="modal"
                                        data-bs-target="#loginModal">
                                    Login
                                </button>
                                <button class="btn btn-outline-theme rounded-pill px-4" data-bs-toggle="modal"
                                        data-bs-target="#signupModal">
                                    Register
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                </div>
        </div>
    </nav>