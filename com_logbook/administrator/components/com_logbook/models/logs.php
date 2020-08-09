<?php
/**
 * @copyright Copyright (c)2020 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */
defined('_JEXEC') or die; //No direct access to this file.

/**
 * Methods supporting a list of log records.
 *
 * @since  1.6
 */
class LogbookModelLogs extends JModelList
{
    /**
     * Constructor.
     *
     * @param	array	an optional associative array of configuration settings
     *
     * @see		JController
     * @since	1.6
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'l.id',
                'file_name', 'l.file_name',
                'file_type', 'l.file_type',
                'file_size', 'l.file_size',
                'checked_out', 'l.checked_out',
                'checked_out_time', 'l.checked_out_time',
                'wdid', 'l.wdid',
                'signatories', 'l.signatories',
                'access', 'l.access', 'access_level',
                'created', 'l.created',
                'modified', 'l.modified',
                'closed', 'l.closed',
                'created_by', 'l.created_by',
                'created_by_alias', 'a.created_by_alias',
                'downloads', 'l.downloads',
                'inset_title',
                'bprint_title',
                'wcenter_title',
                'tinterval_title',
                'author_id',
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
     * @since   1.6
     */
    protected function populateState($ordering = 'l.id', $direction = 'desc')
    {
        // Initialise variables.
        $app = JFactory::getApplication();
        $session = JFactory::getSession();

        //Adjust the context to support modal layouts.
        if ($layout = JFactory::getApplication()->input->get('layout')) {
            $this->context .= '.'.$layout;
        }

        //Get the state values set by the user.
        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $access = $this->getUserStateFromRequest($this->context.'.filter.access', 'filter_access');
        $this->setState('filter.access', $access);

        $authorId = $app->getUserStateFromRequest($this->context.'.filter.author_id', 'filter_author_id');
        $this->setState('filter.author_id', $userId);

        $closed = $this->getUserStateFromRequest($this->context.'.filter.closed', 'filter_closed');
        $this->setState('filter.closed', $published);
        //Filter for the log attributes.
        $wdogId = $this->getUserStateFromRequest($this->context.'.filter.wdog_id', 'filter_wdog_id');
        $this->setState('filter.wdog_id', $wdogId);

        $insetId = $this->getUserStateFromRequest($this->context.'.filter.inset_id', 'filter_inset_id');
        $this->setState('filter.inset_id', $insetId);

        $bprintId = $this->getUserStateFromRequest($this->context.'.filter.bprint_id', 'filter_bprint_id');
        $this->setState('filter.bprint_id', $bprintId);

        $wcenterId = $this->getUserStateFromRequest($this->context.'.filter.wcenter_id', 'filter_wcenter_id');
        $this->setState('filter.wcenter_id', $wcenterId);

        $tintervalId = $this->getUserStateFromRequest($this->context.'.filter.tinterval_id', 'filter_tinterval_id');
        $this->setState('filter.tinterval_id', $tintervalId);

        // List state information.
        parent::populateState($ordering, $direction);
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
     * @since	1.6
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':'.$this->getState('filter.search');
        $id .= ':'.$this->getState('filter.access');
        $id .= ':'.$this->getState('filter.wdog_id');
        $id .= ':'.$this->getState('filter.inset_id');
        $id .= ':'.$this->getState('filter.bprint_id');
        $id .= ':'.$this->getState('filter.wcenter_id');
        $id .= ':'.$this->getState('filter.tinterval_id');

        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data.These datas are retrieved in.
     * the view with the getItems function.
     *
     * @return JDatabaseQuery
     *
     * @since   1.6
     */
    protected function getListQuery()
    {
        //Create a new JDatabaseQuery object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $user = JFactory::getUser();

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'l.id, l.wdid, l.file_name, l.file_type, l.file_size, l.file_icon, l.downloads'.
                ', l.signatories, l.access, l.created, l.modified, l.closed_by'.
                ', l.created_by, l.created_by_alias, l.modified_by'
            )
        );

        $query->from('#__logbook_logs AS l');

        //Join over the watchdogs
        $query->select('wd.isid, wd.bpid, wd.wcid, wd.tiid')
            ->join('LEFT', '`#__logbook_watchdogs` AS wd ON wd.id = l.wdid');
        //Join over other tables
        $query->select('inset.title AS inset_title')
            ->join('LEFT', '`#__logbook_instructionsets` AS inset ON inset.id = wd.isid');
        $query->select('bprint.title AS bprint_title')
            ->join('LEFT', '`#__logbook_blueprints` AS bprint ON bprint.id = wd.bpid');
        $query->select('wcenter.title AS wcenter_title')
            ->join('LEFT', '`#__logbook_workcenters` AS wcenter ON wcenter.id = wd.wcid');
        $query->select('tinterval.title AS tinterval_title')
            ->join('LEFT', '`#__logbook_timeintervals` AS tinterval ON tinterval.id = wd.tiid');

        // Join over the asset groups.
        $query->select('ag.title AS access_level');
        $query->join('LEFT', '#__viewlevels AS ag ON ag.id = l.access');

        // Join over the users for the checked out user.
        $query->select('uc.name AS editor');
        $query->join('LEFT', '#__users AS uc ON uc.id=l.checked_out');

        // Join over the users for the uploaded_by.
        $query->select('ua.name AS uploaded_by')->join('LEFT', '#__users AS ua ON ua.id = l.created_by');

        // Filter by access level.
        if ($access = $this->getState('filter.access')) {
            $query->where('l.access = '.(int) $access);
        }

        // Filter by instruction sets.
        if ($insetId = $this->getState('filter.inset_id')) {
            $query->where('wd.isid = '.(int) $insetId);
        }
        // Filter by blueprints.
        if ($bprintId = $this->getState('filter.bprint_id')) {
            $query->where('wd.bpid = '.(int) $bprintId);
        }
        // Filter by workcenter.
        if ($wcenterId = $this->getState('filter.wcenter_id')) {
            $query->where('wd.wcid = '.(int) $wcenterId);
        }
        // Filter by frequency.
        if ($tinterval = $this->getState('filter.tinterval')) {
            $query->where('wd.tiid = '.(int) $tinterval);
        }
        // Filter by search.
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('l.id = '.(int) substr($search, 3));
            } elseif (stripos($search, 'Uploaded_by:') === 0) {
                $search = $db->quote('%'.$db->escape(substr($search, 7), true).'%');
                $query->where('(ua.name LIKE '.$search.' OR ua.username LIKE '.$search.')');
            } else {
            }
        }

        //Filter by user.
        $userId = $this->getState('filter.author_id');
        if (is_numeric($userId)) {
            $type = $this->getState('filter.author_id.include', true) ? '= ' : '<>';
            $query->where('l.created_by'.$type.(int) $userId);
        }

        //Add the list to the sort.
        $orderCol = $this->state->get('list.ordering', 'l.id');
        $orderDirn = $this->state->get('list.direction', 'DESC'); //asc or desc

        $query->order($db->escape($orderCol).' '.$db->escape($orderDirn));

        return $query;
    }

    /**
     * Build a list of authors.
     *
     * @return stdClass
     *
     * @since   1.6
     */
    public function getAuthors()
    {
        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Construct the query
        $query->select('u.id AS value, u.name AS text')
            ->from('#__users AS u')
            ->join('INNER', '#__logbook_logs AS c ON c.created_by = u.id')
            ->group('u.id, u.name')
            ->order('u.name');

        // Setup the query
        $db->setQuery($query);

        // Return the result
        return $db->loadObjectList();
    }

    /**
     * Method to get a list of logs.
     * Overridden to add a check for access levels.
     *
     * @return mixed an array of data items on success, false on failure
     *
     * @since   1.6.1
     */
    public function getItems()
    {
        $items = parent::getItems();

        if (JFactory::getApplication()->isClient('site')) {
            $groups = JFactory::getUser()->getAuthorisedViewLevels();

            foreach (array_keys($items) as $x) {
                // Check the access level. Remove logs the user shouldn't see
                if (!in_array($items[$x]->access, $groups)) {
                    unset($items[$x]);
                }
            }
        }

        return $items;
    }
}
