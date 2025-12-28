<?php
$title = 'Custom Tax Rules';
include __DIR__ . '/_header.php';
?>

<div class="admin-content">
    <div class="admin-header">
        <h1><i class="fas fa-percentage"></i> Custom Tax Rules</h1>
        <p>Manage conditional taxes based on product categories and price ranges</p>
    </div>

    <!-- Tax System Toggle -->
    <div class="content-card">
        <div class="content-card-header">
            <h5 class="content-card-title">Tax System Configuration</h5>
        </div>
        <div class="row align-items-center">
            <div class="col-md-8">
                <h6>Enable Custom Tax System</h6>
                <p class="text-muted mb-0">When enabled, the system will apply custom tax rules based on product categories and price ranges instead of standard tax rates.</p>
            </div>
            <div class="col-md-4 text-end">
                <div class="form-check form-switch d-inline-block">
                    <input class="form-check-input" type="checkbox" id="customTaxToggle" style="width: 60px; height: 30px; cursor: pointer;">
                    <label class="form-check-label ms-2" for="customTaxToggle">
                        <span id="taxStatusText" class="badge badge-danger">Disabled</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Tax Rules List -->
    <div class="content-card">
        <div class="content-card-header">
            <h5 class="content-card-title">Tax Rules</h5>
            <button class="btn btn-primary" onclick="openAddTaxRuleModal()">
                <i class="fas fa-plus"></i> Add Tax Rule
            </button>
        </div>

        <div class="table-responsive">
            <table class="admin-table" id="taxRulesTable">
                <thead>
                    <tr>
                        <th>Rule Name</th>
                        <th>Type</th>
                        <th>Condition</th>
                        <th>Tax Rate</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="taxRulesTableBody">
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                            <p>Loading tax rules...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Tax Rule Modal -->
<div class="modal fade" id="taxRuleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="taxRuleModalTitle">Add Tax Rule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="taxRuleForm">
                    <input type="hidden" id="ruleId" name="rule_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Rule Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ruleName" name="rule_name" required placeholder="e.g., Electronics High Tax">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Rule Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="ruleType" name="rule_type" required onchange="toggleRuleFields()">
                            <option value="">Select Type</option>
                            <option value="category">Category-Based Tax</option>
                            <option value="price_range">Price Range-Based Tax</option>
                        </select>
                    </div>

                    <!-- Category Field -->
                    <div class="mb-3" id="categoryField" style="display: none;">
                        <label class="form-label">Product Category <span class="text-danger" id="categoryRequired">*</span></label>
                        <select class="form-select" id="categoryId" name="category_id">
                            <option value="">Select Category (Optional for Price Range)</option>
                        </select>
                        <small class="text-muted" id="categoryHelp">Tax will be applied to all products in this category</small>
                    </div>

                    <!-- Price Range Fields -->
                    <div id="priceRangeFields" style="display: none;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Minimum Price (₹) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="minPrice" name="min_price" step="0.01" placeholder="0.00">
                                <small class="text-muted">Leave blank for no minimum</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Maximum Price (₹) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="maxPrice" name="max_price" step="0.01" placeholder="999999.99">
                                <small class="text-muted">Leave blank for no maximum</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tax Rate (%) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="taxRate" name="tax_rate" required step="0.01" placeholder="e.g., 18.00">
                            <small class="text-muted">Enter tax rate as percentage (e.g., 18 for 18%)</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Priority</label>
                            <input type="number" class="form-control" id="priority" name="priority" value="0" placeholder="0">
                            <small class="text-muted">Higher priority rules are applied first</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="isActive" name="is_active" checked>
                            <label class="form-check-label" for="isActive">Active</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveTaxRule()">
                    <i class="fas fa-save"></i> Save Tax Rule
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const csrfToken = '<?php echo generateCsrfToken(); ?>';
let taxRuleModal;

document.addEventListener('DOMContentLoaded', function() {
    taxRuleModal = new bootstrap.Modal(document.getElementById('taxRuleModal'));
    
    loadTaxSystemStatus();
    loadTaxRules();
    loadCategories();
    
    // Tax system toggle
    document.getElementById('customTaxToggle').addEventListener('change', toggleTaxSystem);
});

function toggleRuleFields() {
    const ruleType = document.getElementById('ruleType').value;
    const categoryField = document.getElementById('categoryField');
    const priceRangeFields = document.getElementById('priceRangeFields');
    const categoryRequired = document.getElementById('categoryRequired');
    const categoryHelp = document.getElementById('categoryHelp');
    
    if (ruleType === 'category') {
        categoryField.style.display = 'block';
        priceRangeFields.style.display = 'none';
        document.getElementById('categoryId').required = true;
        document.getElementById('minPrice').required = false;
        document.getElementById('maxPrice').required = false;
        categoryRequired.style.display = 'inline';
        categoryHelp.textContent = 'Tax will be applied to all products in this category';
    } else if (ruleType === 'price_range') {
        categoryField.style.display = 'block';
        priceRangeFields.style.display = 'block';
        document.getElementById('categoryId').required = false;
        document.getElementById('minPrice').required = false;
        document.getElementById('maxPrice').required = false;
        categoryRequired.style.display = 'none';
        categoryHelp.textContent = 'Optional: Select a category to apply this tax only to products in that category within the price range';
    } else {
        categoryField.style.display = 'none';
        priceRangeFields.style.display = 'none';
    }
}

async function loadTaxSystemStatus() {
    try {
        const response = await fetch('/api/settings/custom_tax_enabled');
        const data = await response.json();
        
        if (data.success) {
            const isEnabled = data.value === 'true' || data.value === true;
            document.getElementById('customTaxToggle').checked = isEnabled;
            updateTaxStatusBadge(isEnabled);
        }
    } catch (error) {
        console.error('Error loading tax system status:', error);
    }
}

async function toggleTaxSystem() {
    const isEnabled = document.getElementById('customTaxToggle').checked;
    
    try {
        const response = await fetch('/api/settings/custom_tax_enabled', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                value: isEnabled,
                csrf_token: csrfToken
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            updateTaxStatusBadge(isEnabled);
            alert(isEnabled ? 'Custom tax system enabled successfully!' : 'Custom tax system disabled successfully!');
        } else {
            alert('Error updating tax system: ' + data.message);
            document.getElementById('customTaxToggle').checked = !isEnabled;
        }
    } catch (error) {
        console.error('Error toggling tax system:', error);
        alert('Error updating tax system');
        document.getElementById('customTaxToggle').checked = !isEnabled;
    }
}

function updateTaxStatusBadge(isEnabled) {
    const badge = document.getElementById('taxStatusText');
    if (isEnabled) {
        badge.textContent = 'Enabled';
        badge.className = 'badge badge-success';
    } else {
        badge.textContent = 'Disabled';
        badge.className = 'badge badge-danger';
    }
}

async function loadTaxRules() {
    try {
        const response = await fetch('/api/tax-rules');
        const data = await response.json();
        
        if (data.success) {
            renderTaxRules(data.rules);
        } else {
            showError('Failed to load tax rules');
        }
    } catch (error) {
        console.error('Error loading tax rules:', error);
        showError('Error loading tax rules');
    }
}

function renderTaxRules(rules) {
    const tbody = document.getElementById('taxRulesTableBody');
    
    if (rules.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-4">
                    <i class="fas fa-inbox fa-3x mb-3" style="color: #ddd;"></i>
                    <p class="text-muted">No tax rules configured yet. Click "Add Tax Rule" to create one.</p>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = rules.map(rule => `
        <tr>
            <td><strong>${escapeHtml(rule.rule_name)}</strong></td>
            <td>
                <span class="badge ${rule.rule_type === 'category' ? 'badge-info' : 'badge-warning'}">
                    ${rule.rule_type === 'category' ? 'Category' : 'Price Range'}
                </span>
            </td>
            <td>${renderCondition(rule)}</td>
            <td><strong>${rule.tax_rate}%</strong></td>
            <td>${rule.priority}</td>
            <td>
                <span class="badge ${rule.is_active ? 'badge-success' : 'badge-danger'}">
                    ${rule.is_active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td>
                <button class="btn btn-sm btn-outline-primary" onclick="editTaxRule(${rule.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteTaxRule(${rule.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

function renderCondition(rule) {
    if (rule.rule_type === 'category') {
        return `Category: <strong>${escapeHtml(rule.category_name || 'ID: ' + rule.category_id)}</strong>`;
    } else {
        const min = rule.min_price ? '₹' + parseFloat(rule.min_price).toFixed(2) : 'No min';
        const max = rule.max_price ? '₹' + parseFloat(rule.max_price).toFixed(2) : 'No max';
        return `${min} - ${max}`;
    }
}

async function loadCategories() {
    try {
        const response = await fetch('/api/categories');
        const data = await response.json();
        
        if (data.success) {
            const select = document.getElementById('categoryId');
            select.innerHTML = '<option value="">Select Category</option>' + 
                data.categories.map(cat => `<option value="${cat.term_id}">${escapeHtml(cat.name)}</option>`).join('');
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

function openAddTaxRuleModal() {
    document.getElementById('taxRuleModalTitle').textContent = 'Add Tax Rule';
    document.getElementById('taxRuleForm').reset();
    document.getElementById('ruleId').value = '';
    document.getElementById('isActive').checked = true;
    toggleRuleFields();
    taxRuleModal.show();
}

async function editTaxRule(id) {
    try {
        const response = await fetch(`/api/tax-rules/${id}`);
        const data = await response.json();
        
        if (data.success) {
            const rule = data.rule;
            document.getElementById('taxRuleModalTitle').textContent = 'Edit Tax Rule';
            document.getElementById('ruleId').value = rule.id;
            document.getElementById('ruleName').value = rule.rule_name;
            document.getElementById('ruleType').value = rule.rule_type;
            document.getElementById('categoryId').value = rule.category_id || '';
            document.getElementById('minPrice').value = rule.min_price || '';
            document.getElementById('maxPrice').value = rule.max_price || '';
            document.getElementById('taxRate').value = rule.tax_rate;
            document.getElementById('priority').value = rule.priority;
            document.getElementById('isActive').checked = rule.is_active;
            toggleRuleFields();
            taxRuleModal.show();
        }
    } catch (error) {
        console.error('Error loading tax rule:', error);
        alert('Error loading tax rule');
    }
}

async function saveTaxRule() {
    const form = document.getElementById('taxRuleForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const ruleId = document.getElementById('ruleId').value;
    const ruleData = {
        rule_name: document.getElementById('ruleName').value,
        rule_type: document.getElementById('ruleType').value,
        category_id: document.getElementById('categoryId').value || null,
        min_price: document.getElementById('minPrice').value || null,
        max_price: document.getElementById('maxPrice').value || null,
        tax_rate: document.getElementById('taxRate').value,
        priority: document.getElementById('priority').value || 0,
        is_active: document.getElementById('isActive').checked,
        csrf_token: csrfToken
    };
    
    try {
        const url = ruleId ? `/api/tax-rules/${ruleId}` : '/api/tax-rules';
        const method = ruleId ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(ruleData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(ruleId ? 'Tax rule updated successfully!' : 'Tax rule created successfully!');
            taxRuleModal.hide();
            loadTaxRules();
        } else {
            alert('Error saving tax rule: ' + data.message);
        }
    } catch (error) {
        console.error('Error saving tax rule:', error);
        alert('Error saving tax rule');
    }
}

async function deleteTaxRule(id) {
    if (!confirm('Are you sure you want to delete this tax rule?')) {
        return;
    }
    
    try {
        const response = await fetch(`/api/tax-rules/${id}`, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ csrf_token: csrfToken })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Tax rule deleted successfully!');
            loadTaxRules();
        } else {
            alert('Error deleting tax rule: ' + data.message);
        }
    } catch (error) {
        console.error('Error deleting tax rule:', error);
        alert('Error deleting tax rule');
    }
}

function showError(message) {
    const tbody = document.getElementById('taxRulesTableBody');
    tbody.innerHTML = `
        <tr>
            <td colspan="7" class="text-center py-4 text-danger">
                <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                <p>${escapeHtml(message)}</p>
            </td>
        </tr>
    `;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<?php include __DIR__ . '/_footer.php'; ?>
