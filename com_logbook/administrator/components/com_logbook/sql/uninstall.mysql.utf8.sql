DELETE FROM `#__content_types` WHERE `type_alias` IN ('com_logbook.log', 'com_logbook.category');

DROP TABLE IF EXISTS `#__logbook_logs`;
