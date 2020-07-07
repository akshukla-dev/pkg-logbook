<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Logbook
 *
 * @copyright   Copyright (C) 2020 Amit Kumar Shukla, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
JHtml::_('behavior.tabstate');

if (!JFactory::getUser()->authorise('core.manage', 'com_logbook'))
{
	throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
}

$controller	= JControllerLegacy::getInstance('Logbook');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
