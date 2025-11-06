<?php
// This file is admin/admin_header.php

// Set a default title if it wasn't set on the page.
if (!isset($pageTitle)) {
    $pageTitle = 'Admin Panel';
}
?>
<nav class="top-navbar">
    <div class="d-flex align-items-center">
        <button class="btn btn-outline-secondary me-3 d-lg-none" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <h5 class="page-title"><?php echo htmlspecialchars($pageTitle); ?></h5>
    </div>
    <div class="user-info">
        <span class="welcome-text d-none d-md-inline">Welcome, <?php echo htmlspecialchars($admin_name); ?></span>
        <div class="dropdown">
            <div class="user-avatar dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
                <?php echo htmlspecialchars($admin_initial); ?>
            </div>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="../actions/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
        </div>
    </div>
</nav>