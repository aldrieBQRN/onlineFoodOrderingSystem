<?php
// Start output buffering to prevent headers already sent errors
ob_start();

// Start the session for user authentication
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
$host = "localhost";
$user = "root";
$pass = "";
$db   = "online_ordering_system_db";

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4 for proper encoding
$conn->set_charset("utf8mb4");

// Function to sanitize input data
function sanitize_input($data, $conn)
{
    return mysqli_real_escape_string($conn, trim($data));
}

// Check if user is logged in as admin
function is_admin_logged_in()
{
    return isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Redirect to login if not admin
function require_admin_login()
{
    if (! is_admin_logged_in()) {
        header("Location: login.php");
        exit();
    }
}

// Helper function to get status badge class
function getStatusClass($status)
{
    switch ($status) {
        case 'pending':return 'pending';
        case 'confirmed':return 'confirmed';
        case 'preparing':return 'preparing';
        case 'ready':return 'ready';
        case 'completed':return 'completed';
        case 'cancelled':return 'cancelled';
        default: return 'pending';
    }
}

// Helper functions for order counts
function getPendingCount($conn)
{
    return getOrderCountByStatus($conn, 'pending');
}

function getConfirmedCount($conn)
{
    return getOrderCountByStatus($conn, 'confirmed');
}

function getPreparingCount($conn)
{
    return getOrderCountByStatus($conn, 'preparing');
}

function getReadyCount($conn)
{
    return getOrderCountByStatus($conn, 'ready');
}

function getCompletedCount($conn)
{
    return getOrderCountByStatus($conn, 'completed');
}

function getCancelledCount($conn)
{
    return getOrderCountByStatus($conn, 'cancelled');
}

function getOrderCountByStatus($conn, $status)
{
    $sql  = "SELECT COUNT(*) as count FROM orders WHERE order_status = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $status);
    $stmt->execute();
    $result = $stmt->get_result();
    $row    = $result->fetch_assoc();
    $stmt->close();
    return $row['count'];
}

// Function to build order query with filters
function buildOrderQuery($filters = [])
{
    $whereConditions = [];
    $params          = [];
    $types           = '';

    // Status filter
    if (! empty($filters['status'])) {
        $whereConditions[] = "o.order_status = ?";
        $params[]          = $filters['status'];
        $types .= 's';
    }

    // Date range filters
    if (! empty($filters['date_from'])) {
        $whereConditions[] = "DATE(o.order_date) >= ?";
        $params[]          = $filters['date_from'];
        $types .= 's';
    }

    if (! empty($filters['date_to'])) {
        $whereConditions[] = "DATE(o.order_date) <= ?";
        $params[]          = $filters['date_to'];
        $types .= 's';
    }

    // Search filter (order ID or customer name)
    if (! empty($filters['search'])) {
        $whereConditions[] = "(o.order_id = ? OR u.name LIKE ? OR u.email LIKE ?)";
        $params[]          = $filters['search'];
        $params[]          = '%' . $filters['search'] . '%';
        $params[]          = '%' . $filters['search'] . '%';
        $types .= 'sss';
    }

    // Amount range filters
    if (! empty($filters['min_amount'])) {
        $whereConditions[] = "o.total_amount >= ?";
        $params[]          = $filters['min_amount'];
        $types .= 'd';
    }

    if (! empty($filters['max_amount'])) {
        $whereConditions[] = "o.total_amount <= ?";
        $params[]          = $filters['max_amount'];
        $types .= 'd';
    }

    // Payment method filter
    if (! empty($filters['payment_method'])) {
        $whereConditions[] = "o.payment_method = ?";
        $params[]          = $filters['payment_method'];
        $types .= 's';
    }

    // Delivery type filter
    if (! empty($filters['delivery_type'])) {
        $whereConditions[] = "o.delivery_type = ?";
        $params[]          = $filters['delivery_type'];
        $types .= 's';
    }

    $whereClause = '';
    if (! empty($whereConditions)) {
        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    }

    // Sort order
    $sort_by      = $filters['sort_by'] ?? 'order_date';
    $sort_order   = $filters['sort_order'] ?? 'DESC';
    $allowed_sort = ['order_date', 'total_amount', 'customer_name'];
    $sort_by      = in_array($sort_by, $allowed_sort) ? $sort_by : 'order_date';
    $sort_order   = $sort_order === 'ASC' ? 'ASC' : 'DESC';

    $query = "SELECT o.*, u.name as customer_name, u.email as customer_email
              FROM orders o
              LEFT JOIN users u ON o.user_id = u.user_id
              $whereClause
              ORDER BY $sort_by $sort_order";

    return [
        'query'  => $query,
        'params' => $params,
        'types'  => $types,
    ];
}
