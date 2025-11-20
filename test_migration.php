<?php
include 'function/connection.php';

echo "Testing database migration...\n";

// Check if harga_modal column exists
$result = $connection->query("SHOW COLUMNS FROM produk LIKE 'harga_modal'");
if ($result->num_rows > 0) {
    echo "✓ Kolom harga_modal sudah ada\n";
} else {
    echo "✗ Kolom harga_modal belum ada, menambahkan...\n";
    $query = "ALTER TABLE produk ADD COLUMN harga_modal DECIMAL(15,2) NOT NULL DEFAULT 0 AFTER harga";
    if ($connection->query($query)) {
        echo "✓ Kolom harga_modal berhasil ditambahkan\n";
    } else {
        echo "✗ Error menambahkan kolom: " . $connection->error . "\n";
    }
}

// Update existing products with default harga_modal
$update_query = "UPDATE produk SET harga_modal = harga * 0.5 WHERE harga_modal = 0 OR harga_modal IS NULL";
if ($connection->query($update_query)) {
    echo "✓ Data harga_modal berhasil diupdate untuk produk yang ada\n";
} else {
    echo "✗ Error update data: " . $connection->error . "\n";
}

// Check some sample data
$result = $connection->query("SELECT nama_produk, harga, harga_modal FROM produk LIMIT 5");
if ($result) {
    echo "\nSample data produk:\n";
    while ($row = $result->fetch_assoc()) {
        echo "- {$row['nama_produk']}: Harga jual Rp " . number_format($row['harga'], 0, ',', '.') . ", Modal Rp " . number_format($row['harga_modal'], 0, ',', '.') . "\n";
    }
}

echo "\nMigration test selesai.\n";
?>
