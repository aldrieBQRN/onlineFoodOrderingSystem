<?php
    // Include configuration at the very top
    require_once '../includes/config.php';
    require_admin_login();

    // Get dashboard statistics with proper initialization
    $total_orders    = 0;
    $pending_orders  = 0;
    $total_revenue   = 0;
    $today_orders    = 0;
    $today_revenue   = 0;
    $today_customers = 0;

    // Total orders
    $sql    = "SELECT COUNT(*) as total FROM orders";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row          = $result->fetch_assoc();
        $total_orders = $row['total'];
    }

    // Pending orders
    $sql    = "SELECT COUNT(*) as pending FROM orders WHERE order_status = 'pending'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row            = $result->fetch_assoc();
        $pending_orders = $row['pending'];
    }

    // Total revenue
    $sql    = "SELECT SUM(total_amount) as revenue FROM orders WHERE order_status IN ('confirmed', 'preparing', 'ready', 'completed')";
    $result = $conn->query($sql);
    if ($result) {
        $row           = $result->fetch_assoc();
        $total_revenue = $row['revenue'] ?: 0;
    }

    // Today's orders and revenue
    $today = date('Y-m-d');
    $sql   = "SELECT COUNT(*) as today_orders, SUM(total_amount) as today_revenue, COUNT(DISTINCT user_id) as today_customers
        FROM orders
        WHERE DATE(created_at) = '$today' AND order_status != 'cancelled'";
    $result = $conn->query($sql);
    if ($result) {
        $row             = $result->fetch_assoc();
        $today_orders    = $row['today_orders'] ?: 0;
        $today_revenue   = $row['today_revenue'] ?: 0;
        $today_customers = $row['today_customers'] ?: 0;
    }

    // Recent orders
    $recent_orders = [];
    $sql           = "SELECT o.order_id, o.order_number, CONCAT(oc.first_name, ' ', oc.last_name) as customer_name,
               o.order_type, o.total_amount, o.order_status, o.created_at
        FROM orders o
        LEFT JOIN order_contacts oc ON o.order_id = oc.order_id
        ORDER BY o.created_at DESC
        LIMIT 5";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $recent_orders[] = $row;
        }
    }

    // Popular items
    $popular_items = [];
    $sql           = "SELECT oi.item_name,
               COUNT(oi.order_item_id) as times_ordered,
               SUM(oi.quantity) as total_quantity,
               SUM(oi.total_price) as total_revenue
        FROM order_items oi
        GROUP BY oi.item_name
        ORDER BY total_quantity DESC
        LIMIT 5";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $popular_items[] = $row;
        }
    }

    // Order status distribution
    $order_status_data = [];
    $sql               = "SELECT order_status, COUNT(*) as count
        FROM orders
        GROUP BY order_status";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $order_status_data[$row['order_status']] = $row['count'];
        }
    }

    // Revenue data for last 7 days
    $revenue_data = [];
    $sql          = "SELECT DATE(created_at) as date, SUM(total_amount) as revenue
        FROM orders
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        AND order_status IN ('confirmed', 'preparing', 'ready', 'completed')
        GROUP BY DATE(created_at)
        ORDER BY date DESC";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $revenue_data[$row['date']] = $row['revenue'];
        }
    }

    // Order type distribution
    $order_type_data = [];
    $sql             = "SELECT order_type, COUNT(*) as count
        FROM orders
        GROUP BY order_type";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $order_type_data[$row['order_type']] = $row['count'];
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
    <title>Admin Dashboard - Bente Sais Lomihan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            --sidebar-collapsed: 70px;
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
            background-color: #f5f7fb;
            color: #333;
            line-height: 1.6;
            overflow-x: hidden;
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

        /* Main Content Area */
        .admin-main {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: var(--transition);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            width: calc(100% - var(--sidebar-width));
        }

        /* Top Navigation */
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
            font-size: 1.4rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .welcome-text {
            font-weight: 500;
            color: #495057;
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
            flex-shrink: 0;
        }

        /* Content Area */
        .content-area {
            flex: 1;
            padding: 1.5rem;
        }

        /* Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: var(--card-radius);
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
            border: none;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--primary);
        }

        .stat-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stat-data {
            display: flex;
            flex-direction: column;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            line-height: 1.2;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            font-weight: 500;
            margin-top: 0.25rem;
        }

        .stat-icon {
            font-size: 2.8rem;
            color: var(--primary);
            opacity: 0.2;
            flex-shrink: 0;
        }

        /* Content Cards */
        .content-card {
            background: white;
            border-radius: var(--card-radius);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border: none;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            background: white;
        }

        .card-header h5 {
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Chart Containers */
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }

        .mini-chart-container {
            position: relative;
            height: 200px;
            width: 100%;
        }

        /* Table Styles */
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
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e9ecef;
        }

        .table td {
            padding: 1.1rem 1.5rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f3f4;
        }

        .table tbody tr {
            transition: var(--transition);
        }

        .table tbody tr:hover {
            background-color: #f8fafc;
        }

        .status-badge {
            padding: 0.5rem 0.9rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .bg-pending { background: #fff3cd; color: #856404; }
        .bg-confirmed { background: #d1ecf1; color: #0c5460; }
        .bg-preparing { background: #d1e7ff; color: #084298; }
        .bg-ready { background: #d4edda; color: #155724; }
        .bg-completed { background: #d1e7dd; color: #0f5132; }
        .bg-cancelled { background: #f8d7da; color: #721c24; }

        /* Popular Items */
        .popular-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #f1f3f4;
            transition: var(--transition);
        }

        .popular-item:hover {
            background-color: #f8fafc;
            padding-left: 0.5rem;
            padding-right: 0.5rem;
            margin: 0 -0.5rem;
            border-radius: 8px;
        }

        .popular-item:last-child {
            border-bottom: none;
        }

        .item-name {
            font-weight: 500;
            color: #2c3e50;
        }

        .item-stats {
            font-size: 0.85rem;
            color: #6c757d;
        }

        /* Summary Items */
        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.9rem 0;
            border-bottom: 1px solid #f1f3f4;
        }

        .summary-item:last-child {
            border-bottom: none;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .action-btn {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: var(--card-radius);
            padding: 1.5rem 1rem;
            text-align: center;
            transition: var(--transition);
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }

        .action-btn:hover {
            border-color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(50, 205, 50, 0.1);
            color: inherit;
            text-decoration: none;
        }

        .action-icon {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .action-text {
            font-weight: 600;
            color: #2c3e50;
        }

        /* Green accent elements */
        .text-success {
            color: var(--primary) !important;
        }

        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .dropdown-item:focus, .dropdown-item:hover {
            background-color: rgba(50, 205, 50, 0.1);
        }

        /* Mobile Menu Overlay */
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

        /* Responsive Breakpoints */
        @media (max-width: 991px) {
            .admin-sidebar {
                transform: translateX(-100%);
                width: var(--sidebar-width);
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

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 767px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
            }

            .chart-container {
                height: 250px;
            }
        }

        @media (max-width: 575px) {
            .quick-actions {
                grid-template-columns: 1fr;
            }

            .chart-container {
                height: 200px;
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
                        <a class="nav-link active" href="admin_dashboard.php">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">
                            <i class="bi bi-bag-check"></i>
                            <span>Orders</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="menu.php">
                            <i class="bi bi-menu-button"></i>
                            <span>Menu Management</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="customers.php">
                            <i class="bi bi-people"></i>
                            <span>Customers</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">
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
                        <a class="nav-link" href="../actions/logout.php">
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
                    <h5 class="page-title">Dashboard Overview</h5>
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
                            <li><a class="dropdown-item" href="../actions/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Content Area -->
            <div class="content-area">
                <!-- Quick Actions -->
                <div class="quick-actions">
                    <a href="admin_orders.php?status=pending" class="action-btn">
                        <div class="action-icon">
                            <i class="bi bi-clock"></i>
                        </div>
                        <div class="action-text">Pending Orders</div>
                    </a>
                    <a href="admin_menu.php" class="action-btn">
                        <div class="action-icon">
                            <i class="bi bi-plus-circle"></i>
                        </div>
                        <div class="action-text">Add Menu Item</div>
                    </a>
                    <a href="admin_reports.php" class="action-btn">
                        <div class="action-icon">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <div class="action-text">View Reports</div>
                    </a>
                    <a href="admin_customers.php" class="action-btn">
                        <div class="action-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="action-text">Manage Customers</div>
                    </a>
                </div>

                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-content">
                            <div class="stat-data">
                                <div class="stat-number"><?php echo number_format($total_orders); ?></div>
                                <div class="stat-label">Total Orders</div>
                            </div>
                            <i class="bi bi-cart-check stat-icon"></i>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-content">
                            <div class="stat-data">
                                <div class="stat-number"><?php echo number_format($pending_orders); ?></div>
                                <div class="stat-label">Pending Orders</div>
                            </div>
                            <i class="bi bi-clock stat-icon"></i>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-content">
                            <div class="stat-data">
                                <div class="stat-number">₱<?php echo number_format($total_revenue, 2); ?></div>
                                <div class="stat-label">Total Revenue</div>
                            </div>
                            <i class="bi bi-currency-dollar stat-icon"></i>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-content">
                            <div class="stat-data">
                                <div class="stat-number"><?php echo number_format($today_orders); ?></div>
                                <div class="stat-label">Today's Orders</div>
                            </div>
                            <i class="bi bi-calendar-day stat-icon"></i>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Charts Section -->
                    <div class="col-lg-8">
                        <!-- Revenue Chart -->
                        <div class="content-card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5>Revenue Overview (Last 7 Days)</h5>
                                <div>
                                    <select class="form-select form-select-sm" id="chartPeriod" style="width: auto;">
                                        <option value="7">Last 7 Days</option>
                                        <option value="30">Last 30 Days</option>
                                    </select>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="revenueChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Orders -->
                        <div class="content-card">
                            <div class="card-header">
                                <h5>Recent Orders</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Order #</th>
                                                <th>Customer</th>
                                                <th class="order-type">Type</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th class="order-date">Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($recent_orders)): ?>
                                                <tr>
                                                    <td colspan="6" class="text-center py-4">No orders found</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($recent_orders as $order): ?>
                                                <tr style="cursor: pointer;" onclick="viewOrder(<?php echo $order['order_id']; ?>)">
                                                    <td class="fw-bold">#<?php echo htmlspecialchars($order['order_number']); ?></td>
                                                    <td><?php echo htmlspecialchars($order['customer_name'] ?? 'N/A'); ?></td>
                                                    <td class="order-type"><?php echo htmlspecialchars($order['order_type']); ?></td>
                                                    <td class="fw-semibold">₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                                    <td>
                                                        <span class="status-badge bg-<?php echo getStatusClass($order['order_status']); ?>">
                                                            <?php echo ucfirst($order['order_status']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="order-date text-muted"><?php echo date('M j, g:i A', strtotime($order['created_at'])); ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Sidebar -->
                    <div class="col-lg-4">
                        <!-- Order Status Distribution -->
                        <div class="content-card mb-4">
                            <div class="card-header">
                                <h5>Order Status</h5>
                            </div>
                            <div class="card-body">
                                <div class="mini-chart-container">
                                    <canvas id="orderStatusChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Popular Items -->
                        <div class="content-card mb-4">
                            <div class="card-header">
                                <h5>Popular Items</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($popular_items)): ?>
                                    <div class="text-center py-3 text-muted">No popular items found</div>
                                <?php else: ?>
                                    <?php foreach ($popular_items as $item): ?>
                                    <div class="popular-item">
                                        <div>
                                            <div class="item-name"><?php echo htmlspecialchars($item['item_name']); ?></div>
                                            <div class="item-stats">Sold:                                                                                                                                                   <?php echo number_format($item['total_quantity']); ?></div>
                                        </div>
                                        <div class="text-success fw-bold">
                                            ₱<?php echo number_format($item['total_revenue'], 2); ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="content-card">
                            <div class="card-header">
                                <h5>Today's Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="summary-item">
                                    <span>Orders:</span>
                                    <strong><?php echo number_format($today_orders); ?></strong>
                                </div>
                                <div class="summary-item">
                                    <span>Revenue:</span>
                                    <strong class="text-success">₱<?php echo number_format($today_revenue, 2); ?></strong>
                                </div>
                                <div class="summary-item">
                                    <span>Customers:</span>
                                    <strong><?php echo number_format($today_customers); ?></strong>
                                </div>
                                <div class="summary-item">
                                    <span>Avg. Order Value:</span>
                                    <strong class="text-success">₱<?php echo $today_orders > 0 ? number_format($today_revenue / $today_orders, 2) : '0.00'; ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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

        function viewOrder(orderId) {
            window.location.href = 'admin_order_details.php?id=' + orderId;
        }

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

        // Charts
        document.addEventListener('DOMContentLoaded', function() {
            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            const revenueChart = new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels:                                                       <?php echo json_encode(array_keys($revenue_data)); ?>,
                    datasets: [{
                        label: 'Revenue (₱)',
                        data:                                                           <?php echo json_encode(array_values($revenue_data)); ?>,
                        borderColor: '#32cd32',
                        backgroundColor: 'rgba(50, 205, 50, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return '₱' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₱' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            // Order Status Chart
            const statusCtx = document.getElementById('orderStatusChart').getContext('2d');
            const statusChart = new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels:                                                       <?php echo json_encode(array_keys($order_status_data)); ?>,
                    datasets: [{
                        data:                                                           <?php echo json_encode(array_values($order_status_data)); ?>,
                        backgroundColor: [
                            '#fff3cd', // pending
                            '#d1ecf1', // confirmed
                            '#d1e7ff', // preparing
                            '#d4edda', // ready
                            '#d1e7dd', // completed
                            '#f8d7da'  // cancelled
                        ],
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 15
                            }
                        }
                    },
                    cutout: '70%'
                }
            });

            // Chart period selector
            document.getElementById('chartPeriod').addEventListener('change', function() {
                // This would typically make an AJAX call to update the chart data
                alert('Chart period changed to ' + this.value + ' days. This would update the chart with new data.');
            });
        });

        // Auto-refresh dashboard every 30 seconds
        setInterval(() => {
            // You can implement auto-refresh here
            console.log('Auto-refresh triggered');
        }, 30000);
    </script>
</body>
</html>
<?php
    // Close database connection
$conn->close();
?>