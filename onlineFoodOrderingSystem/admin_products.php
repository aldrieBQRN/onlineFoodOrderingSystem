<?php
include 'config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Handle product actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_product':
                $category_id = $_POST['category_id'];
                $item_name = trim($_POST['item_name']);
                $description = trim($_POST['description'] ?? '');
                $price = floatval($_POST['price']);
                $badge = trim($_POST['badge'] ?? '');
                $image_url = trim($_POST['image_url'] ?? '');
                $is_available = isset($_POST['is_available']) ? 1 : 0;

                $sql = "INSERT INTO menu_item (category_id, item_name, description, price, badge, image_url, is_available) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("issdssi", $category_id, $item_name, $description, $price, $badge, $image_url, $is_available);
                
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = 'Product added successfully!';
                } else {
                    $_SESSION['error_message'] = 'Error adding product: ' . $stmt->error;
                }
                break;

            case 'edit_product':
                $item_id = $_POST['item_id'];
                $category_id = $_POST['category_id'];
                $item_name = trim($_POST['item_name']);
                $description = trim($_POST['description'] ?? '');
                $price = floatval($_POST['price']);
                $badge = trim($_POST['badge'] ?? '');
                $image_url = trim($_POST['image_url'] ?? '');
                $is_available = isset($_POST['is_available']) ? 1 : 0;

                $sql = "UPDATE menu_item SET category_id = ?, item_name = ?, description = ?, price = ?, 
                        badge = ?, image_url = ?, is_available = ? WHERE item_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("issdssii", $category_id, $item_name, $description, $price, $badge, $image_url, $is_available, $item_id);
                
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = 'Product updated successfully!';
                } else {
                    $_SESSION['error_message'] = 'Error updating product: ' . $stmt->error;
                }
                break;

            case 'delete_product':
                $item_id = $_POST['item_id'];
                $sql = "DELETE FROM menu_item WHERE item_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $item_id);
                
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = 'Product deleted successfully!';
                } else {
                    $_SESSION['error_message'] = 'Error deleting product: ' . $stmt->error;
                }
                break;
        }
        header('Location: admin_products.php');
        exit;
    }
}

// Fetch products with category names
$sql = "SELECT mi.*, mc.category_name 
        FROM menu_item mi 
        JOIN menu_category mc ON mi.category_id = mc.category_id 
        ORDER BY mi.item_name";
$products = $conn->query($sql);

// Fetch categories for dropdown
$categories_sql = "SELECT * FROM menu_category ORDER BY category_name";
$categories = $conn->query($categories_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Bente Sais Lomihan</title>
    <link rel="stylesheet" href="bootstrapfile/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .sidebar {
            min-height: calc(100vh - 56px);
            background: #f8f9fa;
            border-right: 1px solid #dee2e6;
        }
        .sidebar .nav-link {
            color: #333;
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            margin-bottom: 0.25rem;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: var(--bs-primary);
            color: white;
        }
        .product-image {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        .availability-badge {
            font-size: 0.75rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="admin_dashboard.php">BENTESAIS Admin</a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-2"></i>
                        <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="index.php"><i class="bi bi-house me-2"></i>View Site</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="d-flex flex-column flex-shrink-0 p-3">
                    <ul class="nav nav-pills flex-column mb-auto">
                        <li class="nav-item">
                            <a href="admin_dashboard.php" class="nav-link">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="admin_products.php" class="nav-link active">
                                <i class="bi bi-egg-fried me-2"></i>
                                Products
                            </a>
                        </li>
                        <li>
                            <a href="admin_categories.php" class="nav-link">
                                <i class="bi bi-tags me-2"></i>
                                Categories
                            </a>
                        </li>
                        <li>
                            <a href="admin_orders.php" class="nav-link">
                                <i class="bi bi-receipt me-2"></i>
                                Orders
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 ms-sm-auto p-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Manage Products</h1>
                    <button class="btn btn-theme" data-bs-toggle="modal" data-bs-target="#addProductModal">
                        <i class="bi bi-plus-circle me-2"></i>Add Product
                    </button>
                </div>

                <!-- Messages -->
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['success_message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['error_message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>

                <!-- Products Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Badge</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($products->num_rows > 0): ?>
                                        <?php while ($product = $products->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <?php if (!empty($product['image_url'])): ?>
                                                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                                             alt="<?php echo htmlspecialchars($product['item_name']); ?>" 
                                                             class="product-image">
                                                    <?php else: ?>
                                                        <div class="product-image bg-light d-flex align-items-center justify-content-center">
                                                            <i class="bi bi-image text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($product['item_name']); ?></td>
                                                <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                                <td>₱<?php echo number_format($product['price'], 2); ?></td>
                                                <td>
                                                    <?php if (!empty($product['badge'])): ?>
                                                        <span class="badge badge-theme"><?php echo htmlspecialchars($product['badge']); ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo $product['is_available'] ? 'bg-success' : 'bg-danger'; ?> availability-badge">
                                                        <?php echo $product['is_available'] ? 'Available' : 'Unavailable'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary edit-product" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editProductModal"
                                                            data-product='<?php echo json_encode($product); ?>'>
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger delete-product" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#deleteProductModal"
                                                            data-product-id="<?php echo $product['item_id']; ?>"
                                                            data-product-name="<?php echo htmlspecialchars($product['item_name']); ?>">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No products found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_product">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Product Name</label>
                                <input type="text" class="form-control" name="item_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category</label>
                                <select class="form-select" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php while ($category = $categories->fetch_assoc()): ?>
                                        <option value="<?php echo $category['category_id']; ?>">
                                            <?php echo htmlspecialchars($category['category_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Price</label>
                                <input type="number" class="form-control" name="price" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Badge (Optional)</label>
                                <input type="text" class="form-control" name="badge" placeholder="e.g., Best Seller, New">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Image URL</label>
                            <input type="url" class="form-control" name="image_url" placeholder="https://example.com/image.jpg">
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_available" id="addAvailable" checked>
                            <label class="form-check-label" for="addAvailable">Available for ordering</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-theme">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div class="modal fade" id="editProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_product">
                        <input type="hidden" name="item_id" id="editItemId">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Product Name</label>
                                <input type="text" class="form-control" name="item_name" id="editItemName" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category</label>
                                <select class="form-select" name="category_id" id="editCategoryId" required>
                                    <option value="">Select Category</option>
                                    <?php 
                                    $categories->data_seek(0); // Reset pointer
                                    while ($category = $categories->fetch_assoc()): ?>
                                        <option value="<?php echo $category['category_id']; ?>">
                                            <?php echo htmlspecialchars($category['category_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="editDescription" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Price</label>
                                <input type="number" class="form-control" name="price" id="editPrice" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Badge (Optional)</label>
                                <input type="text" class="form-control" name="badge" id="editBadge" placeholder="e.g., Best Seller, New">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Image URL</label>
                            <input type="url" class="form-control" name="image_url" id="editImageUrl" placeholder="https://example.com/image.jpg">
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_available" id="editAvailable">
                            <label class="form-check-label" for="editAvailable">Available for ordering</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-theme">Update Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Product Modal -->
    <div class="modal fade" id="deleteProductModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete_product">
                        <input type="hidden" name="item_id" id="deleteItemId">
                        <p>Are you sure you want to delete "<span id="deleteProductName" class="fw-bold"></span>"?</p>
                        <p class="text-danger">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="bootstrapfile/js/bootstrap.bundle.min.js"></script>
    <script>
        // Edit product modal
        document.querySelectorAll('.edit-product').forEach(button => {
            button.addEventListener('click', function() {
                const product = JSON.parse(this.dataset.product);
                document.getElementById('editItemId').value = product.item_id;
                document.getElementById('editItemName').value = product.item_name;
                document.getElementById('editCategoryId').value = product.category_id;
                document.getElementById('editDescription').value = product.description || '';
                document.getElementById('editPrice').value = product.price;
                document.getElementById('editBadge').value = product.badge || '';
                document.getElementById('editImageUrl').value = product.image_url || '';
                document.getElementById('editAvailable').checked = product.is_available == 1;
            });
        });

        // Delete product modal
        document.querySelectorAll('.delete-product').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('deleteItemId').value = this.dataset.productId;
                document.getElementById('deleteProductName').textContent = this.dataset.productName;
            });
        });
    </script>
</body>
</html>