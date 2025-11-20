<?php
include 'function/connection.php';

$query = "UPDATE roles SET permissions = JSON_SET(permissions, '$.purchase_management', true) WHERE role_name IN ('super_admin', 'admin', 'manager', 'staff')";
$result = mysqli_query($connection, $query);
echo $result ? 'Permissions updated successfully' : 'Error: ' . mysqli_error($connection);
?>
