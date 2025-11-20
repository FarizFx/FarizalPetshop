<?php
include "./function/connection.php";

$updates = [
    "UPDATE roles SET permissions = JSON_SET(permissions, '$.stock_management', true) WHERE role_name = 'super_admin'",
    "UPDATE roles SET permissions = JSON_SET(permissions, '$.stock_management', true) WHERE role_name = 'admin'",
    "UPDATE roles SET permissions = JSON_SET(permissions, '$.stock_management', true) WHERE role_name = 'manager'",
    "UPDATE roles SET permissions = JSON_SET(permissions, '$.stock_management', true) WHERE role_name = 'staff'",
    "UPDATE roles SET permissions = JSON_SET(permissions, '$.stock_management', false) WHERE role_name = 'user'",
    "UPDATE roles SET permissions = JSON_SET(permissions, '$.stock_management', false) WHERE role_name = 'guest'"
];

foreach ($updates as $query) {
    if (mysqli_query($connection, $query)) {
        echo "Updated: " . substr($query, strpos($query, "role_name = '") + 13, -1) . PHP_EOL;
    } else {
        echo "Error updating: " . mysqli_error($connection) . PHP_EOL;
    }
}

echo "Role permissions updated successfully.";
?>
