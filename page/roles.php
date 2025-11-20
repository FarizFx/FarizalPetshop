<?php
include "./function/connection.php";
include "./function/language.php";
include_once "./function/role_manager.php";

// Inisialisasi role manager
global $role_manager;
$role_manager = new RoleManager($connection);

// Cek permission untuk mengakses halaman ini
if (!hasPermission('role_management')) {
    include "page/error.php";
    exit();
}

// Handle success messages from session
$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']); // Clear it after displaying
session_write_close(); // Ensure session is saved

// Handle actions
$action = $_GET['action'] ?? '';

if ($action === 'update_permissions' && isset($_POST['role']) && isset($_POST['permissions'])) {
    $role = $_POST['role'];
    $permissions = $_POST['permissions'];

    // Convert permissions array to boolean values
    $permissions_bool = [];
    foreach ($permissions as $perm => $value) {
        $permissions_bool[$perm] = (bool)$value;
    }

    // Update permissions untuk role tertentu di database
    $permissions_json = json_encode($permissions_bool);
    $stmt = $connection->prepare("UPDATE roles SET permissions = ?, updated_at = NOW() WHERE role_name = ?");
    $stmt->bind_param("ss", $permissions_json, $role);

    if ($stmt->execute()) {
        $success = "Permissions untuk role '$role' berhasil diperbarui!";
        $role_manager->logRoleAction('UPDATE_ROLE_PERMISSIONS', "Role: $role");
    } else {
        $error = "Gagal memperbarui permissions untuk role '$role'!";
    }
    $stmt->close();
}

if ($action === 'create_role' && isset($_POST['role_name']) && isset($_POST['role_level'])) {
    $role_name = $_POST['role_name'];
    $role_level = (int)$_POST['role_level'];

    // Gunakan method createRole dari RoleManager
    $result = $role_manager->createRole($role_name, $role_level);

    if ($result['success']) {
        $success = $result['message'];
    } else {
        $error = $result['message'];
    }
}

if ($action === 'delete_role' && isset($_POST['role_name'])) {
    $role_name = $_POST['role_name'];

    // Validasi permission - hanya super_admin yang bisa hapus role
    if (!hasRole('super_admin')) {
        $error = "Hanya Super Administrator yang dapat menghapus role!";
    } elseif ($role_name === 'super_admin') {
        $error = "Role Super Administrator tidak dapat dihapus!";
    } else {
        // Hapus role menggunakan method dari RoleManager
        $result = $role_manager->deleteRole($role_name);
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}

// Get all roles with their permissions
$all_roles = $role_manager->getAllRoles();
$role_hierarchy = $role_manager->getRoleHierarchy();
?>
            <div class="page-heading">
                <div class="page-title">
                    <div class="row">
                        <div class="col-12 col-md-6 order-md-1 order-last">
                            <h3><?= __('Role Management') ?></h3>
                            <p class="text-subtitle text-muted">
                                <?= __('Kelola roles dan permissions sistem') ?>
                            </p>
                        </div>
                        <div class="col-12 col-md-6 order-md-2 order-first">
                            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="index.php?halaman=beranda">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Role Management</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>



                <section class="section">
                    <div class="row mb-4">
                        <!-- Role Hierarchy -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-diagram-3 me-2"></i>
                                        <?= __('Hierarki Role') ?>
                                    </h5>
                                    <?php if (hasRole('super_admin')): ?>
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createRoleModal">
                                            <i class="bi bi-plus-circle me-1"></i>
                                            <?= __('Role Baru') ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <div class="role-hierarchy">
                                        <?php foreach ($role_hierarchy as $role => $level): ?>
                                            <div class="role-item mb-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <span class="<?= getRoleBadgeClass($role) ?> me-2">
                                                            <?= getRoleDisplayName($role) ?>
                                                        </span>
                                                        <small class="text-muted">
                                                            Level: <?= $level ?>
                                                        </small>
                                                    </div>
                                                    <div class="btn-group" role="group">
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-primary"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#editPermissionsModal"
                                                                data-role="<?= $role ?>">
                                                            <i class="bi bi-gear"></i>
                                                        </button>
                                                        <?php if (hasRole('super_admin') && $role !== 'super_admin'): ?>
                                                            <button type="button"
                                                                    class="btn btn-sm btn-outline-danger delete-role-btn"
                                                                    data-role="<?= $role ?>"
                                                                    data-role-name="<?= getRoleDisplayName($role) ?>">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Role Statistics -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-bar-chart me-2"></i>
                                        <?= __('Statistik Role') ?>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <?php
                                        $total_users = 0;
                                        foreach ($all_roles as $role) {
                                            $stmt = $connection->prepare("SELECT COUNT(*) as count FROM user WHERE role = ?");
                                            $stmt->bind_param("s", $role);
                                            $stmt->execute();
                                            $result = $stmt->get_result();
                                            $count = $result->fetch_assoc()['count'];
                                            $total_users += $count;
                                        ?>
                                            <div class="col-6 mb-3">
                                                <div class="role-stat">
                                                    <div class="stat-number"><?= $count ?></div>
                                                    <div class="stat-label">
                                                        <span class="<?= getRoleBadgeClass($role) ?>">
                                                            <?= getRoleDisplayName($role) ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <hr>
                                    <div class="text-center">
                                        <strong>Total Users: <?= $total_users ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Permissions Matrix -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-shield-check me-2"></i>
                                <?= __('Matrix Permissions') ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th><?= __('Permission') ?></th>
                                            <?php foreach ($all_roles as $role): ?>
                                                <th class="text-center">
                                                    <span class="<?= getRoleBadgeClass($role) ?>">
                                                        <?= getRoleDisplayName($role) ?>
                                                    </span>
                                                </th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $all_permissions = [
                                            'user_management' => 'Manajemen User',
                                            'role_management' => 'Manajemen Role',
                                            'product_management' => 'Manajemen Produk',
                                            'category_management' => 'Manajemen Kategori',
                                            'sales_management' => 'Manajemen Penjualan',
                                            'stock_management' => 'Manajemen Stok',
                                            'report_management' => 'Manajemen Laporan',
                                            'system_settings' => 'Pengaturan Sistem',
                                            'backup_restore' => 'Backup & Restore',
                                            'view_dashboard' => 'Lihat Dashboard',
                                            'view_profile' => 'Lihat Profil',
                                            'edit_profile' => 'Edit Profil',
                                            'change_password' => 'Ganti Password'
                                        ];

                                        foreach ($all_permissions as $permission => $label):
                                        ?>
                                            <tr>
                                                <td><strong><?= $label ?></strong></td>
                                                <?php foreach ($all_roles as $role): ?>
                                                    <td class="text-center">
                                                        <?php
                                                        $role_permissions = $role_manager->getRolePermissions($role);
                                                        $has_permission = isset($role_permissions[$permission]) && $role_permissions[$permission];
                                                        ?>
                                                        <i class="bi bi-<?= $has_permission ? 'check-circle-fill text-success' : 'x-circle-fill text-muted' ?>"></i>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Modal untuk edit permissions -->
            <div class="modal fade" id="editPermissionsModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-gear me-2"></i>
                                <?= __('Edit Permissions') ?>: <span id="modal-role-name"></span>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST" action="?halaman=roles&action=update_permissions">
                            <div class="modal-body">
                                <input type="hidden" name="role" id="modal-role-input">
                                <div class="row">
                                    <?php
                                    $permission_groups = [
                                        'Management' => [
                                            'user_management' => 'Manajemen User',
                                            'role_management' => 'Manajemen Role',
                                            'product_management' => 'Manajemen Produk',
                                            'category_management' => 'Manajemen Kategori',
                                            'sales_management' => 'Manajemen Penjualan',
                                            'stock_management' => 'Manajemen Stok',
                                            'report_management' => 'Manajemen Laporan'
                                        ],
                                        'System' => [
                                            'system_settings' => 'Pengaturan Sistem',
                                            'backup_restore' => 'Backup & Restore'
                                        ],
                                        'User' => [
                                            'view_dashboard' => 'Lihat Dashboard',
                                            'view_profile' => 'Lihat Profil',
                                            'edit_profile' => 'Edit Profil',
                                            'change_password' => 'Ganti Password'
                                        ]
                                    ];

                                    foreach ($permission_groups as $group_name => $permissions):
                                    ?>
                                        <div class="col-md-4">
                                            <h6 class="mb-3"><strong><?= $group_name ?></strong></h6>
                                            <?php foreach ($permissions as $permission => $label): ?>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input"
                                                           type="checkbox"
                                                           name="permissions[<?= $permission ?>]"
                                                           id="perm_<?= $permission ?>"
                                                           value="1">
                                                    <label class="form-check-label" for="perm_<?= $permission ?>">
                                                        <?= $label ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <?= __('Batal') ?>
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <?= __('Simpan Perubahan') ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal untuk create role baru -->
            <div class="modal fade" id="createRoleModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-plus-circle me-2"></i>
                                <?= __('Buat Role Baru') ?>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST" action="?halaman=roles&action=create_role">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="role_name" class="form-label">
                                        <?= __('Nama Role') ?> *
                                    </label>
                                    <input type="text"
                                           class="form-control"
                                           name="role_name"
                                           id="role_name"
                                           placeholder="contoh: moderator"
                                           required>
                                    <div class="form-text">
                                        <?= __('Gunakan huruf kecil dan underscore (_) untuk nama role') ?>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="role_level" class="form-label">
                                        <?= __('Level Role') ?> *
                                    </label>
                                    <input type="number"
                                           class="form-control"
                                           name="role_level"
                                           id="role_level"
                                           min="0"
                                           max="100"
                                           required>
                                    <div class="form-text">
                                        <?= __('Level 0-100, semakin tinggi semakin banyak akses') ?>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <?= __('Batal') ?>
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <?= __('Buat Role') ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>


    <script src="./assets/compiled/js/app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Embed permissions data for all roles
        const rolesPermissions = <?php echo json_encode(array_map(function($role) use ($role_manager) {
            return $role_manager->getRolePermissions($role);
        }, $all_roles)); ?>;

        // Handle edit permissions modal
        const editPermissionsModal = document.getElementById('editPermissionsModal');
        editPermissionsModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const role = button.getAttribute('data-role');

            const modal = this;
            modal.querySelector('#modal-role-name').textContent = '<?= __('Role') ?>: ' + role;
            modal.querySelector('#modal-role-input').value = role;

            // Load current permissions for this role
            const roleIndex = Array.from(modal.querySelectorAll('input[type="checkbox"]')).map(cb => cb.name.replace('permissions[', '').replace(']', ''));
            const currentPermissions = rolesPermissions[role] || {};

            // Reset all checkboxes
            modal.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                const permName = checkbox.name.replace('permissions[', '').replace(']', '');
                checkbox.checked = currentPermissions[permName] === true;
            });
        });

        // Handle delete role confirmation
        document.addEventListener('click', function(event) {
            if (event.target.closest('.delete-role-btn')) {
                event.preventDefault();
                const button = event.target.closest('.delete-role-btn');
                const role = button.getAttribute('data-role');
                const roleName = button.getAttribute('data-role-name');

                Swal.fire({
                    title: '<?= __('Hapus Role') ?>',
                    text: '<?= __('Apakah Anda yakin ingin menghapus role') ?> "' + roleName + '"? <?= __('Tindakan ini tidak dapat dibatalkan.') ?>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: '<?= __('Ya, Hapus') ?>',
                    cancelButtonText: '<?= __('Batal') ?>'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Create form and submit
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '?halaman=roles&action=delete_role';

                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'role_name';
                        input.value = role;

                        form.appendChild(input);
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            }
        });

        // Close modal if it's still open after successful role creation
        const successAlert = document.querySelector('.alert-success');
        if (successAlert && successAlert.textContent.includes('berhasil dibuat')) {
            const createRoleModal = bootstrap.Modal.getInstance(document.getElementById('createRoleModal'));
            if (createRoleModal) {
                createRoleModal.hide();
            }
        }

        // Show SweetAlert for success/error messages
        <?php if (!empty($success)): ?>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: <?= json_encode($success) ?>,
                confirmButtonText: 'OK'
            });
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: <?= json_encode($error) ?>,
                confirmButtonText: 'OK'
            });
        <?php endif; ?>
    </script>


</html>
