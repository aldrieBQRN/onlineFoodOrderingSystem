<?php
    include 'config.php';
    $is_home = true; // Flag to indicate this is the home/menu page
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
    <title>Bente Sais Lomihan</title>
</head>

<body>

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
                                <i class="bi bi-box-arrow-in-right me-"></i> Login
                            </button>
                        <?php endif; ?>
                    </div>

                    <ul class="navbar-nav justify-content-center flex-grow-1">
                        <li class="nav-item"><a class="nav-link" href="about.php">About us</a></li>
                        <li class="nav-item"><a class="nav-link" href="gallery.php">Gallery</a></li>
                        <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                        <li class="nav-item"><a class="nav-link active" href="index.php">Menu</a></li>
                    </ul>

                    <div class="ms-auto d-none d-lg-block">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <div class="dropdown">
                                <a class="btn dropdown-toggle d-flex align-items-center" href="#"
                                id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-person-circle me-1"></i>
                                    <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                                </a>
                                <!-- In both desktop and mobile dropdown menus, update the dropdown items: -->
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                    <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                                    <li><a class="dropdown-item" href="my_orders.php">My Orders</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
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


    <main class="pt-3">
        <div class="bg-white text-center py-5">
            <h1 class="fw-bold">Our Menu</h1>
            <p class="text-muted">Choose your favorite dish</p>
        </div>

        <section id="menu" class="container pt-5">
            <div class="row">
                <div class="col-lg-9">
                    <?php
                        // Fetch categories
                        $category_sql = "SELECT * FROM menu_category ORDER BY category_name";
                        $categories   = $conn->query($category_sql);

                        while ($cat = $categories->fetch_assoc()) {
                            echo '<div class="px-3 mt-5">';
                            echo '<h3 class="fw-semibold mb-4">' . $cat['category_name'] . '</h3>';
                            echo '<div class="row g-4">';

                            // Fetch items per category
                            $item_sql = "SELECT * FROM menu_item WHERE category_id = " . $cat['category_id'] . " ORDER BY item_name";
                            $items    = $conn->query($item_sql);

                            while ($item = $items->fetch_assoc()) {
                                echo '
                            <div class="col-md-4">
                            <div class="card">
                                <img src="' . $item['image_url'] . '" class="card-img-top" alt="' . $item['item_name'] . '">
                                <div class="card-body text-center">
                                <h5 class="card-title fw-bold">' . $item['item_name'] . '</h5>';

                                if (! empty($item['badge'])) {
                                    echo '<span class="badge badge-theme mb-2">' . $item['badge'] . '</span>';
                                }

                                echo '
                                <p class="card-text fs-5 fw-semibold">₱' . number_format($item['price'], 2) . '</p>
                                <button class="btn btn-theme rounded-pill w-100 py-2 add-to-cart-btn"
                                        type="button"
                                        data-name="' . $item['item_name'] . '"
                                        data-price="' . $item['price'] . '">
                                    <i class="bi bi-plus-circle"></i> Add to Cart
                                </button>
                                </div>
                            </div>
                            </div>
                            ';
                            }

                            echo '</div></div>'; // close row and category section
                        }
                    ?>
                </div>

                <div class="col-lg-3">
                    <div class="cart-sidebar">
                        <div class="cart-body" id="cartBody">
                            <div class="mb-3">
                                <strong>Your Order</strong>
                            </div>
                            <div class="empty-cart" id="emptyCart">
                                <i class="bi bi-cart-x"></i>
                                <p class="mb-0">Your cart is empty</p>
                                <small class="text-muted">Add some delicious items!</small>
                            </div>
                            <div id="cartItems"></div>
                        </div>

                        <div class="cart-footer">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-bold">Total:</span>
                                <span class="fw-bold fs-5" id="cartTotal">₱0.00</span>
                            </div>
                            <button class="btn btn-theme rounded-pill w-100 py-2" id="checkoutBtn"
                                data-bs-toggle="modal" data-bs-target="#checkoutModal">
                                <i class="bi bi-credit-card"></i> Checkout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <div class="cart-btn-container d-flex align-items-center shadow-lg">
        <button class="btn d-flex align-items-center px-3 py-2 flex-grow-1 justify-content-between"
            data-bs-toggle="modal" data-bs-target="#cartModal">
            <span><i class="bi bi-cart fs-5 me-2"></i> View Cart</span>
            <span class="fw-bold" id="floatingCartTotal">₱0.00</span>
        </button>
        <button class="btn btn-theme rounded-pill px-4 py-2 ms-2" data-bs-toggle="modal"
            data-bs-target="#checkoutModal">
            Checkout
        </button>
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

    <?php include 'modals.php'; ?>
    <script src="bootstrapfile/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>

</body>

</html>