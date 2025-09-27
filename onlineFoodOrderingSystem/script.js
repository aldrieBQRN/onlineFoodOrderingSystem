        document.addEventListener('DOMContentLoaded', () => {
            let cart = [];

            // DOM elements
            const cartItems = document.getElementById('cartItems');
            const cartTotal = document.getElementById('cartTotal');
            const cartItemCount = document.getElementById('cartItemCount');
            const emptyCart = document.getElementById('emptyCart');
            
            // Mobile elements
            const mobileCartItems = document.getElementById('mobileCartItems');
            const mobileCartTotal = document.getElementById('mobileCartTotal');
            const mobileCartBadge = document.getElementById('mobileCartBadge');
            const floatingCartTotal = document.getElementById('floatingCartTotal');

            // Checkout modal elements
            const checkoutSummary = document.getElementById('checkoutSummary');
            const checkoutTotal = document.getElementById('checkoutTotal');
            const finalOrderType = document.getElementById('finalOrderType');
            const addressField = document.getElementById('addressField');

            // Add to cart functionality
            document.querySelectorAll('.add-to-cart-btn').forEach(button => {
                button.addEventListener('click', (e) => {
                    const name = e.target.dataset.name;
                    const price = parseFloat(e.target.dataset.price);
                    
                    const existingItem = cart.find(item => item.name === name);
                    
                    if (existingItem) {
                        existingItem.quantity += 1;
                    } else {
                        cart.push({ name, price, quantity: 1 });
                    }
                    
                    updateCartDisplay();
                    
                    // Add visual feedback
                    e.target.innerHTML = '<i class="bi bi-check"></i> Added!';
                    setTimeout(() => {
                        e.target.innerHTML = '<i class="bi bi-plus-circle"></i> Add to Cart';
                    }, 1000);
                });
            });

            // Toggle delivery address field based on order type
            finalOrderType.addEventListener('change', (e) => {
                if (e.target.value === 'Pickup') {
                    addressField.style.display = 'none';
                } else {
                    addressField.style.display = 'block';
                }
            });

            function updateCartDisplay() {
                const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
                const totalPrice = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                
                // Update counters and totals
                if (cartItemCount) {
                    cartItemCount.textContent = totalItems;
                }
                cartTotal.textContent = `₱${totalPrice.toFixed(2)}`;
                if (mobileCartTotal) {
                    mobileCartTotal.textContent = `₱${totalPrice.toFixed(2)}`;
                }
                if (floatingCartTotal) {
                    floatingCartTotal.textContent = `₱${totalPrice.toFixed(2)}`;
                }
                checkoutTotal.textContent = `₱${totalPrice.toFixed(2)}`;
                
                // Update mobile badge
                if (mobileCartBadge) {
                    if (totalItems > 0) {
                        mobileCartBadge.style.display = 'block';
                        mobileCartBadge.textContent = totalItems;
                    } else {
                        mobileCartBadge.style.display = 'none';
                    }
                }
                
                // Clear and repopulate cart displays
                cartItems.innerHTML = '';
                if (mobileCartItems) {
                    mobileCartItems.innerHTML = '';
                }
                checkoutSummary.innerHTML = '';
                
                if (cart.length === 0) {
                    emptyCart.style.display = 'block';
                    if (mobileCartItems) {
                        mobileCartItems.innerHTML = `
                            <div class="empty-cart text-center py-4">
                                <i class="bi bi-cart-x"></i>
                                <p class="mb-0">Your cart is empty</p>
                                <small class="text-muted">Add some delicious items!</small>
                            </div>
                        `;
                    }
                    checkoutSummary.innerHTML = '<li class="list-group-item">Your cart is empty.</li>';
                } else {
                    emptyCart.style.display = 'none';
                    
                    cart.forEach((item, index) => {
                        const cartItemHTML = `
                            <div class="cart-item">
                                <div class="cart-item-name">${item.name}</div>
                                <div class="cart-item-price">₱${item.price.toFixed(2)} each</div>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <div class="quantity-controls">
                                        <button class="quantity-btn" onclick="updateQuantity(${index}, -1)">
                                            <i class="bi bi-dash"></i>
                                        </button>
                                        <span class="fw-semibold">${item.quantity}</span>
                                        <button class="quantity-btn" onclick="updateQuantity(${index}, 1)">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                    </div>
                                    <div class="fw-bold">₱${(item.price * item.quantity).toFixed(2)}</div>
                                </div>
                            </div>
                        `;
                        
                        cartItems.innerHTML += cartItemHTML;
                        if (mobileCartItems) {
                            mobileCartItems.innerHTML += cartItemHTML;
                        }
                        
                        // Add item to checkout summary
                        const checkoutItemHTML = `
                            <li class="list-group-item d-flex justify-content-between">
                                <span>${item.quantity}x ${item.name}</span>
                                <span>₱${(item.price * item.quantity).toFixed(2)}</span>
                            </li>
                        `;
                        checkoutSummary.innerHTML += checkoutItemHTML;
                    });
                }
            }

            // Make updateQuantity globally available
            window.updateQuantity = function(index, change) {
                cart[index].quantity += change;
                
                if (cart[index].quantity <= 0) {
                    cart.splice(index, 1);
                }
                
                updateCartDisplay();
            };
        });
 