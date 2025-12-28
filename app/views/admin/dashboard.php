<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Admin Dashboard'; ?> - B-Plus POS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f7fa;
        }

        /* Admin Navbar */
        .admin-navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            height: 70px;
        }

        .admin-navbar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
        }

        .admin-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 24px;
            font-weight: 700;
        }

        .admin-user-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .admin-user-info {
            background: rgba(255,255,255,0.15);
            padding: 8px 18px;
            border-radius: 25px;
        }

        /* Sidebar */
        .admin-sidebar {
            position: fixed;
            top: 70px;
            left: 0;
            width: 260px;
            height: calc(100vh - 70px);
            background: white;
            border-right: 1px solid #e1e8ed;
            overflow-y: auto;
            padding: 20px 0;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li {
            margin: 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 25px;
            color: #5a6c7d;
            text-decoration: none;
            transition: all 0.2s;
            font-weight: 500;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: #f8f9fa;
            color: #667eea;
            border-left: 3px solid #667eea;
        }

        .sidebar-menu i {
            width: 20px;
            text-align: center;
            font-size: 18px;
        }

        .sidebar-section-title {
            padding: 20px 25px 10px;
            font-size: 11px;
            font-weight: 700;
            color: #95a5a6;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Main Content */
        .admin-content {
            margin-left: 260px;
            margin-top: 70px;
            padding: 30px;
            min-height: calc(100vh - 70px);
        }

        .admin-header {
            margin-bottom: 30px;
        }

        .admin-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .admin-header p {
            color: #7f8c8d;
            margin: 0;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            border: 1px solid #e8e8e8;
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }

        .stat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 12px;
        }

        .stat-card-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .stat-card-icon.blue {
            background: rgba(102,126,234,0.1);
            color: #667eea;
        }

        .stat-card-icon.green {
            background: rgba(46,204,113,0.1);
            color: #2ecc71;
        }

        .stat-card-icon.orange {
            background: rgba(243,156,18,0.1);
            color: #f39c12;
        }

        .stat-card-icon.red {
            background: rgba(231,76,60,0.1);
            color: #e74c3c;
        }

        .stat-card-value {
            font-size: 32px;
            font-weight: 700;
            color: #2c3e50;
            line-height: 1;
            margin-bottom: 6px;
        }

        .stat-card-label {
            font-size: 14px;
            color: #7f8c8d;
            font-weight: 500;
        }

        /* Content Cards */
        .content-card {
            background: white;
            border-radius: 12px;
            border: 1px solid #e8e8e8;
            padding: 24px;
            margin-bottom: 20px;
        }

        .content-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .content-card-title {
            font-size: 18px;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }

        /* Tables */
        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }

        .admin-table th {
            background: #f8f9fa;
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            color: #5a6c7d;
            border-bottom: 2px solid #e1e8ed;
        }

        .admin-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #f0f0f0;
            color: #2c3e50;
        }

        .admin-table tr:hover {
            background: #f8f9fa;
        }

        .badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .quick-action-btn {
            background: white;
            border: 2px solid #e8e8e8;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: #2c3e50;
        }

        .quick-action-btn:hover {
            border-color: #667eea;
            background: rgba(102,126,234,0.05);
            transform: translateY(-2px);
        }

        .quick-action-btn i {
            font-size: 32px;
            color: #667eea;
            margin-bottom: 10px;
        }

        .quick-action-btn span {
            display: block;
            font-weight: 600;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <!-- Admin Navbar -->
    <div class="admin-navbar">
        <div class="admin-navbar-content">
            <div class="admin-brand">
                <i class="fas fa-cog"></i>
                <span>B-Plus Admin</span>
            </div>
            <div class="admin-user-section">
                <div class="admin-user-info">
                    <i class="fas fa-user-shield"></i>
                    <?php echo htmlspecialchars($user['display_name'] ?? 'Admin'); ?>
                </div>
                <a href="/pos" class="btn btn-sm btn-light">
                    <i class="fas fa-cash-register"></i> POS
                </a>
                <a href="/logout" class="btn btn-sm btn-outline-light">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="admin-sidebar">
        <ul class="sidebar-menu">
            <li><a href="/admin" class="active"><i class="fas fa-chart-line"></i> Dashboard</a></li>
            
            <div class="sidebar-section-title">Sales & Orders</div>
            <li><a href="/admin/orders"><i class="fas fa-receipt"></i> Orders</a></li>
            <li><a href="/admin/sessions"><i class="fas fa-clock"></i> Cashier Sessions</a></li>
            <li><a href="/admin/reports"><i class="fas fa-chart-bar"></i> Reports</a></li>
            
            <div class="sidebar-section-title">Inventory</div>
            <li><a href="/admin/products"><i class="fas fa-box"></i> Products</a></li>
            <li><a href="/admin/inventory"><i class="fas fa-warehouse"></i> Stock Management</a></li>
            <li><a href="/admin/barcodes"><i class="fas fa-barcode"></i> Barcodes</a></li>
            
            <div class="sidebar-section-title">Customers</div>
            <li><a href="/admin/customers"><i class="fas fa-users"></i> Customers</a></li>
            <li><a href="/admin/loyalty"><i class="fas fa-gift"></i> Loyalty Program</a></li>
            <li><a href="/admin/coupons"><i class="fas fa-tags"></i> Coupons</a></li>
            
            <div class="sidebar-section-title">System</div>
            <li><a href="/admin/users"><i class="fas fa-user-tie"></i> Users</a></li>
            <li><a href="/admin/stores"><i class="fas fa-store"></i> Stores</a></li>
            <li><a href="/admin/settings"><i class="fas fa-cog"></i> Settings</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="admin-content">
        <div class="admin-header">
            <h1><i class="fas fa-chart-line"></i> Dashboard</h1>
            <p>Overview of your POS system performance and statistics</p>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div>
                        <div class="stat-card-value">₹<?php echo number_format($stats['today_sales'], 2); ?></div>
                        <div class="stat-card-label">Today's Sales</div>
                    </div>
                    <div class="stat-card-icon blue">
                        <i class="fas fa-rupee-sign"></i>
                    </div>
                </div>
                <small class="text-muted"><?php echo $stats['today_orders']; ?> orders</small>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <div>
                        <div class="stat-card-value">₹<?php echo number_format($stats['month_sales'], 2); ?></div>
                        <div class="stat-card-label">This Month</div>
                    </div>
                    <div class="stat-card-icon green">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
                <small class="text-muted"><?php echo $stats['month_orders']; ?> orders</small>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <div>
                        <div class="stat-card-value"><?php echo number_format($stats['total_products']); ?></div>
                        <div class="stat-card-label">Total Products</div>
                    </div>
                    <div class="stat-card-icon orange">
                        <i class="fas fa-box"></i>
                    </div>
                </div>
                <small class="text-muted"><?php echo $stats['low_stock_count']; ?> low stock items</small>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <div>
                        <div class="stat-card-value"><?php echo number_format($stats['total_customers']); ?></div>
                        <div class="stat-card-label">Total Customers</div>
                    </div>
                    <div class="stat-card-icon red">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <small class="text-muted"><?php echo $stats['active_sessions']; ?> active sessions</small>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="/pos" class="quick-action-btn">
                <i class="fas fa-cash-register"></i>
                <span>Open POS</span>
            </a>
            <a href="/admin/orders" class="quick-action-btn">
                <i class="fas fa-receipt"></i>
                <span>View Orders</span>
            </a>
            <a href="/admin/products" class="quick-action-btn">
                <i class="fas fa-box"></i>
                <span>Manage Products</span>
            </a>
            <a href="/admin/customers" class="quick-action-btn">
                <i class="fas fa-users"></i>
                <span>Customers</span>
            </a>
            <a href="/admin/reports" class="quick-action-btn">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </a>
            <a href="/admin/settings" class="quick-action-btn">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </div>

        <!-- Recent Orders -->
        <div class="content-card">
            <div class="content-card-header">
                <h3 class="content-card-title"><i class="fas fa-receipt"></i> Recent Orders</h3>
                <a href="/admin/orders" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            
            <?php if (!empty($stats['recent_orders'])): ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Cashier</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['recent_orders'] as $order): ?>
                    <tr>
                        <td><strong>#<?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                        <td><?php echo htmlspecialchars($order['customer_name'] ?? 'Walk-in'); ?></td>
                        <td><?php echo htmlspecialchars($order['cashier_name'] ?? 'N/A'); ?></td>
                        <td><strong>₹<?php echo number_format($order['total'], 2); ?></strong></td>
                        <td><?php echo strtoupper($order['payment_method'] ?? 'N/A'); ?></td>
                        <td>
                            <?php 
                            $statusClass = $order['order_status'] === 'completed' ? 'success' : 
                                         ($order['order_status'] === 'pending' ? 'warning' : 'info');
                            ?>
                            <span class="badge badge-<?php echo $statusClass; ?>">
                                <?php echo ucfirst($order['order_status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p class="text-center text-muted py-4">No orders yet</p>
            <?php endif; ?>
        </div>

        <!-- Top Products -->
        <?php if (!empty($stats['top_products'])): ?>
        <div class="content-card">
            <div class="content-card-header">
                <h3 class="content-card-title"><i class="fas fa-star"></i> Top Selling Products (This Month)</h3>
            </div>
            
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity Sold</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['top_products'] as $product): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($product['product_name']); ?></strong></td>
                        <td><?php echo number_format($product['total_sold']); ?> units</td>
                        <td><strong>₹<?php echo number_format($product['revenue'], 2); ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
