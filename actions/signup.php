<?php
include '../includes/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Get raw POST data and decode it
$input     = json_decode(file_get_contents('php://input'), true);
$full_name = trim($input['full_name'] ?? ($_POST['full_name'] ?? ''));
$email     = trim($input['email'] ?? ($_POST['email'] ?? ''));
$phone     = trim($input['phone'] ?? ($_POST['phone'] ?? ''));
$password  = $input['password'] ?? ($_POST['password'] ?? '');

// Basic validation
if (empty($full_name) || empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Full Name, Email, and Password are required.']);
    exit;
}

if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
    exit;
}

if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long.']);
    exit;
}

try {
    // Check if email already exists
    $check_sql  = "SELECT user_id FROM users WHERE email = ?";
    $check_stmt = $conn->prepare($check_sql);

    if (! $check_stmt) {
        throw new Exception('Database preparation failed: ' ( $conn->error));
    }

    $check_stmt->bind_param("s", $email);

    if (! $check_stmt->execute()) {
        throw new Exception('Execution failed: ' . $check_stmt->error);
    }

    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'This email is already registered.']);
        $check_stmt->close();
        $conn->close();
        exit;
    }
    $check_stmt->close();

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Set default values
    $role   = 'customer';
    $status = 'active';

    // Insert new user
    $insert_sql  = "INSERT INTO users (full_name, email, phone, password, role, status) VALUES (?, ?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);

    if (! $insert_stmt) {
        throw new Exception('Database preparation failed: ' . $conn->error);
    }

    $insert_stmt->bind_param("ssssss", $full_name, $email, $phone, $hashed_password, $role, $status);

    if ($insert_stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Registration successful! You can now log in.']);
    } else {
        throw new Exception('Insert failed: ' . $insert_stmt->error);
    }

    $insert_stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

$conn->close();
?>