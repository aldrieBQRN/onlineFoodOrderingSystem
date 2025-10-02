<?php

include 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Get raw POST data and decode it
$input    = json_decode(file_get_contents('php://input'), true);
$email    = trim($input['email'] ?? ($_POST['email'] ?? ''));
$password = $input['password'] ?? ($_POST['password'] ?? '');

// Basic validation
if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email and Password are required.']);
    exit;
}

if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
    exit;
}

try {
    // Fetch user by email
    $sql  = "SELECT user_id, full_name, password, role, status FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);

    if (! $stmt) {
        throw new Exception('Database preparation failed: ' . $conn->error);
    }

    $stmt->bind_param("s", $email);

    if (! $stmt->execute()) {
        throw new Exception('Execution failed: ' . $stmt->error);
    }

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            if ($user['status'] !== 'active') {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Your account is inactive.']);
            } else {
                // Login successful - Set session variables
                $_SESSION['user_id']   = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role']      = $user['role'];
                $_SESSION['email']     = $email;

                echo json_encode([
                    'success'   => true,
                    'message'   => 'Login successful!',
                    'full_name' => $user['full_name'],
                    'email'     => $email,
                    'user_id'   => $user['user_id'],
                    'role'      => $user['role'], // Add role to response
                ]);
            }
        } else {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
        }
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

if (isset($stmt)) {
    $stmt->close();
}

$conn->close();
