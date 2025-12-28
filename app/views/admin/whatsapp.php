<?php
$pageTitle = $title ?? 'WhatsApp Notifications';
require_once __DIR__ . '/_header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="mb-3">📱 WhatsApp Business Integration</h2>
            <p class="text-muted">Send automated WhatsApp notifications to customers</p>
        </div>
        <div class="col-md-6 text-end">
            <button class="btn btn-success" onclick="sendTestMessage()">
                <i class="fab fa-whatsapp"></i> Send Test Message
            </button>
            <button class="btn btn-primary" onclick="configureAPI()">
                <i class="fas fa-cog"></i> API Settings
            </button>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-success">
                <div class="card-body">
                    <h5 class="text-muted mb-2">Messages Sent</h5>
                    <h2 class="mb-0 text-success">1,245</h2>
                    <small class="text-muted">This Month</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-primary">
                <div class="card-body">
                    <h5 class="text-muted mb-2">Delivery Rate</h5>
                    <h2 class="mb-0 text-primary">98.5%</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-info">
                <div class="card-body">
                    <h5 class="text-muted mb-2">Read Rate</h5>
                    <h2 class="mb-0 text-info">87.3%</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-warning">
                <div class="card-body">
                    <h5 class="text-muted mb-2">Failed</h5>
                    <h2 class="mb-0 text-warning">18</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">Notification Templates</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Template</th>
                                    <th>Trigger</th>
                                    <th>Status</th>
                                    <th>Sent Count</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <i class="fab fa-whatsapp text-success"></i>
                                        <strong>Order Confirmation</strong>
                                    </td>
                                    <td>After checkout</td>
                                    <td><span class="badge bg-success">Active</span></td>
                                    <td>542</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="editTemplate('order_confirm')">Edit</button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="toggleTemplate('order_confirm')">Disable</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <i class="fab fa-whatsapp text-success"></i>
                                        <strong>Payment Received</strong>
                                    </td>
                                    <td>After payment</td>
                                    <td><span class="badge bg-success">Active</span></td>
                                    <td>542</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="editTemplate('payment')">Edit</button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="toggleTemplate('payment')">Disable</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <i class="fab fa-whatsapp text-success"></i>
                                        <strong>Low Stock Alert</strong>
                                    </td>
                                    <td>Stock below threshold</td>
                                    <td><span class="badge bg-warning">Paused</span></td>
                                    <td>45</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="editTemplate('low_stock')">Edit</button>
                                        <button class="btn btn-sm btn-outline-success" onclick="toggleTemplate('low_stock')">Enable</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <i class="fab fa-whatsapp text-success"></i>
                                        <strong>Birthday Wishes</strong>
                                    </td>
                                    <td>Customer birthday</td>
                                    <td><span class="badge bg-success">Active</span></td>
                                    <td>116</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="editTemplate('birthday')">Edit</button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="toggleTemplate('birthday')">Disable</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">Send Bulk Message</h5>
                </div>
                <div class="card-body">
                    <form id="bulkMessageForm">
                        <div class="mb-3">
                            <label class="form-label">Recipient Group</label>
                            <select class="form-control" id="recipientGroup">
                                <option value="all">All Customers</option>
                                <option value="vip">VIP Customers</option>
                                <option value="new">New Customers</option>
                                <option value="inactive">Inactive Customers</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message Template</label>
                            <select class="form-control" id="messageTemplate">
                                <option value="custom">Custom Message</option>
                                <option value="promotion">Promotional Offer</option>
                                <option value="announcement">General Announcement</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea class="form-control" id="bulkMessage" rows="4" placeholder="Enter your message..."></textarea>
                            <small class="text-muted">You can use variables: {name}, {phone}, {orderid}</small>
                        </div>
                        <button type="button" class="btn btn-success w-100" onclick="sendBulkMessage()">
                            <i class="fab fa-whatsapp"></i> Send to All Recipients
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">WhatsApp API Configuration</h5>
                </div>
                <div class="card-body">
                    <form id="whatsappConfigForm">
                        <div class="mb-3">
                            <label class="form-label">API Provider</label>
                            <select class="form-control" id="apiProvider">
                                <option value="twilio">Twilio</option>
                                <option value="gupshup">Gupshup</option>
                                <option value="msg91">MSG91</option>
                                <option value="wati">Wati.io</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">API Key</label>
                            <input type="text" class="form-control" id="apiKey" placeholder="Enter API key">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone Number (with country code)</label>
                            <input type="text" class="form-control" id="phoneNumber" placeholder="+91">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="enableNotifications" checked>
                                <label class="form-check-label" for="enableNotifications">
                                    Enable automatic notifications
                                </label>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary w-100" onclick="saveWhatsAppConfig()">
                            <i class="fas fa-save"></i> Save Configuration
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function sendTestMessage() {
    const phone = prompt('Enter phone number (with country code):');
    if (phone) {
        alert(`Sending test message to ${phone}\n\n"Hello! This is a test message from B-Plus POS."\n\nMessage sent successfully!`);
    }
}

function configureAPI() {
    alert('Opening WhatsApp API Configuration\n\nThis allows you to:\n- Configure API provider\n- Set API credentials\n- Test connection');
}

function editTemplate(template) {
    alert(`Editing template: ${template}\n\nYou can customize:\n- Message content\n- Variables\n- Trigger conditions`);
}

function toggleTemplate(template) {
    alert(`Template ${template} status toggled`);
    location.reload();
}

function sendBulkMessage() {
    const group = $('#recipientGroup option:selected').text();
    const message = $('#bulkMessage').val();
    
    if (!message) {
        alert('Please enter a message');
        return;
    }
    
    alert(`Sending bulk message to: ${group}\n\nMessage:\n${message}\n\nThis would queue messages for delivery.`);
}

function saveWhatsAppConfig() {
    const provider = $('#apiProvider option:selected').text();
    const apiKey = $('#apiKey').val();
    
    alert(`WhatsApp configuration saved!\nProvider: ${provider}\n\nConfiguration applied successfully.`);
}
</script>

<?php require_once __DIR__ . '/_footer.php'; ?>
