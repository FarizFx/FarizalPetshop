-- Tambahkan kolom harga_modal ke tabel produk
ALTER TABLE produk ADD COLUMN harga_modal DECIMAL(15,2) NOT NULL DEFAULT 0 AFTER harga;

-- Update produk yang sudah ada dengan harga_modal = harga_jual / 2 (sebagai default)
UPDATE produk SET harga_modal = harga * 0.5 WHERE harga_modal = 0;
