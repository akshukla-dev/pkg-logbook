<?php
/**
 *
 * @copyright Copyright (c)
 * @license GNU General Public License version 3, or later
 *
 */


defined('_JEXEC') or die; // No direct access.

require_once JPATH_COMPONENT.'/helpers/route.php';
require_once JPATH_COMPONENT.'/helpers/query.php';

$controller = JControllerLegacy::getInstance('Logmoniter');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();


