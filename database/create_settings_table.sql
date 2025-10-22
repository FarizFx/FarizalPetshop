-- Create settings table for Farizal Petshop
-- Run this SQL to create the settings table

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

-- Insert default settings
INSERT INTO `settings` (`id`) VALUES (1);

-- Add primary key
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);
