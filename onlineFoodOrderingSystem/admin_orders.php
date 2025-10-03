<?php
    // Include configuration at the very top
    require_once 'config.php';
    require_admin_login();

    // Initialize variables
    $message       = '';
    $error         = '';
    $orders        = [];
    $status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
    $search_term   = isset($_GET['search']) ? $_GET['search'] : '';

    // Handle order status updates
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
        $order_id   = sanitize_input($_POST['order_id'], $conn);
        $new_status = sanitize_input($_POST['order_status'], $conn);

        $sql  = "UPDATE orders SET order_status = ? WHERE order_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $new_status, $order_id);

        if ($stmt->execute()) {
            $message = "✅ Order status updated successfully!";
        } else {
            $error = "❌ Error updating order status: " . $stmt->error;
        }
        $stmt->close();
    }

    // Build query based on filters
    $sql = "SELECT o.*,
                   CONCAT(oc.first_name, ' ', oc.last_name) as customer_name,
                   oc.phone_number, oc.email,
                   COUNT(oi.order_item_id) as item_count
            FROM orders o
            LEFT JOIN order_contacts oc ON o.order_id = oc.order_id
            LEFT JOIN order_items oi ON o.order_id = oi.order_id
            WHERE 1=1";

    $params = [];
    $types  = '';

    if ($status_filter !== 'all') {
        $sql .= " AND o.order_status = ?";
        $params[] = $status_filter;
        $types .= 's';
    }

    if (! empty($search_term)) {
        $sql .= " AND (o.order_number LIKE ? OR oc.first_name LIKE ? OR oc.last_name LIKE ? OR oc.phone_number LIKE ?)";
        $search_like = "%$search_term%";
        $params      = array_merge($params, [$search_like, $search_like, $search_like, $search_like]);
        $types .= 'ssss';
    }

    $sql .= " GROUP BY o.order_id ORDER BY o.created_at DESC";

    // Prepare and execute query
    if (! empty($params)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($sql);
    }

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
    }

    // Get status counts for filter badges
    $status_counts = [];
    $status_sql    = "SELECT order_status, COUNT(*) as count FROM orders GROUP BY order_status";
    $status_result = $conn->query($status_sql);
    if ($status_result && $status_result->num_rows > 0) {
        while ($row = $status_result->fetch_assoc()) {
            $status_counts[$row['order_status']] = $row['count'];
        }
    }

    // Get admin name for display
    $admin_name    = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Admin';
    $admin_initial = strtoupper(substr($admin_name, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - Bente Sais Lomihan</title>
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

        /* Content Area */
        .content-area {
            flex: 1;
            padding: 1.5rem;
        }

        /* Cards */
        .card {
            border: none;
            border-radius: var(--card-radius);
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background: white;
            border-bottom: 1px solid #f1f3f4;
            padding: 1.25rem 1.5rem;
        }

        .card-header h5 {
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Buttons */
        .btn {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .btn-outline-primary {
            color: var(--primary);
            border-color: var(--primary);
        }

        .btn-outline-primary:hover {
            background: var(--primary);
            border-color: var(--primary);
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.875rem;
        }

        /* Filter Section */
        .filter-section {
            background: white;
            border-radius: var(--card-radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .status-filter {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .status-btn {
            padding: 0.5rem 1rem;
            border: 2px solid #e9ecef;
            border-radius: 50px;
            background: white;
            color: #6c757d;
            font-weight: 500;
            transition: var(--transition);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-btn:hover, .status-btn.active {
            border-color: var(--primary);
            color: var(--primary);
            text-decoration: none;
        }

        .status-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .status-badge {
            background: #f8f9fa;
            color: #6c757d;
            border-radius: 20px;
            padding: 0.2rem 0.6rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-btn.active .status-badge {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        /* Search Box */
        .search-box {
            position: relative;
            max-width: 400px;
        }

        .search-box .form-control {
            padding-left: 2.5rem;
        }

        .search-box .bi-search {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }

        /* Tables */
        .table {
            margin: 0;
        }

        .table th {
            border-top: none;
            font-weight: 600;
            color: #2c3e50;
            background: #f8fafc;
            padding: 1rem 1.5rem;
            font-size: 0.85rem;
            border-bottom: 1px solid #e9ecef;
        }

        .table td {
            padding: 1rem 1.5rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f3f4;
        }

        .table tbody tr {
            transition: var(--transition);
        }

        .table tbody tr:hover {
            background-color: #f8fafc;
        }

        /* Status Badges */
        .order-status {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d1ecf1; color: #0c5460; }
        .status-preparing { background: #d1e7ff; color: #084298; }
        .status-ready { background: #d4edda; color: #155724; }
        .status-completed { background: #d1e7dd; color: #0f5132; }
        .status-cancelled { background: #f8d7da; color: #721c24; }

        /* Order Type Badges */
        .order-type-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            background: #e9ecef;
            color: #495057;
        }

        .type-dinein { background: #d1e7ff; color: #084298; }
        .type-takeout { background: #d1e7dd; color: #0f5132; }
        .type-delivery { background: #e7d1ff; color: #5a2d9e; }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        /* Empty States */
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

        /* Alerts */
        .alert {
            border: none;
            border-radius: var(--card-radius);
            padding: 1rem 1.5rem;
            border-left: 4px solid;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left-color: #28a745;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left-color: #dc3545;
        }

        /* Modal Styles */
        .modal-content {
            border: none;
            border-radius: var(--card-radius);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            background: white;
            border-bottom: 1px solid #f1f3f4;
            padding: 1.5rem;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid #f1f3f4;
            padding: 1rem 1.5rem;
        }

        /* Forms */
        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 0.75rem 1rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(50, 205, 50, 0.1);
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
            .status-filter {
                justify-content: center;
            }
            .action-buttons {
                flex-direction: column;
            }
            .search-box {
                max-width: 100%;
            }
        }

        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.875rem;
            }
            .table th,
            .table td {
                padding: 0.75rem 1rem;
            }
            .status-filter {
                gap: 0.25rem;
            }
            .status-btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.875rem;
            }
        }

        @media (max-width: 576px) {
            .content-area {
                padding: 1rem;
            }
            .filter-section {
                padding: 1rem;
            }
            .card-header {
                padding: 1rem;
            }
            .card-body {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Mobile Overlay -->
        <div class="mobile-overlay" id="mobileOverlay"></div>

        <!-- Sidebar -->
        <div class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-logo">
                <h4>BENTE SAIS</h4>
                <small>Admin Panel</small>
            </div>

            <nav class="sidebar-nav">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="admin_dashboard.php">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="admin_orders.php">
                            <i class="bi bi-bag-check"></i>
                            <span>Orders</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_menu.php">
                            <i class="bi bi-menu-button"></i>
                            <span>Menu Management</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_customers.php">
                            <i class="bi bi-people"></i>
                            <span>Customers</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_reports.php">
                            <i class="bi bi-graph-up"></i>
                            <span>Reports</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php" target="_blank">
                            <i class="bi bi-shop"></i>
                            <span>View Site</span>
                        </a>
                    </li>
                    <li class="nav-item mt-4">
                        <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="admin-main">
            <!-- Top Navigation -->
            <nav class="top-navbar">
                <div class="d-flex align-items-center">
                    <button class="btn btn-outline-secondary me-3 d-lg-none" id="sidebarToggle">
                        <i class="bi bi-list"></i>
                    </button>
                    <h5 class="page-title">Order Management</h5>
                </div>
                <div class="user-info">
                    <span class="welcome-text d-none d-md-inline">Welcome,                                                                                                                                                     <?php echo htmlspecialchars($admin_name); ?></span>
                    <div class="dropdown">
                        <div class="user-avatar dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
                            <?php echo htmlspecialchars($admin_initial); ?>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Content Area -->
            <div class="content-area">
                <!-- Alerts -->
                <?php if ($message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i><?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Filter Section -->
                <div class="filter-section">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="status-filter">
                                <a href="admin_orders.php" class="status-btn                                                                                                                                                         <?php echo $status_filter === 'all' ? 'active' : ''; ?>">
                                    All Orders
                                    <span class="status-badge"><?php echo array_sum($status_counts); ?></span>
                                </a>
                                <a href="admin_orders.php?status=pending" class="status-btn                                                                                                                                                                                       <?php echo $status_filter === 'pending' ? 'active' : ''; ?>">
                                    <i class="bi bi-clock"></i> Pending
                                    <span class="status-badge"><?php echo $status_counts['pending'] ?? 0; ?></span>
                                </a>
                                <a href="admin_orders.php?status=confirmed" class="status-btn                                                                                                                                                                                           <?php echo $status_filter === 'confirmed' ? 'active' : ''; ?>">
                                    <i class="bi bi-check-circle"></i> Confirmed
                                    <span class="status-badge"><?php echo $status_counts['confirmed'] ?? 0; ?></span>
                                </a>
                                <a href="admin_orders.php?status=preparing" class="status-btn                                                                                                                                                                                           <?php echo $status_filter === 'preparing' ? 'active' : ''; ?>">
                                    <i class="bi bi-egg-fried"></i> Preparing
                                    <span class="status-badge"><?php echo $status_counts['preparing'] ?? 0; ?></span>
                                </a>
                                <a href="admin_orders.php?status=ready" class="status-btn                                                                                                                                                                                   <?php echo $status_filter === 'ready' ? 'active' : ''; ?>">
                                    <i class="bi bi-check2-square"></i> Ready
                                    <span class="status-badge"><?php echo $status_counts['ready'] ?? 0; ?></span>
                                </a>
                                <a href="admin_orders.php?status=completed" class="status-btn                                                                                                                                                                                           <?php echo $status_filter === 'completed' ? 'active' : ''; ?>">
                                    <i class="bi bi-check2-all"></i> Completed
                                    <span class="status-badge"><?php echo $status_counts['completed'] ?? 0; ?></span>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <form method="GET" class="search-box">
                                <div class="input-group">
                                    <i class="bi bi-search"></i>
                                    <input type="text" class="form-control" name="search" placeholder="Search orders..." value="<?php echo htmlspecialchars($search_term); ?>">
                                    <?php if ($status_filter !== 'all'): ?>
                                        <input type="hidden" name="status" value="<?php echo $status_filter; ?>">
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Orders Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="bi bi-bag-check"></i> Orders</h5>
                        <div class="text-muted small">
                            Showing                                                                       <?php echo count($orders); ?> order(s)
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($orders)): ?>
                            <div class="empty-state">
                                <i class="bi bi-bag-x"></i>
                                <h4>No Orders Found</h4>
                                <p><?php echo $status_filter !== 'all' ? "No {$status_filter} orders found." : "No orders found matching your criteria."; ?></p>
                                <?php if ($status_filter !== 'all'): ?>
                                    <a href="admin_orders.php" class="btn btn-primary">
                                        <i class="bi bi-arrow-left"></i> View All Orders
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Order #</th>
                                            <th>Customer</th>
                                            <th>Type</th>
                                            <th>Items</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orders as $order): ?>
                                            <tr>
                                                <td>
                                                    <strong>#<?php echo htmlspecialchars($order['order_number']); ?></strong>
                                                </td>
                                                <td>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($order['customer_name'] ?? 'Guest'); ?></strong>
                                                        <?php if (! empty($order['phone_number'])): ?>
                                                            <br><small class="text-muted"><?php echo htmlspecialchars($order['phone_number']); ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="order-type-badge type-<?php echo strtolower($order['order_type']); ?>">
                                                        <?php echo ucfirst($order['order_type']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="fw-semibold"><?php echo $order['item_count']; ?> item(s)</span>
                                                </td>
                                                <td>
                                                    <strong class="text-success">₱<?php echo number_format($order['total_amount'], 2); ?></strong>
                                                </td>
                                                <td>
                                                    <span class="order-status status-<?php echo $order['order_status']; ?>">
                                                        <?php echo ucfirst($order['order_status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="small text-muted">
                                                        <?php echo date('M j, Y', strtotime($order['created_at'])); ?>
                                                        <br>
                                                        <small><?php echo date('g:i A', strtotime($order['created_at'])); ?></small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <button class="btn btn-sm btn-outline-primary view-order-btn"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#viewOrderModal"
                                                                data-order-id="<?php echo $order['order_id']; ?>">
                                                            <i class="bi bi-eye"></i> View
                                                        </button>
                                                        <?php if ($order['order_status'] !== 'completed' && $order['order_status'] !== 'cancelled'): ?>
                                                            <button class="btn btn-sm btn-outline-success update-status-btn"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#updateStatusModal"
                                                                    data-order-id="<?php echo $order['order_id']; ?>"
                                                                    data-order-number="<?php echo htmlspecialchars($order['order_number']); ?>"
                                                                    data-current-status="<?php echo $order['order_status']; ?>">
                                                                <i class="bi bi-arrow-clockwise"></i> Update
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Order Modal -->
    <div class="modal fade" id="viewOrderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-eye"></i> Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewOrderContent">
                    <!-- Order details will be loaded here via JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-arrow-clockwise"></i> Update Order Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="order_id" id="update_order_id">
                        <div class="mb-3">
                            <label class="form-label">Order Number</label>
                            <input type="text" class="form-control" id="update_order_number" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Current Status</label>
                            <input type="text" class="form-control" id="update_current_status" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Status</label>
                            <select class="form-select" name="order_status" id="update_order_status" required>
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="preparing">Preparing</option>
                                <option value="ready">Ready</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile sidebar toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const adminSidebar = document.getElementById('adminSidebar');
        const mobileOverlay = document.getElementById('mobileOverlay');

        sidebarToggle.addEventListener('click', function() {
            adminSidebar.classList.toggle('mobile-open');
            mobileOverlay.classList.toggle('active');
        });

        mobileOverlay.addEventListener('click', function() {
            adminSidebar.classList.remove('mobile-open');
            mobileOverlay.classList.remove('active');
        });

        // Auto-dismiss alerts
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Update Status Modal
        document.addEventListener('click', function(e) {
            if (e.target.closest('.update-status-btn')) {
                const btn = e.target.closest('.update-status-btn');
                document.getElementById('update_order_id').value = btn.dataset.orderId;
                document.getElementById('update_order_number').value = btn.dataset.orderNumber;
                document.getElementById('update_current_status').value = btn.dataset.currentStatus;
                document.getElementById('update_order_status').value = btn.dataset.currentStatus;
            }
        });

        // View Order Modal
        document.addEventListener('click', function(e) {
        if (e.target.closest('.view-order-btn')) {
            const btn = e.target.closest('.view-order-btn');
            const orderId = btn.dataset.orderId;

            // Show loading state
            document.getElementById('viewOrderContent').innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading order details...</p>
                </div>
            `;

            // AJAX call to fetch order details
            fetch('get_order_details.php?order_id=' + orderId)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('viewOrderContent').innerHTML = data;
                })
                .catch(error => {
                    document.getElementById('viewOrderContent').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> Error loading order details: ${error}
                        </div>
                    `;
                });
        }
    });

        // Auto-submit search on input change
        document.querySelector('input[name="search"]').addEventListener('input', function() {
            this.form.submit();
        });

        // Close sidebar when clicking on a link (for mobile)
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth < 992) {
                    adminSidebar.classList.remove('mobile-open');
                    mobileOverlay.classList.remove('active');
                }
            });
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 992) {
                adminSidebar.classList.remove('mobile-open');
                mobileOverlay.classList.remove('active');
            }
        });
    </script>
</body>
</html>
<?php
    // Close database connection
$conn->close();
?>