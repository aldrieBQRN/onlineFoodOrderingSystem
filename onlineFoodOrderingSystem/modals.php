<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loginModalLabel">Login</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="loginForm">
                    <div class="mb-3">
                        <label for="loginEmail" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="loginEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="loginPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="loginPassword" name="password" required>
                    </div>
                    <div id="loginMessage" class="alert d-none" role="alert"></div>
                    <button type="submit" class="btn btn-theme w-100 py-2 rounded-pill">Login</button>
                </form>
                <p class="text-center mt-3">
                    Don't have an account? <a href="#" data-bs-toggle="modal" data-bs-target="#signupModal" data-bs-dismiss="modal">Sign up here</a>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="signupModal" tabindex="-1" aria-labelledby="signupModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="signupModalLabel">Sign Up</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="signupForm">
                    <div class="mb-3">
                        <label for="signupFullName" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="signupFullName" name="full_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="signupEmail" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="signupEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="signupPhone" class="form-label">Contact Number</label>
                        <input type="text" class="form-control" id="signupPhone" name="phone">
                    </div>
                    <div class="mb-3">
                        <label for="signupPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="signupPassword" name="password" required>
                    </div>
                    <div id="signupMessage" class="alert d-none" role="alert"></div>
                    <button type="submit" class="btn btn-theme w-100 py-2 rounded-pill">Sign Up</button>
                </form>
                <p class="text-center mt-3">
                    Already have an account? <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">Login here</a>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cartModalLabel">Your Cart</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="mobileCartItems"></div>
                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                    <span class="fw-bold fs-5">Total:</span>
                    <span class="fw-bold fs-5" id="mobileCartTotal">₱0.00</span>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-theme rounded-pill w-100 py-2" data-bs-toggle="modal" data-bs-target="#checkoutModal" data-bs-dismiss="modal">
                    <i class="bi bi-credit-card"></i> Proceed to Checkout
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="checkoutModalLabel">Checkout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Order Type</label>
                    <select class="form-select" id="finalOrderType">
                        <option value="Delivery">Delivery</option>
                        <option value="Pickup">Pick-up</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Full Name</label>
                    <input type="text" class="form-control" placeholder="Enter your name">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Contact Number</label>
                    <input type="text" class="form-control" placeholder="09XXXXXXXXX">
                </div>

                <div class="mb-3" id="addressField">
                    <label class="form-label fw-semibold">Delivery Address</label>
                    <textarea class="form-control" rows="2" placeholder="Enter complete address"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Payment Method</label>
                    <select class="form-select">
                        <option value="COD">Cash on Delivery</option>
                        <option value="Gcash">GCash</option>
                        <option value="Card">Credit/Debit Card</option>
                    </select>
                </div>

                <h6 class="fw-bold">Order Summary</h6>
                <ul class="list-group mb-3" id="checkoutSummary">
                    <li class="list-group-item">Your order summary will appear here.</li>
                </ul>
                <div class="d-flex justify-content-between fw-bold">
                    <span>Total:</span>
                    <span id="checkoutTotal">₱0.00</span>
                </div>
            </div>
            <div class="modal-footer d-flex flex-column">
                <button class="btn btn-theme w-100 py-2 rounded-pill">
                    <i class="bi bi-bag-check"></i> Place Order
                </button>
                <button class="btn btn-link mt-2" data-bs-toggle="modal" data-bs-target="#cartModal" data-bs-dismiss="modal">
                    <i class="bi bi-arrow-left"></i> Back to Cart
                </button>
            </div>
        </div>
    </div>
</div>