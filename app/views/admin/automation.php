<?php
$pageTitle = $title ?? 'Workflow Automation';
require_once __DIR__ . '/_header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="mb-3">🤖 Workflow Automation</h2>
            <p class="text-muted">Automate repetitive tasks and create smart workflows</p>
        </div>
        <div class="col-md-6 text-end">
            <button class="btn btn-primary btn-lg" onclick="createWorkflow()">
                <i class="fas fa-plus-circle"></i> Create Workflow
            </button>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-primary">
                <div class="card-body">
                    <h5 class="text-muted mb-2">Active Workflows</h5>
                    <h2 class="mb-0 text-primary">5</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-success">
                <div class="card-body">
                    <h5 class="text-muted mb-2">Executions Today</h5>
                    <h2 class="mb-0 text-success">124</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-info">
                <div class="card-body">
                    <h5 class="text-muted mb-2">Success Rate</h5>
                    <h2 class="mb-0 text-info">98%</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-warning">
                <div class="card-body">
                    <h5 class="text-muted mb-2">Time Saved</h5>
                    <h2 class="mb-0 text-warning">15h</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">Pre-built Automation Templates</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3 h-100">
                                <h6><i class="fas fa-envelope text-primary"></i> Low Stock Alert Email</h6>
                                <p class="text-muted small">Automatically email when stock falls below threshold</p>
                                <button class="btn btn-sm btn-primary" onclick="useTemplate('low_stock_email')">Use Template</button>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3 h-100">
                                <h6><i class="fab fa-whatsapp text-success"></i> Order Confirmation</h6>
                                <p class="text-muted small">Send WhatsApp confirmation after every order</p>
                                <button class="btn btn-sm btn-primary" onclick="useTemplate('order_whatsapp')">Use Template</button>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3 h-100">
                                <h6><i class="fas fa-birthday-cake text-warning"></i> Birthday Wishes</h6>
                                <p class="text-muted small">Automatic birthday greetings with special discount</p>
                                <button class="btn btn-sm btn-primary" onclick="useTemplate('birthday')">Use Template</button>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3 h-100">
                                <h6><i class="fas fa-sync text-info"></i> Daily Sales Report</h6>
                                <p class="text-muted small">Generate and email daily sales summary</p>
                                <button class="btn btn-sm btn-primary" onclick="useTemplate('daily_report')">Use Template</button>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3 h-100">
                                <h6><i class="fas fa-user-clock text-secondary"></i> Inactive Customer Re-engagement</h6>
                                <p class="text-muted small">Reach out to customers who haven't purchased in 30 days</p>
                                <button class="btn btn-sm btn-primary" onclick="useTemplate('reengagement')">Use Template</button>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3 h-100">
                                <h6><i class="fas fa-star text-warning"></i> Review Request</h6>
                                <p class="text-muted small">Request reviews from satisfied customers</p>
                                <button class="btn btn-sm btn-primary" onclick="useTemplate('review_request')">Use Template</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">Active Workflows</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Trigger</th>
                                    <th>Action</th>
                                    <th>Last Run</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Low Stock Email Alert</strong></td>
                                    <td><span class="badge bg-info">Every 6 hours</span></td>
                                    <td>Send email to manager</td>
                                    <td>2 hours ago</td>
                                    <td><span class="badge bg-success">Active</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-warning" onclick="editWorkflow(1)"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="toggleWorkflow(1)"><i class="fas fa-pause"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Order Confirmation WhatsApp</strong></td>
                                    <td><span class="badge bg-success">After order placed</span></td>
                                    <td>Send WhatsApp message</td>
                                    <td>15 minutes ago</td>
                                    <td><span class="badge bg-success">Active</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-warning" onclick="editWorkflow(2)"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="toggleWorkflow(2)"><i class="fas fa-pause"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Daily Sales Report</strong></td>
                                    <td><span class="badge bg-primary">Daily at 11 PM</span></td>
                                    <td>Email PDF report</td>
                                    <td>23 hours ago</td>
                                    <td><span class="badge bg-success">Active</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-warning" onclick="editWorkflow(3)"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="toggleWorkflow(3)"><i class="fas fa-pause"></i></button>
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

<div class="modal fade" id="workflowModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Workflow</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="workflowForm">
                    <div class="mb-3">
                        <label class="form-label">Workflow Name</label>
                        <input type="text" class="form-control" id="workflowName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Trigger</label>
                        <select class="form-control" id="trigger">
                            <option value="order_placed">When order is placed</option>
                            <option value="low_stock">When stock is low</option>
                            <option value="schedule">On schedule</option>
                            <option value="customer_birthday">Customer birthday</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Action</label>
                        <select class="form-control" id="action">
                            <option value="email">Send Email</option>
                            <option value="whatsapp">Send WhatsApp</option>
                            <option value="sms">Send SMS</option>
                            <option value="webhook">Call Webhook</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Conditions (Optional)</label>
                        <textarea class="form-control" id="conditions" rows="3" placeholder="e.g., Order total > 1000"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveWorkflow()">Create Workflow</button>
            </div>
        </div>
    </div>
</div>

<script>
let workflowModal;

$(document).ready(function() {
    workflowModal = new bootstrap.Modal(document.getElementById('workflowModal'));
});

function createWorkflow() {
    $('#workflowForm')[0].reset();
    workflowModal.show();
}

function useTemplate(template) {
    alert(`Using template: ${template}\n\nThis would pre-fill the workflow form with template settings.`);
    workflowModal.show();
}

function saveWorkflow() {
    const name = $('#workflowName').val();
    if (!name) {
        alert('Please enter a workflow name');
        return;
    }
    
    alert(`Workflow created: ${name}\n\nWould be saved to database and activated.`);
    workflowModal.hide();
    location.reload();
}

function editWorkflow(id) {
    alert(`Editing workflow #${id}`);
    workflowModal.show();
}

function toggleWorkflow(id) {
    alert(`Workflow #${id} status toggled`);
    location.reload();
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
