<?php
include 'function/connection.php';

$stmt = $connection->prepare("SELECT role_name FROM roles WHERE role_name != 'super_admin'");
$stmt->execute();
$result = $stmt->get_result();

echo "Roles that can be deleted:\n";
while($row = $result->fetch_assoc()) {
    echo $row['role_name'] . "\n";
}
?>
