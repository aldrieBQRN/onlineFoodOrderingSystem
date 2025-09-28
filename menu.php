<div class="bg-white text-center py-5">
        <h1 class="fw-bold">Our Menu</h1>
        <p class="text-muted">Choose your favorite dish</p>
    </div>

<section id="menu" class="container pt-5">
    <div class="row">
        <div class="col-lg-8">
            <?php
            // Fetch categories
            $category_sql = "SELECT * FROM menu_category ORDER BY category_name";
            $categories = $conn->query($category_sql);

            while ($cat = $categories->fetch_assoc()) {
                echo '<div class="px-3 mt-5">';
                echo '<h3 class="fw-semibold mb-4">'. $cat['category_name'] .'</h3>';
                echo '<div class="row g-4">';

                // Fetch items per category
                $item_sql = "SELECT * FROM menu_item WHERE category_id = ".$cat['category_id']." ORDER BY item_name";
                $items = $conn->query($item_sql);

                while ($item = $items->fetch_assoc()) {
                    echo '
                    <div class="col-md-6">
                    <div class="card">
                        <img src="'. $item['image_url'] .'" class="card-img-top" alt="'. $item['item_name'] .'">
                        <div class="card-body text-center">
                        <h5 class="card-title fw-bold">'. $item['item_name'] .'</h5>';
                        
                        if (!empty($item['badge'])) {
                            echo '<span class="badge badge-theme mb-2">'. $item['badge'] .'</span>';
                        }
                        
                        echo '
                        <p class="card-text fs-5 fw-semibold">₱'. number_format($item['price'], 2) .'</p>
                        <button class="btn btn-theme rounded-pill w-100 py-2 add-to-cart-btn" 
                                type="button" 
                                data-name="'. $item['item_name'] .'" 
                                data-price="'. $item['price'] .'">
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

        <div class="col-lg-4">
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
                    <button class="btn btn-theme rounded-pill w-100 py-2" id="checkoutBtn" data-bs-toggle="modal" data-bs-target="#checkoutModal">
                        <i class="bi bi-credit-card"></i> Checkout
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>