<?php
/**
 * @copyright   Copyright (C) 2020 Amit Kumar Shukla, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

// Set some global property
$document = JFactory::getDocument();
$document->addStyleDeclaration('.icon-logbook {background-image: url(../media/com_logbook/images/logbook-icon-48x48.png);}');

if (!JFactory::getUser()->authorise('core.manage', 'com_logbook')) {
    throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
}
// Get an instance of the controller prefixed by HelloWorld
$controller = JControllerLegacy::getInstance('Logbook');
// Perform the Request task
$controller->execute(JFactory::getApplication()->input->get('task'));
// Redirect if set by the controller
$controller->redirect();
