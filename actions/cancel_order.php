<?php
include '../includes/config.php'; // <-- CORRECTED PATH
header('Content-Type: application/json');

// Check session *after* including config (which should start session)
if (! isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please log in.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$input    = json_decode(file_get_contents('php://input'), true);
$order_id = $input['order_id'] ?? null;
$user_id  = $_SESSION['user_id'];

if (! $order_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Order ID is required.']);
    exit;
}

try {
    // Check if order belongs to user and is still pending
    $check_sql  = "SELECT order_status FROM orders WHERE order_id = ? AND user_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $order_id, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Order not found or access denied.']); // More specific message
        exit;
    }

    $order = $result->fetch_assoc();

    if ($order['order_status'] !== 'pending') {
        http_response_code(400); // Bad Request is appropriate here
        echo json_encode(['success' => false, 'message' => 'Only pending orders can be cancelled. Current status: ' . $order['order_status']]);
        exit;
    }
    $check_stmt->close(); // Close the statement after fetching

    // Update order status to cancelled
    $update_sql  = "UPDATE orders SET order_status = 'cancelled', updated_at = NOW() WHERE order_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $order_id);

    if ($update_stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Order cancelled successfully.']);
    } else {
        throw new Exception('Failed to update order status in database.'); // More specific exception
    }

    $update_stmt->close();


} catch (Exception $e) {
    http_response_code(500);
    // Log the detailed error for yourself
    error_log('Error in cancel_order.php: ' . $e->getMessage()); 
    echo json_encode(['success' => false, 'message' => 'An internal server error occurred while cancelling the order.']);
}

$conn->close();
?>