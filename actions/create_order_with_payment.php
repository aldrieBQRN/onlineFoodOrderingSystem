<?php
require_once '../includes/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

// Check for all required data
if (!isset($_POST['orderData']) || !isset($_FILES['receipt_image'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing order data or receipt image.']);
    exit;
}

// --- 1. FILE VALIDATION ---
$file = $_FILES['receipt_image'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'File upload error. Code: ' . $file['error']]);
    exit;
}

$allowed_mime_types = ['image/jpeg', 'image/png', 'image/jpg'];
$file_mime_type = mime_content_type($file['tmp_name']);
if (!in_array($file_mime_type, $allowed_mime_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG and PNG are allowed.']);
    exit;
}

// --- 2. DECODE ORDER DATA ---
$input = json_decode($_POST['orderData'], true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid order data format.']);
    exit;
}

$reference_number = isset($_POST['reference_number']) ? trim($_POST['reference_number']) : null;
$user_id = $_SESSION['user_id'];

try {
    $conn->begin_transaction();

    // --- 3. CREATE THE ORDER ---
    $order_number = 'ORD' . date('ymd') . rand(1000, 9999);
    $order_sql = "INSERT INTO orders (user_id, order_number, order_type, order_time, payment_method, total_amount, order_status)
                  VALUES (?, ?, ?, ?, ?, ?, 'pending')"; // Start as 'pending'
    $order_stmt = $conn->prepare($order_sql);
    $order_stmt->bind_param("issssd", $user_id, $order_number, $input['order_type'],
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

    // --- 4. UPLOAD THE FILE ---
    $upload_dir = '../uploads/receipts/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = 'order_' . $order_id . '_' . time() . '.' . $file_extension;
    $upload_path = $upload_dir . $new_filename;
    $db_path = 'uploads/receipts/' . $new_filename; // Path to store in DB

    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        throw new Exception('Failed to move uploaded file.');
    }

    // --- 5. INSERT THE PAYMENT RECORD ---
    $sql = "INSERT INTO payments (order_id, receipt_image_url, reference_number) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $order_id, $db_path, $reference_number);
    $stmt->execute();
    $stmt->close();
    
    // --- 6. COMMIT TRANSACTION ---
    $conn->commit();
    
    echo json_encode([
        'success'      => true, 
        'message'      => 'Payment uploaded successfully!',
        'order_number' => $order_number
    ]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    // Delete the file if it was uploaded but the DB failed
    if (isset($upload_path) && file_exists($upload_path)) {
        unlink($upload_path);
    }
    echo json_encode(['success' => false, 'message' => 'Failed to place order: ' . $e->getMessage()]);
}

$conn->close();
?>