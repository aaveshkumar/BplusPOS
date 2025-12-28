<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-5">
            <div class="card shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h2><i class="fas fa-cash-register text-primary"></i></h2>
                        <h3 class="mb-0">B-Plus POS</h3>
                        <p class="text-muted">Point of Sale System</p>
                    </div>
                    
                    <?php 
                    if (Session::hasFlash('error')): 
                        $flash = Session::getFlash('error');
                    ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $flash['message']; ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="/login">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="username" name="username" autocomplete="username" required autofocus>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" autocomplete="current-password" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                    </form>
                    
                    <div class="mt-4 text-center">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> Use your WooCommerce admin credentials
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-3">
                <small class="text-muted">B-Plus POS v1.0 &copy; 2025</small>
            </div>
        </div>
    </div>
</div>
