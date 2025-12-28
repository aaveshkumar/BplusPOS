<h2><i class="fas fa-chart-line"></i> Dashboard</h2>
<hr>

<div class="row">
    <!-- Today's Sales -->
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="card-title"><i class="fas fa-rupee-sign"></i> Today's Sales</h6>
                <h3><?php echo formatCurrency($todaySales['total_sales'] ?? 0); ?></h3>
                <small><?php echo ($todaySales['total_orders'] ?? 0); ?> orders</small>
            </div>
        </div>
    </div>
    
    <!-- Low Stock Alert -->
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h6 class="card-title"><i class="fas fa-exclamation-triangle"></i> Low Stock</h6>
                <h3><?php echo count($lowStockProducts); ?></h3>
                <small>Products need restocking</small>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- Recent Orders -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-receipt"></i> Recent Orders</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentOrders)): ?>
                                <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['order_id']; ?></td>
                                    <td><?php echo formatDate($order['order_date'], 'M d, Y g:i A'); ?></td>
                                    <td><?php echo formatCurrency($order['total']); ?></td>
                                    <td><span class="badge bg-success"><?php echo $order['status']; ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No orders yet</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Low Stock Products -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-warning text-white">
                <h5><i class="fas fa-box"></i> Low Stock Alert</h5>
            </div>
            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                <?php if (!empty($lowStockProducts)): ?>
                    <ul class="list-group">
                        <?php foreach ($lowStockProducts as $product): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo htmlspecialchars($product['name']); ?>
                            <span class="badge bg-danger rounded-pill"><?php echo $product['stock_quantity']; ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted">All products are well stocked!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
