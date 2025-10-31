<?php
    // Include configuration at the very top
    require_once '../includes/config.php';
    require_admin_login();

    // Initialize variables
    $message               = '';
    $error                 = '';
    $all_categories        = []; // For modal dropdowns
    $filtered_categories   = []; // For category tab display
    $menu_items            = [];
    $active_tab            = isset($_GET['tab']) ? $_GET['tab'] : 'items';
    $search_term           = isset($_GET['search']) ? sanitize_input($_GET['search'], $conn) : '';
    $category_search_term  = isset($_GET['category_search']) ? sanitize_input($_GET['category_search'], $conn) : ''; // NEW
    
    // --- *** FIXED: Define root-relative path for DB and server-relative path for file operations *** ---
    define('UPLOAD_DB_PATH', 'uploads/products/');
    define('UPLOAD_SERVER_PATH', '../' . UPLOAD_DB_PATH);


    // Handle form actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // ... (All your existing POST handling logic for add/edit/delete) ...
        // (No changes needed here, keeping it collapsed for brevity)
        if (isset($_POST['add_category']) || isset($_POST['update_category'])) {
            $category_name = sanitize_input($_POST['category_name'], $conn);
            $description   = sanitize_input($_POST['description'], $conn);

            if (! empty($category_name)) {
                if (isset($_POST['add_category'])) {
                    $sql             = "INSERT INTO menu_category (category_name, description) VALUES (?, ?)";
                    $success_message = "Category added successfully!";
                } else {
                    $category_id     = sanitize_input($_POST['category_id'], $conn);
                    $sql             = "UPDATE menu_category SET category_name = ?, description = ? WHERE category_id = ?";
                    $success_message = "Category updated successfully!";
                }

                $stmt = $conn->prepare($sql);
                if (isset($_POST['add_category'])) {
                    $stmt->bind_param("ss", $category_name, $description);
                } else {
                    $stmt->bind_param("ssi", $category_name, $description, $category_id);
                }

                if ($stmt->execute()) {
                    $message    = $success_message;
                    $active_tab = 'categories';
                } else {
                    $error = "Error: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = "Category name is required!";
            }
        } elseif (isset($_POST['add_item']) || isset($_POST['update_item'])) {
            $category_id = sanitize_input($_POST['category_id'], $conn);
            $item_name   = sanitize_input($_POST['item_name'], $conn);
            $price       = sanitize_input($_POST['price'], $conn);
            $badge       = sanitize_input($_POST['badge'], $conn);

            // Handle file upload
            $uploaded_image_path = '';
            if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] === UPLOAD_ERR_OK) {
                $upload_result = handleImageUpload($_FILES['item_image']);
                if ($upload_result['success']) {
                    $uploaded_image_path = $upload_result['file_path']; // This is now the DB path
                } else {
                    $error = $upload_result['error'];
                }
            }

            // For update item, keep current image if no new upload
            if (isset($_POST['update_item']) && empty($uploaded_image_path) && ! $error) {
                // Keep the current image
                $item_id      = sanitize_input($_POST['item_id'], $conn);
                $current_sql  = "SELECT image_url FROM menu_item WHERE item_id = ?";
                $current_stmt = $conn->prepare($current_sql);
                $current_stmt->bind_param("i", $item_id);
                $current_stmt->execute();
                $current_result = $current_stmt->get_result();
                $current_row    = $current_result->fetch_assoc();
                $current_stmt->close();
                $uploaded_image_path = $current_row['image_url'];
            }

            if (! $error && ! empty($item_name) && ! empty($price)) {
                if (isset($_POST['add_item'])) {
                    $sql             = "INSERT INTO menu_item (category_id, item_name, price, badge, image_url) VALUES (?, ?, ?, ?, ?)";
                    $success_message = "Menu item added successfully!";
                } else {
                    $item_id = sanitize_input($_POST['item_id'], $conn);

                    // Get current image to delete if replaced with new upload
                    if (! empty($uploaded_image_path) && isset($_FILES['item_image']) && $_FILES['item_image']['error'] === UPLOAD_ERR_OK) {
                        $current_sql  = "SELECT image_url FROM menu_item WHERE item_id = ?";
                        $current_stmt = $conn->prepare($current_sql);
                        $current_stmt->bind_param("i", $item_id);
                        $current_stmt->execute();
                        $current_result = $current_stmt->get_result();
                        $current_row    = $current_result->fetch_assoc();
                        $current_stmt->close();

                        // --- *** FIXED: Delete old file using server path *** ---
                        if ($current_row && ! empty($current_row['image_url']) && strpos($current_row['image_url'], UPLOAD_DB_PATH) !== false) {
                            $old_file_path = UPLOAD_SERVER_PATH . basename($current_row['image_url']);
                            if (file_exists($old_file_path)) {
                                @unlink($old_file_path);
                            }
                        }
                    }

                    $sql             = "UPDATE menu_item SET category_id = ?, item_name = ?, price = ?, badge = ?, image_url = ? WHERE item_id = ?";
                    $success_message = "Menu item updated successfully!";
                }

                $stmt = $conn->prepare($sql);
                if (isset($_POST['add_item'])) {
                    $stmt->bind_param("issss", $category_id, $item_name, $price, $badge, $uploaded_image_path);
                } else {
                    $stmt->bind_param("issssi", $category_id, $item_name, $price, $badge, $uploaded_image_path, $item_id);
                }

                if ($stmt->execute()) {
                    $message    = $success_message;
                    $active_tab = 'items';
                } else {
                    $error = "Error: " . $stmt->error;
                }
                $stmt->close();
            } elseif (! $error) {
                $error = "Item name and price are required!";
            }
        } elseif (isset($_POST['delete_item'])) {
            $item_id = sanitize_input($_POST['item_id'], $conn);

            // Get image path to delete file if it exists
            $current_sql  = "SELECT image_url FROM menu_item WHERE item_id = ?";
            $current_stmt = $conn->prepare($current_sql);
            $current_stmt->bind_param("i", $item_id);
            $current_stmt->execute();
            $current_result = $current_stmt->get_result();
            $current_row    = $current_result->fetch_assoc();
            $current_stmt->close();

            // --- *** FIXED: Delete file using server path *** ---
            if ($current_row && ! empty($current_row['image_url']) && strpos($current_row['image_url'], UPLOAD_DB_PATH) !== false) {
                $file_to_delete = UPLOAD_SERVER_PATH . basename($current_row['image_url']);
                if (file_exists($file_to_delete)) {
                    @unlink($file_to_delete);
                }
            }

            $sql  = "DELETE FROM menu_item WHERE item_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $item_id);

            if ($stmt->execute()) {
                $message    = "Menu item deleted successfully!";
                $active_tab = 'items';
            } else {
                $error = "Error deleting menu item: " . $stmt->error;
            }
            $stmt->close();
        } elseif (isset($_POST['delete_category'])) {
            $category_id = sanitize_input($_POST['category_id'], $conn);

            // Check if category has items
            $check_sql  = "SELECT COUNT(*) as item_count FROM menu_item WHERE category_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("i", $category_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            $row    = $result->fetch_assoc();
            $check_stmt->close();

            if ($row['item_count'] == 0) {
                $sql  = "DELETE FROM menu_category WHERE category_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $category_id);

                if ($stmt->execute()) {
                    $message    = "Category deleted successfully!";
                    $active_tab = 'categories';
                } else {
                    $error = "Error deleting category: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = "Cannot delete category. There are {$row['item_count']} menu items associated with it.";
            }
        }
    }

    // Function to handle image upload
    function handleImageUpload($file)
    {
        // --- *** FIXED: Use defined server path for file operations *** ---
        $physical_upload_dir = UPLOAD_SERVER_PATH; 
        $max_file_size = 5 * 1024 * 1024; // 5MB
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];

        // Create uploads directory if it doesn't exist
        if (! is_dir($physical_upload_dir)) {
            mkdir($physical_upload_dir, 0755, true);
        }

        $file_name = basename($file['name']);
        $file_tmp  = $file['tmp_name'];
        $file_size = $file['size'];
        $file_type = mime_content_type($file_tmp); // More reliable type check

        // Validate file size
        if ($file_size > $max_file_size) {
            return ['success' => false, 'error' => 'File size too large. Maximum size is 5MB.'];
        }

        // Validate file type
        if (! in_array($file_type, $allowed_types)) {
            return ['success' => false, 'error' => 'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.'];
        }

        // Generate unique filename
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
        $unique_name    = uniqid() . '_' . time() . '.' . $file_extension;
        
        // --- *** FIXED: Use physical path for moving file *** ---
        $physical_file_path = $physical_upload_dir . $unique_name;

        // Move uploaded file
        if (move_uploaded_file($file_tmp, $physical_file_path)) {
            // --- *** FIXED: Return the database-friendly path *** ---
            $db_path = UPLOAD_DB_PATH . $unique_name;
            return ['success' => true, 'file_path' => $db_path];
        } else {
            return ['success' => false, 'error' => 'Failed to upload file.'];
        }
    }

    // --- Load Categories ---
    
    // 1. Load ALL categories (for modal dropdowns)
    $all_cat_sql = "SELECT * FROM menu_category ORDER BY category_name";
    $result = $conn->query($all_cat_sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $all_categories[] = $row;
        }
    }
    
    // 2. Load FILTERED categories (for category tab display)
    $cat_sql = "SELECT * FROM menu_category";
    $cat_params = [];
    $cat_types  = '';
    if (! empty($category_search_term)) {
        $cat_sql .= " WHERE category_name LIKE ? OR description LIKE ?";
        $search_like = "%$category_search_term%";
        $cat_params[] = $search_like;
        $cat_params[] = $search_like;
        $cat_types .= 'ss';
    }
    $cat_sql .= " ORDER BY category_name";
    
    $cat_stmt = $conn->prepare($cat_sql);
    if (!empty($cat_params)) {
        $cat_stmt->bind_param($cat_types, ...$cat_params);
    }
    $cat_stmt->execute();
    $result = $cat_stmt->get_result();
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $filtered_categories[] = $row;
        }
    }
    $cat_stmt->close();
    
    // --- Load Menu Items with Search and Pagination ---

    // Pagination variables
    $per_page     = 10;
    $total_items  = 0;
    
    // Base SQL for counting
    $count_sql = "SELECT COUNT(mi.item_id) as total
                  FROM menu_item mi
                  LEFT JOIN menu_category mc ON mi.category_id = mc.category_id
                  WHERE 1=1";
    
    $params = [];
    $types  = '';

    if (! empty($search_term)) {
        $count_sql .= " AND (mi.item_name LIKE ? OR mc.category_name LIKE ?)";
        $search_like = "%$search_term%";
        $params[]    = $search_like;
        $params[]    = $search_like;
        $types .= 'ss';
    }

    $count_stmt = $conn->prepare($count_sql);
    if (! empty($params)) {
        $count_stmt->bind_param($types, ...$params);
    }
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_items  = $count_result->fetch_assoc()['total'];
    $count_stmt->close();
    
    $total_pages  = ceil($total_items / $per_page);
    $current_page = isset($_GET['page']) ? max(1, min($total_pages, intval($_GET['page']))) : 1;
    if ($current_page > $total_pages && $total_pages > 0) { $current_page = $total_pages; }
    $offset       = ($current_page - 1) * $per_page;


    // Main query for loading items
    $sql = "SELECT mi.*, mc.category_name
            FROM menu_item mi
            LEFT JOIN menu_category mc ON mi.category_id = mc.category_id
            WHERE 1=1";

    if (! empty($search_term)) {
        $sql .= " AND (mi.item_name LIKE ? OR mc.category_name LIKE ?)";
    }
    
    $sql .= " ORDER BY mc.category_name, mi.item_name LIMIT ? OFFSET ?";
    
    // Add pagination params
    $params[] = $per_page;
    $params[] = $offset;
    $types .= 'ii';

    $stmt = $conn->prepare($sql);
    if (! empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $menu_items[] = $row;
        }
    }
    $stmt->close();
    
    // Calculate total menu value (all items, not just paginated)
    $total_menu_value = 0;
    $value_sql = "SELECT SUM(price) as total_value FROM menu_item";
    $value_result = $conn->query($value_sql);
    if($value_result) {
        $total_menu_value = $value_result->fetch_assoc()['total_value'] ?: 0;
    }
    
    // Get featured items count (all items)
    $featured_count = 0;
    $featured_sql = "SELECT COUNT(*) as featured FROM menu_item WHERE badge IS NOT NULL AND badge != ''";
    $featured_result = $conn->query($featured_sql);
    if($featured_result) {
        $featured_count = $featured_result->fetch_assoc()['featured'] ?: 0;
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
    <title>Menu Management</title>
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
            background-color: #f5f7fb; /* Match dashboard */
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
            font-size: 1.4rem;
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
        }

        /* Content Area */
        .content-area {
            flex: 1;
            padding: 1.5rem;
        }

        /* Tabs */
        .nav-tabs {
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 1.5rem; /* Reduced margin */
        }

        .nav-tabs .nav-link {
            color: #6c757d;
            border: none;
            padding: 0.8rem 1.25rem;
            font-weight: 500;
            margin: 0;
            border-radius: 8px 8px 0 0;
        }

        .nav-tabs .nav-link.active {
            color: var(--primary);
            background: white;
            border-bottom: 3px solid var(--primary);
        }

        /* General Card */
        .content-card {
            background: white;
            border-radius: var(--card-radius);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border: none;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .card-header {
            background: white;
            border-bottom: 1px solid #f1f3f4;
            padding: 1.25rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        
        .card-body.p-0 {
            padding: 0;
        }
        
        .card-footer {
            background: #fdfdfd;
            border-top: 1px solid #f1f3f4;
            padding: 1rem 1.5rem;
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
            color: white;
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.875rem;
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
        
        .table tbody tr:hover {
            background-color: #f8fafc;
        }

        /* Badges */
        .badge {
            padding: 0.4rem 0.8rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-new { background: #d4edda; color: #155724; }
        .badge-bestseller { background: #fff3cd; color: #856404; }
        .badge-special { background: #d1ecf1; color: #0c5460; }

        /* Menu Item Image */
        .menu-item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e9ecef;
        }

        .no-image {
            width: 60px;
            height: 60px;
            background: #f8f9fa;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-size: 0.7rem;
            border: 2px dashed #dee2e6;
        }

        /* Stats Cards (Copied from admin_customers) */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
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
            margin-bottom: 1.5rem; /* Added margin */
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

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        /* Modal Styles */
        .modal-content {
            border: none;
            border-radius: var(--card-radius);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border-bottom: none;
            padding: 1.5rem;
        }
        
        .modal-header .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
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

        /* Search Box (Copied from admin_customers) */
        .search-container {
            position: relative;
            max-width: 300px;
        }

        .search-box {
            padding-left: 2.5rem;
            border-radius: 8px;
            transition: var(--transition);
            width: 100%;
        }

        .search-icon {
            position: absolute;
            left: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            z-index: 10;
        }

        /* Pagination (Copied from admin_customers) */
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
        
        /* Category Card (in tab) */
        .category-card {
            border: none;
            border-radius: var(--card-radius);
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
            transition: var(--transition);
        }
        
        .category-card:hover {
             transform: translateY(-3px);
             box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
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
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 767px) {
             .stats-grid {
                grid-template-columns: 1fr;
            }
            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }
            .card-header .search-form {
                width: 100%;
            }
            .search-container {
                max-width: 100%;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="mobile-overlay" id="mobileOverlay"></div>

        <div class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-logo">
                <h4>BENTE SAIS</h4>
                <small>Admin Panel</small>
            </div>

            <nav class="sidebar-nav">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
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
                        <a class="nav-link active" href="menu.php">
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
                        <a class="nav-link" href="../index.php" target="_blank">
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

        <div class="admin-main">
            <nav class="top-navbar">
                <div class="d-flex align-items-center">
                    <button class="btn btn-outline-secondary me-3 d-lg-none" id="sidebarToggle">
                        <i class="bi bi-list"></i>
                    </button>
                    <h5 class="page-title">Menu Management</h5>
                </div>
                <div class="user-info">
                    <span class="welcome-text d-none d-md-inline">Welcome, <?php echo htmlspecialchars($admin_name); ?></span>
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

            <div class="content-area">
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

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count($all_categories); ?></div>
                        <div class="stat-label">Total Categories</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $total_items; ?></div>
                        <div class="stat-label">Menu Items</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">₱<?php echo number_format($total_menu_value, 2); ?></div>
                        <div class="stat-label">Total Menu Value</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $featured_count; ?></div>
                        <div class="stat-label">Featured Items</div>
                    </div>
                </div>

                <ul class="nav nav-tabs" id="menuTabs">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_tab === 'items' ? 'active' : ''; ?>"
                           href="#items" data-bs-toggle="tab">
                           <i class="bi bi-cup-hot me-1"></i> Menu Items (<?php echo $total_items; ?>)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_tab === 'categories' ? 'active' : ''; ?>"
                           href="#categories" data-bs-toggle="tab">
                           <i class="bi bi-tags me-1"></i> Categories (<?php echo count($all_categories); ?>)
                        </a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade <?php echo $active_tab === 'items' ? 'show active' : ''; ?>" id="items">
                        <div class="content-card">
                            <div class="card-header">
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                                    <i class="bi bi-plus-circle"></i> Add Item
                                </button>
                                <form method="GET" class="search-form" id="searchForm">
                                    <input type="hidden" name="tab" value="items">
                                    <div class="search-container" id="searchContainer">
                                        <i class="bi bi-search search-icon"></i>
                                        <input type="text"
                                               class="form-control search-box"
                                               name="search"
                                               placeholder="Search items..."
                                               value="<?php echo htmlspecialchars($search_term); ?>"
                                               id="searchInput"
                                               onkeyup="searchTable()">
                                    </div>
                                </form>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($menu_items) && !empty($search_term)): ?>
                                    <div class="empty-state">
                                        <i class="bi bi-search"></i>
                                        <h4>No Items Found</h4>
                                        <p>Your search for "<?php echo htmlspecialchars($search_term); ?>" returned no results.</p>
                                        <a href="menu.php?tab=items" class="btn btn-primary">Clear Search</a>
                                    </div>
                                <?php elseif (empty($menu_items) && $total_items == 0): ?>
                                    <div class="empty-state">
                                        <i class="bi bi-menu-up"></i>
                                        <h4>No Menu Items Yet</h4>
                                        <p>Get started by adding your first menu item!</p>
                                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                                            <i class="bi bi-plus-circle"></i> Add First Item
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover" id="itemsTable">
                                            <thead>
                                                <tr>
                                                    <th>Item</th>
                                                    <th>Category</th>
                                                    <th>Price</th>
                                                    <th>Badge</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($menu_items as $item): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center gap-3">
                                                                <?php if (! empty($item['image_url'])): ?>
                                                                    <img src="../<?php echo htmlspecialchars($item['image_url']); ?>"
                                                                         alt="<?php echo htmlspecialchars($item['item_name']); ?>"
                                                                         class="menu-item-image">
                                                                <?php else: ?>
                                                                    <div class="no-image">
                                                                        <i class="bi bi-image"></i>
                                                                    </div>
                                                                <?php endif; ?>
                                                                <div>
                                                                    <strong><?php echo htmlspecialchars($item['item_name']); ?></strong>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                                                        <td><strong class="text-success">₱<?php echo number_format($item['price'], 2); ?></strong></td>
                                                        <td>
                                                            <?php if (! empty($item['badge'])): ?>
                                                                <?php
                                                                    $badge_class = 'badge-' . strtolower(str_replace(' ', '', $item['badge']));
                                                                ?>
                                                                <span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($item['badge']); ?></span>
                                                            <?php else: ?>
                                                                <span class="text-muted">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <div class="action-buttons">
                                                                <button class="btn btn-sm btn-outline-primary edit-item-btn"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#editItemModal"
                                                                        data-item-id="<?php echo $item['item_id']; ?>"
                                                                        data-item-name="<?php echo htmlspecialchars($item['item_name']); ?>"
                                                                        data-category-id="<?php echo $item['category_id']; ?>"
                                                                        data-price="<?php echo $item['price']; ?>"
                                                                        data-badge="<?php echo htmlspecialchars($item['badge']); ?>"
                                                                        data-image-url="<?php echo htmlspecialchars($item['image_url']); ?>">
                                                                    <i class="bi bi-pencil"></i> Edit
                                                                </button>
                                                                <button class="btn btn-sm btn-outline-danger delete-item-btn"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#deleteItemModal"
                                                                        data-item-id="<?php echo $item['item_id']; ?>"
                                                                        data-item-name="<?php echo htmlspecialchars($item['item_name']); ?>">
                                                                    <i class="bi bi-trash"></i> Delete
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php if ($total_pages > 1): ?>
                                <div class="card-footer d-flex justify-content-between align-items-center">
                                    <div class="pagination-info">
                                        Page <?php echo $current_page; ?> of <?php echo $total_pages; ?> (<?php echo $total_items; ?> items)
                                    </div>
                                    <nav>
                                        <ul class="pagination mb-0">
                                            <?php if ($current_page > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" 
                                                       href="?tab=items&search=<?php echo urlencode($search_term); ?>&page=<?php echo $current_page - 1; ?>">
                                                        <i class="bi bi-chevron-left"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>

                                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                                <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                                                    <a class="page-link" 
                                                       href="?tab=items&search=<?php echo urlencode($search_term); ?>&page=<?php echo $i; ?>">
                                                        <?php echo $i; ?>
                                                    </a>
                                                </li>
                                            <?php endfor; ?>

                                            <?php if ($current_page < $total_pages): ?>
                                                <li class="page-item">
                                                    <a class="page-link" 
                                                       href="?tab=items&search=<?php echo urlencode($search_term); ?>&page=<?php echo $current_page + 1; ?>">
                                                        <i class="bi bi-chevron-right"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </nav>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="tab-pane fade <?php echo $active_tab === 'categories' ? 'show active' : ''; ?>" id="categories">
                        <div class="content-card">
                            <div class="card-header">
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                    <i class="bi bi-plus-circle"></i> Add Category
                                </button>
                                <form method="GET" class="search-form" id="categorySearchForm">
                                    <input type="hidden" name="tab" value="categories">
                                    <div class="search-container" id="categorySearchContainer">
                                        <i class="bi bi-search search-icon"></i>
                                        <input type="text"
                                               class="form-control search-box"
                                               name="category_search"
                                               placeholder="Search categories..."
                                               value="<?php echo htmlspecialchars($category_search_term); ?>"
                                               id="categorySearchInput"
                                               onkeyup="searchCategories()">
                                    </div>
                                </form>
                            </div>
                            <div class="card-body">
                                <?php if (empty($all_categories)): ?>
                                    <div class="empty-state">
                                        <i class="bi bi-tag"></i>
                                        <h4>No Categories Yet</h4>
                                        <p>Get started by adding your first category!</p>
                                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                            <i class="bi bi-plus-circle"></i> Add First Category
                                        </button>
                                    </div>
                                <?php elseif (empty($filtered_categories) && !empty($category_search_term)): ?>
                                    <div class="empty-state" id="categoryEmptySearchState">
                                        <i class="bi bi-search"></i>
                                        <h4>No Categories Found</h4>
                                        <p>Your search for "<?php echo htmlspecialchars($category_search_term); ?>" returned no results.</p>
                                        <a href="menu.php?tab=categories" class="btn btn-primary">Clear Search</a>
                                    </div>
                                <?php else: ?>
                                    <div class="row" id="categoriesList">
                                        <?php foreach ($filtered_categories as $category): ?>
                                            <?php
                                                $count_sql  = "SELECT COUNT(*) as item_count FROM menu_item WHERE category_id = ?";
                                                $count_stmt = $conn->prepare($count_sql);
                                                $count_stmt->bind_param("i", $category['category_id']);
                                                $count_stmt->execute();
                                                $count_result = $count_stmt->get_result();
                                                $count_row    = $count_result->fetch_assoc();
                                                $count_stmt->close();
                                            ?>
                                            <div class="col-md-6 col-lg-4 category-card-wrapper">
                                                <div class="category-card h-100">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <h6 class="card-title mb-0"><?php echo htmlspecialchars($category['category_name']); ?></h6>
                                                            <span class="badge bg-primary"><?php echo $count_row['item_count']; ?> items</span>
                                                        </div>
                                                        <?php if (! empty($category['description'])): ?>
                                                            <p class="card-text text-muted small"><?php echo htmlspecialchars($category['description']); ?></p>
                                                        <?php else: ?>
                                                            <p class="card-text text-muted small fst-italic">No description provided.</p>
                                                        <?php endif; ?>
                                                        <div class="action-buttons mt-3">
                                                            <button class="btn btn-sm btn-outline-primary edit-category-btn"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#editCategoryModal"
                                                                    data-category-id="<?php echo $category['category_id']; ?>"
                                                                    data-category-name="<?php echo htmlspecialchars($category['category_name']); ?>"
                                                                    data-description="<?php echo htmlspecialchars($category['description']); ?>">
                                                                <i class="bi bi-pencil"></i> Edit
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-danger delete-category-btn"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#deleteCategoryModal"
                                                                    data-category-id="<?php echo $category['category_id']; ?>"
                                                                    data-category-name="<?php echo htmlspecialchars($category['category_name']); ?>"
                                                                    data-item-count="<?php echo $count_row['item_count']; ?>">
                                                                <i class="bi bi-trash"></i> Delete
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="empty-state" id="categoryEmptyLiveSearchState" style="display: none;">
                                        <i class="bi bi-search"></i>
                                        <h4>No Categories Found</h4>
                                        <p>Your client-side search returned no results.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addItemModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add New Item</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category_id" required>
                                <option value="" disabled selected>Select Category</option>
                                <?php foreach ($all_categories as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>"><?php echo htmlspecialchars($category['category_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Item Name</label>
                            <input type="text" class="form-control" name="item_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price</label>
                            <input type="number" class="form-control" name="price" step="0.01" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Badge</label>
                            <select class="form-select" name="badge">
                                <option value="">No Badge</option>
                                <option value="New">New</option>
                                <option value="Best Seller">Best Seller</option>
                                <option value="Special">Special</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Item Image</label>
                            <input type="file" class="form-control" name="item_image" accept="image/*">
                            <div class="form-text">Max file size: 5MB. Allowed types: JPG, PNG, GIF, WebP</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_item" class="btn btn-primary">Add Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editItemModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Item</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="item_id" id="edit_item_id">
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category_id" id="edit_item_category" required>
                                <option value="">Select Category</option>
                                <?php foreach ($all_categories as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>"><?php echo htmlspecialchars($category['category_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Item Name</label>
                            <input type="text" class="form-control" name="item_name" id="edit_item_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price</label>
                            <input type="number" class="form-control" name="price" id="edit_item_price" step="0.01" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Badge</label>
                            <select class="form-select" name="badge" id="edit_item_badge">
                                <option value="">No Badge</option>
                                <option value="New">New</option>
                                <option value="Best Seller">Best Seller</option>
                                <option value="Special">Special</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Current Image</label>
                            <div id="edit_current_image" class="mb-2">
                                </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Upload New Image</label>
                            <input type="file" class="form-control" name="item_image" accept="image/*">
                            <div class="form-text">Leave empty to keep current image. Max file size: 5MB.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_item" class="btn btn-primary">Update Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Category Name</label>
                            <input type="text" class="form-control" name="category_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description (Optional)</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="category_id" id="edit_category_id">
                        <div class="mb-3">
                            <label class="form-label">Category Name</label>
                            <input type="text" class="form-control" name="category_name" id="edit_category_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description (Optional)</label>
                            <textarea class="form-control" name="description" id="edit_category_description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_category" class="btn btn-primary">Update Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteItemModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Delete Item</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="item_id" id="delete_item_id">
                        <p>Are you sure you want to delete <strong id="delete_item_name"></strong>?</p>
                        <p class="text-danger">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="delete_item" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Delete Category</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="category_id" id="delete_category_id">
                        <p>Are you sure you want to delete <strong id="delete_category_name"></strong>?</p>
                        <p id="delete_category_warning" class="text-danger small fw-bold"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="delete_category" class="btn btn-danger" id="deleteCategoryBtn">Delete</button>
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
            const alerts = document.querySelectorAll('.alert:not(.alert-dismissible)');
            alerts.forEach(alert => {
                new bootstrap.Alert(alert).close();
            });
        }, 5000);
        
        // Handle persistent tabs on reload
        const urlParams = new URLSearchParams(window.location.search);
        const activeTabName = urlParams.get('tab') || 'items';
        const activeTabEl = document.querySelector(`.nav-tabs a[href="#${activeTabName}"]`);
        if (activeTabEl) {
            const activeTab = new bootstrap.Tab(activeTabEl);
            activeTab.show();
        }
        
        // Update URL on tab switch
        const menuTabs = document.querySelectorAll('#menuTabs a[data-bs-toggle="tab"]');
        menuTabs.forEach(tab => {
            tab.addEventListener('shown.bs.tab', event => {
                const newTab = event.target.getAttribute('href').substring(1);
                const newUrl = new URL(window.location);
                newUrl.searchParams.set('tab', newTab);
                // Clear search/page params when switching tabs
                newUrl.searchParams.delete('search');
                newUrl.searchParams.delete('category_search');
                newUrl.searchParams.delete('page');
                window.history.pushState({}, '', newUrl);
            });
        });


        // Edit Item Modal - Show current image
        document.addEventListener('click', function(e) {
            if (e.target.closest('.edit-item-btn')) {
                const btn = e.target.closest('.edit-item-btn');
                document.getElementById('edit_item_id').value = btn.dataset.itemId;
                document.getElementById('edit_item_name').value = btn.dataset.itemName;
                document.getElementById('edit_item_category').value = btn.dataset.categoryId;
                document.getElementById('edit_item_price').value = btn.dataset.price;
                document.getElementById('edit_item_badge').value = btn.dataset.badge;

                // Display current image
                const currentImageContainer = document.getElementById('edit_current_image');
                const imageUrl = btn.dataset.imageUrl;

                if (imageUrl) {
                    // --- *** FIXED: Prepend ../ to image src for admin display *** ---
                    currentImageContainer.innerHTML = `
                        <img src="../${imageUrl}" alt="Current image" class="menu-item-image" style="width: 100px; height: 100px;">
                    `;
                } else {
                    currentImageContainer.innerHTML = `
                        <div class="no-image" style="width: 100px; height: 100px;">
                            <i class="bi bi-image"></i>
                        </div>
                        <div class="form-text">No current image</div>
                    `;
                }
            }
        });

        // Clear file input when modal is closed
        document.addEventListener('hidden.bs.modal', function (e) {
            if (e.target.id === 'addItemModal' || e.target.id === 'editItemModal') {
                const fileInput = e.target.querySelector('input[type="file"]');
                if (fileInput) {
                    fileInput.value = '';
                }
            }
        });

        // Edit Category Modal
        document.addEventListener('click', function(e) {
            if (e.target.closest('.edit-category-btn')) {
                const btn = e.target.closest('.edit-category-btn');
                document.getElementById('edit_category_id').value = btn.dataset.categoryId;
                document.getElementById('edit_category_name').value = btn.dataset.categoryName;
                document.getElementById('edit_category_description').value = btn.dataset.description;
            }
        });

        // Delete Item Modal
        document.addEventListener('click', function(e) {
            if (e.target.closest('.delete-item-btn')) {
                const btn = e.target.closest('.delete-item-btn');
                document.getElementById('delete_item_id').value = btn.dataset.itemId;
                document.getElementById('delete_item_name').textContent = btn.dataset.itemName;
            }
        });

        // Delete Category Modal
        document.addEventListener('click', function(e) {
            if (e.target.closest('.delete-category-btn')) {
                const btn = e.target.closest('.delete-category-btn');
                document.getElementById('delete_category_id').value = btn.dataset.categoryId;
                document.getElementById('delete_category_name').textContent = btn.dataset.categoryName;

                const itemCount = parseInt(btn.dataset.itemCount, 10);
                const warningEl = document.getElementById('delete_category_warning');
                const deleteBtn = document.getElementById('deleteCategoryBtn');
                
                if (itemCount > 0) {
                    warningEl.textContent =
                        `Warning: This category has ${itemCount} item(s) and cannot be deleted.`;
                    deleteBtn.disabled = true;
                } else {
                    warningEl.textContent = 'This action cannot be undone.';
                    deleteBtn.disabled = false;
                }
            }
        });
        
        // Live Search Table Function
        function searchTable() {
            const input = document.getElementById("searchInput");
            if (!input) return;
            
            const filter = input.value.toLowerCase();
            const table = document.getElementById("itemsTable");
            if (!table) return;

            const rows = table.getElementsByTagName("tr");
            let visibleCount = 0;

            for (let i = 1; i < rows.length; i++) { // skip header row (index 0)
                const cells = rows[i].getElementsByTagName("td");
                let found = false;
                
                // Check cell 0 (Item Name) and 1 (Category)
                if (cells[0] && cells[0].textContent.toLowerCase().includes(filter)) {
                    found = true;
                } else if (cells[1] && cells[1].textContent.toLowerCase().includes(filter)) {
                    found = true;
                }

                if (found) {
                    rows[i].style.display = "";
                    visibleCount++;
                } else {
                    rows[i].style.display = "none";
                }
            }
        }
        
        // NEW Function for Category Search
        function searchCategories() {
            const input = document.getElementById("categorySearchInput");
            if (!input) return;
            
            const filter = input.value.toLowerCase();
            const list = document.getElementById("categoriesList");
            if (!list) return;

            const cards = list.getElementsByClassName("category-card-wrapper");
            const emptyState = document.getElementById("categoryEmptyLiveSearchState");
            let visibleCount = 0;

            for (let i = 0; i < cards.length; i++) {
                const card = cards[i];
                const title = card.querySelector(".card-title");
                const description = card.querySelector(".card-text");
                let found = false;

                if (title && title.textContent.toLowerCase().includes(filter)) {
                    found = true;
                } else if (description && description.textContent.toLowerCase().includes(filter)) {
                    found = true;
                }

                if (found) {
                    card.style.display = "";
                    visibleCount++;
                } else {
                    card.style.display = "none";
                }
            }
            
            // Toggle empty state message
            if (emptyState) {
                if (visibleCount === 0 && cards.length > 0) {
                    list.style.display = "none";
                    emptyState.style.display = "";
                } else {
                    list.style.display = ""; // Assumes list is display:flex/grid via .row
                    emptyState.style.display = "none";
                }
            }
        }
        
        // Add listeners
        const itemSearchInput = document.getElementById('searchInput');
        if (itemSearchInput) {
            itemSearchInput.addEventListener('keyup', searchTable);
        }
        
        const categorySearchInput = document.getElementById('categorySearchInput');
        if (categorySearchInput) {
            categorySearchInput.addEventListener('keyup', searchCategories);
        }
        
    </script>
</body>
</html>
<?php
    // Close database connection
$conn->close();
?>