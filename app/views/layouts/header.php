<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo generateCsrfToken(); ?>">
    <title><?php echo $title ?? 'B-Plus POS'; ?></title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #64748b;
            --success-color: #10b981;
            --danger-color: #ef4444;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
        }
        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color) !important;
        }
        .sidebar {
            min-height: 100vh;
            background-color: #1e293b;
            color: #fff;
        }
        .sidebar a {
            color: #cbd5e1;
            text-decoration: none;
            padding: 12px 20px;
            display: block;
            transition: all 0.3s;
        }
        .sidebar a:hover {
            background-color: #334155;
            color: #fff;
        }
        .sidebar a.active {
            background-color: var(--primary-color);
            color: #fff;
        }
        .main-content {
            padding: 20px;
        }
        .card {
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            border: none;
            margin-bottom: 20px;
        }
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
        }
    </style>
</head>
<body>
    <?php if (isLoggedIn()): ?>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-md-block sidebar px-0">
                <div class="p-3">
                    <h4 class="text-white mb-4">
                        <i class="fas fa-cash-register"></i> B-Plus POS
                    </h4>
                    <hr class="bg-secondary">
                    <div class="mb-3">
                        <small class="text-muted">Logged in as:</small><br>
                        <strong><?php echo getCurrentUser()['name'] ?? 'User'; ?></strong><br>
                        <small class="text-muted"><?php echo ucfirst(getCurrentUser()['role'] ?? 'cashier'); ?></small>
                    </div>
                    <hr class="bg-secondary">
                </div>
                <div class="position-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a href="/pos" class="<?php echo ($_SERVER['REQUEST_URI'] == '/pos') ? 'active' : ''; ?>">
                                <i class="fas fa-shopping-cart"></i> POS
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/dashboard" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/dashboard') !== false) ? 'active' : ''; ?>">
                                <i class="fas fa-chart-line"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/products">
                                <i class="fas fa-box"></i> Products
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/customers">
                                <i class="fas fa-users"></i> Customers
                            </a>
                        </li>
                        <?php if (hasPermission('view_reports') || hasPermission('all')): ?>
                        <li class="nav-item">
                            <a href="/dashboard/reports">
                                <i class="fas fa-file-alt"></i> Reports
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (hasPermission('manage_system') || hasPermission('all')): ?>
                        <li class="nav-item">
                            <a href="/admin/options" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/options') !== false) ? 'active' : ''; ?>">
                                <i class="fas fa-list-check"></i> Options
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item mt-4">
                            <a href="/logout">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-10 ms-sm-auto main-content">
                <?php 
                // Display flash messages
                if (Session::hasFlash('success')): 
                    $flash = Session::getFlash('success');
                ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $flash['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <?php 
                if (Session::hasFlash('error')): 
                    $flash = Session::getFlash('error');
                ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $flash['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
    <?php endif; ?>
