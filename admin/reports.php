<?php
    // Include configuration at the very top
    require_once '../includes/config.php';
    require_admin_login();

    // --- Date Filtering ---
    // Default to last 30 days
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-29 days'));
    $end_date   = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

    // --- Summary Statistics ---
    $total_revenue   = 0;
    $total_orders    = 0;
    $total_customers = 0;

    $sql  = "SELECT
               SUM(total_amount) as revenue,
               COUNT(order_id) as orders,
               COUNT(DISTINCT user_id) as customers
             FROM orders
             WHERE order_status IN ('confirmed', 'preparing', 'ready', 'completed')
             AND DATE(created_at) BETWEEN ? AND ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $row             = $result->fetch_assoc();
        $total_revenue   = $row['revenue'] ?: 0;
        $total_orders    = $row['orders'] ?: 0;
        $total_customers = $row['customers'] ?: 0;
    }
    $stmt->close();

    // --- Sales Chart Data (Revenue per day) ---
    $sales_chart_data = [];
    $sql              = "SELECT
                   DATE(created_at) as date,
                   SUM(total_amount) as revenue
                 FROM orders
                 WHERE order_status IN ('confirmed', 'preparing', 'ready', 'completed')
                 AND DATE(created_at) BETWEEN ? AND ?
                 GROUP BY DATE(created_at)
                 ORDER BY date ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $sales_chart_data[$row['date']] = $row['revenue'];
        }
    }
    $stmt->close();

    // Fill in missing dates with 0 for the chart
    $current_date = new DateTime($start_date);
    $end_date_obj = new DateTime($end_date);
    $sales_labels = [];
    $sales_values = [];

    while ($current_date <= $end_date_obj) {
        $date_string = $current_date->format('Y-m-d');
        $sales_labels[] = $current_date->format('M j');
        $sales_values[] = isset($sales_chart_data[$date_string]) ? $sales_chart_data[$date_string] : 0;
        $current_date->modify('+1 day');
    }

    // --- Top Selling Items ---
    $top_items = [];
    $sql       = "SELECT
                oi.item_name,
                SUM(oi.quantity) as total_quantity,
                SUM(oi.total_price) as total_revenue
              FROM order_items oi
              JOIN orders o ON oi.order_id = o.order_id
              WHERE o.order_status IN ('confirmed', 'preparing', 'ready', 'completed')
              AND DATE(o.created_at) BETWEEN ? AND ?
              GROUP BY oi.item_name
              ORDER BY total_revenue DESC
              LIMIT 10";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $top_items[] = $row;
        }
    }
    $stmt->close();

    // --- Order Type Distribution ---
    $order_type_data = [];
    $sql             = "SELECT
                  order_type,
                  COUNT(order_id) as count
                FROM orders
                WHERE order_status != 'cancelled'
                AND DATE(created_at) BETWEEN ? AND ?
                GROUP BY order_type";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $order_type_data[$row['order_type']] = $row['count'];
        }
    }
    $stmt->close();

    // Get admin name for display
    $admin_name    = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Admin';
    $admin_initial = strtoupper(substr($admin_name, 0, 1));
    $pageTitle = "Reports Overview";
    $currentPage = "reports";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
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
            height: 250px;
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

        /* Filter Form */
        .filter-form {
            background: white;
            border-radius: var(--card-radius);
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
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
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
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
        }

        @media (max-width: 767px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="mobile-overlay" id="mobileOverlay"></div>

        <?php include 'admin_sidebar.php'; ?>

        <div class="admin-main">
            <?php include 'admin_header.php'; ?>

            <div class="content-area">
                
                <div class="filter-form">
                    <form method="GET" action="admin_reports.php">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                            </div>
                            <div class="col-md-5">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="stats-grid">
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
                                <div class="stat-number"><?php echo number_format($total_orders); ?></div>
                                <div class="stat-label">Total Orders</div>
                            </div>
                            <i class="bi bi-cart-check stat-icon"></i>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-content">
                            <div class="stat-data">
                                <div class="stat-number"><?php echo number_format($total_customers); ?></div>
                                <div class="stat-label">Total Customers</div>
                            </div>
                            <i class="bi bi-people stat-icon"></i>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-content">
                            <div class="stat-data">
                                <div class="stat-number">₱<?php echo $total_orders > 0 ? number_format($total_revenue / $total_orders, 2) : '0.00'; ?></div>
                                <div class="stat-label">Avg. Order Value</div>
                            </div>
                            <i class="bi bi-receipt stat-icon"></i>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="content-card">
                            <div class="card-header">
                                <h5>Sales Overview</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="salesChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="content-card">
                            <div class="card-header">
                                <h5>Order Types</h5>
                            </div>
                            <div class="card-body">
                                <div class="mini-chart-container">
                                    <canvas id="orderTypeChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12">
                         <div class="content-card">
                            <div class="card-header">
                                <h5>Top Selling Items</h5>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($top_items)): ?>
                                    <div class="empty-state">
                                        <i class="bi bi-cup-hot"></i>
                                        <h4>No Sales Data</h4>
                                        <p>No items were sold in the selected period.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Item Name</th>
                                                    <th>Quantity Sold</th>
                                                    <th>Total Revenue</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($top_items as $item): ?>
                                                <tr>
                                                    <td><strong><?php echo htmlspecialchars($item['item_name']); ?></strong></td>
                                                    <td><?php echo number_format($item['total_quantity']); ?></td>
                                                    <td class="fw-bold text-success">₱<?php echo number_format($item['total_revenue'], 2); ?></td>
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
            // Sales Chart (Line)
            const salesCtx = document.getElementById('salesChart').getContext('2d');
            const salesChart = new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($sales_labels); ?>,
                    datasets: [{
                        label: 'Revenue (₱)',
                        data: <?php echo json_encode($sales_values); ?>,
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
                                    return '₱' + context.parsed.y.toLocaleString('en-US', { minimumFractionDigits: 2 });
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

            // Order Type Chart (Doughnut)
            const orderTypeCtx = document.getElementById('orderTypeChart').getContext('2d');
            const orderTypeChart = new Chart(orderTypeCtx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode(array_keys($order_type_data)); ?>,
                    datasets: [{
                        data: <?php echo json_encode(array_values($order_type_data)); ?>,
                        backgroundColor: [
                            '#d1e7ff', // e.g., Dine-in
                            '#d1e7dd', // e.g., Takeout
                            '#e7d1ff'  // e.g., Delivery
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
        });
    </script>
</body>
</html>
<?php
    // Close database connection
$conn->close();
?>