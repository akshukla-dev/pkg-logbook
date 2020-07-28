<?php
/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
JHtml::_('behavior.tabstate');

if (!JFactory::getUser()->authorise('core.manage', 'com_logmoniter')) {
    throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
}

//JLoader::register('LogmoniterHelper', __DIR__.'/helpers/logmoniter.php');

$controller = JControllerLegacy::getInstance('Logmoniter');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
