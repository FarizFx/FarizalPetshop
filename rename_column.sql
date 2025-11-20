-- Rename column 'harga' to 'harga_jual' in produk table
ALTER TABLE produk CHANGE harga harga_jual DECIMAL(15,2) NOT NULL DEFAULT 0;
