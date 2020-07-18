--
-- Insert data into table `#__content_types` for UCM functions
--

INSERT INTO `#__content_types` (`type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `router`, `content_history_options`) VALUES
('Logbook Log', 'com_logbook.log', '{"special":{"dbtable":"#__logbook_logs","key":"id","type":"Log","prefix":"LogbookTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"state","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"null", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"null", "core_featured":"null", "core_metadata":"null", "core_language":"null", "core_images":"null", "core_urls":"null", "core_version":"null", "core_ordering":"null", "core_metakey":"null", "core_metadesc":"null", "core_catid":"null", "core_xreference":"null", "asset_id":"asset_id"}, "special":{}}', 'LogbookHelperRoute::getLogRoute', '{"formFile":"administrator\\/components\\/com_logbook\\/models\\/forms\\/log.xml", "hideFields":["asset_id","checked_out","checked_out_time"], "ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time", "version", "downloads"], "convertToInt":["closed"], "displayLookup":[{"sourceColumn":"wdid","targetTable":"#__logbook_watchdogs","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"created_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"} ]}'),
('Logbook Watchdogs', 'com_logbook.watchdog', '{"special":{"dbtable":"#__logbook_watchdogs","key":"id","type":"Watchdog","prefix":"LogbookTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"state","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":""", "core_hits":"hits","core_publish_up":"","core_publish_down":"","core_access":"access", "core_params":"", "core_featured":"null", "core_metadata":"", "core_language":"", "core_images":"null", "core_urls":"null", "core_version":"null", "core_ordering":"ordering", "core_metakey":"", "core_metadesc":"", "core_catid":"null", "core_xreference":"null", "asset_id":"asset_id"}, "special":{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}', 'LogbookHelperRoute::getWatchdogRoute', '{"formFile":"administrator\\/components\\/com_logbook\\/models\\/forms\\/watchdog.xml", "hideFields":["asset_id","checked_out","checked_out_time","version","lft","rgt","level","path","extension"], "ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "path"],"convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"isid","targetTable":"#__logbook_instructionsets","targetColumn":"id","displayColumn":"title"}, {"sourceColumn":"bpid","targetTable":"#__logbook_blueprints","targetColumn":"id","displayColumn":"title"}, {"sourceColumn":"wcid","targetTable":"#__logbook_workcenters","targetColumn":"id","displayColumn":"title"}, {"sourceColumn":"tiid","targetTable":"#__logbook_timeintervals","targetColumn":"id","displayColumn":"title"}, {"sourceColumn":"created_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"}]}');

--
-- Table structure for table `#__logbook_logs`
--
CREATE TABLE IF NOT EXISTS `#__logbook_logs`(
	`id`	INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `asset_id` INT(10) UNSIGNED NOT NULL DEFAULT 0 ,
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `alias` VARCHAR(400) NOT NULL DEFAULT '' ,
	`remarks` TINYTEXT NOT NULL DEFAULT '' ,
	`wdid` INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `file` VARCHAR(255) NOT NULL DEFAULT '',
  `file_name` VARCHAR(255) NOT NULL DEFAULT '',
  `file_type` VARCHAR(61) NOT NULL DEFAULT '',
  `file_size` VARCHAR(123) NOT NULL DEFAULT '',
  `file_path` VARCHAR(123) NOT NULL DEFAULT '',
  `file_icon` VARCHAR(61) NOT NULL DEFAULT '',
  `signatories` TINYTEXT NOT NULL DEFAULT '',
  `downloads` INT(10) UNSIGNED NOT NULL DEFAULT 0 ,
	`hits` INT(10) UNSIGNED NOT NULL DEFAULT 0 ,
  `access` TINYINT(3) NOT NULL DEFAULT 0 ,
	`state` TINYINT NOT NULL DEFAULT 0 ,
  `created_by` INT(10) UNSIGNED NOT NULL DEFAULT 0,
	`created_by_alias` VARCHAR(400) NOT NULL DEFAULT '',
  `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
	`checked_out` INT(10) UNSIGNED NOT NULL DEFAULT 0 ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  `modified_by` INT(10) UNSIGNED NOT NULL DEFAULT 0,
	`closed` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `closed_by` INT(10) UNSIGNED NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_access` (`access`),
	KEY `idx_wdid` (`wdid`),
  KEY `idx_createdby` (`created_by`),
	KEY `idx_alias` (`alias`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `#__logbook_watchdogs`
--

CREATE TABLE IF NOT EXISTS `#__logbook_watchdogs` (
  `id`	INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`asset_id` INT(10) UNSIGNED NOT NULL DEFAULT 0 ,
	`title` VARCHAR(255) NOT NULL DEFAULT '',
  `alias` VARCHAR(400) NOT NULL DEFAULT '',
	`isid` INT(10) UNSIGNED NOT NULL DEFAULT 0 ,
	`bpid` INT(10) UNSIGNED NOT NULL DEFAULT 0 ,
	`wcid` INT(10) UNSIGNED NOT NULL DEFAULT 0 ,
	`tiid` INT(10) UNSIGNED NOT NULL DEFAULT 0 ,
	`logging_window` TINYINT UNSIGNED NOT NULL DEFAULT 0 ,
	`first_log_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
	`last_log_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
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
	PRIMARY KEY (`id`),
  KEY `idx_isid` (`isid`),
  KEY `idx_bpid` (`bpid`),
  KEY `idx_wcid` (`wcid`),
  KEY `idx_tiid` (`tiid`),
  KEY `idx_access` (`access`),
  KEY `idx_createdby` (`created_by`),
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
