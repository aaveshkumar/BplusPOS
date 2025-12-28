<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - <?php echo htmlspecialchars($order['order_number'] ?? $order['order_id']); ?></title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <style>
        /* Print Styles - Thermal Printer Optimized */
        @media print {
            body { 
                margin: 0; 
                padding: 0;
                background: white;
            }
            .no-print { display: none !important; }
            .receipt-container {
                box-shadow: none;
                margin: 0;
                padding: 5mm;
                background: white;
            }
            @page { 
                margin: 0; 
                size: 80mm auto; /* Auto height for continuous paper */
            }
            @page :first {
                margin-top: 0;
            }
        }
        
        /* Thermal 58mm */
        @media print and (max-width: 58mm) {
            @page { size: 58mm auto; }
            body { font-size: 9px; }
            .receipt-container { max-width: 58mm; }
            .store-name { font-size: 14px; }
            .items-table { font-size: 8px; }
        }
        
        /* Thermal 80mm */
        @media print and (min-width: 58mm) and (max-width: 80mm) {
            @page { size: 80mm auto; }
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', monospace;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .receipt-container {
            max-width: 80mm;
            margin: 0 auto;
            background: white;
            padding: 10mm;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .receipt-container.thermal-58 {
            max-width: 58mm;
        }
        
        .receipt-header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        
        .store-logo {
            max-width: 100px;
            margin-bottom: 10px;
        }
        
        .store-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .store-details {
            font-size: 10px;
            line-height: 1.4;
        }
        
        .receipt-info {
            font-size: 11px;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #000;
        }
        
        .receipt-info div {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        
        .items-table {
            width: 100%;
            font-size: 10px;
            margin-bottom: 10px;
            border-collapse: collapse;
        }
        
        .items-table th {
            text-align: left;
            border-bottom: 1px solid #000;
            padding: 5px 0;
        }
        
        .items-table td {
            padding: 5px 0;
            vertical-align: top;
        }
        
        .item-name {
            font-weight: bold;
        }
        
        .item-details {
            font-size: 9px;
            color: #666;
        }
        
        .text-right {
            text-align: right;
        }
        
        .totals {
            border-top: 1px dashed #000;
            padding-top: 10px;
            margin-bottom: 10px;
        }
        
        .totals div {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 11px;
        }
        
        .totals .grand-total {
            font-size: 14px;
            font-weight: bold;
            border-top: 2px solid #000;
            padding-top: 8px;
            margin-top: 8px;
        }
        
        .payment-info {
            font-size: 11px;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #000;
        }
        
        .footer {
            text-align: center;
            font-size: 10px;
            margin-top: 15px;
        }
        
        .footer-message {
            font-style: italic;
            margin-top: 10px;
        }
        
        .tax-summary {
            font-size: 9px;
            background: #f9f9f9;
            padding: 8px;
            margin-bottom: 10px;
        }
        
        .tax-summary div {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }
        
        .action-buttons {
            text-align: center;
            margin: 20px 0;
        }
        
        .action-buttons button {
            margin: 0 5px;
            padding: 10px 20px;
            font-size: 14px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            background: #667eea;
            color: white;
        }
        
        .action-buttons button:hover {
            background: #5568d3;
        }
        
        .split-payment-details {
            font-size: 10px;
            margin-bottom: 10px;
        }
        
        .split-payment-details div {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            padding-left: 10px;
        }
    </style>
</head>
<body>
    <?php
    $settings = $config['pos']['receipt'] ?? [];
    ?>
    <div class="receipt-container <?php echo ($settings['paper_size'] ?? '') === '58mm' ? 'thermal-58' : ''; ?>">
        <!-- Header -->
        <div class="receipt-header">
            <?php if (!empty($settings['logo_url'])): ?>
                <img src="<?php echo htmlspecialchars($settings['logo_url']); ?>" alt="Logo" class="store-logo">
            <?php endif; ?>
            
            <div class="store-name"><?php echo htmlspecialchars($settings['store_name'] ?? $config['app']['name'] ?? 'B-Plus POS'); ?></div>
            <div class="store-details">
                <?php if (!empty($settings['address'])): ?>
                    <?php echo nl2br(htmlspecialchars($settings['address'])); ?><br>
                <?php endif; ?>
                <?php if (!empty($settings['phone'])): ?>
                    Tel: <?php echo htmlspecialchars($settings['phone']); ?><br>
                <?php endif; ?>
                <?php if (!empty($settings['email'])): ?>
                    Email: <?php echo htmlspecialchars($settings['email']); ?><br>
                <?php endif; ?>
                <?php if (!empty($settings['gstin'])): ?>
                    GSTIN: <?php echo htmlspecialchars($settings['gstin']); ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Receipt Info -->
        <div class="receipt-info">
            <div>
                <span>Receipt #:</span>
                <strong><?php echo htmlspecialchars($order['order_number'] ?? $order['order_id']); ?></strong>
            </div>
            <div>
                <span>Date:</span>
                <span><?php echo date('d-M-Y h:i A', strtotime($order['order_date'] ?? 'now')); ?></span>
            </div>
            <?php if (!empty($order['customer_name'])): ?>
            <div>
                <span>Customer:</span>
                <span><?php echo htmlspecialchars($order['customer_name']); ?></span>
            </div>
            <?php endif; ?>
            <div>
                <span>Cashier:</span>
                <span><?php echo htmlspecialchars($order['cashier_name'] ?? getCurrentUser()['name'] ?? 'N/A'); ?></span>
            </div>
        </div>
        
        <!-- Items -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $taxBreakdown = [];
                if (!empty($items)): 
                    foreach ($items as $item): 
                        $quantity = (int)($item['quantity'] ?? 1);
                        
                        // Get MRP and pricing info
                        $regularPrice = (float)($item['regular_price'] ?? 0);
                        
                        // Use actual order data from WooCommerce
                        // line_subtotal = price before discount (excl tax)
                        // line_total = price after discount (excl tax)
                        // tax_amount = tax on discounted price
                        $itemTax = (float)($item['tax_amount'] ?? 0);
                        $lineSubtotal = (float)($item['line_subtotal'] ?? 0);
                        $lineTotal = (float)($item['total'] ?? 0);
                        
                        // Calculate selling price per unit (before coupon discount)
                        $unitPrice = ($quantity > 0) ? ($lineSubtotal / $quantity) : 0;
                        
                        // Determine MRP - use regular_price if available
                        $mrp = $regularPrice > 0 ? $regularPrice : $unitPrice;
                        
                        // Calculate MRP discount (MRP - selling price)
                        $mrpDiscount = ($mrp - $unitPrice) * $quantity;
                        
                        // Calculate coupon/cart discount (line_subtotal - line_total)
                        $couponDiscount = $lineSubtotal - $lineTotal;
                        
                        // Total discount shown
                        $totalDiscount = $mrpDiscount + $couponDiscount;
                        
                        // Final price (line_total after discount + tax)
                        $finalPrice = $lineTotal + $itemTax;
                        
                        // Calculate actual tax percentage from WooCommerce line data
                        // Use line_total (after discount) as the tax base, not line_subtotal
                        if ($itemTax > 0 && $lineTotal > 0) {
                            $taxPercent = ($itemTax / $lineTotal) * 100;
                        } else {
                            $taxPercent = 0;
                        }
                        
                        // Group tax by rate
                        $taxKey = number_format($taxPercent, 2);
                        if (!isset($taxBreakdown[$taxKey])) {
                            $taxBreakdown[$taxKey] = 0;
                        }
                        $taxBreakdown[$taxKey] += $itemTax;
                ?>
                <tr>
                    <td>
                        <div class="item-name"><?php echo htmlspecialchars($item['product_name'] ?? $item['name']); ?></div>
                        <div class="item-details">
                            MRP: ₹<?php echo number_format($mrp, 2); ?> × <?php echo $quantity; ?><br>
                            <?php if ($totalDiscount > 0): ?>
                                Discount: ₹<?php echo number_format($totalDiscount, 2); ?><br>
                            <?php endif; ?>
                            <?php if ($itemTax > 0): ?>
                                Tax @ <?php echo number_format($taxPercent, 2); ?>%: ₹<?php echo number_format($itemTax, 2); ?><br>
                            <?php endif; ?>
                            Final: ₹<?php echo number_format($finalPrice, 2); ?>
                        </div>
                    </td>
                    <td class="text-right"><?php echo $quantity; ?></td>
                    <td class="text-right">₹<?php echo number_format($unitPrice, 2); ?></td>
                    <td class="text-right">₹<?php echo number_format($finalPrice, 2); ?></td>
                </tr>
                <?php 
                    endforeach; 
                endif; 
                ?>
            </tbody>
        </table>
        
        <!-- Tax Summary -->
        <?php if (!empty($taxBreakdown)): ?>
        <div class="tax-summary">
            <strong>Tax Breakdown:</strong>
            <?php foreach ($taxBreakdown as $rate => $amount): ?>
            <div>
                <span>GST @ <?php echo $rate; ?>%:</span>
                <span>₹<?php echo number_format($amount, 2); ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <!-- Totals -->
        <div class="totals">
            <div>
                <span>Subtotal:</span>
                <span>₹<?php echo number_format($order['subtotal'] ?? $order['total'], 2); ?></span>
            </div>
            <?php if (($order['discount_amount'] ?? 0) > 0): ?>
            <div>
                <span>Discount:</span>
                <span>-₹<?php echo number_format($order['discount_amount'], 2); ?></span>
            </div>
            <?php endif; ?>
            <div>
                <span>Tax:</span>
                <span>₹<?php echo number_format($order['tax_amount'] ?? $order['tax'] ?? 0, 2); ?></span>
            </div>
            <div class="grand-total">
                <span>TOTAL:</span>
                <span>₹<?php echo number_format($order['total'], 2); ?></span>
            </div>
        </div>
        
        <!-- Payment Info -->
        <div class="payment-info">
            <strong>Payment Method: <?php echo strtoupper(htmlspecialchars($order['payment_method'] ?? 'CASH')); ?></strong>
            
            <?php if (!empty($order['split_payments'])): ?>
            <div class="split-payment-details">
                <?php foreach ($order['split_payments'] as $payment): ?>
                <div>
                    <span><?php echo ucfirst(htmlspecialchars($payment['method'])); ?>:</span>
                    <span>₹<?php echo number_format($payment['amount'], 2); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <?php if (!empty($settings['footer_message'])): ?>
            <div class="footer-message">
                <?php echo nl2br(htmlspecialchars($settings['footer_message'])); ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($settings['terms'])): ?>
            <div style="margin-top: 10px; font-size: 8px;">
                <?php echo nl2br(htmlspecialchars($settings['terms'])); ?>
            </div>
            <?php endif; ?>
            
            <div style="margin-top: 15px; font-weight: bold;">
                Thank you for your business!
            </div>
            
            <div style="margin-top: 5px; font-size: 8px;">
                Powered by B-Plus POS
            </div>
        </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="action-buttons no-print">
        <button onclick="window.print()" style="background: #10b981;">🖨️ Print Receipt</button>
        <button onclick="shareWhatsApp()" style="background: #25d366;">📱 Share on WhatsApp</button>
        <button onclick="emailReceipt()" style="background: #3b82f6;">📧 Email Receipt</button>
        <button onclick="downloadPDF()" style="background: #f59e0b;">📄 Save as PDF</button>
        <button onclick="window.close()" style="background: #6b7280;">✕ Close</button>
    </div>
    
    <!-- Print Settings Help -->
    <div class="no-print" style="max-width: 80mm; margin: 20px auto; padding: 15px; background: #f0f9ff; border-radius: 8px; font-size: 12px;">
        <strong>💡 Print Tips:</strong>
        <ul style="margin: 10px 0; padding-left: 20px;">
            <li><strong>Thermal Printer (58mm/80mm):</strong> Select your printer and use default settings</li>
            <li><strong>Regular Printer (A4):</strong> Enable "Fit to page" in print settings</li>
            <li><strong>Save as PDF:</strong> Click "Save as PDF" button and select "Save as PDF" in print dialog</li>
            <li><strong>Shortcuts:</strong> Press Ctrl+P (Windows) or Cmd+P (Mac) to print, ESC to close</li>
        </ul>
    </div>
    
    <script>
        // Auto-print on load if requested
        window.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const autoPrint = urlParams.get('auto_print');
            
            if (autoPrint === '1' || autoPrint === 'true') {
                // Small delay to ensure page is fully rendered
                setTimeout(function() {
                    window.print();
                }, 500);
            }
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl+P or Cmd+P for print
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
            // Escape to close
            if (e.key === 'Escape') {
                window.close();
            }
        });
        
        // Share receipt via WhatsApp
        function shareWhatsApp() {
            const phone = prompt('Enter customer WhatsApp number (with country code, e.g., 919876543210):');
            if (!phone) return;
            
            // Remove any non-digit characters
            const cleanPhone = phone.replace(/\D/g, '');
            
            if (cleanPhone.length < 10) {
                alert('Please enter a valid phone number');
                return;
            }
            
            // Create receipt message
            const orderNumber = '<?php echo htmlspecialchars($order['order_number'] ?? $order['order_id']); ?>';
            const orderDate = '<?php echo date('d-M-Y h:i A', strtotime($order['order_date'] ?? 'now')); ?>';
            const storeName = '<?php echo addslashes($settings['store_name'] ?? $config['app']['name'] ?? 'B-Plus POS'); ?>';
            const total = '<?php echo number_format($order['total'], 2); ?>';
            const customerName = '<?php echo addslashes($order['customer_name'] ?? ''); ?>';
            
            let message = `*${storeName}*\n`;
            message += `Invoice/Receipt\n\n`;
            message += `📄 Receipt #: *${orderNumber}*\n`;
            message += `📅 Date: ${orderDate}\n`;
            if (customerName) {
                message += `👤 Customer: ${customerName}\n`;
            }
            message += `\n*Items Purchased:*\n`;
            
            <?php if (!empty($items)): 
                foreach ($items as $index => $item): 
                    $quantity = (int)($item['quantity'] ?? 1);
                    $itemTax = (float)($item['tax_amount'] ?? 0);
                    $lineTotal = (float)($item['total'] ?? 0);
                    $finalPrice = $lineTotal + $itemTax;
            ?>
            message += `${<?php echo $index + 1; ?>}. <?php echo addslashes($item['product_name'] ?? $item['name']); ?>\n`;
            message += `   Qty: <?php echo $quantity; ?> × ₹<?php echo number_format($finalPrice / $quantity, 2); ?> = ₹<?php echo number_format($finalPrice, 2); ?>\n`;
            <?php endforeach; endif; ?>
            
            message += `\n*Summary:*\n`;
            message += `Subtotal: ₹<?php echo number_format($order['subtotal'] ?? $order['total'], 2); ?>\n`;
            <?php if (($order['discount_amount'] ?? 0) > 0): ?>
            message += `Discount: -₹<?php echo number_format($order['discount_amount'], 2); ?>\n`;
            <?php endif; ?>
            message += `Tax: ₹<?php echo number_format($order['tax_amount'] ?? $order['tax'] ?? 0, 2); ?>\n`;
            message += `*TOTAL: ₹${total}*\n\n`;
            message += `Payment: <?php echo strtoupper(htmlspecialchars($order['payment_method'] ?? 'CASH')); ?>\n\n`;
            message += `Thank you for your business! 🙏\n`;
            message += `Powered by B-Plus POS`;
            
            // Encode message for URL
            const encodedMessage = encodeURIComponent(message);
            
            // Create WhatsApp URL
            const whatsappURL = `https://wa.me/${cleanPhone}?text=${encodedMessage}`;
            
            // Open WhatsApp
            window.open(whatsappURL, '_blank');
        }
        
        // Email receipt function
        function emailReceipt() {
            const email = prompt('Enter customer email address:');
            if (email && validateEmail(email)) {
                const button = event.target;
                button.disabled = true;
                button.textContent = '📧 Sending...';
                
                fetch('/pos/email-receipt', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        order_id: <?php echo $order['id'] ?? $order['order_id']; ?>,
                        email: email
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Receipt sent successfully to ' + email);
                    } else {
                        alert('❌ Failed to send receipt: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('❌ Error sending receipt. Please try again.');
                })
                .finally(() => {
                    button.disabled = false;
                    button.textContent = '📧 Email Receipt';
                });
            } else if (email) {
                alert('Please enter a valid email address');
            }
        }
        
        // Email validation
        function validateEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }
        
        // Print with options
        function printReceipt() {
            window.print();
        }
        
        // Download as PDF (using print dialog)
        function downloadPDF() {
            alert('Use your browser\'s Print dialog and select "Save as PDF" as the destination.');
            window.print();
        }
    </script>
</body>
</html>
