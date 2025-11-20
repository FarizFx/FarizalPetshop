-- Tambahkan kolom satuan ke tabel produk
ALTER TABLE produk ADD COLUMN satuan VARCHAR(50) NOT NULL DEFAULT 'pcs' AFTER deskripsi;

-- Tambahkan kolom satuan ke tabel detail_pembelian
ALTER TABLE detail_pembelian ADD COLUMN satuan VARCHAR(50) NOT NULL DEFAULT 'pcs' AFTER subtotal;

-- Update produk yang sudah ada dengan satuan default 'pcs'
UPDATE produk SET satuan = 'pcs' WHERE satuan = '' OR satuan IS NULL;

-- Update detail_pembelian yang sudah ada dengan satuan default 'pcs'
UPDATE detail_pembelian SET satuan = 'pcs' WHERE satuan = '' OR satuan IS NULL;
