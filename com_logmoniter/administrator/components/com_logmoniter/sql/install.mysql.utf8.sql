--
-- Table structure for table `#__logbook_watchdogs`
--
DROP TABLE IF EXISTS `#__logbook_watchdogs`;
CREATE TABLE `#__logbook_watchdogs` (
    `id`	INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `asset_id` INT(10) UNSIGNED NOT NULL DEFAULT 0 ,
    `title` VARCHAR(255) NOT NULL DEFAULT '',
    `alias` VARCHAR(400) NOT NULL DEFAULT '',
		`catid` INT NOT NULL DEFAULT 0,
		`wcid` INT(10) UNSIGNED NOT NULL DEFAULT 0 ,
    `isid` INT(10) UNSIGNED NOT NULL DEFAULT 0 ,
    `bpid` INT(10) UNSIGNED NOT NULL DEFAULT 0 ,
    `tiid` INT(10) UNSIGNED NOT NULL DEFAULT 0 ,
		`lwid` INT(10) UNSIGNED NOT NULL DEFAULT 0 ,
    `log_path` TINYTEXT NOT NULL DEFAULT '',
    `log_count` INT UNSIGNED DEFAULT 0 ,
    `hits` INT(10) UNSIGNED NOT NULL DEFAULT 0 ,
    `latest_log_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
    `next_due_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
    `checked_out` INT UNSIGNED NOT NULL DEFAULT 0 ,
    `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
    `access` TINYINT NOT NULL DEFAULT 0 ,
    `state` TINYINT NOT NULL DEFAULT 0 ,
    `ordering` INT(11) NOT NULL DEFAULT 0 ,
    `created_by` INT(10) UNSIGNED NOT NULL DEFAULT 0 ,
    `created_by_alias` VARCHAR(400) NOT NULL DEFAULT '',
    `publish_up` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
    `publish_down` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
    `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
    `modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
    `modified_by` INT(10) UNSIGNED NOT NULL DEFAULT 0 ,
		`params` TEXT NOT NULL ,
		`metakey` TEXT NOT NULL ,
		`metadesc` TEXT NOT NULL ,
		`metadata` TEXT NOT NULL ,
		`language` CHAR(7) NOT NULL,
		`version` int(10) UNSIGNED NOT NULL DEFAULT 1,
    `note` varchar(255) NOT NULL DEFAULT '',
PRIMARY KEY (`id`),
  KEY `idx_checkout` (`checked_out`),
	KEY `idx_catid` (`catid`),
  KEY `idx_isid` (`isid`),
  KEY `idx_bpid` (`bpid`),
  KEY `idx_wcid` (`wcid`),
  KEY `idx_tiid` (`tiid`),
  KEY `idx_access` (`access`),
	KEY `idx_state` (`state`),
  KEY `idx_createdby` (`created_by`),
	KEY `idx_language` (`language`),
  KEY `idx_alias` (`alias`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `#__logbook_workcenters`
--

CREATE TABLE IF NOT EXISTS `#__logbook_workcenters` (
  `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(250) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `#__logbook_timeintervals`
--

CREATE TABLE IF NOT EXISTS `#__logbook_timeintervals` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `#__logbook_instructionsets`
--

CREATE TABLE IF NOT EXISTS `#__logbook_instructionsets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `#__logbook_blueprints`
--

CREATE TABLE IF NOT EXISTS `#__logbook_blueprints`(
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL DEFAULT '',
	`isid` INT(10) UNSIGNED NOT NULL DEFAULT 0 ,
	PRIMARY KEY (`id`),
	KEY `idx_isid` (`isid`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

