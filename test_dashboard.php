<?php
include 'function/connection.php';

echo "Testing dashboard calculations...\n";

// Test daily modal calculation
$query = "SELECT COALESCE(SUM(dp.qty * pr.harga_modal), 0) as total_modal_harian FROM detail_penjualan dp LEFT JOIN penjualan p ON dp.id_penjualan = p.id_penjualan LEFT JOIN produk pr ON dp.id_produk = pr.id_produk WHERE DATE(p.tanggal) = CURDATE()";
$result = $connection->query($query);
$row = $result->fetch_assoc();
echo "Total modal harian: Rp " . number_format($row['total_modal_harian'], 0, ',', '.') . "\n";

// Test monthly modal calculation
$query = "SELECT COALESCE(SUM(dp.qty * pr.harga_modal), 0) as total_modal_bulan FROM detail_penjualan dp LEFT JOIN penjualan p ON dp.id_penjualan = p.id_penjualan LEFT JOIN produk pr ON dp.id_produk = pr.id_produk WHERE MONTH(p.tanggal) = MONTH(CURDATE()) AND YEAR(p.tanggal) = YEAR(CURDATE())";
$result = $connection->query($query);
$row = $result->fetch_assoc();
echo "Total modal bulan ini: Rp " . number_format($row['total_modal_bulan'], 0, ',', '.') . "\n";

// Test profit calculation
$query = "SELECT (SUM(p.total_harga) - COALESCE(SUM(dp.qty * pr.harga_modal), 0)) as total_profit_harian FROM penjualan p LEFT JOIN detail_penjualan dp ON p.id_penjualan = dp.id_penjualan LEFT JOIN produk pr ON dp.id_produk = pr.id_produk WHERE DATE(p.tanggal) = CURDATE()";
$result = $connection->query($query);
$row = $result->fetch_assoc();
echo "Total profit harian: Rp " . number_format($row['total_profit_harian'] ?? 0, 0, ',', '.') . "\n";

echo "Dashboard calculation test selesai.\n";
?>
