<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Logbook
 *
 * @copyright   Copyright (C) 2020 Amit Kumar Shukla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the logs functions only once
require_once __DIR__ . '/helper.php';

$list = ModLogbookHelper::getList($params);

if (!count($list))
{
	return;
}

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

require JModuleHelper::getLayoutPath('mod_logs', $params->get('layout', 'default'));
