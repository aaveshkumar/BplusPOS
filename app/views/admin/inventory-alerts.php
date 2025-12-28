<?php
$pageTitle = $title ?? 'Inventory Alerts';
require_once __DIR__ . '/_header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-3">🔔 Inventory Alerts</h2>
            <p class="text-muted">Monitor low stock and out of stock products</p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card text-center border-warning">
                <div class="card-body">
                    <h5 class="text-muted mb-2">⚠️ Low Stock Products</h5>
                    <h2 class="mb-0 text-warning"><?php echo count($lowStockProducts); ?></h2>
                    <p class="text-muted mb-0 mt-2">Products need restocking soon</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card text-center border-danger">
                <div class="card-body">
                    <h5 class="text-muted mb-2">❌ Out of Stock</h5>
                    <h2 class="mb-0 text-danger"><?php echo count($outOfStockProducts); ?></h2>
                    <p class="text-muted mb-0 mt-2">Products unavailable for sale</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-warning bg-opacity-10 border-bottom border-warning">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0">⚠️ Low Stock Products</h5>
                </div>
                <div class="col-md-6 text-end">
                    <button class="btn btn-sm btn-warning" onclick="exportLowStock()">
                        📥 Export
                    </button>
                    <button class="btn btn-sm btn-outline-primary" onclick="sendLowStockAlert()">
                        📧 Send Alert
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Product Name</th>
                            <th>SKU</th>
                            <th>Current Stock</th>
                            <th>Low Stock Threshold</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($lowStockProducts)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-success">
                                        <h5>✓ All products are well stocked!</h5>
                                        <p class="text-muted mb-0">No low stock alerts at this time</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($lowStockProducts as $product): ?>
                                <?php
                                $stockQty = $product['stock_quantity'] ?? 0;
                                $lowStockAmount = $product['low_stock_amount'] ?? 5;
                                
                                if ($stockQty == 0) {
                                    $alertClass = 'danger';
                                } elseif ($stockQty <= $lowStockAmount / 2) {
                                    $alertClass = 'danger';
                                } else {
                                    $alertClass = 'warning';
                                }
                                ?>
                                <tr class="table-<?php echo $alertClass; ?> table-<?php echo $alertClass; ?>-light">
                                    <td>
                                        <strong><?php echo htmlspecialchars($product['product_name']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['sku'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $alertClass; ?> fs-6">
                                            <?php echo number_format($stockQty); ?>
                                        </span>
                                    </td>
                                    <td><?php echo number_format($lowStockAmount); ?></td>
                                    <td>₹<?php echo number_format($product['regular_price'] ?? 0, 2); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" onclick="reorderProduct(<?php echo $product['product_id']; ?>, '<?php echo htmlspecialchars($product['product_name']); ?>')">
                                            🛒 Reorder
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary" onclick="viewProduct(<?php echo $product['product_id']; ?>)">
                                            View
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-danger bg-opacity-10 border-bottom border-danger">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0">❌ Out of Stock Products</h5>
                </div>
                <div class="col-md-6 text-end">
                    <button class="btn btn-sm btn-danger" onclick="exportOutOfStock()">
                        📥 Export
                    </button>
                    <button class="btn btn-sm btn-outline-primary" onclick="sendOutOfStockAlert()">
                        📧 Send Alert
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Product Name</th>
                            <th>SKU</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($outOfStockProducts)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="text-success">
                                        <h5>✓ No products are out of stock!</h5>
                                        <p class="text-muted mb-0">All products are available</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($outOfStockProducts as $product): ?>
                                <tr class="table-danger table-danger-light">
                                    <td>
                                        <strong><?php echo htmlspecialchars($product['product_name']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['sku'] ?? 'N/A'); ?></td>
                                    <td>₹<?php echo number_format($product['regular_price'] ?? 0, 2); ?></td>
                                    <td>
                                        <span class="badge bg-danger fs-6">
                                            Out of Stock
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" onclick="reorderProduct(<?php echo $product['product_id']; ?>, '<?php echo htmlspecialchars($product['product_name']); ?>')">
                                            🛒 Reorder
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning" onclick="updateStock(<?php echo $product['product_id']; ?>, '<?php echo htmlspecialchars($product['product_name']); ?>')">
                                            Update Stock
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="reorderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reorder Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="reorderForm">
                    <input type="hidden" id="reorderProductId">
                    <div class="mb-3">
                        <label class="form-label">Product</label>
                        <input type="text" class="form-control" id="reorderProductName" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Supplier</label>
                        <select class="form-select" id="reorderSupplier" required>
                            <option value="">Select Supplier</option>
                            <option value="supplier1">Supplier 1</option>
                            <option value="supplier2">Supplier 2</option>
                            <option value="supplier3">Supplier 3</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reorder Quantity</label>
                        <input type="number" class="form-control" id="reorderQuantity" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Expected Delivery Date</label>
                        <input type="date" class="form-control" id="deliveryDate" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="reorderNotes" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitReorder()">Place Order</button>
            </div>
        </div>
    </div>
</div>

<script>
let reorderModal;

document.addEventListener('DOMContentLoaded', function() {
    reorderModal = new bootstrap.Modal(document.getElementById('reorderModal'));
});

function reorderProduct(productId, productName) {
    document.getElementById('reorderProductId').value = productId;
    document.getElementById('reorderProductName').value = productName;
    document.getElementById('reorderQuantity').value = '';
    document.getElementById('reorderSupplier').value = '';
    document.getElementById('deliveryDate').value = '';
    document.getElementById('reorderNotes').value = '';
    
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    document.getElementById('deliveryDate').min = tomorrow.toISOString().split('T')[0];
    
    reorderModal.show();
}

function submitReorder() {
    const productName = document.getElementById('reorderProductName').value;
    const quantity = document.getElementById('reorderQuantity').value;
    const supplier = document.getElementById('reorderSupplier').value;
    const deliveryDate = document.getElementById('deliveryDate').value;
    
    if (!supplier || !quantity || !deliveryDate) {
        alert('Please fill in all required fields');
        return;
    }
    
    alert(`Reorder placed successfully!\n\nProduct: ${productName}\nQuantity: ${quantity}\nSupplier: ${supplier}\nExpected Delivery: ${deliveryDate}\n\nNote: This is a demo. In production, this would create a purchase order.`);
    reorderModal.hide();
}

function updateStock(productId, productName) {
    const newStock = prompt(`Update stock for "${productName}".\nEnter new stock quantity:`);
    
    if (newStock !== null && newStock !== '') {
        const quantity = parseInt(newStock);
        if (quantity >= 0) {
            alert(`Stock updated successfully!\n\nProduct: ${productName}\nNew Stock: ${quantity}\n\nNote: This is a demo. In production, this would update the WooCommerce database.`);
            location.reload();
        } else {
            alert('Please enter a valid quantity');
        }
    }
}

function viewProduct(productId) {
    alert(`View product details for Product ID: ${productId}\nNote: This would open the product details page.`);
}

function exportLowStock() {
    alert('Exporting low stock products to CSV...\nNote: This is a demo. In production, this would generate a CSV file.');
}

function exportOutOfStock() {
    alert('Exporting out of stock products to CSV...\nNote: This is a demo. In production, this would generate a CSV file.');
}

function sendLowStockAlert() {
    alert('Sending low stock alert email to inventory manager...\nNote: This is a demo. In production, this would send an email notification.');
}

function sendOutOfStockAlert() {
    alert('Sending out of stock alert email to inventory manager...\nNote: This is a demo. In production, this would send an email notification.');
}
</script>

<style>
.table-warning-light {
    background-color: rgba(255, 193, 7, 0.1);
}
.table-danger-light {
    background-color: rgba(220, 53, 69, 0.1);
}
</style>

<?php require_once __DIR__ . '/_footer.php'; ?>
