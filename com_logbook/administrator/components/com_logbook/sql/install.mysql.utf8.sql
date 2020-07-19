--
-- Insert data into table `#__content_types` for UCM functions
--

INSERT INTO `#__content_types` (`type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `router`, `content_history_options`) VALUES
('Logbook Log', 'com_logbook.log', '{"special":{"dbtable":"#__logbook_logs","key":"id","type":"Log","prefix":"LogbookTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"state","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"null", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"null", "core_featured":"null", "core_metadata":"null", "core_language":"null", "core_images":"null", "core_urls":"null", "core_version":"null", "core_ordering":"null", "core_metakey":"null", "core_metadesc":"null", "core_catid":"null", "core_xreference":"null", "asset_id":"asset_id"}, "special":{}}', 'LogbookHelperRoute::getLogRoute', '{"formFile":"administrator\\/components\\/com_logbook\\/models\\/forms\\/log.xml", "hideFields":["asset_id","checked_out","checked_out_time"], "ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time", "version", "downloads"], "convertToInt":["closed"], "displayLookup":[{"sourceColumn":"wdid","targetTable":"#__logbook_watchdogs","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"created_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"} ]}'),

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
