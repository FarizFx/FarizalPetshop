<?php
include "./function/connection.php";
include "./function/role_manager.php";

session_start();

$role_manager = new RoleManager($connection);
echo 'User role: ' . $role_manager->getUserRole() . PHP_EOL;
echo 'Has stock_management: ' . ($role_manager->hasPermission('stock_management') ? 'YES' : 'NO') . PHP_EOL;
echo 'Has view_dashboard: ' . ($role_manager->hasPermission('view_dashboard') ? 'YES' : 'NO') . PHP_EOL;
?>
