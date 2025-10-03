<?php
    require_once 'config.php';
    require_admin_login();

    if (! isset($_GET['order_id']) || empty($_GET['order_id'])) {
        die('<div class="alert alert-danger">Order ID is required</div>');
    }

    $order_id = intval($_GET['order_id']);

    // Fetch order details
    $order_sql = "SELECT o.*,
                     CONCAT(oc.first_name, ' ', oc.last_name) as customer_name,
                     oc.email, oc.phone_number,
                     u.full_name as user_full_name,
                     oa.street_address, oa.barangay, oa.city, oa.province, oa.zip_code,
                     oa.landmarks, oa.delivery_instructions
              FROM orders o
              LEFT JOIN order_contacts oc ON o.order_id = oc.order_id
              LEFT JOIN users u ON o.user_id = u.user_id
              LEFT JOIN order_addresses oa ON o.order_id = oa.order_id
              WHERE o.order_id = ?";

    $order_stmt = $conn->prepare($order_sql);
    $order_stmt->bind_param("i", $order_id);
    $order_stmt->execute();
    $order_result = $order_stmt->get_result();

    if ($order_result->num_rows === 0) {
        die('<div class="alert alert-danger">Order not found</div>');
    }

    $order = $order_result->fetch_assoc();
    $order_stmt->close();

    // Fetch order items
    $items_sql  = "SELECT * FROM order_items WHERE order_id = ?";
    $items_stmt = $conn->prepare($items_sql);
    $items_stmt->bind_param("i", $order_id);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();

    // Fetch order notes
    $notes_sql = "SELECT onotes.*, u.full_name as admin_name
              FROM order_notes onotes
              LEFT JOIN users u ON onotes.admin_id = u.user_id
              WHERE onotes.order_id = ?
              ORDER BY onotes.created_at DESC";
    $notes_stmt = $conn->prepare($notes_sql);
    $notes_stmt->bind_param("i", $order_id);
    $notes_stmt->execute();
    $notes_result = $notes_stmt->get_result();
?>

<div class="order-details">
    <!-- Order Header -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h6 class="fw-bold text-muted">ORDER INFORMATION</h6>
            <h4 class="fw-bold">#<?php echo htmlspecialchars($order['order_number']); ?></h4>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="order-status status-<?php echo $order['order_status']; ?>">
                    <?php echo ucfirst($order['order_status']); ?>
                </span>
                <span class="order-type-badge type-<?php echo strtolower($order['order_type']); ?>">
                    <?php echo ucfirst($order['order_type']); ?>
                </span>
            </div>
        </div>
        <div class="col-md-6 text-md-end">
            <h6 class="fw-bold text-muted">ORDER DATE</h6>
            <p class="mb-1 fw-semibold"><?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
            <p class="text-muted"><?php echo date('g:i A', strtotime($order['created_at'])); ?></p>
        </div>
    </div>

    <!-- Customer Information -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0 fw-bold"><i class="bi bi-person me-2"></i>CUSTOMER INFORMATION</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Name:</strong>                                                           <?php echo htmlspecialchars($order['customer_name']); ?></p>
                    <p class="mb-1"><strong>Email:</strong>                                                            <?php echo htmlspecialchars($order['email']); ?></p>
                    <p class="mb-0"><strong>Phone:</strong>                                                            <?php echo htmlspecialchars($order['phone_number']); ?></p>
                </div>
                <div class="col-md-6">
                    <?php if (! empty($order['user_full_name'])): ?>
                        <p class="mb-0"><strong>Registered User:</strong><?php echo htmlspecialchars($order['user_full_name']); ?></p>
                    <?php else: ?>
                        <p class="mb-0"><strong>Guest Order</strong></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Delivery Information -->
    <?php if ($order['order_type'] === 'Delivery' && (! empty($order['street_address']) || ! empty($order['barangay']))): ?>
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0 fw-bold"><i class="bi bi-geo-alt me-2"></i>DELIVERY INFORMATION</h6>
        </div>
        <div class="card-body">
            <?php if (! empty($order['street_address'])): ?>
                <p class="mb-1"><strong>Address:</strong><?php echo htmlspecialchars($order['street_address']); ?></p>
            <?php endif; ?>
            <?php if (! empty($order['barangay'])): ?>
                <p class="mb-1"><strong>Barangay:</strong><?php echo htmlspecialchars($order['barangay']); ?></p>
            <?php endif; ?>
            <?php if (! empty($order['city'])): ?>
                <p class="mb-1"><strong>City:</strong><?php echo htmlspecialchars($order['city']); ?></p>
            <?php endif; ?>
            <?php if (! empty($order['province'])): ?>
                <p class="mb-1"><strong>Province:</strong><?php echo htmlspecialchars($order['province']); ?></p>
            <?php endif; ?>
            <?php if (! empty($order['zip_code'])): ?>
                <p class="mb-1"><strong>ZIP Code:</strong><?php echo htmlspecialchars($order['zip_code']); ?></p>
            <?php endif; ?>
            <?php if (! empty($order['landmarks'])): ?>
                <p class="mb-1"><strong>Landmarks:</strong><?php echo htmlspecialchars($order['landmarks']); ?></p>
            <?php endif; ?>
            <?php if (! empty($order['delivery_instructions'])): ?>
                <p class="mb-0"><strong>Instructions:</strong><?php echo htmlspecialchars($order['delivery_instructions']); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Order Items -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0 fw-bold"><i class="bi bi-list-ul me-2"></i>ORDER ITEMS</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Item</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $subtotal = 0;
                            if ($items_result->num_rows > 0):
                                while ($item = $items_result->fetch_assoc()):
                                    $subtotal += $item['total_price'];
                                ?>
		                        <tr>
		                            <td>
		                                <strong><?php echo htmlspecialchars($item['item_name']); ?></strong>
		                            </td>
		                            <td class="text-center"><?php echo $item['quantity']; ?></td>
		                            <td class="text-end">₱<?php echo number_format($item['unit_price'], 2); ?></td>
		                            <td class="text-end fw-semibold">₱<?php echo number_format($item['total_price'], 2); ?></td>
		                        </tr>
		                        <?php
                                        endwhile;
                                    else:
                                ?>
                        <tr>
                            <td colspan="4" class="text-center py-3 text-muted">No items found</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Total Amount:</td>
                            <td class="text-end fw-bold text-success">₱<?php echo number_format($order['total_amount'], 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Order Summary -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-clock me-2"></i>ORDER TIMING</h6>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Order Time:</strong>                                                                 <?php echo ucfirst($order['order_time']); ?></p>
                    <?php if ($order['order_time'] === 'Specific' && ! empty($order['specific_time'])): ?>
                        <p class="mb-0"><strong>Specific Time:</strong><?php echo date('F j, Y g:i A', strtotime($order['specific_time'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-credit-card me-2"></i>PAYMENT INFORMATION</h6>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Payment Method:</strong>                                                                     <?php echo $order['payment_method']; ?></p>
                    <p class="mb-0"><strong>Status:</strong>
                        <?php if ($order['payment_method'] === 'COD'): ?>
                            Cash on Delivery
                        <?php else: ?>
                            <?php echo ucfirst($order['payment_method']); ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Notes -->
    <?php if ($notes_result->num_rows > 0): ?>
    <div class="card mt-4">
        <div class="card-header bg-light">
            <h6 class="mb-0 fw-bold"><i class="bi bi-chat-left-text me-2"></i>ORDER NOTES</h6>
        </div>
        <div class="card-body">
            <?php while ($note = $notes_result->fetch_assoc()): ?>
            <div class="border-start border-3 border-primary ps-3 mb-3">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <strong><?php echo ! empty($note['admin_name']) ? htmlspecialchars($note['admin_name']) : 'System'; ?></strong>
                    <small class="text-muted"><?php echo date('M j, g:i A', strtotime($note['created_at'])); ?></small>
                </div>
                <p class="mb-0"><?php echo htmlspecialchars($note['note']); ?></p>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Add Note Form -->
    <div class="card mt-4">
        <div class="card-header bg-light">
            <h6 class="mb-0 fw-bold"><i class="bi bi-plus-circle me-2"></i>ADD NOTE</h6>
        </div>
        <div class="card-body">
            <form id="addNoteForm" method="POST" action="add_order_note.php">
                <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                <div class="mb-3">
                    <textarea class="form-control" name="note" rows="3" placeholder="Add a note about this order..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Add Note
                </button>
            </form>
        </div>
    </div>
</div>

<script>
// Handle add note form submission
document.getElementById('addNoteForm')?.addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('add_order_note.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        // Reload the order details to show the new note
        const orderId =                        <?php echo $order_id; ?>;
        fetch('get_order_details.php?order_id=' + orderId)
            .then(response => response.text())
            .then(html => {
                document.getElementById('viewOrderContent').innerHTML = html;
            });
    })
    .catch(error => {
        alert('Error adding note: ' + error);
    });
});
</script>

<?php
    $items_stmt->close();
    $notes_stmt->close();
$conn->close();
?>