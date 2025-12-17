<?php
include "./function/connection.php";
include "./function/language.php";

// Cek permission untuk mengakses halaman ini
if (!hasPermission('user_management')) {
    include "page/error.php";
    exit();
}

// Handle actions
$action = $_GET['action'] ?? '';
$user_id = $_GET['id'] ?? 0;

if ($action === 'delete' && $user_id) {
    // Cek apakah user bisa menghapus user lain
    $stmt = $connection->prepare("SELECT role FROM user WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $target_user = $result->fetch_assoc();
        if (canManageUser($target_user['role'])) {
            $delete_stmt = $connection->prepare("DELETE FROM user WHERE id = ?");
            $delete_stmt->bind_param("i", $user_id);
            $delete_stmt->execute();
            $delete_stmt->close();

            $GLOBALS['role_manager']->logRoleAction('DELETE_USER', "User ID: $user_id");
        }
    }
    $stmt->close();
}

if ($action === 'change_role' && $user_id && isset($_POST['new_role'])) {
    $new_role = $_POST['new_role'];

    // Cek apakah role valid dan bisa diubah
    if ($role_manager->roleExists($new_role)) {
        $stmt = $connection->prepare("SELECT role FROM user WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $target_user = $result->fetch_assoc();
            if ($role_manager->canChangeRole($target_user['role'], $new_role) && canManageUser($target_user['role'])) {
                $update_stmt = $connection->prepare("UPDATE user SET role = ?, updated_at = NOW() WHERE id = ?");
                $update_stmt->bind_param("si", $new_role, $user_id);
                $update_stmt->execute();
                $update_stmt->close();

                $role_manager->logRoleAction('CHANGE_USER_ROLE', "User ID: $user_id, New Role: $new_role");
            }
        }
        $stmt->close();
    }
}

// Get all users
$users_query = "SELECT id, nama, username, email, role, foto_profil, created_at, updated_at FROM user ORDER BY created_at DESC";
$users_result = $connection->query($users_query);
?>

<!DOCTYPE html>
<html lang="id">



<body>
    
            <div class="page-heading">
                <div class="page-title">
                    <div class="row">
                        <div class="col-12 col-md-6 order-md-1 order-last">
                            <h3><?= __('User Management') ?></h3>
                            <p class="text-subtitle text-muted">
                                <?= __('Kelola pengguna sistem dan role mereka') ?>
                            </p>
                        </div>
                        <div class="col-12 col-md-6 order-md-2 order-first">
                            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="index.php?halaman=beranda">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">User Management</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <section class="section">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-people-fill me-2"></i>
                                <?= __('Daftar Pengguna') ?>
                            </h5>
                            <?php if (hasPermission('user_management')): ?>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                    <i class="bi bi-person-plus-fill me-2"></i>
                                    <?= __('Tambah User') ?>
                                </button>
                            <?php endif; ?>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="userTable">
                                    <thead>
                                        <tr>
                                            <th><?= __('Foto') ?></th>
                                            <th><?= __('Nama') ?></th>
                                            <th><?= __('Username') ?></th>
                                            <th><?= __('Email') ?></th>
                                            <th><?= __('Role') ?></th>
                                            <th><?= __('Status') ?></th>
                                            <th><?= __('Terdaftar') ?></th>
                                            <th><?= __('Aksi') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($user = $users_result->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <?php if (!empty($user['foto_profil'])): ?>
                                                        <img src="./assets/uploads/profiles/<?= htmlspecialchars($user['foto_profil']) ?>"
                                                             alt="Foto Profil"
                                                             class="rounded-circle"
                                                             style="width: 40px; height: 40px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="avatar avatar-sm">
                                                            <div class="avatar-content">
                                                                <i class="bi bi-person-circle" style="font-size: 40px;"></i>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($user['nama']) ?></td>
                                                <td><?= htmlspecialchars($user['username']) ?></td>
                                                <td><?= htmlspecialchars($user['email'] ?? '-') ?></td>
                                                <td>
                                                    <span class="<?= getRoleBadgeClass($user['role']) ?>">
                                                        <?= getRoleDisplayName($user['role']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-circle-fill me-1"></i>
                                                        <?= __('Aktif') ?>
                                                    </span>
                                                </td>
                                                <td><?= date('d M Y', strtotime($user['created_at'])) ?></td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <?php if (canManageUser($user['role'])): ?>
                                                            <button type="button"
                                                                    class="btn btn-sm btn-outline-primary"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#changeRoleModal"
                                                                    data-user-id="<?= $user['id'] ?>"
                                                                    data-user-name="<?= htmlspecialchars($user['nama']) ?>"
                                                                    data-current-role="<?= $user['role'] ?>">
                                                                <i class="bi bi-shield-check"></i>
                                                            </button>
                                                        <?php endif; ?>

                                                        <?php if ($user['id'] != $_SESSION['user_id'] && canManageUser($user['role'])): ?>
                                                            <button type="button"
                                                                    class="btn btn-sm btn-outline-danger"
                                                                    onclick="confirmDelete(<?= $user['id'] ?>, '<?= htmlspecialchars($user['nama']) ?>')">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Modal untuk mengubah role -->
            <div class="modal fade" id="changeRoleModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-shield-check me-2"></i>
                                <?= __('Ubah Role User') ?>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST" action="?halaman=users&action=change_role&id=<?= $user_id ?>">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <?= __('User') ?>: <span id="modal-user-name"></span>
                                    </label>
                                </div>
                                <div class="mb-3">
                                    <label for="new_role" class="form-label">
                                        <?= __('Role Baru') ?>
                                    </label>
                                    <select class="form-select" name="new_role" id="new_role" required>
                                        <option value="">-- <?= __('Pilih Role') ?> --</option>
                                        <?php
                                        $available_roles = $role_manager->getAllRoles();
                                        $current_user_role = getUserRole();

                                        foreach ($available_roles as $role):
                                            if ($role_manager->canChangeRole($current_user_role, $role)):
                                        ?>
                                            <option value="<?= $role ?>">
                                                <?= getRoleDisplayName($role) ?>
                                            </option>
                                        <?php
                                            endif;
                                        endforeach;
                                        ?>
                                    </select>
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

            <!-- Modal untuk tambah user -->
            <div class="modal fade" id="addUserModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-person-plus-fill me-2"></i>
                                <?= __('Tambah User Baru') ?>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST" action="page/process_add_user.php">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="nama" class="form-label">
                                        <?= __('Nama Lengkap') ?> *
                                    </label>
                                    <input type="text" class="form-control" name="nama" id="nama" required>
                                </div>
                                <div class="mb-3">
                                    <label for="username" class="form-label">
                                        <?= __('Username') ?> *
                                    </label>
                                    <input type="text" class="form-control" name="username" id="username" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">
                                        <?= __('Email') ?>
                                    </label>
                                    <input type="email" class="form-control" name="email" id="email">
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">
                                        <?= __('Password') ?> *
                                    </label>
                                    <input type="password" class="form-control" name="password" id="password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="role" class="form-label">
                                        <?= __('Role') ?> *
                                    </label>
                                    <select class="form-select" name="role" id="role" required>
                                        <option value="">-- <?= __('Pilih Role') ?> --</option>
                                        <?php
                                        $available_roles = $role_manager->getAllRoles();
                                        $current_user_role = getUserRole();

                                        foreach ($available_roles as $role):
                                            if ($role_manager->canChangeRole($current_user_role, $role)):
                                        ?>
                                            <option value="<?= $role ?>">
                                                <?= getRoleDisplayName($role) ?>
                                            </option>
                                        <?php
                                            endif;
                                        endforeach;
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <?= __('Batal') ?>
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <?= __('Tambah User') ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="./assets/compiled/js/app.js"></script>
    <script src="./assets/extensions/jquery/jquery.min.js"></script>
    <script src="./assets/extensions/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="./assets/extensions/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>

    <script>
        // Initialize DataTable
        $(document).ready(function() {
            $('#userTable').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                }
            });
        });

        // Handle change role modal
        const changeRoleModal = document.getElementById('changeRoleModal');
        changeRoleModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const userId = button.getAttribute('data-user-id');
            const userName = button.getAttribute('data-user-name');
            const currentRole = button.getAttribute('data-current-role');

            const modal = this;
            modal.querySelector('#modal-user-name').textContent = userName;
            modal.querySelector('form').action = `?halaman=users&action=change_role&id=${userId}`;
        });

        // Confirm delete function
        function confirmDelete(userId, userName) {
            Swal.fire({
                title: '<?= __('Apakah Anda yakin?') ?>',
                text: `<?= __('User') ?> "${userName}" <?= __('akan dihapus permanen') ?>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '<?= __('Ya, Hapus') ?>',
                cancelButtonText: '<?= __('Batal') ?>'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `?halaman=users&action=delete&id=${userId}`;
                }
            });
        }
    </script>
</body>

</html>
