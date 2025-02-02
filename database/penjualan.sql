-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 01, 2025 at 03:53 PM
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
-- Database: `penjualan`
--

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `nik` varchar(25) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `foto` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `nik`, `name`, `email`, `phone`, `address`, `foto`, `created_at`) VALUES
(1, '3208537891950004', 'Muhammad Hidayat', 'hidayat@gmail.com', '085967028702', 'Cilimus, Kuningan', 'assets/uploads/user/IMG_20240924_103525.png', '2025-01-19 14:41:53'),
(4, '3208234576519865', 'Syahrani', 'syah@gmail.com', '087656275431', 'Cirebon', 'assets/uploads/user/OIP (1).jpg', '2025-02-01 14:51:54');

-- --------------------------------------------------------

--
-- Table structure for table `mobil`
--

CREATE TABLE `mobil` (
  `mobil_id` int(11) NOT NULL,
  `model` varchar(100) NOT NULL,
  `brand` varchar(100) NOT NULL,
  `price` varchar(20) NOT NULL,
  `warna` varchar(50) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `foto` varchar(300) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mobil`
--

INSERT INTO `mobil` (`mobil_id`, `model`, `brand`, `price`, `warna`, `stock`, `foto`, `created_at`) VALUES
(1, 'Civic', 'Honda', '300000000', 'Putih', 9, 'assets/uploads/mobil/civic.jpeg', '2025-01-19 13:49:23'),
(2, 'Avanza', 'Toyota', '200000000', 'Putih', 14, 'assets/uploads/mobil/avanza.jpeg', '2025-01-19 13:49:23'),
(3, 'Fortuner', 'Toyota', '500000000', 'Abu-abu', 4, 'assets/uploads/mobil/fortuner.jpeg', '2025-01-19 13:49:23'),
(4, 'Xenia', 'Daihatsu', '190000000', 'Merah', 20, 'assets/uploads/mobil/xenia.jpeg', '2025-01-19 13:49:23'),
(5, 'Pajero', 'Mitsubishi', '550000000', 'Silver', 8, 'assets/uploads/mobil/pajero.jpeg', '2025-01-19 13:49:23'),
(6, 'CR-V', 'Honda', '450000000', 'Biru', 7, 'assets/uploads/mobil/cr-v.jpeg', '2025-01-19 13:49:23'),
(7, 'Innova', 'Toyota', '350000000', 'Hitam', 10, 'assets/uploads/mobil/inova.jpeg', '2025-01-19 13:49:23'),
(8, 'Jazz', 'Honda', '250000000', 'Kuning', 18, 'assets/uploads/mobil/jazz.png', '2025-01-19 13:49:23');

-- --------------------------------------------------------

--
-- Table structure for table `sale`
--

CREATE TABLE `sale` (
  `sale_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `mobil_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sale_date` date NOT NULL,
  `total_price` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_type` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sale`
--

INSERT INTO `sale` (`sale_id`, `customer_id`, `mobil_id`, `user_id`, `sale_date`, `total_price`, `created_at`, `payment_type`) VALUES
(1, 1, 3, 4, '2025-01-19', '500000000', '2025-01-19 16:31:12', 'Cash'),
(4, 4, 7, 4, '2025-02-01', '350000000', '2025-02-01 14:52:41', 'Cash');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `nama` char(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','sales') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `nama`, `username`, `password`, `role`, `created_at`) VALUES
(4, 'Budi', 'budi', '$2y$10$o8WRhz9VgC9HptXT/aOi5uIGhczyuZa7UjVmVVrMicQ3NhZJTFEOu', 'sales', '2024-07-14 14:49:19'),
(7, 'Hidayat', 'hidayat', '$2y$10$LZXZz1uFDX/DlBe/VCBhXOalXCP5jAJW22IAsFDgrMWlE0Q/VDcpK', 'admin', '2025-01-17 16:10:39');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `mobil`
--
ALTER TABLE `mobil`
  ADD PRIMARY KEY (`mobil_id`);

--
-- Indexes for table `sale`
--
ALTER TABLE `sale`
  ADD PRIMARY KEY (`sale_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `motorcycle_id` (`mobil_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `mobil`
--
ALTER TABLE `mobil`
  MODIFY `mobil_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `sale`
--
ALTER TABLE `sale`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `sale`
--
ALTER TABLE `sale`
  ADD CONSTRAINT `sale_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`),
  ADD CONSTRAINT `sale_ibfk_2` FOREIGN KEY (`mobil_id`) REFERENCES `mobil` (`mobil_id`),
  ADD CONSTRAINT `sale_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
