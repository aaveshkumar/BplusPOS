<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Analytics & Reports - <?= $config['app']['name'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        body {
            background: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .content-wrapper {
            padding: 30px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .stat-card h2 {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
            margin: 10px 0;
        }
        
        .stat-card .change {
            font-size: 14px;
            font-weight: 500;
        }
        
        .change.positive { color: #28a745; }
        .change.negative { color: #dc3545; }
        
        .chart-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        
        .chart-card h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
        }
        
        .filter-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .quick-filter-btn {
            margin: 5px;
        }
        
        .chart-container {
            position: relative;
            height: 350px;
        }
        
        .top-products-list {
            list-style: none;
            padding: 0;
        }
        
        .top-products-list li {
            padding: 12px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .top-products-list li:last-child {
            border-bottom: none;
        }
        
        .product-rank {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../components/navbar.php'; ?>
    
    <div class="content-wrapper">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-chart-line"></i> Sales Analytics & Reports</h1>
            <button class="btn btn-primary btn-lg" onclick="window.location.href='/pos'">
                <i class="fas fa-arrow-left"></i> Back to POS
            </button>
        </div>
        
        <div class="filter-section">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <label>Start Date</label>
                    <input type="date" class="form-control" id="startDate" value="<?= date('Y-m-01') ?>">
                </div>
                <div class="col-md-3">
                    <label>End Date</label>
                    <input type="date" class="form-control" id="endDate" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-6">
                    <label>Quick Filters</label><br>
                    <button class="btn btn-sm btn-outline-primary quick-filter-btn" onclick="setDateRange('today')">Today</button>
                    <button class="btn btn-sm btn-outline-primary quick-filter-btn" onclick="setDateRange('yesterday')">Yesterday</button>
                    <button class="btn btn-sm btn-outline-primary quick-filter-btn" onclick="setDateRange('week')">This Week</button>
                    <button class="btn btn-sm btn-outline-primary quick-filter-btn" onclick="setDateRange('month')">This Month</button>
                    <button class="btn btn-sm btn-outline-primary quick-filter-btn" onclick="setDateRange('year')">This Year</button>
                    <button class="btn btn-sm btn-success quick-filter-btn" onclick="loadAllReports()">
                        <i class="fas fa-sync"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <p class="mb-0">Total Sales</p>
                <h2 id="totalSales">₹0</h2>
                <div class="change" id="salesChange">
                    <i class="fas fa-arrow-up"></i> 0% vs yesterday
                </div>
            </div>
            
            <div class="stat-card">
                <p class="mb-0">Total Orders</p>
                <h2 id="totalOrders">0</h2>
                <div class="change" id="ordersChange">
                    <i class="fas fa-arrow-up"></i> 0% vs yesterday
                </div>
            </div>
            
            <div class="stat-card">
                <p class="mb-0">Avg Order Value</p>
                <h2 id="avgOrderValue">₹0</h2>
                <div class="change" id="aovChange">
                    <i class="fas fa-arrow-up"></i> 0% vs yesterday
                </div>
            </div>
            
            <div class="stat-card">
                <p class="mb-0">Total Discounts</p>
                <h2 id="totalDiscounts">₹0</h2>
                <div class="change" id="discountChange">
                    <i class="fas fa-arrow-down"></i> 0% of sales
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-8">
                <div class="chart-card">
                    <h3>Sales Trend</h3>
                    <div class="chart-container">
                        <canvas id="salesTrendChart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="chart-card">
                    <h3>Payment Methods</h3>
                    <div class="chart-container">
                        <canvas id="paymentMethodsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="chart-card">
                    <h3>Hourly Sales Distribution</h3>
                    <div class="chart-container">
                        <canvas id="hourlySalesChart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="chart-card">
                    <h3>Top 10 Products</h3>
                    <ul class="top-products-list" id="topProductsList">
                        <li class="text-center text-muted">Loading...</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="chart-card">
                    <h3>Sales by Category</h3>
                    <div class="chart-container">
                        <canvas id="categorySalesChart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="chart-card">
                    <h3>Customer Analysis</h3>
                    <div class="chart-container">
                        <canvas id="customerAnalysisChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let charts = {};
        
        $(document).ready(function() {
            loadAllReports();
        });
        
        function setDateRange(range) {
            const today = new Date();
            let startDate = new Date();
            
            switch(range) {
                case 'today':
                    startDate = today;
                    break;
                case 'yesterday':
                    startDate.setDate(today.getDate() - 1);
                    today.setDate(today.getDate() - 1);
                    break;
                case 'week':
                    startDate.setDate(today.getDate() - today.getDay());
                    break;
                case 'month':
                    startDate.setDate(1);
                    break;
                case 'year':
                    startDate = new Date(today.getFullYear(), 0, 1);
                    break;
            }
            
            $('#startDate').val(formatDate(startDate));
            $('#endDate').val(formatDate(today));
            
            loadAllReports();
        }
        
        function loadAllReports() {
            const startDate = $('#startDate').val() + ' 00:00:00';
            const endDate = $('#endDate').val() + ' 23:59:59';
            
            loadSalesSummary(startDate, endDate);
            loadSalesTrend(startDate, endDate);
            loadPaymentMethods(startDate, endDate);
            loadHourlySales(startDate, endDate);
            loadTopProducts(startDate, endDate);
            loadCategorySales(startDate, endDate);
            loadCustomerAnalysis(startDate, endDate);
        }
        
        function loadSalesSummary(startDate, endDate) {
            $.ajax({
                url: '/api/reports/summary',
                data: { start_date: startDate, end_date: endDate },
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        $('#totalSales').text('₹' + formatPrice(data.total_sales || 0));
                        $('#totalOrders').text(data.total_orders || 0);
                        $('#avgOrderValue').text('₹' + formatPrice(data.average_order_value || 0));
                        $('#totalDiscounts').text('₹' + formatPrice(data.total_discounts || 0));
                    }
                }
            });
        }
        
        function loadSalesTrend(startDate, endDate) {
            $.ajax({
                url: '/api/reports/trend',
                data: { start_date: startDate, end_date: endDate, group_by: 'day' },
                success: function(response) {
                    if (response.success) {
                        renderSalesTrendChart(response.data);
                    }
                }
            });
        }
        
        function loadPaymentMethods(startDate, endDate) {
            $.ajax({
                url: '/api/reports/payment-methods',
                data: { start_date: startDate, end_date: endDate },
                success: function(response) {
                    if (response.success) {
                        renderPaymentMethodsChart(response.data);
                    }
                }
            });
        }
        
        function loadHourlySales(startDate, endDate) {
            $.ajax({
                url: '/api/reports/hourly',
                data: { start_date: startDate, end_date: endDate },
                success: function(response) {
                    if (response.success) {
                        renderHourlySalesChart(response.data);
                    }
                }
            });
        }
        
        function loadTopProducts(startDate, endDate) {
            $.ajax({
                url: '/api/reports/top-products',
                data: { start_date: startDate, end_date: endDate, limit: 10 },
                success: function(response) {
                    if (response.success) {
                        renderTopProducts(response.data);
                    }
                }
            });
        }
        
        function loadCategorySales(startDate, endDate) {
            $.ajax({
                url: '/api/reports/category-sales',
                data: { start_date: startDate, end_date: endDate },
                success: function(response) {
                    if (response.success) {
                        renderCategorySalesChart(response.data);
                    }
                }
            });
        }
        
        function loadCustomerAnalysis(startDate, endDate) {
            $.ajax({
                url: '/api/reports/customer-stats',
                data: { start_date: startDate, end_date: endDate },
                success: function(response) {
                    if (response.success) {
                        renderCustomerAnalysisChart(response.data);
                    }
                }
            });
        }
        
        function renderSalesTrendChart(data) {
            if (charts.salesTrend) charts.salesTrend.destroy();
            
            const ctx = document.getElementById('salesTrendChart').getContext('2d');
            charts.salesTrend = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.map(d => d.period),
                    datasets: [{
                        label: 'Total Sales (₹)',
                        data: data.map(d => d.total_sales),
                        borderColor: 'rgb(102, 126, 234)',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }
        
        function renderPaymentMethodsChart(data) {
            if (charts.paymentMethods) charts.paymentMethods.destroy();
            
            const ctx = document.getElementById('paymentMethodsChart').getContext('2d');
            charts.paymentMethods = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.map(d => d.payment_method.toUpperCase()),
                    datasets: [{
                        data: data.map(d => d.total_sales),
                        backgroundColor: [
                            'rgb(102, 126, 234)',
                            'rgb(118, 75, 162)',
                            'rgb(255, 159, 64)',
                            'rgb(75, 192, 192)',
                            'rgb(153, 102, 255)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
        
        function renderHourlySalesChart(data) {
            if (charts.hourlySales) charts.hourlySales.destroy();
            
            const ctx = document.getElementById('hourlySalesChart').getContext('2d');
            charts.hourlySales = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(d => d.hour + ':00'),
                    datasets: [{
                        label: 'Sales (₹)',
                        data: data.map(d => d.total_sales),
                        backgroundColor: 'rgba(102, 126, 234, 0.8)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }
        
        function renderTopProducts(products) {
            const list = $('#topProductsList');
            list.empty();
            
            if (products.length === 0) {
                list.html('<li class="text-center text-muted">No data available</li>');
                return;
            }
            
            products.forEach((product, index) => {
                list.append(`
                    <li>
                        <div class="d-flex align-items-center">
                            <div class="product-rank">${index + 1}</div>
                            <div>
                                <strong>${product.product_name}</strong><br>
                                <small class="text-muted">Qty: ${product.total_quantity}</small>
                            </div>
                        </div>
                        <div class="text-end">
                            <strong>₹${formatPrice(product.total_revenue)}</strong><br>
                            <small class="text-muted">${product.order_count} orders</small>
                        </div>
                    </li>
                `);
            });
        }
        
        function renderCategorySalesChart(data) {
            if (charts.categorySales) charts.categorySales.destroy();
            
            const ctx = document.getElementById('categorySalesChart').getContext('2d');
            charts.categorySales = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(d => d.category_name || 'Uncategorized'),
                    datasets: [{
                        label: 'Revenue (₹)',
                        data: data.map(d => d.total_revenue),
                        backgroundColor: 'rgba(118, 75, 162, 0.8)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }
        
        function renderCustomerAnalysisChart(data) {
            if (charts.customerAnalysis) charts.customerAnalysis.destroy();
            
            const ctx = document.getElementById('customerAnalysisChart').getContext('2d');
            charts.customerAnalysis = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Registered Customers', 'Walk-in'],
                    datasets: [{
                        data: [data.registered_customer_orders, data.walk_in_orders],
                        backgroundColor: [
                            'rgb(102, 126, 234)',
                            'rgb(255, 159, 64)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
        
        function formatPrice(price) {
            return parseFloat(price || 0).toFixed(2);
        }
        
        function formatDate(date) {
            const d = new Date(date);
            return d.toISOString().split('T')[0];
        }
    </script>
</body>
</html>
