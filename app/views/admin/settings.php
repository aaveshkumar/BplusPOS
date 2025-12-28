<?php include __DIR__ . '/_header.php'; ?>

<div class="admin-content">
    <div class="admin-header">
        <h1><i class="fas fa-cog"></i> System Settings</h1>
        <p>Configure your POS system settings and preferences</p>
    </div>

    <?php if (Session::hasFlash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> <?php echo Session::getFlash('success'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if (Session::hasFlash('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i> <?php echo Session::getFlash('error'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <form method="POST" action="/admin/settings/save">
        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
        
        <!-- Tax Settings -->
        <div class="content-card">
            <div class="content-card-header">
                <h3 class="content-card-title"><i class="fas fa-percent"></i> Tax & Pricing Settings</h3>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Default Tax Rate (GST %)</label>
                    <div class="input-group">
                        <input type="number" 
                               class="form-control" 
                               name="tax_rate" 
                               step="0.01"
                               value="<?php echo $settings['tax_rate']['value'] ?? 18.00; ?>">
                        <span class="input-group-text">%</span>
                    </div>
                    <small class="text-muted">Default GST/tax rate applied to sales</small>
                </div>
            </div>
        </div>

        <!-- Inventory Settings -->
        <div class="content-card">
            <div class="content-card-header">
                <h3 class="content-card-title"><i class="fas fa-warehouse"></i> Inventory Settings</h3>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Low Stock Threshold</label>
                    <input type="number" 
                           class="form-control" 
                           name="low_stock_threshold" 
                           value="<?php echo $settings['low_stock_threshold']['value'] ?? 10; ?>">
                    <small class="text-muted">Alert when stock falls below this number</small>
                </div>
            </div>
        </div>

        <!-- Receipt Settings -->
        <div class="content-card">
            <div class="content-card-header">
                <h3 class="content-card-title"><i class="fas fa-print"></i> Receipt Settings</h3>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" 
                               type="checkbox" 
                               name="receipt_print_auto" 
                               value="true"
                               id="receiptPrintAuto"
                               <?php echo ($settings['receipt_print_auto']['value'] ?? false) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="receiptPrintAuto">
                            Auto-print receipt after checkout
                        </label>
                    </div>
                    <small class="text-muted">Automatically send receipt to printer after completing sale</small>
                </div>
            </div>
        </div>

        <!-- Session Settings -->
        <div class="content-card">
            <div class="content-card-header">
                <h3 class="content-card-title"><i class="fas fa-clock"></i> Session Settings</h3>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Session Timeout (hours)</label>
                    <input type="number" 
                           class="form-control" 
                           name="session_timeout" 
                           value="<?php echo $settings['session_timeout']['value'] ?? 8; ?>">
                    <small class="text-muted">Automatically close sessions after this many hours</small>
                </div>
            </div>
        </div>

        <!-- Loyalty Settings -->
        <div class="content-card">
            <div class="content-card-header">
                <h3 class="content-card-title"><i class="fas fa-gift"></i> Loyalty Program Settings</h3>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Loyalty Points per Rupee</label>
                    <input type="number" 
                           class="form-control" 
                           name="loyalty_points_per_rupee" 
                           step="0.1"
                           value="<?php echo $settings['loyalty_points_per_rupee']['value'] ?? 1; ?>">
                    <small class="text-muted">Points earned per rupee spent</small>
                </div>
            </div>
        </div>

        <!-- Advanced Settings -->
        <div class="content-card">
            <div class="content-card-header">
                <h3 class="content-card-title"><i class="fas fa-sliders-h"></i> Advanced Settings</h3>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" 
                               type="checkbox" 
                               name="offline_mode_enabled" 
                               value="true"
                               id="offlineModeEnabled"
                               <?php echo ($settings['offline_mode_enabled']['value'] ?? true) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="offlineModeEnabled">
                            Enable Offline Mode
                        </label>
                    </div>
                    <small class="text-muted">Allow POS to work without internet connection</small>
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="content-card">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save"></i> Save Settings
            </button>
            <a href="/admin" class="btn btn-outline-secondary btn-lg ms-2">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
