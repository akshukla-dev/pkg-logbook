<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Logbook
 *
 * @copyright   Copyright (C) 2020 Amit Kumar Shukla. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * System plugin for Joomla Web Links.
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgSystemLogbook extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Supported Extensions
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	private $supportedExtensions = array(
		'mod_stats',
		'mod_stats_admin',
	);

	/**
	 * Method to add statistics information to Administrator control panel.
	 *
	 * @param   string   $extension  The extension requesting information.
	 *
	 * @return  array containing statistical information.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onGetStats($extension)
	{
		if (!in_array($extension, $this->supportedExtensions))
		{
			return array();
		}

		if (!JComponentHelper::isEnabled('com_logbook'))
		{
			return array();
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('COUNT(id) AS count_logs')
			->from('#__logbook_logs')
			->where('state = 1');
		$logs = $db->setQuery($query)->loadResult();

		if (!$logs)
		{
			return array();
		}

		return array(array(
			'title' => JText::_('PLG_SYSTEM_LOGBOOK_STATISTICS'),
			'icon'  => 'out-2',
			'data'  => $logs
		));
	}
}
