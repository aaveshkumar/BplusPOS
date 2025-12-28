<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management - <?= $config['app']['name'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            padding: 1rem 0;
        }

        .content-wrapper {
            padding: 30px 20px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .page-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #2c3e50;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-left: 4px solid #667eea;
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        }

        .stat-card .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #667eea;
            margin: 10px 0;
        }

        .stat-card .stat-label {
            font-size: 13px;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .search-section {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .search-box {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .search-input-wrapper {
            flex: 1;
            min-width: 250px;
            display: flex;
            align-items: center;
            background: #f8f9fa;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            padding: 0 12px;
            transition: all 0.2s;
        }

        .search-input-wrapper:focus-within {
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .search-input-wrapper i {
            color: #999;
            margin-right: 8px;
        }

        .search-input-wrapper input {
            flex: 1;
            border: none;
            background: transparent;
            padding: 10px 0;
            font-size: 14px;
            outline: none;
        }

        .btn-action {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
            color: white;
            text-decoration: none;
        }

        .btn-secondary-custom {
            background: #f0f0f0;
            color: #2c3e50;
        }

        .btn-secondary-custom:hover {
            background: #e0e0e0;
            text-decoration: none;
        }

        .customers-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .table-responsive {
            min-height: 400px;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead {
            background: #f8f9fa;
            border-bottom: 2px solid #e1e8ed;
        }

        .table thead th {
            font-weight: 700;
            color: #2c3e50;
            padding: 15px;
            border: none;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table tbody td {
            padding: 15px;
            vertical-align: middle;
            border-color: #f0f0f0;
        }

        .table tbody tr {
            transition: background 0.2s;
        }

        .table tbody tr:hover {
            background: #f8f9fa;
        }

        .customer-name {
            font-weight: 600;
            color: #2c3e50;
        }

        .customer-email {
            font-size: 12px;
            color: #7f8c8d;
        }

        .action-buttons {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .btn-icon {
            width: 36px;
            height: 36px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
        }

        .btn-view {
            background: #e3f2fd;
            color: #1976d2;
        }

        .btn-view:hover {
            background: #1976d2;
            color: white;
        }

        .btn-edit {
            background: #fff3e0;
            color: #f57c00;
        }

        .btn-edit:hover {
            background: #f57c00;
            color: white;
        }

        .btn-delete {
            background: #ffebee;
            color: #c62828;
        }

        .btn-delete:hover {
            background: #c62828;
            color: white;
        }

        .pagination-wrapper {
            padding: 20px;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 6px;
            background: #f8f9fa;
            border-top: 1px solid #e1e8ed;
        }

        .pagination {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            margin: 0;
            list-style: none;
            padding: 0;
            justify-content: center;
        }

        .page-link {
            min-width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e1e8ed;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            color: #667eea;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
        }

        .page-link:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .page-link.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .page-link.disabled {
            color: #ccc;
            cursor: not-allowed;
            border-color: #e1e8ed;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }

        .empty-state i {
            font-size: 64px;
            color: #ddd;
            margin-bottom: 15px;
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }

        .modal-title {
            font-weight: 700;
            font-size: 18px;
        }

        .btn-close {
            filter: brightness(0) invert(1);
        }

        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-control {
            padding: 10px 12px;
            border: 1.5px solid #e1e8ed;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        .modal-footer {
            border-top: 1px solid #e1e8ed;
        }

        .loading-spinner {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .loading-spinner i {
            font-size: 36px;
            margin-bottom: 15px;
        }

        .alert-info {
            background: #e3f2fd;
            border: 1px solid #90caf9;
            color: #1565c0;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 15px;
        }

        .alert-success {
            background: #e8f5e9;
            border: 1px solid #81c784;
            color: #2e7d32;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 15px;
        }

        .alert-danger {
            background: #ffebee;
            border: 1px solid #ef5350;
            color: #c62828;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 15px;
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .search-box {
                flex-direction: column;
            }

            .search-input-wrapper {
                width: 100%;
            }

            .table-responsive {
                font-size: 12px;
            }

            .table thead th,
            .table tbody td {
                padding: 10px;
            }

            .action-buttons {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../components/navbar.php'; ?>

    <div class="content-wrapper">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-users"></i> Customer Management</h1>
            <button class="btn-action btn-primary-custom" onclick="openAddCustomerModal()">
                <i class="fas fa-plus"></i> Add New Customer
            </button>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid" id="statsGrid">
            <div class="stat-card">
                <i class="fas fa-users" style="color: #667eea; font-size: 24px;"></i>
                <div class="stat-value" id="totalCustomers">0</div>
                <div class="stat-label">Total Customers</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-user-plus" style="color: #2ecc71; font-size: 24px;"></i>
                <div class="stat-value" id="newCustomers">0</div>
                <div class="stat-label">New This Month</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-star" style="color: #f39c12; font-size: 24px;"></i>
                <div class="stat-value" id="vipCustomers">0</div>
                <div class="stat-label">VIP Customers</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-coins" style="color: #e74c3c; font-size: 24px;"></i>
                <div class="stat-value" id="totalPoints">0</div>
                <div class="stat-label">Total Loyalty Points</div>
            </div>
        </div>

        <!-- Search Section -->
        <div class="search-section">
            <div class="search-box">
                <div class="search-input-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search by name, email, phone or username..." onkeyup="handleSearch(event)">
                </div>
                <button class="btn-action btn-primary-custom" onclick="performSearch()">
                    <i class="fas fa-search"></i> Search
                </button>
                <button class="btn-action btn-secondary-custom" onclick="resetSearch()">
                    <i class="fas fa-redo"></i> Reset
                </button>
            </div>
        </div>

        <!-- Customers Table -->
        <div class="customers-section">
            <div class="table-responsive" id="tableContainer">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th class="hide-mobile">City</th>
                            <th>Store Credits</th>
                            <th class="hide-mobile">Joined</th>
                            <th style="text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="customersTableBody">
                        <tr>
                            <td colspan="7" class="empty-state">
                                <i class="fas fa-spinner fa-spin"></i>
                                <p>Loading customers...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination-wrapper" id="paginationWrapper"></div>
        </div>
    </div>

    <!-- Add Customer Modal -->
    <div class="modal fade" id="addCustomerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="addError" class="alert-danger" style="display: none;"></div>
                    <form id="addCustomerForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">First Name *</label>
                                    <input type="text" class="form-control" id="addFirstName" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Last Name *</label>
                                    <input type="text" class="form-control" id="addLastName" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="addEmail">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone *</label>
                            <input type="tel" class="form-control" id="addPhone" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" id="addAddress">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">City</label>
                                    <input type="text" class="form-control" id="addCity">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">State</label>
                                    <input type="text" class="form-control" id="addState">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pincode</label>
                            <input type="text" class="form-control" id="addPincode">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveNewCustomer()">Add Customer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Customer Modal -->
    <div class="modal fade" id="editCustomerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="editError" class="alert-danger" style="display: none;"></div>
                    <form id="editCustomerForm">
                        <input type="hidden" id="editCustomerId">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">First Name *</label>
                                    <input type="text" class="form-control" id="editFirstName" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Last Name *</label>
                                    <input type="text" class="form-control" id="editLastName" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="editEmail">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone *</label>
                            <input type="tel" class="form-control" id="editPhone" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" id="editAddress">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">City</label>
                                    <input type="text" class="form-control" id="editCity">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">State</label>
                                    <input type="text" class="form-control" id="editState">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pincode</label>
                            <input type="text" class="form-control" id="editPincode">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveEditCustomer()">Update Customer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Customer Modal -->
    <div class="modal fade" id="viewCustomerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Customer Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewCustomerBody">
                    <p>Loading...</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let currentPage = 1;
        let currentSearch = '';
        const itemsPerPage = 20;

        $(document).ready(function() {
            loadStats();
            loadCustomers(1);
        });

        // Load stats
        function loadStats() {
            $.ajax({
                url: '/api/customers/stats',
                method: 'GET',
                success: function(response) {
                    if (response.success && response.data) {
                        const stats = response.data;
                        $('#totalCustomers').text((stats.total || 0).toLocaleString());
                        $('#newCustomers').text((stats.new_this_month || 0).toLocaleString());
                        $('#vipCustomers').text((stats.vip || 0).toLocaleString());
                        $('#totalPoints').text((stats.total_points || 0).toLocaleString());
                    }
                },
                error: function() {
                    console.error('Failed to load stats');
                }
            });
        }

        // Load customers
        function loadCustomers(page = 1) {
            if (!page || page < 1) page = 1;
            const offset = (page - 1) * itemsPerPage;

            $.ajax({
                url: '/api/customers',
                method: 'GET',
                data: {
                    limit: itemsPerPage,
                    offset: offset,
                    search: currentSearch || ''
                },
                success: function(response) {
                    if (response.success) {
                        currentPage = page;
                        displayCustomers(response.customers || []);
                        if (response.pagination) {
                            displayPagination(response.pagination);
                        }
                    }
                },
                error: function() {
                    showError('Error loading customers');
                }
            });
        }

        // Display customers
        function displayCustomers(customers) {
            const tbody = $('#customersTableBody');
            tbody.empty();

            if (customers.length === 0) {
                tbody.html(`
                    <tr>
                        <td colspan="8" class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>No customers found</p>
                        </td>
                    </tr>
                `);
                return;
            }

            customers.forEach(customer => {
                const joinDate = new Date(customer.created_at).toLocaleDateString();
                const name = (customer.first_name || '') + ' ' + (customer.last_name || '');
                const storeCredit = parseFloat(customer.store_credit || 0).toFixed(2);

                tbody.append(`
                    <tr>
                        <td><strong>#${customer.id}</strong></td>
                        <td>
                            <div class="customer-name">${escapeHtml(name.trim())}</div>
                            <div class="customer-email">${escapeHtml(customer.username || '')}</div>
                        </td>
                        <td>${escapeHtml(customer.email || 'N/A')}</td>
                        <td>${escapeHtml(customer.mobile || 'N/A')}</td>
                        <td class="hide-mobile">${escapeHtml(customer.city || 'N/A')}</td>
                        <td><span class="badge ${storeCredit > 0 ? 'bg-success' : 'bg-secondary'}">₹${storeCredit}</span></td>
                        <td class="hide-mobile">${joinDate}</td>
                        <td style="text-align: center;">
                            <div class="action-buttons">
                                <button class="btn-icon btn-view" onclick="viewCustomer(${customer.id})" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn-icon btn-edit" onclick="openEditCustomerModal(${customer.id})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-icon btn-delete" onclick="deleteCustomer(${customer.id})" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `);
            });
        }

        // Display pagination
        function displayPagination(pagination) {
            const wrapper = $('#paginationWrapper');
            wrapper.empty();

            if (!pagination || pagination.total_pages <= 1) return;

            let html = '<ul class="pagination">';

            if (currentPage > 1) {
                html += `<li><a class="page-link" href="javascript:loadCustomers(${currentPage - 1})">← Previous</a></li>`;
            }

            for (let i = 1; i <= pagination.total_pages; i++) {
                html += `<li><a class="page-link ${i === currentPage ? 'active' : ''}" href="javascript:loadCustomers(${i})">${i}</a></li>`;
            }

            if (currentPage < pagination.total_pages) {
                html += `<li><a class="page-link" href="javascript:loadCustomers(${currentPage + 1})">Next →</a></li>`;
            }

            html += '</ul>';
            wrapper.html(html);
        }

        // Search
        function performSearch() {
            currentSearch = $('#searchInput').val().trim();
            currentPage = 1;
            loadCustomers(1);
        }

        function handleSearch(event) {
            if (event.key === 'Enter') {
                performSearch();
            }
        }

        function resetSearch() {
            $('#searchInput').val('');
            currentSearch = '';
            currentPage = 1;
            loadCustomers(1);
        }

        // Open add modal
        function openAddCustomerModal() {
            $('#addCustomerForm')[0].reset();
            $('#addError').hide();
            const modal = new bootstrap.Modal(document.getElementById('addCustomerModal'));
            modal.show();
        }

        // Save new customer
        function saveNewCustomer() {
            const firstName = $('#addFirstName').val().trim();
            const lastName = $('#addLastName').val().trim();
            const email = $('#addEmail').val().trim();
            const phone = $('#addPhone').val().trim();
            const address = $('#addAddress').val().trim();
            const city = $('#addCity').val().trim();
            const state = $('#addState').val().trim();
            const pincode = $('#addPincode').val().trim();

            if (!firstName || !lastName || !phone) {
                showModalError('addError', 'Please fill all required fields');
                return;
            }

            $.ajax({
                url: '/api/customers',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    first_name: firstName,
                    last_name: lastName,
                    email: email,
                    mobile: phone,
                    address: address,
                    city: city,
                    state: state,
                    pincode: pincode
                }),
                success: function(response) {
                    if (response.success) {
                        bootstrap.Modal.getInstance(document.getElementById('addCustomerModal')).hide();
                        loadCustomers(1);
                        loadStats();
                    } else {
                        showModalError('addError', response.message || 'Error adding customer');
                    }
                },
                error: function(xhr) {
                    showModalError('addError', xhr.responseJSON?.message || 'Error adding customer');
                }
            });
        }

        // Open edit modal
        function openEditCustomerModal(customerId) {
            $.ajax({
                url: '/api/customer/' + customerId,
                method: 'GET',
                success: function(response) {
                    if (response.success && response.data) {
                        const customer = response.data;
                        $('#editCustomerId').val(customer.id);
                        $('#editFirstName').val(customer.first_name || '');
                        $('#editLastName').val(customer.last_name || '');
                        $('#editEmail').val(customer.email || '');
                        $('#editPhone').val(customer.mobile || '');
                        $('#editAddress').val(customer.address || '');
                        $('#editCity').val(customer.city || '');
                        $('#editState').val(customer.state || '');
                        $('#editPincode').val(customer.pincode || '');
                        $('#editError').hide();

                        const modal = new bootstrap.Modal(document.getElementById('editCustomerModal'));
                        modal.show();
                    }
                },
                error: function() {
                    showError('Error loading customer');
                }
            });
        }

        // Save edited customer
        function saveEditCustomer() {
            const customerId = $('#editCustomerId').val();
            const firstName = $('#editFirstName').val().trim();
            const lastName = $('#editLastName').val().trim();
            const email = $('#editEmail').val().trim();
            const phone = $('#editPhone').val().trim();
            const address = $('#editAddress').val().trim();
            const city = $('#editCity').val().trim();
            const state = $('#editState').val().trim();
            const pincode = $('#editPincode').val().trim();

            if (!firstName || !lastName || !phone) {
                showModalError('editError', 'Please fill all required fields');
                return;
            }

            $.ajax({
                url: '/api/customer/' + customerId,
                method: 'PUT',
                contentType: 'application/json',
                data: JSON.stringify({
                    first_name: firstName,
                    last_name: lastName,
                    email: email,
                    mobile: phone,
                    address: address,
                    city: city,
                    state: state,
                    pincode: pincode
                }),
                success: function(response) {
                    if (response.success) {
                        bootstrap.Modal.getInstance(document.getElementById('editCustomerModal')).hide();
                        loadCustomers(currentPage);
                        loadStats();
                    } else {
                        showModalError('editError', response.message || 'Error updating customer');
                    }
                },
                error: function(xhr) {
                    showModalError('editError', xhr.responseJSON?.message || 'Error updating customer');
                }
            });
        }

        // View customer
        function viewCustomer(customerId) {
            $.ajax({
                url: '/api/customer/' + customerId,
                method: 'GET',
                success: function(response) {
                    if (response.success && response.data) {
                        const customer = response.data;
                        const name = customer.first_name + ' ' + customer.last_name;
                        
                        let html = `
                            <div class="alert-info">
                                <strong>${escapeHtml(name)}</strong>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <p><strong>ID:</strong> #${customer.id}</p>
                                    <p><strong>Email:</strong> ${escapeHtml(customer.email || 'N/A')}</p>
                                    <p><strong>Phone:</strong> ${escapeHtml(customer.mobile || 'N/A')}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>City:</strong> ${escapeHtml(customer.city || 'N/A')}</p>
                                    <p><strong>State:</strong> ${escapeHtml(customer.state || 'N/A')}</p>
                                    <p><strong>Pincode:</strong> ${escapeHtml(customer.pincode || 'N/A')}</p>
                                </div>
                            </div>
                            <p><strong>Address:</strong> ${escapeHtml(customer.address || 'N/A')}</p>
                            <hr>
                            <p><strong>Joined:</strong> ${new Date(customer.created_at).toLocaleDateString()}</p>
                        `;
                        
                        $('#viewCustomerBody').html(html);
                        const modal = new bootstrap.Modal(document.getElementById('viewCustomerModal'));
                        modal.show();
                    }
                },
                error: function() {
                    showError('Error loading customer details');
                }
            });
        }

        // Delete customer
        function deleteCustomer(customerId) {
            if (!confirm('Are you sure you want to delete this customer?')) return;

            $.ajax({
                url: '/api/customer/' + customerId,
                method: 'DELETE',
                success: function(response) {
                    if (response.success) {
                        loadCustomers(currentPage);
                        loadStats();
                    } else {
                        showError(response.message || 'Error deleting customer');
                    }
                },
                error: function() {
                    showError('Error deleting customer');
                }
            });
        }

        // Utilities
        function showModalError(elementId, message) {
            const $element = $('#' + elementId);
            $element.text(message).show();
        }

        function showError(message) {
            alert(message);
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }
    </script>
</body>
</html>
