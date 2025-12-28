<?php
$pageTitle = $title ?? 'Payment Methods';
require_once __DIR__ . '/_header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-3">💳 Payment Methods Configuration</h2>
            <p class="text-muted">Configure and manage payment options for POS</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-money-bill-wave fa-3x text-success mb-3"></i>
                    <h4>Cash</h4>
                    <p class="text-muted">Accept cash payments at POS</p>
                    <div class="form-check form-switch d-inline-block">
                        <input class="form-check-input" type="checkbox" id="enableCash" checked>
                        <label class="form-check-label" for="enableCash">Enabled</label>
                    </div>
                    <hr>
                    <button class="btn btn-sm btn-outline-primary" onclick="configureCash()">Configure</button>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-credit-card fa-3x text-primary mb-3"></i>
                    <h4>Card</h4>
                    <p class="text-muted">Accept credit/debit card payments</p>
                    <div class="form-check form-switch d-inline-block">
                        <input class="form-check-input" type="checkbox" id="enableCard" checked>
                        <label class="form-check-label" for="enableCard">Enabled</label>
                    </div>
                    <hr>
                    <button class="btn btn-sm btn-outline-primary" onclick="configureCard()">Configure</button>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-mobile-alt fa-3x text-info mb-3"></i>
                    <h4>UPI</h4>
                    <p class="text-muted">Accept UPI payments (GPay, PhonePe, etc.)</p>
                    <div class="form-check form-switch d-inline-block">
                        <input class="form-check-input" type="checkbox" id="enableUPI" checked>
                        <label class="form-check-label" for="enableUPI">Enabled</label>
                    </div>
                    <hr>
                    <button class="btn btn-sm btn-outline-primary" onclick="configureUPI()">Configure</button>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-wallet fa-3x text-warning mb-3"></i>
                    <h4>Digital Wallets</h4>
                    <p class="text-muted">Paytm, Amazon Pay, etc.</p>
                    <div class="form-check form-switch d-inline-block">
                        <input class="form-check-input" type="checkbox" id="enableWallet">
                        <label class="form-check-label" for="enableWallet">Disabled</label>
                    </div>
                    <hr>
                    <button class="btn btn-sm btn-outline-primary" onclick="configureWallet()">Configure</button>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-university fa-3x text-secondary mb-3"></i>
                    <h4>Bank Transfer</h4>
                    <p class="text-muted">NEFT, RTGS, IMPS</p>
                    <div class="form-check form-switch d-inline-block">
                        <input class="form-check-input" type="checkbox" id="enableBank">
                        <label class="form-check-label" for="enableBank">Disabled</label>
                    </div>
                    <hr>
                    <button class="btn btn-sm btn-outline-primary" onclick="configureBank()">Configure</button>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-handshake fa-3x text-danger mb-3"></i>
                    <h4>Credit/EMI</h4>
                    <p class="text-muted">Customer credit and EMI options</p>
                    <div class="form-check form-switch d-inline-block">
                        <input class="form-check-input" type="checkbox" id="enableCredit">
                        <label class="form-check-label" for="enableCredit">Disabled</label>
                    </div>
                    <hr>
                    <button class="btn btn-sm btn-outline-primary" onclick="configureCredit()">Configure</button>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">Payment Gateway Integration</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Gateway</th>
                                    <th>Methods Supported</th>
                                    <th>Transaction Fee</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Razorpay</strong></td>
                                    <td>Card, UPI, Wallet, Net Banking</td>
                                    <td>2% + ₹3</td>
                                    <td><span class="badge bg-success">Connected</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="configureGateway('razorpay')">Settings</button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="disconnectGateway('razorpay')">Disconnect</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Stripe</strong></td>
                                    <td>Card, Apple Pay, Google Pay</td>
                                    <td>2.9% + ₹2</td>
                                    <td><span class="badge bg-secondary">Not Connected</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-success" onclick="connectGateway('stripe')">Connect</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>PayU</strong></td>
                                    <td>Card, UPI, Wallet</td>
                                    <td>2.5% + ₹2</td>
                                    <td><span class="badge bg-secondary">Not Connected</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-success" onclick="connectGateway('payu')">Connect</button>
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

<script>
function configureCash() {
    alert('Cash Payment Configuration\n\n- Set denomination buttons\n- Cash drawer settings\n- Change calculation rules');
}

function configureCard() {
    alert('Card Payment Configuration\n\n- Terminal integration\n- Transaction limits\n- Receipt settings');
}

function configureUPI() {
    alert('UPI Configuration\n\n- QR code generation\n- UPI ID setup\n- Auto-reconciliation');
}

function configureWallet() {
    alert('Digital Wallet Configuration\n\n- Supported wallets\n- API credentials\n- Callback URLs');
}

function configureBank() {
    alert('Bank Transfer Configuration\n\n- Bank account details\n- Verification process\n- Reconciliation settings');
}

function configureCredit() {
    alert('Credit/EMI Configuration\n\n- Credit limits\n- EMI plans\n- Interest rates\n- Approval workflow');
}

function configureGateway(gateway) {
    alert(`Configure ${gateway}\n\nEnter API keys and configure settings.`);
}

function connectGateway(gateway) {
    alert(`Connecting to ${gateway}...\n\nYou'll need:\n- API Key\n- Secret Key\n- Webhook URL`);
}

function disconnectGateway(gateway) {
    if (confirm(`Disconnect ${gateway}?`)) {
        alert('Gateway disconnected');
        location.reload();
    }
}
</script>

<?php require_once __DIR__ . '/_footer.php'; ?>
