<?php
require_once '../includes/config.php';
require_admin_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id']);
    $note     = sanitize_input($_POST['note'], $conn);
    $admin_id = $_SESSION['user_id'];

    if (! empty($note)) {
        $sql  = "INSERT INTO order_notes (order_id, admin_id, note) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $order_id, $admin_id, $note);

        if ($stmt->execute()) {
            echo "Note added successfully";
        } else {
            echo "Error adding note: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Note cannot be empty";
    }
} else {
    echo "Invalid request method";
}

$conn->close();
