<?php
/**
 *
 * @copyright Copyright (c)2020 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 *
 */


defined('_JEXEC') or die; // No direct access.

//Check against the user permissions.
if(!JFactory::getUser()->authorise('core.manage', 'com_logmoniter')) {
  JFactory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
  return false;
}

$controller = JControllerLegacy::getInstance('Logmoniter');

//Execute the requested task (set in the url).
//If no task is set then the "display' task will be executed.
$controller->execute(JFactory::getApplication()->input->get('task'));

$controller->redirect();



