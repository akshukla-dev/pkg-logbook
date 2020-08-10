<?php
/**
 *
 * @copyright Copyright (c)
 * @license GNU General Public License version 3, or later
 *
 */


defined('_JEXEC') or die; // No direct access.

JLoader::register('LogmoniterHelperRoute', JPATH_SITE . '/components/com_logmoniter/helpers/route.php');
JLoader::register('LogmoniterHelperQuery', JPATH_SITE . '/components/com_logmoniter/helpers/query.php');
JLoader::register('LogmoniterHelperAssociation', JPATH_SITE . '/components/com_logmoniter/helpers/association.php');

$input = JFactory::getApplication()->input;
$user  = JFactory::getUser();

if ($input->get('view') === 'watchdogs' && $input->get('layout') === 'modal')
{
	if (!$user->authorise('core.create', 'com_logmoniter'))
	{
		JFactory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'warning');

		return;
	}
}

$controller = JControllerLegacy::getInstance('Logmoniter');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();


