<h2><i class="fas fa-box"></i> Products</h2>
<hr>

<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0">Product List</h5>
            </div>
            <div class="col-md-6">
                <input type="text" id="searchProduct" class="form-control" placeholder="Search products...">
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>SKU</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo $product['sku'] ?? 'N/A'; ?></td>
                            <td><?php echo formatCurrency($product['price']); ?></td>
                            <td><?php echo $product['stock_quantity'] ?? '0'; ?></td>
                            <td>
                                <?php if ($product['stock_status'] == 'instock'): ?>
                                    <span class="badge bg-success">In Stock</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Out of Stock</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">No products found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($totalPages > 1): ?>
        <nav style="margin-top: 20px;">
            <ul class="pagination" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(40px, 1fr)); gap: 4px; max-width: 100%; padding: 0 !important; margin: 0 !important; list-style: none;">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>" style="display: inline-block; width: 100%;">
                    <a class="page-link" href="/products?page=<?php echo $i; ?>" style="display: block; padding: 6px 8px; text-align: center; font-size: 12px;"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>
