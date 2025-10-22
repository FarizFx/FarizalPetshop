<?php
/**
 * Role Manager - Sistem Kontrol Akses Berbasis Role yang Komprehensif
 * Sistem Manajemen Petshop Farizal
 */

class RoleManager {
    private $connection;
    private $user_role;
    private $user_id;
    private $roles_hierarchy = [];
    private $role_permissions = [];

    public function __construct($connection) {
        $this->connection = $connection;
        $this->initializeUserRole();
        $this->loadRolesFromDB();
    }

    /**
     * Load roles and permissions from the database
     */
    private function loadRolesFromDB() {
        $this->roles_hierarchy = [];
        $this->role_permissions = [];

        $query = "SELECT role_name, role_level, permissions FROM roles ORDER BY role_level DESC";
        $result = $this->connection->query($query);

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $role = $row['role_name'];
                $level = (int)$row['role_level'];
                $permissions = json_decode($row['permissions'], true);

                $this->roles_hierarchy[$role] = $level;
                $this->role_permissions[$role] = $permissions ?: [];
            }
            $result->free();
        }
    }

    /**
     * Inisialisasi role user dari session
     */
    private function initializeUserRole() {
        if (isset($_SESSION['user_id'])) {
            $this->user_id = $_SESSION['user_id'];
            $this->user_role = $_SESSION['role'] ?? 'guest';
        } else {
            $this->user_role = 'guest';
            $this->user_id = null;
        }
    }

    /**
     * Cek apakah user memiliki permission tertentu
     */
    public function hasPermission($permission) {
        if (!isset($this->role_permissions[$this->user_role])) {
            return false;
        }

        return isset($this->role_permissions[$this->user_role][$permission]) &&
               $this->role_permissions[$this->user_role][$permission] === true;
    }

    /**
     * Cek apakah user memiliki role tertentu
     */
    public function hasRole($role) {
        return $this->user_role === $role;
    }

    /**
     * Cek apakah user memiliki salah satu role yang ditentukan
     */
    public function hasAnyRole($roles) {
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        return in_array($this->user_role, $roles);
    }

    /**
     * Cek apakah user memiliki role dengan level minimum
     */
    public function hasRoleLevel($min_level) {
        $user_level = $this->roles_hierarchy[$this->user_role] ?? 0;
        return $user_level >= $min_level;
    }

    /**
     * Mendapatkan role user
     */
    public function getUserRole() {
        return $this->user_role;
    }

    /**
     * Mendapatkan level role user
     */
    public function getUserRoleLevel() {
        return $this->roles_hierarchy[$this->user_role] ?? 0;
    }

    /**
     * Mendapatkan semua roles yang tersedia
     */
    public function getAllRoles() {
        return array_keys($this->roles_hierarchy);
    }

    /**
     * Mendapatkan hierarki role
     */
    public function getRoleHierarchy() {
        return $this->roles_hierarchy;
    }

    /**
     * Mendapatkan permissions role
     */
    public function getRolePermissions($role = null) {
        $role = $role ?? $this->user_role;
        return $this->role_permissions[$role] ?? [];
    }

    /**
     * Cek apakah role ada
     */
    public function roleExists($role) {
        return isset($this->roles_hierarchy[$role]);
    }

    /**
     * Membandingkan dua roles (untuk hierarki)
     */
    public function compareRoles($role1, $role2) {
        $level1 = $this->roles_hierarchy[$role1] ?? 0;
        $level2 = $this->roles_hierarchy[$role2] ?? 0;

        if ($level1 > $level2) {
            return 1; // role1 lebih tinggi
        } elseif ($level1 < $level2) {
            return -1; // role2 lebih tinggi
        } else {
            return 0; // level sama
        }
    }

    /**
     * Mendapatkan roles yang lebih tinggi dari role user saat ini
     */
    public function getHigherRoles() {
        $current_level = $this->getUserRoleLevel();
        $higher_roles = [];

        foreach ($this->roles_hierarchy as $role => $level) {
            if ($level > $current_level) {
                $higher_roles[] = $role;
            }
        }

        return $higher_roles;
    }

    /**
     * Mendapatkan roles yang lebih rendah dari role user saat ini
     */
    public function getLowerRoles() {
        $current_level = $this->getUserRoleLevel();
        $lower_roles = [];

        foreach ($this->roles_hierarchy as $role => $level) {
            if ($level < $current_level) {
                $lower_roles[] = $role;
            }
        }

        return $lower_roles;
    }

    /**
     * Cek apakah user saat ini bisa mengelola user lain
     */
    public function canManageUser($target_user_role) {
        $current_level = $this->getUserRoleLevel();
        $target_level = $this->roles_hierarchy[$target_user_role] ?? 0;

        // Bisa mengelola user dengan role level yang lebih rendah atau sama
        return $current_level > $target_level;
    }

    /**
     * Mendapatkan nama tampilan role
     */
    public function getRoleDisplayName($role) {
        $display_names = [
            'super_admin' => 'Super Administrator',
            'admin' => 'Administrator',
            'manager' => 'Manager',
            'staff' => 'Staff',
            'user' => 'User',
            'guest' => 'Guest'
        ];

        return $display_names[$role] ?? ucfirst(str_replace('_', ' ', $role));
    }

    /**
     * Mendapatkan class badge untuk role
     */
    public function getRoleBadgeClass($role) {
        $badge_classes = [
            'super_admin' => 'badge bg-danger',
            'admin' => 'badge bg-primary',
            'manager' => 'badge bg-warning',
            'staff' => 'badge bg-info',
            'user' => 'badge bg-secondary',
            'guest' => 'badge bg-light text-dark'
        ];

        return $badge_classes[$role] ?? 'badge bg-secondary';
    }

    /**
     * Validasi transisi role
     */
    public function canChangeRole($current_role, $new_role) {
        // Super admin bisa mengubah role apapun
        if ($this->user_role === 'super_admin') {
            return true;
        }

        // Admin bisa mengubah role kecuali super_admin
        if ($this->user_role === 'admin') {
            return $new_role !== 'super_admin';
        }

        // Manager hanya bisa mengubah ke staff dan user
        if ($this->user_role === 'manager') {
            return in_array($new_role, ['staff', 'user']);
        }

        // Staff hanya bisa mengubah ke user
        if ($this->user_role === 'staff') {
            return $new_role === 'user';
        }

        // User biasa tidak bisa mengubah role
        return false;
    }

    /**
     * Mendapatkan menu items berdasarkan role user
     */
    public function getAllowedMenuItems() {
        $menu_items = [
            'dashboard' => [
                'title' => 'Dashboard',
                'icon' => 'bi-grid-fill',
                'url' => 'index.php?halaman=beranda',
                'permission' => 'view_dashboard'
            ],
            'categories' => [
                'title' => 'Kategori',
                'icon' => 'bi-grid-3x3-gap-fill',
                'url' => 'index.php?halaman=kategori',
                'permission' => 'category_management'
            ],
            'products' => [
                'title' => 'Produk',
                'icon' => 'bi-box-seam-fill',
                'url' => 'index.php?halaman=produk',
                'permission' => 'product_management'
            ],
            'sales' => [
                'title' => 'Laporan Penjualan',
                'icon' => 'bi-receipt-cutoff',
                'url' => 'index.php?halaman=penjualan',
                'permission' => 'sales_management'
            ],
            'reports' => [
                'title' => 'Laporan',
                'icon' => 'bi-bar-chart-fill',
                'url' => 'index.php?halaman=reports',
                'permission' => 'report_management'
            ],
            'users' => [
                'title' => 'Manajemen User',
                'icon' => 'bi-people-fill',
                'url' => 'index.php?halaman=users',
                'permission' => 'user_management'
            ],
            'roles' => [
                'title' => 'Manajemen Role',
                'icon' => 'bi-shield-check',
                'url' => 'index.php?halaman=roles',
                'permission' => 'role_management'
            ],
            'settings' => [
                'title' => 'Pengaturan',
                'icon' => 'bi-gear-fill',
                'url' => 'index.php?halaman=settings',
                'permission' => 'system_settings'
            ],
            'profile' => [
                'title' => 'Profil',
                'icon' => 'bi-person-circle',
                'url' => 'index.php?halaman=profile',
                'permission' => 'view_profile'
            ]
        ];

        $allowed_items = [];

        foreach ($menu_items as $key => $item) {
            if ($this->hasPermission($item['permission'])) {
                $allowed_items[$key] = $item;
            }
        }

        return $allowed_items;
    }

    /**
     * Hapus role dari sistem
     */
    public function deleteRole($role_name) {
        // Cek apakah role ada
        if (!$this->roleExists($role_name)) {
            return ['success' => false, 'message' => 'Role tidak ditemukan'];
        }

        // Cek apakah ada user yang menggunakan role ini
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM user WHERE role = ?");
        $stmt->bind_param("s", $role_name);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_assoc()['count'];
        $stmt->close();

        if ($count > 0) {
            return ['success' => false, 'message' => "Role '$role_name' masih digunakan oleh $count user"];
        }

        // Hapus role dari database
        $stmt = $this->connection->prepare("DELETE FROM roles WHERE role_name = ?");
        $stmt->bind_param("s", $role_name);

        if ($stmt->execute()) {
            $stmt->close();

            // Reload roles dari database
            $this->loadRolesFromDB();

            // Log aksi penghapusan
            $this->logRoleAction('DELETE_ROLE', "Role: $role_name");

            return ['success' => true, 'message' => "Role '$role_name' berhasil dihapus"];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Gagal menghapus role dari database'];
        }
    }

    /**
     * Log aksi berbasis role
     */
    public function logRoleAction($action, $details = '') {
        if ($this->user_id) {
            $log_data = [
                'user_id' => $this->user_id,
                'user_role' => $this->user_role,
                'action' => $action,
                'details' => $details,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'timestamp' => date('Y-m-d H:i:s')
            ];

            // Anda bisa mengimplementasikan logging ke database di sini
            error_log("ROLE_ACTION: " . json_encode($log_data));
        }
    }
}

// Fungsi helper untuk akses mudah
function hasPermission($permission) {
    global $role_manager;
    return $role_manager ? $role_manager->hasPermission($permission) : false;
}

function hasRole($role) {
    global $role_manager;
    return $role_manager ? $role_manager->hasRole($role) : false;
}

function hasAnyRole($roles) {
    global $role_manager;
    return $role_manager ? $role_manager->hasAnyRole($roles) : false;
}

function hasRoleLevel($min_level) {
    global $role_manager;
    return $role_manager ? $role_manager->hasRoleLevel($min_level) : false;
}

function getUserRole() {
    global $role_manager;
    return $role_manager ? $role_manager->getUserRole() : 'guest';
}

function getRoleDisplayName($role) {
    global $role_manager;
    return $role_manager ? $role_manager->getRoleDisplayName($role) : ucfirst($role);
}

function getRoleBadgeClass($role) {
    global $role_manager;
    return $role_manager ? $role_manager->getRoleBadgeClass($role) : 'badge bg-secondary';
}

function canManageUser($target_user_role) {
    global $role_manager;
    return $role_manager ? $role_manager->canManageUser($target_user_role) : false;
}
?>
