<?php
include '../includes/config.php'; // <-- CORRECTED PATH
header('Content-Type: application/json');

// Check session *after* including config (which should start session)
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Use 401 Unauthorized
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

if (!isset($_GET['order_id'])) {
    http_response_code(400); // Use 400 Bad Request
    echo json_encode(['error' => 'No order ID provided']);
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = intval($_GET['order_id']);
$data = [];

try {
    // 1. Get Order Details (Security Check included)
    $order_sql = "SELECT * FROM orders WHERE order_id = ? AND user_id = ?";
    $order_stmt = $conn->prepare($order_sql);
    $order_stmt->bind_param("ii", $order_id, $user_id);
    $order_stmt->execute();
    $order_result = $order_stmt->get_result();

    if ($order_result->num_rows == 0) {
        http_response_code(404); // Use 404 Not Found
        echo json_encode(['error' => 'Order not found or access denied']);
        exit;
    }
    $data['order'] = $order_result->fetch_assoc();
    $order_stmt->close();

    // 2. Get Contact Details
    $contact_sql = "SELECT * FROM order_contacts WHERE order_id = ?";
    $contact_stmt = $conn->prepare($contact_sql);
    $contact_stmt->bind_param("i", $order_id);
    $contact_stmt->execute();
    $data['contact'] = $contact_stmt->get_result()->fetch_assoc();
    $contact_stmt->close();

    // 3. Get Address Details (if it's a delivery)
    $data['address'] = null;
    if ($data['order']['order_type'] == 'Delivery') {
        $address_sql = "SELECT * FROM order_addresses WHERE order_id = ?";
        $address_stmt = $conn->prepare($address_sql);
        $address_stmt->bind_param("i", $order_id);
        $address_stmt->execute();
        $address_result = $address_stmt->get_result();
        if ($address_result->num_rows > 0) {
            $data['address'] = $address_result->fetch_assoc();
        }
        $address_stmt->close();
    }

    // 4. Get Item Details
    $items_sql = "SELECT oi.*, m.image_url 
                  FROM order_items oi
                  LEFT JOIN menu_item m ON oi.item_name = m.item_name
                  WHERE oi.order_id = ?";
    $items_stmt = $conn->prepare($items_sql);
    $items_stmt->bind_param("i", $order_id);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();
    $data['items'] = [];
    while ($item = $items_result->fetch_assoc()) {
        $data['items'][] = $item;
    }
    $items_stmt->close();

    echo json_encode($data); // Success

} catch (Exception $e) {
    http_response_code(500); // Use 500 Internal Server Error
    // Log the detailed error for yourself, but show a generic message to the user
    error_log('Error in get_order_details.php: ' . $e->getMessage()); 
    echo json_encode(['error' => 'An internal server error occurred.']);
}

$conn->close();
?>