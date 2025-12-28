<?php
$pageTitle = $title ?? 'Store Management';
require_once __DIR__ . '/_header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="mb-3">🏪 Multi-Store Management</h2>
            <p class="text-muted">Manage multiple store locations, inventory, and staff assignments</p>
        </div>
        <div class="col-md-6 text-end">
            <button class="btn btn-primary btn-lg" onclick="showAddStoreModal()">
                <i class="fas fa-plus-circle"></i> Add New Store
            </button>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-primary">
                <div class="card-body">
                    <h5 class="text-muted mb-2">📍 Total Stores</h5>
                    <h2 class="mb-0 text-primary" id="totalStores"><?php echo count($stores ?? []); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-success">
                <div class="card-body">
                    <h5 class="text-muted mb-2">✅ Active Stores</h5>
                    <h2 class="mb-0 text-success" id="activeStores">
                        <?php echo count(array_filter($stores ?? [], fn($s) => $s['status'] === 'active')); ?>
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-info">
                <div class="card-body">
                    <h5 class="text-muted mb-2">💰 Combined Sales</h5>
                    <h2 class="mb-0 text-info">₹0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-warning">
                <div class="card-body">
                    <h5 class="text-muted mb-2">👥 Total Staff</h5>
                    <h2 class="mb-0 text-warning">0</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">Store Locations</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Store Name</th>
                                    <th>Location</th>
                                    <th>Manager</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Inventory Value</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($stores)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-store fa-3x text-muted mb-3"></i>
                                            <p class="text-muted mb-2">No stores configured yet</p>
                                            <button class="btn btn-primary" onclick="showAddStoreModal()">
                                                <i class="fas fa-plus"></i> Add Your First Store
                                            </button>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($stores as $store): ?>
                                        <tr>
                                            <td><strong>#<?php echo $store['id']; ?></strong></td>
                                            <td>
                                                <i class="fas fa-store text-primary"></i>
                                                <strong><?php echo htmlspecialchars($store['store_name']); ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($store['address'] . ', ' . $store['city']); ?></td>
                                            <td><?php echo htmlspecialchars($store['manager_name'] ?? 'Not assigned'); ?></td>
                                            <td><?php echo htmlspecialchars($store['phone'] ?? '-'); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $store['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                    <?php echo ucfirst($store['status']); ?>
                                                </span>
                                            </td>
                                            <td>₹<?php echo number_format($store['inventory_value'] ?? 0, 2); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-info" onclick="viewStore(<?php echo $store['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-warning" onclick="editStore(<?php echo $store['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteStore(<?php echo $store['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
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
    </div>
</div>

<!-- Add/Edit Store Modal -->
<div class="modal fade" id="storeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="storeModalTitle">Add New Store</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="storeForm">
                    <input type="hidden" id="storeId">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Store Name *</label>
                            <input type="text" class="form-control" id="storeName" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Store Code</label>
                            <input type="text" class="form-control" id="storeCode">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" id="storeAddress">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" class="form-control" id="storeCity">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">State</label>
                            <input type="text" class="form-control" id="storeState">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Pincode</label>
                            <input type="text" class="form-control" id="storePincode">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="storePhone">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="storeEmail">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Manager</label>
                            <select class="form-control" id="storeManager">
                                <option value="">Select Manager</option>
                                <option value="1">John Doe</option>
                                <option value="2">Jane Smith</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" id="storeStatus">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveStore()">Save Store</button>
            </div>
        </div>
    </div>
</div>

<script>
let storeModal;

$(document).ready(function() {
    storeModal = new bootstrap.Modal(document.getElementById('storeModal'));
});

function showAddStoreModal() {
    $('#storeModalTitle').text('Add New Store');
    $('#storeForm')[0].reset();
    $('#storeId').val('');
    storeModal.show();
}

function editStore(storeId) {
    $('#storeModalTitle').text('Edit Store');
    $('#storeId').val(storeId);
    alert('Loading store data...\nIn production, this would fetch store details from the database.');
    storeModal.show();
}

function saveStore() {
    const storeName = $('#storeName').val();
    if (!storeName) {
        alert('Please enter store name');
        return;
    }
    
    alert(`Store saved successfully!\nName: ${storeName}\n\nNote: In production, this would save to pos_stores table.`);
    storeModal.hide();
    location.reload();
}

function viewStore(storeId) {
    alert(`Viewing Store #${storeId}\n\nThis would show:\n- Store performance metrics\n- Inventory breakdown\n- Staff assignments\n- Sales analytics`);
}

function deleteStore(storeId) {
    if (confirm('Are you sure you want to delete this store?')) {
        alert('Store deleted!\nNote: In production, this would remove the store from pos_stores table.');
        location.reload();
    }
}
</script>

<style>
.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}
.modal-header .btn-close {
    filter: brightness(0) invert(1);
}
</style>

<?php require_once __DIR__ . '/_footer.php'; ?>
