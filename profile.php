<?php
    include 'includes/config.php';

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php');
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $message = '';
    $message_type = '';

    // Fetch current user data
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        // Force logout if user not found
        header('Location: actions/logout.php');
        exit;
    }
    $stmt->close();

    // Handle Form Submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        // --- Update Profile Info ---
        if (isset($_POST['update_profile'])) {
            $full_name = trim($_POST['full_name']);
            $email     = trim($_POST['email']);
            $phone     = trim($_POST['phone']);

            if (empty($full_name) || empty($email)) {
                $message = "Full Name and Email are required.";
                $message_type = "danger";
            } else {
                // Check if email is taken by another user
                $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
                $check_stmt->bind_param("si", $email, $user_id);
                $check_stmt->execute();
                
                if ($check_stmt->get_result()->num_rows > 0) {
                    $message = "This email address is already registered to another account.";
                    $message_type = "danger";
                } else {
                    // Update Database
                    $update_stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE user_id = ?");
                    $update_stmt->bind_param("sssi", $full_name, $email, $phone, $user_id);
                    
                    if ($update_stmt->execute()) {
                        $message = "Profile details updated successfully!";
                        $message_type = "success";
                        
                        // Update Session & Local Data
                        $_SESSION['full_name'] = $full_name;
                        $user['full_name'] = $full_name;
                        $user['email'] = $email;
                        $user['phone'] = $phone;
                    } else {
                        $message = "Database error: " . $conn->error;
                        $message_type = "danger";
                    }
                    $update_stmt->close();
                }
                $check_stmt->close();
            }
        } 
        
        // --- Change Password ---
        elseif (isset($_POST['change_password'])) {
            $current_password = $_POST['current_password'];
            $new_password     = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            if (password_verify($current_password, $user['password'])) {
                if ($new_password === $confirm_password) {
                    if (strlen($new_password) >= 8) {
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        
                        $pwd_stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                        $pwd_stmt->bind_param("si", $hashed_password, $user_id);
                        
                        if ($pwd_stmt->execute()) {
                            $message = "Password changed successfully!";
                            $message_type = "success";
                            // Update local data to ensure next check passes if needed
                            $user['password'] = $hashed_password;
                        } else {
                            $message = "Error updating password.";
                            $message_type = "danger";
                        }
                        $pwd_stmt->close();
                    } else {
                        $message = "New password must be at least 8 characters long.";
                        $message_type = "danger";
                    }
                } else {
                    $message = "New passwords do not match.";
                    $message_type = "danger";
                }
            } else {
                $message = "Current password is incorrect.";
                $message_type = "danger";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Quick Crave</title>
    <link rel="stylesheet" href="assets/bootstrapfile/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .profile-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #f0f0f0;
            overflow: hidden;
            height: 100%;
        }
        
        .profile-header-bg {
            background: linear-gradient(135deg, #32cd32 0%, #228b22 100%);
            padding: 3rem 2rem;
            color: white;
            text-align: center;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: white;
            color: #32cd32;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: bold;
            margin: 0 auto 1rem;
            border: 4px solid rgba(255,255,255,0.3);
            text-transform: uppercase;
        }

        .card-header-custom {
            background: #f8f9fa;
            padding: 1.25rem;
            border-bottom: 1px solid #e9ecef;
            font-weight: 600;
        }

        .form-control:focus {
            border-color: #32cd32;
            box-shadow: 0 0 0 0.25rem rgba(50, 205, 50, 0.15);
        }
    </style>
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <main style="padding-top: 100px; min-height: 100vh; background: #F7F1F4;">
        <div class="container py-5">
            
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show mb-4" role="alert">
                    <i class="bi bi-<?php echo ($message_type == 'success') ? 'check-circle-fill' : 'exclamation-triangle-fill'; ?> me-2"></i>
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="profile-card">
                        <div class="profile-header-bg">
                            <div class="profile-avatar">
                                <?php echo substr($user['full_name'], 0, 1); ?>
                            </div>
                            <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($user['full_name']); ?></h4>
                            <p class="mb-0 opacity-75"><?php echo htmlspecialchars($user['email']); ?></p>
                            <span class="badge bg-white text-success mt-2 rounded-pill px-3">Customer</span>
                        </div>
                        <div class="p-4">
                            <div class="d-flex justify-content-between mb-3 border-bottom pb-3">
                                <span class="text-muted">Member Since</span>
                                <span class="fw-bold"><?php echo date('M Y', strtotime($user['created_at'])); ?></span>
                            </div>
                            <div class="d-grid">
                                <a href="my_orders.php" class="btn btn-outline-theme rounded-pill">
                                    <i class="bi bi-bag-check me-2"></i> View My Orders
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="d-flex flex-column gap-4">
                        
                        <div class="profile-card">
                            <div class="card-header-custom">
                                <i class="bi bi-person-lines-fill me-2 text-success"></i> Personal Information
                            </div>
                            <div class="p-4">
                                <form method="POST" action="">
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label">Full Name</label>
                                            <input type="text" name="full_name" class="form-control" 
                                                   value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Email Address</label>
                                            <input type="email" name="email" class="form-control" 
                                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Phone Number</label>
                                            <input type="text" name="phone" class="form-control" 
                                                   value="<?php echo htmlspecialchars($user['phone']); ?>" placeholder="09123456789">
                                        </div>
                                        <div class="col-12 text-end mt-4">
                                            <button type="submit" name="update_profile" class="btn btn-theme rounded-pill px-4">
                                                Save Changes
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="profile-card">
                            <div class="card-header-custom">
                                <i class="bi bi-shield-lock me-2 text-success"></i> Security
                            </div>
                            <div class="p-4">
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label class="form-label">Current Password</label>
                                        <input type="password" name="current_password" class="form-control" required>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">New Password</label>
                                            <input type="password" name="new_password" class="form-control" 
                                                   minlength="8" placeholder="Min. 8 characters" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Confirm New Password</label>
                                            <input type="password" name="confirm_password" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="text-end mt-4">
                                        <button type="submit" name="change_password" class="btn btn-outline-theme rounded-pill px-4">
                                            Update Password
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/modals.php'; ?>

    <script src="assets/bootstrapfile/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>