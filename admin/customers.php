<?php
    // Include configuration
    require_once '../includes/config.php';;
    require_admin_login();

    // Initialize variables
    $search        = '';
    $message       = '';
    $message_type  = '';

    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['add_user'])) {
            // Add new user
            $full_name = sanitize_input($_POST['full_name'], $conn);
            $email     = sanitize_input($_POST['email'], $conn);
            $phone     = sanitize_input($_POST['phone'], $conn);
            $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $role      = sanitize_input($_POST['role'], $conn);
            $status    = sanitize_input($_POST['status'], $conn);

            // Check if email already exists
            $check_sql  = "SELECT user_id FROM users WHERE email = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {
                $message      = "Email already exists!";
                $message_type = "danger";
            } else {
                $insert_sql  = "INSERT INTO users (full_name, email, phone, password, role, status) VALUES (?, ?, ?, ?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("ssssss", $full_name, $email, $phone, $password, $role, $status);

                if ($insert_stmt->execute()) {
                    $message      = "User added successfully!";
                    $message_type = "success";
                } else {
                    $message      = "Error adding user: " . $conn->error;
                    $message_type = "danger";
                }
                $insert_stmt->close();
            }
            $check_stmt->close();
        } elseif (isset($_POST['edit_user'])) {
            // Update user
            $user_id   = intval($_POST['user_id']);
            $full_name = sanitize_input($_POST['full_name'], $conn);
            $email     = sanitize_input($_POST['email'], $conn);
            $phone     = sanitize_input($_POST['phone'], $conn);
            $role      = sanitize_input($_POST['role'], $conn);
            $status    = sanitize_input($_POST['status'], $conn);

            // Check if email already exists (excluding current user)
            $check_sql  = "SELECT user_id FROM users WHERE email = ? AND user_id != ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("si", $email, $user_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {
                $message      = "Email already exists!";
                $message_type = "danger";
            } else {
                $update_sql  = "UPDATE users SET full_name = ?, email = ?, phone = ?, role = ?, status = ? WHERE user_id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("sssssi", $full_name, $email, $phone, $role, $status, $user_id);

                if ($update_stmt->execute()) {
                    $message      = "User updated successfully!";
                    $message_type = "success";
                } else {
                    $message      = "Error updating user: " . $conn->error;
                    $message_type = "danger";
                }
                $update_stmt->close();
            }
            $check_stmt->close();
        } elseif (isset($_POST['update_password'])) {
            // Update password
            $user_id  = intval($_POST['user_id']);
            $password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

            $update_sql  = "UPDATE users SET password = ? WHERE user_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $password, $user_id);

            if ($update_stmt->execute()) {
                $message      = "Password updated successfully!";
                $message_type = "success";
            } else {
                $message      = "Error updating password: " . $conn->error;
                $message_type = "danger";
            }
            $update_stmt->close();
        } elseif (isset($_POST['delete_user'])) {
            // Delete user
            $user_id = intval($_POST['user_id']);

            // Check if user has orders
            $check_sql  = "SELECT COUNT(*) as order_count FROM orders WHERE user_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("i", $user_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            $order_count  = $check_result->fetch_assoc()['order_count'];
            $check_stmt->close();

            if ($order_count > 0) {
                $message      = "Cannot delete user with existing orders. Deactivate instead.";
                $message_type = "warning";
            } else {
                $delete_sql  = "DELETE FROM users WHERE user_id = ?";
                $delete_stmt = $conn->prepare($delete_sql);
                $delete_stmt->bind_param("i", $user_id);

                if ($delete_stmt->execute()) {
                    $message      = "User deleted successfully!";
                    $message_type = "success";
                } else {
                    $message      = "Error deleting user: " . $conn->error;
                    $message_type = "danger";
                }
                $delete_stmt->close();
            }
        } elseif (isset($_POST['toggle_status'])) {
            // Toggle user status
            $user_id        = intval($_POST['user_id']);
            $current_status = sanitize_input($_POST['current_status'], $conn);
            $new_status     = $current_status === 'active' ? 'inactive' : 'active';

            $update_sql  = "UPDATE users SET status = ? WHERE user_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $new_status, $user_id);

            if ($update_stmt->execute()) {
                $message      = "User status updated successfully!";
                $message_type = "success";
            } else {
                $message      = "Error updating user status: " . $conn->error;
                $message_type = "danger";
            }
            $update_stmt->close();
        }
    }

    // Handle search
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $search = isset($_GET['search']) ? sanitize_input($_GET['search'], $conn) : '';
    }

    // Build query with search
    $whereConditions = [];
    $params          = [];
    $types           = '';

    // Search filter
    if (!empty($search)) {
        $whereConditions[] = "(u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ? OR u.role LIKE ? OR u.status LIKE ?)";
        $searchParam = '%' . $search . '%';
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $types .= 'sssss';
    }

    $whereClause = '';
    if (!empty($whereConditions)) {
        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    }

    // Get total count for pagination
    $count_sql  = "SELECT COUNT(*) as total FROM users u $whereClause";
    $count_stmt = $conn->prepare($count_sql);
    if (!empty($params)) {
        $count_stmt->bind_param($types, ...$params);
    }
    $count_stmt->execute();
    $count_result    = $count_stmt->get_result();
    $total_customers = $count_result->fetch_assoc()['total'];
    $count_stmt->close();

    // Pagination
    $per_page     = 10;
    $total_pages  = ceil($total_customers / $per_page);
    $current_page = isset($_GET['page']) ? max(1, min($total_pages, intval($_GET['page']))) : 1;
    $offset       = ($current_page - 1) * $per_page;

    // Main query with order counts and last order date
    $sql = "SELECT u.*,
               COUNT(o.order_id) as total_orders,
               MAX(o.created_at) as last_order_date,
               SUM(CASE WHEN o.order_status IN ('confirmed', 'preparing', 'ready', 'completed') THEN o.total_amount ELSE 0 END) as total_spent
        FROM users u
        LEFT JOIN orders o ON u.user_id = o.user_id
        $whereClause
        GROUP BY u.user_id
        ORDER BY u.created_at DESC
        LIMIT ? OFFSET ?";

    $stmt     = $conn->prepare($sql);
    $params[] = $per_page;
    $params[] = $offset;
    $types .= 'ii';

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result    = $stmt->get_result();
    $customers = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Get statistics
    $total_active         = 0;
    $total_inactive       = 0;
    $total_customers_role = 0;
    $total_staff          = 0;
    $total_admins         = 0;

    $stats_sql = "SELECT
    COUNT(*) as total,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
    SUM(CASE WHEN role = 'customer' THEN 1 ELSE 0 END) as customers,
    SUM(CASE WHEN role = 'staff' THEN 1 ELSE 0 END) as staff,
    SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins
    FROM users";

    $stats_result = $conn->query($stats_sql);
    if ($stats_result && $stats_result->num_rows > 0) {
        $stats                = $stats_result->fetch_assoc();
        $total_active         = $stats['active'];
        $total_inactive       = $stats['inactive'];
        $total_customers_role = $stats['customers'];
        $total_staff          = $stats['staff'];
        $total_admins         = $stats['admins'];
    }

    // Get admin name for display
    $admin_name    = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Admin';
    $admin_initial = strtoupper(substr($admin_name, 0, 1));
    $pageTitle = "Customer Management";
    $currentPage = "customers";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management</title>
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
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
            transform: translateY(-3px);
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
            font-size: 1.8rem;
            font-weight: 700;
            color: #2c3e50;
            line-height: 1.2;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.85rem;
            font-weight: 500;
            margin-top: 0.25rem;
        }

        .stat-icon {
            font-size: 2.2rem;
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
            /* === FIX: Copied styles from admin_menu.php === */
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h5 {
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }

        .card-body {
            padding: 1.5rem;
        }
        
        .card-body.p-0 {
            padding: 0;
        }
        
        .card-footer {
            background: #fdfdfd;
            border-top: 1px solid #f1f3f4;
            padding: 1rem 1.5rem;
        }

        /* Search Box Styles */
        .search-container {
            position: relative;
            width: 300px; /* FIX: Match admin_menu.php */
        }
        
        .search-form {
            margin-bottom: 0; /* FIX: Add helper class */
        }

        /* === FIX: Replaced this entire rule with the simpler one from admin_menu.php === */
        .search-box {
            padding-left: 2.5rem; /* This now correctly overrides the .form-control padding */
            width: 100%;
            /* It inherits border, border-radius, transition, and other padding from .form-control */
        }

        .search-box:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(50, 205, 50, 0.1);
            outline: none;
        }

        .search-icon {
            position: absolute;
            left: 0.9rem; /* FIX: Match admin_menu.php */
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            z-index: 10;
        }

        .search-clear {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            display: none;
            padding: 0.25rem;
            border-radius: 50%;
            transition: var(--transition);
        }

        .search-clear:hover {
            background: #f8f9fa;
            color: #495057;
        }

        .search-container.has-value .search-clear {
            display: block;
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
            padding: 0.4rem 0.8rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .bg-active { background: #d4edda; color: #155724; }
        .bg-inactive { background: #f8d7da; color: #721c24; }
        .bg-customer { background: #d1ecf1; color: #0c5460; }
        .bg-staff { background: #e2e3e5; color: #383d41; }
        .bg-admin { background: #d1e7ff; color: #084298; }

        .customer-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .action-buttons {
            display: flex;
            gap: 0.25rem; /* Reduced gap */
        }

        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }
        
        .action-buttons .d-inline {
            display: inline-flex !important; /* Ensure forms align */
        }


        /* Pagination */
        .pagination {
            margin-bottom: 0;
        }

        .page-link {
            color: var(--primary);
            border: 1px solid #dee2e6;
        }

        .page-item.active .page-link {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .pagination-info {
            font-size: 0.875rem;
            color: #6c757d;
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

        /* Buttons */
        .btn {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
        }
        
        .btn i {
             /* Ensure icons in buttons don't have extra margin if not specified */
             margin-right: 0;
        }
        
        .btn .bi-plus-circle {
            margin-right: 0.25rem; /* Specific margin for add button icon */
        }


        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn-outline-primary {
            color: var(--primary);
            border-color: var(--primary);
        }

        .btn-outline-primary:hover {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        .btn-outline-secondary {
            color: #6c757d;
            border-color: #6c757d;
        }

        .btn-outline-secondary:hover {
            background: #6c757d;
            border-color: #6c757d;
            color: white;
        }

        .btn-outline-warning {
            color: #ffc107;
            border-color: #ffc107;
        }

        .btn-outline-warning:hover {
            background: #ffc107;
            border-color: #ffc107;
            color: #212529;
        }

        .btn-outline-danger {
            color: #dc3545;
            border-color: #dc3545;
        }

        .btn-outline-danger:hover {
            background: #dc3545;
            border-color: #dc3545;
            color: white;
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.875rem;
        }

        /* Modal Styles */
        .modal-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border-bottom: none;
            padding: 1.5rem;
        }
        
        .modal-header .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%); /* White close button */
        }

        .modal-content {
            border: none;
            border-radius: var(--card-radius);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .modal-body {
            padding: 1.5rem;
        }
        
        /* View Modal Definition List */
        .modal-body .dl.row {
            font-size: 0.95rem;
        }
        .modal-body dt {
            font-weight: 600;
            color: #555;
        }
        .modal-body dd {
            color: #333;
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
            transition: var(--transition);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(50, 205, 50, 0.1);
            outline: none;
        }

        /* Password Strength */
        .password-strength {
            height: 5px;
            margin-top: 5px;
            border-radius: 2px;
            transition: var(--transition);
        }

        .strength-weak { background-color: #dc3545; width: 25%; }
        .strength-fair { background-color: #ffc107; width: 50%; }
        .strength-good { background-color: #28a745; width: 75%; }
        .strength-strong { background-color: #20c997; width: 100%; }

        /* Alerts */
        .alert {
            border: none;
            border-radius: var(--card-radius);
            padding: 1rem 1.5rem;
            border-left: 4px solid;
            margin-bottom: 1.5rem;
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

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border-left-color: #ffc107;
        }

        /* Search results styling */
        .search-results-info {
            font-size: 0.875rem;
            color: #6c757d;
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
            
            .search-container {
                width: 250px; /* Adjust search width for tablet */
            }
        }

        @media (max-width: 767px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .table-responsive {
                font-size: 0.85rem;
            }
            
            .content-area {
                padding: 1rem;
            }
            
            .card-header {
                padding: 1rem;
                flex-direction: column; /* Stack button and search on mobile */
                align-items: stretch;
                gap: 0.75rem;
            }
            
            .search-container {
                width: 100%; /* Full width search on mobile */
            }
            
            .card-body {
                padding: 1rem;
            }
            
            .card-footer {
                flex-direction: column;
                gap: 0.75rem;
            }
            
            .pagination {
                justify-content: center !important;
            }
        }

        @media (max-width: 575px) {
            .top-navbar {
                padding: 0.8rem 1rem;
            }
            
            .content-area {
                padding: 0.75rem;
            }
            
            .search-box {
                /* padding is now inherited from .form-control */
                padding-left: 2.5rem; /* This is the only padding override */
                font-size: 0.875rem;
            }
            
            .search-icon {
                left: 0.9rem; /* Match the new CSS */
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 0.25rem;
                align-items: flex-start;
            }
            
            .action-buttons .btn {
                margin-right: 0;
                margin-bottom: 0.25rem;
                width: 100%; /* Make action buttons full-width in column */
            }
            
            .table th,
            .table td {
                padding: 0.75rem 1rem;
            }
            
            .stats-grid {
                gap: 1rem;
            }
            
            .stat-card {
                padding: 1.25rem;
            }
            
            .stat-number {
                font-size: 1.5rem;
            }
        }

        /* Animation for search results */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(5px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .table tbody tr {
            animation: fadeIn 0.3s ease-out;
        }

        /* Empty state styling */
        .text-center.py-4 {
            color: #6c757d;
            font-style: italic;
        }

        /* Dropdown styles */
        .dropdown-menu {
            border: none;
            border-radius: var(--card-radius);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 0.5rem 0;
        }

        .dropdown-item {
            padding: 0.5rem 1rem;
            transition: var(--transition);
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
            color: var(--primary);
        }

        /* Custom scrollbar for sidebar */
        .admin-sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .admin-sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        .admin-sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        .admin-sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
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
                <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-content">
                            <div class="stat-data">
                                <div class="stat-number"><?php echo number_format($total_customers); ?></div>
                                <div class="stat-label">Total Users</div>
                            </div>
                            <i class="bi bi-people stat-icon"></i>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-content">
                            <div class="stat-data">
                                <div class="stat-number"><?php echo number_format($total_active); ?></div>
                                <div class="stat-label">Active Users</div>
                            </div>
                            <i class="bi bi-check-circle stat-icon"></i>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-content">
                            <div class="stat-data">
                                <div class="stat-number"><?php echo number_format($total_customers_role); ?></div>
                                <div class="stat-label">Customers</div>
                            </div>
                            <i class="bi bi-person stat-icon"></i>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-content">
                            <div class="stat-data">
                                <div class="stat-number"><?php echo number_format($total_staff + $total_admins); ?></div>
                                <div class="stat-label">Staff & Admins</div>
                            </div>
                            <i class="bi bi-shield-check stat-icon"></i>
                        </div>
                    </div>
                </div>

                <div class="content-card">
                    <div class="card-header">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class="bi bi-plus-circle"></i> Add New User
                        </button>
                        <form method="GET" class="search-form" id="searchForm">
                            <input type="hidden" name="page" value="<?php echo $current_page; ?>">
                            <div class="search-container" id="searchContainer">
                                <i class="bi bi-search search-icon"></i>
                                <input type="text" 
                                       class="form-control search-box" 
                                       placeholder="Search users..." 
                                       name="search"
                                       value="<?php echo htmlspecialchars($search); ?>"
                                       id="searchInput"
                                       onkeyup="searchTable()">
                                 <button type="button" class="search-clear" id="searchClear">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="dataTable">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Contact</th>
                                        <th>Role</th>
                                        <th>Status</th>                                 
                                        <th>Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($customers)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4">No users found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($customers as $customer): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="customer-avatar me-3">
                                                        <?php echo strtoupper(substr($customer['full_name'], 0, 1)); ?>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold"><?php echo htmlspecialchars($customer['full_name']); ?></div>
                                                        <small class="text-muted">ID: <?php echo $customer['user_id']; ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div><?php echo htmlspecialchars($customer['email']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($customer['phone'] ?? 'N/A'); ?></small>
                                            </td>
                                            <td>
                                                <span class="status-badge bg-<?php echo $customer['role']; ?>">
                                                    <?php echo ucfirst($customer['role']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="status-badge bg-<?php echo $customer['status']; ?>">
                                                    <?php echo ucfirst($customer['status']); ?>
                                                </span>
                                            </td>                                                                              
                                            <td>
                                                <small><?php echo date('M j, Y', strtotime($customer['created_at'])); ?></small>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="btn btn-sm btn-outline-primary view-user"
                                                            data-user-id="<?php echo $customer['user_id']; ?>"
                                                            data-full-name="<?php echo htmlspecialchars($customer['full_name']); ?>"
                                                            data-email="<?php echo htmlspecialchars($customer['email']); ?>"
                                                            data-phone="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>"
                                                            data-role="<?php echo $customer['role']; ?>"
                                                            data-status="<?php echo $customer['status']; ?>"
                                                            data-created-at="<?php echo $customer['created_at']; ?>"
                                                            data-total-orders="<?php echo $customer['total_orders']; ?>"
                                                            data-total-spent="<?php echo $customer['total_spent'] ?? 0; ?>"
                                                            title="View Details">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-warning edit-user"
                                                            data-user-id="<?php echo $customer['user_id']; ?>"
                                                            data-full-name="<?php echo htmlspecialchars($customer['full_name']); ?>"
                                                            data-email="<?php echo htmlspecialchars($customer['email']); ?>"
                                                            data-phone="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>"
                                                            data-role="<?php echo $customer['role']; ?>"
                                                            data-status="<?php echo $customer['status']; ?>"
                                                            title="Edit User">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                   
                                                    <?php if ($customer['total_orders'] == 0): ?>
                                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                                        <input type="hidden" name="user_id" value="<?php echo $customer['user_id']; ?>">
                                                        <button type="submit" name="delete_user" class="btn btn-sm btn-outline-danger" title="Delete User">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <?php if ($total_pages > 1 || $total_customers > 0): // Show footer if pagination is needed OR if there are any users to count ?>
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <div class="text-muted small search-results-info" id="searchResultsInfo">
                            Showing <?php echo count($customers); ?> of <?php echo $total_customers; ?> user(s)
                            <?php if (!empty($search)): ?>
                                - Filtered
                            <?php endif; ?>
                        </div>
                        <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-end mb-0">
                                <?php if ($current_page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $current_page - 1])); ?>">Previous</a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item<?php echo $i == $current_page ? ' active' : ''; ?>">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($current_page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $current_page + 1])); ?>">Next</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New User</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Full Name *</label>
                            <input type="text" class="form-control" name="full_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password *</label>
                            <input type="password" class="form-control" name="password" id="newPassword" required>
                            <div class="password-strength" id="passwordStrength"></div>
                            <small class="form-text text-muted">Password must be at least 8 characters</small>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Role *</label>
                                    <select class="form-select" name="role" required>
                                        <option value="customer">Customer</option>
                                        <option value="staff">Staff</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status *</label>
                                    <select class="form-select" name="status" required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="user_id" id="editUserId">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit User</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Full Name *</label>
                            <input type="text" class="form-control" name="full_name" id="editFullName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="email" id="editEmail" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone" id="editPhone">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Role *</label>
                                    <select class="form-select" name="role" id="editRole" required>
                                        <option value="customer">Customer</option>
                                        <option value="staff">Staff</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status *</label>
                                    <select class="form-select" name="status" id="editStatus" required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="edit_user" class="btn btn-primary">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="viewUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">User Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <dl class="row gy-2"> <dt class="col-sm-4">Full Name</dt>
                        <dd class="col-sm-8 mb-0" id="viewFullName"></dd>

                        <dt class="col-sm-4">Email</dt>
                        <dd class="col-sm-8 mb-0" id="viewEmail"></dd>

                        <dt class="col-sm-4">Phone</dt>
                        <dd class="col-sm-8 mb-0" id="viewPhone"></dd>

                        <dt class="col-sm-4">Role</dt>
                        <dd class="col-sm-8 mb-0">
                            <span class="status-badge" id="viewRole"></span>
                        </dd>

                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8 mb-0">
                            <span class="status-badge" id="viewStatus"></span>
                        </dd>

                        <dt class="col-sm-4">Total Orders</dt>
                        <dd class="col-sm-8 mb-0" id="viewTotalOrders"></dd>

                        <dt class="col-sm-4">Total Spent</dt>
                        <dd class="col-sm-8 mb-0 text-success fw-bold" id="viewTotalSpent"></dd>

                        <dt class="col-sm-4">Registered</dt>
                        <dd class="col-sm-8 mb-0" id="viewCreatedAt"></dd>
                    </dl>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="changePasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="user_id" id="passwordUserId">
                    <div class="modal-header">
                        <h5 class="modal-title">Change Password</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">New Password *</label>
                            <input type="password" class="form-control" name="new_password" id="changePassword" required>
                            <div class="password-strength" id="changePasswordStrength"></div>
                            <small class="form-text text-muted">Password must be at least 8 characters</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm Password *</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_password" class="btn btn-primary">Update Password</button>
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

        // Dynamic Search Functionality
        const searchInput = document.getElementById('searchInput');
        const searchContainer = document.getElementById('searchContainer');
        const searchClear = document.getElementById('searchClear');

        // Function to filter table rows based on search term
        function searchTable() {
            const input = document.getElementById("searchInput");
            const filter = input.value.toLowerCase();
            const table = document.getElementById("dataTable");
            const rows = table.getElementsByTagName("tr");
            let visibleCount = 0;

            for (let i = 1; i < rows.length; i++) { // skip header row
                const cells = rows[i].getElementsByTagName("td");
                let found = false;

                // Skip the "No users found" row
                if (cells.length === 1 && cells[0].classList.contains('text-center')) {
                    rows[i].style.display = 'none'; // Always hide this row during search
                    continue;
                }

                for (let cell of cells) {
                    if (cell.textContent.toLowerCase().includes(filter)) {
                        found = true;
                        break;
                    }
                }

                if (found) {
                    rows[i].style.display = "";
                    visibleCount++;
                } else {
                    rows[i].style.display = "none";
                }
            }
            
            // Show "No users found" message if no rows are visible
            const noUsersRow = table.querySelector('tbody tr td.text-center');
            if (noUsersRow) {
                if (visibleCount === 0) {
                    noUsersRow.parentElement.style.display = '';
                } else {
                    noUsersRow.parentElement.style.display = 'none';
                }
            }

            // This live search won't update the "Showing X of Y" count correctly
            // as it doesn't know the server-side total.
            // For client-side filtering, we can update a "showing" count.
            updateShowingCount(visibleCount, filter !== '');
        }
        
        // Handle search input visibility
        if(searchInput) {
            searchInput.addEventListener('input', function() {
                if (searchInput.value.length > 0) {
                    searchContainer.classList.add('has-value');
                } else {
                    searchContainer.classList.remove('has-value');
                }
            });
            
            // Initial check
            if (searchInput.value.length > 0) {
                searchContainer.classList.add('has-value');
            }
        }

        if(searchClear) {
            searchClear.addEventListener('click', function() {
                searchInput.value = '';
                searchContainer.classList.remove('has-value');
                searchTable(); // Re-run search to show all rows
                
                // If the search was URL-based, redirect to clear it
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.has('search')) {
                    urlParams.delete('search');
                    window.location.search = urlParams.toString();
                }
            });
        }
        
        // This function is for client-side search count only
        // The server-side count is rendered by PHP
        function updateShowingCount(visibleCount, isFiltered) {
             const searchResultsInfo = document.getElementById('searchResultsInfo');
             if (searchResultsInfo) {
                const totalUsers = <?php echo $total_customers; ?>; // Total from server
                const serverCount = <?php echo count($customers); ?>; // Count on current page from server
                
                if (isFiltered) {
                     // When live-filtering, it's tricky. We'll just say "showing X results"
                     searchResultsInfo.textContent = `Showing ${visibleCount} matching user(s) on this page`;
                } else {
                    // Revert to server-rendered text
                     searchResultsInfo.textContent = `Showing ${serverCount} of ${totalUsers} user(s)`;
                }
             }
        }


        // View User Modal
        document.querySelectorAll('.view-user').forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-user-id');
                const fullName = this.getAttribute('data-full-name');
                const email = this.getAttribute('data-email');
                const phone = this.getAttribute('data-phone') || 'N/A';
                const role = this.getAttribute('data-role');
                const status = this.getAttribute('data-status');
                const createdAt = this.getAttribute('data-created-at');
                const totalOrders = this.getAttribute('data-total-orders');
                const totalSpent = this.getAttribute('data-total-spent');

                document.getElementById('viewFullName').textContent = fullName;
                document.getElementById('viewEmail').textContent = email;
                document.getElementById('viewPhone').textContent = phone;

                const viewRole = document.getElementById('viewRole');
                viewRole.textContent = role.charAt(0).toUpperCase() + role.slice(1);
                viewRole.className = `status-badge bg-${role}`;

                const viewStatus = document.getElementById('viewStatus');
                viewStatus.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                viewStatus.className = `status-badge bg-${status}`;

                document.getElementById('viewTotalOrders').textContent = totalOrders;
                document.getElementById('viewTotalSpent').textContent = '₱' + parseFloat(totalSpent).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                document.getElementById('viewCreatedAt').textContent = new Date(createdAt).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });

                new bootstrap.Modal(document.getElementById('viewUserModal')).show();
            });
        });

        // Edit User Modal
        document.querySelectorAll('.edit-user').forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-user-id');
                const fullName = this.getAttribute('data-full-name');
                const email = this.getAttribute('data-email');
                const phone = this.getAttribute('data-phone') || '';
                const role = this.getAttribute('data-role');
                const status = this.getAttribute('data-status');

                document.getElementById('editUserId').value = userId;
                document.getElementById('editFullName').value = fullName;
                document.getElementById('editEmail').value = email;
                document.getElementById('editPhone').value = phone;
                document.getElementById('editRole').value = role;
                document.getElementById('editStatus').value = status;

                new bootstrap.Modal(document.getElementById('editUserModal')).show();
            });
        });

        // Change Password Modal
        document.querySelectorAll('.change-password').forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-user-id');
                const fullName = this.getAttribute('data-full-name');

                document.getElementById('passwordUserId').value = userId;
                document.querySelector('#changePasswordModal .modal-title').textContent = `Change Password for ${fullName}`;

                new bootstrap.Modal(document.getElementById('changePasswordModal')).show();
            });
        });

        // Password strength indicator
        function checkPasswordStrength(password, strengthElement) {
            let strength = 0;
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]+/)) strength++;
            if (password.match(/[A-Z]+/)) strength++;
            if (password.match(/[0-9]+/)) strength++;
            if (password.match(/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]+/)) strength++;

            strengthElement.className = 'password-strength';
            if (password.length === 0) {
                strengthElement.style.width = '0%';
            } else if (strength < 2) {
                strengthElement.className += ' strength-weak';
            } else if (strength < 4) {
                strengthElement.className += ' strength-fair';
            } else if (strength < 5) {
                strengthElement.className += ' strength-good';
            } else {
                strengthElement.className += ' strength-strong';
            }
        }

        document.getElementById('newPassword').addEventListener('input', function() {
            checkPasswordStrength(this.value, document.getElementById('passwordStrength'));
        });

        const changePasswordInput = document.getElementById('changePassword');
        if (changePasswordInput) {
            changePasswordInput.addEventListener('input', function() {
                checkPasswordStrength(this.value, document.getElementById('changePasswordStrength'));
            });
        }
        

        // Form validation for password confirmation
        const changePasswordForm = document.querySelector('form[name="update_password"]');
        if (changePasswordForm) {
            changePasswordForm.addEventListener('submit', function(e) {
                const password = document.querySelector('input[name="new_password"]').value;
                const confirmPassword = document.querySelector('input[name="confirm_password"]').value;

                if (password !== confirmPassword) {
                    e.preventDefault();
                    alert('Passwords do not match!');
                }

                if (password.length < 8) {
                    e.preventDefault();
                    alert('Password must be at least 8 characters long!');
                }
            });
        }
    </script>
</body>
</html>
<?php
    // Close database connection
$conn->close();
?>