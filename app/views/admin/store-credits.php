<?php
$pageTitle = 'Store Credits Management';
require_once __DIR__ . '/_header.php';
?>

<style>
    .credit-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .stat-box {
        background: white;
        padding: 20px;
        border-radius: 12px;
        border-left: 4px solid #667eea;
        margin-bottom: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .stat-box .label {
        font-size: 13px;
        color: #7f8c8d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    .stat-box .value {
        font-size: 28px;
        font-weight: 700;
        color: #667eea;
    }

    .badge-success {
        background: #e8f5e9;
        color: #2e7d32;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-warning {
        background: #fff3cd;
        color: #856404;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-info {
        background: #e3f2fd;
        color: #1565c0;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .filter-section {
        background: white;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .table-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .table {
        margin-bottom: 0;
    }

    .table th {
        background: #f8f9fa;
        border-bottom: 2px solid #e1e8ed;
        font-weight: 600;
        color: #2c3e50;
        padding: 15px;
    }

    .table td {
        padding: 15px;
        border-bottom: 1px solid #e1e8ed;
        vertical-align: middle;
    }

    .table tbody tr:hover {
        background: #f8f9fa;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #7f8c8d;
    }

    .empty-state i {
        font-size: 64px;
        color: #ddd;
        margin-bottom: 15px;
    }

    .btn-export {
        background: #667eea;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-export:hover {
        background: #764ba2;
        color: white;
    }
</style>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h2 class="mb-2">💳 Store Credits Management</h2>
                    <p class="text-muted">Monitor and manage all store credits in the system</p>
                </div>
                <button class="btn-export" onclick="exportStoreCredits()">
                    <i class="fas fa-download"></i> Export Report
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Grid -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-box">
                <div class="label">Total Store Credits Issued</div>
                <div class="value" id="totalIssued">₹0.00</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box">
                <div class="label">Total Store Credits Redeemed</div>
                <div class="value" id="totalRedeemed">₹0.00</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box">
                <div class="label">Outstanding Store Credits</div>
                <div class="value" id="totalOutstanding">₹0.00</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box">
                <div class="label">Customers with Credits</div>
                <div class="value" id="customerCount">0</div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <div class="row">
            <div class="col-md-3">
                <label class="form-label" style="font-weight: 600;">Filter by Status</label>
                <select class="form-control" id="statusFilter" onchange="applyFilters()">
                    <option value="">All</option>
                    <option value="active">Active (Has Credits)</option>
                    <option value="redeemed">Fully Redeemed</option>
                    <option value="pending">Pending Transactions</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" style="font-weight: 600;">Search Customer</label>
                <input type="text" class="form-control" id="searchInput" placeholder="Name, email, or phone..." onkeyup="applyFilters()">
            </div>
            <div class="col-md-3">
                <label class="form-label" style="font-weight: 600;">Date Range</label>
                <select class="form-control" id="dateRange" onchange="applyFilters()">
                    <option value="">All Time</option>
                    <option value="7">Last 7 Days</option>
                    <option value="30">Last 30 Days</option>
                    <option value="90">Last 90 Days</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" style="font-weight: 600;">&nbsp;</label>
                <button class="form-control btn-export" onclick="resetFilters()">
                    <i class="fas fa-redo"></i> Reset Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Customers with Store Credits Table -->
    <div class="table-container">
        <h5 style="padding: 20px; padding-bottom: 0; margin-bottom: 0;">📋 Customers & Their Store Credits</h5>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Available Balance</th>
                        <th>Total Issued</th>
                        <th>Total Used</th>
                        <th>Status</th>
                        <th style="text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody id="creditsTableBody">
                    <tr>
                        <td colspan="7" class="empty-state">
                            <i class="fas fa-spinner fa-spin"></i>
                            <p>Loading store credits...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Transaction History Section -->
    <div style="margin-top: 40px;">
        <div class="table-container">
            <h5 style="padding: 20px; padding-bottom: 0; margin-bottom: 0;">📊 Recent Store Credit Transactions</h5>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Reference</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody id="transactionsTableBody">
                        <tr>
                            <td colspan="6" class="empty-state">
                                <i class="fas fa-spinner fa-spin"></i>
                                <p>Loading transactions...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Customer Details Modal -->
<div class="modal fade" id="customerDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
                <h5 class="modal-title">Customer Store Credits Details</h5>
                <button type="button" class="btn-close" style="filter: brightness(0) invert(1);" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="customerDetailsContent">
                <!-- Loaded dynamically -->
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        loadStoreCreditsData();
        loadTransactionHistory();
    });

    function loadStoreCreditsData() {
        $.ajax({
            url: '/api/store-credits/summary',
            method: 'GET',
            success: function(response) {
                if (response.success && response.data) {
                    const data = response.data;
                    $('#totalIssued').text('₹' + parseFloat(data.total_issued || 0).toFixed(2));
                    $('#totalRedeemed').text('₹' + parseFloat(data.total_redeemed || 0).toFixed(2));
                    $('#totalOutstanding').text('₹' + parseFloat(data.total_outstanding || 0).toFixed(2));
                    $('#customerCount').text(data.customer_count || 0);
                }
                loadCustomerCredits();
            },
            error: function() {
                console.error('Failed to load summary');
            }
        });
    }

    function loadCustomerCredits() {
        const status = $('#statusFilter').val();
        const search = $('#searchInput').val();
        const dateRange = $('#dateRange').val();

        $.ajax({
            url: '/api/store-credits/customers',
            method: 'GET',
            data: {
                status: status,
                search: search,
                date_range: dateRange
            },
            success: function(response) {
                if (response.success) {
                    displayCreditsTable(response.data || []);
                }
            },
            error: function() {
                showError('Failed to load customer credits');
            }
        });
    }

    function displayCreditsTable(customers) {
        const tbody = $('#creditsTableBody');
        tbody.empty();

        if (customers.length === 0) {
            tbody.html(`
                <tr>
                    <td colspan="7" class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>No store credits found</p>
                    </td>
                </tr>
            `);
            return;
        }

        customers.forEach(customer => {
            const balance = parseFloat(customer.available_balance || 0);
            const issued = parseFloat(customer.total_issued || 0);
            const used = parseFloat(customer.total_used || 0);
            
            let status = '<span class="badge-info">No Credits</span>';
            if (balance > 0) {
                status = '<span class="badge-success">Active</span>';
            } else if (issued > 0) {
                status = '<span class="badge-warning">Redeemed</span>';
            }

            tbody.append(`
                <tr>
                    <td>
                        <strong>${escapeHtml(customer.name || 'N/A')}</strong><br>
                        <small class="text-muted">#${customer.id}</small>
                    </td>
                    <td>
                        ${customer.email ? '<div>' + escapeHtml(customer.email) + '</div>' : ''}
                        ${customer.phone ? '<div>' + escapeHtml(customer.phone) + '</div>' : ''}
                    </td>
                    <td><strong>₹${balance.toFixed(2)}</strong></td>
                    <td>₹${issued.toFixed(2)}</td>
                    <td>₹${used.toFixed(2)}</td>
                    <td>${status}</td>
                    <td style="text-align: center;">
                        <button class="btn btn-sm btn-outline-primary" onclick="viewCustomerDetails(${customer.id})" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>
            `);
        });
    }

    function loadTransactionHistory() {
        $.ajax({
            url: '/api/store-credits/transactions',
            method: 'GET',
            data: { limit: 20 },
            success: function(response) {
                if (response.success) {
                    displayTransactions(response.data || []);
                }
            },
            error: function() {
                console.error('Failed to load transactions');
            }
        });
    }

    function displayTransactions(transactions) {
        const tbody = $('#transactionsTableBody');
        tbody.empty();

        if (transactions.length === 0) {
            tbody.html(`
                <tr>
                    <td colspan="6" class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>No transactions found</p>
                    </td>
                </tr>
            `);
            return;
        }

        transactions.forEach(tx => {
            const date = new Date(tx.created_at).toLocaleDateString();
            const type = tx.type === 'issue' ? '<span class="badge-success">Issued</span>' : '<span class="badge-warning">Redeemed</span>';
            const amount = parseFloat(tx.amount || 0).toFixed(2);

            tbody.append(`
                <tr>
                    <td>${date}</td>
                    <td>${escapeHtml(tx.customer_name || 'N/A')}</td>
                    <td>${type}</td>
                    <td><strong>₹${amount}</strong></td>
                    <td><small>${escapeHtml(tx.reference_id || 'N/A')}</small></td>
                    <td><small class="text-muted">${escapeHtml(tx.notes || 'N/A')}</small></td>
                </tr>
            `);
        });
    }

    function viewCustomerDetails(customerId) {
        $.ajax({
            url: `/api/store-credits/customer/${customerId}`,
            method: 'GET',
            success: function(response) {
                if (response.success && response.data) {
                    const customer = response.data;
                    const balance = parseFloat(customer.available_balance || 0);
                    const issued = parseFloat(customer.total_issued || 0);
                    const used = parseFloat(customer.total_used || 0);

                    const html = `
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Customer Name:</strong><br>${escapeHtml(customer.name || 'N/A')}
                            </div>
                            <div class="col-md-6">
                                <strong>Email:</strong><br>${escapeHtml(customer.email || 'N/A')}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Phone:</strong><br>${escapeHtml(customer.phone || 'N/A')}
                            </div>
                            <div class="col-md-6">
                                <strong>Member Since:</strong><br>${new Date(customer.created_at).toLocaleDateString()}
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-4">
                                <div style="text-align: center; padding: 15px; background: #e8f5e9; border-radius: 8px;">
                                    <div style="font-size: 24px; font-weight: 700; color: #2e7d32;">₹${balance.toFixed(2)}</div>
                                    <div style="font-size: 12px; color: #7f8c8d;">Available Balance</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div style="text-align: center; padding: 15px; background: #e3f2fd; border-radius: 8px;">
                                    <div style="font-size: 24px; font-weight: 700; color: #1565c0;">₹${issued.toFixed(2)}</div>
                                    <div style="font-size: 12px; color: #7f8c8d;">Total Issued</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div style="text-align: center; padding: 15px; background: #fff3cd; border-radius: 8px;">
                                    <div style="font-size: 24px; font-weight: 700; color: #856404;">₹${used.toFixed(2)}</div>
                                    <div style="font-size: 12px; color: #7f8c8d;">Total Redeemed</div>
                                </div>
                            </div>
                        </div>
                    `;

                    $('#customerDetailsContent').html(html);
                    const modal = new bootstrap.Modal(document.getElementById('customerDetailsModal'));
                    modal.show();
                }
            },
            error: function() {
                alert('Error loading customer details');
            }
        });
    }

    function applyFilters() {
        loadCustomerCredits();
    }

    function resetFilters() {
        $('#statusFilter').val('');
        $('#searchInput').val('');
        $('#dateRange').val('');
        loadStoreCreditsData();
    }

    function exportStoreCredits() {
        const status = $('#statusFilter').val();
        const search = $('#searchInput').val();
        const dateRange = $('#dateRange').val();

        $.ajax({
            url: '/api/store-credits/export',
            method: 'POST',
            data: {
                status: status,
                search: search,
                date_range: dateRange
            },
            success: function(response) {
                if (response.success) {
                    // Create and download CSV
                    const csv = response.csv;
                    const blob = new Blob([csv], { type: 'text/csv' });
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'store-credits-' + new Date().toISOString().split('T')[0] + '.csv';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                }
            },
            error: function() {
                alert('Error exporting store credits');
            }
        });
    }

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    function showError(msg) {
        alert('Error: ' + msg);
    }
</script>

<?php require_once __DIR__ . '/_footer.php'; ?>
