-- Tambahkan kolom harga_modal ke tabel detail_penjualan
ALTER TABLE detail_penjualan ADD COLUMN harga_modal DECIMAL(15,2) NOT NULL DEFAULT 0 AFTER harga;

-- Update data yang sudah ada dengan harga_modal dari tabel produk
UPDATE detail_penjualan dp
JOIN produk p ON dp.id_produk = p.id_produk
SET dp.harga_modal = p.harga_modal
WHERE dp.harga_modal = 0;
