<?php
require_once '../includes/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Get admin ID from session
$admin_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

if (!$admin_id) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Admin authentication required.']);
    exit;
}

// Get raw POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
    exit;
}

// Validate required fields
if (!isset($input['items']) || empty($input['items'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Items are required.']);
    exit;
}

try {
    $conn->begin_transaction();

    // Generate unique order number
    $order_number = 'ORD' . date('ymd') . rand(1000, 9999);

    // Determine payment method
    $payment_method = $input['payment_method'] ?? 'Cash';
    $order_status = 'completed'; // POS orders are immediately completed

    // Insert order
    $order_sql = "INSERT INTO orders (order_number, order_type, payment_method, total_amount, order_status, created_by) 
                  VALUES (?, 'DineIn', ?, ?, ?, ?)";
    $order_stmt = $conn->prepare($order_sql);
    $order_stmt->bind_param("ssdsi", $order_number, $payment_method, $input['total'], $order_status, $admin_id);
    $order_stmt->execute();
    $order_id = $conn->insert_id;
    $order_stmt->close();

    // Insert customer info (for POS, we use generic info)
    $contact_sql = "INSERT INTO order_contacts (order_id, first_name, last_name, email, phone_number) 
                    VALUES (?, 'Walk-in', 'Customer', 'pos@bentesais.com', 'N/A')";
    $contact_stmt = $conn->prepare($contact_sql);
    $contact_stmt->bind_param("i", $order_id);
    $contact_stmt->execute();
    $contact_stmt->close();

    // Insert order items
    $item_sql = "INSERT INTO order_items (order_id, item_name, quantity, unit_price, total_price) 
                 VALUES (?, ?, ?, ?, ?)";
    $item_stmt = $conn->prepare($item_sql);

    foreach ($input['items'] as $item) {
        $item_stmt->bind_param("isidd", $order_id, $item['name'], $item['quantity'], 
                              $item['price'], $item['total']);
        $item_stmt->execute();
    }
    $item_stmt->close();

    // Insert payment record for non-cash payments
    if ($payment_method !== 'Cash' && $payment_method !== 'none') {
        $payment_sql = "INSERT INTO payments (order_id, receipt_image_url, reference_number, status) 
                        VALUES (?, 'pos_payment', 'POS_$order_number', 'verified')";
        $payment_stmt = $conn->prepare($payment_sql);
        $payment_stmt->bind_param("i", $order_id);
        $payment_stmt->execute();
        $payment_stmt->close();
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Order completed successfully!',
        'order_number' => $order_number,
        'order_id' => $order_id
    ]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to process order: ' . $e->getMessage()]);
}

$conn->close();
?>