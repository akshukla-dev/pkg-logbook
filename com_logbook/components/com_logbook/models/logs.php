<?php
/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

JLoader::register('LogbookHelperAssociation', JPATH_SITE.'/components/com_logbook/helpers/association.php');

/**
 * This models supports retrieving lists of watchdogs.
 *
 * @since  1.6
 */
class LogbookModelLogs extends JModelList
{
    /**
     * Watchdog items data.
     *
     * @var array
     */
    protected $_item = null;

    protected $_logs = null;

    /**
     * Model context string.
     *
     * @var string
     */
    protected $_context = 'com_logbook.logs';

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
                'alias', 'l.alias',
                'wdid', 'l.wdid', 'watchdog_title',
                'wcid', 'wd.wcid', 'wcenter_title',
                'isid', 'wd.isid', 'inset_title',
                'bpid', 'wd.bpid', 'bprint_title',
                'tiid', 'wd.tiid', 'tinterval_title',
                'checked_out', 'l.checked_out',
                'checked_out_time', 'l.checked_out_time',
                'state', 'l.state',
                'access', 'l.access', 'access_level',
                'created', 'l.created',
                'created_by', 'l.created_by',
                'modified', 'l.modified',
                'language', 'l.language',
                'author', 'l.author',
                'hits', 'l.hits',
                'downloads', 'l.downloads',
                'filter_tag',
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
    protected function populateState($ordering = 'l.created', $direction = 'DESC')
    {
        $app = JFactory::getApplication('site');

        $forcedLanguage = $app->input->get('forcedLanguage', '', 'cmd');

        // Adjust the context to support forced languages.
        if ($forcedLanguage) {
            $this->context .= '.'.$forcedLanguage;
        }

        // Load the parameters. Merge Global and Menu Item params into new object
        $params = $app->getParams();
        $menuParams = new Registry();

        if ($menu = $app->getMenu()->getActive()) {
            $menuParams->loadString($menu->params);
        }

        $mergedParams = clone $menuParams;
        $mergedParams->merge($params);

        $this->setState('params', $mergedParams);

        $user = JFactory::getUser();

        $asset = 'com_logbook';

        if ((!$user->authorise('core.edit.state', $asset)) && (!$user->authorise('core.edit', $asset))) {
            // Limit to published for people who can't edit or edit.state.
            $this->setState('filter.published', 1);
        } else {
            $this->setState('filter.published', array(0, 1));
        }

        // Process show_noauth parameter
        if (!$params->get('show_noauth')) {
            $this->setState('filter.access', true);
        } else {
            $this->setState('filter.access', false);
        }

        // Optional filter text
        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('list.search', $search);

        $watchdogId = $this->getUserStateFromRequest($this->context.'.filter.watchdog_id', 'filter_watchdog_id');
        $this->setState('filter.watchdog_id', $watchdogId);

        $wcenterId = $this->getUserStateFromRequest($this->context.'.filter.wcenter_id', 'filter_wcenter_id');
        $this->setState('filter.wcenter_id', $wcenterId);

        $wcenterId = $this->getUserStateFromRequest($this->context.'.filter.wcenter_id', 'filter_wcenter_id');
        $this->setState('filter.wcenter_id', $wcenterId);

        $insetId = $this->getUserStateFromRequest($this->context.'.filter.inset_id', 'filter_inset_id');
        $this->setState('filter.inset_id', $insetId);

        $bprintId = $this->getUserStateFromRequest($this->context.'.filter.bprint_id', 'filter_bprint_id');
        $this->setState('filter.bprint_id', $bprintId);

        $tintervalId = $this->getUserStateFromRequest($this->context.'.filter.tinterval_id', 'filter_tinterval_id');
        $this->setState('filter.tinterval_id', $tintervalId);

        // List state information.
        parent::populateState($ordering, $direction);

        // Force a language
        if (!empty($forcedLanguage)) {
            $this->setState('filter.language', $forcedLanguage);
            $this->setState('filter.forcedLanguage', $forcedLanguage);
        }
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
        $id .= ':'.serialize($this->getState('filter.watchdog_id'));
        $id .= ':'.$this->getState('filter.watchdog_id.include');
        $id .= ':'.serialize($this->getState('filter.wcenter_id'));
        $id .= ':'.$this->getState('filter.wcenter_id.include');
        $id .= ':'.serialize($this->getState('filter.inset_id'));
        $id .= ':'.$this->getState('filter.inset_id.include');
        $id .= ':'.serialize($this->getState('filter.bprint_id'));
        $id .= ':'.$this->getState('filter.brpint_id.include');
        $id .= ':'.serialize($this->getState('filter.tinterval_id'));
        $id .= ':'.$this->getState('filter.tinterval_id.include');

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

        // Join over the Work Centers.
        $query->select('wc.title AS wcenter_title')
            ->join('LEFT', '#__logbook_workcenters AS wc ON wc.id = wd.wcid');

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
        $query->select('ua.name AS author')
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
        $state = $this->getState('filter.published');

        if (is_numeric($state)) {
            $query->where('l.state = '.(int) $state);
        } elseif ($state === '') {
            $query->where('(l.state IN (0, 1, 2))');
        }

        // Do not show trashed links on the front-end
        $query->where('l.state != -2');

        // Filter by a single or group of watchdogs
        $watchdogId = $this->getState('filter.watchdog_id');

        if (is_numeric($watchdogId)) {
            $type = $this->getState('filter.watchdog_id.include', true) ? '= ' : '<>';
            $query->where('l.wdid '.$type.(int) $watchdogId);
        } elseif (is_array($watchdogId)) {
            $watchdogId = ArrayHelper::toInteger($watchdogId);
            $watchdogId = implode(',', $watchdogId);
            $type = $this->getState('filter.watchdog_id.include', true) ? 'IN' : 'NOT IN';
            $query->where('l.wdid '.$type.' ('.$watchdogId.')');
        }

        // Filter by a single or group of work-centers
        $wcenterId = $this->getState('filter.wcenter_id');

        if (is_numeric($wcenterId)) {
            $type = $this->getState('filter.wcenter_id.include', true) ? '= ' : '<>';
            $query->where('wd.wcid '.$type.(int) $wcenterId);
        } elseif (is_array($wcenterId)) {
            $wcenterId = ArrayHelper::toInteger($wcenterId);
            $wcenterId = implode(',', $wcenterId);
            $type = $this->getState('filter.wcenter_id.include', true) ? 'IN' : 'NOT IN';
            $query->where('wd.wcid '.$type.' ('.$wcenterId.')');
        }

        // Filter by a single or group of instruction sets
        $insetId = $this->getState('filter.inset_id');

        if (is_numeric($insetId)) {
            $type = $this->getState('filter.inset_id.include', true) ? '= ' : '<> ';
            $query->where('wd.isid '.$type.(int) $insetId);
        } elseif (is_array($insetId)) {
            $insetId = ArrayHelper::toInteger($insetId);
            $insetId = implode(',', $insetId);
            $type = $this->getState('filter.inset_id.include', true) ? 'IN' : 'NOT IN';
            $query->where('wd.isid '.$type.' ('.$insetId.')');
        }

        // Filter by a single or group of blueprints
        $bprintId = $this->getState('filter.bprint_id');

        if (is_numeric($bprintId)) {
            $type = $this->getState('filter.bprint_id.include', true) ? '= ' : '<> ';
            $query->where('wd.bpid '.$type.(int) $bprintId);
        } elseif (is_array($bprintId)) {
            $bprintId = ArrayHelper::toInteger($bprintId);
            $bprintId = implode(',', $bprintId);
            $type = $this->getState('filter.bprint_id.include', true) ? 'IN' : 'NOT IN';
            $query->where('wd.bpid '.$type.' ('.$bprintId.')');
        }

        // Filter by a single or group of time intervals
        $tintervalId = $this->getState('filter.tinterval_id');

        if (is_numeric($tintervalId)) {
            $type = $this->getState('filter.tinterval_id.include', true) ? '= ' : '<> ';
            $query->where('wd.tiid '.$type.(int) $tintervalId);
        } elseif (is_array($tintervalId)) {
            $tintervalId = ArrayHelper::toInteger($tintervalId);
            $tintervalId = implode(',', $tintervalId);
            $type = $this->getState('filter.tinterval_id.include', true) ? 'IN' : 'NOT IN';
            $query->where('wd.tiid '.$type.' ('.$tintervalId.')');
        }

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
     * Method to get a list of logs.
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
