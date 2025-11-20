<?php
include 'function/connection.php';

$stmt = $connection->prepare("SELECT role, COUNT(*) as count FROM user GROUP BY role");
$stmt->execute();
$result = $stmt->get_result();

echo "User count by role:\n";
while($row = $result->fetch_assoc()) {
    echo $row['role'] . ': ' . $row['count'] . "\n";
}
?>
