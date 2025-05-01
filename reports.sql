-- --------------------------------------------------------
-- Table structure for table `reports` (modified to include `state`)
-- --------------------------------------------------------

DROP TABLE IF EXISTS `reports`;

CREATE TABLE `reports` (
  `uid` int(4) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `authtok` varchar(64) DEFAULT NULL,
  `time` timestamp NULL DEFAULT current_timestamp(),
  `lat` double DEFAULT NULL,
  `long` double DEFAULT NULL,
  `etype` varchar(50) DEFAULT NULL,
  `state` enum('conscious','unconscious','unknown') DEFAULT 'unknown',  -- ✅ 추가된 필드
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
  `status` enum('Pending','Enroute','On Scene','Egress','Canceled') DEFAULT 'Pending',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;