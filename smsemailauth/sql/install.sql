CREATE TABLE IF NOT EXISTS `PREFIX_smsauth_codes` (
  `id_code` int(11) NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) NOT NULL,
  `type` varchar(10) NOT NULL,
  `code` varchar(10) NOT NULL,
  `attempts` int(11) DEFAULT 0,
  `date_add` datetime NOT NULL,
  `date_expire` datetime NOT NULL,
  PRIMARY KEY (`id_code`),
  KEY `identifier` (`identifier`),
  KEY `date_expire` (`date_expire`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `PREFIX_smsauth_providers` (
  `id_provider` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `type` varchar(10) NOT NULL,
  `settings` text,
  `active` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id_provider`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `PREFIX_smsauth_logs` (
  `id_log` int(11) NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) NOT NULL,
  `type` varchar(10) NOT NULL,
  `action` varchar(50) NOT NULL,
  `status` varchar(20) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text,
  `date_add` datetime NOT NULL,
  PRIMARY KEY (`id_log`),
  KEY `identifier` (`identifier`),
  KEY `date_add` (`date_add`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;