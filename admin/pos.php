<?php
// This file is admin/pos.php
require_once '../includes/config.php';
require_admin_login();

// Get admin name for display
$admin_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Admin';
$admin_initial = strtoupper(substr($admin_name, 0, 1));
$pageTitle = "Point of Sale (POS)";
$currentPage = "pos";

// Fetch menu categories and items
$categories = [];
$category_sql = "SELECT * FROM menu_category ORDER BY category_name";
$category_result = $conn->query($category_sql);
if ($category_result && $category_result->num_rows > 0) {
    while ($cat = $category_result->fetch_assoc()) {
        $categories[$cat['category_id']] = $cat;
        
        // Fetch items for this category
        $item_sql = "SELECT * FROM menu_item WHERE category_id = ? ORDER BY item_name";
        $item_stmt = $conn->prepare($item_sql);
        $item_stmt->bind_param("i", $cat['category_id']);
        $item_stmt->execute();
        $item_result = $item_stmt->get_result();
        
        $categories[$cat['category_id']]['items'] = [];
        while ($item = $item_result->fetch_assoc()) {
            $categories[$cat['category_id']]['items'][] = $item;
        }
        $item_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #32cd32;
            --primary-light: #5ce65c;
            --primary-dark: #28a428;
            --secondary: #2a9a2a;
            --success: #32cd32;
            --dark: #1a2e1a;
            --light: #f8f9fa;
            --sidebar-width: 260px;
            --header-height: 70px;
            --card-radius: 12px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8fafc;
            color: #334155;
            line-height: 1.6;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .admin-sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--dark) 0%, #2a4a2a 100%);
            color: white;
            transition: var(--transition);
            position: fixed;
            height: 100vh;
            z-index: 1000;
            box-shadow: 2px 0 15px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
        }

        .sidebar-logo {
            padding: 1.5rem 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
            height: var(--header-height);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .sidebar-logo h4 {
            font-weight: 700;
            margin-bottom: 0;
            color: white;
            font-size: 1.5rem;
        }

        .sidebar-logo small {
            font-size: 0.75rem;
            opacity: 0.7;
            margin-top: 0.25rem;
        }

        .sidebar-nav {
            padding: 1.5rem 0;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.85rem 1.5rem;
            margin: 0.15rem 0.8rem;
            border-radius: 8px;
            transition: var(--transition);
            font-weight: 500;
            display: flex;
            align-items: center;
            white-space: nowrap;
        }

        .nav-link:hover, .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }

        .nav-link.active {
            background: var(--primary);
            box-shadow: 0 4px 12px rgba(50, 205, 50, 0.3);
        }

        .nav-link i {
            width: 22px;
            margin-right: 12px;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        /* Main Content */
        .admin-main {
            flex: 1;
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .top-navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            padding: 0.8rem 1.5rem;
            height: var(--header-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .page-title {
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            cursor: pointer;
        }

        /* POS Layout */
        .pos-container {
            display: grid;
            grid-template-columns: 1fr 450px;
            gap: 1.5rem;
            height: calc(100vh - var(--header-height));
            padding: 1.5rem;
        }

        .menu-section {
            background: white;
            border-radius: var(--card-radius);
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .order-section {
            background: white;
            border-radius: var(--card-radius);
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .section-header {
            background: white;
            border-bottom: 1px solid #f1f3f4;
            padding: 1.25rem 1.5rem;
        }

        .section-body {
            flex: 1;
            overflow: auto;
            padding: 1.5rem;
        }

        /* Category Tabs */
        .category-tabs {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }

        .category-tab {
            padding: 0.75rem 1.5rem;
            border: 2px solid #e9ecef;
            border-radius: 50px;
            background: white;
            color: #6c757d;
            font-weight: 500;
            transition: var(--transition);
            cursor: pointer;
            text-decoration: none;
        }

        .category-tab:hover, .category-tab.active {
            border-color: var(--primary);
            color: var(--primary);
        }

        .category-tab.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        /* Menu Items Grid */
        .menu-items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
        }

        .menu-item-card {
            border: 1px solid #e9ecef;
            border-radius: var(--card-radius);
            padding: 1rem;
            text-align: center;
            transition: var(--transition);
            cursor: pointer;
            background: white;
        }

        .menu-item-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border-color: var(--primary);
        }

        .menu-item-image {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 0.75rem;
        }

        .menu-item-name {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .menu-item-price {
            font-weight: 600;
            color: var(--primary);
            font-size: 1rem;
        }

        .menu-item-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            background: var(--primary);
            color: white;
            border-radius: 4px;
            font-size: 0.75rem;
            margin-bottom: 0.5rem;
        }

        /* Order Items */
        .order-items {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 1rem;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #f1f3f4;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-item-info {
            flex: 1;
        }

        .order-item-name {
            font-weight: 600;
            color: #2c3e50;
        }

        .order-item-price {
            color: #6c757d;
            font-size: 0.875rem;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .quantity-btn {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 1px solid var(--primary);
            background: white;
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            transition: var(--transition);
        }

        .quantity-btn:hover {
            background: var(--primary);
            color: white;
        }

        .quantity-display {
            min-width: 40px;
            text-align: center;
            font-weight: 600;
        }

        .remove-item {
            color: #dc3545;
            cursor: pointer;
            padding: 0.25rem;
        }

        .remove-item:hover {
            color: #c82333;
        }

        /* Order Summary */
        .order-summary {
            border-top: 2px solid #f1f3f4;
            padding-top: 1rem;
            margin-bottom: 1.5rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .summary-total {
            font-size: 1.25rem;
            font-weight: 700;
            color: #2c3e50;
            border-top: 2px solid #f1f3f4;
            padding-top: 0.5rem;
            margin-top: 0.5rem;
        }

        /* Payment Section */
        .payment-section {
            background: #f8f9fa;
            border-radius: var(--card-radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .payment-methods {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .payment-method {
            padding: 1rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            background: white;
        }

        .payment-method:hover {
            border-color: var(--primary);
        }

        .payment-method.active {
            border-color: var(--primary);
            background: var(--primary);
            color: white;
        }

        .cash-input-group {
            margin-top: 1rem;
        }

        .cash-input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1.1rem;
            text-align: center;
            font-weight: 600;
        }

        .cash-input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(50, 205, 50, 0.1);
        }

        .change-display {
            margin-top: 0.5rem;
            padding: 0.75rem;
            background: #e7f3e7;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
            color: var(--primary-dark);
            display: none;
        }

        /* Buttons */
        .btn {
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
        }

        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .btn-success {
            background: var(--success);
            border-color: var(--success);
        }

        .btn-lg {
            padding: 1rem 2rem;
            font-size: 1.1rem;
        }

        .btn-block {
            width: 100%;
            justify-content: center;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .quick-action-btn {
            padding: 0.75rem;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            background: white;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
        }

        .quick-action-btn:hover {
            background: #f8f9fa;
            border-color: var(--primary);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        /* Receipt Modal */
        .receipt {
            font-family: 'Courier New', monospace;
            background: white;
            padding: 1rem;
            border: 1px solid #ddd;
        }

        .receipt-header {
            text-align: center;
            margin-bottom: 1rem;
            border-bottom: 1px dashed #000;
            padding-bottom: 0.5rem;
        }

        .receipt-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.25rem;
        }

        .receipt-total {
            border-top: 1px dashed #000;
            padding-top: 0.5rem;
            margin-top: 0.5rem;
            font-weight: bold;
        }

        /* Mobile Styles */
        .mobile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        @media (max-width: 1200px) {
            .pos-container {
                grid-template-columns: 1fr 400px;
            }
        }

        @media (max-width: 991px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }
            .admin-sidebar.mobile-open {
                transform: translateX(0);
            }
            .admin-main {
                margin-left: 0;
                width: 100%;
            }
            .mobile-overlay.active {
                display: block;
            }
            
            .pos-container {
                grid-template-columns: 1fr;
                height: auto;
            }
            
            .order-section {
                order: -1;
                margin-bottom: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .menu-items-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
            
            .pos-container {
                padding: 1rem;
            }
            
            .payment-methods {
                grid-template-columns: 1fr;
            }
            
            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .category-tabs {
                justify-content: center;
            }
            
            .category-tab {
                padding: 0.5rem 1rem;
                font-size: 0.875rem;
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
            }
        }

        /* Spinner */
        .spinner {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="mobile-overlay" id="mobileOverlay"></div>

        <?php include 'admin_sidebar.php'; ?>

        <div class="admin-main">
            <?php include 'admin_header.php'; ?>

            <div class="pos-container">
                <!-- Menu Section -->
                <div class="menu-section">
                    <div class="section-header">
                        <h5 class="mb-0"><i class="bi bi-menu-button"></i> Menu Items</h5>
                    </div>
                    <div class="section-body">
                        <!-- Category Tabs -->
                        <div class="category-tabs">
                            <div class="category-tab active" data-category="all">All Items</div>
                            <?php foreach ($categories as $category): ?>
                                <div class="category-tab" data-category="<?php echo $category['category_id']; ?>">
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Quick Actions -->
                        <div class="quick-actions">
                            <div class="quick-action-btn" onclick="pos.quickAdd('Lomi Special', 60.00)">
                                <small>Lomi Special</small>
                                <div class="fw-bold">₱60.00</div>
                            </div>
                            <div class="quick-action-btn" onclick="pos.quickAdd('Lomi Jumbo', 80.00)">
                                <small>Lomi Jumbo</small>
                                <div class="fw-bold">₱80.00</div>
                            </div>
                            <div class="quick-action-btn" onclick="pos.quickAdd('Tapsilog', 90.00)">
                                <small>Tapsilog</small>
                                <div class="fw-bold">₱90.00</div>
                            </div>
                        </div>

                        <!-- Menu Items -->
                        <div class="menu-items-grid" id="menuItemsGrid">
                            <?php foreach ($categories as $category): ?>
                                <?php foreach ($category['items'] as $item): ?>
                                    <div class="menu-item-card" 
                                         data-category="<?php echo $category['category_id']; ?>"
                                         data-item-id="<?php echo $item['item_id']; ?>"
                                         data-item-name="<?php echo htmlspecialchars($item['item_name']); ?>"
                                         data-item-price="<?php echo $item['price']; ?>"
                                         data-item-image="<?php echo htmlspecialchars($item['image_url']); ?>">
                                        <?php if ($item['image_url']): ?>
                                            <img src="../<?php echo htmlspecialchars($item['image_url']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['item_name']); ?>" 
                                                 class="menu-item-image">
                                        <?php else: ?>
                                            <div class="menu-item-image bg-light d-flex align-items-center justify-content-center">
                                                <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($item['badge'])): ?>
                                            <div class="menu-item-badge"><?php echo htmlspecialchars($item['badge']); ?></div>
                                        <?php endif; ?>
                                        
                                        <div class="menu-item-name"><?php echo htmlspecialchars($item['item_name']); ?></div>
                                        <div class="menu-item-price">₱<?php echo number_format($item['price'], 2); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Order Section -->
                <div class="order-section">
                    <div class="section-header">
                        <h5 class="mb-0"><i class="bi bi-receipt"></i> Current Order</h5>
                    </div>
                    <div class="section-body">
                        <!-- Order Items -->
                        <div class="order-items" id="orderItems">
                            <div class="empty-state">
                                <i class="bi bi-cart-x"></i>
                                <h5>No items added</h5>
                                <p>Select items from the menu to start an order</p>
                            </div>
                        </div>

                        <!-- Order Summary -->
                        <div class="order-summary" id="orderSummary" style="display: none;">
                            <div class="summary-row">
                                <span>Subtotal:</span>
                                <span id="subtotalAmount">₱0.00</span>
                            </div>
                            <div class="summary-row">
                                <span>Tax (0%):</span>
                                <span id="taxAmount">₱0.00</span>
                            </div>
                            <div class="summary-row summary-total">
                                <span>Total:</span>
                                <span id="totalAmount">₱0.00</span>
                            </div>
                        </div>

                        <!-- Payment Section -->
                        <div class="payment-section" id="paymentSection" style="display: none;">
                            <h6 class="mb-3">Payment Method</h6>
                            <div class="payment-methods">
                                <div class="payment-method active" data-method="cash">
                                    <i class="bi bi-cash-coin fs-4"></i>
                                    <div>Cash</div>
                                </div>
                                <div class="payment-method" data-method="gcash">
                                    <i class="bi bi-phone fs-4"></i>
                                    <div>GCash</div>
                                </div>
                                <div class="payment-method" data-method="card">
                                    <i class="bi bi-credit-card fs-4"></i>
                                    <div>Card</div>
                                </div>
                                <div class="payment-method" data-method="none">
                                    <i class="bi bi-x-circle fs-4"></i>
                                    <div>None</div>
                                </div>
                            </div>

                            <!-- Cash Payment -->
                            <div class="cash-payment" id="cashPayment">
                                <div class="cash-input-group">
                                    <label class="form-label">Amount Received</label>
                                    <input type="number" class="cash-input" id="cashAmount" placeholder="0.00" min="0" step="0.01">
                                </div>
                                <div class="change-display" id="changeDisplay">
                                    Change: ₱<span id="changeAmount">0.00</span>
                                </div>
                            </div>

                            <!-- Digital Payment -->
                            <div class="digital-payment" id="digitalPayment" style="display: none;">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i>
                                    Digital payment received
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-3">
                            <button class="btn btn-primary btn-lg btn-block" id="checkoutBtn" disabled>
                                <i class="bi bi-credit-card"></i> Process Payment
                            </button>
                            <div class="d-flex gap-2 mt-2">
                                <button class="btn btn-outline-secondary flex-fill" id="holdOrderBtn">
                                    <i class="bi bi-pause-circle"></i> Hold Order
                                </button>
                                <button class="btn btn-outline-danger flex-fill" id="clearOrderBtn">
                                    <i class="bi bi-x-circle"></i> Clear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Receipt Modal -->
    <div class="modal fade" id="receiptModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order Receipt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="receipt" id="receiptContent">
                        <!-- Receipt content will be generated here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="window.print()">
                        <i class="bi bi-printer"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // POS System JavaScript
        class POSSystem {
            constructor() {
                this.currentOrder = [];
                this.paymentMethod = 'cash';
                this.cashAmount = 0;
                this.initEventListeners();
            }

            initEventListeners() {
                // Category tabs
                document.querySelectorAll('.category-tab').forEach(tab => {
                    tab.addEventListener('click', () => this.filterMenuItems(tab.dataset.category));
                });

                // Menu item cards
                document.querySelectorAll('.menu-item-card').forEach(card => {
                    card.addEventListener('click', () => this.addToOrder(card));
                });

                // Payment methods
                document.querySelectorAll('.payment-method').forEach(method => {
                    method.addEventListener('click', () => this.selectPaymentMethod(method.dataset.method));
                });

                // Cash amount input
                document.getElementById('cashAmount').addEventListener('input', (e) => {
                    this.cashAmount = parseFloat(e.target.value) || 0;
                    this.calculateChange();
                });

                // Checkout button
                document.getElementById('checkoutBtn').addEventListener('click', () => this.processPayment());

                // Hold order button
                document.getElementById('holdOrderBtn').addEventListener('click', () => this.holdOrder());

                // Clear order button
                document.getElementById('clearOrderBtn').addEventListener('click', () => this.clearOrder());

                // Mobile sidebar toggle
                const sidebarToggle = document.getElementById('sidebarToggle');
                const adminSidebar = document.getElementById('adminSidebar');
                const mobileOverlay = document.getElementById('mobileOverlay');

                if (sidebarToggle) {
                    sidebarToggle.addEventListener('click', function() {
                        adminSidebar.classList.toggle('mobile-open');
                        mobileOverlay.classList.toggle('active');
                    });
                }

                if (mobileOverlay) {
                    mobileOverlay.addEventListener('click', function() {
                        adminSidebar.classList.remove('mobile-open');
                        mobileOverlay.classList.remove('active');
                    });
                }
            }

            filterMenuItems(categoryId) {
                // Update active tab
                document.querySelectorAll('.category-tab').forEach(tab => {
                    tab.classList.toggle('active', tab.dataset.category === categoryId);
                });

                // Show/hide menu items
                document.querySelectorAll('.menu-item-card').forEach(card => {
                    if (categoryId === 'all' || card.dataset.category === categoryId) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }

            quickAdd(itemName, itemPrice) {
                this.addToOrder({
                    dataset: {
                        itemId: 'quick-' + Date.now(),
                        itemName: itemName,
                        itemPrice: itemPrice,
                        itemImage: ''
                    }
                });
            }

            addToOrder(card) {
                const itemId = card.dataset.itemId;
                const itemName = card.dataset.itemName;
                const itemPrice = parseFloat(card.dataset.itemPrice);
                const itemImage = card.dataset.itemImage;

                // Check if item already exists in order
                const existingItem = this.currentOrder.find(item => item.id === itemId);
                
                if (existingItem) {
                    existingItem.quantity += 1;
                    existingItem.total = existingItem.quantity * itemPrice;
                } else {
                    this.currentOrder.push({
                        id: itemId,
                        name: itemName,
                        price: itemPrice,
                        image: itemImage,
                        quantity: 1,
                        total: itemPrice
                    });
                }

                this.updateOrderDisplay();
            }

            updateOrderDisplay() {
                const orderItemsContainer = document.getElementById('orderItems');
                const orderSummary = document.getElementById('orderSummary');
                const paymentSection = document.getElementById('paymentSection');
                const checkoutBtn = document.getElementById('checkoutBtn');
                const emptyState = orderItemsContainer.querySelector('.empty-state');

                if (this.currentOrder.length === 0) {
                    orderItemsContainer.innerHTML = `
                        <div class="empty-state">
                            <i class="bi bi-cart-x"></i>
                            <h5>No items added</h5>
                            <p>Select items from the menu to start an order</p>
                        </div>
                    `;
                    orderSummary.style.display = 'none';
                    paymentSection.style.display = 'none';
                    checkoutBtn.disabled = true;
                    return;
                }

                // Hide empty state
                if (emptyState) emptyState.style.display = 'none';

                // Generate order items HTML
                let itemsHTML = '';
                let subtotal = 0;

                this.currentOrder.forEach((item, index) => {
                    subtotal += item.total;
                    
                    itemsHTML += `
                        <div class="order-item">
                            <div class="order-item-info">
                                <div class="order-item-name">${item.name}</div>
                                <div class="order-item-price">₱${item.price.toFixed(2)} × ${item.quantity}</div>
                            </div>
                            <div class="quantity-controls">
                                <button class="quantity-btn" onclick="pos.decreaseQuantity(${index})">-</button>
                                <span class="quantity-display">${item.quantity}</span>
                                <button class="quantity-btn" onclick="pos.increaseQuantity(${index})">+</button>
                            </div>
                            <div class="ms-3">
                                <strong>₱${item.total.toFixed(2)}</strong>
                            </div>
                            <div class="remove-item ms-2" onclick="pos.removeItem(${index})">
                                <i class="bi bi-trash"></i>
                            </div>
                        </div>
                    `;
                });

                orderItemsContainer.innerHTML = itemsHTML;
                orderSummary.style.display = 'block';
                paymentSection.style.display = 'block';

                // Update summary
                const tax = 0; // You can add tax calculation if needed
                const total = subtotal + tax;

                document.getElementById('subtotalAmount').textContent = `₱${subtotal.toFixed(2)}`;
                document.getElementById('taxAmount').textContent = `₱${tax.toFixed(2)}`;
                document.getElementById('totalAmount').textContent = `₱${total.toFixed(2)}`;

                // Update checkout button
                checkoutBtn.disabled = this.currentOrder.length === 0;

                // Reset cash input and calculate change
                if (this.paymentMethod === 'cash') {
                    document.getElementById('cashAmount').value = '';
                    this.cashAmount = 0;
                    this.calculateChange();
                }
            }

            increaseQuantity(index) {
                this.currentOrder[index].quantity += 1;
                this.currentOrder[index].total = this.currentOrder[index].quantity * this.currentOrder[index].price;
                this.updateOrderDisplay();
            }

            decreaseQuantity(index) {
                if (this.currentOrder[index].quantity > 1) {
                    this.currentOrder[index].quantity -= 1;
                    this.currentOrder[index].total = this.currentOrder[index].quantity * this.currentOrder[index].price;
                    this.updateOrderDisplay();
                } else {
                    this.removeItem(index);
                }
            }

            removeItem(index) {
                this.currentOrder.splice(index, 1);
                this.updateOrderDisplay();
            }

            selectPaymentMethod(method) {
                this.paymentMethod = method;
                
                // Update UI
                document.querySelectorAll('.payment-method').forEach(m => {
                    m.classList.toggle('active', m.dataset.method === method);
                });

                // Show/hide payment sections
                const cashPayment = document.getElementById('cashPayment');
                const digitalPayment = document.getElementById('digitalPayment');
                const changeDisplay = document.getElementById('changeDisplay');

                if (method === 'cash') {
                    cashPayment.style.display = 'block';
                    digitalPayment.style.display = 'none';
                    this.calculateChange();
                } else if (method === 'none') {
                    cashPayment.style.display = 'none';
                    digitalPayment.style.display = 'none';
                    changeDisplay.style.display = 'none';
                } else {
                    cashPayment.style.display = 'none';
                    digitalPayment.style.display = 'block';
                    changeDisplay.style.display = 'none';
                }
            }

            calculateChange() {
                const total = this.calculateTotal();
                const change = this.cashAmount - total;
                const changeDisplay = document.getElementById('changeDisplay');
                const changeAmount = document.getElementById('changeAmount');

                if (this.cashAmount > 0 && change >= 0) {
                    changeAmount.textContent = change.toFixed(2);
                    changeDisplay.style.display = 'block';
                } else {
                    changeDisplay.style.display = 'none';
                }
            }

            async processPayment() {
                if (this.currentOrder.length === 0) {
                    alert('Please add items to the order.');
                    return;
                }

                if (this.paymentMethod === 'cash' && this.cashAmount < this.calculateTotal()) {
                    alert('Cash amount is less than total amount.');
                    return;
                }

                if (this.paymentMethod === 'none') {
                    if (!confirm('Process order without payment?')) {
                        return;
                    }
                }

                const orderData = {
                    items: this.currentOrder,
                    order_type: 'DineIn',
                    payment_method: this.paymentMethod === 'none' ? 'Cash' : this.paymentMethod,
                    cash_amount: this.paymentMethod === 'cash' ? this.cashAmount : null,
                    total: this.calculateTotal()
                };

                try {
                    const checkoutBtn = document.getElementById('checkoutBtn');
                    checkoutBtn.disabled = true;
                    checkoutBtn.innerHTML = '<i class="bi bi-arrow-repeat spinner"></i> Processing...';

                    const response = await fetch('pos_create_order.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(orderData)
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.showReceipt(result.order_number, result.order_id);
                        this.clearOrder();
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while processing the order.');
                } finally {
                    const checkoutBtn = document.getElementById('checkoutBtn');
                    checkoutBtn.disabled = false;
                    checkoutBtn.innerHTML = '<i class="bi bi-credit-card"></i> Process Payment';
                }
            }

            showReceipt(orderNumber, orderId) {
                const total = this.calculateTotal();
                const receiptContent = document.getElementById('receiptContent');
                
                let receiptHTML = `
                    <div class="receipt-header">
                        <h6>BENTE SAIS</h6>
                        <small>Restaurant & Cafe</small>
                        <div>${new Date().toLocaleDateString()} ${new Date().toLocaleTimeString()}</div>
                    </div>
                    <div class="receipt-items">
                `;

                this.currentOrder.forEach(item => {
                    receiptHTML += `
                        <div class="receipt-item">
                            <span>${item.name} × ${item.quantity}</span>
                            <span>₱${item.total.toFixed(2)}</span>
                        </div>
                    `;
                });

                receiptHTML += `
                    </div>
                    <div class="receipt-total">
                        <div class="receipt-item">
                            <span>TOTAL:</span>
                            <span>₱${total.toFixed(2)}</span>
                        </div>
                        <div class="receipt-item">
                            <span>PAYMENT:</span>
                            <span>${this.paymentMethod.toUpperCase()}</span>
                        </div>
                        ${this.paymentMethod === 'cash' ? `
                        <div class="receipt-item">
                            <span>CASH:</span>
                            <span>₱${this.cashAmount.toFixed(2)}</span>
                        </div>
                        <div class="receipt-item">
                            <span>CHANGE:</span>
                            <span>₱${(this.cashAmount - total).toFixed(2)}</span>
                        </div>
                        ` : ''}
                    </div>
                    <div class="text-center mt-3">
                        <strong>ORDER #: ${orderNumber}</strong><br>
                        <small>Thank you for your order!</small>
                    </div>
                `;

                receiptContent.innerHTML = receiptHTML;
                
                // Show receipt modal
                const receiptModal = new bootstrap.Modal(document.getElementById('receiptModal'));
                receiptModal.show();
            }

            holdOrder() {
                if (this.currentOrder.length === 0) {
                    alert('No items to hold.');
                    return;
                }

                // Store order in localStorage for later retrieval
                const heldOrder = {
                    items: this.currentOrder,
                    timestamp: new Date().toISOString()
                };

                let heldOrders = JSON.parse(localStorage.getItem('heldOrders') || '[]');
                heldOrders.push(heldOrder);
                localStorage.setItem('heldOrders', JSON.stringify(heldOrders));

                alert('Order held successfully! You can retrieve it later.');
                this.clearOrder();
            }

            clearOrder() {
                if (this.currentOrder.length === 0) return;
                
                if (confirm('Are you sure you want to clear the current order?')) {
                    this.currentOrder = [];
                    this.paymentMethod = 'cash';
                    this.cashAmount = 0;
                    document.getElementById('cashAmount').value = '';
                    document.getElementById('changeDisplay').style.display = 'none';
                    this.updateOrderDisplay();
                }
            }

            calculateTotal() {
                return this.currentOrder.reduce((total, item) => total + item.total, 0);
            }
        }

        // Initialize POS System
        const pos = new POSSystem();
    </script>
</body>
</html>
<?php
// Close database connection
$conn->close();
?>