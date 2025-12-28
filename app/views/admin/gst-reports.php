<?php
$pageTitle = $title ?? 'GST Reports';
require_once __DIR__ . '/_header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="mb-3">📋 GST Reports & Compliance</h2>
            <p class="text-muted">Generate GST returns, reports, and tax compliance documents</p>
        </div>
        <div class="col-md-6 text-end">
            <button class="btn btn-success" onclick="generateGSTR1()">
                <i class="fas fa-file-invoice"></i> Generate GSTR-1
            </button>
            <button class="btn btn-primary" onclick="generateGSTR3B()">
                <i class="fas fa-file-alt"></i> Generate GSTR-3B
            </button>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-primary">
                <div class="card-body">
                    <h5 class="text-muted mb-2">Total GST Collected</h5>
                    <h2 class="mb-0 text-primary">₹24,500</h2>
                    <small class="text-muted">This Month</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-success">
                <div class="card-body">
                    <h5 class="text-muted mb-2">CGST (9%)</h5>
                    <h2 class="mb-0 text-success">₹12,250</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-info">
                <div class="card-body">
                    <h5 class="text-muted mb-2">SGST (9%)</h5>
                    <h2 class="mb-0 text-info">₹12,250</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-warning">
                <div class="card-body">
                    <h5 class="text-muted mb-2">IGST (18%)</h5>
                    <h2 class="mb-0 text-warning">₹0</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">GST Summary by Tax Rate</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tax Rate</th>
                                    <th>Taxable Amount</th>
                                    <th>CGST</th>
                                    <th>SGST</th>
                                    <th>IGST</th>
                                    <th>Total GST</th>
                                    <th>Total Invoice Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>0%</strong></td>
                                    <td>₹10,000</td>
                                    <td>₹0</td>
                                    <td>₹0</td>
                                    <td>₹0</td>
                                    <td>₹0</td>
                                    <td>₹10,000</td>
                                </tr>
                                <tr>
                                    <td><strong>5%</strong></td>
                                    <td>₹20,000</td>
                                    <td>₹500</td>
                                    <td>₹500</td>
                                    <td>₹0</td>
                                    <td>₹1,000</td>
                                    <td>₹21,000</td>
                                </tr>
                                <tr>
                                    <td><strong>12%</strong></td>
                                    <td>₹50,000</td>
                                    <td>₹3,000</td>
                                    <td>₹3,000</td>
                                    <td>₹0</td>
                                    <td>₹6,000</td>
                                    <td>₹56,000</td>
                                </tr>
                                <tr>
                                    <td><strong>18%</strong></td>
                                    <td>₹1,50,000</td>
                                    <td>₹13,500</td>
                                    <td>₹13,500</td>
                                    <td>₹0</td>
                                    <td>₹27,000</td>
                                    <td>₹1,77,000</td>
                                </tr>
                                <tr>
                                    <td><strong>28%</strong></td>
                                    <td>₹30,000</td>
                                    <td>₹4,200</td>
                                    <td>₹4,200</td>
                                    <td>₹0</td>
                                    <td>₹8,400</td>
                                    <td>₹38,400</td>
                                </tr>
                                <tr class="table-active">
                                    <td><strong>Total</strong></td>
                                    <td><strong>₹2,60,000</strong></td>
                                    <td><strong>₹21,200</strong></td>
                                    <td><strong>₹21,200</strong></td>
                                    <td><strong>₹0</strong></td>
                                    <td><strong>₹42,400</strong></td>
                                    <td><strong>₹3,02,400</strong></td>
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
                    <h5 class="mb-0">Generate GST Returns</h5>
                </div>
                <div class="card-body">
                    <form id="gstReturnForm">
                        <div class="mb-3">
                            <label class="form-label">Return Type</label>
                            <select class="form-control" id="returnType">
                                <option value="gstr1">GSTR-1 (Outward Supplies)</option>
                                <option value="gstr2">GSTR-2 (Inward Supplies)</option>
                                <option value="gstr3b">GSTR-3B (Summary Return)</option>
                                <option value="gstr9">GSTR-9 (Annual Return)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Month/Period</label>
                            <input type="month" class="form-control" id="gstPeriod">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Format</label>
                            <select class="form-control" id="gstFormat">
                                <option value="pdf">PDF</option>
                                <option value="excel">Excel</option>
                                <option value="json">JSON (for GSTN portal)</option>
                            </select>
                        </div>
                        <button type="button" class="btn btn-primary w-100" onclick="generateGSTReturn()">
                            <i class="fas fa-download"></i> Generate & Download
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">Quick GST Tools</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary" onclick="calculateGST()">
                            <i class="fas fa-calculator"></i> GST Calculator
                        </button>
                        <button class="btn btn-outline-success" onclick="validateGSTIN()">
                            <i class="fas fa-check-circle"></i> GSTIN Validator
                        </button>
                        <button class="btn btn-outline-info" onclick="hsnCodeLookup()">
                            <i class="fas fa-search"></i> HSN/SAC Code Lookup
                        </button>
                        <button class="btn btn-outline-warning" onclick="reconcileGST()">
                            <i class="fas fa-balance-scale"></i> Reconcile with GSTN
                        </button>
                        <button class="btn btn-outline-danger" onclick="exportForGSTN()">
                            <i class="fas fa-upload"></i> Export for GSTN Portal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function generateGSTR1() {
    alert('Generating GSTR-1 Report\n\nThis includes:\n- B2B Invoices\n- B2C Large Invoices\n- Exports\n- Credit/Debit Notes\n- HSN Summary');
}

function generateGSTR3B() {
    alert('Generating GSTR-3B Report\n\nThis includes:\n- Outward Taxable Supplies\n- Inward Supplies\n- ITC Claimed\n- Tax Payment Details');
}

function generateGSTReturn() {
    const type = $('#returnType option:selected').text();
    const period = $('#gstPeriod').val();
    const format = $('#gstFormat').val().toUpperCase();
    
    alert(`Generating ${type}\nPeriod: ${period}\nFormat: ${format}\n\nReport would be downloaded automatically.`);
}

function calculateGST() {
    const amount = prompt('Enter amount (excluding GST):');
    if (amount) {
        const gst = parseFloat(amount) * 0.18;
        const total = parseFloat(amount) + gst;
        alert(`Amount: ₹${amount}\nGST (18%): ₹${gst.toFixed(2)}\nTotal: ₹${total.toFixed(2)}`);
    }
}

function validateGSTIN() {
    const gstin = prompt('Enter GSTIN to validate:');
    if (gstin) {
        alert(`Validating GSTIN: ${gstin}\n\nFormat check would be performed and business details fetched from GSTN portal.`);
    }
}

function hsnCodeLookup() {
    const search = prompt('Enter product name or category:');
    if (search) {
        alert(`Searching HSN codes for: ${search}\n\nWould show relevant HSN/SAC codes with tax rates.`);
    }
}

function reconcileGST() {
    alert('GST Reconciliation Tool\n\nThis would:\n- Compare your records with GSTN portal\n- Identify mismatches\n- Suggest corrections');
}

function exportForGSTN() {
    alert('Export for GSTN Portal\n\nGenerating JSON file compatible with GSTN portal upload.\n\nFile would be downloaded automatically.');
}
</script>

<?php require_once __DIR__ . '/_footer.php'; ?>
