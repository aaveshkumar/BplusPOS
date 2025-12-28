<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="fas fa-list-check text-primary"></i> System Options & Features</h2>
                    <p class="text-muted">Complete list of all available features with role-based access control</p>
                </div>
                <div>
                    <span class="badge bg-success fs-6">30 Total Features</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-user-shield"></i> Role Legend</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <span class="badge bg-danger me-2"><i class="fas fa-crown"></i> Admin</span>
                            <small>Full system access</small>
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-primary me-2"><i class="fas fa-user-tie"></i> Manager</span>
                            <small>Most operational features</small>
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-success me-2"><i class="fas fa-user"></i> Cashier</span>
                            <small>POS operations only</small>
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-warning text-dark me-2"><i class="fas fa-boxes"></i> Stock Manager</span>
                            <small>Inventory focus</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php foreach ($groupedFeatures as $category => $features): ?>
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-3">
                <i class="fas fa-folder-open text-secondary"></i> <?php echo htmlspecialchars($category); ?>
                <span class="badge bg-secondary"><?php echo count($features); ?> features</span>
            </h4>
            
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th width="5%">#</th>
                            <th width="25%">Feature Name</th>
                            <th width="35%">Description</th>
                            <th width="15%">URL</th>
                            <th width="20%">Access Roles</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($features as $feature): ?>
                        <tr>
                            <td class="text-center"><?php echo $feature['id']; ?></td>
                            <td>
                                <i class="fas <?php echo $feature['icon']; ?> text-primary me-2"></i>
                                <strong><?php echo htmlspecialchars($feature['name']); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($feature['description']); ?></td>
                            <td>
                                <a href="<?php echo $feature['url']; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="fas fa-external-link-alt"></i> Visit
                                </a>
                                <code class="ms-2"><?php echo htmlspecialchars($feature['url']); ?></code>
                            </td>
                            <td>
                                <?php if ($feature['admin']): ?>
                                    <span class="badge bg-danger mb-1"><i class="fas fa-crown"></i> Admin</span>
                                <?php endif; ?>
                                <?php if ($feature['manager']): ?>
                                    <span class="badge bg-primary mb-1"><i class="fas fa-user-tie"></i> Manager</span>
                                <?php endif; ?>
                                <?php if ($feature['cashier']): ?>
                                    <span class="badge bg-success mb-1"><i class="fas fa-user"></i> Cashier</span>
                                <?php endif; ?>
                                <?php if ($feature['stock_manager']): ?>
                                    <span class="badge bg-warning text-dark mb-1"><i class="fas fa-boxes"></i> Stock Mgr</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body">
                    <h5><i class="fas fa-info-circle text-info"></i> Quick Statistics</h5>
                    <div class="row text-center mt-3">
                        <div class="col-md-3">
                            <div class="card border-danger">
                                <div class="card-body">
                                    <h3 class="text-danger">
                                        <?php 
                                        $adminCount = count(array_filter($features, function($f) { return $f['admin']; }));
                                        echo $adminCount;
                                        ?>
                                    </h3>
                                    <small class="text-muted">Admin Features</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-primary">
                                <div class="card-body">
                                    <h3 class="text-primary">
                                        <?php 
                                        $managerCount = count(array_filter($features, function($f) { return $f['manager']; }));
                                        echo $managerCount;
                                        ?>
                                    </h3>
                                    <small class="text-muted">Manager Features</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-success">
                                <div class="card-body">
                                    <h3 class="text-success">
                                        <?php 
                                        $cashierCount = count(array_filter($features, function($f) { return $f['cashier']; }));
                                        echo $cashierCount;
                                        ?>
                                    </h3>
                                    <small class="text-muted">Cashier Features</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-warning">
                                <div class="card-body">
                                    <h3 class="text-warning">
                                        <?php 
                                        $stockCount = count(array_filter($features, function($f) { return $f['stock_manager']; }));
                                        echo $stockCount;
                                        ?>
                                    </h3>
                                    <small class="text-muted">Stock Manager Features</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <h6><i class="fas fa-lightbulb"></i> System Information</h6>
                <ul class="mb-0">
                    <li><strong>Total Features:</strong> 29 modules</li>
                    <li><strong>Integration:</strong> Full WooCommerce integration via REST API and MySQL</li>
                    <li><strong>Technologies:</strong> PHP, MySQL, Bootstrap, JavaScript, IndexedDB</li>
                    <li><strong>Special Features:</strong> AI Business Intelligence, WhatsApp Integration, Offline Mode, Workflow Automation, Custom Tax Rules</li>
                    <li><strong>Version:</strong> B-Plus POS v1.0.0</li>
                    <li><strong>Last Updated:</strong> <?php echo date('F d, Y'); ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
    .table td {
        vertical-align: middle;
    }
    .badge {
        font-weight: 500;
        padding: 5px 10px;
    }
    code {
        font-size: 11px;
        color: #666;
    }
    .card {
        transition: transform 0.2s;
    }
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
</style>
