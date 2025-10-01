<?php
include 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Check if user is logged in
if (! isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please log in to place an order.']);
    exit;
}

// Get raw POST data
$input = json_decode(file_get_contents('php://input'), true);

if (! $input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
    exit;
}

// Validate required fields
$required_fields = ['contact', 'order_type', 'payment_method', 'order_time', 'items', 'total'];
foreach ($required_fields as $field) {
    if (! isset($input[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit;
    }
}

try {
    $conn->begin_transaction();

    // Generate unique order number
    $order_number = 'BSL' . date('Ymd') . strtoupper(uniqid());

    // Insert order
    $order_sql = "INSERT INTO orders (user_id, order_number, order_type, order_time, payment_method, total_amount)
                  VALUES (?, ?, ?, ?, ?, ?)";
    $order_stmt = $conn->prepare($order_sql);
    $order_stmt->bind_param("issssd", $_SESSION['user_id'], $order_number, $input['order_type'],
        $input['order_time'], $input['payment_method'], $input['total']);
    $order_stmt->execute();
    $order_id = $conn->insert_id;
    $order_stmt->close();

    // Insert contact details
    $contact_sql = "INSERT INTO order_contacts (order_id, first_name, last_name, email, phone_number)
                    VALUES (?, ?, ?, ?, ?)";
    $contact_stmt = $conn->prepare($contact_sql);
    $contact_stmt->bind_param("issss", $order_id, $input['contact']['firstName'],
        $input['contact']['lastName'], $input['contact']['email'],
        $input['contact']['phone']);
    $contact_stmt->execute();
    $contact_stmt->close();

    // Insert address if delivery order
    if ($input['order_type'] === 'Delivery' && isset($input['delivery'])) {
        $address_sql = "INSERT INTO order_addresses (order_id, street_address, barangay, city, province, zip_code, landmarks, delivery_instructions)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $address_stmt = $conn->prepare($address_sql);
        $address_stmt->bind_param("isssssss", $order_id, $input['delivery']['street'],
            $input['delivery']['barangay'], $input['delivery']['city'],
            $input['delivery']['province'], $input['delivery']['zipCode'],
            $input['delivery']['landmarks'], $input['delivery']['instructions']);
        $address_stmt->execute();
        $address_stmt->close();
    }

    // Insert order items
    $item_sql = "INSERT INTO order_items (order_id, item_name, quantity, unit_price, total_price)
                 VALUES (?, ?, ?, ?, ?)";
    $item_stmt = $conn->prepare($item_sql);

    foreach ($input['items'] as $item) {
        $total_price = $item['price'] * $item['quantity'];
        $item_stmt->bind_param("isidd", $order_id, $item['name'], $item['quantity'],
            $item['price'], $total_price);
        $item_stmt->execute();
    }
    $item_stmt->close();

    $conn->commit();

    echo json_encode([
        'success'      => true,
        'message'      => 'Order placed successfully!',
        'order_number' => $order_number,
        'order_id'     => $order_id,
    ]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to place order: ' . $e->getMessage()]);
}

$conn->close();
