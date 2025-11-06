<?php
require_once '../includes/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'No order ID provided']);
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = intval($input['order_id']);

try {
    // Security check: Verify the order belongs to the user
    $order_check_sql = "SELECT order_id FROM orders WHERE order_id = ? AND user_id = ?";
    $order_check_stmt = $conn->prepare($order_check_sql);
    $order_check_stmt->bind_param("ii", $order_id, $user_id);
    $order_check_stmt->execute();
    if ($order_check_stmt->get_result()->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'Order not found or access denied']);
        exit;
    }
    $order_check_stmt->close();

    // Initialize cart if it doesn't exist
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Get items from the old order and find their current details from menu_item
    $items_sql = "SELECT oi.item_name, oi.quantity, m.item_id, m.price AS current_price, m.image_url
                  FROM order_items oi
                  JOIN menu_item m ON oi.item_name = m.item_name
                  WHERE oi.order_id = ?";
    
    $items_stmt = $conn->prepare($items_sql);
    $items_stmt->bind_param("i", $order_id);
    $items_stmt->execute();
    $items_result = $items_result = $items_stmt->get_result();
    
    $items_added = 0;
    while ($item = $items_result->fetch_assoc()) {
        $item_id = $item['item_id'];
        
        // Use current price from menu_item, not old price from order_items
        $cart_item = [
            'id' => $item['item_id'],
            'name' => $item['item_name'],
            'price' => $item['current_price'],
            'image' => $item['image_url'],
            'quantity' => $item['quantity']
        ];

        // If item already in cart, update quantity. Otherwise, add it.
        if (isset($_SESSION['cart'][$item_id])) {
            $_SESSION['cart'][$item_id]['quantity'] += $item['quantity'];
        } else {
            $_SESSION['cart'][$item_id] = $cart_item;
        }
        $items_added++;
    }
    $items_stmt->close();

    if ($items_added > 0) {
        echo json_encode(['success' => true, 'items_added' => $items_added, 'message' => 'Items added to cart.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No valid items could be found to reorder.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>