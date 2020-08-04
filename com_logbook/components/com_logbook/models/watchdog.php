<?php
/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

JLoader::register('LogbookHelperAssociation', JPATH_SITE.'/components/com_logbook/helpers/association.php');

/**
 * This models supports retrieving lists of watchdogs.
 *
 * @since  1.6
 */
class LogbookModelWatchdog extends JModelList
{
    /**
     * Watchdog items data.
     *
     * @var array
     */
    protected $_item = null;

    protected $_logs = null;

    /**
     * The watchdog that applies.
     *
     * @var object
     */
    protected $_watchdog = null;

    /**
     * Constructor.
     *
     * @param array $config an optional associative array of configuration settings
     *
     * @see     JController
     * @since   1.6
     */
    public function __construct($config = array())
    {
        // Set the state column alias
        //$this->setColumnAlias('state', 'state');

        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'l.id',
                'title', 'l.title',
                'hits', 'l.hits',
                'downloads', 'l.downloads',
            );
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * This method should only be called once per instantiation and is designed
     * to be called on the first call to the getState() method unless the model
     * configuration flag to ignore the request is set.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param string $ordering  an optional ordering field
     * @param string $direction an optional direction (asc|desc)
     *
     * @since   12.2
     */
    protected function populateState($ordering = 'l.title', $direction = 'ASC')
    {
        $app = JFactory::getApplication();
        $params = JComponentHelper::getParams('com_logbook');

        $forcedLanguage = $app->input->get('forcedLanguage', '', 'cmd');

        // Adjust the context to support forced languages.
        if ($forcedLanguage) {
            $this->context .= '.'.$forcedLanguage;
        }

        // List state information
        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'), 'uint');
        $this->setState('list.limit', $limit);

        $limitstart = $app->input->get('limitstart', 0, 'uint');
        $this->setState('list.start', $limitstart);

        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        // List state information.
        parent::populateState($ordering, $direction);

        // Force a language
        if (!empty($forcedLanguage)) {
            $this->setState('filter.language', $forcedLanguage);
            $this->setState('filter.forcedLanguage', $forcedLanguage);
        }

        $id = $app->input->get('id', 0, 'int');
        $this->setState('logwatchdogs.id', $id);

        // Load the parameters.
        $this->setState('params', $params);
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param string $id a prefix for the store id
     *
     * @return string a store id
     *
     * @since   1.6
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':'.$this->getState('filter.search');

        return parent::getStoreId($id);
    }

    /**
     * Get the master query for retrieving a list of watchdogs subject to the model state.
     *
     * @return JDatabaseQuery
     *
     * @since   1.6
     */
    protected function getListQuery()
    {
        // Get the current user for authorisation checks
        $user = JFactory::getUser();
        $groups = implode(',', $user->getAuthorisedViewLevels());

        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select($this->getState('list.select', 'l.*'));
        $query->from('#__logbook_logs AS l');
        $query->where('l.access IN ('.$groups.')');

        // Filter by watchdog.
        if ($watchdogId = $this->getState('logwatchdogs.id')) {
            $query->where('l.wdid = '.(int) $watchdogId);

            // Filter by state watchdogs
            $wdpublished = $this->getState('filter.wd.state');

            if (is_numeric($wdpublished)) {
                $query->where('wd.state = '.(int) $wdpublished);
            }
        }

        // Join over the watchdogs.
        $query->select('wd.title AS watchdog_title, wd.isid, wd.bpid, wd.tiid')
            ->join('LEFT', '#__logbook_watchdogs AS wd ON wd.id = l.wdid');

        // Join over the instrcution sets.
        $query->select('inset.title AS inset_title')
            ->join('LEFT', '#__logbook_instructionsets AS inset ON inset.id = wd.isid');

        // Join over the Blueprints.
        $query->select('bp.title AS bprint_title')
            ->join('LEFT', '#__logbook_blueprints AS bp ON bp.id = wd.bpid');

        // Join over the Time Intervals.
        $query->select('ti.title AS tinterval_title')
            ->join('LEFT', '#__logbook_timeintervals AS ti ON ti.id = wd.tiid');

        // Join over the language
        $query->select('lg.title AS language_title, lg.image AS language_image')
            ->join('LEFT', $db->quoteName('#__languages').' AS lg ON lg.lang_code = l.language');

        // Join over the users for the checked out user.
        $query->select('uc.name AS editor')
            ->join('LEFT', '#__users AS uc ON uc.id=l.checked_out');

        // Join over the asset groups.
        $query->select('ag.title AS access_level')
            ->join('LEFT', '#__viewlevels AS ag ON ag.id = l.access');

        // Join over the categories.
        $query->select('c.title AS category_title')
            ->join('LEFT', '#__categories AS c ON c.id = l.catid');

        // Join over the users for the author.
        $query->select('ua.name AS author_name')
            ->join('LEFT', '#__users AS ua ON ua.id = l.created_by');

        // Join over the associations.
        if (JLanguageAssociations::isEnabled()) {
            $query->select('COUNT(asso2.id)>1 as association')
                ->join('LEFT', '#__associations AS asso ON asso.id = l.id AND asso.context='.$db->quote('com_logbook.item'))
                ->join('LEFT', '#__associations AS asso2 ON asso2.key = asso.key')
                ->group($assogroup);
        }
        // Filter: like / search
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            $like = $db->quote('%'.$search.'%');
            $query->where('l.title LIKE '.$like);
        }

        // Filter by state state
        $state = $this->getState('filter.state');

        if (is_numeric($state)) {
            $query->where('state = '.(int) $state);
        } elseif ($state === '') {
            $query->where('(state IN (0, 1))');
        }

        // Do not show trashed links on the front-end
        $query->where('l.state != -2');

        // Filter by start and end dates.
        $nullDate = $db->quote($db->getNullDate());
        $nowDate = $db->quote(JFactory::getDate()->toSql());

        if ((!$user->authorise('core.edit.state', 'com_logbook')) && (!$user->authorise('core.edit', 'com_logbook'))) {
            $query->where('(l.publish_up = '.$nullDate.' OR l.publish_up <= '.$nowDate.')')
                ->where('(l.publish_down = '.$nullDate.' OR l.publish_down >= '.$nowDate.')');
        }

        // Filter by Date Range or Relative Date
        $dateFiltering = $this->getState('filter.date_filtering', 'off');
        $dateField = $this->getState('filter.date_field', 'l.created');

        switch ($dateFiltering) {
            case 'range':
                $startDateRange = $db->quote($this->getState('filter.start_date_range', $nullDate));
                $endDateRange = $db->quote($this->getState('filter.end_date_range', $nullDate));
                $query->where(
                    '('.$dateField.' >= '.$startDateRange.' AND '.$dateField.
                        ' <= '.$endDateRange.')'
                );
                break;

            case 'relative':
                $relativeDate = (int) $this->getState('filter.relative_date', 0);
                $query->where(
                    $dateField.' >= DATE_SUB('.$nowDate.', INTERVAL '.
                        $relativeDate.' DAY)'
                );
                break;

            case 'off':
            default:
                break;
        }

        // Add the list ordering clause.
        $orderCol = $this->state->get('list.ordering', 'l.created');
        $orderDirn = $this->state->get('list.direction', 'DESC');
        $query->order($db->escape($orderCol).' '.$db->escape($orderDirn));

        return $query;
    }

    /*
     * Method to get a list of watchdogs.
     *
     * Overriden to inject convert the params field into a JParameter object.
     *
     * @return mixed an array of objects on success, false on failure
     *
     * @since   1.6
     */
    public function getItems()
    {// Invoke the parent getItems method to get the main list
        $items = parent::getItems();

        // Convert the params field into an object, saving original in _params
        foreach ($items as $item) {
            if (!isset($this->_params)) {
                $params = new Registry();
                $params->loadString($item->params);
                $item->params = $params;
            }

            // Get the tags
            $item->tags = new JHelperTags();
            $item->tags->getItemTags('com_logbook.log', $item->id);
        }

        return $items;
    }

    /*
     * Method to get the starting number of items for the data set.
     *
     * @return int the starting number of items available in the data set
     *
     * @since   12.2
     */
    public function getStart()
    {
        return $this->getState('list.start');
    }
}
