<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>B-Plus POS - Point of Sale</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f7fa;
            overflow: hidden;
        }

        /* Professional POS Header */
        .pos-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 20px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.15);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            height: 60px;
        }

        .pos-header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 100%;
        }

        .pos-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .pos-brand i {
            font-size: 28px;
        }

        .pos-brand-text h1 {
            font-size: 20px;
            font-weight: 700;
            margin: 0;
            line-height: 1;
        }

        .pos-brand-text small {
            font-size: 11px;
            opacity: 0.9;
        }

        .pos-user-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .pos-user-info {
            background: rgba(255,255,255,0.15);
            padding: 6px 16px;
            border-radius: 25px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .pos-logout-btn {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            padding: 6px 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .pos-logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        /* Main Container */
        .pos-container {
            display: flex;
            height: calc(100vh - 60px);
            margin-top: 60px;
            overflow: hidden;
        }

        /* Left Side - Products */
        .products-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 12px 12px;
            overflow: hidden;
            max-width: calc(100vw - 420px);
        }

        /* Search & Filter Bar */
        .search-filter-bar {
            background: white;
            padding: 10px 12px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            margin-bottom: 8px;
        }

        .search-row {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 8px;
            margin-bottom: 8px;
        }

        .barcode-search {
            position: relative;
        }

        .barcode-search input {
            padding-left: 36px;
            height: 40px;
            font-size: 14px;
            border: 2px solid #e1e8ed;
            border-radius: 6px;
        }

        .barcode-search input:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }

        .barcode-search .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 18px;
        }

        .customer-search {
            position: relative;
        }

        .category-pills {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .category-pill {
            padding: 6px 12px;
            border: 1.5px solid #e1e8ed;
            background: white;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 11px;
            font-weight: 500;
        }

        .category-pill:hover, .category-pill.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        /* Products Grid */
        .products-grid-container {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding-right: 8px;
        }

        .products-grid-container::-webkit-scrollbar {
            width: 8px;
        }

        .products-grid-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .products-grid-container::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 8px;
            padding-bottom: 12px;
            padding-left: 4px;
            padding-right: 4px;
        }

        .product-card {
            background: white;
            border: 1.5px solid #e8e8e8;
            border-radius: 8px;
            padding: 10px;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(102,126,234,0.15);
            border-color: #667eea;
        }

        .product-card.on-sale::after {
            content: 'SALE';
            position: absolute;
            top: 8px;
            right: 8px;
            background: #ff4757;
            color: white;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
        }

        .product-name {
            font-weight: 600;
            font-size: 12px;
            color: #2c3e50;
            margin-bottom: 4px;
            line-height: 1.2;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-sku {
            font-size: 10px;
            color: #7f8c8d;
            margin-bottom: 6px;
        }

        .product-stock {
            display: flex;
            align-items: center;
            gap: 4px;
            margin-bottom: 6px;
            font-size: 10px;
        }

        .stock-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .stock-good { background: #2ecc71; }
        .stock-low { background: #f39c12; }
        .stock-out { background: #e74c3c; }

        .product-price {
            font-size: 14px;
            font-weight: 700;
            color: #27ae60;
        }

        .product-price-old {
            text-decoration: line-through;
            color: #95a5a6;
            font-size: 11px;
            margin-left: 4px;
        }

        .loading-spinner {
            text-align: center;
            padding: 20px;
            color: #999;
        }

        /* Right Sidebar - Cart */
        .cart-sidebar {
            width: 420px;
            background: white;
            border-left: 1px solid #e1e8ed;
            display: flex;
            flex-direction: column;
            box-shadow: -2px 0 15px rgba(0,0,0,0.05);
        }

        .cart-header {
            padding: 12px 16px;
            border-bottom: 2px solid #f0f0f0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .cart-header h2 {
            font-size: 16px;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .cart-badge {
            background: rgba(255,255,255,0.3);
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
        }

        /* Customer Info Section */
        .customer-info-box {
            padding: 10px 16px;
            background: #f8f9fa;
            border-bottom: 1px solid #e1e8ed;
        }

        .customer-info-box .selected-customer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: white;
            padding: 8px 12px;
            border-radius: 6px;
            border: 1.5px solid #e1e8ed;
        }

        .customer-info-box .customer-name {
            font-weight: 600;
            font-size: 13px;
            color: #2c3e50;
        }

        .customer-info-box .customer-email {
            font-size: 11px;
            color: #7f8c8d;
        }

        /* Rewards Section (Coupon & Loyalty Points) */
        .rewards-section {
            padding: 12px 16px;
            background: #f8f9fa;
            border-bottom: 1px solid #e1e8ed;
        }

        .coupon-box, .loyalty-box {
            background: white;
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 10px;
            border: 1px solid #e1e8ed;
        }

        .loyalty-box {
            margin-bottom: 0;
        }

        .coupon-input-group, .points-redeem-group {
            display: flex;
            gap: 6px;
            margin-top: 6px;
        }

        .coupon-input-group input, .points-redeem-group input {
            flex: 1;
            padding: 6px 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 12px;
        }

        .coupon-apply-btn, .redeem-points-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .coupon-apply-btn:hover, .redeem-points-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
        }

        .coupon-message, .points-message {
            margin-top: 6px;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 500;
        }

        .coupon-message.success, .points-message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .coupon-message.error, .points-message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .applied-coupon-box, .redeemed-points-box {
            margin-top: 8px;
            padding: 8px 10px;
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            border-radius: 6px;
            border: 1px solid #a5d6a7;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .applied-coupon-info, .redeemed-points-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex: 1;
            gap: 10px;
        }

        .coupon-label, .points-label {
            font-size: 11px;
            font-weight: 600;
            color: #2e7d32;
        }

        .coupon-savings, .points-savings {
            font-size: 13px;
            font-weight: 700;
            color: #1b5e20;
        }

        .remove-coupon-btn, .remove-points-btn {
            background: #ff5252;
            color: white;
            border: none;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            transition: all 0.2s;
        }

        .remove-coupon-btn:hover, .remove-points-btn:hover {
            background: #d32f2f;
            transform: scale(1.1);
        }

        .loyalty-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .points-balance {
            background: linear-gradient(135deg, #ffd54f 0%, #ffb300 100%);
            color: #000;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
        }

        /* Cart Items */
        .cart-items {
            flex: 1;
            overflow-y: auto;
            padding: 12px 16px;
            min-height: 0;
        }

        .cart-items::-webkit-scrollbar {
            width: 6px;
        }

        .cart-items::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }

        .cart-item-enhanced {
            background: #f8f9fa;
            border: 1.5px solid #e9ecef;
            border-radius: 8px;
            padding: 8px;
            margin-bottom: 8px;
            transition: all 0.2s;
        }

        .cart-item-enhanced:hover {
            border-color: #667eea;
            box-shadow: 0 2px 8px rgba(102,126,234,0.1);
        }

        .cart-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }

        .cart-item-name {
            font-weight: 600;
            font-size: 12px;
            color: #2c3e50;
            flex: 1;
            line-height: 1.3;
        }

        .cart-item-remove {
            color: #e74c3c;
            cursor: pointer;
            font-size: 16px;
            padding: 2px 4px;
            transition: all 0.2s;
            margin-left: 8px;
        }

        .cart-item-remove:hover {
            transform: scale(1.2);
        }

        /* Single Row Pricing Display */
        .cart-item-pricing-row {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 4px;
            background: white;
            padding: 6px;
            border-radius: 6px;
            margin-bottom: 6px;
            font-size: 10px;
        }

        .pricing-cell {
            text-align: center;
            padding: 2px;
        }

        .pricing-label {
            display: block;
            font-size: 9px;
            color: #7f8c8d;
            font-weight: 600;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .pricing-value {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: #2c3e50;
        }

        .pricing-cell.discount .pricing-value {
            color: #27ae60;
        }

        .pricing-cell.tax .pricing-value {
            color: #e67e22;
        }

        .pricing-cell.total .pricing-value {
            color: #667eea;
            font-size: 12px;
        }

        .cart-item-controls {
            display: flex;
            gap: 6px;
        }

        .quantity-section {
            flex: 1;
        }

        .quantity-section label,
        .discount-section-item label {
            display: block;
            font-size: 9px;
            color: #7f8c8d;
            margin-bottom: 2px;
        }

        .discount-section-item {
            flex: 1;
        }

        .item-discount-input {
            width: 100%;
            padding: 4px 6px;
            border: 1px solid #e1e8ed;
            border-radius: 4px;
            font-size: 11px;
        }

        .cart-item-total {
            text-align: right;
            padding-top: 6px;
            border-top: 2px solid #e1e8ed;
            font-size: 13px;
            color: #667eea;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 8px;
            background: white;
            padding: 4px;
            border-radius: 25px;
            border: 1.5px solid #e1e8ed;
        }

        .quantity-controls button {
            width: 28px;
            height: 28px;
            border: none;
            background: #667eea;
            color: white;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            transition: all 0.2s;
        }

        .quantity-controls button:hover {
            background: #5568d3;
        }

        .quantity-controls span {
            min-width: 35px;
            text-align: center;
            font-weight: 600;
            font-size: 14px;
        }

        .cart-item-price {
            font-weight: 700;
            color: #27ae60;
            font-size: 15px;
        }

        .cart-empty {
            text-align: center;
            padding: 60px 20px;
            color: #95a5a6;
        }

        .cart-empty i {
            font-size: 64px;
            margin-bottom: 16px;
            opacity: 0.3;
        }

        /* Cart Footer */
        .cart-footer {
            border-top: 2px solid #f0f0f0;
            padding: 10px 16px;
            background: #fafafa;
        }

        /* Row 1: Discount & Payment Method */
        .cart-row-1 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 8px;
        }

        .discount-section {
            margin-bottom: 0;
        }
        
        .cart-footer .form-label {
            margin-bottom: 4px !important;
            font-size: 11px;
            font-weight: 600;
            color: #555;
        }

        .discount-input-group {
            display: flex;
            gap: 6px;
        }

        .discount-input-group input {
            flex: 1;
            padding: 8px 10px;
            border: 1.5px solid #e1e8ed;
            border-radius: 6px;
            font-size: 13px;
        }

        .discount-input-group button {
            padding: 8px 14px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
            font-size: 12px;
        }

        .discount-input-group button:hover {
            background: #5568d3;
        }

        .payment-method-select {
            margin-bottom: 0;
        }

        .payment-method-select select {
            width: 100%;
            padding: 8px 10px;
            border: 1.5px solid #e1e8ed;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
        }

        /* Row 2: Totals Row */
        .cart-totals-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 6px;
            margin-bottom: 8px;
            background: #f8f9fa;
            padding: 8px 6px;
            border-radius: 6px;
            border: 1.5px solid #e1e8ed;
        }

        .total-item {
            text-align: center;
        }

        .total-label {
            display: block;
            font-size: 11px;
            color: #666;
            margin-bottom: 4px;
            font-weight: 600;
        }

        .total-value {
            display: block;
            font-size: 14px;
            font-weight: 700;
            color: #2c3e50;
        }

        .total-item.highlight {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 6px;
            padding: 4px;
            color: white;
        }

        .total-item.highlight .total-label {
            color: rgba(255,255,255,0.9);
        }

        .total-item.highlight .total-value {
            color: white;
            font-size: 16px;
        }

        /* Row 3: Payment Buttons */
        .cart-row-3 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 8px;
        }

        .checkout-btn-half {
            padding: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(102,126,234,0.3);
        }

        .checkout-btn-half:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102,126,234,0.4);
        }

        .checkout-btn-half:disabled {
            background: #ccc;
            cursor: not-allowed;
            box-shadow: none;
        }

        .split-btn-half {
            padding: 10px;
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }

        .split-btn-half:hover:not(:disabled) {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }

        .split-btn-half:disabled {
            border-color: #ccc;
            color: #ccc;
            cursor: not-allowed;
        }

        /* Old checkout-btn for compatibility */
        .checkout-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(102,126,234,0.3);
        }

        .checkout-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102,126,234,0.4);
        }

        .checkout-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            box-shadow: none;
        }
        
        #splitPaymentBtn {
            padding: 10px;
            font-size: 13px;
            margin-top: 8px;
            margin-bottom: 0 !important;
        }

        /* Row 4: Action Buttons */
        .action-buttons {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
        }

        .action-buttons button {
            padding: 10px 8px;
            background: white;
            border: 1.5px solid #e1e8ed;
            border-radius: 8px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.2s;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }

        .action-buttons button:hover {
            background: #f8f9fa;
            border-color: #667eea;
            color: #667eea;
            transform: translateY(-2px);
        }

        /* Customer option styling in dropdown */
        .customer-option {
            padding: 8px 0;
        }
        
        .customer-option-name {
            font-size: 14px;
            margin-bottom: 4px;
            color: #2c3e50;
        }
        
        .customer-option-detail {
            font-size: 12px;
            color: #7f8c8d;
            margin-left: 20px;
            margin-top: 2px;
        }
        
        .customer-option-detail i {
            width: 14px;
            margin-right: 4px;
        }

        /* Select2 Customization */
        .select2-container--bootstrap-5 .select2-selection {
            height: 48px !important;
            padding: 8px 14px !important;
            border: 2px solid #e1e8ed !important;
            border-radius: 8px !important;
        }

        .select2-container--bootstrap-5 .select2-selection__rendered {
            line-height: 30px !important;
        }

        .add-customer-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            white-space: nowrap;
        }

        .add-customer-btn:hover {
            background: #5568d3;
        }
    </style>
</head>
<body>
    <!-- POS Header -->
    <div class="pos-header">
        <div class="pos-header-content">
            <div class="pos-brand">
                <i class="fas fa-cash-register"></i>
                <div class="pos-brand-text">
                    <h1>B-Plus POS</h1>
                    <small>Point of Sale System</small>
                </div>
            </div>
            
            <!-- Selected Customer Details in Header -->
            <div id="headerCustomerSection" style="display: none; flex: 1; margin: 0 20px; padding: 8px 15px; background: rgba(255,255,255,0.1); border-radius: 6px; min-width: 250px;">
                <div style="font-size: 12px; color: #fff; line-height: 1.4;">
                    <strong id="headerCustomerName" style="font-size: 13px;">--</strong><br>
                    <small id="headerCustomerContact" style="opacity: 0.9;">--</small><br>
                    <small id="headerCustomerCredit" style="color: #ffd700;">Store Credit: ₹<span id="headerCreditAmount">0.00</span></small>
                </div>
            </div>
            
            <div class="pos-user-section">
                <button onclick="openCashierReturns()" class="pos-logout-btn" style="background: #f59e0b; margin-right: 8px;" title="Process Returns & Exchanges">
                    <i class="fas fa-undo"></i> Returns
                </button>
                <button onclick="showOrdersHistory()" class="pos-logout-btn" style="background: #3b82f6; margin-right: 12px;">
                    <i class="fas fa-receipt"></i> Orders
                </button>
                <div class="pos-user-info">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo htmlspecialchars(Session::get('username', 'Cashier')); ?></span>
                </div>
                <a href="/logout" class="pos-logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="pos-container">
        <!-- Products Section -->
        <div class="products-section">
            <!-- Search & Filter Bar -->
            <div class="search-filter-bar">
                <div class="search-row">
                    <div class="barcode-search">
                        <i class="fas fa-barcode search-icon"></i>
                        <input type="text" 
                               id="barcodeSearch" 
                               class="form-control" 
                               placeholder="Scan barcode or search product name, SKU..."
                               autocomplete="off">
                    </div>
                    <button class="add-customer-btn" onclick="showAddCustomerModal()">
                        <i class="fas fa-user-plus"></i> New Customer
                    </button>
                    <div class="customer-search">
                        <select id="customerSelect" class="form-select" style="width: 100%;">
                            <option value="">Select or search customer...</option>
                        </select>
                    </div>
                </div>
                <div class="category-pills">
                    <button class="category-pill active" data-category="all">
                        <i class="fas fa-th"></i> All Products
                    </button>
                    <button class="category-pill" data-category="featured">
                        <i class="fas fa-star"></i> Featured
                    </button>
                    <button class="category-pill" data-category="sale">
                        <i class="fas fa-tag"></i> On Sale
                    </button>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="products-grid-container" id="productsContainer">
                <div class="products-grid" id="productsGrid">
                    <!-- Products will be loaded here -->
                </div>
                <div class="loading-spinner" id="loadingSpinner" style="display: none;">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p>Loading products...</p>
                </div>
            </div>
        </div>

        <!-- Cart Sidebar -->
        <div class="cart-sidebar">
            <div class="cart-header">
                <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; gap: 8px;">
                    <div style="flex: 1;">
                        <span><i class="fas fa-shopping-cart"></i> Cart</span>
                        <span class="cart-badge" id="cartBadge">0 items</span>
                    </div>
                    <!-- Coupon Input in Cart Header -->
                    <div id="cartHeaderCoupon" style="display: none; flex: 1; min-width: 180px;">
                        <div style="display: flex; gap: 4px; align-items: center;">
                            <input type="text" id="cartHeaderCouponCode" placeholder="Coupon" style="flex: 1; padding: 4px 8px; border-radius: 4px; border: 1px solid #ddd; font-size: 11px; text-transform: uppercase;">
                            <button onclick="applyCouponFromHeader()" style="padding: 4px 8px; background: #667eea; color: white; border: none; border-radius: 4px; font-size: 10px; cursor: pointer;">
                                <i class="fas fa-check"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Info - Hidden (moved to header) -->
            <div class="customer-info-box" id="selectedCustomerBox" style="display: none;">
                <div class="selected-customer">
                    <div>
                        <div class="customer-name" id="selectedCustomerName" style="display: none;">--</div>
                        <div class="customer-email" id="selectedCustomerEmail" style="display: none;">--</div>
                        <div id="storeCreditDisplay" style="display: none;">
                            <strong style="color: #856404;">Store Credit: ₹<span id="availableCredit">0.00</span></strong>
                            <button type="button" class="btn btn-sm btn-link p-0 float-end" onclick="toggleStoreCreditApplication()" style="font-size: 10px;">Apply</button>
                        </div>
                    </div>
                    <button class="cart-item-remove" onclick="clearSelectedCustomer()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <!-- Applied Coupon Display -->
            <div id="appliedCouponBox" class="applied-coupon-box" style="display: none; margin-bottom: 10px;">
                <div class="applied-coupon-info">
                    <span class="coupon-label"><i class="fas fa-tag"></i> <span id="appliedCouponCode"></span></span>
                    <span class="coupon-savings">-₹<span id="appliedCouponAmount">0</span></span>
                </div>
                <button class="remove-coupon-btn" onclick="removeCoupon()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Loyalty Points Section -->
            <div class="rewards-section" id="rewardsSection" style="display: none;">

                <!-- Loyalty Points -->
                <div class="loyalty-box" id="loyaltyBox" style="display: none;">
                    <div class="loyalty-header">
                        <label class="form-label small mb-1">
                            <i class="fas fa-star"></i> Loyalty Points
                        </label>
                        <span class="points-balance" id="pointsBalance">0 pts</span>
                    </div>
                    <div class="points-redeem-group">
                        <input type="number" 
                               id="pointsToRedeem" 
                               placeholder="Points to redeem"
                               min="0"
                               value="0">
                        <button onclick="redeemLoyaltyPoints()" class="redeem-points-btn">
                            <i class="fas fa-gift"></i> Redeem
                        </button>
                    </div>
                    <div id="pointsMessage" class="points-message" style="display: none;"></div>
                    <div id="redeemedPointsBox" class="redeemed-points-box" style="display: none;">
                        <div class="redeemed-points-info">
                            <span class="points-label"><i class="fas fa-gift"></i> <span id="redeemedPointsCount">0</span> points redeemed</span>
                            <span class="points-savings">-₹<span id="redeemedPointsAmount">0</span></span>
                        </div>
                        <button class="remove-points-btn" onclick="removeRedeemedPoints()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="cart-items" id="cartItems">
                <div class="cart-empty">
                    <i class="fas fa-shopping-cart"></i>
                    <p>Cart is empty<br><small>Add products to start</small></p>
                </div>
            </div>

            <div class="cart-footer">
                <!-- Row 1: Discount & Payment Method -->
                <div class="cart-row-1">
                    <div class="discount-section">
                        <label class="form-label small mb-1"><i class="fas fa-percent"></i> Discount (%)</label>
                        <div class="discount-input-group">
                            <input type="number" 
                                   id="discountPercent" 
                                   placeholder="0" 
                                   min="0" 
                                   max="100" 
                                   value="0">
                            <button onclick="applyDiscount()">Apply</button>
                        </div>
                    </div>

                    <div class="payment-method-select">
                        <label class="form-label small mb-1"><i class="fas fa-credit-card"></i> Payment Method</label>
                        <select id="paymentMethod" onchange="updatePaymentDisplay()">
                            <option value="cash">💵 Cash</option>
                            <option value="card">💳 Card</option>
                            <option value="upi">📱 UPI</option>
                            <option value="store_credit">🎁 Store Credit</option>
                        </select>
                    </div>
                    <div id="storeCreditPaymentBox" style="display: none; margin-top: 8px; padding: 8px; background: #e7f3ff; border-radius: 4px; border-left: 3px solid #0066cc;">
                        <small style="color: #0066cc;"><strong>Available: ₹<span id="creditAvailable">0.00</span></strong></small><br>
                        <small style="color: #0066cc;">Amount to use: ₹<span id="creditToUse">0.00</span></small>
                    </div>
                </div>

                <!-- Row 2: Subtotal, Discount, Tax, Total -->
                <div class="cart-totals-row">
                    <div class="total-item">
                        <span class="total-label">Subtotal</span>
                        <span class="total-value" id="cartSubtotal">₹0.00</span>
                    </div>
                    <div class="total-item">
                        <span class="total-label">Discount</span>
                        <span class="total-value" id="cartDiscount">₹0.00</span>
                    </div>
                    <div class="total-item">
                        <span class="total-label">Tax</span>
                        <span class="total-value" id="cartTax">₹0.00</span>
                    </div>
                    <div id="storeCreditRowDisplay" class="total-item" style="display: none;">
                        <span class="total-label">Store Credit</span>
                        <span class="total-value" id="cartStoreCredit">-₹0.00</span>
                    </div>
                    <div class="total-item highlight">
                        <span class="total-label">Total</span>
                        <span class="total-value" id="cartTotal">₹0.00</span>
                    </div>
                </div>

                <!-- Row 3: Process Payment & Split Payment -->
                <div class="cart-row-3" style="gap: 6px;">
                    <button class="checkout-btn-half" id="checkoutBtn" disabled onclick="processCheckout()" style="padding: 8px 12px; font-size: 13px;">
                        <i class="fas fa-check-circle"></i> Payment
                    </button>

                    <button class="split-btn-half" id="splitPaymentBtn" disabled onclick="showSplitPayment()" style="padding: 8px 12px; font-size: 13px;">
                        <i class="fas fa-money-bill-wave"></i> Split
                    </button>
                </div>

                <!-- Row 4: Action Buttons -->
                <div class="action-buttons" style="gap: 6px;">
                    <button onclick="holdOrder()" style="padding: 6px 10px; font-size: 12px; flex: 1;">
                        <i class="fas fa-pause"></i> Hold
                    </button>
                    <button onclick="showHeldOrders()" style="padding: 6px 10px; font-size: 12px; flex: 1;">
                        <i class="fas fa-list"></i> Orders
                    </button>
                    <button onclick="printReceipt()">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <button onclick="clearCart()">
                        <i class="fas fa-trash"></i> Clear
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Held Orders Modal -->
    <div class="modal fade" id="heldOrdersModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-list"></i> Held Orders</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="heldOrdersList">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Split Payment Modal -->
    <div class="modal fade" id="splitPaymentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-money-bill-wave"></i> Split Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Total Amount:</strong> <span id="splitTotalAmount">₹0.00</span><br>
                        <strong>Remaining:</strong> <span id="splitRemainingAmount" class="text-danger">₹0.00</span>
                    </div>

                    <div id="splitPaymentsList">
                        <!-- Payment entries will be added here -->
                    </div>

                    <button class="btn btn-success w-100 mb-3" onclick="addPaymentEntry()">
                        <i class="fas fa-plus"></i> Add Payment Method
                    </button>

                    <div id="splitPaymentError" class="alert alert-danger" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="processSplitPayment()" id="completeSplitBtn" disabled>
                        <i class="fas fa-check-circle"></i> Complete Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders History Modal -->
    <div class="modal fade" id="ordersHistoryModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-receipt"></i> My Orders History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 d-flex gap-2">
                        <input type="date" id="orderDateFilter" class="form-control" style="width: auto;" />
                        <select id="orderStatusFilter" class="form-select" style="width: auto;">
                            <option value="">All Status</option>
                            <option value="completed">Completed</option>
                            <option value="processing">Processing</option>
                            <option value="pending">Pending</option>
                        </select>
                        <button class="btn btn-primary" onclick="loadOrdersHistory()">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <button class="btn btn-secondary" onclick="resetOrderFilters()">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                    </div>

                    <div id="ordersHistoryList">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- New Customer Modal -->
    <div class="modal fade" id="newCustomerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-plus"></i> Add New Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="newCustomerForm">
                        <div class="mb-3">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="customerName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="customerMobile" pattern="[0-9]{10}" required>
                            <small class="text-muted">10-digit mobile number</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email (Optional)</label>
                            <input type="email" class="form-control" id="customerEmail">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address (Optional)</label>
                            <textarea class="form-control" id="customerAddress" rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">City (Optional)</label>
                                <input type="text" class="form-control" id="customerCity">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">PIN Code (Optional)</label>
                                <input type="text" class="form-control" id="customerPincode" pattern="[0-9]{6}">
                            </div>
                        </div>
                        <div id="customerFormError" class="alert alert-danger" style="display: none;"></div>
                        <div id="customerFormSuccess" class="alert alert-success" style="display: none;"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveNewCustomer()">
                        <i class="fas fa-save"></i> Save Customer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Global variables
        let cart = {};
        let selectedCustomer = null;
        let customerStoreCredit = 0;
        let storeCreditApplied = false;
        let currentPage = 1;
        let isLoading = false;
        let hasMore = true;
        let currentSearchTerm = '';
        let selectedDiscount = 0;
        let appliedCoupon = null;
        let redeemedPoints = null;
        let customerLoyaltyPoints = 0;
        const CSRF_TOKEN = '<?php echo generateCsrfToken(); ?>';
        
        // WooCommerce Tax Rates (simplified - can be loaded from API)
        const TAX_RATES = {
            'standard': 0.18,  // 18% GST
            'reduced-rate': 0.05,  // 5% GST
            'zero-rate': 0.00,  // 0% GST
            '': 0.18  // Default
        };
        
        // Calculate tax for a product (Tax-Inclusive - extract GST from MRP)
        function calculateProductTax(item) {
            if (item.tax_status !== 'taxable') return 0;
            // Use tax_rate from item if available (from API), otherwise fallback to TAX_RATES
            const taxRate = item.tax_rate !== undefined ? item.tax_rate : (TAX_RATES[item.tax_class] || TAX_RATES['standard']);
            // MRP already includes tax, so we extract it using reverse calculation
            const priceInclTax = item.price - (item.item_discount || 0);
            const basePrice = priceInclTax / (1 + taxRate);
            const taxAmount = priceInclTax - basePrice;
            return taxAmount;
        }
        
        // Fetch applicable tax rate from server for a product
        function fetchProductTaxRate(productId, callback) {
            $.ajax({
                url: `/api/product-tax-rate/${productId}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        callback(response.tax_rate);
                    } else {
                        callback(null);
                    }
                },
                error: function() {
                    callback(null);
                }
            });
        }

        // Initialize POS
        $(document).ready(function() {
            initializeCustomerSelect();
            loadProducts();
            setupBarcodeSearch();
            setupInfiniteScroll();
            setupCategoryFilters();
        });

        // Initialize Select2 for customer search with optimizations
        function initializeCustomerSelect() {
            $('#customerSelect').select2({
                theme: 'bootstrap-5',
                placeholder: 'Select or search customer...',
                allowClear: true,
                minimumInputLength: 2,
                ajax: {
                    url: '/api/customers/search',
                    dataType: 'json',
                    delay: 400,
                    cache: true,
                    data: function (params) {
                        return {
                            search: params.term || '',
                            limit: 30
                        };
                    },
                    processResults: function (data, params) {
                        if (!data || !data.results) {
                            console.error('Invalid customer data format:', data);
                            return { results: [] };
                        }
                        return {
                            results: data.results.map(c => ({
                                id: c.id,
                                text: c.text || c.name || 'Unknown',
                                customer: c
                            })),
                            pagination: {
                                more: data.pagination?.more || false
                            }
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0,
                templateResult: formatCustomer,
                templateSelection: formatCustomerSelection
            }).on('select2:select', function (e) {
                selectedCustomer = e.params.data.customer;
                updateSelectedCustomerDisplay();
            });
        }

        // Format customer in dropdown (rich display)
        function formatCustomer(customer) {
            if (customer.loading) return customer.text;
            if (!customer.customer) return customer.text;
            
            const c = customer.customer;
            const $container = $(
                '<div class="customer-option">' +
                    '<div class="customer-option-name"><i class="fas fa-user text-primary"></i> <strong>' + c.name + '</strong></div>' +
                    (c.email ? '<div class="customer-option-detail"><i class="fas fa-envelope text-muted"></i> ' + c.email + '</div>' : '') +
                    (c.phone ? '<div class="customer-option-detail"><i class="fas fa-phone text-muted"></i> ' + c.phone + '</div>' : '') +
                '</div>'
            );
            return $container;
        }

        // Format customer when selected (compact display)
        function formatCustomerSelection(customer) {
            return customer.text || customer.customer?.name || customer.id;
        }

        // Update selected customer display (moved to top header)
        function updateSelectedCustomerDisplay() {
            if (selectedCustomer) {
                // Update header customer section
                $('#headerCustomerName').text(selectedCustomer.name);
                $('#headerCustomerContact').text(selectedCustomer.email || selectedCustomer.phone || 'No contact');
                $('#headerCustomerSection').show();
                
                // Show coupon input in cart header
                $('#cartHeaderCoupon').show();
                
                // Show rewards section and fetch loyalty points
                $('#rewardsSection').slideDown();
                fetchCustomerLoyaltyPoints();
                
                // Load and display store credits
                loadCustomerStoreCredit(selectedCustomer.id);
                
                // Show header credit section
                setTimeout(() => {
                    const credit = parseFloat($('#headerCreditAmount').text() || 0);
                    if (credit > 0) {
                        $('#headerCustomerCredit').show();
                    } else {
                        $('#headerCustomerCredit').hide();
                    }
                }, 300);
            } else {
                $('#headerCustomerSection').hide();
                $('#cartHeaderCoupon').hide();
                $('#rewardsSection').slideUp();
                $('#loyaltyBox').hide();
                $('#headerCustomerCredit').hide();
                customerLoyaltyPoints = 0;
            }
        }

        // Clear selected customer
        function clearSelectedCustomer() {
            selectedCustomer = null;
            $('#customerSelect').val(null).trigger('change');
            $('#headerCustomerSection').hide();
            $('#cartHeaderCoupon').hide();
            $('#rewardsSection').slideUp();
            
            // Clear coupon and points
            removeCoupon();
            removeRedeemedPoints();
            customerLoyaltyPoints = 0;
            $('#loyaltyBox').hide();
        }

        // Fetch customer loyalty points
        function fetchCustomerLoyaltyPoints() {
            if (!selectedCustomer || !selectedCustomer.id) {
                return;
            }

            $.ajax({
                url: `/api/customers/${selectedCustomer.id}/points`,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        customerLoyaltyPoints = response.points;
                        $('#pointsBalance').text(response.points + ' pts (₹' + response.rupee_value.toFixed(2) + ')');
                        
                        // Show loyalty box only if customer has points
                        if (response.points > 0) {
                            $('#loyaltyBox').slideDown();
                        } else {
                            $('#loyaltyBox').hide();
                        }
                    }
                },
                error: function() {
                    $('#loyaltyBox').hide();
                }
            });
        }

        // Load customer store credit
        function loadCustomerStoreCredit(customerId) {
            if (!customerId) return;
            
            $.ajax({
                url: `/api/customer/${customerId}/store-credit`,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        customerStoreCredit = response.total_balance || 0;
                        $('#availableCredit').text(customerStoreCredit.toFixed(2));
                        $('#headerCreditAmount').text(customerStoreCredit.toFixed(2));
                        
                        if (customerStoreCredit > 0) {
                            $('#storeCreditDisplay').show();
                            $('#headerCustomerCredit').show();
                        } else {
                            $('#storeCreditDisplay').hide();
                            $('#headerCustomerCredit').hide();
                        }
                    }
                },
                error: function() {
                    customerStoreCredit = 0;
                    $('#storeCreditDisplay').hide();
                    $('#headerCustomerCredit').hide();
                }
            });
        }
        
        // Apply coupon from header input
        function applyCouponFromHeader() {
            const code = $('#cartHeaderCouponCode').val().trim().toUpperCase();
            $('#couponCode').val(code);
            applyCoupon();
        }

        // Update payment display for store credit
        function updatePaymentDisplay() {
            const method = $('#paymentMethod').val();
            if (method === 'store_credit') {
                if (customerStoreCredit <= 0) {
                    alert('This customer has no store credit available!');
                    $('#paymentMethod').val('cash').trigger('change');
                    return;
                }
                $('#storeCreditPaymentBox').slideDown();
                $('#creditAvailable').text(customerStoreCredit.toFixed(2));
            } else {
                $('#storeCreditPaymentBox').slideUp();
            }
        }

        // Toggle store credit application
        function toggleStoreCreditApplication() {
            if (!selectedCustomer) {
                alert('Please select a customer first!');
                return;
            }
            
            storeCreditApplied = !storeCreditApplied;
            const total = getCartTotal();
            const creditToUse = Math.min(customerStoreCredit, total);
            
            if (storeCreditApplied) {
                $('#creditToUse').text(creditToUse.toFixed(2));
                $('#storeCreditPaymentBox').find('.btn-link').text('Remove');
                alert('Store credit will be applied to this order: ₹' + creditToUse.toFixed(2));
            } else {
                $('#creditToUse').text('0.00');
                $('#storeCreditPaymentBox').find('.btn-link').text('Apply');
                alert('Store credit will NOT be applied to this order');
            }
            updateTotals();
        }

        // Get current cart total
        function getCartTotal() {
            const totalText = $('#cartTotal').text().replace('₹', '').trim();
            return parseFloat(totalText) || 0;
        }

        // Apply coupon code
        function applyCoupon() {
            const code = $('#couponCode').val().trim().toUpperCase();
            
            if (!code) {
                showCouponMessage('Please enter a coupon code', 'error');
                return;
            }

            // Get cart data for validation
            const cartItems = [];
            let cartSubtotal = 0;
            
            Object.values(cart).forEach(item => {
                cartItems.push({
                    product_id: item.id,
                    quantity: item.quantity,
                    price: item.price
                });
                cartSubtotal += item.price * item.quantity;
            });

            const cartData = {
                subtotal: cartSubtotal,
                items: cartItems,
                customer_id: selectedCustomer?.id || null
            };

            $.ajax({
                url: '/api/coupons/validate',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    code: code,
                    cart: cartData,
                    csrf_token: CSRF_TOKEN
                }),
                success: function(response) {
                    if (response.success) {
                        appliedCoupon = response.coupon;
                        showCouponMessage(response.message, 'success');
                        
                        // Show applied coupon box
                        $('#appliedCouponCode').text(appliedCoupon.code);
                        $('#appliedCouponAmount').text(formatPrice(appliedCoupon.discount_amount));
                        $('#appliedCouponBox').slideDown();
                        $('#couponCode').val('').prop('disabled', true);
                        $('.coupon-apply-btn').prop('disabled', true);
                        
                        // Update cart totals
                        updateCartTotals();
                    }
                },
                error: function(xhr) {
                    const error = xhr.responseJSON?.message || 'Invalid coupon code';
                    showCouponMessage(error, 'error');
                    appliedCoupon = null;
                }
            });
        }

        // Remove applied coupon
        function removeCoupon() {
            appliedCoupon = null;
            $('#appliedCouponBox').slideUp();
            $('#couponCode').val('').prop('disabled', false);
            $('.coupon-apply-btn').prop('disabled', false);
            $('#couponMessage').hide();
            updateCartTotals();
        }

        // Show coupon message
        function showCouponMessage(message, type) {
            $('#couponMessage')
                .removeClass('success error')
                .addClass(type)
                .text(message)
                .show();
                
            setTimeout(() => {
                $('#couponMessage').fadeOut();
            }, 3000);
        }

        // Redeem loyalty points
        function redeemLoyaltyPoints() {
            const pointsToRedeem = parseInt($('#pointsToRedeem').val()) || 0;
            
            if (pointsToRedeem <= 0) {
                showPointsMessage('Please enter points to redeem', 'error');
                return;
            }

            if (pointsToRedeem > customerLoyaltyPoints) {
                showPointsMessage('Insufficient points balance', 'error');
                return;
            }

            // Calculate cart total
            let cartTotal = 0;
            Object.values(cart).forEach(item => {
                cartTotal += item.price * item.quantity;
            });

            $.ajax({
                url: '/api/points/redeem',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    customer_id: selectedCustomer.id,
                    points: pointsToRedeem,
                    cart_total: cartTotal,
                    csrf_token: CSRF_TOKEN
                }),
                success: function(response) {
                    if (response.success) {
                        redeemedPoints = {
                            points: response.points_redeemed,
                            discount_amount: response.discount_amount,
                            remaining_points: response.remaining_points
                        };
                        
                        showPointsMessage(response.message, 'success');
                        
                        // Show redeemed points box
                        $('#redeemedPointsCount').text(redeemedPoints.points);
                        $('#redeemedPointsAmount').text(formatPrice(redeemedPoints.discount_amount));
                        $('#redeemedPointsBox').slideDown();
                        $('#pointsToRedeem').val('').prop('disabled', true);
                        $('.redeem-points-btn').prop('disabled', true);
                        
                        // Update cart totals
                        updateCartTotals();
                    }
                },
                error: function(xhr) {
                    const error = xhr.responseJSON?.message || 'Error redeeming points';
                    showPointsMessage(error, 'error');
                }
            });
        }

        // Remove redeemed points
        function removeRedeemedPoints() {
            redeemedPoints = null;
            $('#redeemedPointsBox').slideUp();
            $('#pointsToRedeem').val('').prop('disabled', false);
            $('.redeem-points-btn').prop('disabled', false);
            $('#pointsMessage').hide();
            updateCartTotals();
        }

        // Show points message
        function showPointsMessage(message, type) {
            $('#pointsMessage')
                .removeClass('success error')
                .addClass(type)
                .text(message)
                .show();
                
            setTimeout(() => {
                $('#pointsMessage').fadeOut();
            }, 3000);
        }

        // Load products with pagination
        function loadProducts(reset = false) {
            if (isLoading || (!hasMore && !reset)) return;

            if (reset) {
                currentPage = 1;
                hasMore = true;
                $('#productsGrid').empty();
            }

            isLoading = true;
            $('#loadingSpinner').show();

            $.ajax({
                url: '/api/products',
                method: 'GET',
                data: {
                    search: currentSearchTerm,
                    page: currentPage,
                    limit: 20
                },
                success: function(response) {
                    if (response.success) {
                        displayProducts(response.products, reset);
                        
                        if (response.is_barcode && response.products.length > 0) {
                            // Auto-add to cart if barcode found
                            addToCart(response.products[0]);
                            $('#barcodeSearch').val('').focus();
                        }
                        
                        if (response.products.length < 20) {
                            hasMore = false;
                        }
                        
                        currentPage++;
                    }
                },
                complete: function() {
                    isLoading = false;
                    $('#loadingSpinner').hide();
                }
            });
        }

        // Display products
        function displayProducts(products, reset) {
            const grid = $('#productsGrid');
            
            if (reset) {
                grid.empty();
            }

            products.forEach(product => {
                const stock = parseInt(product.stock_quantity) || 0;
                const stockClass = stock > 10 ? 'stock-good' : (stock > 0 ? 'stock-low' : 'stock-out');
                const onSale = product.sale_price && parseFloat(product.sale_price) < parseFloat(product.price);
                const price = onSale ? product.sale_price : product.price;

                const card = $(`
                    <div class="product-card ${onSale ? 'on-sale' : ''}" 
                         data-id="${product.id}"
                         data-name="${escapeHtml(product.name)}"
                         data-price="${price}"
                         data-regular-price="${product.regular_price || price}"
                         data-sale-price="${product.sale_price || 0}"
                         data-sku="${escapeHtml(product.sku || 'N/A')}"
                         data-stock="${stock}"
                         data-tax-class="${product.tax_class || ''}"
                         data-tax-status="${product.tax_status || 'taxable'}">
                        <div class="product-name">${escapeHtml(product.name)}</div>
                        <div class="product-sku">SKU: ${escapeHtml(product.sku || 'N/A')}</div>
                        <div class="product-stock">
                            <span class="stock-indicator ${stockClass}"></span>
                            <span>Stock: ${stock}</span>
                        </div>
                        <div class="product-price">
                            ₹${formatPrice(price)}
                            ${onSale ? `<span class="product-price-old">₹${formatPrice(product.regular_price)}</span>` : ''}
                        </div>
                    </div>
                `);

                card.on('click', function() {
                    const productData = {
                        id: $(this).data('id'),
                        name: $(this).data('name'),
                        price: parseFloat($(this).data('price')),
                        regular_price: parseFloat($(this).data('regular-price') || $(this).data('price')),
                        sale_price: parseFloat($(this).data('sale-price') || 0),
                        sku: $(this).data('sku'),
                        stock: parseInt($(this).data('stock')),
                        tax_class: $(this).data('tax-class') || '',
                        tax_status: $(this).data('tax-status') || 'taxable'
                    };
                    addToCart(productData);
                });

                grid.append(card);
            });
        }

        // Barcode search setup
        function setupBarcodeSearch() {
            let searchTimeout;
            $('#barcodeSearch').on('input', function() {
                clearTimeout(searchTimeout);
                const term = $(this).val().trim();
                
                searchTimeout = setTimeout(() => {
                    currentSearchTerm = term;
                    loadProducts(true);
                }, 300);
            });

            // Enter key for instant search
            $('#barcodeSearch').on('keypress', function(e) {
                if (e.which === 13) {
                    clearTimeout(searchTimeout);
                    currentSearchTerm = $(this).val().trim();
                    loadProducts(true);
                }
            });
        }

        // Infinite scroll setup
        function setupInfiniteScroll() {
            const container = $('#productsContainer');
            container.on('scroll', function() {
                if ($(this).scrollTop() + $(this).innerHeight() >= $(this)[0].scrollHeight - 100) {
                    loadProducts();
                }
            });
        }

        // Category filters
        function setupCategoryFilters() {
            $('.category-pill').on('click', function() {
                $('.category-pill').removeClass('active');
                $(this).addClass('active');
                
                const category = $(this).data('category');
                // Implement category filtering as needed
            });
        }

        // Add to cart
        function addToCart(product) {
            if (product.stock <= 0) {
                alert('Product out of stock!');
                return;
            }

            const id = product.id;
            
            if (cart[id]) {
                if (cart[id].quantity < product.stock) {
                    cart[id].quantity++;
                } else {
                    alert('Cannot add more. Maximum stock reached!');
                    return;
                }
            } else {
                cart[id] = {
                    id: product.id,
                    name: product.name,
                    price: product.price,
                    regular_price: product.regular_price || product.price,
                    sale_price: product.sale_price || 0,
                    sku: product.sku,
                    stock: product.stock,
                    tax_class: product.tax_class || '',
                    tax_status: product.tax_status || 'taxable',
                    quantity: 1,
                    item_discount: 0, // Additional cashier discount
                    tax_rate: product.tax_rate || 0.05 // Use 5% tax from product data
                };
            }

            updateCartDisplay();
        }

        // Update cart display
        function updateCartDisplay() {
            const cartContainer = $('#cartItems');
            cartContainer.empty();

            if (Object.keys(cart).length === 0) {
                cartContainer.html(`
                    <div class="cart-empty">
                        <i class="fas fa-shopping-cart"></i>
                        <p>Cart is empty<br><small>Add products to start</small></p>
                    </div>
                `);
                $('#checkoutBtn').prop('disabled', true);
                updateTotals();
                return;
            }

            let itemCount = 0;
            
            for (let id in cart) {
                const item = cart[id];
                itemCount += item.quantity;
                
                // MRP is regular_price from database (already tax-inclusive)
                const mrp = item.regular_price;
                const salePrice = item.price;
                const productDiscount = mrp - salePrice;
                const itemDiscount = item.item_discount || 0;
                const finalUnitPrice = salePrice - itemDiscount;
                
                const taxRate = item.tax_rate || (TAX_RATES[item.tax_class] || TAX_RATES['standard']);
                const taxPercent = (taxRate * 100).toFixed(2);
                const taxAmount = calculateProductTax(item) * item.quantity;
                // Tax is already included in price, so don't add it again
                const itemTotal = (finalUnitPrice * item.quantity);

                const totalDiscount = productDiscount + itemDiscount;
                
                const cartItem = $(`
                    <div class="cart-item-enhanced">
                        <!-- Header with Product Name, Quantity Controls, and Discount -->
                        <div class="cart-item-header" style="display: flex; align-items: center; gap: 8px; justify-content: space-between; margin-bottom: 6px;">
                            <div class="cart-item-name" style="flex: 1;">${escapeHtml(item.name)}</div>
                            
                            <!-- Quantity Controls (Compact, Right side) -->
                            <div class="quantity-controls" style="display: flex; align-items: center; gap: 4px; background: white; padding: 3px; border-radius: 4px; border: 1px solid #e1e8ed;">
                                <button class="qty-btn" onclick="decreaseQuantity(${id})" style="width: 22px; height: 22px; padding: 0; font-size: 12px;">−</button>
                                <input type="number" class="qty-input" value="${item.quantity}" readonly style="width: 28px; height: 22px; padding: 2px; text-align: center; border: none; font-size: 11px; font-weight: 600;">
                                <button class="qty-btn" onclick="increaseQuantity(${id})" style="width: 22px; height: 22px; padding: 0; font-size: 12px;">+</button>
                            </div>
                            
                            <!-- Extra Discount Input (Right of Quantity) -->
                            <input type="number" 
                                   class="item-discount-input" 
                                   value="${itemDiscount}" 
                                   min="0" 
                                   max="${salePrice}"
                                   onchange="updateItemDiscount(${id}, this.value)"
                                   placeholder="Disc"
                                   style="width: 50px; height: 28px; padding: 4px; border: 1px solid #ddd; border-radius: 4px; font-size: 10px; text-align: center;">
                            
                            <!-- Remove Button -->
                            <i class="fas fa-times cart-item-remove" onclick="removeFromCart(${id})" style="cursor: pointer; color: #e74c3c; font-size: 14px;"></i>
                        </div>
                        
                        <!-- Single Row Pricing: MRP, Discount, Tax, Sale Price, Total -->
                        <div class="cart-item-pricing-row">
                            <div class="pricing-cell">
                                <span class="pricing-label">MRP</span>
                                <span class="pricing-value">₹${formatPrice(mrp)}</span>
                            </div>
                            <div class="pricing-cell discount">
                                <span class="pricing-label">Disc</span>
                                <span class="pricing-value">-₹${formatPrice(totalDiscount)}</span>
                            </div>
                            <div class="pricing-cell tax">
                                <span class="pricing-label">Tax</span>
                                <span class="pricing-value">₹${formatPrice(taxAmount)}</span>
                            </div>
                            <div class="pricing-cell">
                                <span class="pricing-label">Price</span>
                                <span class="pricing-value">₹${formatPrice(finalUnitPrice)}</span>
                            </div>
                            <div class="pricing-cell total">
                                <span class="pricing-label">Total</span>
                                <span class="pricing-value">₹${formatPrice(itemTotal)}</span>
                            </div>
                        </div>
                    </div>
                `);

                cartContainer.append(cartItem);
            }

            $('#cartBadge').text(itemCount + ' item' + (itemCount !== 1 ? 's' : ''));
            $('#checkoutBtn').prop('disabled', false);
            $('#splitPaymentBtn').prop('disabled', false);
            updateTotals();
        }
        
        // Update per-item discount
        function updateItemDiscount(id, value) {
            const discount = parseFloat(value) || 0;
            if (cart[id]) {
                cart[id].item_discount = Math.max(0, Math.min(discount, cart[id].price));
                updateCartDisplay();
            }
        }

        // Increase quantity
        function increaseQuantity(id) {
            if (cart[id].quantity < cart[id].stock) {
                cart[id].quantity++;
                updateCartDisplay();
            } else {
                alert('Cannot add more. Maximum stock reached!');
            }
        }

        // Decrease quantity
        function decreaseQuantity(id) {
            if (cart[id].quantity > 1) {
                cart[id].quantity--;
                updateCartDisplay();
            } else {
                removeFromCart(id);
            }
        }

        // Remove from cart
        function removeFromCart(id) {
            delete cart[id];
            updateCartDisplay();
        }

        // Clear cart
        function clearCart() {
            if (Object.keys(cart).length === 0) return;
            
            if (confirm('Clear all items from cart?')) {
                cart = {};
                selectedDiscount = 0;
                $('#discountPercent').val(0);
                removeCoupon();
                removeRedeemedPoints();
                updateCartDisplay();
            }
        }

        // Apply discount
        function applyDiscount() {
            const discount = parseFloat($('#discountPercent').val()) || 0;
            if (discount < 0 || discount > 100) {
                alert('Discount must be between 0 and 100%');
                return;
            }
            selectedDiscount = discount;
            updateTotals();
        }

        // Update totals (includes coupon and points discounts)
        function updateTotals() {
            let subtotal = 0;
            let totalTax = 0;
            let totalItemDiscounts = 0;
            
            for (let id in cart) {
                const item = cart[id];
                const itemSubtotal = item.price * item.quantity;
                const itemDiscount = (item.item_discount || 0) * item.quantity;
                
                subtotal += itemSubtotal;
                totalItemDiscounts += itemDiscount;
                totalTax += calculateProductTax(item) * item.quantity;
            }

            // Discount calculation order: line discounts → coupon → loyalty points → manual percentage
            let runningTotal = subtotal - totalItemDiscounts;
            
            // Apply coupon discount
            const couponDiscount = appliedCoupon ? appliedCoupon.discount_amount : 0;
            runningTotal -= couponDiscount;
            
            // Apply points redemption discount
            const pointsDiscount = redeemedPoints ? redeemedPoints.discount_amount : 0;
            runningTotal -= pointsDiscount;
            
            // Apply manual percentage discount
            const manualDiscountAmount = (runningTotal * selectedDiscount) / 100;
            runningTotal -= manualDiscountAmount;
            
            // Calculate total discounts for display
            const totalDiscounts = totalItemDiscounts + couponDiscount + pointsDiscount + manualDiscountAmount;
            
            // Tax is already included in MRP, so don't add it again
            const total = Math.max(0, runningTotal); // Ensure total is never negative

            $('#cartSubtotal').text('₹' + formatPrice(subtotal));
            $('#cartDiscount').text('₹' + formatPrice(totalDiscounts));
            $('#cartTax').text('₹' + formatPrice(totalTax));
            $('#cartTotal').text('₹' + formatPrice(total));
        }
        
        // Alias for updateTotals (used by coupon/points functions)
        function updateCartTotals() {
            updateTotals();
        }

        // Process checkout
        function processCheckout() {
            if (Object.keys(cart).length === 0) return;

            const paymentMethod = $('#paymentMethod').val();
            
            // Handle store credit payment
            if (paymentMethod === 'store_credit' && !selectedCustomer) {
                alert('Please select a customer to use store credit!');
                return;
            }
            
            if (paymentMethod === 'store_credit' && customerStoreCredit <= 0) {
                alert('This customer has no available store credit!');
                return;
            }
            
            const orderData = {
                cart: cart,
                customer_id: selectedCustomer ? selectedCustomer.id : 0,
                payment_method: paymentMethod,
                discount_percent: selectedDiscount,
                coupon_code: appliedCoupon ? appliedCoupon.code : null,
                coupon_discount: appliedCoupon ? appliedCoupon.discount_amount : 0,
                points_redeemed: redeemedPoints ? redeemedPoints.points : 0,
                points_discount: redeemedPoints ? redeemedPoints.discount_amount : 0,
                store_credit_applied: storeCreditApplied,
                store_credit_amount: storeCreditApplied ? Math.min(customerStoreCredit, getCartTotal()) : 0,
                csrf_token: CSRF_TOKEN
            };

            $.ajax({
                url: '/pos/checkout',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(orderData),
                success: function(response) {
                    if (response.success) {
                        // Clear cart and reset
                        cart = {};
                        selectedDiscount = 0;
                        storeCreditApplied = false;
                        customerStoreCredit = 0;
                        $('#discountPercent').val(0);
                        removeCoupon();
                        removeRedeemedPoints();
                        clearSelectedCustomer();
                        updateCartDisplay();
                        
                        // Show success message and ask about printing
                        const printNow = confirm('✅ Order completed successfully! Order ID: ' + response.order_id + '\n\n🖨️ Do you want to print the receipt?');
                        
                        if (printNow) {
                            printReceipt(response.order_id, true);
                        }
                    } else {
                        alert('❌ Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error processing checkout. Please try again.');
                }
            });
        }

        // Split Payment Variables
        let splitPayments = [];
        let splitPaymentCounter = 0;
        
        // Show split payment modal
        function showSplitPayment() {
            if (Object.keys(cart).length === 0) {
                alert('Cart is empty!');
                return;
            }
            
            // Calculate total with per-product taxes
            let subtotal = 0;
            let totalTax = 0;
            let totalItemDiscounts = 0;
            
            for (let id in cart) {
                const item = cart[id];
                const itemSubtotal = item.price * item.quantity;
                const itemDiscount = (item.item_discount || 0) * item.quantity;
                
                subtotal += itemSubtotal;
                totalItemDiscounts += itemDiscount;
                totalTax += calculateProductTax(item) * item.quantity;
            }
            
            const netSubtotal = subtotal - totalItemDiscounts;
            const globalDiscountAmount = (netSubtotal * selectedDiscount) / 100;
            // Tax is already included in MRP, so don't add it again
            const total = netSubtotal - globalDiscountAmount;
            
            // Reset split payments
            splitPayments = [];
            splitPaymentCounter = 0;
            $('#splitPaymentsList').empty();
            $('#splitTotalAmount').text('₹' + formatPrice(total));
            $('#splitRemainingAmount').text('₹' + formatPrice(total));
            $('#splitPaymentError').hide();
            
            // Add first payment entry
            addPaymentEntry();
            
            // Show modal
            const modalElement = document.getElementById('splitPaymentModal');
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        }
        
        // Add payment entry
        function addPaymentEntry() {
            const entryId = splitPaymentCounter++;
            
            const entry = $(`
                <div class="card mb-2" data-entry-id="${entryId}">
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small">Payment Method</label>
                                <select class="form-select form-select-sm payment-method-select" data-entry-id="${entryId}">
                                    <option value="cash">💵 Cash</option>
                                    <option value="card">💳 Card</option>
                                    <option value="upi">📱 UPI</option>
                                    <option value="wallet">👛 Wallet</option>
                                    <option value="check">🏦 Check</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small">Amount</label>
                                <input type="number" 
                                       class="form-control form-control-sm payment-amount-input" 
                                       data-entry-id="${entryId}"
                                       placeholder="0.00" 
                                       step="0.01" 
                                       min="0">
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button class="btn btn-sm btn-outline-danger w-100" onclick="removePaymentEntry(${entryId})">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mt-2">
                            <input type="text" 
                                   class="form-control form-control-sm payment-ref-input" 
                                   data-entry-id="${entryId}"
                                   placeholder="Transaction ID / Reference (optional)">
                        </div>
                    </div>
                </div>
            `);
            
            $('#splitPaymentsList').append(entry);
            
            // Add event listeners
            entry.find('.payment-amount-input').on('input', updateSplitTotals);
            entry.find('.payment-method-select').on('change', updateSplitTotals);
        }
        
        // Remove payment entry
        function removePaymentEntry(entryId) {
            $(`.card[data-entry-id="${entryId}"]`).remove();
            updateSplitTotals();
        }
        
        // Update split totals
        function updateSplitTotals() {
            // Calculate order total
            let subtotal = 0;
            for (let id in cart) {
                subtotal += cart[id].price * cart[id].quantity;
            }
            const discountAmount = (subtotal * selectedDiscount) / 100;
            const afterDiscount = subtotal - discountAmount;
            // Tax is already included in MRP, so don't add it again
            const orderTotal = afterDiscount;
            
            // Calculate sum of split payments
            let splitTotal = 0;
            $('.payment-amount-input').each(function() {
                const amount = parseFloat($(this).val()) || 0;
                splitTotal += amount;
            });
            
            const remaining = orderTotal - splitTotal;
            
            $('#splitRemainingAmount').text('₹' + formatPrice(remaining));
            
            // Update button state and style
            if (Math.abs(remaining) < 0.01) {
                $('#completeSplitBtn').prop('disabled', false);
                $('#splitRemainingAmount').removeClass('text-danger').addClass('text-success');
                $('#splitPaymentError').hide();
            } else {
                $('#completeSplitBtn').prop('disabled', true);
                $('#splitRemainingAmount').removeClass('text-success').addClass('text-danger');
                
                if (remaining < 0) {
                    $('#splitPaymentError').text('Payment amount exceeds order total!').show();
                } else {
                    $('#splitPaymentError').hide();
                }
            }
        }
        
        // Process split payment
        function processSplitPayment() {
            if (Object.keys(cart).length === 0) return;
            
            // Collect payment data
            const payments = [];
            $('.payment-amount-input').each(function() {
                const entryId = $(this).data('entry-id');
                const amount = parseFloat($(this).val()) || 0;
                const method = $(`.payment-method-select[data-entry-id="${entryId}"]`).val();
                const reference = $(`.payment-ref-input[data-entry-id="${entryId}"]`).val();
                
                if (amount > 0) {
                    payments.push({
                        method: method,
                        amount: amount,
                        reference: reference
                    });
                }
            });
            
            if (payments.length === 0) {
                alert('Please add at least one payment');
                return;
            }
            
            const orderData = {
                cart: cart,
                customer_id: selectedCustomer ? selectedCustomer.id : 0,
                payment_method: 'split',
                split_payments: payments,
                discount_percent: selectedDiscount,
                csrf_token: CSRF_TOKEN
            };
            
            $.ajax({
                url: '/pos/checkout',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(orderData),
                success: function(response) {
                    if (response.success) {
                        // Close modal
                        const modalElement = document.getElementById('splitPaymentModal');
                        const modal = bootstrap.Modal.getInstance(modalElement);
                        if (modal) modal.hide();
                        
                        // Clear cart and reset
                        cart = {};
                        selectedDiscount = 0;
                        $('#discountPercent').val(0);
                        clearSelectedCustomer();
                        updateCartDisplay();
                        
                        // Show success message and ask about printing
                        const printNow = confirm('✅ Order completed successfully! Order ID: ' + response.order_id + '\n\n🖨️ Do you want to print the receipt?');
                        
                        if (printNow) {
                            printReceipt(response.order_id, true);
                        }
                    } else {
                        alert('❌ Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error processing checkout. Please try again.');
                }
            });
        }
        
        // Hold order (save for later)
        function holdOrder() {
            if (Object.keys(cart).length === 0) {
                alert('Cart is empty!');
                return;
            }
            
            const orderName = prompt('Enter order name/reference (e.g., customer name or table number):');
            if (!orderName || orderName.trim() === '') return;

            const orderData = {
                reference_name: orderName.trim(),
                cart: cart,
                customer_id: selectedCustomer ? selectedCustomer.id : 0,
                discount_percent: selectedDiscount,
                coupon_code: appliedCoupon ? appliedCoupon.code : null,
                coupon_discount: appliedCoupon ? appliedCoupon.discount_amount : 0,
                points_redeemed: redeemedPoints ? redeemedPoints.points : 0,
                points_discount: redeemedPoints ? redeemedPoints.discount_amount : 0,
                notes: '',
                csrf_token: CSRF_TOKEN
            };

            $.ajax({
                url: '/api/orders/hold',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(orderData),
                success: function(response) {
                    if (response.success) {
                        alert('Order held successfully! Reference: ' + orderName);
                        cart = {};
                        selectedDiscount = 0;
                        $('#discountPercent').val(0);
                        removeCoupon();
                        removeRedeemedPoints();
                        clearSelectedCustomer();
                        updateCartDisplay();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error holding order. Please try again.');
                }
            });
        }
        
        // Show held orders modal
        function showHeldOrders() {
            const modalElement = document.getElementById('heldOrdersModal');
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
            loadHeldOrders();
        }
        
        // Load held orders list
        function loadHeldOrders() {
            $('#heldOrdersList').html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `);
            
            $.ajax({
                url: '/api/orders/held',
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        displayHeldOrders(response.orders);
                    } else {
                        $('#heldOrdersList').html('<p class="text-center text-muted py-4">Error loading held orders</p>');
                    }
                },
                error: function() {
                    $('#heldOrdersList').html('<p class="text-center text-muted py-4">Error loading held orders</p>');
                }
            });
        }
        
        // Display held orders
        function displayHeldOrders(orders) {
            if (orders.length === 0) {
                $('#heldOrdersList').html(`
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-inbox fa-3x mb-3" style="opacity: 0.3;"></i>
                        <p>No held orders</p>
                    </div>
                `);
                return;
            }
            
            let html = '<div class="list-group">';
            
            orders.forEach(order => {
                const itemCount = Object.keys(order.cart_data).length;
                let totalAmount = 0;
                
                for (let id in order.cart_data) {
                    const item = order.cart_data[id];
                    totalAmount += item.price * item.quantity;
                }
                
                const discountAmount = (totalAmount * order.discount_percent) / 100;
                const afterDiscount = totalAmount - discountAmount;
                // Tax is already included in MRP, so don't add it again
                const total = afterDiscount;
                
                const heldDate = new Date(order.held_at);
                const timeAgo = getTimeAgo(heldDate);
                
                html += `
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><strong>${escapeHtml(order.reference_name)}</strong></h6>
                                <p class="mb-1 text-muted small">
                                    <i class="fas fa-user"></i> ${escapeHtml(order.cashier_name || 'Unknown')}
                                    ${order.customer_name ? ' | <i class="fas fa-shopping-bag"></i> ' + escapeHtml(order.customer_name) : ''}
                                </p>
                                <p class="mb-1">
                                    <span class="badge bg-info">${itemCount} items</span>
                                    <span class="badge bg-success">₹${formatPrice(total)}</span>
                                    ${order.discount_percent > 0 ? '<span class="badge bg-warning text-dark">' + order.discount_percent + '% OFF</span>' : ''}
                                </p>
                                <small class="text-muted"><i class="fas fa-clock"></i> ${timeAgo}</small>
                            </div>
                            <div class="btn-group-vertical">
                                <button class="btn btn-sm btn-primary" onclick="resumeOrder(${order.id})">
                                    <i class="fas fa-play"></i> Resume
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="cancelHeldOrder(${order.id})">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            $('#heldOrdersList').html(html);
        }
        
        // Resume held order
        function resumeOrder(orderId) {
            if (Object.keys(cart).length > 0) {
                if (!confirm('Current cart will be replaced with the held order. Continue?')) {
                    return;
                }
            }
            
            $.ajax({
                url: '/api/orders/resume/' + orderId,
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ csrf_token: CSRF_TOKEN }),
                success: function(response) {
                    if (response.success) {
                        cart = response.cart;
                        selectedDiscount = parseFloat(response.discount_percent) || 0;
                        $('#discountPercent').val(selectedDiscount);
                        
                        if (response.customer_id && response.customer_name) {
                            selectedCustomer = {
                                id: response.customer_id,
                                name: response.customer_name,
                                email: response.customer_email || ''
                            };
                            updateSelectedCustomerDisplay();
                        }
                        
                        updateCartDisplay();
                        const modalElement = document.getElementById('heldOrdersModal');
                        const modal = bootstrap.Modal.getInstance(modalElement);
                        if (modal) modal.hide();
                        alert('Order resumed successfully!');
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error resuming order. Please try again.');
                }
            });
        }
        
        // Cancel held order
        function cancelHeldOrder(orderId) {
            if (!confirm('Are you sure you want to cancel this held order?')) {
                return;
            }
            
            $.ajax({
                url: '/api/orders/cancel/' + orderId,
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ csrf_token: CSRF_TOKEN }),
                success: function(response) {
                    if (response.success) {
                        alert('Held order cancelled successfully');
                        loadHeldOrders();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error cancelling order. Please try again.');
                }
            });
        }
        
        // Get time ago
        function getTimeAgo(date) {
            const now = new Date();
            const diff = Math.floor((now - date) / 1000);
            
            if (diff < 60) return diff + ' seconds ago';
            if (diff < 3600) return Math.floor(diff / 60) + ' minutes ago';
            if (diff < 86400) return Math.floor(diff / 3600) + ' hours ago';
            return Math.floor(diff / 86400) + ' days ago';
        }

        // Print receipt
        function printReceipt(orderId, autoPrint = false) {
            if (!orderId) {
                alert('No order ID provided');
                return;
            }
            
            // Open receipt in new window with auto-print parameter
            const url = autoPrint 
                ? `/pos/receipt/${orderId}?auto_print=1` 
                : `/pos/receipt/${orderId}`;
            
            const receiptWindow = window.open(url, '_blank', 'width=400,height=600');
            
            if (!receiptWindow) {
                alert('Please allow popups to print receipts');
            }
        }

        // Show add customer modal
        function showAddCustomerModal() {
            $('#newCustomerForm')[0].reset();
            $('#customerFormError').hide();
            $('#customerFormSuccess').hide();
            const modal = new bootstrap.Modal(document.getElementById('newCustomerModal'));
            modal.show();
        }

        // Save new customer
        function saveNewCustomer() {
            const name = $('#customerName').val().trim();
            const mobile = $('#customerMobile').val().trim();
            const email = $('#customerEmail').val().trim();
            const address = $('#customerAddress').val().trim();
            const city = $('#customerCity').val().trim();
            const pincode = $('#customerPincode').val().trim();

            // Validation
            if (!name || !mobile) {
                $('#customerFormError').text('Name and Mobile are required').show();
                return;
            }

            if (!/^\d{10}$/.test(mobile)) {
                $('#customerFormError').text('Mobile must be 10 digits').show();
                return;
            }

            if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                $('#customerFormError').text('Invalid email format').show();
                return;
            }

            $('#customerFormError').hide();

            // Save customer via API
            $.ajax({
                url: '/api/customers/create',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    name: name,
                    mobile: mobile,
                    email: email,
                    address: address,
                    city: city,
                    pincode: pincode,
                    csrf_token: CSRF_TOKEN
                }),
                success: function(response) {
                    if (response.success) {
                        $('#customerFormSuccess').text('Customer created successfully!').show();
                        
                        // Auto-select the new customer
                        selectedCustomer = {
                            id: response.customer.id,
                            name: name,
                            email: email,
                            phone: mobile,
                            mobile: mobile
                        };
                        
                        // Update Select2
                        const newOption = new Option(name, response.customer.id, true, true);
                        $('#customerSelect').append(newOption).trigger('change');
                        
                        // Update display
                        updateSelectedCustomerDisplay();
                        
                        // Close modal after 1 second
                        setTimeout(function() {
                            bootstrap.Modal.getInstance(document.getElementById('newCustomerModal')).hide();
                        }, 1000);
                    } else {
                        $('#customerFormError').text(response.message || 'Error creating customer').show();
                    }
                },
                error: function(xhr) {
                    const error = xhr.responseJSON?.message || 'Error creating customer. Please try again.';
                    $('#customerFormError').text(error).show();
                }
            });
        }

        // Show orders history modal
        function showOrdersHistory() {
            const modalElement = document.getElementById('ordersHistoryModal');
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
            loadOrdersHistory();
        }

        // Load orders history
        function loadOrdersHistory() {
            const dateFilter = $('#orderDateFilter').val();
            const statusFilter = $('#orderStatusFilter').val();
            
            $('#ordersHistoryList').html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `);

            const params = new URLSearchParams();
            if (dateFilter) params.append('date', dateFilter);
            if (statusFilter) params.append('status', statusFilter);

            $.ajax({
                url: '/api/orders/my-orders?' + params.toString(),
                method: 'GET',
                success: function(response) {
                    if (response.success && response.orders) {
                        displayOrdersHistory(response.orders);
                    } else {
                        $('#ordersHistoryList').html(`
                            <div class="alert alert-warning">
                                <i class="fas fa-info-circle"></i> No orders found
                            </div>
                        `);
                    }
                },
                error: function() {
                    $('#ordersHistoryList').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> Error loading orders. Please try again.
                        </div>
                    `);
                }
            });
        }

        // Display orders history
        function displayOrdersHistory(orders) {
            if (orders.length === 0) {
                $('#ordersHistoryList').html(`
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No orders found
                    </div>
                `);
                return;
            }

            let html = `
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Date & Time</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Payment</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            orders.forEach(order => {
                const orderDate = new Date(order.created_at || order.order_date);
                const statusClass = order.order_status === 'completed' ? 'success' : 
                                  order.order_status === 'processing' ? 'warning' : 'secondary';
                
                html += `
                    <tr>
                        <td><strong>${escapeHtml(order.order_number || order.order_id)}</strong></td>
                        <td>${orderDate.toLocaleString('en-IN', { 
                            day: '2-digit', 
                            month: 'short', 
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        })}</td>
                        <td>${escapeHtml(order.customer_name || 'Walk-in Customer')}</td>
                        <td>${order.total_items || '-'}</td>
                        <td><span class="badge bg-info">${(order.payment_method || 'CASH').toUpperCase()}</span></td>
                        <td><strong>₹${formatPrice(order.total)}</strong></td>
                        <td><span class="badge bg-${statusClass}">${(order.order_status || 'completed').toUpperCase()}</span></td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="viewOrderDetails(${order.wc_order_id || order.order_id})" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-success" onclick="printReceipt(${order.wc_order_id || order.order_id}, true)" title="Print Receipt">
                                <i class="fas fa-print"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });

            html += `
                        </tbody>
                    </table>
                </div>
            `;

            $('#ordersHistoryList').html(html);
        }

        // View order details
        function viewOrderDetails(orderId) {
            if (!orderId) {
                alert('Invalid order ID');
                return;
            }
            // Open receipt in new window (preview mode - no auto-print)
            printReceipt(orderId, false);
        }

        // Reset order filters
        function resetOrderFilters() {
            $('#orderDateFilter').val('');
            $('#orderStatusFilter').val('');
            loadOrdersHistory();
        }

        // Utility functions
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatPrice(price) {
            return parseFloat(price).toFixed(2);
        }

        // ===== CASHIER RETURN/EXCHANGE FUNCTIONS =====
        let cashierOrderData = null;
        let cashierSelectedProduct = null;

        function openCashierReturns() {
            cashierOrderData = null;
            cashierSelectedProduct = null;
            const orderInput = document.getElementById('cashierReturnOrderNumber');
            const orderInfo = document.getElementById('cashierOrderInfo');
            const returnForm = document.getElementById('cashierReturnForm');
            const processBtn = document.getElementById('processCashierReturnBtn');
            const modal = document.getElementById('cashierReturnModal');
            
            if (orderInput) orderInput.value = '';
            if (orderInfo) orderInfo.style.display = 'none';
            if (returnForm) returnForm.style.display = 'none';
            if (processBtn) processBtn.disabled = true;
            if (modal) {
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
            }
        }

        function cashierLoadOrder() {
            const orderInput = document.getElementById('cashierReturnOrderNumber');
            const orderInfo = document.getElementById('cashierOrderInfo');
            const orderDetails = document.getElementById('cashierOrderDetails');
            const orderItemsList = document.getElementById('cashierOrderItemsList');
            const returnForm = document.getElementById('cashierReturnForm');
            const refundAmount = document.getElementById('cashierTotalRefund');
            
            if (!orderInput) {
                alert('Order input field not found');
                return;
            }
            
            const orderNumber = orderInput.value.trim();
            if (!orderNumber) {
                alert('Please enter an order number');
                return;
            }
            
            if (orderInfo) {
                orderInfo.style.display = 'block';
            }
            if (orderDetails) {
                orderDetails.innerHTML = '<div class="spinner-border spinner-border-sm"></div> Loading...';
            }
            
            fetch(`/api/orders/search?q=${encodeURIComponent(orderNumber)}`)
                .then(r => {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.json();
                })
                .then(data => {
                    console.log('Order search response:', data);
                    
                    if (!data) {
                        throw new Error('Empty response');
                    }
                    
                    if (data.success === false) {
                        if (orderDetails) orderDetails.innerHTML = `<span class="text-danger"><i class="fas fa-exclamation-circle"></i> ${data.message || 'Order not found'}</span>`;
                        return;
                    }
                    
                    if (!data.orders || data.orders.length === 0) {
                        if (orderDetails) orderDetails.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-circle"></i> Order not found</span>';
                        return;
                    }
                    
                    cashierOrderData = data.orders[0];
                    console.log('Order data loaded:', cashierOrderData);
                    
                    // Build order details with items
                    let detailsHtml = `
                        <div class="mb-2">
                            <strong>Order #: ${escapeHtml(cashierOrderData.order_number || 'N/A')}</strong><br>
                            Customer: ${escapeHtml(cashierOrderData.customer_name || 'Walk-in')}<br>
                            Total: ₹${parseFloat(cashierOrderData.total || 0).toFixed(2)}<br>
                            Date: ${cashierOrderData.created_at ? new Date(cashierOrderData.created_at).toLocaleDateString() : 'N/A'}
                        </div>
                        <div class="mt-3">
                            <label class="fw-bold">Order Items:</label>
                            <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto; background: #f9f9f9;">
                    `;
                    
                    // Display order items
                    if (cashierOrderData.line_items && Array.isArray(cashierOrderData.line_items) && cashierOrderData.line_items.length > 0) {
                        cashierOrderData.line_items.forEach(item => {
                            const itemPrice = parseFloat(item.total || item.price || 0);
                            const itemQty = parseInt(item.quantity || 1);
                            detailsHtml += `
                                <div class="d-flex justify-content-between align-items-center p-2 border-bottom" style="font-size: 13px;">
                                    <div style="flex: 1;">
                                        <strong>${escapeHtml(item.product_name || 'Product')}</strong><br>
                                        <small>ID: ${escapeHtml(item.product_id || '')}</small>
                                    </div>
                                    <div style="text-align: right;">
                                        <div>Qty: ${itemQty}</div>
                                        <div style="font-weight: 600;">₹${itemPrice.toFixed(2)}</div>
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        detailsHtml += '<div class="text-muted p-2"><i class="fas fa-box"></i> No items in this order</div>';
                    }
                    
                    detailsHtml += '</div></div>';
                    
                    if (orderDetails) {
                        orderDetails.innerHTML = detailsHtml;
                    }
                    
                    if (returnForm) returnForm.style.display = 'block';
                    if (refundAmount) refundAmount.textContent = parseFloat(cashierOrderData.total || 0).toFixed(2);
                    console.log('Order details loaded successfully');
                })
                .catch(e => {
                    console.error('Order loading error:', e);
                    if (orderDetails) orderDetails.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle"></i> Error: ' + (e.message || 'Unknown error') + '</span>';
                });
        }

        function updateCashierReturnType() {
            const typeSelect = document.getElementById('cashierReturnType');
            const exchangeSection = document.getElementById('exchangeProductSection');
            const processBtn = document.getElementById('processCashierReturnBtn');
            
            if (!typeSelect) return;
            const type = typeSelect.value;
            
            if (exchangeSection) {
                exchangeSection.style.display = type === 'exchange' ? 'block' : 'none';
            }
            if (processBtn) {
                processBtn.disabled = !type;
            }
        }

        function searchCashierProducts() {
            const searchInput = document.getElementById('searchCashierProduct');
            const resultsDiv = document.getElementById('productSearchResults');
            
            if (!searchInput) {
                alert('Search input not found');
                return;
            }
            
            const search = searchInput.value.trim();
            if (!search) {
                alert('Enter a product name');
                return;
            }
            
            fetch(`/api/products?search=${encodeURIComponent(search)}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.products && resultsDiv) {
                        let html = '<div class="list-group">';
                        data.products.slice(0, 5).forEach(p => {
                            const price = parseFloat(p.sale_price || p.regular_price);
                            html += `<button type="button" class="list-group-item list-group-item-action" onclick='selectCashierProduct(${JSON.stringify({id: p.product_id, name: p.name, price: price})})'><strong>${p.name}</strong><br><small>₹${price.toFixed(2)}</small></button>`;
                        });
                        html += '</div>';
                        resultsDiv.innerHTML = html;
                    }
                })
                .catch(e => {
                    if (resultsDiv) {
                        resultsDiv.innerHTML = '<div class="alert alert-danger">Error searching products</div>';
                    }
                });
        }

        function selectCashierProduct(product) {
            cashierSelectedProduct = product;
            const resultsDiv = document.getElementById('productSearchResults');
            const processBtn = document.getElementById('processCashierReturnBtn');
            
            if (resultsDiv) {
                resultsDiv.innerHTML = `<div class="alert alert-success">Selected: <strong>${product.name}</strong> - ₹${product.price.toFixed(2)}</div>`;
            }
            if (processBtn) {
                processBtn.disabled = false;
            }
        }

        function processCashierReturn() {
            const type = document.getElementById('cashierReturnType').value;
            const reason = document.getElementById('cashierReturnReason').value;
            const method = document.getElementById('cashierRefundMethod').value;
            const amount = parseFloat(document.getElementById('cashierTotalRefund').textContent);
            
            if (!type || !reason || !method) { alert('Please fill all required fields'); return; }
            if (type === 'exchange' && !cashierSelectedProduct) { alert('Please select a replacement product'); return; }
            if (!confirm(`Process ${type} for ₹${amount.toFixed(2)}?`)) return;
            
            fetch('/admin/process-return', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    order_id: cashierOrderData.id,
                    order_number: cashierOrderData.order_number,
                    return_type: type,
                    return_reason: reason,
                    refund_amount: amount,
                    refund_method: method,
                    replacement_product_id: cashierSelectedProduct?.id || null,
                    notes: document.getElementById('cashierReturnNotes').value,
                    csrf_token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + (type === 'exchange' ? 'Exchange' : 'Return') + ' Processed!\n\nReturn #: ' + data.return_number);
                    
                    // Update order status to "returned" or "exchanged" 
                    fetch(`/api/orders/${cashierOrderData.id}/update-status`, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({
                            status: type === 'exchange' ? 'exchanged' : 'returned'
                        })
                    }).catch(e => console.error('Status update error:', e));
                    
                    bootstrap.Modal.getInstance(document.getElementById('cashierReturnModal')).hide();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(e => alert('Error: ' + e.message));
        }
    </script>

    <!-- Cashier Return/Exchange Modal -->
    <div class="modal fade" id="cashierReturnModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="fas fa-undo"></i> Process Return/Exchange</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Find Order to Return</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="cashierReturnOrderNumber" placeholder="Enter order number...">
                            <button type="button" class="btn btn-primary" onclick="cashierLoadOrder()"><i class="fas fa-search"></i> Search</button>
                        </div>
                    </div>
                    
                    <div id="cashierOrderInfo" class="border rounded p-3 mb-3 bg-light" style="display:none;">
                        <div id="cashierOrderDetails"></div>
                    </div>
                    
                    <div id="cashierReturnForm" style="display:none;">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Type *</label>
                                <select class="form-select" id="cashierReturnType" onchange="updateCashierReturnType()">
                                    <option value="">Select...</option>
                                    <option value="return">Return (Refund)</option>
                                    <option value="exchange">Exchange</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Reason *</label>
                                <select class="form-select" id="cashierReturnReason">
                                    <option value="">Select...</option>
                                    <option value="defective">Defective/Not Working</option>
                                    <option value="wrong_item">Wrong Item Sent</option>
                                    <option value="quality_issue">Poor Quality</option>
                                    <option value="damaged">Damaged/Broken</option>
                                    <option value="not_as_described">Not As Described</option>
                                    <option value="size_fit">Size/Fit Issue</option>
                                    <option value="color_mismatch">Color Mismatch</option>
                                    <option value="missing_parts">Missing Parts</option>
                                    <option value="duplicate_order">Duplicate Order</option>
                                    <option value="changed_mind">Changed Mind</option>
                                    <option value="expiry_expired">Expired Product</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Refund Method *</label>
                                <select class="form-select" id="cashierRefundMethod">
                                    <option value="">Select...</option>
                                    <option value="cash">Cash</option>
                                    <option value="original_payment">Original Payment Method</option>
                                    <option value="store_credit">Store Credit</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Amount</label>
                                <input type="text" class="form-control" id="cashierRefundAmount" readonly placeholder="₹0.00">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" id="cashierReturnNotes" rows="2" placeholder="Add any notes about this return..."></textarea>
                        </div>
                        
                        <div id="exchangeProductSection" style="display:none;" class="mb-3">
                            <label class="form-label fw-bold">Replacement Product</label>
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" id="searchCashierProduct" placeholder="Search...">
                                <button class="btn btn-primary" onclick="searchCashierProducts()">Search</button>
                            </div>
                            <div id="productSearchResults"></div>
                        </div>
                        
                        <div class="alert alert-info">
                            <strong>Refund: ₹<span id="cashierTotalRefund">0.00</span></strong>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" id="processCashierReturnBtn" onclick="processCashierReturn()" disabled>Process</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
