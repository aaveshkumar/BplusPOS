<?php include __DIR__ . '/_header.php'; ?>

<div class="admin-content">
    <div class="admin-header">
        <h1><i class="fas fa-undo"></i> Returns & Exchange Management</h1>
        <p>Process and manage product returns and refunds</p>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="content-card">
                <h6 class="text-muted mb-2">Total Returns</h6>
                <h3 class="mb-0"><?php echo number_format($stats['total_returns']); ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="content-card">
                <h6 class="text-muted mb-2">Pending</h6>
                <h3 class="mb-0 text-warning"><?php echo number_format($stats['pending_returns']); ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="content-card">
                <h6 class="text-muted mb-2">Completed</h6>
                <h3 class="mb-0 text-success"><?php echo number_format($stats['completed_returns'] + $stats['approved_returns']); ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="content-card">
                <h6 class="text-muted mb-2">30-Day Refunds</h6>
                <h3 class="mb-0">₹<?php echo number_format($stats['refund_amount_30d'], 2); ?></h3>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="content-card mb-4">
        <div class="row">
            <div class="col-md-5">
                <input type="text" class="form-control" id="searchReturn" placeholder="🔍 Search by return number, order number, customer...">
            </div>
            <div class="col-md-3">
                <select class="form-select" id="filterStatus">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="completed">Completed</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100" onclick="filterReturns()">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
            <div class="col-md-2">
                <div class="btn-group w-100">
                    <button class="btn btn-danger" onclick="processNewReturn()">
                        <i class="fas fa-undo"></i> Return
                    </button>
                    <button class="btn btn-success" onclick="processNewExchange()">
                        <i class="fas fa-exchange-alt"></i> Exchange
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Returns Table -->
    <div class="content-card">
        <div class="content-card-header">
            <h3 class="content-card-title"><i class="fas fa-list"></i> All Returns</h3>
            <div>
                <button class="btn btn-outline-primary btn-sm" onclick="exportReturns()">
                    <i class="fas fa-file-excel"></i> Export
                </button>
            </div>
        </div>

        <?php if (!empty($returns)): ?>
        <div class="table-responsive">
            <table class="admin-table" id="returnsTable">
                <thead>
                    <tr>
                        <th>Return #</th>
                        <th>Original Order</th>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Reason</th>
                        <th>Amount</th>
                        <th>Refund Method</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($returns as $return): ?>
                    <tr>
                        <td><strong>#<?php echo htmlspecialchars($return['return_number'] ?? 'N/A'); ?></strong></td>
                        <td><?php echo htmlspecialchars($return['original_order_number'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($return['customer_name'] ?? 'Walk-in'); ?></td>
                        <td><?php echo ucwords(str_replace('_', ' ', $return['return_type'] ?? 'N/A')); ?></td>
                        <td><?php echo htmlspecialchars($return['return_reason'] ?? 'N/A'); ?></td>
                        <td><strong>₹<?php echo number_format($return['refund_amount'] ?? 0, 2); ?></strong></td>
                        <td><?php echo strtoupper($return['refund_method'] ?? 'N/A'); ?></td>
                        <td>
                            <?php 
                            $status = $return['status'] ?? 'pending';
                            $statusClass = $status === 'completed' ? 'success' : 
                                         ($status === 'approved' ? 'info' :
                                         ($status === 'pending' ? 'warning' : 'danger'));
                            ?>
                            <span class="badge badge-<?php echo $statusClass; ?>">
                                <?php echo ucfirst($status); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y H:i', strtotime($return['created_at'])); ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="viewReturn(<?php echo $return['id']; ?>)" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <?php if ($status === 'pending'): ?>
                            <button class="btn btn-sm btn-outline-success" onclick="approveReturn(<?php echo $return['id']; ?>)" title="Approve">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="rejectReturn(<?php echo $return['id']; ?>)" title="Reject">
                                <i class="fas fa-times"></i>
                            </button>
                            <?php endif; ?>
                            <?php if ($status === 'approved'): ?>
                            <button class="btn btn-sm btn-outline-info" onclick="processRefund(<?php echo $return['id']; ?>)" title="Process Refund">
                                <i class="fas fa-money-bill"></i>
                            </button>
                            <?php endif; ?>
                            <?php if ($status === 'completed'): ?>
                            <button class="btn btn-sm btn-outline-primary" onclick="viewReturnReceipt(<?php echo $return['id']; ?>)" title="View Receipt">
                                <i class="fas fa-receipt"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center text-muted py-5">
            <i class="fas fa-undo fa-3x mb-3" style="opacity: 0.3;"></i>
            <p>No returns found</p>
            <button class="btn btn-primary mt-2" onclick="processNewReturn()">
                <i class="fas fa-plus"></i> Process First Return
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- New Return Modal -->
<div class="modal fade" id="newReturnModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-undo"></i> Process Product Return (Refund)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="newReturnForm">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Step 1: Find Original Order</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="returnOrderNumber" placeholder="Enter order number (e.g., ORD-12345)">
                                <button type="button" class="btn btn-primary" onclick="loadOrderForReturn()">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Order Details</label>
                            <div id="returnOrderDetails" class="border rounded p-2 bg-light">
                                <small class="text-muted">Order information will appear here after search</small>
                            </div>
                        </div>
                    </div>
                    
                    <div id="returnItemsSection" style="display: none;">
                        <label class="form-label fw-bold">Step 2: Select Items to Return</label>
                        <div id="returnItemsList" class="mb-3"></div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Return Reason *</label>
                                <select class="form-select" id="returnReason" required>
                                    <option value="">Select reason...</option>
                                    <option value="defective">Defective/Faulty Product</option>
                                    <option value="wrong_item">Wrong Item Delivered</option>
                                    <option value="not_as_described">Product Not as Described</option>
                                    <option value="customer_changed_mind">Customer Changed Mind</option>
                                    <option value="damaged">Damaged in Transit</option>
                                    <option value="quality_issue">Quality Issue</option>
                                    <option value="other">Other Reason</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Refund Method *</label>
                                <select class="form-select" id="refundMethod" required>
                                    <option value="">Select method...</option>
                                    <option value="cash">Cash Refund</option>
                                    <option value="original_payment">Original Payment Method</option>
                                    <option value="upi">UPI Transfer</option>
                                    <option value="store_credit">Store Credit</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Additional Notes</label>
                            <textarea class="form-control" id="returnNotes" rows="2" placeholder="Any special instructions or notes..."></textarea>
                        </div>
                        
                        <div class="alert alert-info">
                            <strong>Total Refund Amount: ₹<span id="totalRefundAmount">0.00</span></strong>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="submitReturnBtn" onclick="submitNewReturn()" disabled>
                    <i class="fas fa-check"></i> Process Return & Refund
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Return/Exchange Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-receipt"></i> <span id="receiptTitle">Return Receipt</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="receiptContent" style="font-family: 'Courier New', monospace; font-size: 12px; line-height: 1.6;">
                <!-- Receipt will be inserted here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" onclick="downloadReceiptPDF()">
                    <i class="fas fa-file-pdf"></i> Download PDF
                </button>
                <button type="button" class="btn btn-outline-primary" onclick="printReceipt()">
                    <i class="fas fa-print"></i> Print
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- New Exchange Modal -->
<div class="modal fade" id="newExchangeModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-exchange-alt"></i> Process Product Exchange</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="newExchangeForm">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Step 1: Find Original Order</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="exchangeOrderNumber" placeholder="Enter order number (e.g., ORD-12345)">
                                <button type="button" class="btn btn-primary" onclick="loadOrderForExchange()">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Order Details</label>
                            <div id="exchangeOrderDetails" class="border rounded p-2 bg-light">
                                <small class="text-muted">Order information will appear here after search</small>
                            </div>
                        </div>
                    </div>
                    
                    <div id="exchangeItemsSection" style="display: none;">
                        <label class="form-label fw-bold">Step 2: Select Item to Exchange</label>
                        <div id="exchangeItemsList" class="mb-3"></div>
                        
                        <div id="exchangeProductSection" style="display: none;">
                            <label class="form-label fw-bold">Step 3: Select Replacement Product</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" id="searchExchangeProduct" placeholder="Search for replacement product...">
                                <button type="button" class="btn btn-primary" onclick="searchReplacementProducts()">
                                    <i class="fas fa-search"></i> Search Products
                                </button>
                            </div>
                            <div id="replacementProductsList"></div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Exchange Reason *</label>
                                <select class="form-select" id="exchangeReason" required>
                                    <option value="">Select reason...</option>
                                    <option value="size_issue">Wrong Size</option>
                                    <option value="color_issue">Wrong Color/Variant</option>
                                    <option value="defective">Defective Product</option>
                                    <option value="preference">Customer Preference</option>
                                    <option value="other">Other Reason</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Price Difference</label>
                                <input type="text" class="form-control" id="priceDifference" readonly placeholder="Will be calculated automatically">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Exchange Notes</label>
                            <textarea class="form-control" id="exchangeNotes" rows="2" placeholder="Any special instructions..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="submitExchangeBtn" onclick="submitNewExchange()" disabled>
                    <i class="fas fa-check"></i> Process Exchange
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let returnOrderData = null;
let exchangeOrderData = null;
let selectedReturnItems = [];
let selectedExchangeItem = null;
let selectedReplacementProduct = null;

// Filter returns
function filterReturns() {
    const search = document.getElementById('searchReturn').value.toLowerCase();
    const status = document.getElementById('filterStatus').value.toLowerCase();
    const table = document.getElementById('returnsTable');
    if (!table) return;
    
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let row of rows) {
        const text = row.textContent.toLowerCase();
        const statusBadge = row.querySelector('.badge');
        const rowStatus = statusBadge ? statusBadge.textContent.toLowerCase() : '';
        
        const matchesSearch = search === '' || text.includes(search);
        const matchesStatus = status === '' || rowStatus.includes(status);
        
        row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
    }
}

// RETURN WORKFLOW FUNCTIONS
function processNewReturn() {
    document.getElementById('returnOrderNumber').value = '';
    document.getElementById('returnOrderDetails').innerHTML = '<small class="text-muted">Order information will appear here after search</small>';
    document.getElementById('returnItemsSection').style.display = 'none';
    document.getElementById('submitReturnBtn').disabled = true;
    returnOrderData = null;
    selectedReturnItems = [];
    
    const modal = new bootstrap.Modal(document.getElementById('newReturnModal'));
    modal.show();
}

function loadOrderForReturn() {
    const orderNumber = document.getElementById('returnOrderNumber').value.trim();
    if (!orderNumber) {
        alert('Please enter an order number');
        return;
    }
    
    document.getElementById('returnOrderDetails').innerHTML = '<div class="spinner-border spinner-border-sm"></div> Loading...';
    
    fetch(`/api/orders/search?q=${encodeURIComponent(orderNumber)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.orders && data.orders.length > 0) {
                returnOrderData = data.orders[0];
                displayReturnOrderDetails();
                loadReturnItems();
            } else {
                document.getElementById('returnOrderDetails').innerHTML = '<span class="text-danger">Order not found</span>';
                document.getElementById('returnItemsSection').style.display = 'none';
            }
        })
        .catch(error => {
            document.getElementById('returnOrderDetails').innerHTML = '<span class="text-danger">Error: ' + error.message + '</span>';
            console.error('Error:', error);
        });
}

function displayReturnOrderDetails() {
    const order = returnOrderData;
    document.getElementById('returnOrderDetails').innerHTML = `
        <strong>Order: ${order.order_number}</strong><br>
        <small>Customer: ${order.customer_name || 'Walk-in'}</small><br>
        <small>Total: ₹${parseFloat(order.total).toFixed(2)}</small><br>
        <small>Date: ${new Date(order.created_at).toLocaleDateString()}</small>
    `;
}

function loadReturnItems() {
    // For now, display a simplified item list
    // In production, this would load from order items API
    const itemsHTML = `
        <div class="border rounded p-3 mb-2">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="returnItem1" onchange="updateReturnTotal()">
                <label class="form-check-label" for="returnItem1">
                    <strong>Order Items</strong><br>
                    <small>Subtotal: ₹${parseFloat(returnOrderData.subtotal).toFixed(2)}</small><br>
                    <small>Tax: ₹${parseFloat(returnOrderData.tax_amount).toFixed(2)}</small><br>
                    <small>Discount: -₹${parseFloat(returnOrderData.discount_amount).toFixed(2)}</small><br>
                    <strong>Total: ₹${parseFloat(returnOrderData.total).toFixed(2)}</strong>
                </label>
            </div>
        </div>
    `;
    
    document.getElementById('returnItemsList').innerHTML = itemsHTML;
    document.getElementById('returnItemsSection').style.display = 'block';
}

function updateReturnTotal() {
    const checked = document.getElementById('returnItem1').checked;
    const amount = checked ? parseFloat(returnOrderData.total) : 0;
    document.getElementById('totalRefundAmount').textContent = amount.toFixed(2);
    document.getElementById('submitReturnBtn').disabled = !checked;
}

function submitNewReturn() {
    const reason = document.getElementById('returnReason').value;
    const method = document.getElementById('refundMethod').value;
    const notes = document.getElementById('returnNotes').value;
    const amount = parseFloat(document.getElementById('totalRefundAmount').textContent);
    
    if (!reason || !method || amount <= 0) {
        alert('Please fill all required fields and select items to return');
        return;
    }
    
    if (!confirm(`Process return of ₹${amount.toFixed(2)}?`)) {
        return;
    }
    
    fetch('/admin/process-return', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            order_id: returnOrderData.id,
            order_number: returnOrderData.order_number,
            return_type: 'return',
            return_reason: reason,
            refund_amount: amount,
            refund_method: method,
            notes: notes
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('newReturnModal')).hide();
            setTimeout(() => {
                loadAndShowReturnReceipt(data.return_id);
            }, 300);
        } else {
            alert('Error: ' + (data.message || 'Failed to process return'));
        }
    })
    .catch(e => alert('Error: ' + e.message));
}

// EXCHANGE WORKFLOW FUNCTIONS
function processNewExchange() {
    document.getElementById('exchangeOrderNumber').value = '';
    document.getElementById('exchangeOrderDetails').innerHTML = '<small class="text-muted">Order information will appear here after search</small>';
    document.getElementById('exchangeItemsSection').style.display = 'none';
    document.getElementById('exchangeProductSection').style.display = 'none';
    document.getElementById('submitExchangeBtn').disabled = true;
    exchangeOrderData = null;
    selectedExchangeItem = null;
    selectedReplacementProduct = null;
    
    const modal = new bootstrap.Modal(document.getElementById('newExchangeModal'));
    modal.show();
}

function loadOrderForExchange() {
    const orderNumber = document.getElementById('exchangeOrderNumber').value.trim();
    if (!orderNumber) {
        alert('Please enter an order number');
        return;
    }
    
    document.getElementById('exchangeOrderDetails').innerHTML = '<div class="spinner-border spinner-border-sm"></div> Loading...';
    
    fetch(`/api/orders/search?q=${encodeURIComponent(orderNumber)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.orders && data.orders.length > 0) {
                exchangeOrderData = data.orders[0];
                displayExchangeOrderDetails();
                loadExchangeItems();
            } else {
                document.getElementById('exchangeOrderDetails').innerHTML = '<span class="text-danger">Order not found</span>';
                document.getElementById('exchangeItemsSection').style.display = 'none';
            }
        })
        .catch(error => {
            document.getElementById('exchangeOrderDetails').innerHTML = '<span class="text-danger">Error: ' + error.message + '</span>';
            console.error('Error:', error);
        });
}

function displayExchangeOrderDetails() {
    const order = exchangeOrderData;
    document.getElementById('exchangeOrderDetails').innerHTML = `
        <strong>Order: ${order.order_number}</strong><br>
        <small>Customer: ${order.customer_name || 'Walk-in'}</small><br>
        <small>Total: ₹${parseFloat(order.total).toFixed(2)}</small><br>
        <small>Date: ${new Date(order.created_at).toLocaleDateString()}</small>
    `;
}

function loadExchangeItems() {
    const itemsHTML = `
        <div class="border rounded p-3 mb-2">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="exchangeItem" id="exchangeItem1" onchange="selectExchangeItem()">
                <label class="form-check-label" for="exchangeItem1">
                    <strong>Product from Order #${exchangeOrderData.order_number}</strong><br>
                    <small>Price: ₹${parseFloat(exchangeOrderData.subtotal).toFixed(2)}</small>
                </label>
            </div>
        </div>
    `;
    
    document.getElementById('exchangeItemsList').innerHTML = itemsHTML;
    document.getElementById('exchangeItemsSection').style.display = 'block';
}

function selectExchangeItem() {
    const checked = document.getElementById('exchangeItem1').checked;
    if (checked) {
        selectedExchangeItem = {
            price: parseFloat(exchangeOrderData.subtotal),
            name: 'Order Item'
        };
        document.getElementById('exchangeProductSection').style.display = 'block';
    }
}

function searchReplacementProducts() {
    const searchTerm = document.getElementById('searchExchangeProduct').value.trim();
    if (!searchTerm) {
        alert('Please enter a search term');
        return;
    }
    
    document.getElementById('replacementProductsList').innerHTML = '<div class="spinner-border"></div> Searching...';
    
    fetch(`/api/products?search=${encodeURIComponent(searchTerm)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.products && data.products.length > 0) {
                displayReplacementProducts(data.products.slice(0, 5));
            } else {
                document.getElementById('replacementProductsList').innerHTML = '<div class="alert alert-warning">No products found</div>';
            }
        })
        .catch(error => {
            document.getElementById('replacementProductsList').innerHTML = '<div class="alert alert-danger">Error searching products: ' + error.message + '</div>';
            console.error(error);
        });
}

function displayReplacementProducts(products) {
    let html = '<div class="list-group">';
    products.forEach(product => {
        const price = parseFloat(product.sale_price || product.regular_price);
        html += `
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${product.name}</strong><br>
                        <small>₹${price.toFixed(2)}</small>
                    </div>
                    <button class="btn btn-sm btn-primary" onclick='selectReplacementProduct(${JSON.stringify({ id: product.product_id, name: product.name, price: price })})'>
                        Select
                    </button>
                </div>
            </div>
        `;
    });
    html += '</div>';
    document.getElementById('replacementProductsList').innerHTML = html;
}

function selectReplacementProduct(product) {
    selectedReplacementProduct = product;
    const priceDiff = product.price - selectedExchangeItem.price;
    document.getElementById('priceDifference').value = (priceDiff >= 0 ? '+' : '') + '₹' + Math.abs(priceDiff).toFixed(2);
    document.getElementById('submitExchangeBtn').disabled = false;
    
    document.getElementById('replacementProductsList').innerHTML = `
        <div class="alert alert-success">
            <strong>Selected: ${product.name}</strong><br>
            Price: ₹${product.price.toFixed(2)}<br>
            Difference: ${priceDiff >= 0 ? 'Customer pays' : 'Refund to customer'} ₹${Math.abs(priceDiff).toFixed(2)}
        </div>
    `;
}

function submitNewExchange() {
    const reason = document.getElementById('exchangeReason').value;
    const notes = document.getElementById('exchangeNotes').value;
    
    if (!reason || !selectedReplacementProduct) {
        alert('Please select exchange reason and replacement product');
        return;
    }
    
    const priceDiff = selectedReplacementProduct.price - selectedExchangeItem.price;
    
    if (!confirm(`Process exchange?\n\nOriginal: Order #${exchangeOrderData.order_number}\nReplacement: ${selectedReplacementProduct.name}\nPrice Difference: ${priceDiff >= 0 ? 'Customer pays' : 'Refund'} ₹${Math.abs(priceDiff).toFixed(2)}`)) {
        return;
    }
    
    fetch('/admin/process-return', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            order_id: exchangeOrderData.id,
            order_number: exchangeOrderData.order_number,
            return_type: 'exchange',
            return_reason: reason,
            refund_amount: priceDiff,
            refund_method: priceDiff > 0 ? 'cash' : 'store_credit',
            replacement_product_id: selectedReplacementProduct.id,
            notes: notes
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('newExchangeModal')).hide();
            setTimeout(() => {
                loadAndShowReturnReceipt(data.return_id);
            }, 300);
        } else {
            alert('Error: ' + (data.message || 'Failed to process exchange'));
        }
    })
    .catch(e => alert('Error: ' + e.message));
}

// OTHER FUNCTIONS
function viewReturn(id) {
    if (confirm('Load detailed return information?')) {
        fetch('/admin/get-return-details?id=' + id)
            .then(r => r.json())
            .then(data => {
                if (data.success && data.return) {
                    const ret = data.return;
                    alert(`Return #${ret.return_number}\n\nOrder: ${ret.original_order_number}\nCustomer: ${ret.customer_name}\nType: ${ret.return_type}\nReason: ${ret.return_reason}\nAmount: ₹${ret.refund_amount}\nStatus: ${ret.status}\nMethod: ${ret.refund_method}\nDate: ${ret.created_at}`);
                }
            });
    }
}

function approveReturn(id) {
    if (confirm('Approve this return request?')) {
        fetch('/admin/approve-return', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({return_id: id})
        })
        .then(r => r.json())
        .then(data => {
            alert(data.success ? '✅ Return approved!' : 'Error: ' + data.message);
            if (data.success) location.reload();
        });
    }
}

function rejectReturn(id) {
    const reason = prompt('Enter rejection reason:');
    if (reason) {
        fetch('/admin/reject-return', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({return_id: id, reason: reason})
        })
        .then(r => r.json())
        .then(data => {
            alert(data.success ? '✅ Return rejected' : 'Error: ' + data.message);
            if (data.success) location.reload();
        });
    }
}

function processRefund(id) {
    if (confirm('Process refund for this return?')) {
        fetch('/admin/process-refund', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({return_id: id})
        })
        .then(r => r.json())
        .then(data => {
            alert(data.success ? '✅ Refund processed' : 'Error: ' + data.message);
            if (data.success) location.reload();
        });
    }
}

function exportReturns() {
    const table = document.getElementById('returnsTable');
    if (!table) {
        alert('No returns to export');
        return;
    }
    
    let csv = 'Return Number,Original Order,Customer,Type,Reason,Amount,Refund Method,Status,Date\n';
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let row of rows) {
        if (row.style.display !== 'none') {
            const cells = row.getElementsByTagName('td');
            const rowData = [];
            for (let i = 0; i < cells.length - 1; i++) {
                rowData.push('"' + cells[i].textContent.trim() + '"');
            }
            csv += rowData.join(',') + '\n';
        }
    }
    
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `returns_export_${new Date().toISOString().split('T')[0]}.csv`;
    link.click();
    URL.revokeObjectURL(url);
}

// RECEIPT FUNCTIONS
function loadAndShowReturnReceipt(returnId) {
    fetch(`/admin/get-return-receipt?id=${returnId}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                displayReturnReceipt(data.receipt);
                const modal = new bootstrap.Modal(document.getElementById('receiptModal'));
                modal.show();
            }
        });
}

function viewReturnReceipt(returnId) {
    fetch(`/admin/get-return-receipt?id=${returnId}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                displayReturnReceipt(data.receipt);
                const modal = new bootstrap.Modal(document.getElementById('receiptModal'));
                modal.show();
            } else {
                alert('Error loading receipt: ' + data.message);
            }
        })
        .catch(e => alert('Error: ' + e.message));
}

function displayReturnReceipt(receipt) {
    const type = receipt.return_type === 'exchange' ? 'Exchange' : 'Return';
    document.getElementById('receiptTitle').textContent = type + ' Receipt';
    
    let html = `
        <div style="text-align: center; border-bottom: 1px dashed #666; padding-bottom: 10px; margin-bottom: 10px;">
            <strong style="font-size: 14px;">B-PLUS POS SYSTEM</strong><br>
            <small>${receipt.store_name || 'Store'}</small><br>
            <small>${receipt.store_address || ''}</small><br>
            <small>Ph: ${receipt.store_phone || ''}</small>
        </div>
        
        <table style="width: 100%; margin-bottom: 10px;">
            <tr><td><strong>${type} #:</strong></td><td style="text-align: right;"><strong>${receipt.return_number}</strong></td></tr>
            <tr><td>Original Order:</td><td style="text-align: right;">${receipt.original_order_number}</td></tr>
            <tr><td>Date:</td><td style="text-align: right;">${new Date(receipt.created_at).toLocaleString()}</td></tr>
            <tr><td>Customer:</td><td style="text-align: right;">${receipt.customer_name || 'Walk-in'}</td></tr>
            <tr><td>Type:</td><td style="text-align: right;"><strong>${type}</strong></td></tr>
        </table>
        
        <div style="border-top: 1px dashed #666; border-bottom: 1px dashed #666; padding: 10px 0; margin: 10px 0;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                <span><strong>Reason:</strong></span>
                <span>${receipt.return_reason}</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                <span><strong>Refund Method:</strong></span>
                <span>${receipt.refund_method.toUpperCase()}</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                <span><strong>Status:</strong></span>
                <span>${receipt.status.toUpperCase()}</span>
            </div>
        </div>
        
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
            <tr style="background: #f0f0f0; border-bottom: 1px solid #999;">
                <td><strong>Description</strong></td>
                <td style="text-align: right;"><strong>Amount</strong></td>
            </tr>
            <tr style="border-bottom: 1px dotted #999;">
                <td>Original Order Amount</td>
                <td style="text-align: right;">₹${parseFloat(receipt.order_total || 0).toFixed(2)}</td>
            </tr>
            ${receipt.return_type === 'exchange' ? 
                `<tr style="border-bottom: 1px dotted #999;">
                    <td>Replacement Product Cost</td>
                    <td style="text-align: right;">₹${parseFloat(receipt.replacement_price || 0).toFixed(2)}</td>
                </tr>` : ''}
            <tr style="border-bottom: 2px solid #999; background: #f9f9f9;">
                <td><strong>Refund Amount</strong></td>
                <td style="text-align: right;"><strong>₹${parseFloat(receipt.refund_amount).toFixed(2)}</strong></td>
            </tr>
        </table>
        
        <div style="text-align: center; border-top: 1px dashed #666; padding-top: 10px; font-size: 11px;">
            <p>${receipt.notes || 'Thank you for your business!'}</p>
            <p>Processed by: ${receipt.cashier_name || 'Staff'}</p>
            <p style="margin-top: 10px;">
                <strong style="font-size: 12px;">${type} ID: ${receipt.return_number}</strong><br>
                <small style="font-size: 9px;">Generated: ${new Date().toLocaleString()}</small>
            </p>
        </div>
    `;
    
    document.getElementById('receiptContent').innerHTML = html;
}

function printReceipt() {
    const printContent = document.getElementById('receiptContent').innerHTML;
    const printWindow = window.open('', '', 'width=400,height=600');
    printWindow.document.write(`
        <html>
        <head>
            <title>Receipt</title>
            <style>
                body { font-family: 'Courier New', monospace; margin: 0; padding: 10px; font-size: 12px; }
                @media print { body { margin: 0; padding: 0; } }
            </style>
        </head>
        <body>
            ${printContent}
        </body>
        </html>
    `);
    printWindow.document.close();
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 250);
}

function downloadReceiptPDF() {
    const type = document.getElementById('receiptTitle').textContent;
    const content = document.getElementById('receiptContent').innerText;
    const blob = new Blob([content], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `${type.replace(' ', '_')}_${new Date().toISOString().split('T')[0]}.txt`;
    link.click();
    URL.revokeObjectURL(url);
}

// Auto-search on Enter key
if (document.getElementById('searchReturn')) {
    document.getElementById('searchReturn').addEventListener('keyup', function(e) {
        if (e.key === 'Enter') filterReturns();
    });
}
</script>

<?php include __DIR__ . '/_footer.php'; ?>
