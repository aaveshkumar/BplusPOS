<?php
$pageTitle = $title ?? 'Reports';
require_once __DIR__ . '/_header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-3">📊 Comprehensive Reports Dashboard</h2>
            <p class="text-muted">Generate and export detailed business reports</p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-primary">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-chart-line text-primary"></i> Sales Reports</h5>
                    <p class="card-text text-muted">Daily, weekly, monthly sales analysis with trends</p>
                    <button class="btn btn-primary btn-sm" onclick="generateReport('sales')">
                        <i class="fas fa-file-pdf"></i> Generate
                    </button>
                    <button class="btn btn-outline-primary btn-sm" onclick="exportReport('sales', 'excel')">
                        <i class="fas fa-file-excel"></i> Export
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-success">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-box text-success"></i> Inventory Reports</h5>
                    <p class="card-text text-muted">Stock levels, valuations, and movement history</p>
                    <button class="btn btn-success btn-sm" onclick="generateReport('inventory')">
                        <i class="fas fa-file-pdf"></i> Generate
                    </button>
                    <button class="btn btn-outline-success btn-sm" onclick="exportReport('inventory', 'excel')">
                        <i class="fas fa-file-excel"></i> Export
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-info">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-users text-info"></i> Customer Reports</h5>
                    <p class="card-text text-muted">Customer analysis, loyalty, and purchase patterns</p>
                    <button class="btn btn-info btn-sm" onclick="generateReport('customer')">
                        <i class="fas fa-file-pdf"></i> Generate
                    </button>
                    <button class="btn btn-outline-info btn-sm" onclick="exportReport('customer', 'excel')">
                        <i class="fas fa-file-excel"></i> Export
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-warning">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-money-bill text-warning"></i> Payment Reports</h5>
                    <p class="card-text text-muted">Payment methods breakdown and reconciliation</p>
                    <button class="btn btn-warning btn-sm" onclick="generateReport('payment')">
                        <i class="fas fa-file-pdf"></i> Generate
                    </button>
                    <button class="btn btn-outline-warning btn-sm" onclick="exportReport('payment', 'excel')">
                        <i class="fas fa-file-excel"></i> Export
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-danger">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-file-invoice text-danger"></i> Tax Reports</h5>
                    <p class="card-text text-muted">GST, VAT, and other tax calculations</p>
                    <button class="btn btn-danger btn-sm" onclick="generateReport('tax')">
                        <i class="fas fa-file-pdf"></i> Generate
                    </button>
                    <button class="btn btn-outline-danger btn-sm" onclick="exportReport('tax', 'excel')">
                        <i class="fas fa-file-excel"></i> Export
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-secondary">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-undo text-secondary"></i> Returns Reports</h5>
                    <p class="card-text text-muted">Returns, exchanges, and refund analysis</p>
                    <button class="btn btn-secondary btn-sm" onclick="generateReport('returns')">
                        <i class="fas fa-file-pdf"></i> Generate
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="exportReport('returns', 'excel')">
                        <i class="fas fa-file-excel"></i> Export
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">Custom Report Builder</h5>
                </div>
                <div class="card-body">
                    <form id="customReportForm">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Report Type</label>
                                <select class="form-control" id="reportType">
                                    <option value="sales">Sales</option>
                                    <option value="inventory">Inventory</option>
                                    <option value="customer">Customer</option>
                                    <option value="tax">Tax</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Date Range</label>
                                <select class="form-control" id="dateRange">
                                    <option value="today">Today</option>
                                    <option value="yesterday">Yesterday</option>
                                    <option value="last7">Last 7 Days</option>
                                    <option value="last30">Last 30 Days</option>
                                    <option value="this_month">This Month</option>
                                    <option value="last_month">Last Month</option>
                                    <option value="custom">Custom Range</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="startDate">
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label">End Date</label>
                                <input type="date" class="form-control" id="endDate">
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label">Export Format</label>
                                <select class="form-control" id="exportFormat">
                                    <option value="pdf">PDF</option>
                                    <option value="excel">Excel</option>
                                    <option value="csv">CSV</option>
                                </select>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="generateCustomReport()">
                            <i class="fas fa-play"></i> Generate Report
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="scheduleReport()">
                            <i class="fas fa-clock"></i> Schedule Report
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function generateReport(type) {
    alert(`Generating ${type} report...\n\nThis would:\n- Query relevant data from database\n- Format into PDF report\n- Include charts and graphs\n- Provide download link`);
}

function exportReport(type, format) {
    alert(`Exporting ${type} report as ${format.toUpperCase()}...\n\nFile would be downloaded automatically.`);
}

function generateCustomReport() {
    const reportType = $('#reportType').val();
    const dateRange = $('#dateRange').val();
    const format = $('#exportFormat').val();
    
    alert(`Generating custom report:\nType: ${reportType}\nRange: ${dateRange}\nFormat: ${format.toUpperCase()}\n\nReport would be generated and downloaded.`);
}

function scheduleReport() {
    alert('Report scheduling feature\n\nThis would allow you to:\n- Set recurring reports (daily/weekly/monthly)\n- Email delivery\n- Automated generation');
}
</script>

<?php require_once __DIR__ . '/_footer.php'; ?>
