<?php
include "./function/connection.php";

$result = $connection->query('DESCRIBE produk');
echo "Current columns in produk table:\n";
while ($row = $result->fetch_assoc()) {
    echo "- " . $row['Field'] . "\n";
}
?>
