-- phpMyAdmin SQL Dump
-- version 4.9.11
-- https://www.phpmyadmin.net/
--
-- Host: db5017669720.hosting-data.io
-- Generation Time: May 2, 2025
-- Server version: 10.11.7-MariaDB-log
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

DROP TABLE IF EXISTS `reports`;

CREATE TABLE `reports` (
  `uid` int(4) UNSIGNED ZEROFILL NOT NULL,
  `authtok` varchar(64) DEFAULT NULL,
  `time` timestamp NULL DEFAULT current_timestamp(),
  `lat` double DEFAULT NULL,
  `long` double DEFAULT NULL,
  `etype` varchar(50) DEFAULT NULL,

  -- 추가된 필드들
  `type` ENUM('Medical-Urgent','Medical-General','Security') DEFAULT NULL,
  `priority` ENUM('High','Normal') DEFAULT NULL,


  `details1` text DEFAULT NULL,
  `details2` text DEFAULT NULL,
  `conditions` text DEFAULT NULL,
  `info` text DEFAULT NULL,


  `status` enum('Pending','Enroute','On Scene','Egress','Canceled') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

ALTER TABLE `reports`
  ADD PRIMARY KEY (`uid`);

ALTER TABLE `reports`
  MODIFY `uid` int(4) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT;

COMMIT;