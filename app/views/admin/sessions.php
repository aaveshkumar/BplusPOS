<?php
$pageTitle = $title ?? 'Cashier Sessions';
require_once __DIR__ . '/_header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="mb-3">🕐 Cashier Sessions Management</h2>
            <p class="text-muted">Track cashier shifts, opening/closing balances, and session performance</p>
        </div>
        <div class="col-md-6 text-end">
            <button class="btn btn-success btn-lg" onclick="openNewSession()">
                <i class="fas fa-plus-circle"></i> Start New Session
            </button>
            <button class="btn btn-danger btn-lg" onclick="closeCurrentSession()">
                <i class="fas fa-stop-circle"></i> Close Current Session
            </button>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h5 class="text-muted mb-2">Active Sessions</h5>
                    <h2 class="mb-0 text-success" id="activeSessions">0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h5 class="text-muted mb-2">Today's Sales</h5>
                    <h2 class="mb-0 text-primary" id="todaySales">₹0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h5 class="text-muted mb-2">Total Orders</h5>
                    <h2 class="mb-0 text-info" id="totalOrders">0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h5 class="text-muted mb-2">Cash in Drawer</h5>
                    <h2 class="mb-0 text-warning" id="cashInDrawer">₹0</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">Recent Sessions</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Session ID</th>
                                    <th>Cashier</th>
                                    <th>Opened At</th>
                                    <th>Closed At</th>
                                    <th>Opening Balance</th>
                                    <th>Closing Balance</th>
                                    <th>Total Sales</th>
                                    <th>Orders</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($sessions)): ?>
                                    <tr>
                                        <td colspan="10" class="text-center py-4">
                                            <p class="text-muted mb-0">No sessions found. Start a new session to begin tracking!</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($sessions as $session): ?>
                                        <?php
                                        $statusClass = $session['status'] === 'open' ? 'success' : 'secondary';
                                        $statusIcon = $session['status'] === 'open' ? 'fa-circle-check' : 'fa-circle-xmark';
                                        ?>
                                        <tr>
                                            <td><strong>#<?php echo $session['id']; ?></strong></td>
                                            <td>
                                                <i class="fas fa-user text-primary"></i>
                                                <?php echo htmlspecialchars($session['cashier_name'] ?? 'Unknown'); ?>
                                            </td>
                                            <td><?php echo date('M d, Y h:i A', strtotime($session['session_start'])); ?></td>
                                            <td>
                                                <?php if ($session['session_end']): ?>
                                                    <?php echo date('M d, Y h:i A', strtotime($session['session_end'])); ?>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>₹<?php echo number_format($session['opening_balance'], 2); ?></td>
                                            <td>
                                                <?php if ($session['closing_balance']): ?>
                                                    ₹<?php echo number_format($session['closing_balance'], 2); ?>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td>₹<?php echo number_format($session['total_sales'], 2); ?></td>
                                            <td><?php echo $session['total_orders']; ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $statusClass; ?>">
                                                    <i class="fas <?php echo $statusIcon; ?>"></i>
                                                    <?php echo ucfirst($session['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-info" onclick="viewSessionDetails(<?php echo $session['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if ($session['status'] === 'open'): ?>
                                                    <button class="btn btn-sm btn-outline-danger" onclick="closeSession(<?php echo $session['id']; ?>)">
                                                        <i class="fas fa-stop"></i> Close
                                                    </button>
                                                <?php endif; ?>
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

<!-- New Session Modal -->
<div class="modal fade" id="newSessionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Start New Cashier Session</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="newSessionForm">
                    <div class="mb-3">
                        <label class="form-label">Opening Cash Balance (₹)</label>
                        <input type="number" class="form-control" id="openingBalance" min="0" step="0.01" required>
                        <small class="text-muted">Enter the initial cash amount in the drawer</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="sessionNotes" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="saveNewSession()">Start Session</button>
            </div>
        </div>
    </div>
</div>

<!-- Close Session Modal -->
<div class="modal fade" id="closeSessionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Close Cashier Session</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="closeSessionForm">
                    <input type="hidden" id="closeSessionId">
                    <div class="mb-3">
                        <label class="form-label">Closing Cash Balance (₹)</label>
                        <input type="number" class="form-control" id="closingBalance" min="0" step="0.01" required>
                        <small class="text-muted">Count the cash in drawer and enter the total</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Closing Notes (Optional)</label>
                        <textarea class="form-control" id="closingNotes" rows="2"></textarea>
                    </div>
                    <div class="alert alert-info">
                        <strong>Expected Cash:</strong> ₹<span id="expectedCash">0.00</span><br>
                        <strong>Actual Cash:</strong> ₹<span id="actualCash">0.00</span><br>
                        <strong>Difference:</strong> <span id="cashDifference" class="text-danger">₹0.00</span>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="saveCloseSession()">Close Session</button>
            </div>
        </div>
    </div>
</div>

<script>
let newSessionModal, closeSessionModal;

$(document).ready(function() {
    newSessionModal = new bootstrap.Modal(document.getElementById('newSessionModal'));
    closeSessionModal = new bootstrap.Modal(document.getElementById('closeSessionModal'));
    
    calculateStats();
    
    $('#closingBalance').on('input', calculateDifference);
});

function calculateStats() {
    let activeSessions = 0;
    let todaySales = 0;
    let totalOrders = 0;
    let cashInDrawer = 0;
    
    <?php foreach ($sessions as $session): ?>
        <?php if ($session['status'] === 'open'): ?>
            activeSessions++;
            cashInDrawer += <?php echo $session['opening_balance'] + $session['total_sales']; ?>;
        <?php endif; ?>
        
        <?php if (date('Y-m-d', strtotime($session['session_start'])) === date('Y-m-d')): ?>
            todaySales += <?php echo $session['total_sales']; ?>;
            totalOrders += <?php echo $session['total_orders']; ?>;
        <?php endif; ?>
    <?php endforeach; ?>
    
    $('#activeSessions').text(activeSessions);
    $('#todaySales').text('₹' + todaySales.toLocaleString('en-IN', {minimumFractionDigits: 2}));
    $('#totalOrders').text(totalOrders);
    $('#cashInDrawer').text('₹' + cashInDrawer.toLocaleString('en-IN', {minimumFractionDigits: 2}));
}

function openNewSession() {
    $('#newSessionForm')[0].reset();
    newSessionModal.show();
}

function saveNewSession() {
    const openingBalance = $('#openingBalance').val();
    const notes = $('#sessionNotes').val();
    
    if (!openingBalance) {
        alert('Please enter the opening cash balance');
        return;
    }
    
    alert(`New session started!\nOpening Balance: ₹${parseFloat(openingBalance).toFixed(2)}\n\nNote: This is a demo. In production, this would create a new pos_sessions record.`);
    newSessionModal.hide();
    location.reload();
}

function closeCurrentSession() {
    const activeSessions = parseInt($('#activeSessions').text());
    if (activeSessions === 0) {
        alert('No active sessions to close');
        return;
    }
    
    <?php foreach ($sessions as $session): ?>
        <?php if ($session['status'] === 'open'): ?>
            closeSession(<?php echo $session['id']; ?>);
            return;
        <?php endif; ?>
    <?php endforeach; ?>
}

function closeSession(sessionId) {
    $('#closeSessionId').val(sessionId);
    $('#closingBalance').val('');
    $('#closingNotes').val('');
    closeSessionModal.show();
}

function calculateDifference() {
    const closingBalance = parseFloat($('#closingBalance').val()) || 0;
    const openingBalance = 5000; // This should be fetched from session data
    const totalSales = 2500; // This should be fetched from session data
    
    const expectedCash = openingBalance + totalSales;
    const difference = closingBalance - expectedCash;
    
    $('#expectedCash').text(expectedCash.toFixed(2));
    $('#actualCash').text(closingBalance.toFixed(2));
    $('#cashDifference').text('₹' + Math.abs(difference).toFixed(2))
        .removeClass('text-danger text-success')
        .addClass(difference >= 0 ? 'text-success' : 'text-danger');
    
    if (difference !== 0) {
        $('#cashDifference').text((difference >= 0 ? '+' : '-') + '₹' + Math.abs(difference).toFixed(2));
    }
}

function saveCloseSession() {
    const sessionId = $('#closeSessionId').val();
    const closingBalance = $('#closingBalance').val();
    const notes = $('#closingNotes').val();
    
    if (!closingBalance) {
        alert('Please enter the closing cash balance');
        return;
    }
    
    alert(`Session #${sessionId} closed!\nClosing Balance: ₹${parseFloat(closingBalance).toFixed(2)}\n\nNote: This is a demo. In production, this would update the pos_sessions record.`);
    closeSessionModal.hide();
    location.reload();
}

function viewSessionDetails(sessionId) {
    alert(`Viewing details for Session #${sessionId}\n\nThis would show:\n- Transaction breakdown\n- Payment method totals\n- Discrepancies\n- Detailed audit trail`);
}
</script>

<style>
.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-5px);
}

.table th {
    font-weight: 600;
    color: #495057;
}

.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.modal-header .btn-close {
    filter: brightness(0) invert(1);
}
</style>

<?php require_once __DIR__ . '/_footer.php'; ?>
