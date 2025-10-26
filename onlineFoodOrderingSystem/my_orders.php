<?php
    include 'includes/config.php'; // <-- UPDATED

    // Check if user is logged in
    if (! isset($_SESSION['user_id'])) {
        header('Location: index.php'); // <-- UPDATED (Redirect to home to show login)
        exit;
    }

    $user_id   = $_SESSION['user_id'];
    $user_name = $_SESSION['full_name'];

    // Fetch user's orders
    $orders_sql = "SELECT o.*,
               COUNT(oi.order_item_id) as item_count,
               SUM(oi.quantity) as total_quantity
               FROM orders o
               LEFT JOIN order_items oi ON o.order_id = oi.order_id
               WHERE o.user_id = ?
               GROUP BY o.order_id
               ORDER BY o.created_at DESC";
    $orders_stmt = $conn->prepare($orders_sql);
    $orders_stmt->bind_param("i", $user_id);
    $orders_stmt->execute();
    $orders_result = $orders_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="assets/bootstrapfile/css/bootstrap.min.css"> <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css"> <title>My Orders - Bente Sais Lomihan</title>
    <style>
    .orders-container {
        max-width: 1200px;
        margin: 0 auto;
    }

    .order-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border: 1px solid #f0f0f0;
        transition: all 0.3s ease;
        margin-bottom: 1.5rem;
        overflow: hidden;
    }

    .order-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    }

    .order-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 1.5rem;
        border-bottom: 1px solid #e9ecef;
    }

    .order-body {
        padding: 1.5rem;
    }

    .order-footer {
        background: #f8f9fa;
        padding: 1rem 1.5rem;
        border-top: 1px solid #e9ecef;
    }

    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
        white-space: nowrap;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    .status-confirmed {
        background: #d1ecf1;
        color: #0c5460;
    }

    .status-preparing {
        background: #d1e7ff;
        color: #084298;
    }

    .status-ready {
        background: #d4edda;
        color: #155724;
    }

    .status-completed {
        background: #d1e7dd;
        color: #0f5132;
    }

    .status-cancelled {
        background: #f8d7da;
        color: #721c24;
    }

    .order-item {
        display: flex;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .order-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .order-item:first-child {
        padding-top: 0;
    }

    .item-image {
        width: 60px;
        height: 60px;
        border-radius: 8px;
        object-fit: cover;
        margin-right: 1rem;
        background: #f4f4f4;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #6c757d;
        background: #fff;
        border-radius: 16px;
    }

    .empty-state i {
        font-size: 4rem;
        color: #dee2e6;
        margin-bottom: 1.5rem;
    }

    .order-meta {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .meta-item {
        display: flex;
        flex-direction: column;
    }

    .meta-label {
        font-size: 0.85rem;
        color: #6c757d;
        margin-bottom: 0.25rem;
    }

    .meta-value {
        font-weight: 600;
        color: #495057;
    }

    .timeline {
        position: relative;
        padding-left: 2rem;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 5px; /* Centered on the dot */
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 1.5rem;
    }
    .timeline-item:last-child {
        margin-bottom: 0;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -2rem;
        top: 0.25rem;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #e9ecef;
        border: 2px solid white;
        z-index: 1;
    }

    .timeline-item.completed::before,
    .timeline-item.current::before {
        background: #32CD32;
        box-shadow: 0 0 0 3px #32CD32;
    }
    
    .timeline-item.pending::before {
        background: #adb5bd;
    }

    .timeline-item.current::before {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(50, 205, 50, 0.4);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(50, 205, 50, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(50, 205, 50, 0);
        }
    }

    .order-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .items-toggle {
        cursor: pointer;
        user-select: none;
    }
    
    .items-toggle .bi-chevron-down {
        transition: transform 0.3s ease;
    }

    .items-toggle[aria-expanded="true"] .bi-chevron-down {
        transform: rotate(180deg);
    }

    /* Modal Styling */
    #orderDetailsModal .modal-body {
        background: #f8f9fa;
    }
    #orderDetailsModal .list-group-item {
        background: #fff;
    }
    #orderDetailsModal .item-image-modal {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        object-fit: cover;
        margin-right: 1rem;
    }

    /* REMOVED #modalSpinner CSS from here. 
      It's no longer a separate element.
    */

    @media (max-width: 768px) {
        .order-meta {
            grid-template-columns: 1fr;
        }

        .order-actions {
            flex-direction: column;
            width: 100%;
            margin-top: 1rem;
        }

        .order-actions .btn {
            width: 100%;
        }
        
        .order-footer {
            flex-direction: column;
            align-items: flex-start !important;
        }
    }
    </style>
</head>

<body>
    <nav class="navbar fixed-top navbar-expand-lg">
        <div class="container py-3">
            <a class="navbar-brand fw-bold fs-3" href="index.php">
                <span style="color: #32cd32;">Quick</span>Crave
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
                        <div class="dropdown">
                            <a class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center w-100"
                                href="#" id="offcanvasUserDropdown" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="bi bi-person-circle me-2"></i>
                                <?php echo htmlspecialchars($user_name); ?>
                            </a>
                            <ul class="dropdown-menu w-100" aria-labelledby="offcanvasUserDropdown">
                                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                                <li><a class="dropdown-item active" href="my_orders.php">My Orders</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="actions/logout.php">Logout</a></li> </ul>
                        </div>
                    </div>

                    <ul class="navbar-nav justify-content-center flex-grow-1">
                        <li class="nav-item"><a class="nav-link" href="about.php">About us</a></li>
                        <li class="nav-item"><a class="nav-link" href="gallery.php">Gallery</a></li>
                        <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php">Menu</a></li>

                    </ul>

                    <div class="ms-auto d-none d-lg-block">
                        <div class="dropdown">
                            <a class="btn dropdown-toggle d-flex align-items-center" href="#" id="userDropdown"
                                role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle me-1"></i>
                                <?php echo htmlspecialchars($user_name); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                    <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                                    <li><a class="dropdown-item" href="my_orders.php"><i class="bi bi-bag-check me-2"></i>My Orders</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="actions/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main style="padding-top: 100px; min-height: 100vh; background: #F7F1F4;">
        <div class="container orders-container py-5">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h1 class="fw-bold mb-2">My Orders</h1>
                            <p class="text-muted mb-0">Track your orders and view order history</p>
                        </div>
                        <a href="index.php" class="btn btn-theme rounded-pill px-4">
                            <i class="bi bi-plus-circle me-2"></i>New Order
                        </a>
                    </div>

                    <?php if ($orders_result->num_rows > 0): ?>
                    <div class="orders-list">
                        <?php while ($order = $orders_result->fetch_assoc()):
                                // **FIXED QUERY**: Join order_items with menu_item ON item_name to get image_url
                                $items_sql  = "SELECT oi.*, m.image_url 
                                               FROM order_items oi 
                                               LEFT JOIN menu_item m ON oi.item_name = m.item_name 
                                               WHERE oi.order_id = ?";
                                $items_stmt = $conn->prepare($items_sql);
                                $items_stmt->bind_param("i", $order['order_id']);
                                $items_stmt->execute();
                                $items_result = $items_stmt->get_result();

                                // Get contact info
                                $contact_sql  = "SELECT * FROM order_contacts WHERE order_id = ?";
                                $contact_stmt = $conn->prepare($contact_sql);
                                $contact_stmt->bind_param("i", $order['order_id']);
                                $contact_stmt->execute();
                                $contact = $contact_stmt->get_result()->fetch_assoc();
                            ?>

                        <div class="order-card" id="order-card-<?php echo $order['order_id']; ?>">
                            <div class="order-header">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="fw-bold mb-1">Order #<?php echo $order['order_number']; ?></h5>
                                        <p class="text-muted mb-0">
                                            <i class="bi bi-calendar me-1"></i>
                                            <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?>
                                        </p>
                                    </div>
                                    <span
                                        class="status-badge status-<?php echo htmlspecialchars($order['order_status']); ?>">
                                        <?php echo ucfirst(htmlspecialchars($order['order_status'])); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="order-body">
                                <div class="order-meta">
                                    <div class="meta-item">
                                        <span class="meta-label">Order Type</span>
                                        <span class="meta-value"><?php echo htmlspecialchars($order['order_type']); ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-label">Payment Method</span>
                                        <span class="meta-value"><?php echo htmlspecialchars($order['payment_method']); ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-label">Total Amount</span>
                                        <span
                                            class="meta-value fw-bold text-success">₱<?php echo number_format($order['total_amount'], 2); ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-label">Contact</span>
                                        <span
                                            class="meta-value"><?php echo htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']); ?></span>
                                    </div>
                                </div>

                                <h6
                                    class="fw-semibold mb-3 d-flex justify-content-between align-items-center items-toggle"
                                    data-bs-toggle="collapse"
                                    href="#orderItems-<?php echo $order['order_id']; ?>" role="button"
                                    aria-expanded="false"
                                    aria-controls="orderItems-<?php echo $order['order_id']; ?>">
                                    <span>
                                        <i class="bi bi-receipt me-2"></i>Order Items
                                        (<?php echo $order['total_quantity']; ?>)
                                    </span>
                                    <i class="bi bi-chevron-down"></i>
                                </h6>
                                <div class="collapse" id="orderItems-<?php echo $order['order_id']; ?>">
                                    <div class="order-items mb-4">
                                        <?php while ($item = $items_result->fetch_assoc()): ?>
                                        <div class="order-item">
                                            <img src="<?php echo !empty($item['image_url']) ? htmlspecialchars($item['image_url']) : 'assets/images/placeholder.png'; ?>" alt="<?php echo htmlspecialchars($item['item_name']); ?>"
                                                class="item-image">
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold"><?php echo htmlspecialchars($item['item_name']); ?>
                                                </div>
                                                <div class="text-muted small">
                                                    ₱<?php echo number_format($item['unit_price'], 2); ?> ×
                                                    <?php echo $item['quantity']; ?>
                                                </div>
                                            </div>
                                            <div class="fw-bold ms-3">
                                                ₱<?php echo number_format($item['total_price'], 2); ?>
                                            </div>
                                        </div>
                                        <?php endwhile; ?>
                                    </div>
                                </div>


                                <div class="mt-4">
                                    <h6 class="fw-semibold mb-3"><i class="bi bi-truck me-2"></i>Order Status</h6>
                                    <div class="timeline">
                                        <?php
                                            $statuses = [
                                                'pending'   => 'Order Received',
                                                'confirmed' => 'Order Confirmed',
                                                'preparing' => 'Preparing Your Order',
                                                'ready'     => 'Ready for Pickup/Delivery',
                                                'completed' => 'Order Completed',
                                            ];
                                            
                                            // Handle cancelled state
                                            if ($order['order_status'] == 'cancelled') {
                                                $statuses = [
                                                    'pending' => 'Order Received',
                                                    'cancelled' => 'Order Cancelled'
                                                ];
                                            }

                                            $current_status = $order['order_status'];
                                            $status_keys    = array_keys($statuses);
                                            $current_index  = array_search($current_status, $status_keys);

                                            foreach ($statuses as $status => $label):
                                                $status_index = array_search($status, $status_keys);
                                                
                                                $is_completed = $status_index < $current_index;
                                                $is_current   = $status_index == $current_index;
                                                $is_pending   = $status_index > $current_index;

                                                $class = '';
                                                if ($is_completed) $class = 'completed';
                                                if ($is_current) $class = 'current';
                                                if ($is_pending) $class = 'pending';
                                                
                                                // Special case for cancelled
                                                if ($current_status == 'cancelled' && $status == 'cancelled') {
                                                    $class = 'current text-danger';
                                                } elseif ($current_status == 'cancelled' && $status != 'cancelled') {
                                                    $class = 'completed';
                                                }

                                            ?>
                                        <div class="timeline-item <?php echo $class; ?>">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="fw-semibold"><?php echo $label; ?></div>
                                                    <small class="text-muted">
                                                        <?php
                                                            if ($status === 'pending' && ($is_completed || $is_current)) {
                                                                echo date('F j, Y g:i A', strtotime($order['created_at']));
                                                            } elseif ($is_completed) {
                                                                echo 'Completed';
                                                            } elseif ($is_current) {
                                                                echo 'In Progress';
                                                            }
                                                        ?>
                                                    </small>
                                                </div>
                                                <?php if ($is_completed): ?>
                                                <i class="bi bi-check-circle-fill text-success"></i>
                                                <?php elseif ($current_status == 'cancelled' && $status == 'cancelled'): ?>
                                                <i class="bi bi-x-circle-fill text-danger"></i>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="order-footer">
                                <div
                                    class="d-flex justify-content-between align-items-center flex-wrap w-100">
                                    <small class="text-muted">
                                        Order ID: <?php echo $order['order_id']; ?>
                                    </small>
                                    <div class="order-actions">
                                        <button class="btn btn-outline-primary btn-sm"
                                            onclick="viewOrderDetails(<?php echo $order['order_id']; ?>, '<?php echo $order['order_number']; ?>')">
                                            <i class="bi bi-eye me-1"></i>View Details
                                        </button>
                                        <?php if ($order['order_status'] === 'pending'): ?>
                                        <button class="btn btn-outline-danger btn-sm"
                                            id="cancel-btn-<?php echo $order['order_id']; ?>"
                                            onclick="cancelOrder(<?php echo $order['order_id']; ?>)">
                                            <i class="bi bi-x-circle me-1"></i>Cancel Order
                                        </button>
                                        <?php endif; ?>
                                        <button class="btn btn-outline-success btn-sm"
                                            onclick="reorder(<?php echo $order['order_id']; ?>)">
                                            <i class="bi bi-arrow-repeat me-1"></i>Reorder
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php
                            $items_stmt->close();
                            $contact_stmt->close();
                        endwhile; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-bag-x"></i>
                        <h3 class="fw-bold mb-3">No Orders Yet</h3>
                        <p class="text-muted mb-4">You haven't placed any orders yet. Start exploring our menu!</p>
                        <a href="index.php" class="btn btn-theme rounded-pill px-4 py-2">
                            <i class="bi bi-arrow-right me-2"></i>Browse Menu
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0" style="border-radius: 16px;">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" id="orderDetailsModalLabel">Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0 position-relative">
                    
                    <div id="modalBodyContent">
                        </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


    <?php include 'includes/modals.php'; ?> <script src="assets/bootstrapfile/js/bootstrap.bundle.min.js"></script> 
    
    <script>
    const orderDetailsModal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
    const modalBody = document.getElementById('modalBodyContent');
    const modalTitle = document.getElementById('orderDetailsModalLabel');
    
    // Define the spinner HTML
    const modalSpinnerHTML = `
        <div style="display: flex; justify-content: center; align-items: center; min-height: 250px; background: #f8f9fa;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>`;

    /**
     * Fetches full order details and displays them in a modal.
     * **FIXED** to work with your get_order_details.php and schema
     */
    async function viewOrderDetails(orderId, orderNumber) {
        modalTitle.textContent = `Details for Order #${orderNumber}`;
        modalBody.innerHTML = modalSpinnerHTML; // <-- FIX: Inject spinner HTML
        orderDetailsModal.show();

        try {
            // This file must be in the 'actions' folder
            const response = await fetch(`actions/get_order_details.php?order_id=${orderId}`); // <-- UPDATED
            
            if (!response.ok) {
                // Try to get error text from server for more clarity
                let errorMsg = 'Network response was not ok';
                try {
                    const errorData = await response.json();
                    errorMsg = errorData.error || errorMsg;
                } catch (e) {
                    // response was not JSON (e.g., 500 server error HTML)
                    errorMsg = `Server error (Status: ${response.status})`;
                }
                throw new Error(errorMsg);
            }
            
            const data = await response.json();

            if (data.error) {
                throw new Error(data.error);
            }

            // Build Modal HTML
            let itemsHtml = '';
            data.items.forEach(item => {
                // **FIXED**: Use image_url and placeholder
                itemsHtml += `
                    <li class="list-group-item d-flex align-items-center">
                        <img src="${item.image_url || 'assets/images/placeholder.png'}" class="item-image-modal" alt="${item.item_name}"> <div class="flex-grow-1">
                            <div class="fw-semibold">${item.item_name}</div>
                            <small class="text-muted">₱${parseFloat(item.unit_price).toFixed(2)} x ${item.quantity}</small>
                        </div>
                        <div class="fw-bold">₱${parseFloat(item.total_price).toFixed(2)}</div>
                    </li>
                `;
            });

            const contact = data.contact;
            const order = data.order;
            const address = data.address; // Get address data
            
            const orderDate = new Date(order.created_at).toLocaleDateString('en-US', {
                year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
            });

            // **FIXED**: Added Address and corrected contact.phone_number
            let addressHtml = '';
            if (order.order_type === 'Delivery' && address) {
                addressHtml = `
                <li class="list-group-item d-flex flex-column">
                    <span><strong>Delivery Address:</strong></span>
                    <small class="text-muted">
                        ${address.street_address}, ${address.barangay}, ${address.city}, ${address.province}
                        ${address.zip_code ? `, ${address.zip_code}` : ''}
                    </small>
                    ${address.landmarks ? `<small class="text-muted"><strong>Landmarks:</strong> ${address.landmarks}</small>` : ''}
                </li>`;
            }

            // FIX: This new HTML will completely replace the spinner
            modalBody.innerHTML = `
                <div class="p-4">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <h6 class="fw-bold mb-3">Order Summary</h6>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between"><span>Order ID:</span> <strong>#${order.order_number}</strong></li>
                                <li class="list-group-item d-flex justify-content-between"><span>Order Date:</span> <strong>${orderDate}</strong></li>
                                <li class="list-group-item d-flex justify-content-between"><span>Order Type:</span> <strong>${order.order_type}</strong></li>
                                <li class="list-group-item d-flex justify-content-between"><span>Payment:</span> <strong>${order.payment_method}</strong></li>
                                <li class="list-group-item d-flex justify-content-between"><span>Status:</span> <strong class="text-capitalize status-badge status-${order.order_status}">${order.order_status}</strong></li>
                            </ul>
                        </div>
                        <div class="col-md-6 mb-4">
                            <h6 class="fw-bold mb-3">Contact Information</h6>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between"><span>Name:</span> <strong>${contact.first_name} ${contact.last_name}</strong></li>
                                <li class="list-group-item d-flex justify-content-between"><span>Phone:</span> <strong>${contact.phone_number}</strong></li>
                                <li class="list-group-item d-flex justify-content-between"><span>Email:</span> <strong>${contact.email}</strong></li>
                                ${addressHtml}
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white p-4">
                    <h6 class="fw-bold mb-3">Items Ordered (${data.items.length})</h6>
                    <ul class="list-group list-group-flush">
                        ${itemsHtml}
                    </ul>
                    <div class="d-flex justify-content-end align-items-center mt-3">
                        <span class="text-muted me-2">Total:</span>
                        <span class="fw-bold fs-5 text-success">₱${parseFloat(order.total_amount).toFixed(2)}</span>
                    </div>
                </div>
            `;

        } catch (error) {
            console.error('Error fetching order details:', error);
            // FIX: This error message will completely replace the spinner
            modalBody.innerHTML = `<div class="alert alert-danger m-4">Failed to load order details: ${error.message}</div>`;
        } 
        // FIX: No 'finally' block is needed anymore
    }


    /**
     * Cancels an order. Assumes your 'cancel_order.php' file works.
     */
    async function cancelOrder(orderId) {
        const cancelBtn = document.getElementById(`cancel-btn-${orderId}`);
        if (confirm('Are you sure you want to cancel this order? This action cannot be undone.')) {
            cancelBtn.disabled = true;
            cancelBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Cancelling...';

            try {
                // This file must be in the 'actions' folder
                const response = await fetch('actions/cancel_order.php', { // <-- UPDATED
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order_id: orderId })
                });
                
                const data = await response.json();

                if (data.success) {
                    const orderCard = document.getElementById(`order-card-${orderId}`);
                    orderCard.querySelector('.status-badge').textContent = 'Cancelled';
                    orderCard.querySelector('.status-badge').className = 'status-badge status-cancelled';
                    cancelBtn.remove();
                    alert('Order cancelled successfully');
                } else {
                    throw new Error(data.message || 'Unknown error');
                }

            } catch (error) {
                console.error('Error:', error);
                alert('Failed to cancel order: ' + error.message);
                cancelBtn.disabled = false;
                cancelBtn.innerHTML = '<i class="bi bi-x-circle me-1"></i>Cancel Order';
            }
        }
    }

    /**
     * Adds items from an old order to the current cart.
     */
    async function reorder(orderId) {
        if (confirm('This will add all items from order #' + orderId + ' to your cart. Proceed?')) {
            
            try {
                // This file must be in the 'actions' folder
                const response = await fetch('actions/reorder.php', { // <-- UPDATED
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order_id: orderId })
                });

                const data = await response.json();

                if (data.success) {
                    alert(`Success! ${data.items_added} items were added to your cart.`);
                    window.location.href = 'index.php'; 
                } else {
                    throw new Error(data.message || 'Could not add items to cart.');
                }

            } catch (error) {
                console.error('Error reordering:', error);
                alert('An error occurred while reordering: ' + error.message);
            }
        }
    }
    </script>
</body>

</html>

<?php
    $orders_stmt->close();
$conn->close();
?>