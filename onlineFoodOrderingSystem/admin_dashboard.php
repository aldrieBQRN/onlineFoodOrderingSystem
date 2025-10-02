<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Bente Sais Lomihan</title>
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
        /* Large devices (desktops, 992px and up) */
        @media (min-width: 992px) {
            .mobile-overlay {
                display: none !important;
            }
        }

        /* Medium devices (tablets, 768px to 991px) */
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

            .table-responsive {
                font-size: 0.9rem;
            }

            .table th, .table td {
                padding: 0.8rem 1rem;
            }
        }

        /* Small devices (landscape phones, 576px to 767px) */
        @media (max-width: 767px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .stat-number {
                font-size: 1.8rem;
            }

            .stat-icon {
                font-size: 2.2rem;
            }

            .content-area {
                padding: 1rem;
            }

            .welcome-text {
                display: none;
            }

            .top-navbar {
                padding: 0.8rem 1rem;
            }

            .card-header, .card-body {
                padding: 1rem;
            }

            .table th, .table td {
                padding: 0.7rem 0.8rem;
                font-size: 0.8rem;
            }

            .status-badge {
                padding: 0.4rem 0.7rem;
                font-size: 0.7rem;
            }
        }

        /* Extra small devices (portrait phones, less than 576px) */
        @media (max-width: 575px) {
            .page-title {
                font-size: 1.2rem;
            }

            .content-area {
                padding: 0.75rem;
            }

            .stats-grid {
                gap: 1rem;
            }

            .stat-card {
                padding: 1.25rem;
            }

            .stat-number {
                font-size: 1.6rem;
            }

            .stat-icon {
                font-size: 2rem;
            }

            .table-responsive {
                font-size: 0.8rem;
            }

            .table th, .table td {
                padding: 0.6rem 0.5rem;
            }

            .popular-item, .summary-item {
                padding: 0.8rem 0;
            }

            .item-name, .item-stats {
                font-size: 0.85rem;
            }

            /* Hide less important columns on mobile */
            .order-type, .order-date {
                display: none;
            }
        }

        /* Very small devices (less than 400px) */
        @media (max-width: 400px) {
            .stat-card {
                padding: 1rem;
            }

            .stat-content {
                flex-direction: column;
                align-items: flex-start;
            }

            .stat-icon {
                margin-top: 0.5rem;
                align-self: flex-end;
            }

            .card-header h5 {
                font-size: 1rem;
            }

            .table th:nth-child(3), .table td:nth-child(3),
            .table th:nth-child(6), .table td:nth-child(6) {
                display: none;
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
                        <a class="nav-link" href="admin_orders.php">
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
                    <h5 class="page-title">Dashboard Overview</h5>
                </div>
                <div class="user-info">
                    <span class="welcome-text d-none d-md-inline">Welcome, Admin</span>
                    <div class="dropdown">
                        <div class="user-avatar dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
                            A
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
                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-content">
                            <div class="stat-data">
                                <div class="stat-number">1,247</div>
                                <div class="stat-label">Total Orders</div>
                            </div>
                            <i class="bi bi-cart-check stat-icon"></i>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-content">
                            <div class="stat-data">
                                <div class="stat-number">24</div>
                                <div class="stat-label">Pending Orders</div>
                            </div>
                            <i class="bi bi-clock stat-icon"></i>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-content">
                            <div class="stat-data">
                                <div class="stat-number">₱42,580.75</div>
                                <div class="stat-label">Total Revenue</div>
                            </div>
                            <i class="bi bi-currency-dollar stat-icon"></i>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-content">
                            <div class="stat-data">
                                <div class="stat-number">18</div>
                                <div class="stat-label">Today's Orders</div>
                            </div>
                            <i class="bi bi-calendar-day stat-icon"></i>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Recent Orders -->
                    <div class="col-lg-8">
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
                                            <tr style="cursor: pointer;">
                                                <td class="fw-bold">#ORD-7842</td>
                                                <td>Juan Dela Cruz</td>
                                                <td class="order-type">Dine-in</td>
                                                <td class="fw-semibold">₱450.00</td>
                                                <td>
                                                    <span class="status-badge bg-completed">Completed</span>
                                                </td>
                                                <td class="order-date text-muted">Jun 12, 2:30 PM</td>
                                            </tr>
                                            <tr style="cursor: pointer;">
                                                <td class="fw-bold">#ORD-7841</td>
                                                <td>Maria Santos</td>
                                                <td class="order-type">Takeout</td>
                                                <td class="fw-semibold">₱320.50</td>
                                                <td>
                                                    <span class="status-badge bg-ready">Ready</span>
                                                </td>
                                                <td class="order-date text-muted">Jun 12, 1:45 PM</td>
                                            </tr>
                                            <tr style="cursor: pointer;">
                                                <td class="fw-bold">#ORD-7840</td>
                                                <td>Robert Lim</td>
                                                <td class="order-type">Delivery</td>
                                                <td class="fw-semibold">₱680.25</td>
                                                <td>
                                                    <span class="status-badge bg-preparing">Preparing</span>
                                                </td>
                                                <td class="order-date text-muted">Jun 12, 1:15 PM</td>
                                            </tr>
                                            <tr style="cursor: pointer;">
                                                <td class="fw-bold">#ORD-7839</td>
                                                <td>Anna Reyes</td>
                                                <td class="order-type">Dine-in</td>
                                                <td class="fw-semibold">₱275.00</td>
                                                <td>
                                                    <span class="status-badge bg-confirmed">Confirmed</span>
                                                </td>
                                                <td class="order-date text-muted">Jun 12, 12:30 PM</td>
                                            </tr>
                                            <tr style="cursor: pointer;">
                                                <td class="fw-bold">#ORD-7838</td>
                                                <td>James Garcia</td>
                                                <td class="order-type">Takeout</td>
                                                <td class="fw-semibold">₱520.75</td>
                                                <td>
                                                    <span class="status-badge bg-pending">Pending</span>
                                                </td>
                                                <td class="order-date text-muted">Jun 12, 11:45 AM</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Popular Items & Quick Stats -->
                    <div class="col-lg-4">
                        <!-- Popular Items -->
                        <div class="content-card mb-4">
                            <div class="card-header">
                                <h5>Popular Items</h5>
                            </div>
                            <div class="card-body">
                                <div class="popular-item">
                                    <div>
                                        <div class="item-name">Chicken Adobo</div>
                                        <div class="item-stats">Sold: 142</div>
                                    </div>
                                    <div class="text-success fw-bold">
                                        ₱12,780.00
                                    </div>
                                </div>
                                <div class="popular-item">
                                    <div>
                                        <div class="item-name">Pork Sinigang</div>
                                        <div class="item-stats">Sold: 98</div>
                                    </div>
                                    <div class="text-success fw-bold">
                                        ₱8,820.00
                                    </div>
                                </div>
                                <div class="popular-item">
                                    <div>
                                        <div class="item-name">Beef Caldereta</div>
                                        <div class="item-stats">Sold: 76</div>
                                    </div>
                                    <div class="text-success fw-bold">
                                        ₱7,980.00
                                    </div>
                                </div>
                                <div class="popular-item">
                                    <div>
                                        <div class="item-name">Lechon Kawali</div>
                                        <div class="item-stats">Sold: 65</div>
                                    </div>
                                    <div class="text-success fw-bold">
                                        ₱7,475.00
                                    </div>
                                </div>
                                <div class="popular-item">
                                    <div>
                                        <div class="item-name">Crispy Pata</div>
                                        <div class="item-stats">Sold: 52</div>
                                    </div>
                                    <div class="text-success fw-bold">
                                        ₱9,360.00
                                    </div>
                                </div>
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
                                    <strong>18</strong>
                                </div>
                                <div class="summary-item">
                                    <span>Revenue:</span>
                                    <strong class="text-success">₱4,250.50</strong>
                                </div>
                                <div class="summary-item">
                                    <span>Customers:</span>
                                    <strong>14</strong>
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
    </script>
</body>
</html>
