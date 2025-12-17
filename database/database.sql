-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 15, 2025 at 07:49 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `login_petshop`
--

-- --------------------------------------------------------

--
-- Table structure for table `detail_pembelian`
--

CREATE TABLE `detail_pembelian` (
  `id_detail` int(11) NOT NULL,
  `id_pembelian` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `harga` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `satuan` varchar(50) NOT NULL DEFAULT 'pcs',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_pembelian`
--

INSERT INTO `detail_pembelian` (`id_detail`, `id_pembelian`, `id_produk`, `qty`, `harga`, `subtotal`, `satuan`, `created_at`) VALUES
(7, 5, 8, 2, 10000.00, 20000.00, 'pcs', '2025-11-27 13:20:48');

-- --------------------------------------------------------

--
-- Table structure for table `detail_penjualan`
--

CREATE TABLE `detail_penjualan` (
  `id_detail` int(11) NOT NULL,
  `id_penjualan` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `harga` decimal(12,2) NOT NULL,
  `harga_jual` decimal(15,2) NOT NULL DEFAULT 0.00,
  `harga_modal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_penjualan`
--

INSERT INTO `detail_penjualan` (`id_detail`, `id_penjualan`, `id_produk`, `qty`, `harga`, `harga_jual`, `harga_modal`, `subtotal`) VALUES
(32, 23, 8, 1, 20000.00, 20000.00, 10000.00, 20000.00),
(33, 23, 10, 1, 38000.00, 38000.00, 19000.00, 38000.00),
(40, 29, 8, 1, 20000.00, 0.00, 10000.00, 20000.00),
(41, 29, 9, 1, 15000.00, 0.00, 7500.00, 15000.00),
(42, 29, 10, 1, 38000.00, 0.00, 19000.00, 38000.00),
(43, 29, 11, 1, 38000.00, 0.00, 19000.00, 38000.00),
(44, 30, 8, 1, 20000.00, 0.00, 10000.00, 20000.00),
(45, 30, 9, 3, 15000.00, 0.00, 7500.00, 45000.00);

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`, `deskripsi`, `created_at`, `updated_at`) VALUES
(1, 'Makanan Kucing', 'Makanan untuk kucing', '2025-07-21 23:21:08', '2025-10-01 06:13:04'),
(2, 'Pasir Kucing', 'pasir kucing', '2025-07-22 02:46:59', '2025-07-22 02:46:59');

-- --------------------------------------------------------

--
-- Table structure for table `pembelian`
--

CREATE TABLE `pembelian` (
  `id_pembelian` int(11) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_harga` decimal(15,2) NOT NULL DEFAULT 0.00,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pembelian`
--

INSERT INTO `pembelian` (`id_pembelian`, `tanggal`, `total_harga`, `keterangan`, `created_at`, `updated_at`) VALUES
(5, '2025-11-27 13:08:00', 20000.00, '', '2025-11-27 13:20:48', '2025-11-27 13:20:48');

-- --------------------------------------------------------

--
-- Table structure for table `penjualan`
--

CREATE TABLE `penjualan` (
  `id_penjualan` int(11) NOT NULL,
  `tanggal` datetime NOT NULL DEFAULT current_timestamp(),
  `total_harga` decimal(12,2) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penjualan`
--

INSERT INTO `penjualan` (`id_penjualan`, `tanggal`, `total_harga`, `keterangan`) VALUES
(23, '2025-07-24 13:10:38', 58000.00, NULL),
(25, '2025-09-16 11:35:28', 0.00, NULL),
(29, '2025-11-27 16:29:08', 111000.00, NULL),
(30, '2025-12-15 15:31:53', 65000.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id_produk` int(11) NOT NULL,
  `id_kategori` int(11) NOT NULL,
  `nama_produk` varchar(255) NOT NULL,
  `merk` varchar(100) DEFAULT NULL,
  `harga_jual` decimal(15,2) NOT NULL DEFAULT 0.00,
  `harga_modal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `stok` int(11) NOT NULL DEFAULT 0,
  `deskripsi` text DEFAULT NULL,
  `satuan` varchar(50) NOT NULL DEFAULT 'pcs',
  `gambar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id_produk`, `id_kategori`, `nama_produk`, `merk`, `harga_jual`, `harga_modal`, `stok`, `deskripsi`, `satuan`, `gambar`, `created_at`, `updated_at`) VALUES
(8, 1, 'Bolt Kitten 800gr', 'Bolt', 20000.00, 10000.00, 12, '', 'pcs', NULL, '2025-07-24 06:09:14', '2025-12-15 08:31:53'),
(9, 1, 'Felibite 500gr', 'Felibite', 15000.00, 7500.00, 13, '', 'pcs', NULL, '2025-07-24 06:09:35', '2025-12-15 08:31:53'),
(10, 2, 'Markotops Lavender 5,5L', 'Markotops', 38000.00, 19000.00, 11, '', 'pcs', NULL, '2025-07-24 06:09:51', '2025-11-27 09:29:08'),
(11, 2, 'Royal Belle 5L', 'Royal Belle S', 38000.00, 19000.00, 9, '', 'pcs', NULL, '2025-07-24 06:10:17', '2025-11-27 09:29:08');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_name` varchar(50) NOT NULL,
  `role_level` int(11) NOT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`permissions`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_name`, `role_level`, `permissions`, `created_at`, `updated_at`) VALUES
('admin', 80, '{\"user_management\": true, \"role_management\": false, \"product_management\": true, \"category_management\": true, \"sales_management\": true, \"report_management\": true, \"system_settings\": true, \"backup_restore\": true, \"view_dashboard\": true, \"view_profile\": true, \"edit_profile\": true, \"change_password\": true, \"stock_management\": true, \"purchase_management\": true}', '2025-10-06 02:18:42', '2025-11-13 20:27:46'),
('Moderator', 50, '{\"product_management\":true}', '2025-12-15 08:15:53', '2025-12-15 08:16:04'),
('super_admin', 100, '{\"user_management\": true, \"role_management\": true, \"product_management\": true, \"category_management\": true, \"sales_management\": true, \"report_management\": true, \"system_settings\": true, \"backup_restore\": true, \"view_dashboard\": true, \"view_profile\": true, \"edit_profile\": true, \"change_password\": true, \"stock_management\": true, \"purchase_management\": true}', '2025-10-06 02:18:42', '2025-11-13 20:27:46');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL DEFAULT 1,
  `store_name` varchar(255) DEFAULT '',
  `store_address` text DEFAULT NULL,
  `store_phone` varchar(20) DEFAULT '',
  `store_email` varchar(255) DEFAULT '',
  `store_description` text DEFAULT NULL,
  `theme` varchar(20) DEFAULT 'light',
  `language` varchar(10) DEFAULT 'id',
  `email_notifications` tinyint(1) DEFAULT 0,
  `currency` varchar(10) DEFAULT 'IDR',
  `date_format` varchar(20) DEFAULT 'd/m/Y',
  `timezone` varchar(50) DEFAULT 'Asia/Jakarta',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `store_name`, `store_address`, `store_phone`, `store_email`, `store_description`, `theme`, `language`, `email_notifications`, `currency`, `date_format`, `timezone`, `created_at`, `updated_at`) VALUES
(1, 'Farizal Petshop', '', '081310108547', '', '', 'dark', 'id', 0, 'IDR', 'd/m/Y', 'Asia/Jakarta', '2025-09-05 21:02:45', '2025-11-27 08:52:04');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `nama`, `username`, `password`, `email`, `role`, `foto_profil`, `created_at`, `updated_at`) VALUES
(1, 'Mad Farizi', 'admin', '$2y$12$1iqtUCb2YgCRQ.jSZ94wyOR9pvVvbzjPAALdJELwMNRcL42Vn916a', 'admin@example.com', 'super_admin', 'profile_1_1757030273.png', '2025-10-15 05:12:59', '2025-10-16 08:10:43'),
(9, 'test', 'test', '$2y$12$5NHokwrHJ/OSkHqD9lGXN.ZjJWRFIch7FtFjEeVuCw03W6DTp7Txa', 'email@example.com', 'Moderator', NULL, '2025-12-15 08:15:37', '2025-12-15 08:16:11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `detail_pembelian`
--
ALTER TABLE `detail_pembelian`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `idx_id_pembelian` (`id_pembelian`),
  ADD KEY `idx_id_produk` (`id_produk`);

--
-- Indexes for table `detail_penjualan`
--
ALTER TABLE `detail_penjualan`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `fk_penjualan` (`id_penjualan`),
  ADD KEY `fk_produk` (`id_produk`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `pembelian`
--
ALTER TABLE `pembelian`
  ADD PRIMARY KEY (`id_pembelian`),
  ADD KEY `idx_tanggal` (`tanggal`);

--
-- Indexes for table `penjualan`
--
ALTER TABLE `penjualan`
  ADD PRIMARY KEY (`id_penjualan`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_name`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `user_ibfk_1` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `detail_pembelian`
--
ALTER TABLE `detail_pembelian`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `detail_penjualan`
--
ALTER TABLE `detail_penjualan`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pembelian`
--
ALTER TABLE `pembelian`
  MODIFY `id_pembelian` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `penjualan`
--
ALTER TABLE `penjualan`
  MODIFY `id_penjualan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_pembelian`
--
ALTER TABLE `detail_pembelian`
  ADD CONSTRAINT `detail_pembelian_ibfk_1` FOREIGN KEY (`id_pembelian`) REFERENCES `pembelian` (`id_pembelian`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_pembelian_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE;

--
-- Constraints for table `detail_penjualan`
--
ALTER TABLE `detail_penjualan`
  ADD CONSTRAINT `fk_penjualan` FOREIGN KEY (`id_penjualan`) REFERENCES `penjualan` (`id_penjualan`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE;

--
-- Constraints for table `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `produk_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`);

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`role`) REFERENCES `roles` (`role_name`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
