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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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

INSERT INTO roles (role_name, role_level, permissions) VALUES
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
)),
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
)),
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
)),
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
)),
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
)),
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
));

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
