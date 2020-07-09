CREATE TABLE IF NOT EXISTS `#__logbook_logs`(
	`id`	INT(10) unsigned NOT NULL AUTO_INCREMENT,
	`log` VARCHAR(250) NOT NULL,
	`state` tinyint(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
