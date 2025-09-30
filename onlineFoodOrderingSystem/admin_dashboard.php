<?php
include 'config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Bente Sais Lomihan</title>
    <link rel="stylesheet" href="bootstrapfile/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .sidebar {
            min-height: calc(100vh - 56px);
            background: #f8f9fa;
            border-right: 1px solid #dee2e6;
        }
        .sidebar .nav-link {
            color: #333;
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            margin-bottom: 0.25rem;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: var(--bs-primary);
            color: white;
        }
        .dashboard-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
        .dashboard-card:hover {
            transform: translateY(-2px);
        }
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--bs-primary);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="admin_dashboard.php">BENTESAIS Admin</a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-2"></i>
                        <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="index.php"><i class="bi bi-house me-2"></i>View Site</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="d-flex flex-column flex-shrink-0 p-3">
                    <ul class="nav nav-pills flex-column mb-auto">
                        <li class="nav-item">
                            <a href="admin_dashboard.php" class="nav-link active">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="admin_products.php" class="nav-link">
                                <i class="bi bi-egg-fried me-2"></i>
                                Products
                            </a>
                        </li>
                        <li>
                            <a href="admin_categories.php" class="nav-link">
                                <i class="bi bi-tags me-2"></i>
                                Categories
                            </a>
                        </li>
                        <li>
                            <a href="admin_orders.php" class="nav-link">
                                <i class="bi bi-receipt me-2"></i>
                                Orders
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 ms-sm-auto p-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                </div>

                <!-- Stats Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="card dashboard-card">
                            <div class="card-body text-center">
                                <i class="bi bi-egg-fried fs-1 text-primary mb-3"></i>
                                <h5 class="card-title">Total Products</h5>
                                <div class="stats-number">
                                    <?php
                                    $sql = "SELECT COUNT(*) as total FROM menu_item";
                                    $result = $conn->query($sql);
                                    $row = $result->fetch_assoc();
                                    echo $row['total'];
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card dashboard-card">
                            <div class="card-body text-center">
                                <i class="bi bi-tags fs-1 text-success mb-3"></i>
                                <h5 class="card-title">Categories</h5>
                                <div class="stats-number">
                                    <?php
                                    $sql = "SELECT COUNT(*) as total FROM menu_category";
                                    $result = $conn->query($sql);
                                    $row = $result->fetch_assoc();
                                    echo $row['total'];
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card dashboard-card">
                            <div class="card-body text-center">
                                <i class="bi bi-receipt fs-1 text-warning mb-3"></i>
                                <h5 class="card-title">Today's Orders</h5>
                                <div class="stats-number">
                                    <?php
                                    $sql = "SELECT COUNT(*) as total FROM orders WHERE DATE(order_date) = CURDATE()";
                                    $result = $conn->query($sql);
                                    $row = $result->fetch_assoc();
                                    echo $row['total'];
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card dashboard-card">
                            <div class="card-body text-center">
                                <i class="bi bi-people fs-1 text-info mb-3"></i>
                                <h5 class="card-title">Total Users</h5>
                                <div class="stats-number">
                                    <?php
                                    $sql = "SELECT COUNT(*) as total FROM users";
                                    $result = $conn->query($sql);
                                    $row = $result->fetch_assoc();
                                    echo $row['total'];
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="row">
                    <div class="col-12">
                        <div class="card dashboard-card">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Orders</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Order ID</th>
                                                <th>Customer</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sql = "SELECT o.order_id, u.full_name, o.total_amount, o.status, o.order_date 
                                                    FROM orders o 
                                                    JOIN users u ON o.user_id = u.user_id 
                                                    ORDER BY o.order_date DESC 
                                                    LIMIT 5";
                                            $result = $conn->query($sql);
                                            
                                            if ($result->num_rows > 0) {
                                                while ($row = $result->fetch_assoc()) {
                                                    $status_class = '';
                                                    switch ($row['status']) {
                                                        case 'pending': $status_class = 'bg-warning'; break;
                                                        case 'confirmed': $status_class = 'bg-info'; break;
                                                        case 'preparing': $status_class = 'bg-primary'; break;
                                                        case 'ready': $status_class = 'bg-success'; break;
                                                        case 'completed': $status_class = 'bg-secondary'; break;
                                                        default: $status_class = 'bg-light';
                                                    }
                                                    
                                                    echo '<tr>';
                                                    echo '<td>#' . $row['order_id'] . '</td>';
                                                    echo '<td>' . htmlspecialchars($row['full_name']) . '</td>';
                                                    echo '<td>₱' . number_format($row['total_amount'], 2) . '</td>';
                                                    echo '<td><span class="badge ' . $status_class . '">' . ucfirst($row['status']) . '</span></td>';
                                                    echo '<td>' . date('M j, Y g:i A', strtotime($row['order_date'])) . '</td>';
                                                    echo '</tr>';
                                                }
                                            } else {
                                                echo '<tr><td colspan="5" class="text-center">No recent orders</td></tr>';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="bootstrapfile/js/bootstrap.bundle.min.js"></script>
</body>
</html>