<?php include __DIR__ . '/_header.php'; ?>

<div class="admin-content">
    <div class="admin-header">
        <h1><i class="fas fa-user-tie"></i> User Management</h1>
        <p>Manage cashiers, stock managers, and admin users</p>
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

    <div class="content-card">
        <div class="content-card-header">
            <h3 class="content-card-title"><i class="fas fa-users"></i> All Users</h3>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fas fa-plus"></i> Add New User
            </button>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Display Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['ID']; ?></td>
                        <td><strong><?php echo htmlspecialchars($user['user_login']); ?></strong></td>
                        <td><?php echo htmlspecialchars($user['display_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['user_email']); ?></td>
                        <td>
                            <?php
                            $role = $user['role'] ?? 'cashier';
                            $roleClass = $role === 'admin' ? 'danger' : ($role === 'stock_manager' ? 'warning' : 'info');
                            ?>
                            <span class="badge badge-<?php echo $roleClass; ?>">
                                <?php echo ucwords(str_replace('_', ' ', $role)); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($user['user_registered'])); ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary edit-user-btn"
                                    data-id="<?php echo $user['ID']; ?>"
                                    data-username="<?php echo htmlspecialchars($user['user_login']); ?>"
                                    data-email="<?php echo htmlspecialchars($user['user_email']); ?>"
                                    data-display="<?php echo htmlspecialchars($user['display_name']); ?>"
                                    data-role="<?php echo $role; ?>">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <?php if ($user['ID'] != Session::get('user_id')): ?>
                            <button class="btn btn-sm btn-outline-danger delete-user-btn"
                                    data-id="<?php echo $user['ID']; ?>"
                                    data-name="<?php echo htmlspecialchars($user['display_name']); ?>">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No users found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/admin/users/save">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                <input type="hidden" name="user_id" id="editUserId">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="username" id="editUsername" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Display Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="display_name" id="editDisplayName" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" id="editEmail" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select class="form-select" name="role" id="editRole" required>
                            <option value="cashier">Cashier</option>
                            <option value="stock_manager">Stock Manager</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Password <span id="passwordRequired" class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="password" id="editPassword">
                        <small class="text-muted">Leave blank to keep existing password (when editing)</small>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Edit user
    $('.edit-user-btn').click(function() {
        const userId = $(this).data('id');
        const username = $(this).data('username');
        const email = $(this).data('email');
        const displayName = $(this).data('display');
        const role = $(this).data('role');
        
        $('#modalTitle').text('Edit User');
        $('#editUserId').val(userId);
        $('#editUsername').val(username).prop('readonly', true);
        $('#editEmail').val(email);
        $('#editDisplayName').val(displayName);
        $('#editRole').val(role);
        $('#editPassword').prop('required', false);
        $('#passwordRequired').hide();
        
        $('#addUserModal').modal('show');
    });
    
    // Reset form when adding new
    $('#addUserModal').on('hidden.bs.modal', function() {
        $('#modalTitle').text('Add New User');
        $('#editUserId').val('');
        $('#editUsername').val('').prop('readonly', false);
        $('#editEmail').val('');
        $('#editDisplayName').val('');
        $('#editRole').val('cashier');
        $('#editPassword').val('').prop('required', true);
        $('#passwordRequired').show();
    });
    
    // Delete user
    $('.delete-user-btn').click(function() {
        const userId = $(this).data('id');
        const userName = $(this).data('name');
        
        if (confirm('Are you sure you want to delete user "' + userName + '"? This action cannot be undone.')) {
            $.ajax({
                url: '/admin/users/delete/' + userId,
                method: 'POST',
                data: {
                    csrf_token: '<?php echo generateCsrfToken(); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error deleting user');
                }
            });
        }
    });
});
</script>

<?php include __DIR__ . '/_footer.php'; ?>
