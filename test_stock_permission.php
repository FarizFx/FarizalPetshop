<?php
include 'function/connection.php';
include_once 'function/role_manager.php';

$role_manager = new RoleManager($connection);
$allowed = $role_manager->getAllowedMenuItems();

echo "Stock menu is " . (isset($allowed['stock']) ? 'allowed' : 'NOT allowed') . "\n";
echo "User role: " . $role_manager->getUserRole() . "\n";
echo "Has stock_management permission: " . ($role_manager->hasPermission('stock_management') ? 'YES' : 'NO') . "\n";
?>
