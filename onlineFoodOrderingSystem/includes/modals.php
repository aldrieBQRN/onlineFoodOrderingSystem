<div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header d-block">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="modal-title" id="checkoutModalLabel">Place Your Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="d-flex justify-content-between align-items-center text-center">
                    <div class="flex-fill">
                        <i class="bi bi-circle-fill text-success fs-4 d-block" id="stepOrderIcon"></i>
                        <small class="fw-bold text-success" id="stepOrderText">Order</small>
                    </div>
                    <div class="flex-fill border-top mx-1" style="transform: translateY(-8px);"></div>
                    <div class="flex-fill">
                        <i class="bi bi-circle text-muted fs-4 d-block" id="stepConfirmationIcon"></i>
                        <small class="text-muted" id="stepConfirmationText">Confirmation</small>
                    </div>
                    <div class="flex-fill border-top mx-1" style="transform: translateY(-8px);"></div>
                    <div class="flex-fill">
                        <i class="bi bi-circle text-muted fs-4 d-block" id="stepReadyIcon"></i>
                        <small class="text-muted" id="stepReadyText">Ready</small>
                    </div>
                </div>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-7 border-end pe-lg-4">
                        <form id="checkoutForm">
                            <div class="card checkout-card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 fw-bold"><i class="bi bi-bag-check me-2"></i>Order Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Payment Method</label>
                                        <select class="form-select" name="payment_method" required>
                                            <option value="COD">Cash on Delivery / Pickup</option>
                                            <option value="Gcash">GCash</option>
                                            <option value="Card">Credit/Debit Card</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Order Type</label>
                                        <select class="form-select" id="finalOrderType" name="order_type">
                                            <option value="Delivery">Delivery</option>
                                            <option value="Pickup">Pick-up</option>
                                        </select>
                                    </div>

                                    <div class="mb-0">
                                        <label class="form-label fw-semibold">When</label>
                                        <select class="form-select" name="order_time">
                                            <option value="ASAP">As Soon as Possible</option>
                                            <option value="Specific">Specific Time</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="card checkout-card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 fw-bold"><i class="bi bi-person me-2"></i>Contact Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">First Name</label>
                                            <input type="text" class="form-control" name="first_name"
                                                placeholder="Enter first name" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Last Name</label>
                                            <input type="text" class="form-control" name="last_name"
                                                placeholder="Enter last name" required>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Email Address</label>
                                        <input type="email" class="form-control" name="email"
                                            placeholder="your.email@example.com" required>
                                    </div>

                                    <div class="mb-0">
                                        <label class="form-label fw-semibold">Contact Number</label>
                                        <input type="text" class="form-control" name="phone_number"
                                            placeholder="09XXXXXXXXX" required>
                                    </div>
                                </div>
                            </div>

                            <div id="addressField" class="card checkout-card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 fw-bold"><i class="bi bi-geo-alt me-2"></i>Delivery Address</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 mb-3">
                                            <label class="form-label fw-semibold">Street Address</label>
                                            <input type="text" class="form-control" name="street_address"
                                                placeholder="House/Building No., Street Name">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Barangay</label>
                                            <input type="text" class="form-control" name="barangay"
                                                placeholder="Barangay">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">City/Municipality</label>
                                            <input type="text" class="form-control" name="city"
                                                placeholder="City/Municipality">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Province</label>
                                            <input type="text" class="form-control" name="province"
                                                placeholder="Province">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">ZIP Code</label>
                                            <input type="text" class="form-control" name="zip_code"
                                                placeholder="ZIP Code">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Landmarks (Optional)</label>
                                        <textarea class="form-control" name="landmarks" rows="2"
                                            placeholder="Nearby landmarks, building names, etc."></textarea>
                                    </div>

                                    <div class="mb-0">
                                        <label class="form-label fw-semibold">Delivery Instructions (Optional)</label>
                                        <textarea class="form-control" name="delivery_instructions" rows="2"
                                            placeholder="Special instructions for delivery"></textarea>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="col-lg-5 ps-lg-4">
                        <div class="card checkout-card sticky-top" style="top: 20px;">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 fw-bold"><i class="bi bi-receipt me-2"></i>Order Summary</h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-group mb-3" id="checkoutSummary">
                                    <li class="list-group-item border-0 text-center text-muted py-4">
                                        <i class="bi bi-cart-x fs-1 text-muted mb-2 d-block"></i>
                                        Your order summary will appear here.
                                    </li>
                                </ul>
                                <div class="d-flex justify-content-between fw-bold fs-5 pt-3 border-top">
                                    <span>Total:</span>
                                    <span id="checkoutTotal">₱0.00</span>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent border-0">
                                <button class="btn btn-theme w-100 py-2 rounded-pill" form="checkoutForm" type="submit"
                                    id="createOrderBtn">
                                    <i class="bi bi-bag-check"></i> Create Order
                                </button>
                                <button class="btn btn-link w-100 mt-2 text-decoration-none" data-bs-toggle="modal"
                                    data-bs-target="#cartModal" data-bs-dismiss="modal">
                                    <i class="bi bi-arrow-left"></i> Back to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
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
                <button class="btn btn-theme rounded-pill w-100 py-2" data-bs-toggle="modal"
                    data-bs-target="#checkoutModal" data-bs-dismiss="modal">
                    <i class="bi bi-credit-card"></i> Proceed to Checkout
                </button>
            </div>
        </div>
    </div>
</div>

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
                    Don't have an account? <a href="#" data-bs-toggle="modal" data-bs-target="#signupModal"
                        data-bs-dismiss="modal">Sign up here</a>
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
                    <button typeS="submit" class="btn btn-theme w-100 py-2 rounded-pill">Sign Up</button>
                </form>
                <p class="text-center mt-3">
                    Already have an account? <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal"
                        data-bs-dismiss="modal">Login here</a>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3">
    <div id="orderSuccessToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-success text-white">
            <i class="bi bi-check-circle-fill me-2"></i>
            <strong class="me-auto">Order Placed!</strong>
            <button type="button" classs="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="toastMessageBody">
            Your order has been placed successfully.
        </div>
    </div>
</div>

<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">GCash Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="paymentModalBody">
                <div class="row">
                    <div class="col-md-6 text-center border-end">
                        <h6 class="fw-bold">Scan to Pay</h6>
                        <p class="text-muted small">Please pay the exact amount for your order.</p>
                        <img src="../uploads/qr/gcash_qr.png" id="gcashQrCode" class="img-fluid rounded mb-3" alt="GCash QR Code" style="max-width: 250px;">
                        
                        <h6 class="fw-bold">Or Pay To:</h6>
                        <p class="mb-1">
                            <strong>Account Name:</strong> <span id="gcashAccountName"></span>
                        </p>
                        <p>
                            <strong>Account Number:</strong> <span id="gcashAccountNumber"></span>
                        </p>
                        
                        <div class="alert alert-info" id="gcashInstructions">
                            </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold">Upload Transaction Receipt</h6>
                        <p class="text-muted small">After paying, please upload a screenshot of your transaction as proof of payment.</p>
                        
                        <form id="paymentUploadForm" enctype="multipart/form-data">
                            <input type="hidden" name="order_id" id="paymentOrderId" value="">
                            
                            <div class="mb-3">
                                <label for="reference_number" class="form-label">Reference Number (Optional)</label>
                                <input type="text" class="form-control" id="reference_number" name="reference_number" placeholder="Enter GCash Ref. No.">
                            </div>
                            
                            <div class="mb-3">
                                <label for="receipt_image" class="form-label">Receipt Screenshot</label>
                                <input type="file" class="form-control" id="receipt_image" name="receipt_image" accept="image/png, image/jpeg, image/jpg" required>
                            </div>

                            <div id="paymentUploadMessage" class="alert d-none" role="alert"></div>

                            <button type="submit" class="btn btn-theme w-100 py-2 rounded-pill" id="uploadPaymentBtn">
                                <i class="bi bi-upload"></i> Submit Payment
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>