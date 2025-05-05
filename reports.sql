DROP TABLE IF EXISTS `reports`;

CREATE TABLE `reports` (
  `uid` INT(4) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `authtok` VARCHAR(64) DEFAULT NULL,
  `time` TIMESTAMP NULL DEFAULT current_timestamp(),
  
  -- 위치 정보
  `lat` DOUBLE DEFAULT NULL,
  `long` DOUBLE DEFAULT NULL,

  -- 응급 상황
  `etype` VARCHAR(50) DEFAULT NULL,
  `type` ENUM('Medical-Urgent','Medical-General','Security') DEFAULT NULL,
  `priority` ENUM('High','Normal') DEFAULT NULL,

  -- 제보자 정보
  `rname` VARCHAR(100) DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,

  -- 추가 정보
  `info` TEXT DEFAULT NULL,
  `details1` TEXT DEFAULT NULL,
  `details2` TEXT DEFAULT NULL,
  `conditions` TEXT DEFAULT NULL,

  -- 상태 정보
  `status` ENUM('Pending','Enroute','On Scene','Egress','Canceled','Dispatched','Completed') DEFAULT 'Pending',

  -- 생성 시간
  `created_at` DATETIME DEFAULT current_timestamp(),

  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;