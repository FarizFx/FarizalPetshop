<?php
include 'function/connection.php';

echo "Testing profit calculation with sample data...\n";

// Insert sample sales data if not exists
$query = 'SELECT COUNT(*) as count FROM penjualan';
$result = $connection->query($query);
$count = $result->fetch_assoc()['count'];

if ($count == 0) {
    echo 'Inserting sample sales data...\n';

    // Insert sample penjualan
    $connection->query("INSERT INTO penjualan (tanggal, total_harga) VALUES ('" . date('Y-m-d') . "', 20000)");
    $id_penjualan = $connection->insert_id;

    // Insert sample detail_penjualan
    $connection->query("INSERT INTO detail_penjualan (id_penjualan, id_produk, qty, harga, subtotal) VALUES ($id_penjualan, 1, 1, 20000, 20000)");

    echo 'Sample data inserted.\n';
}

// Now test the calculations
$query = 'SELECT COALESCE(SUM(dp.qty * pr.harga_modal), 0) as total_modal_harian FROM detail_penjualan dp LEFT JOIN penjualan p ON dp.id_penjualan = p.id_penjualan LEFT JOIN produk pr ON dp.id_produk = pr.id_produk WHERE DATE(p.tanggal) = CURDATE()';
$result = $connection->query($query);
$row = $result->fetch_assoc();
echo 'Total modal harian: Rp ' . number_format($row['total_modal_harian'], 0, ',', '.') . "\n";

$query = 'SELECT SUM(p.total_harga) as total_pendapatan, COALESCE(SUM(dp.qty * pr.harga_modal), 0) as total_modal FROM penjualan p LEFT JOIN detail_penjualan dp ON p.id_penjualan = dp.id_penjualan LEFT JOIN produk pr ON dp.id_produk = pr.id_produk WHERE DATE(p.tanggal) = CURDATE()';
$result = $connection->query($query);
$row = $result->fetch_assoc();
$profit = ($row['total_pendapatan'] ?? 0) - ($row['total_modal'] ?? 0);
echo 'Total pendapatan harian: Rp ' . number_format($row['total_pendapatan'] ?? 0, 0, ',', '.') . "\n";
echo 'Total profit harian: Rp ' . number_format($profit, 0, ',', '.') . "\n";

echo "Profit calculation test completed.\n";
?>
