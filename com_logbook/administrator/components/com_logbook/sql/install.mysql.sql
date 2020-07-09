--
-- Insert data into table `#__content_types` for UCM functions
--

--INSERT INTO `#__content_types` (`type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `router`, `content_history_options`) VALUES
--('log', 'com_logbook.log', '{"special":{"dbtable":"#__logbook_log","key":"id","type":"Log","prefix":"LogbookTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"state","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"description", "core_hits":"hits","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"params", "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"images", "core_urls":"url", "core_version":"version", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "core_xreference":"xreference", "asset_id":"null"}, "special":{}}', 'LogbookHelperRoute::getLogRoute', '{"formFile":"administrator\\/components\\/com_logbook\\/models\\/forms\\/log.xml", "hideFields":["asset_id","checked_out","checked_out_time","version","featured","images"], "ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time", "version", "hits"], "convertToInt":["publish_up", "publish_down", "featured", "ordering"], "displayLookup":[{"sourceColumn":"catid","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"created_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"} ]}'),
--('Logbook Category', 'com_category.category', '{"special":{"dbtable":"#__categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special":{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}', 'LogbookHelperRoute::getCategoryRoute', '{"formFile":"administrator\\/components\\/com_categories\\/models\\/forms\\/category.xml", "hideFields":["asset_id","checked_out","checked_out_time","version","lft","rgt","level","path","extension"], "ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"],"convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"parent_id","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"}]}');

--
-- Table structure for table `#__logbook_logs`
--
CREATE TABLE IF NOT EXISTS `#__logbook_logs`(
	`id`	INT(10) unsigned NOT NULL AUTO_INCREMENT,
	`log` VARCHAR(250) NOT NULL,
	`state` tinyint(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `#__logbook_locations`
--

CREATE TABLE IF NOT EXISTS `#__logbook_locations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  --`alias` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `state` tinyint(1) NOT NULL DEFAULT 0,
  --`checked_out` int(11) NOT NULL DEFAULT 0,
  --`checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  --`access` int(11) NOT NULL DEFAULT 1,
  --`params` text NOT NULL,
  --`language` char(7) NOT NULL DEFAULT '',
  --`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  --`created_by` int(10) unsigned NOT NULL DEFAULT 0,
  --`created_by_alias` varchar(255) NOT NULL DEFAULT '',
  --`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  --`modified_by` int(10) unsigned NOT NULL DEFAULT 0,
  --`metakey` text NOT NULL,
  --`metadesc` text NOT NULL,
  --`metadata` text NOT NULL,
  --`xreference` varchar(50) NOT NULL COMMENT 'A reference to enable linkages to external data sets.',
  --`publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  --`publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  --`version` int(10) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_state` (`state`),
  KEY `idx_createdby` (`created_by`),
  KEY `idx_language` (`language`),
  KEY `idx_xreference` (`xreference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
