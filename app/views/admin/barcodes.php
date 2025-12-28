<?php
$pageTitle = $title ?? 'Barcode Management';
require_once __DIR__ . '/_header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-3">🏷️ Barcode Management</h2>
            <p class="text-muted">Generate and print product barcodes</p>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0">Barcode Generator</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Barcode Type</label>
                        <select class="form-select" id="barcodeType">
                            <option value="code128">Code 128</option>
                            <option value="ean13">EAN-13</option>
                            <option value="upca">UPC-A</option>
                            <option value="code39">Code 39</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Label Size</label>
                        <select class="form-select" id="labelSize">
                            <option value="small">Small (40x20mm)</option>
                            <option value="medium" selected>Medium (50x25mm)</option>
                            <option value="large">Large (70x35mm)</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="showProductName" checked>
                        <label class="form-check-label" for="showProductName">
                            Show Product Name
                        </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="showPrice" checked>
                        <label class="form-check-label" for="showPrice">
                            Show Price
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0">Select Products</h5>
                </div>
                <div class="col-md-6 text-end">
                    <button class="btn btn-success me-2" onclick="printSelectedBarcodes()">
                        🖨️ Print Selected
                    </button>
                    <button class="btn btn-primary" onclick="printAllBarcodes()">
                        🖨️ Print All
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                            </th>
                            <th>Product Name</th>
                            <th>SKU</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <p class="text-muted mb-0">No products found</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="product-checkbox" value="<?php echo $product['product_id']; ?>" 
                                               data-name="<?php echo htmlspecialchars($product['product_name']); ?>"
                                               data-sku="<?php echo htmlspecialchars($product['sku'] ?? 'N/A'); ?>"
                                               data-price="<?php echo number_format($product['regular_price'] ?? 0, 2); ?>">
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($product['product_name']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['sku'] ?? 'N/A'); ?></td>
                                    <td>₹<?php echo number_format($product['regular_price'] ?? 0, 2); ?></td>
                                    <td><?php echo number_format($product['stock_quantity'] ?? 0); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="generateSingleBarcode(<?php echo $product['product_id']; ?>, '<?php echo htmlspecialchars($product['product_name']); ?>', '<?php echo htmlspecialchars($product['sku'] ?? 'N/A'); ?>', '<?php echo number_format($product['regular_price'] ?? 0, 2); ?>')">
                                            Generate
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary" onclick="printSingleBarcode(<?php echo $product['product_id']; ?>, '<?php echo htmlspecialchars($product['product_name']); ?>', '<?php echo htmlspecialchars($product['sku'] ?? 'N/A'); ?>', '<?php echo number_format($product['regular_price'] ?? 0, 2); ?>')">
                                            Print
                                        </button>
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

<div class="modal fade" id="barcodePreviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Barcode Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" id="barcodePreviewContent">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="window.print()">Print</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

<script>
let previewModal;

document.addEventListener('DOMContentLoaded', function() {
    previewModal = new bootstrap.Modal(document.getElementById('barcodePreviewModal'));
});

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.product-checkbox');
    checkboxes.forEach(cb => cb.checked = selectAll.checked);
}

function generateSingleBarcode(productId, productName, sku, price) {
    const barcodeType = document.getElementById('barcodeType').value;
    const showName = document.getElementById('showProductName').checked;
    const showPrice = document.getElementById('showPrice').checked;
    
    let barcodeValue = sku !== 'N/A' ? sku : `PROD${productId.toString().padStart(8, '0')}`;
    
    if (barcodeType === 'ean13' && barcodeValue.length !== 13) {
        barcodeValue = barcodeValue.substring(0, 12).padEnd(12, '0') + '0';
    }
    
    const barcodeHtml = `
        <div class="barcode-label" style="display: inline-block; padding: 20px; border: 1px dashed #ccc; margin: 10px;">
            <svg id="barcode-${productId}"></svg>
            ${showName ? `<div style="margin-top: 5px; font-size: 12px;">${productName}</div>` : ''}
            ${showPrice ? `<div style="margin-top: 3px; font-size: 14px; font-weight: bold;">₹${price}</div>` : ''}
        </div>
    `;
    
    document.getElementById('barcodePreviewContent').innerHTML = barcodeHtml;
    
    try {
        JsBarcode(`#barcode-${productId}`, barcodeValue, {
            format: barcodeType.toUpperCase(),
            width: 2,
            height: 50,
            displayValue: true
        });
    } catch (e) {
        alert('Error generating barcode: ' + e.message);
        return;
    }
    
    previewModal.show();
}

function printSingleBarcode(productId, productName, sku, price) {
    generateSingleBarcode(productId, productName, sku, price);
}

function printSelectedBarcodes() {
    const selected = document.querySelectorAll('.product-checkbox:checked');
    
    if (selected.length === 0) {
        alert('Please select at least one product');
        return;
    }
    
    const barcodeType = document.getElementById('barcodeType').value;
    const showName = document.getElementById('showProductName').checked;
    const showPrice = document.getElementById('showPrice').checked;
    
    let barcodeHtml = '<div style="display: flex; flex-wrap: wrap; justify-content: center;">';
    
    selected.forEach((checkbox, index) => {
        const productId = checkbox.value;
        const productName = checkbox.dataset.name;
        const sku = checkbox.dataset.sku;
        const price = checkbox.dataset.price;
        
        let barcodeValue = sku !== 'N/A' ? sku : `PROD${productId.toString().padStart(8, '0')}`;
        
        if (barcodeType === 'ean13' && barcodeValue.length !== 13) {
            barcodeValue = barcodeValue.substring(0, 12).padEnd(12, '0') + '0';
        }
        
        barcodeHtml += `
            <div class="barcode-label" style="display: inline-block; padding: 15px; border: 1px dashed #ccc; margin: 10px; text-align: center;">
                <svg id="barcode-multi-${index}"></svg>
                ${showName ? `<div style="margin-top: 5px; font-size: 11px;">${productName}</div>` : ''}
                ${showPrice ? `<div style="margin-top: 3px; font-size: 13px; font-weight: bold;">₹${price}</div>` : ''}
            </div>
        `;
    });
    
    barcodeHtml += '</div>';
    
    document.getElementById('barcodePreviewContent').innerHTML = barcodeHtml;
    
    selected.forEach((checkbox, index) => {
        const sku = checkbox.dataset.sku;
        const productId = checkbox.value;
        let barcodeValue = sku !== 'N/A' ? sku : `PROD${productId.toString().padStart(8, '0')}`;
        
        if (barcodeType === 'ean13' && barcodeValue.length !== 13) {
            barcodeValue = barcodeValue.substring(0, 12).padEnd(12, '0') + '0';
        }
        
        try {
            JsBarcode(`#barcode-multi-${index}`, barcodeValue, {
                format: barcodeType.toUpperCase(),
                width: 2,
                height: 50,
                displayValue: true
            });
        } catch (e) {
            console.error('Error generating barcode:', e);
        }
    });
    
    previewModal.show();
}

function printAllBarcodes() {
    const allCheckboxes = document.querySelectorAll('.product-checkbox');
    allCheckboxes.forEach(cb => cb.checked = true);
    printSelectedBarcodes();
}
</script>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    #barcodePreviewContent, #barcodePreviewContent * {
        visibility: visible;
    }
    #barcodePreviewContent {
        position: absolute;
        left: 0;
        top: 0;
    }
}
</style>

<?php require_once __DIR__ . '/_footer.php'; ?>
