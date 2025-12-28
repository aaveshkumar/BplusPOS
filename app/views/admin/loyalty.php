<?php
$pageTitle = $title ?? 'Loyalty Programs';
require_once __DIR__ . '/_header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-3">🎁 Loyalty Programs & Rewards</h2>
            <p class="text-muted">Manage customer loyalty points, tiers, and rewards</p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="text-muted mb-2">👥 Total Members</h5>
                    <h2 class="mb-0"><?php echo number_format($stats['total_members']); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-success">
                <div class="card-body">
                    <h5 class="text-muted mb-2">✨ Points Issued</h5>
                    <h2 class="mb-0 text-success"><?php echo number_format($stats['total_points_issued']); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-warning">
                <div class="card-body">
                    <h5 class="text-muted mb-2">🎯 Points Redeemed</h5>
                    <h2 class="mb-0 text-warning"><?php echo number_format($stats['total_points_redeemed']); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-primary">
                <div class="card-body">
                    <h5 class="text-muted mb-2">💎 Active Points</h5>
                    <h2 class="mb-0 text-primary"><?php echo number_format($stats['active_points']); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">🏆 Customer Tiers Distribution</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="tier-badge bronze">
                                <i class="fas fa-medal"></i>
                                <h3><?php echo number_format($stats['tier_breakdown']['bronze'] ?? 0); ?></h3>
                                <p class="mb-0">Bronze</p>
                                <small class="text-muted">0-999 points</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="tier-badge silver">
                                <i class="fas fa-medal"></i>
                                <h3><?php echo number_format($stats['tier_breakdown']['silver'] ?? 0); ?></h3>
                                <p class="mb-0">Silver</p>
                                <small class="text-muted">1,000-4,999 points</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="tier-badge gold">
                                <i class="fas fa-medal"></i>
                                <h3><?php echo number_format($stats['tier_breakdown']['gold'] ?? 0); ?></h3>
                                <p class="mb-0">Gold</p>
                                <small class="text-muted">5,000-9,999 points</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="tier-badge platinum">
                                <i class="fas fa-crown"></i>
                                <h3><?php echo number_format($stats['tier_breakdown']['platinum'] ?? 0); ?></h3>
                                <p class="mb-0">Platinum</p>
                                <small class="text-muted">10,000+ points</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0">🌟 Top Loyalty Customers</h5>
                        </div>
                        <div class="col-md-6 text-end">
                            <button class="btn btn-sm btn-primary" onclick="showAddPointsModal()">
                                <i class="fas fa-plus"></i> Add Points
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-hover mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Customer</th>
                                    <th>Email</th>
                                    <th>Points</th>
                                    <th>Tier</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($topCustomers)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <p class="text-muted mb-0">No loyalty members yet</p>
                                            <small>Points will be added automatically when customers make purchases</small>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($topCustomers as $customer): ?>
                                        <?php
                                        $tier = $customer['tier'] ?? 'bronze';
                                        $tierColors = [
                                            'bronze' => 'secondary',
                                            'silver' => 'light text-dark',
                                            'gold' => 'warning',
                                            'platinum' => 'primary'
                                        ];
                                        $tierColor = $tierColors[$tier] ?? 'secondary';
                                        ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($customer['customer_name'] ?? 'Unknown'); ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($customer['customer_email'] ?? ''); ?></td>
                                            <td>
                                                <span class="badge bg-success fs-6">
                                                    <?php echo number_format($customer['points']); ?> pts
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $tierColor; ?>">
                                                    <?php echo ucfirst($tier); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" onclick="viewCustomerLoyalty(<?php echo $customer['customer_id']; ?>, '<?php echo htmlspecialchars($customer['customer_name']); ?>')">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-success" onclick="addPoints(<?php echo $customer['customer_id']; ?>, '<?php echo htmlspecialchars($customer['customer_name']); ?>')">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-warning" onclick="redeemPoints(<?php echo $customer['customer_id']; ?>, '<?php echo htmlspecialchars($customer['customer_name']); ?>', <?php echo $customer['points']; ?>)">
                                                    <i class="fas fa-gift"></i>
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

        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0">📝 Recent Transactions</h5>
                        </div>
                        <div class="col-md-6 text-end">
                            <button class="btn btn-sm btn-outline-secondary" onclick="exportTransactions()">
                                <i class="fas fa-download"></i> Export
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-hover mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Type</th>
                                    <th>Points</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentTransactions)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <p class="text-muted mb-0">No transactions yet</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recentTransactions as $transaction): ?>
                                        <?php
                                        $type = $transaction['transaction_type'];
                                        $typeColors = [
                                            'earned' => 'success',
                                            'redeemed' => 'warning',
                                            'expired' => 'danger',
                                            'adjusted' => 'info'
                                        ];
                                        $typeColor = $typeColors[$type] ?? 'secondary';
                                        $typeIcons = [
                                            'earned' => 'fa-plus',
                                            'redeemed' => 'fa-gift',
                                            'expired' => 'fa-hourglass-end',
                                            'adjusted' => 'fa-edit'
                                        ];
                                        $typeIcon = $typeIcons[$type] ?? 'fa-circle';
                                        ?>
                                        <tr>
                                            <td>
                                                <small><?php echo date('M d, Y', strtotime($transaction['created_at'])); ?></small>
                                            </td>
                                            <td>
                                                <small><?php echo htmlspecialchars($transaction['customer_name'] ?? 'Unknown'); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $typeColor; ?>">
                                                    <i class="fas <?php echo $typeIcon; ?>"></i> <?php echo ucfirst($type); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong class="text-<?php echo $typeColor; ?>">
                                                    <?php echo ($type === 'redeemed' ? '-' : '+') . number_format($transaction['points']); ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <small><?php echo htmlspecialchars($transaction['description'] ?? '-'); ?></small>
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

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">⚙️ Loyalty Program Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Points per ₹ Spent</label>
                                <input type="number" class="form-control" id="pointsPerRupee" value="1" min="0" step="0.1">
                                <small class="text-muted">Customers earn this many points for every rupee spent</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Redemption Value</label>
                                <input type="number" class="form-control" id="redemptionValue" value="0.10" min="0" step="0.01">
                                <small class="text-muted">Each point is worth this many rupees when redeemed</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Minimum Redeem Points</label>
                                <input type="number" class="form-control" id="minRedeemPoints" value="100" min="1">
                                <small class="text-muted">Minimum points required to redeem rewards</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Points Expiry (Days)</label>
                                <input type="number" class="form-control" id="pointsExpiry" value="365" min="0">
                                <small class="text-muted">Points expire after this many days (0 = never expire)</small>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-primary" onclick="saveLoyaltySettings()">
                        <i class="fas fa-save"></i> Save Settings
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addPointsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Loyalty Points</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addPointsForm">
                    <input type="hidden" id="addPointsCustomerId">
                    <div class="mb-3">
                        <label class="form-label">Customer</label>
                        <input type="text" class="form-control" id="addPointsCustomerName" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Points to Add</label>
                        <input type="number" class="form-control" id="addPointsAmount" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <textarea class="form-control" id="addPointsReason" rows="2" placeholder="e.g., Birthday bonus, Promotion"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="saveAddPoints()">Add Points</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="redeemPointsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Redeem Loyalty Points</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="redeemPointsForm">
                    <input type="hidden" id="redeemPointsCustomerId">
                    <div class="mb-3">
                        <label class="form-label">Customer</label>
                        <input type="text" class="form-control" id="redeemPointsCustomerName" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Available Points</label>
                        <input type="text" class="form-control" id="redeemPointsAvailable" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Points to Redeem</label>
                        <input type="number" class="form-control" id="redeemPointsAmount" min="1" required>
                        <small class="text-muted">Discount value: ₹<span id="redeemValue">0.00</span></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason (Optional)</label>
                        <textarea class="form-control" id="redeemPointsReason" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="saveRedeemPoints()">Redeem Points</button>
            </div>
        </div>
    </div>
</div>

<script>
let addPointsModal, redeemPointsModal;

document.addEventListener('DOMContentLoaded', function() {
    addPointsModal = new bootstrap.Modal(document.getElementById('addPointsModal'));
    redeemPointsModal = new bootstrap.Modal(document.getElementById('redeemPointsModal'));
    
    document.getElementById('redeemPointsAmount').addEventListener('input', function() {
        const points = parseInt(this.value) || 0;
        const redemptionValue = parseFloat(document.getElementById('redemptionValue').value) || 0.10;
        const value = points * redemptionValue;
        document.getElementById('redeemValue').textContent = value.toFixed(2);
    });
});

function showAddPointsModal() {
    document.getElementById('addPointsCustomerId').value = '';
    document.getElementById('addPointsCustomerName').value = '';
    document.getElementById('addPointsAmount').value = '';
    document.getElementById('addPointsReason').value = '';
    addPointsModal.show();
}

function addPoints(customerId, customerName) {
    document.getElementById('addPointsCustomerId').value = customerId;
    document.getElementById('addPointsCustomerName').value = customerName;
    document.getElementById('addPointsAmount').value = '';
    document.getElementById('addPointsReason').value = '';
    addPointsModal.show();
}

function saveAddPoints() {
    const customerId = document.getElementById('addPointsCustomerId').value;
    const customerName = document.getElementById('addPointsCustomerName').value;
    const points = document.getElementById('addPointsAmount').value;
    const reason = document.getElementById('addPointsReason').value;
    
    if (!points || points <= 0) {
        alert('Please enter a valid number of points');
        return;
    }
    
    alert(`Successfully added ${points} points to ${customerName}!\n\nNote: This is a demo. In production, this would update the database.`);
    addPointsModal.hide();
    location.reload();
}

function redeemPoints(customerId, customerName, availablePoints) {
    document.getElementById('redeemPointsCustomerId').value = customerId;
    document.getElementById('redeemPointsCustomerName').value = customerName;
    document.getElementById('redeemPointsAvailable').value = availablePoints + ' points';
    document.getElementById('redeemPointsAmount').value = '';
    document.getElementById('redeemPointsReason').value = '';
    document.getElementById('redeemValue').textContent = '0.00';
    redeemPointsModal.show();
}

function saveRedeemPoints() {
    const customerId = document.getElementById('redeemPointsCustomerId').value;
    const customerName = document.getElementById('redeemPointsCustomerName').value;
    const points = parseInt(document.getElementById('redeemPointsAmount').value);
    const availablePoints = parseInt(document.getElementById('redeemPointsAvailable').value);
    const reason = document.getElementById('redeemPointsReason').value;
    
    if (!points || points <= 0) {
        alert('Please enter a valid number of points');
        return;
    }
    
    if (points > availablePoints) {
        alert('Cannot redeem more points than available!');
        return;
    }
    
    const redemptionValue = parseFloat(document.getElementById('redemptionValue').value) || 0.10;
    const discountValue = (points * redemptionValue).toFixed(2);
    
    alert(`Successfully redeemed ${points} points from ${customerName}!\nDiscount value: ₹${discountValue}\n\nNote: This is a demo. In production, this would update the database.`);
    redeemPointsModal.hide();
    location.reload();
}

function viewCustomerLoyalty(customerId, customerName) {
    alert(`View loyalty history for ${customerName}\nCustomer ID: ${customerId}\n\nNote: This would show detailed transaction history.`);
}

function exportTransactions() {
    alert('Exporting loyalty transactions to CSV...\nNote: This is a demo. In production, this would generate a CSV file.');
}

function saveLoyaltySettings() {
    const pointsPerRupee = document.getElementById('pointsPerRupee').value;
    const redemptionValue = document.getElementById('redemptionValue').value;
    const minRedeemPoints = document.getElementById('minRedeemPoints').value;
    const pointsExpiry = document.getElementById('pointsExpiry').value;
    
    alert(`Loyalty settings saved!\n\nPoints per ₹: ${pointsPerRupee}\nRedemption value: ₹${redemptionValue}\nMinimum redeem: ${minRedeemPoints} points\nExpiry: ${pointsExpiry} days\n\nNote: This is a demo. In production, this would update the pos_settings table.`);
}
</script>

<style>
.tier-badge {
    padding: 20px;
    border-radius: 10px;
    margin: 10px;
    transition: transform 0.3s;
}

.tier-badge:hover {
    transform: scale(1.05);
}

.tier-badge.bronze {
    background: linear-gradient(135deg, #cd7f32 0%, #b87333 100%);
    color: white;
}

.tier-badge.silver {
    background: linear-gradient(135deg, #c0c0c0 0%, #a8a8a8 100%);
    color: #333;
}

.tier-badge.gold {
    background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
    color: #333;
}

.tier-badge.platinum {
    background: linear-gradient(135deg, #e5e4e2 0%, #b9f2ff 100%);
    color: #333;
}

.tier-badge i {
    font-size: 2em;
    margin-bottom: 10px;
}

.tier-badge h3 {
    font-size: 2em;
    margin: 10px 0;
    font-weight: bold;
}

.sticky-top {
    position: sticky;
    top: 0;
    z-index: 10;
}
</style>

<?php require_once __DIR__ . '/_footer.php'; ?>
