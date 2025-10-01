<?php
// Start the session (required to access $_SESSION)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if it's an AJAX request
$isAjax = ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

if ($isAjax) {
    // Return JSON response for AJAX requests
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
    exit;
} else {
    // Redirect to the home page for normal requests
    header("Location: index.php");
    exit;
}
