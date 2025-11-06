<?php
    // Include configuration at the very top
    require_once '../includes/config.php';
    require_admin_login();

    // Initialize variables
    $message       = '';
    $message_type  = '';
    $user_id       = $_SESSION['user_id'];
    $user_data     = [];

    // Fetch current user data
    $sql  = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
    } else {
        // This shouldn't happen if user is logged in, but handle gracefully
        header('Location: ../actions/logout.php');
        exit;
    }
    $stmt->close();

    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['update_profile'])) {
            // Update Profile Information
            $full_name = sanitize_input($_POST['full_name'], $conn);
            $email     = sanitize_input($_POST['email'], $conn);
            $phone     = sanitize_input($_POST['phone'], $conn);

            // Check if email already exists (excluding current user)
            $check_sql  = "SELECT user_id FROM users WHERE email = ? AND user_id != ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("si", $email, $user_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {
                $message      = "Email already exists!";
                $message_type = "danger";
            } else {
                $update_sql  = "UPDATE users SET full_name = ?, email = ?, phone = ? WHERE user_id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("sssi", $full_name, $email, $phone, $user_id);

                if ($update_stmt->execute()) {
                    // Update session with new full name
                    $_SESSION['full_name'] = $full_name;
                    
                    // Refresh user data
                    $user_data['full_name'] = $full_name;
                    $user_data['email']     = $email;
                    $user_data['phone']     = $phone;
                    
                    $message      = "Profile updated successfully!";
                    $message_type = "success";
                } else {
                    $message      = "Error updating profile: " . $conn->error;
                    $message_type = "danger";
                }
                $update_stmt->close();
            }
            $check_stmt->close();
            
        } elseif (isset($_POST['change_password'])) {
            // Change Password
            $current_password = $_POST['current_password'];
            $new_password     = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            // Verify current password
            if (password_verify($current_password, $user_data['password'])) {
                // Check if new passwords match
                if ($new_password === $confirm_password) {
                    // Check password length
                    if (strlen($new_password) >= 8) {
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        
                        $update_sql  = "UPDATE users SET password = ? WHERE user_id = ?";
                        $update_stmt = $conn->prepare($update_sql);
                        $update_stmt->bind_param("si", $hashed_password, $user_id);

                        if ($update_stmt->execute()) {
                            // Update user data with new password hash
                            $user_data['password'] = $hashed_password;
                            
                            $message      = "Password changed successfully!";
                            $message_type = "success";
                        } else {
                            $message      = "Error changing password: " . $conn->error;
                            $message_type = "danger";
                        }
                        $update_stmt->close();
                    } else {
                        $message      = "New password must be at least 8 characters long!";
                        $message_type = "danger";
                    }
                } else {
                    $message      = "New passwords do not match!";
                    $message_type = "danger";
                }
            } else {
                $message      = "Current password is incorrect!";
                $message_type = "danger";
            }
        }
    }

    // Get admin name for display
    $admin_name    = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Admin';
    $admin_initial = strtoupper(substr($admin_name, 0, 1));
    $pageTitle = "My Profile";
    $currentPage = "profile";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #32cd32;
            --primary-light: #5ce65c;
            --primary-dark: #28a428;
            --secondary: #2a9a2a;
            --success: #32cd32;
            --dark: #1a2e1a;
            --light: #f8f9fa;
            --sidebar-width: 260px;
            --header-height: 70px;
            --card-radius: 12px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fb;
            color: #333;
            line-height: 1.6;
            overflow-x: hidden;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .admin-sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--dark) 0%, #2a4a2a 100%);
            color: white;
            transition: var(--transition);
            position: fixed;
            height: 100vh;
            z-index: 1000;
            box-shadow: 2px 0 15px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
        }

        .sidebar-logo {
            padding: 1.5rem 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
            height: var(--header-height);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .sidebar-logo h4 {
            font-weight: 700;
            margin-bottom: 0;
            color: white;
            font-size: 1.5rem;
        }

        .sidebar-logo small {
            font-size: 0.75rem;
            opacity: 0.7;
            margin-top: 0.25rem;
        }

        .sidebar-nav {
            padding: 1.5rem 0;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.85rem 1.5rem;
            margin: 0.15rem 0.8rem;
            border-radius: 8px;
            transition: var(--transition);
            font-weight: 500;
            display: flex;
            align-items: center;
            white-space: nowrap;
        }

        .nav-link:hover, .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }

        .nav-link.active {
            background: var(--primary);
            box-shadow: 0 4px 12px rgba(50, 205, 50, 0.3);
        }

        .nav-link i {
            width: 22px;
            margin-right: 12px;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        /* Main Content Area */
        .admin-main {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: var(--transition);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            width: calc(100% - var(--sidebar-width));
        }

        /* Top Navigation */
        .top-navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            padding: 0.8rem 1.5rem;
            height: var(--header-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .page-title {
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
            font-size: 1.4rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .welcome-text {
            font-weight: 500;
            color: #495057;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            cursor: pointer;
            flex-shrink: 0;
        }

        /* Content Area */
        .content-area {
            flex: 1;
            padding: 1.5rem;
        }

        /* Profile Header */
        .profile-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border-radius: var(--card-radius);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .profile-avatar-large {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: 700;
            border: 4px solid rgba(255, 255, 255, 0.3);
            margin-bottom: 1rem;
        }

        .profile-name {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .profile-role {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.4rem 1rem;
            border-radius: 50px;
            font-size: 0.9rem;
            display: inline-block;
        }

        /* Content Cards */
        .content-card {
            background: white;
            border-radius: var(--card-radius);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border: none;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            background: white;
        }

        .card-header h5 {
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Forms */
        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            transition: var(--transition);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(50, 205, 50, 0.1);
            outline: none;
        }

        .form-control:disabled {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }

        /* Buttons */
        .btn {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
        }

        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn-outline-secondary {
            color: #6c757d;
            border-color: #6c757d;
        }

        .btn-outline-secondary:hover {
            background: #6c757d;
            border-color: #6c757d;
            color: white;
        }

        /* Alerts */
        .alert {
            border: none;
            border-radius: var(--card-radius);
            padding: 1rem 1.5rem;
            border-left: 4px solid;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left-color: #28a745;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left-color: #dc3545;
        }

        /* Password Strength */
        .password-strength {
            height: 5px;
            margin-top: 5px;
            border-radius: 2px;
            transition: var(--transition);
        }

        .strength-weak { background-color: #dc3545; width: 25%; }
        .strength-fair { background-color: #ffc107; width: 50%; }
        .strength-good { background-color: #28a745; width: 75%; }
        .strength-strong { background-color: #20c997; width: 100%; }

        /* Info Items */
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f1f3f4;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 500;
            color: #6c757d;
        }

        .info-value {
            font-weight: 600;
            color: #2c3e50;
        }

        /* Mobile Menu Overlay */
        .mobile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        /* Responsive Breakpoints */
        @media (max-width: 991px) {
            .admin-sidebar {
                transform: translateX(-100%);
                width: var(--sidebar-width);
            }

            .admin-sidebar.mobile-open {
                transform: translateX(0);
            }

            .admin-main {
                margin-left: 0;
                width: 100%;
            }

            .mobile-overlay.active {
                display: block;
            }

            .profile-header {
                text-align: center;
            }

            .profile-avatar-large {
                margin-left: auto;
                margin-right: auto;
            }
        }

        @media (max-width: 767px) {
            .content-area {
                padding: 1rem;
            }

            .profile-header {
                padding: 1.5rem;
            }

            .profile-name {
                font-size: 1.5rem;
            }

            .card-body {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="mobile-overlay" id="mobileOverlay"></div>

        <?php include 'admin_sidebar.php'; ?>

        <div class="admin-main">
            <?php include 'admin_header.php'; ?>

            <div class="content-area">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <i class="bi bi-<?php echo $message_type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'; ?> me-2"></i>
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Profile Header -->
                <div class="profile-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="profile-avatar-large">
                            <?php echo strtoupper(substr($user_data['full_name'], 0, 1)); ?>
                        </div>
                        <div>
                            <div class="profile-name"><?php echo htmlspecialchars($user_data['full_name']); ?></div>
                            <span class="profile-role">
                                <i class="bi bi-shield-check me-1"></i>
                                <?php echo ucfirst($user_data['role']); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Profile Information -->
                    <div class="col-lg-6">
                        <div class="content-card">
                            <div class="card-header">
                                <h5><i class="bi bi-person-circle"></i> Profile Information</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" 
                                               class="form-control" 
                                               name="full_name" 
                                               value="<?php echo htmlspecialchars($user_data['full_name']); ?>" 
                                               required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email Address</label>
                                        <input type="email" 
                                               class="form-control" 
                                               name="email" 
                                               value="<?php echo htmlspecialchars($user_data['email']); ?>" 
                                               required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Phone Number</label>
                                        <input type="text" 
                                               class="form-control" 
                                               name="phone" 
                                               value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>" 
                                               placeholder="Optional">
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" name="update_profile" class="btn btn-primary">
                                            <i class="bi bi-check-circle"></i> Save Changes
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Change Password -->
                    <div class="col-lg-6">
                        <div class="content-card">
                            <div class="card-header">
                                <h5><i class="bi bi-lock"></i> Change Password</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" id="changePasswordForm">
                                    <div class="mb-3">
                                        <label class="form-label">Current Password</label>
                                        <input type="password" 
                                               class="form-control" 
                                               name="current_password" 
                                               required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">New Password</label>
                                        <input type="password" 
                                               class="form-control" 
                                               name="new_password" 
                                               id="newPassword" 
                                               required>
                                        <div class="password-strength" id="passwordStrength"></div>
                                        <small class="form-text text-muted">Password must be at least 8 characters</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Confirm New Password</label>
                                        <input type="password" 
                                               class="form-control" 
                                               name="confirm_password" 
                                               id="confirmPassword" 
                                               required>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" name="change_password" class="btn btn-primary">
                                            <i class="bi bi-key"></i> Update Password
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Account Information -->
                        <div class="content-card">
                            <div class="card-header">
                                <h5><i class="bi bi-info-circle"></i> Account Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="info-item">
                                    <span class="info-label">User ID</span>
                                    <span class="info-value">#<?php echo $user_data['user_id']; ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Role</span>
                                    <span class="info-value"><?php echo ucfirst($user_data['role']); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Status</span>
                                    <span class="info-value">
                                        <span class="badge <?php echo $user_data['status'] === 'active' ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo ucfirst($user_data['status']); ?>
                                        </span>
                                    </span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Member Since</span>
                                    <span class="info-value">
                                        <?php echo date('F j, Y', strtotime($user_data['created_at'])); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile sidebar toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const adminSidebar = document.getElementById('adminSidebar');
        const mobileOverlay = document.getElementById('mobileOverlay');

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                adminSidebar.classList.toggle('mobile-open');
                mobileOverlay.classList.toggle('active');
            });
        }

        if (mobileOverlay) {
            mobileOverlay.addEventListener('click', function() {
                adminSidebar.classList.remove('mobile-open');
                mobileOverlay.classList.remove('active');
            });
        }

        // Close sidebar when clicking on a link (for mobile)
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth < 992) {
                    adminSidebar.classList.remove('mobile-open');
                    mobileOverlay.classList.remove('active');
                }
            });
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 992) {
                adminSidebar.classList.remove('mobile-open');
                mobileOverlay.classList.remove('active');
            }
        });

        // Password strength indicator
        function checkPasswordStrength(password, strengthElement) {
            let strength = 0;
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]+/)) strength++;
            if (password.match(/[A-Z]+/)) strength++;
            if (password.match(/[0-9]+/)) strength++;
            if (password.match(/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]+/)) strength++;

            strengthElement.className = 'password-strength';
            if (password.length === 0) {
                strengthElement.style.width = '0%';
            } else if (strength < 2) {
                strengthElement.className += ' strength-weak';
            } else if (strength < 4) {
                strengthElement.className += ' strength-fair';
            } else if (strength < 5) {
                strengthElement.className += ' strength-good';
            } else {
                strengthElement.className += ' strength-strong';
            }
        }

        const newPasswordInput = document.getElementById('newPassword');
        if (newPasswordInput) {
            newPasswordInput.addEventListener('input', function() {
                checkPasswordStrength(this.value, document.getElementById('passwordStrength'));
            });
        }

        // Form validation for password confirmation
        const changePasswordForm = document.getElementById('changePasswordForm');
        if (changePasswordForm) {
            changePasswordForm.addEventListener('submit', function(e) {
                const newPassword = document.getElementById('newPassword').value;
                const confirmPassword = document.getElementById('confirmPassword').value;

                if (newPassword !== confirmPassword) {
                    e.preventDefault();
                    alert('New passwords do not match!');
                    return false;
                }

                if (newPassword.length < 8) {
                    e.preventDefault();
                    alert('Password must be at least 8 characters long!');
                    return false;
                }
            });
        }

        // Auto-dismiss alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                if (bsAlert) {
                    bsAlert.close();
                }
            });
        }, 5000);
    </script>
</body>
</html>
<?php
    // Close database connection
    $conn->close();
?>