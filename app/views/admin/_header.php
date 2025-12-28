<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Admin'; ?> - B-Plus POS</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; background: #f5f7fa; }
        .admin-navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; box-shadow: 0 2px 15px rgba(0,0,0,0.1); position: fixed; top: 0; left: 0; right: 0; z-index: 1000; height: 70px; }
        .admin-navbar-content { display: flex; justify-content: space-between; align-items: center; max-width: 1400px; margin: 0 auto; }
        .admin-brand { display: flex; align-items: center; gap: 12px; font-size: 24px; font-weight: 700; }
        .admin-user-section { display: flex; align-items: center; gap: 20px; }
        .admin-user-info { background: rgba(255,255,255,0.15); padding: 8px 18px; border-radius: 25px; }
        .admin-sidebar { position: fixed; top: 70px; left: 0; width: 260px; height: calc(100vh - 70px); background: white; border-right: 1px solid #e1e8ed; overflow-y: auto; padding: 20px 0; }
        .sidebar-menu { list-style: none; padding: 0; margin: 0; }
        .sidebar-menu li { margin: 0; }
        .sidebar-menu a { display: flex; align-items: center; gap: 12px; padding: 14px 25px; color: #5a6c7d; text-decoration: none; transition: all 0.2s; font-weight: 500; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: #f8f9fa; color: #667eea; border-left: 3px solid #667eea; }
        .sidebar-menu i { width: 20px; text-align: center; font-size: 18px; }
        .sidebar-section-title { padding: 20px 25px 10px; font-size: 11px; font-weight: 700; color: #95a5a6; text-transform: uppercase; letter-spacing: 0.5px; }
        .admin-content { margin-left: 260px; margin-top: 70px; padding: 30px; min-height: calc(100vh - 70px); }
        .admin-header { margin-bottom: 30px; }
        .admin-header h1 { font-size: 28px; font-weight: 700; color: #2c3e50; margin-bottom: 5px; }
        .admin-header p { color: #7f8c8d; margin: 0; }
        .content-card { background: white; border-radius: 12px; border: 1px solid #e8e8e8; padding: 24px; margin-bottom: 20px; }
        .content-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #f0f0f0; }
        .content-card-title { font-size: 18px; font-weight: 700; color: #2c3e50; margin: 0; }
        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table th { background: #f8f9fa; padding: 12px 16px; text-align: left; font-weight: 600; font-size: 13px; color: #5a6c7d; border-bottom: 2px solid #e1e8ed; }
        .admin-table td { padding: 14px 16px; border-bottom: 1px solid #f0f0f0; color: #2c3e50; }
        .admin-table tr:hover { background: #f8f9fa; }
        .badge { padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
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
                    <?php echo htmlspecialchars(Session::get('username', 'Admin')); ?>
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
            <li><a href="/admin"><i class="fas fa-chart-line"></i> Dashboard</a></li>
            
            <div class="sidebar-section-title">Sales & Orders</div>
            <li><a href="/admin/orders"><i class="fas fa-receipt"></i> Orders</a></li>
            <li><a href="/admin/sessions"><i class="fas fa-clock"></i> Cashier Sessions</a></li>
            <li><a href="/admin/sales-analytics"><i class="fas fa-chart-bar"></i> Sales Analytics</a></li>
            <li><a href="/admin/reports"><i class="fas fa-file-alt"></i> Reports</a></li>
            <li><a href="/admin/returns"><i class="fas fa-undo"></i> Returns & Exchange</a></li>
            
            <div class="sidebar-section-title">Inventory</div>
            <li><a href="/admin/products"><i class="fas fa-box"></i> Products</a></li>
            <li><a href="/admin/inventory"><i class="fas fa-warehouse"></i> Inventory Alerts</a></li>
            <li><a href="/admin/barcodes"><i class="fas fa-barcode"></i> Barcode Management</a></li>
            
            <div class="sidebar-section-title">Customers</div>
            <li><a href="/admin/customers"><i class="fas fa-users"></i> Customers</a></li>
            <li><a href="/admin/loyalty"><i class="fas fa-gift"></i> Loyalty Program</a></li>
            
            <div class="sidebar-section-title">Compliance</div>
            <li><a href="/admin/tax-rules"><i class="fas fa-percentage"></i> Tax Rules</a></li>
            <li><a href="/admin/gst-reports"><i class="fas fa-file-invoice"></i> GST Reports</a></li>
            <li><a href="/admin/e-invoice"><i class="fas fa-file-invoice-dollar"></i> E-Invoicing</a></li>
            
            <div class="sidebar-section-title">Multi-Store</div>
            <li><a href="/admin/stores"><i class="fas fa-store"></i> Store Management</a></li>
            <li><a href="/admin/store-performance"><i class="fas fa-chart-pie"></i> Store Performance</a></li>
            
            <div class="sidebar-section-title">Automation</div>
            <li><a href="/admin/whatsapp"><i class="fab fa-whatsapp"></i> WhatsApp Notifications</a></li>
            <li><a href="/admin/automation"><i class="fas fa-robot"></i> Workflow Automation</a></li>
            <li><a href="/admin/bi-dashboard"><i class="fas fa-brain"></i> Business Intelligence</a></li>
            
            <div class="sidebar-section-title">System</div>
            <li><a href="/admin/users"><i class="fas fa-user-tie"></i> Users</a></li>
            <li><a href="/admin/settings"><i class="fas fa-cog"></i> Settings</a></li>
        </ul>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
