-- Create pembelian table
CREATE TABLE IF NOT EXISTS pembelian (
    id_pembelian INT(11) NOT NULL AUTO_INCREMENT,
    tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_harga DECIMAL(15,2) NOT NULL DEFAULT 0,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_pembelian)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create detail_pembelian table
CREATE TABLE IF NOT EXISTS detail_pembelian (
    id_detail INT(11) NOT NULL AUTO_INCREMENT,
    id_pembelian INT(11) NOT NULL,
    id_produk INT(11) NOT NULL,
    qty INT(11) NOT NULL,
    harga DECIMAL(15,2) NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_detail),
    FOREIGN KEY (id_pembelian) REFERENCES pembelian(id_pembelian) ON DELETE CASCADE,
    FOREIGN KEY (id_produk) REFERENCES produk(id_produk) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add indexes for better performance
ALTER TABLE pembelian ADD INDEX idx_tanggal (tanggal);
ALTER TABLE detail_pembelian ADD INDEX idx_id_pembelian (id_pembelian);
ALTER TABLE detail_pembelian ADD INDEX idx_id_produk (id_produk);
