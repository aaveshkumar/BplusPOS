<?php
$pageTitle = $title ?? 'Discount Management';
require_once __DIR__ . '/_header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="mb-3">🎯 Discount & Coupon Management</h2>
            <p class="text-muted">Create and manage discounts, coupons, and promotional offers</p>
        </div>
        <div class="col-md-6 text-end">
            <button class="btn btn-primary btn-lg" onclick="createDiscount()">
                <i class="fas fa-plus-circle"></i> Create Discount
            </button>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-primary">
                <div class="card-body">
                    <h5 class="text-muted mb-2">Active Coupons</h5>
                    <h2 class="mb-0 text-primary">8</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-success">
                <div class="card-body">
                    <h5 class="text-muted mb-2">Total Redemptions</h5>
                    <h2 class="mb-0 text-success">342</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-warning">
                <div class="card-body">
                    <h5 class="text-muted mb-2">Discount Given</h5>
                    <h2 class="mb-0 text-warning">₹45,680</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-info">
                <div class="card-body">
                    <h5 class="text-muted mb-2">Avg Discount %</h5>
                    <h2 class="mb-0 text-info">15%</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">Active Discounts & Coupons</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Code</th>
                                    <th>Description</th>
                                    <th>Type</th>
                                    <th>Value</th>
                                    <th>Usage</th>
                                    <th>Valid Until</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong class="text-primary">WELCOME10</strong></td>
                                    <td>Welcome discount for new customers</td>
                                    <td><span class="badge bg-info">Percentage</span></td>
                                    <td>10%</td>
                                    <td>45 / ∞</td>
                                    <td>Dec 31, 2025</td>
                                    <td><span class="badge bg-success">Active</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-warning" onclick="editDiscount(1)"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteDiscount(1)"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong class="text-primary">SAVE500</strong></td>
                                    <td>Flat ₹500 off on orders above ₹5000</td>
                                    <td><span class="badge bg-success">Fixed Amount</span></td>
                                    <td>₹500</td>
                                    <td>23 / 100</td>
                                    <td>Nov 30, 2025</td>
                                    <td><span class="badge bg-success">Active</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-warning" onclick="editDiscount(2)"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteDiscount(2)"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong class="text-primary">FLASH25</strong></td>
                                    <td>Flash sale - 25% off</td>
                                    <td><span class="badge bg-info">Percentage</span></td>
                                    <td>25%</td>
                                    <td>89 / 200</td>
                                    <td>Nov 15, 2025</td>
                                    <td><span class="badge bg-warning">Expiring Soon</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-warning" onclick="editDiscount(3)"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteDiscount(3)"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Discount Modal -->
<div class="modal fade" id="discountModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Discount</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="discountForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Coupon Code *</label>
                            <input type="text" class="form-control" id="couponCode" placeholder="e.g., SAVE20" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Discount Type *</label>
                            <select class="form-control" id="discountType">
                                <option value="percentage">Percentage</option>
                                <option value="fixed">Fixed Amount</option>
                                <option value="bogo">Buy One Get One</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Discount Value *</label>
                            <input type="number" class="form-control" id="discountValue" min="0" step="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Min. Order Amount</label>
                            <input type="number" class="form-control" id="minAmount" min="0" placeholder="0 = No minimum">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="description" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Valid From</label>
                            <input type="date" class="form-control" id="validFrom">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Valid Until</label>
                            <input type="date" class="form-control" id="validUntil">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Usage Limit</label>
                            <input type="number" class="form-control" id="usageLimit" min="0" placeholder="0 = Unlimited">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Per Customer Limit</label>
                            <input type="number" class="form-control" id="perCustomerLimit" min="0" value="1">
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="isActive" checked>
                            <label class="form-check-label" for="isActive">
                                Active (customers can use this coupon)
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveDiscount()">Save Discount</button>
            </div>
        </div>
    </div>
</div>

<script>
let discountModal;

$(document).ready(function() {
    discountModal = new bootstrap.Modal(document.getElementById('discountModal'));
});

function createDiscount() {
    $('#discountForm')[0].reset();
    discountModal.show();
}

function editDiscount(id) {
    alert(`Editing discount #${id}\n\nWould load discount data and populate the form.`);
    discountModal.show();
}

function saveDiscount() {
    const code = $('#couponCode').val();
    const type = $('#discountType option:selected').text();
    const value = $('#discountValue').val();
    
    if (!code || !value) {
        alert('Please fill in required fields');
        return;
    }
    
    alert(`Discount saved!\nCode: ${code}\nType: ${type}\nValue: ${value}\n\nWould be saved to database.`);
    discountModal.hide();
    location.reload();
}

function deleteDiscount(id) {
    if (confirm('Are you sure you want to delete this discount?')) {
        alert('Discount deleted!');
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
