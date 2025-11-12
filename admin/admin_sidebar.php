<?php
// This file is admin/admin_sidebar.php

// Set a default page if the variable isn't set
if (!isset($currentPage)) {
    $currentPage = ''; // Default to no active page
}
?>
<div class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-logo">
        <h4>Quick Crave</h4>
        <small>Admin Panel</small>
    </div>

    <nav class="sidebar-nav">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo ($currentPage === 'dashboard') ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($currentPage === 'pos') ? 'active' : ''; ?>" href="pos.php">
                    <i class="bi bi-grid-3x3-gap"></i>
                    <span>Point of Sale (POS)</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($currentPage === 'orders') ? 'active' : ''; ?>" href="orders.php">
                    <i class="bi bi-bag-check"></i>
                    <span>Orders</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($currentPage === 'menu') ? 'active' : ''; ?>" href="menu.php">
                    <i class="bi bi-menu-button"></i>
                    <span>Menu Management</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($currentPage === 'customers') ? 'active' : ''; ?>" href="customers.php">
                    <i class="bi bi-people"></i>
                    <span>Customers</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($currentPage === 'reports') ? 'active' : ''; ?>" href="reports.php">
                    <i class="bi bi-graph-up"></i>
                    <span>Reports</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../index.php" target="_blank">
                    <i class="bi bi-shop"></i>
                    <span>View Site</span>
                </a>
            </li>
            <li class="nav-item mt-4">
                <a class="nav-link" href="../actions/logout.php">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </nav>
</div>