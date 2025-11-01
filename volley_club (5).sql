
-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 31, 2025 at 05:01 AM
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
-- Database: `volley_club`
--

-- --------------------------------------------------------

--
-- Table structure for table `gallery`
--

CREATE TABLE `gallery` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `caption` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gallery`
--

INSERT INTO `gallery` (`id`, `filename`, `caption`, `created_at`) VALUES
(6, 'gallery_1761359988_0.jpg', 'lomba', '2025-10-25 02:39:48'),
(7, 'gallery_1761360075_0.jpg', '', '2025-10-25 02:41:15'),
(8, 'gallery_1761360082_0.jpg', '', '2025-10-25 02:41:22'),
(9, 'gallery_1761360094_0.jpg', '', '2025-10-25 02:41:34'),
(10, 'gallery_1761360103_0.jpg', '', '2025-10-25 02:41:43'),
(11, 'gallery_1761360109_0.jpg', '', '2025-10-25 02:41:49'),
(12, 'gallery_1761360115_0.jpg', '', '2025-10-25 02:41:55'),
(13, 'gallery_1761360120_0.jpg', '', '2025-10-25 02:42:00'),
(14, 'gallery_1761360328_0.jpeg', '', '2025-10-25 02:45:28'),
(15, 'gallery_1761360370_0.jpeg', '', '2025-10-25 02:46:10'),
(16, 'gallery_1761360370_1.jpeg', '', '2025-10-25 02:46:10'),
(17, 'gallery_1761360370_2.jpeg', '', '2025-10-25 02:46:10'),
(18, 'gallery_1761360370_3.jpeg', '', '2025-10-25 02:46:10'),
(19, 'gallery_1761360370_4.jpeg', '', '2025-10-25 02:46:10'),
(20, 'gallery_1761360370_5.jpeg', '', '2025-10-25 02:46:10'),
(21, 'gallery_1761360370_6.jpeg', '', '2025-10-25 02:46:10'),
(22, 'gallery_1761360370_7.jpeg', '', '2025-10-25 02:46:10'),
(23, 'gallery_1761360370_8.jpeg', '', '2025-10-25 02:46:10'),
(24, 'gallery_1761360370_9.jpeg', '', '2025-10-25 02:46:10'),
(25, 'gallery_1761360370_10.jpeg', '', '2025-10-25 02:46:10'),
(26, 'gallery_1761360370_11.jpeg', '', '2025-10-25 02:46:10'),
(27, 'gallery_1761360370_12.jpeg', '', '2025-10-25 02:46:10'),
(28, 'gallery_1761360370_13.jpeg', '', '2025-10-25 02:46:10'),
(29, 'gallery_1761360370_14.jpeg', '', '2025-10-25 02:46:10'),
(30, 'gallery_1761360370_15.jpeg', '', '2025-10-25 02:46:10'),
(31, 'gallery_1761360370_16.jpeg', '', '2025-10-25 02:46:10');

-- --------------------------------------------------------

--
-- Table structure for table `kas`
--

CREATE TABLE `kas` (
  `id` int(11) NOT NULL,
  `jenis` varchar(20) DEFAULT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `deskripsi` varchar(255) DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `approved_at` datetime DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `umur` int(11) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `posisi` varchar(50) DEFAULT NULL,
  `wa` varchar(20) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT 'default-profile.jpg',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `user_id`, `status`, `approved_at`, `approved_by`, `nama`, `umur`, `tanggal_lahir`, `tempat_lahir`, `posisi`, `wa`, `gender`, `alamat`, `catatan`, `foto`, `created_at`) VALUES
(26, NULL, 'approved', '2025-10-31 09:27:16', 2, 'nova satria wahyu saputra', 20, '2004-11-28', 'PONOROGO', 'Libero', '082141186468', 'Putra', 'rungkut', NULL, '69041e841daa2.jpg', '2025-10-31 02:27:16');

-- --------------------------------------------------------

--
-- Table structure for table `trophies`
--

CREATE TABLE `trophies` (
  `id` int(11) NOT NULL,
  `judul` varchar(200) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `tanggal` varchar(50) DEFAULT NULL,
  `badge` varchar(50) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trophies`
--

INSERT INTO `trophies` (`id`, `judul`, `deskripsi`, `tanggal`, `badge`, `foto`, `created_at`) VALUES
(13, 'TOURNAMENT INTERNASIONAL JEPANG', 'semabgaf', 'april 2021', 'Kejuaraan', '68f9b51d56959.jpg', '2025-10-23 04:54:53'),
(14, 'juara 1 antar kampus', 'mantab', 'mei 2025', 'Turnamen', '69041e4fab617.png', '2025-10-31 02:26:23');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `wa` varchar(20) DEFAULT NULL,
  `role` enum('admin','member') DEFAULT 'member',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama`, `wa`, `role`, `created_at`) VALUES
(2, 'wahyunova', '$2y$10$nCKVCoATvs/tPW7Jm3IWg.N3OCsi5couZQOOJchYUYR9lG5T5ZalG', 'nova satria wahyu saputra', NULL, 'admin', '2025-10-23 04:05:54'),
(3, 'nov', '$2y$10$ff5TQYSpQrJCd31L55hLuuKZLqFRa7LC9XpeLIvuzka8x.JywOpu2', 'nono vava sasa', NULL, 'member', '2025-10-23 04:10:14'),
(4, 'nanang', '$2y$10$plYyAhotqFS55XauNZsNYOt7jykrPlelHluLhNAh75VlO1.fblOOW', 'nanang', NULL, 'member', '2025-10-23 04:26:12'),
(5, 'admin', '827ccb0eea8a706c4c34a16891f84e7b', 'Administrator', NULL, 'admin', '2025-10-23 04:41:34'),
(6, 'ryo', '$2y$10$hncvvMBwHLnnLbw6JAyE8.sy73XV/T1fsDhZU33Zrh61k.mc6xM0m', 'ryo maulana', NULL, 'member', '2025-10-23 07:30:34'),
(7, 'wahyu', '$2y$10$8mmJf4ornYE3J2.y6alaEOzXkGgAhFgPvkCF5CrQ8q7GtY2kTFjZ.', 'wahyu2', NULL, 'member', '2025-10-25 12:32:16'),
(8, 'wahyu2', '$2y$10$iiC/p73vhlG4jKbWHfXAqeGKN8lCZPJ5fAv5sGAxpcnTfuGnDkGoe', 'sayawahyu', NULL, 'member', '2025-10-26 13:23:09'),
(9, 'na', '$2y$10$xNYrdWhkDNdgSEdaGe95o.n2la.ALGJIM4yljxUrjkO/m415yh8Zu', 'nananana', NULL, 'member', '2025-10-27 08:05:30'),
(10, 'sese', '$2y$10$j6s2mDWwQiJki1fHILL5X.SHnRtQR/yN.3djm1u3OewomCWJcoXdW', 'sese', NULL, 'member', '2025-10-27 08:34:57');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kas`
--
ALTER TABLE `kas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `trophies`
--
ALTER TABLE `trophies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `kas`
--
ALTER TABLE `kas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `trophies`
--
ALTER TABLE `trophies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
