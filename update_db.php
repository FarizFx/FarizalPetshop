<?php
include "./function/connection.php";

try {
    // Jalankan migrasi untuk menambahkan kolom harga_modal ke detail_penjualan
    $sql = "
        -- Tambahkan kolom harga_modal ke tabel detail_penjualan
        ALTER TABLE detail_penjualan ADD COLUMN harga_modal DECIMAL(15,2) NOT NULL DEFAULT 0 AFTER harga;
    ";

    if (mysqli_query($connection, $sql)) {
        echo "Kolom harga_modal berhasil ditambahkan ke tabel detail_penjualan.\n";

        // Update data yang sudah ada dengan harga_modal dari tabel produk
        $update_sql = "
            UPDATE detail_penjualan dp
            JOIN produk p ON dp.id_produk = p.id_produk
            SET dp.harga_modal = p.harga_modal
            WHERE dp.harga_modal = 0;
        ";

        if (mysqli_query($connection, $update_sql)) {
            echo "Data harga_modal berhasil diupdate dari tabel produk.\n";
            echo "Migrasi database selesai!\n";
        } else {
            echo "Error updating data: " . mysqli_error($connection) . "\n";
        }
    } else {
        echo "Error adding column: " . mysqli_error($connection) . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

mysqli_close($connection);
?>
