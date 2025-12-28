<?php include __DIR__ . '/_header.php'; ?>

<style>
    .orders-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }
    .page-title {
        font-size: 28px;
        font-weight: 700;
        color: #333;
    }
    .header-actions { display: flex; gap: 10px; }
    .btn {
        padding: 10px 18px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: 600;
        font-size: 14px;
        transition: 0.3s;
    }
    .btn-primary { background: #667eea; color: white; }
    .btn-primary:hover { background: #5568d3; }
    .btn-secondary { background: #f0f0f0; color: #333; border: 1px solid #ddd; }
    .btn-secondary:hover { background: #e0e0e0; }
    
    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin-bottom: 25px;
    }
    .stat-item {
        background: white;
        padding: 18px;
        border-radius: 6px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border-left: 4px solid #667eea;
    }
    .stat-label {
        font-size: 11px;
        color: #999;
        text-transform: uppercase;
        margin-bottom: 8px;
    }
    .stat-num {
        font-size: 26px;
        font-weight: 700;
        color: #667eea;
    }
    
    .table-box {
        background: white;
        border-radius: 6px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    .table-wrapper { overflow-x: auto; }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th {
        background: #f8f8f8;
        padding: 14px;
        text-align: left;
        font-weight: 600;
        font-size: 12px;
        color: #666;
        border-bottom: 2px solid #ddd;
    }
    td {
        padding: 12px 14px;
        color: #333;
        border-bottom: 1px solid #f0f0f0;
    }
    tr:hover { background: #f9f9f9; }
    .order-num { font-weight: 700; color: #667eea; }
    .badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 10px;
        font-weight: 600;
    }
    .badge-completed { background: #d4edda; color: #155724; }
    .badge-pending { background: #fff3cd; color: #856404; }
    .badge-held { background: #d1ecf1; color: #0c5460; }
    .actions-btns {
        display: flex;
        gap: 5px;
    }
    .action-btn {
        width: 32px;
        height: 32px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .btn-view { background: #e3f2fd; color: #1976d2; }
    .btn-view:hover { background: #1976d2; color: white; }
    .btn-print { background: #f3e5f5; color: #7b1fa2; }
    .btn-print:hover { background: #7b1fa2; color: white; }
    .empty { text-align: center; padding: 40px; color: #999; }
    
    /* Modal */
    .modal-bg {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 999;
    }
    .modal-bg.show { display: flex; justify-content: center; align-items: center; }
    .modal-box {
        background: white;
        border-radius: 8px;
        max-width: 500px;
        width: 90%;
        max-height: 85vh;
        overflow-y: auto;
    }
    .modal-header {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        padding: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .modal-header h3 { margin: 0; font-size: 16px; }
    .modal-close {
        background: none;
        border: none;
        color: white;
        font-size: 24px;
        cursor: pointer;
    }
    .modal-body { padding: 18px; }
    .modal-footer {
        padding: 12px 18px;
        border-top: 1px solid #e0e0e0;
        display: flex;
        justify-content: flex-end;
        gap: 8px;
    }
    
    /* Invoice Styles */
    .invoice-header {
        text-align: center;
        margin-bottom: 20px;
        border-bottom: 2px solid #667eea;
        padding-bottom: 15px;
    }
    .invoice-header h2 {
        margin: 0 0 5px 0;
        font-size: 20px;
        color: #333;
    }
    .invoice-header p {
        margin: 0;
        font-size: 12px;
        color: #999;
    }
    .invoice-info {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-bottom: 15px;
        font-size: 12px;
    }
    .info-block label {
        font-weight: 600;
        color: #333;
        display: block;
        margin-bottom: 3px;
    }
    .info-block span {
        color: #666;
    }
    .items-table {
        width: 100%;
        margin-bottom: 15px;
        font-size: 12px;
        border-collapse: collapse;
    }
    .items-table th {
        background: #f0f0f0;
        padding: 8px;
        text-align: left;
        font-weight: 600;
        border-bottom: 2px solid #ddd;
    }
    .items-table td {
        padding: 8px;
        border-bottom: 1px solid #f0f0f0;
    }
    .items-table .amount {
        text-align: right;
    }
    .invoice-summary {
        background: #f8f8f8;
        padding: 12px;
        border-radius: 4px;
        margin-bottom: 15px;
        font-size: 12px;
    }
    .summary-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-bottom: 8px;
    }
    .summary-row.total {
        border-top: 2px solid #667eea;
        padding-top: 8px;
        margin-top: 8px;
        font-weight: 700;
        font-size: 14px;
        color: #667eea;
    }
    .invoice-footer {
        text-align: center;
        font-size: 11px;
        color: #999;
        margin-top: 15px;
        padding-top: 12px;
        border-top: 1px solid #e0e0e0;
    }
</style>

<div class="orders-wrapper">
    <!-- Header -->
    <div class="page-header">
        <div class="page-title">Order Management</div>
        <div class="header-actions">
            <button class="btn btn-secondary" onclick="location.reload()">Refresh</button>
            <button class="btn btn-primary" onclick="exportCSV()">Export CSV</button>
        </div>
    </div>

    <!-- Statistics -->
    <div class="stats-row">
        <div class="stat-item">
            <div class="stat-label">Total Orders</div>
            <div class="stat-num" id="stat-total">0</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Today Orders</div>
            <div class="stat-num" id="stat-today">0</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Total Revenue</div>
            <div class="stat-num" id="stat-revenue">₹0</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Completed</div>
            <div class="stat-num" id="stat-completed">0</div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-box">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Cashier</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th style="text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody id="orders-list">
                    <tr><td colspan="7" class="empty">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal-bg" id="modal">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Invoice</h3>
            <button class="modal-close" onclick="closeModal()">×</button>
        </div>
        <div class="modal-body" id="modal-content"></div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal()">Close</button>
            <button class="btn btn-primary" onclick="doPrint()">Print Invoice</button>
        </div>
    </div>
</div>

<script>
var ordersArray = [];
var selectedOrder = null;

document.addEventListener('DOMContentLoaded', function() {
    initPage();
});

function initPage() {
    window.storeSettings = <?php echo json_encode($storeSettings ?? []); ?>;
    var data = <?php echo json_encode($orders ?? []); ?>;
    if (data && Array.isArray(data)) {
        ordersArray = data;
        renderOrders();
        updateStats();
    } else {
        document.getElementById('orders-list').innerHTML = '<tr><td colspan="7" class="empty">No orders found</td></tr>';
    }
}

function renderOrders() {
    var html = '';
    if (!ordersArray || ordersArray.length === 0) {
        html = '<tr><td colspan="7" class="empty">No orders found</td></tr>';
        document.getElementById('orders-list').innerHTML = html;
        return;
    }

    ordersArray.forEach(function(order) {
        var status = (order.order_status || 'pending').toLowerCase();
        var date = new Date(order.created_at).toLocaleString();
        var amount = parseFloat(order.total || 0).toFixed(2);
        
        html += '<tr>';
        html += '<td><span class="order-num">#' + order.order_number + '</span></td>';
        html += '<td>' + (order.cashier_name || 'N/A') + '</td>';
        html += '<td><strong>₹' + amount + '</strong></td>';
        html += '<td>' + (order.payment_method || 'N/A') + '</td>';
        html += '<td><span class="badge badge-' + status + '">' + status.toUpperCase() + '</span></td>';
        html += '<td>' + date + '</td>';
        html += '<td style="text-align: center;"><div class="actions-btns">';
        html += '<button class="action-btn btn-view" onclick="viewInvoice(' + order.id + ')" title="View Invoice">👁️</button>';
        html += '<button class="action-btn btn-print" onclick="printInvoice(' + order.id + ')" title="Print Invoice">🖨️</button>';
        html += '</div></td>';
        html += '</tr>';
    });

    document.getElementById('orders-list').innerHTML = html;
}

function updateStats() {
    var today = new Date().toDateString();
    var totalOrders = ordersArray.length;
    var todayCount = 0;
    var totalRev = 0;
    var completedCount = 0;

    ordersArray.forEach(function(order) {
        var amt = parseFloat(order.total || 0);
        totalRev += amt;
        
        if (new Date(order.created_at).toDateString() === today) {
            todayCount++;
        }
        
        if ((order.order_status || '').toLowerCase() === 'completed') {
            completedCount++;
        }
    });

    document.getElementById('stat-total').textContent = totalOrders;
    document.getElementById('stat-today').textContent = todayCount;
    document.getElementById('stat-revenue').textContent = '₹' + totalRev.toFixed(2);
    document.getElementById('stat-completed').textContent = completedCount;
}

function viewInvoice(orderId) {
    selectedOrder = null;
    for (var i = 0; i < ordersArray.length; i++) {
        if (ordersArray[i].id === orderId) {
            selectedOrder = ordersArray[i];
            break;
        }
    }
    
    if (!selectedOrder) return;
    
    document.getElementById('modal-content').innerHTML = generateInvoiceHTML();
    document.getElementById('modal').classList.add('show');
}

function closeModal() {
    document.getElementById('modal').classList.remove('show');
    selectedOrder = null;
}

function doPrint() {
    if (selectedOrder) {
        printInvoice(selectedOrder.id);
    }
}

function generateInvoiceHTML() {
    if (!selectedOrder) return '';
    
    var subtotal = parseFloat(selectedOrder.subtotal || 0).toFixed(2);
    var discount = parseFloat(selectedOrder.discount_amount || 0).toFixed(2);
    var tax = parseFloat(selectedOrder.tax_amount || 0).toFixed(2);
    var total = parseFloat(selectedOrder.total || 0).toFixed(2);
    var items = selectedOrder.items || [];
    var storeSettings = window.storeSettings || {};
    
    var html = '';
    html += '<style>';
    html += 'body{font-family:"Courier New",monospace;background:#f5f5f5;padding:20px}';
    html += '.receipt-container{max-width:80mm;margin:0 auto;background:#fff;padding:10mm;box-shadow:0 0 10px rgba(0,0,0,0.1)}';
    html += '.receipt-header{text-align:center;border-bottom:2px dashed #000;padding-bottom:10px;margin-bottom:10px}';
    html += '.store-name{font-size:18px;font-weight:bold;margin-bottom:5px}';
    html += '.store-details{font-size:10px;line-height:1.4}';
    html += '.receipt-info{font-size:11px;margin-bottom:10px;padding-bottom:10px;border-bottom:1px dashed #000}';
    html += '.receipt-info div{display:flex;justify-content:space-between;margin-bottom:3px}';
    html += '.items-table{width:100%;font-size:10px;margin-bottom:10px;border-collapse:collapse}';
    html += '.items-table th{text-align:left;border-bottom:1px solid #000;padding:5px 0}';
    html += '.items-table td{padding:5px 0;vertical-align:top}';
    html += '.item-name{font-weight:bold}';
    html += '.item-details{font-size:9px;color:#666}';
    html += '.text-right{text-align:right}';
    html += '.tax-summary{font-size:9px;background:#f9f9f9;padding:8px;margin-bottom:10px}';
    html += '.tax-summary div{display:flex;justify-content:space-between;margin-bottom:2px}';
    html += '.totals{border-top:1px dashed #000;padding-top:10px;margin-bottom:10px}';
    html += '.totals div{display:flex;justify-content:space-between;margin-bottom:5px;font-size:11px}';
    html += '.totals .grand-total{font-size:14px;font-weight:bold;border-top:2px solid #000;padding-top:8px;margin-top:8px}';
    html += '.payment-info{font-size:11px;margin-bottom:10px;padding-bottom:10px;border-bottom:1px dashed #000}';
    html += '.footer{text-align:center;font-size:10px;margin-top:15px}';
    html += '.action-buttons{text-align:center;margin:20px 0;display:flex;gap:5px;justify-content:center;flex-wrap:wrap}';
    html += '.action-buttons button{padding:10px 15px;font-size:12px;cursor:pointer;border:none;border-radius:4px;color:#fff}';
    html += '</style>';
    html += '<div class="receipt-container">';
    
    html += '<div class="receipt-header">';
    html += '<div class="store-name">' + (storeSettings.store_name || 'B-Plus POS') + '</div>';
    if (storeSettings.store_address) html += '<div class="store-details">' + storeSettings.store_address.replace(/\n/g, '<br>') + '</div>';
    if (storeSettings.store_phone) html += '<div class="store-details">Tel: ' + storeSettings.store_phone + '</div>';
    if (storeSettings.store_email) html += '<div class="store-details">Email: ' + storeSettings.store_email + '</div>';
    if (storeSettings.store_gstin) html += '<div class="store-details">GSTIN: ' + storeSettings.store_gstin + '</div>';
    html += '</div>';
    
    html += '<div class="receipt-info">';
    html += '<div><span>Receipt #:</span><strong>' + selectedOrder.order_number + '</strong></div>';
    html += '<div><span>Date:</span><span>' + new Date(selectedOrder.created_at).toLocaleString() + '</span></div>';
    if (selectedOrder.customer_name) html += '<div><span>Customer:</span><span>' + selectedOrder.customer_name + '</span></div>';
    html += '<div><span>Cashier:</span><span>' + (selectedOrder.cashier_name || 'N/A') + '</span></div>';
    html += '</div>';
    
    var taxBreakdown = {};
    if (items && items.length > 0) {
        html += '<table class="items-table">';
        html += '<thead><tr><th>Item</th><th class="text-right">Qty</th><th class="text-right">Price</th><th class="text-right">Total</th></tr></thead>';
        html += '<tbody>';
        
        items.forEach(function(item) {
            var qty = parseFloat(item.quantity || 0);
            var lineSubtotal = parseFloat(item.line_subtotal || 0);
            var lineTotal = parseFloat(item.total || 0);
            var itemTax = parseFloat(item.tax_amount || 0);
            var unitPrice = (qty > 0) ? (lineSubtotal / qty) : 0;
            var regularPrice = parseFloat(item.regular_price || 0);
            var mrp = regularPrice > 0 ? regularPrice : unitPrice;
            var mrpDiscount = (mrp - unitPrice) * qty;
            var couponDiscount = lineSubtotal - lineTotal;
            var totalDiscount = mrpDiscount + couponDiscount;
            var finalPrice = lineTotal + itemTax;
            var taxPct = (itemTax > 0 && lineTotal > 0) ? ((itemTax / lineTotal) * 100).toFixed(2) : 0;
            
            var taxKey = parseFloat(taxPct).toFixed(2);
            if (!taxBreakdown[taxKey]) taxBreakdown[taxKey] = 0;
            taxBreakdown[taxKey] += itemTax;
            
            html += '<tr><td><div class="item-name">' + (item.product_name || 'Product') + '</div>';
            html += '<div class="item-details">';
            html += 'MRP: ₹' + mrp.toFixed(2) + ' × ' + qty + '<br>';
            if (totalDiscount > 0) html += 'Discount: ₹' + totalDiscount.toFixed(2) + '<br>';
            if (itemTax > 0) html += 'Tax @ ' + taxPct + '%: ₹' + itemTax.toFixed(2) + '<br>';
            html += 'Final: ₹' + finalPrice.toFixed(2);
            html += '</div></td>';
            html += '<td class="text-right">' + qty + '</td>';
            html += '<td class="text-right">₹' + unitPrice.toFixed(2) + '</td>';
            html += '<td class="text-right">₹' + finalPrice.toFixed(2) + '</td></tr>';
        });
        
        html += '</tbody></table>';
    }
    
    if (Object.keys(taxBreakdown).length > 0) {
        html += '<div class="tax-summary"><strong>Tax Breakdown:</strong>';
        for (var rate in taxBreakdown) {
            html += '<div><span>GST @ ' + rate + '%:</span><span>₹' + taxBreakdown[rate].toFixed(2) + '</span></div>';
        }
        html += '</div>';
    }
    
    html += '<div class="totals">';
    html += '<div><span>Subtotal:</span><span>₹' + subtotal + '</span></div>';
    if (parseFloat(discount) > 0) html += '<div><span>Discount:</span><span>-₹' + discount + '</span></div>';
    html += '<div><span>Tax:</span><span>₹' + tax + '</span></div>';
    html += '<div class="grand-total"><span>TOTAL:</span><span>₹' + total + '</span></div>';
    html += '</div>';
    
    html += '<div class="payment-info">';
    html += '<strong>Payment Method: ' + (selectedOrder.payment_method || 'CASH').toUpperCase() + '</strong>';
    html += '</div>';
    
    html += '<div class="footer">';
    html += '<div style="margin-top:15px;font-weight:bold;">' + (storeSettings.receipt_footer || 'Thank you for your business!') + '</div>';
    if (storeSettings.receipt_terms) html += '<div style="margin-top:10px;font-size:8px;">' + storeSettings.receipt_terms.replace(/\n/g, '<br>') + '</div>';
    html += '<div style="margin-top:15px;font-weight:bold;">Powered by B-Plus POS</div>';
    html += '</div>';
    
    html += '</div>';
    return html;
}

function printInvoice(orderId) {
    var order = null;
    for (var i = 0; i < ordersArray.length; i++) {
        if (ordersArray[i].id === orderId) {
            order = ordersArray[i];
            break;
        }
    }
    
    if (!order) return;

    var subtotal = parseFloat(order.subtotal || 0).toFixed(2);
    var discount = parseFloat(order.discount_amount || 0).toFixed(2);
    var tax = parseFloat(order.tax_amount || 0).toFixed(2);
    var total = parseFloat(order.total || 0).toFixed(2);
    var items = order.items || [];

    var html = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Receipt - ' + order.order_number + '</title><style>';
    html += '@media print{body{margin:0;padding:0;background:#fff}.no-print{display:none!important}.receipt-container{box-shadow:none;margin:0;padding:5mm;background:#fff}@page{margin:0;size:80mm auto}@page :first{margin-top:0}}';
    html += '@media print and (max-width:58mm){@page{size:58mm auto}body{font-size:9px}.receipt-container{max-width:58mm}.store-name{font-size:14px}.items-table{font-size:8px}}';
    html += '*{margin:0;padding:0;box-sizing:border-box}body{font-family:"Courier New",monospace;background:#f5f5f5;padding:20px}';
    html += '.receipt-container{max-width:80mm;margin:0 auto;background:#fff;padding:10mm;box-shadow:0 0 10px rgba(0,0,0,0.1)}';
    html += '.receipt-header{text-align:center;border-bottom:2px dashed #000;padding-bottom:10px;margin-bottom:10px}';
    html += '.store-name{font-size:18px;font-weight:bold;margin-bottom:5px}.store-details{font-size:10px;line-height:1.4}';
    html += '.receipt-info{font-size:11px;margin-bottom:10px;padding-bottom:10px;border-bottom:1px dashed #000}';
    html += '.receipt-info div{display:flex;justify-content:space-between;margin-bottom:3px}';
    html += '.items-table{width:100%;font-size:10px;margin-bottom:10px;border-collapse:collapse}';
    html += '.items-table th{text-align:left;border-bottom:1px solid #000;padding:5px 0}.items-table td{padding:5px 0;vertical-align:top}';
    html += '.item-name{font-weight:bold}.item-details{font-size:9px;color:#666}.text-right{text-align:right}';
    html += '.totals{border-top:1px dashed #000;padding-top:10px;margin-bottom:10px}';
    html += '.totals div{display:flex;justify-content:space-between;margin-bottom:5px;font-size:11px}';
    html += '.totals .grand-total{font-size:14px;font-weight:bold;border-top:2px solid #000;padding-top:8px;margin-top:8px}';
    html += '.payment-info{font-size:11px;margin-bottom:10px;padding-bottom:10px;border-bottom:1px dashed #000}';
    html += '.footer{text-align:center;font-size:10px;margin-top:15px}';
    html += '.action-buttons{text-align:center;margin:20px 0}.action-buttons button{margin:0 5px;padding:10px 20px;font-size:14px;cursor:pointer;border:none;border-radius:5px;background:#667eea;color:#fff}';
    html += '.action-buttons button:hover{background:#5568d3}';
    html += '</style></head><body>';
    
    html += '<div class="receipt-container">';
    html += '<div class="receipt-header">';
    html += '<div class="store-name">B-Plus POS</div>';
    html += '<div class="store-details">Point of Sale System</div>';
    html += '</div>';
    
    html += '<div class="receipt-info">';
    html += '<div><span>Receipt #:</span><strong>' + order.order_number + '</strong></div>';
    html += '<div><span>Date:</span><span>' + new Date(order.created_at).toLocaleString() + '</span></div>';
    html += '<div><span>Cashier:</span><span>' + (order.cashier_name || 'N/A') + '</span></div>';
    html += '</div>';
    
    if (items && items.length > 0) {
        html += '<table class="items-table">';
        html += '<thead><tr><th>Item</th><th class="text-right">Qty</th><th class="text-right">Price</th><th class="text-right">Total</th></tr></thead>';
        html += '<tbody>';
        
        items.forEach(function(item) {
            var qty = parseFloat(item.quantity || 0);
            var price = parseFloat(item.price || 0);
            var amount = qty * price;
            var itemTax = parseFloat(item.tax_amount || 0);
            var finalPrice = amount + itemTax;
            
            html += '<tr><td><div class="item-name">' + (item.product_name || 'Product') + '</div>';
            html += '<div class="item-details">';
            html += 'Price: ₹' + price.toFixed(2) + ' × ' + qty + '<br>';
            if (itemTax > 0) {
                var taxPct = (itemTax / amount * 100).toFixed(2);
                html += 'Tax @ ' + taxPct + '%: ₹' + itemTax.toFixed(2) + '<br>';
            }
            html += 'Final: ₹' + finalPrice.toFixed(2);
            html += '</div></td>';
            html += '<td class="text-right">' + qty + '</td>';
            html += '<td class="text-right">₹' + price.toFixed(2) + '</td>';
            html += '<td class="text-right">₹' + finalPrice.toFixed(2) + '</td></tr>';
        });
        
        html += '</tbody></table>';
    }
    
    html += '<div class="totals">';
    html += '<div><span>Subtotal:</span><span>₹' + subtotal + '</span></div>';
    if (parseFloat(discount) > 0) {
        html += '<div><span>Discount:</span><span>-₹' + discount + '</span></div>';
    }
    html += '<div><span>Tax:</span><span>₹' + tax + '</span></div>';
    html += '<div class="grand-total"><span>TOTAL:</span><span>₹' + total + '</span></div>';
    html += '</div>';
    
    html += '<div class="payment-info">';
    html += '<strong>Payment Method: ' + (order.payment_method || 'CASH').toUpperCase() + '</strong>';
    html += '</div>';
    
    html += '<div class="footer">';
    html += '<div style="margin-top:15px;font-weight:bold;">Thank you for your business!</div>';
    html += '<div style="margin-top:5px;font-size:8px;">Powered by B-Plus POS</div>';
    html += '</div>';
    
    html += '</div>';
    
    html += '<div class="action-buttons no-print" style="text-align:center;margin:20px 0;">';
    html += '<button onclick="window.print()" style="background:#10b981;margin:5px;padding:10px 15px;border:none;color:#fff;border-radius:4px;cursor:pointer;">🖨️ Print</button>';
    html += '<button onclick="shareWhatsApp()" style="background:#25d366;margin:5px;padding:10px 15px;border:none;color:#fff;border-radius:4px;cursor:pointer;">📱 WhatsApp</button>';
    html += '<button onclick="emailReceipt()" style="background:#3b82f6;margin:5px;padding:10px 15px;border:none;color:#fff;border-radius:4px;cursor:pointer;">📧 Email</button>';
    html += '<button onclick="downloadPDF()" style="background:#f59e0b;margin:5px;padding:10px 15px;border:none;color:#fff;border-radius:4px;cursor:pointer;">📄 PDF</button>';
    html += '<button onclick="window.close()" style="background:#6b7280;margin:5px;padding:10px 15px;border:none;color:#fff;border-radius:4px;cursor:pointer;">✕ Close</button>';
    html += '</div>';
    
    html += '<script>';
    html += 'var printOrderId = ' + order.id + ';';
    html += 'window.addEventListener("DOMContentLoaded",function(){setTimeout(function(){window.print()},500)});';
    html += 'document.addEventListener("keydown",function(e){if((e.ctrlKey||e.metaKey)&&e.key==="p"){e.preventDefault();window.print()}if(e.key==="Escape"){window.close()}});';
    html += 'function shareWhatsApp(){var phone=prompt("Enter WhatsApp number (with country code, e.g., 919876543210):");if(!phone)return;var cleanPhone=phone.replace(/\\D/g,"");if(cleanPhone.length<10){alert("Invalid number");return;}var msg="*' + (order.order_number || 'Receipt') + '*\\n";msg+="₹' + total + '\\nThank you!";window.open("https://wa.me/"+cleanPhone+"?text="+encodeURIComponent(msg));}';
    html += 'function emailReceipt(){var email=prompt("Enter email address:");if(!email)return;if(!/^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/.test(email)){alert("Invalid email");return;}var btn=event.target;btn.disabled=true;btn.textContent="Sending...";fetch("/admin/send-receipt-email",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({order_id:printOrderId,email:email})}).then(r=>r.json()).then(d=>{alert(d.success?"Email sent!":"Error: "+d.message);}).catch(e=>{alert("Error: "+e.message);}).finally(()=>{btn.disabled=false;btn.textContent="📧 Email";});}';
    html += 'function downloadPDF(){alert("Click Print and select Save as PDF");window.print();}';
    html += '</scr' + 'ipt>';
    html += '</body></html>';

    var w = window.open('', '', 'width=900,height=700');
    w.document.write(html);
    w.document.close();
}

function exportCSV() {
    if (!ordersArray || ordersArray.length === 0) {
        alert('No orders to export');
        return;
    }

    var csv = 'Order ID,Cashier,Amount,Payment,Status,Date\n';
    ordersArray.forEach(function(o) {
        csv += '"' + o.order_number + '","' + (o.cashier_name || 'N/A') + '","' + o.total + '","' + (o.payment_method || 'N/A') + '","' + o.order_status + '","' + new Date(o.created_at).toLocaleDateString() + '"\n';
    });

    var blob = new Blob([csv], {type: 'text/csv'});
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = 'orders_' + new Date().toISOString().split('T')[0] + '.csv';
    a.click();
    URL.revokeObjectURL(url);
}

document.getElementById('modal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

<?php include __DIR__ . '/_footer.php'; ?>
