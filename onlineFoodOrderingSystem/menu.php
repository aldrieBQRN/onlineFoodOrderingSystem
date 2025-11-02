<?php
    include 'includes/config.php'; // <-- CORRECT
    $active_page = 'menu';
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
    <title>Our Menu</title>
</head>

<body>

    <?php include 'includes/header.php'; ?>

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

                            echo '</div></div>'; 
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


    <?php include 'includes/footer.php'; // <-- CORRECT ?>
    <?php include 'includes/modals.php'; // <-- CORRECT ?>

    <script src="assets/bootstrapfile/js/bootstrap.bundle.min.js"></script> 
     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/script.js"></script> 
</body>

</html>