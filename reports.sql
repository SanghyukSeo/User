-- phpMyAdmin SQL Dump
-- version 4.9.11
-- https://www.phpmyadmin.net/
--
-- Host: db5017669720.hosting-data.io
-- Generation Time: Apr 22, 2025 at 09:19 PM
-- Server version: 10.11.7-MariaDB-log
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dbs14130702`
--

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `uid` int(4) UNSIGNED ZEROFILL NOT NULL,
  `authtok` varchar(64) DEFAULT NULL,
  `time` timestamp NULL DEFAULT current_timestamp(),
  `lat` double DEFAULT NULL,
  `long` double DEFAULT NULL,
  `etype` varchar(50) DEFAULT NULL,
  `awake` enum('Unknown','Yes','No') DEFAULT 'Unknown',
  `breathing` enum('Unknown','Yes','No') DEFAULT 'Unknown',
  `vomiting` enum('Unknown','None','Significant','Minor') DEFAULT 'Unknown',
  `bleeding` enum('None','Significant','Minor') DEFAULT 'None',
  `mood` enum('Unknown','Calm','Hysterical') DEFAULT 'Unknown',
  `substances` enum('Unknown','Drugs','Alcohol','Drugs & Alcohol') DEFAULT 'Unknown',
  `danger` enum('Unknown','No','Self','Others') DEFAULT 'Unknown',
  `scene` enum('Unknown','Safe','Unsafe') DEFAULT 'Unknown',
  `meds` text DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `conditions` text DEFAULT NULL,
  `oinfo` text DEFAULT NULL,
  `cname` varchar(100) DEFAULT NULL,
  `rname` varchar(100) DEFAULT NULL,
  `rphone` varchar(50) DEFAULT NULL,
  `status` enum('Pending','Enroute','On Scene','Egress','Canceled') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`uid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `uid` int(4) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
