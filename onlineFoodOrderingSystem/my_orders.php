<?php 
include 'config.php'; 

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
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
    <link rel="stylesheet" href="bootstrapfile/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <title>My Orders - Bente Sais Lomihan</title>
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
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d1ecf1; color: #0c5460; }
        .status-preparing { background: #d1e7ff; color: #084298; }
        .status-ready { background: #d4edda; color: #155724; }
        .status-completed { background: #d1e7dd; color: #0f5132; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        .order-item {
            display: flex;
            justify-content: between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f8f9fa;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .item-image {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            margin-right: 1rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6c757d;
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
            left: 0;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -2rem;
            top: 0.25rem;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #32CD32;
            border: 3px solid white;
            box-shadow: 0 0 0 2px #32CD32;
        }
        
        .timeline-item.completed::before {
            background: #32CD32;
            box-shadow: 0 0 0 2px #32CD32;
        }
        
        .timeline-item.pending::before {
            background: #6c757d;
            box-shadow: 0 0 0 2px #6c757d;
        }
        
        .timeline-item.current::before {
            background: #32CD32;
            box-shadow: 0 0 0 2px #32CD32;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(50, 205, 50, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(50, 205, 50, 0); }
            100% { box-shadow: 0 0 0 0 rgba(50, 205, 50, 0); }
        }
        
        .order-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        @media (max-width: 768px) {
            .order-meta {
                grid-template-columns: 1fr;
            }
            
            .order-actions {
                flex-direction: column;
            }
            
            .order-actions .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation (same as index.php) -->
    <nav class="navbar fixed-top navbar-expand-lg">
        <div class="container py-3">
            <a class="navbar-brand fw-bold" href="index.php">BENTESAIS</a>

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
                            <a class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center w-100" href="#"
                               id="offcanvasUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle me-2"></i>
                                <?php echo htmlspecialchars($user_name); ?>
                            </a>
                            <ul class="dropdown-menu w-100" aria-labelledby="offcanvasUserDropdown">
                                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                                <li><a class="dropdown-item" href="my_orders.php">My Orders</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
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
                            <a class="btn dropdown-toggle d-flex align-items-center" href="#"
                               id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle me-1"></i>
                                <?php echo htmlspecialchars($user_name); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                                <li><a class="dropdown-item" href="my_orders.php">My Orders</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
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
                                // Get order items
                                $items_sql = "SELECT * FROM order_items WHERE order_id = ?";
                                $items_stmt = $conn->prepare($items_sql);
                                $items_stmt->bind_param("i", $order['order_id']);
                                $items_stmt->execute();
                                $items_result = $items_stmt->get_result();
                                
                                // Get contact info
                                $contact_sql = "SELECT * FROM order_contacts WHERE order_id = ?";
                                $contact_stmt = $conn->prepare($contact_sql);
                                $contact_stmt->bind_param("i", $order['order_id']);
                                $contact_stmt->execute();
                                $contact = $contact_stmt->get_result()->fetch_assoc();
                            ?>
                            
                            <div class="order-card">
                                <div class="order-header">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h5 class="fw-bold mb-1">Order #<?php echo $order['order_number']; ?></h5>
                                            <p class="text-muted mb-0">
                                                <i class="bi bi-calendar me-1"></i>
                                                <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?>
                                            </p>
                                        </div>
                                        <span class="status-badge status-<?php echo $order['order_status']; ?>">
                                            <?php echo ucfirst($order['order_status']); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="order-body">
                                    <div class="order-meta">
                                        <div class="meta-item">
                                            <span class="meta-label">Order Type</span>
                                            <span class="meta-value"><?php echo $order['order_type']; ?></span>
                                        </div>
                                        <div class="meta-item">
                                            <span class="meta-label">Payment Method</span>
                                            <span class="meta-value"><?php echo $order['payment_method']; ?></span>
                                        </div>
                                        <div class="meta-item">
                                            <span class="meta-label">Total Amount</span>
                                            <span class="meta-value fw-bold text-success">₱<?php echo number_format($order['total_amount'], 2); ?></span>
                                        </div>
                                        <div class="meta-item">
                                            <span class="meta-label">Contact</span>
                                            <span class="meta-value"><?php echo htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']); ?></span>
                                        </div>
                                    </div>
                                    
                                    <h6 class="fw-semibold mb-3">Order Items (<?php echo $order['total_quantity']; ?> items)</h6>
                                    <div class="order-items">
                                        <?php while ($item = $items_result->fetch_assoc()): ?>
                                            <div class="order-item">
                                                <div class="flex-grow-1">
                                                    <div class="fw-semibold"><?php echo htmlspecialchars($item['item_name']); ?></div>
                                                    <div class="text-muted small">
                                                        ₱<?php echo number_format($item['unit_price'], 2); ?> × <?php echo $item['quantity']; ?>
                                                    </div>
                                                </div>
                                                <div class="fw-bold">
                                                    ₱<?php echo number_format($item['total_price'], 2); ?>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                    
                                    <!-- Order Progress Timeline -->
                                    <div class="mt-4">
                                        <h6 class="fw-semibold mb-3">Order Status</h6>
                                        <div class="timeline">
                                            <?php
                                            $statuses = [
                                                'pending' => 'Order Received',
                                                'confirmed' => 'Order Confirmed',
                                                'preparing' => 'Preparing Your Order',
                                                'ready' => 'Ready for Pickup/Delivery',
                                                'completed' => 'Order Completed'
                                            ];
                                            
                                            $current_status = $order['order_status'];
                                            $status_found = false;
                                            
                                            foreach ($statuses as $status => $label):
                                                $is_completed = array_search($status, array_keys($statuses)) < array_search($current_status, array_keys($statuses));
                                                $is_current = $status === $current_status;
                                                $class = $is_completed ? 'completed' : ($is_current ? 'current' : 'pending');
                                            ?>
                                                <div class="timeline-item <?php echo $class; ?>">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <div class="fw-semibold"><?php echo $label; ?></div>
                                                            <?php if ($is_completed || $is_current): ?>
                                                                <small class="text-muted">
                                                                    <?php 
                                                                    if ($is_current && $status === 'pending') {
                                                                        echo 'Just now';
                                                                    } elseif ($is_completed) {
                                                                        echo 'Completed';
                                                                    } else {
                                                                        echo 'In progress';
                                                                    }
                                                                    ?>
                                                                </small>
                                                            <?php endif; ?>
                                                        </div>
                                                        <?php if ($is_completed): ?>
                                                            <i class="bi bi-check-circle-fill text-success"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="order-footer">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            Order ID: <?php echo $order['order_id']; ?>
                                        </small>
                                        <div class="order-actions">
                                            <button class="btn btn-outline-primary btn-sm" onclick="viewOrderDetails(<?php echo $order['order_id']; ?>)">
                                                <i class="bi bi-eye me-1"></i>View Details
                                            </button>
                                            <?php if ($order['order_status'] === 'pending'): ?>
                                                <button class="btn btn-outline-danger btn-sm" onclick="cancelOrder(<?php echo $order['order_id']; ?>)">
                                                    <i class="bi bi-x-circle me-1"></i>Cancel Order
                                                </button>
                                            <?php endif; ?>
                                            <button class="btn btn-outline-success btn-sm" onclick="reorder(<?php echo $order['order_id']; ?>)">
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

    <?php include 'modals.php'; ?>
    <script src="bootstrapfile/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewOrderDetails(orderId) {
            // You can implement a modal or redirect to order details page
            alert('Viewing details for order #' + orderId);
            // window.location.href = 'order_details.php?id=' + orderId;
        }
        
        function cancelOrder(orderId) {
            if (confirm('Are you sure you want to cancel this order?')) {
                // Implement cancel order functionality
                fetch('cancel_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ order_id: orderId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Order cancelled successfully');
                        location.reload();
                    } else {
                        alert('Failed to cancel order: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while cancelling the order');
                });
            }
        }
        
        function reorder(orderId) {
            // Implement reorder functionality
            alert('Adding items from order #' + orderId + ' to cart');
            // You can implement this to fetch order items and add them to cart
        }
    </script>
</body>
</html>

<?php
$orders_stmt->close();
$conn->close();
?>