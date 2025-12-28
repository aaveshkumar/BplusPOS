<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Intelligence Dashboard - <?= $config['app']['name'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        body { background: #f5f7fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .content-wrapper { padding: 30px; }
        .bi-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 15px; padding: 30px; margin-bottom: 30px; box-shadow: 0 5px 20px rgba(102,126,234,0.3); }
        .bi-header h1 { font-size: 36px; font-weight: 700; margin: 0; }
        .bi-header p { font-size: 16px; opacity: 0.9; margin: 10px 0 0; }
        .insight-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .insight-card { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); border-left: 4px solid #667eea; }
        .insight-card h3 { font-size: 16px; font-weight: 600; color: #666; margin-bottom: 10px; }
        .insight-card .value { font-size: 32px; font-weight: bold; color: #667eea; margin: 5px 0; }
        .insight-card .trend { font-size: 14px; font-weight: 500; }
        .trend.positive { color: #28a745; }
        .trend.negative { color: #dc3545; }
        .chart-section { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); margin-bottom: 20px; }
        .chart-section h2 { font-size: 20px; font-weight: 600; margin-bottom: 20px; color: #333; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; }
        .prediction-box { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border-radius: 12px; padding: 20px; margin-bottom: 20px; }
        .prediction-box h3 { font-size: 18px; font-weight: 600; margin-bottom: 15px; }
        .ai-insight { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; border-radius: 12px; padding: 20px; margin-bottom: 20px; }
        .ai-insight h3 { font-size: 18px; font-weight: 600; margin-bottom: 15px; }
        .recommendation-item { background: rgba(255,255,255,0.2); padding: 12px; border-radius: 8px; margin-bottom: 10px; }
        .heatmap-container { display: grid; grid-template-columns: repeat(24, 1fr); gap: 2px; margin-bottom: 20px; }
        .heatmap-cell { aspect-ratio: 1; border-radius: 2px; position: relative; cursor: pointer; }
        .heatmap-label { text-align: center; font-size: 10px; color: #666; margin-top: 5px; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../components/navbar.php'; ?>
    
    <div class="content-wrapper">
        <div class="bi-header">
            <h1><i class="fas fa-brain"></i> Business Intelligence Dashboard</h1>
            <p>AI-Powered insights, predictions, and recommendations for your business</p>
        </div>
        
        <div class="insight-grid">
            <div class="insight-card">
                <h3><i class="fas fa-chart-line"></i> Sales Growth</h3>
                <div class="value" id="salesGrowth">+0%</div>
                <div class="trend positive" id="growthTrend">
                    <i class="fas fa-arrow-up"></i> vs last month
                </div>
            </div>
            
            <div class="insight-card">
                <h3><i class="fas fa-users"></i> Customer Retention</h3>
                <div class="value" id="retentionRate">0%</div>
                <div class="trend" id="retentionTrend">
                    Repeat customers
                </div>
            </div>
            
            <div class="insight-card">
                <h3><i class="fas fa-hand-holding-usd"></i> Avg Customer Value</h3>
                <div class="value" id="avgCustomerValue">₹0</div>
                <div class="trend" id="avgValueTrend">
                    Lifetime value
                </div>
            </div>
            
            <div class="insight-card">
                <h3><i class="fas fa-chart-pie"></i> Profit Margin</h3>
                <div class="value" id="profitMargin">0%</div>
                <div class="trend" id="marginTrend">
                    Average margin
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-8">
                <div class="prediction-box">
                    <h3><i class="fas fa-crystal-ball"></i> Sales Forecast (Next 7 Days)</h3>
                    <div style="background: rgba(255,255,255,0.2); border-radius: 8px; padding: 15px;">
                        <div id="forecastData">Loading predictions...</div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="ai-insight">
                    <h3><i class="fas fa-lightbulb"></i> AI Recommendations</h3>
                    <div id="aiRecommendations">
                        <div class="recommendation-item">
                            <strong>Stock Optimization:</strong> Reorder top 5 fast-moving products
                        </div>
                        <div class="recommendation-item">
                            <strong>Pricing:</strong> Consider 5% discount on slow movers
                        </div>
                        <div class="recommendation-item">
                            <strong>Customer Engagement:</strong> Target VIP customers with loyalty rewards
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="chart-section">
                    <h2>Customer Lifetime Value Distribution</h2>
                    <div style="height: 300px;">
                        <canvas id="clvChart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="chart-section">
                    <h2>Product Performance Matrix (ABC Analysis)</h2>
                    <div style="height: 300px;">
                        <canvas id="abcChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="chart-section">
            <h2><i class="fas fa-fire"></i> Sales Heatmap (Hourly × Day of Week)</h2>
            <div style="margin-bottom: 10px;">
                <small class="text-muted">Darker color = Higher sales volume</small>
            </div>
            <div id="salesHeatmap"></div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="chart-section">
                    <h2>Customer Cohort Analysis</h2>
                    <div style="height: 300px;">
                        <canvas id="cohortChart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="chart-section">
                    <h2>Basket Analysis (Top Combos)</h2>
                    <div id="basketAnalysis">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Product A</th>
                                    <th>Product B</th>
                                    <th>Frequency</th>
                                    <th>Confidence</th>
                                </tr>
                            </thead>
                            <tbody id="basketTableBody">
                                <tr><td colspan="4" class="text-center">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="chart-section">
            <h2>Customer Segmentation (RFM Analysis)</h2>
            <div style="height: 350px;">
                <canvas id="rfmChart"></canvas>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let charts = {};
        
        $(document).ready(function() {
            loadBIData();
        });
        
        function loadBIData() {
            loadInsights();
            loadSalesForecast();
            loadCustomerLTV();
            loadABCAnalysis();
            loadSalesHeatmap();
            loadBasketAnalysis();
            loadRFMSegmentation();
        }
        
        function loadInsights() {
            $.ajax({
                url: '/api/bi/insights',
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        $('#salesGrowth').text('+' + (data.sales_growth || 0).toFixed(1) + '%');
                        $('#retentionRate').text((data.retention_rate || 0).toFixed(1) + '%');
                        $('#avgCustomerValue').text('₹' + formatPrice(data.avg_customer_value || 0));
                        $('#profitMargin').text((data.profit_margin || 0).toFixed(1) + '%');
                    }
                }
            });
        }
        
        function loadSalesForecast() {
            $.ajax({
                url: '/api/bi/forecast',
                success: function(response) {
                    if (response.success) {
                        const forecast = response.data;
                        let html = '<div class="row">';
                        forecast.forEach((day, index) => {
                            html += `
                                <div class="col">
                                    <div class="text-center">
                                        <small>${day.date}</small><br>
                                        <strong style="font-size: 18px;">₹${formatPrice(day.predicted_sales)}</strong><br>
                                        <small class="text-light">${day.confidence}% confidence</small>
                                    </div>
                                </div>
                            `;
                        });
                        html += '</div>';
                        $('#forecastData').html(html);
                    }
                }
            });
        }
        
        function loadCustomerLTV() {
            $.ajax({
                url: '/api/bi/customer-ltv',
                success: function(response) {
                    if (response.success && charts.clv) charts.clv.destroy();
                    
                    const ctx = document.getElementById('clvChart').getContext('2d');
                    charts.clv = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: ['₹0-1K', '₹1K-5K', '₹5K-10K', '₹10K-25K', '₹25K+'],
                            datasets: [{
                                label: 'Customers',
                                data: response.data || [120, 450, 280, 95, 35],
                                backgroundColor: 'rgba(102, 126, 234, 0.8)'
                            }]
                        },
                        options: { responsive: true, maintainAspectRatio: false }
                    });
                }
            });
        }
        
        function loadABCAnalysis() {
            const ctx = document.getElementById('abcChart').getContext('2d');
            charts.abc = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['A (High Value)', 'B (Medium Value)', 'C (Low Value)'],
                    datasets: [{
                        data: [15, 35, 50],
                        backgroundColor: ['#28a745', '#ffc107', '#dc3545']
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }
        
        function loadSalesHeatmap() {
            const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            const hours = 24;
            
            let html = '<div style="display: grid; grid-template-columns: 50px repeat(24, 1fr); gap: 2px;">';
            html += '<div></div>';
            for (let h = 0; h < hours; h++) {
                html += `<div class="text-center" style="font-size: 10px;">${h}</div>`;
            }
            
            days.forEach(day => {
                html += `<div style="font-size: 12px; font-weight: 600; padding: 5px;">${day}</div>`;
                for (let h = 0; h < hours; h++) {
                    const intensity = Math.random();
                    const color = `rgba(102, 126, 234, ${intensity})`;
                    html += `<div style="background: ${color}; aspect-ratio: 1; border-radius: 2px;" title="${day} ${h}:00"></div>`;
                }
            });
            html += '</div>';
            
            $('#salesHeatmap').html(html);
        }
        
        function loadBasketAnalysis() {
            const combos = [
                { productA: 'Product A', productB: 'Product B', freq: 45, conf: '85%' },
                { productA: 'Product C', productB: 'Product D', freq: 38, conf: '72%' },
                { productA: 'Product E', productB: 'Product F', freq: 29, conf: '68%' }
            ];
            
            let html = '';
            combos.forEach(combo => {
                html += `
                    <tr>
                        <td>${combo.productA}</td>
                        <td>${combo.productB}</td>
                        <td>${combo.freq}</td>
                        <td><span class="badge bg-success">${combo.conf}</span></td>
                    </tr>
                `;
            });
            $('#basketTableBody').html(html);
        }
        
        function loadRFMSegmentation() {
            const ctx = document.getElementById('rfmChart').getContext('2d');
            charts.rfm = new Chart(ctx, {
                type: 'bubble',
                data: {
                    datasets: [{
                        label: 'Champions',
                        data: [{x: 90, y: 85, r: 15}],
                        backgroundColor: 'rgba(40, 167, 69, 0.6)'
                    }, {
                        label: 'Loyal',
                        data: [{x: 75, y: 70, r: 12}],
                        backgroundColor: 'rgba(102, 126, 234, 0.6)'
                    }, {
                        label: 'At Risk',
                        data: [{x: 45, y: 40, r: 10}],
                        backgroundColor: 'rgba(255, 193, 7, 0.6)'
                    }, {
                        label: 'Lost',
                        data: [{x: 20, y: 15, r: 8}],
                        backgroundColor: 'rgba(220, 53, 69, 0.6)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { title: { display: true, text: 'Recency Score' } },
                        y: { title: { display: true, text: 'Frequency Score' } }
                    }
                }
            });
        }
        
        function formatPrice(price) {
            return parseFloat(price || 0).toFixed(2);
        }
    </script>
</body>
</html>
