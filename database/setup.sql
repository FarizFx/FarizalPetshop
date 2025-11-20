-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 06, 2023 at 05:14 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `farizalpetshop_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `kontak`
--

CREATE TABLE `kontak` (
  `id` int(11) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `nomor_hp` varchar(15) NOT NULL,
  `status` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `username` varchar(25) NOT NULL,
  `password` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL DEFAULT '',
  `role` varchar(50) NOT NULL DEFAULT 'user',
  `foto_profil` varchar(255) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL DEFAULT 1,
  `store_name` varchar(255) DEFAULT '',
  `store_address` text,
  `store_phone` varchar(20) DEFAULT '',
  `store_email` varchar(255) DEFAULT '',
  `store_description` text,
  `theme` varchar(20) DEFAULT 'light',
  `language` varchar(10) DEFAULT 'id',
  `email_notifications` tinyint(1) DEFAULT 0,
  `currency` varchar(10) DEFAULT 'IDR',
  `date_format` varchar(20) DEFAULT 'd/m/Y',
  `timezone` varchar(50) DEFAULT 'Asia/Jakarta',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE IF NOT EXISTS roles (
    role_name VARCHAR(50) PRIMARY KEY,
    role_level INT NOT NULL,
    permissions JSON NOT NULL,
    stock_permission BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE IF NOT EXISTS kategori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE IF NOT EXISTS produk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    kategori_id INT NOT NULL,
    harga DECIMAL(10,2) NOT NULL,
    harga_modal DECIMAL(15,2) NOT NULL DEFAULT 0,
    stok INT NOT NULL DEFAULT 0,
    satuan VARCHAR(50) DEFAULT 'pcs',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE CASCADE
);

-- --------------------------------------------------------

--
-- Table structure for table `pembelian`
--

CREATE TABLE IF NOT EXISTS pembelian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATE NOT NULL,
    supplier VARCHAR(255) NOT NULL,
    total DECIMAL(10,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- --------------------------------------------------------

--
-- Table structure for table `detail_pembelian`
--

CREATE TABLE IF NOT EXISTS detail_pembelian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pembelian_id INT NOT NULL,
    produk_id INT NOT NULL,
    jumlah INT NOT NULL,
    satuan VARCHAR(50) DEFAULT 'pcs',
    harga DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pembelian_id) REFERENCES pembelian(id) ON DELETE CASCADE,
    FOREIGN KEY (produk_id) REFERENCES produk(id) ON DELETE CASCADE
);

-- --------------------------------------------------------

--
-- Table structure for table `penjualan`
--

CREATE TABLE IF NOT EXISTS penjualan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATE NOT NULL,
    customer VARCHAR(255) NOT NULL,
    total DECIMAL(10,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- --------------------------------------------------------

--
-- Table structure for table `detail_penjualan`
--

CREATE TABLE IF NOT EXISTS detail_penjualan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    penjualan_id INT NOT NULL,
    produk_id INT NOT NULL,
    jumlah INT NOT NULL,
    satuan VARCHAR(50) DEFAULT 'pcs',
    harga DECIMAL(10,2) NOT NULL,
    harga_modal DECIMAL(15,2) NOT NULL DEFAULT 0,
    subtotal DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (penjualan_id) REFERENCES penjualan(id) ON DELETE CASCADE,
    FOREIGN KEY (produk_id) REFERENCES produk(id) ON DELETE CASCADE
);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `kontak`
--
ALTER TABLE `kontak`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `kontak`
--
ALTER TABLE `kontak`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`) VALUES (1);

--
-- Dumping data for table `roles`
--

INSERT INTO roles (role_name, role_level, permissions, stock_permission) VALUES
('super_admin', 100, JSON_OBJECT(
    'user_management', true,
    'role_management', true,
    'product_management', true,
    'category_management', true,
    'sales_management', true,
    'report_management', true,
    'system_settings', true,
    'backup_restore', true,
    'view_dashboard', true,
    'view_profile', true,
    'edit_profile', true,
    'change_password', true
), TRUE),
('admin', 80, JSON_OBJECT(
    'user_management', true,
    'role_management', false,
    'product_management', true,
    'category_management', true,
    'sales_management', true,
    'report_management', true,
    'system_settings', true,
    'backup_restore', true,
    'view_dashboard', true,
    'view_profile', true,
    'edit_profile', true,
    'change_password', true
), TRUE),
('manager', 60, JSON_OBJECT(
    'user_management', false,
    'role_management', false,
    'product_management', true,
    'category_management', true,
    'sales_management', true,
    'report_management', true,
    'system_settings', false,
    'backup_restore', false,
    'view_dashboard', true,
    'view_profile', true,
    'edit_profile', true,
    'change_password', true
), TRUE),
('staff', 40, JSON_OBJECT(
    'user_management', false,
    'role_management', false,
    'product_management', true,
    'category_management', false,
    'sales_management', true,
    'report_management', false,
    'system_settings', false,
    'backup_restore', false,
    'view_dashboard', true,
    'view_profile', true,
    'edit_profile', true,
    'change_password', true
), TRUE),
('user', 20, JSON_OBJECT(
    'user_management', false,
    'role_management', false,
    'product_management', false,
    'category_management', false,
    'sales_management', false,
    'report_management', false,
    'system_settings', false,
    'backup_restore', false,
    'view_dashboard', true,
    'view_profile', true,
    'edit_profile', true,
    'change_password', true
), FALSE),
('guest', 0, JSON_OBJECT(
    'user_management', false,
    'role_management', false,
    'product_management', false,
    'category_management', false,
    'sales_management', false,
    'report_management', false,
    'system_settings', false,
    'backup_restore', false,
    'view_dashboard', false,
    'view_profile', false,
    'edit_profile', false,
    'change_password', false
), FALSE);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
