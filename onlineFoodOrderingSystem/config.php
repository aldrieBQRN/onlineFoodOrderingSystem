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
