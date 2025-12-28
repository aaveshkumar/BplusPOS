<!-- Admin Navbar Component -->
<nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="/admin">
            <i class="fas fa-cog me-2"></i>
            <strong>B-Plus Admin</strong>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/pos">
                        <i class="fas fa-cash-register"></i> POS
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin">
                        <i class="fas fa-chart-line"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="reportsDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-chart-bar"></i> Reports
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/admin/sales-analytics"><i class="fas fa-chart-line"></i> Sales Analytics</a></li>
                        <li><a class="dropdown-item" href="/admin/gst-reports"><i class="fas fa-file-invoice"></i> GST Reports</a></li>
                        <li><a class="dropdown-item" href="/admin/bi-dashboard"><i class="fas fa-brain"></i> Business Intelligence</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <span class="nav-link">
                        <i class="fas fa-user-shield"></i> <?php echo htmlspecialchars(Session::get('username', 'Admin')); ?>
                    </span>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/logout">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
