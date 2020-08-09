<?php
/**
 * @copyright   Copyright (C) 2020 Amit Kumar Shukla, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JLoader::register('LogbookHelperRoute', JPATH_COMPONENT.'/helpers/route.php');
JLoader::register('LogbookHelperQuery', JPATH_COMPONENT.'/helpers/query.php');
JLoader::register('LogbookHelperAssociation', JPATH_COMPONENT.'/helpers/association.php');

$input = JFactory::getApplication()->input;
$user = JFactory::getUser();

if ($input->get('view') === 'logs' && $input->get('layout') === 'modal') {
    if (!$user->authorise('core.create', 'com_logbook')) {
        JFactory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'warning');

        return;
    }
}

$controller = JControllerLegacy::getInstance('Logbook');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
