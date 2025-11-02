<?php
require_once '../includes/config.php';
header('Content-Type: application/json');

if (!isset($_GET['method'])) {
    echo json_encode(['success' => false, 'message' => 'Payment method not specified.']);
    exit;
}

$method_name = $_GET['method'];

try {
    $stmt = $conn->prepare("SELECT qr_code_url, account_name, account_number, instructions 
                            FROM payment_settings 
                            WHERE payment_method_name = ? AND is_active = 1");
    $stmt->bind_param("s", $method_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        // IMPORTANT: Prepend '../' to the image URL if it's stored relative to the project root
        // Adjust this logic if your path is stored differently
        if (!empty($data['qr_code_url']) && strpos($data['qr_code_url'], 'http') !== 0) {
             // Assuming 'uploads/qr/gcash.png' is stored, it becomes '../uploads/qr/gcash.png'
             // If your path is already correct from the DB, you can remove this logic
             $data['qr_code_url'] = '../' . ltrim($data['qr_code_url']);
        }

        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Payment method details not found or is inactive.']);
    }
    
    $stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>