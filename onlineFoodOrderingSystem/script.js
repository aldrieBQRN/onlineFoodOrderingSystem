document.addEventListener('DOMContentLoaded', () => {
    let cart = [];

    // DOM elements
    const cartItems = document.getElementById('cartItems');
    const cartTotal = document.getElementById('cartTotal');
    const emptyCart = document.getElementById('emptyCart');
    
    // Mobile elements
    const mobileCartItems = document.getElementById('mobileCartItems');
    const mobileCartTotal = document.getElementById('mobileCartTotal');
    const floatingCartTotal = document.getElementById('floatingCartTotal');

    // Checkout modal elements
    const checkoutSummary = document.getElementById('checkoutSummary');
    const checkoutTotal = document.getElementById('checkoutTotal');
    const finalOrderType = document.getElementById('finalOrderType');
    const addressField = document.getElementById('addressField');

    // Function to bind cart events
    function bindCartEvents() {
        document.querySelectorAll('.add-to-cart-btn').forEach(button => {
            // Remove existing event listeners to prevent duplicates
            button.replaceWith(button.cloneNode(true));
        });

        // Re-bind events to the new buttons
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
                const originalHTML = e.target.innerHTML;
                e.target.innerHTML = '<i class="bi bi-check"></i> Added!';
                e.target.disabled = true;
                
                setTimeout(() => {
                    e.target.innerHTML = originalHTML;
                    e.target.disabled = false;
                }, 1000);
            });
        });
    }

    // Toggle delivery address field based on order type
    if (finalOrderType && addressField) {
        finalOrderType.addEventListener('change', (e) => {
            addressField.style.display = e.target.value === 'Pickup' ? 'none' : 'block';
        });
    }

    function updateCartDisplay() {
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        const totalPrice = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        
        // Update counters and totals
        if (cartTotal) {
            cartTotal.textContent = `₱${totalPrice.toFixed(2)}`;
        }
        if (mobileCartTotal) {
            mobileCartTotal.textContent = `₱${totalPrice.toFixed(2)}`;
        }
        if (floatingCartTotal) {
            floatingCartTotal.textContent = `₱${totalPrice.toFixed(2)}`;
        }
        if (checkoutTotal) {
            checkoutTotal.textContent = `₱${totalPrice.toFixed(2)}`;
        }
        
        // Clear and repopulate cart displays
        if (cartItems) cartItems.innerHTML = '';
        if (mobileCartItems) mobileCartItems.innerHTML = '';
        if (checkoutSummary) checkoutSummary.innerHTML = '';
        
        if (cart.length === 0) {
            if (emptyCart) emptyCart.style.display = 'block';
            
            if (mobileCartItems) {
                mobileCartItems.innerHTML = `
                    <div class="empty-cart text-center py-4">
                        <i class="bi bi-cart-x"></i>
                        <p class="mb-0">Your cart is empty</p>
                        <small class="text-muted">Add some delicious items!</small>
                    </div>
                `;
            }
            if (checkoutSummary) {
                checkoutSummary.innerHTML = '<li class="list-group-item">Your cart is empty.</li>';
            }
        } else {
            if (emptyCart) emptyCart.style.display = 'none';
            
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
                
                if (cartItems) cartItems.innerHTML += cartItemHTML;
                if (mobileCartItems) mobileCartItems.innerHTML += cartItemHTML;
                
                // Add item to checkout summary
                if (checkoutSummary) {
                    checkoutSummary.innerHTML += `
                        <li class="list-group-item d-flex justify-content-between">
                            <span>${item.quantity}x ${item.name}</span>
                            <span>₱${(item.price * item.quantity).toFixed(2)}</span>
                        </li>
                    `;
                }
            });
        }
    }

    // Make updateQuantity globally available
    window.updateQuantity = function(index, change) {
        if (cart[index]) {
            cart[index].quantity += change;
            
            if (cart[index].quantity <= 0) {
                cart.splice(index, 1);
            }
            
            updateCartDisplay();
        }
    };

    // Authentication Logic
    function handleAuthResponse(data, messageDiv, form, successCallback) {
        messageDiv.classList.remove('d-none', 'alert-success', 'alert-danger');
        if (data.success) {
            messageDiv.classList.add('alert-success');
            messageDiv.textContent = data.message;
            form.reset();
            if (successCallback) successCallback(data);
        } else {
            messageDiv.classList.add('alert-danger');
            messageDiv.textContent = data.message;
        }
    }

    function updateNavbar(isLoggedIn, userName) {
        if (isLoggedIn) {
            location.reload();
        }
    }
    
    // Handle Login Form Submission
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const form = e.target;
            const messageDiv = document.getElementById('loginMessage');
            const formData = new FormData(form);

            fetch('login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                handleAuthResponse(data, messageDiv, form, () => {
                    const loginModal = bootstrap.Modal.getInstance(
                        document.getElementById('loginModal')
                    );
                    if (loginModal) loginModal.hide();
                    updateNavbar(true, data.full_name);
                });
            })
            .catch(error => {
                console.error('Login Error:', error);
                if (messageDiv) {
                    messageDiv.classList.remove('d-none', 'alert-success');
                    messageDiv.classList.add('alert-danger');
                    messageDiv.textContent =
                        'A network error occurred during login.';
                }
            });
        });
    }

    
        

    const signupForm = document.getElementById('signupForm');
    if (signupForm) {
        signupForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const form = e.target;
            const messageDiv = document.getElementById('signupMessage');
            const formData = new FormData(form);

            fetch('signup.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                handleAuthResponse(data, messageDiv, form, () => {
                    const signupModal = bootstrap.Modal.getInstance(document.getElementById('signupModal'));
                    if (signupModal) signupModal.hide();
                    setTimeout(() => {
                        const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
                        loginModal.show();
                    }, 500);
                });
            })
            .catch(error => {
                console.error('Signup Error:', error);
                if (messageDiv) {
                    messageDiv.classList.remove('d-none', 'alert-success');
                    messageDiv.classList.add('alert-danger');
                    messageDiv.textContent = 'A network error occurred during sign up.';
                }
            });
        });
    }

    // Initial bind of cart events
    bindCartEvents();

    // Fetch menu and rebind events
    fetch('fetch_menu.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            const menuSection = document.querySelector('#menu .col-lg-8');
            if (!menuSection) return;

            menuSection.innerHTML = "";

            for (const category in data) {
                const items = data[category];

                let categoryHTML = `
                    <div class="px-3 mt-5">
                        <h3 class="fw-semibold mb-4">${category}</h3>
                        <div class="row g-4">
                `;

                items.forEach(item => {
                    categoryHTML += `
                        <div class="col-md-6">
                            <div class="card">
                                <img src="${item.image_url}" class="card-img-top" alt="${item.item_name}" onerror="this.src='https://via.placeholder.com/300x200?text=Image+Not+Found'">
                                <div class="card-body text-center">
                                    <h5 class="card-title fw-bold">${item.item_name}</h5>
                                    ${item.badge ? `<span class="badge badge-theme mb-2">${item.badge}</span>` : ""}
                                    <p class="card-text fs-5 fw-semibold">₱${parseFloat(item.price).toFixed(2)}</p>
                                    <button class="btn btn-theme rounded-pill w-100 py-2 add-to-cart-btn" 
                                            type="button" 
                                            data-name="${item.item_name}" 
                                            data-price="${item.price}">
                                        <i class="bi bi-plus-circle"></i> Add to Cart
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });

                categoryHTML += "</div></div>";
                menuSection.innerHTML += categoryHTML;
            }

            // Re-bind cart events after menu is loaded
            bindCartEvents();
        })
        .catch(error => {
            console.error('Error fetching menu:', error);
        });
});