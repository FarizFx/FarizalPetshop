<?php
include "./function/connection.php";

$query = "ALTER TABLE produk CHANGE harga harga_jual DECIMAL(15,2) NOT NULL DEFAULT 0";
if ($connection->query($query)) {
    echo "Column renamed successfully from 'harga' to 'harga_jual'\n";
} else {
    echo "Error: " . $connection->error . "\n";
}
?>
