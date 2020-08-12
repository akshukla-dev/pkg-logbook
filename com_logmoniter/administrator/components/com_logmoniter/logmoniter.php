<?php
/**
 * @package LMI/LogManager/LogBook/LogMoniter
 * @copyright Copyright (c)2020 Amit Kumar Shukla. All Rights Reserved.
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
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
