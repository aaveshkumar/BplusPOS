<?php
$title = 'Receipt Customization';
include __DIR__ . '/_header.php';
?>

<div class="admin-content">
    <div class="admin-header">
        <h1><i class="fas fa-receipt"></i> Receipt Customization</h1>
        <p>Customize your receipt templates and branding</p>
    </div>

    <!-- Receipt Preview -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="content-card">
                <div class="content-card-header">
                    <h5 class="content-card-title">Live Preview</h5>
                </div>
                <div id="receiptPreview" style="background: #f5f5f5; padding: 20px; font-family: 'Courier New', monospace; font-size: 12px;">
                    <!-- Preview will be generated here -->
                    <div style="text-align: center; border-bottom: 2px dashed #000; padding-bottom: 10px; margin-bottom: 10px;">
                        <div id="preview-logo" style="margin-bottom: 10px;"></div>
                        <div id="preview-store-name" style="font-size: 18px; font-weight: bold;">B-Plus POS</div>
                        <div id="preview-address" style="font-size: 10px; line-height: 1.4; margin-top: 5px;"></div>
                    </div>
                    <div style="font-size: 11px; margin-bottom: 10px;">
                        <div>Receipt #: <strong>POS-12345</strong></div>
                        <div>Date: <?php echo date('d-M-Y h:i A'); ?></div>
                        <div>Cashier: Demo User</div>
                    </div>
                    <div style="text-align: center; margin-top: 15px; font-size: 10px;">
                        <div id="preview-footer-message" style="font-style: italic;"></div>
                        <div style="margin-top: 10px; font-weight: bold;">Thank you for your business!</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Store Information -->
            <div class="content-card mb-4">
                <div class="content-card-header">
                    <h5 class="content-card-title"><i class="fas fa-store"></i> Store Information</h5>
                </div>
                <form id="receiptSettingsForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Store Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="storeName" name="store_name" required placeholder="Enter store name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="phone" name="phone" placeholder="e.g., +91 98765 43210">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="store@example.com">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">GSTIN / Tax ID</label>
                            <input type="text" class="form-control" id="gstin" name="gstin" placeholder="e.g., 27AABCU9603R1ZV">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3" placeholder="Enter complete store address"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Logo URL</label>
                        <input type="url" class="form-control" id="logoUrl" name="logo_url" placeholder="https://example.com/logo.png">
                        <small class="text-muted">Enter the URL of your store logo image</small>
                    </div>
                </form>
            </div>

            <!-- Receipt Customization -->
            <div class="content-card mb-4">
                <div class="content-card-header">
                    <h5 class="content-card-title"><i class="fas fa-paint-brush"></i> Receipt Customization</h5>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Paper Size</label>
                        <select class="form-select" id="paperSize" name="paper_size">
                            <option value="80mm">80mm (Standard Thermal)</option>
                            <option value="58mm">58mm (Small Thermal)</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Auto Print</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" id="autoPrint" name="auto_print">
                            <label class="form-check-label" for="autoPrint">Automatically print receipt after checkout</label>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Footer Message</label>
                    <textarea class="form-control" id="footerMessage" name="footer_message" rows="2" placeholder="e.g., Visit us again!"></textarea>
                    <small class="text-muted">This message will appear at the bottom of the receipt</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Terms & Conditions</label>
                    <textarea class="form-control" id="terms" name="terms" rows="3" placeholder="Enter terms and conditions (optional)"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Additional Options</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="showBarcode" name="show_barcode">
                        <label class="form-check-label" for="showBarcode">
                            Show barcode/QR code on receipt
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="showTaxBreakdown" name="show_tax_breakdown" checked>
                        <label class="form-check-label" for="showTaxBreakdown">
                            Show detailed tax breakdown
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="showItemDiscount" name="show_item_discount" checked>
                        <label class="form-check-label" for="showItemDiscount">
                            Show item-level discounts
                        </label>
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="text-end">
                <button type="button" class="btn btn-secondary me-2" onclick="loadReceiptSettings()">
                    <i class="fas fa-undo"></i> Reset
                </button>
                <button type="button" class="btn btn-primary" onclick="saveReceiptSettings()">
                    <i class="fas fa-save"></i> Save Settings
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const csrfToken = '<?php echo generateCsrfToken(); ?>';

document.addEventListener('DOMContentLoaded', function() {
    loadReceiptSettings();
    
    // Add event listeners for live preview
    document.querySelectorAll('#receiptSettingsForm input, #receiptSettingsForm textarea, #receiptSettingsForm select').forEach(el => {
        el.addEventListener('input', updatePreview);
        el.addEventListener('change', updatePreview);
    });
});

async function loadReceiptSettings() {
    try {
        const response = await fetch('/api/receipt-settings');
        const data = await response.json();
        
        if (data.success && data.settings) {
            const settings = data.settings;
            document.getElementById('storeName').value = settings.store_name || '';
            document.getElementById('phone').value = settings.phone || '';
            document.getElementById('email').value = settings.email || '';
            document.getElementById('gstin').value = settings.gstin || '';
            document.getElementById('address').value = settings.address || '';
            document.getElementById('logoUrl').value = settings.logo_url || '';
            document.getElementById('paperSize').value = settings.paper_size || '80mm';
            document.getElementById('autoPrint').checked = settings.auto_print === true || settings.auto_print === 'true';
            document.getElementById('footerMessage').value = settings.footer_message || '';
            document.getElementById('terms').value = settings.terms || '';
            document.getElementById('showBarcode').checked = settings.show_barcode === true || settings.show_barcode === 'true';
            document.getElementById('showTaxBreakdown').checked = settings.show_tax_breakdown !== false && settings.show_tax_breakdown !== 'false';
            document.getElementById('showItemDiscount').checked = settings.show_item_discount !== false && settings.show_item_discount !== 'false';
            
            updatePreview();
        }
    } catch (error) {
        console.error('Error loading receipt settings:', error);
    }
}

async function saveReceiptSettings() {
    const formData = {
        store_name: document.getElementById('storeName').value,
        phone: document.getElementById('phone').value,
        email: document.getElementById('email').value,
        gstin: document.getElementById('gstin').value,
        address: document.getElementById('address').value,
        logo_url: document.getElementById('logoUrl').value,
        paper_size: document.getElementById('paperSize').value,
        auto_print: document.getElementById('autoPrint').checked,
        footer_message: document.getElementById('footerMessage').value,
        terms: document.getElementById('terms').value,
        show_barcode: document.getElementById('showBarcode').checked,
        show_tax_breakdown: document.getElementById('showTaxBreakdown').checked,
        show_item_discount: document.getElementById('showItemDiscount').checked,
        csrf_token: csrfToken
    };
    
    try {
        const response = await fetch('/api/receipt-settings', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Receipt settings saved successfully!');
        } else {
            alert('Error saving settings: ' + data.message);
        }
    } catch (error) {
        console.error('Error saving receipt settings:', error);
        alert('Error saving receipt settings. Please try again.');
    }
}

function updatePreview() {
    const storeName = document.getElementById('storeName').value || 'B-Plus POS';
    const address = document.getElementById('address').value;
    const phone = document.getElementById('phone').value;
    const email = document.getElementById('email').value;
    const gstin = document.getElementById('gstin').value;
    const logoUrl = document.getElementById('logoUrl').value;
    const footerMessage = document.getElementById('footerMessage').value;
    
    document.getElementById('preview-store-name').textContent = storeName;
    
    let addressHtml = '';
    if (address) addressHtml += address.replace(/\n/g, '<br>') + '<br>';
    if (phone) addressHtml += 'Tel: ' + phone + '<br>';
    if (email) addressHtml += 'Email: ' + email + '<br>';
    if (gstin) addressHtml += 'GSTIN: ' + gstin;
    document.getElementById('preview-address').innerHTML = addressHtml;
    
    if (logoUrl) {
        document.getElementById('preview-logo').innerHTML = '<img src="' + logoUrl + '" alt="Logo" style="max-width: 100px; max-height: 50px;">';
    } else {
        document.getElementById('preview-logo').innerHTML = '';
    }
    
    if (footerMessage) {
        document.getElementById('preview-footer-message').textContent = footerMessage;
    } else {
        document.getElementById('preview-footer-message').textContent = '';
    }
}
</script>

<?php include __DIR__ . '/_footer.php'; ?>
