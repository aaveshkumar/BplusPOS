<?php
$pageTitle = $title ?? 'Inventory Management';
require_once __DIR__ . '/_header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-3">📦 Inventory Management</h2>
            <p class="text-muted">Manage and track your product inventory</p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="text-muted mb-2">Total Products</h5>
                    <h2 class="mb-0"><?php echo number_format($stats['total_products']); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-success">
                <div class="card-body">
                    <h5 class="text-muted mb-2">In Stock</h5>
                    <h2 class="mb-0 text-success"><?php echo number_format($stats['in_stock']); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-warning">
                <div class="card-body">
                    <h5 class="text-muted mb-2">Low Stock</h5>
                    <h2 class="mb-0 text-warning"><?php echo number_format($stats['low_stock']); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-danger">
                <div class="card-body">
                    <h5 class="text-muted mb-2">Out of Stock</h5>
                    <h2 class="mb-0 text-danger"><?php echo number_format($stats['out_of_stock']); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom">
            <div class="row align-items-center g-2">
                <div class="col-12 col-lg-3">
                    <h5 class="mb-0">Product Inventory</h5>
                </div>
                <div class="col-12 col-lg-9">
                    <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                        <button class="btn btn-sm btn-success" onclick="showAddProductModal()">
                            <i class="fas fa-plus"></i> Add New Product
                        </button>
                        <div class="btn-group flex-wrap">
                            <button class="btn btn-sm btn-outline-secondary active" onclick="filterStock('all')">All</button>
                            <button class="btn btn-sm btn-outline-success" onclick="filterStock('instock')">In Stock</button>
                            <button class="btn btn-sm btn-outline-warning" onclick="filterStock('low')">Low</button>
                            <button class="btn btn-sm btn-outline-danger" onclick="filterStock('outofstock')">Out</button>
                        </div>
                        <input type="search" id="searchInventory" class="form-control form-control-sm" style="max-width: 200px;" placeholder="Search...">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="inventoryTable">
                    <thead class="table-light">
                        <tr>
                            <th>Product Name</th>
                            <th class="d-none d-md-table-cell">SKU</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th class="d-none d-lg-table-cell">Alert At</th>
                            <th class="d-none d-sm-table-cell">Status</th>
                            <th style="min-width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <p class="text-muted mb-0">No products found</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                                <?php
                                $stockQty = $product['stock_quantity'] ?? 0;
                                $lowStockAmount = $product['low_stock_amount'] ?? 5;
                                $stockStatus = $product['stock_status'] ?? 'outofstock';
                                
                                if ($stockStatus === 'outofstock') {
                                    $statusClass = 'danger';
                                    $statusText = 'Out of Stock';
                                } elseif ($stockQty <= $lowStockAmount) {
                                    $statusClass = 'warning';
                                    $statusText = 'Low Stock';
                                } else {
                                    $statusClass = 'success';
                                    $statusText = 'In Stock';
                                }
                                ?>
                                <tr data-status="<?php echo $stockStatus; ?>" data-low-stock="<?php echo ($stockQty <= $lowStockAmount) ? '1' : '0'; ?>" data-product-id="<?php echo $product['product_id']; ?>">
                                    <td>
                                        <strong><?php echo htmlspecialchars($product['product_name']); ?></strong>
                                        <div class="d-sm-none">
                                            <span class="badge bg-<?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                        </div>
                                    </td>
                                    <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($product['sku'] ?? 'N/A'); ?></td>
                                    <td>₹<?php echo number_format($product['regular_price'] ?? 0, 2); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $statusClass; ?> bg-opacity-25 text-<?php echo $statusClass; ?>">
                                            <?php echo number_format($stockQty); ?>
                                        </span>
                                    </td>
                                    <td class="d-none d-lg-table-cell"><?php echo number_format($lowStockAmount); ?></td>
                                    <td class="d-none d-sm-table-cell">
                                        <span class="badge bg-<?php echo $statusClass; ?>">
                                            <?php echo $statusText; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-primary" onclick="adjustStock(<?php echo $product['product_id']; ?>, '<?php echo htmlspecialchars($product['product_name']); ?>', <?php echo $stockQty; ?>)" title="Adjust Stock">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-danger" onclick="deleteProduct(<?php echo $product['product_id']; ?>, '<?php echo htmlspecialchars($product['product_name']); ?>')" title="Delete Product">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
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

<div class="modal fade" id="adjustStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Adjust Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="adjustStockForm">
                    <input type="hidden" id="adjustProductId" name="product_id">
                    <div class="mb-3">
                        <label class="form-label">Product</label>
                        <input type="text" class="form-control" id="adjustProductName" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Current Stock</label>
                        <input type="text" class="form-control" id="currentStock" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Adjustment Type</label>
                        <select class="form-select" id="adjustmentType" required>
                            <option value="add">Add Stock</option>
                            <option value="remove">Remove Stock</option>
                            <option value="set">Set Stock</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="adjustQuantity" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason (Optional)</label>
                        <textarea class="form-control" id="adjustReason" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveStockAdjustment()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
let adjustModal;

document.addEventListener('DOMContentLoaded', function() {
    adjustModal = new bootstrap.Modal(document.getElementById('adjustStockModal'));
    
    document.getElementById('searchInventory').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#inventoryTable tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
});

function filterStock(status) {
    const rows = document.querySelectorAll('#inventoryTable tbody tr');
    
    rows.forEach(row => {
        if (status === 'all') {
            row.style.display = '';
        } else if (status === 'low') {
            row.style.display = row.dataset.lowStock === '1' ? '' : 'none';
        } else {
            row.style.display = row.dataset.status === status ? '' : 'none';
        }
    });
}

function adjustStock(productId, productName, currentStock) {
    document.getElementById('adjustProductId').value = productId;
    document.getElementById('adjustProductName').value = productName;
    document.getElementById('currentStock').value = currentStock;
    document.getElementById('adjustQuantity').value = '';
    document.getElementById('adjustReason').value = '';
    document.getElementById('adjustmentType').value = 'add';
    
    adjustModal.show();
}

function saveStockAdjustment() {
    const productId = document.getElementById('adjustProductId').value;
    const currentStock = parseInt(document.getElementById('currentStock').value);
    const type = document.getElementById('adjustmentType').value;
    const quantity = parseInt(document.getElementById('adjustQuantity').value);
    const reason = document.getElementById('adjustReason').value;
    
    if (!quantity || quantity < 0) {
        alert('Please enter a valid quantity');
        return;
    }
    
    let newStock;
    if (type === 'add') {
        newStock = currentStock + quantity;
    } else if (type === 'remove') {
        newStock = Math.max(0, currentStock - quantity);
    } else {
        newStock = quantity;
    }
    
    alert(`Stock adjustment saved! New stock: ${newStock}\nNote: This is a demo. In production, this would update the WooCommerce database.`);
    adjustModal.hide();
}

function viewProduct(productId) {
    alert(`View product details for Product ID: ${productId}\nNote: This would open the product details page.`);
}

function showAddProductModal() {
    alert('Add Product functionality:\n\nTo add new products, please use your WooCommerce admin panel:\n\n1. Go to WooCommerce → Products → Add New\n2. The products will automatically sync to B-Plus POS\n3. You can then manage inventory and sales through this system\n\nFor local product creation without WooCommerce, this feature can be added in a future update.');
}

function deleteProduct(productId, productName) {
    if (!confirm(`⚠️ Are you sure you want to delete "${productName}"?\n\nThis will:\n✗ Remove the product from inventory\n✗ Prevent future sales\n✗ Cannot be undone\n\nNote: This deletes from the WooCommerce database.`)) {
        return;
    }
    
    // Send delete request
    fetch(`/api/products/${productId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Product deleted successfully!');
            // Remove the row from table
            document.querySelector(`tr[data-product-id="${productId}"]`).remove();
        } else {
            alert('❌ Error deleting product: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('❌ Error deleting product: ' + error.message);
    });
}
</script>

<style>
.card {
    transition: all 0.3s ease;
}
.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>

<?php require_once __DIR__ . '/_footer.php'; ?>
