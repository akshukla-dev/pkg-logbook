<?php
/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
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
                'title', 'l.title',
                'alias', 'l.alias',
                'file_type', 'l.file_type',
                'checked_out', 'l.checked_out',
                'checked_out_time', 'l.checked_out_time',
                'catid', 'l.catid', 'category_id',
                'c.title', 'category_title',
                'wdid', 'l.wdid', 'watchdog_id',
                'wd.title', 'watchdog_title',
                'signatories', 'l.signatories',
                'access', 'l.access', 'access_level',
                'state', 'l.state', 'published',
                'ordering', 'a.ordering',
                'language', 'l.language',
                'lg.title', 'language_title',
                'created', 'l.created',
                'modified', 'l.modified',
                'created_by', 'l.created_by',
                'created_by_alias', 'a.created_by_alias',
                'hits', 'l.hits',
                'downloads', 'l.downloads',
                'publish_up', 'l.publish_up',
                'publish_down', 'l.publish_down',
                'inset_title',
                'bprint_title',
                'wcenter_title',
                'tinterval_title',
                'tag',
                'level', 'c.level',
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

        $forcedLanguage = $app->input->get('forcedLanguage', '', 'cmd');

        //Adjust the context to support modal layouts.
        if ($layout = JFactory::getApplication()->input->get('layout')) {
            $this->context .= '.'.$layout;
        }

        // Adjust the context to support forced languages.
        if ($forcedLanguage) {
            $this->context .= '.'.$forcedLanguage;
        }

        //Get the state values set by the user.
        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search', '', 'string');
        $this->setState('filter.search', $search);

        $access = $this->getUserStateFromRequest($this->context.'.filter.access', 'filter_access', '', 'cmd');
        $this->setState('filter.access', $access);

        $this->setState('filter.language', $this->getUserStateFromRequest($this->context.'.filter.language', 'filter_language', '', 'string'));
        $this->setState('filter.tag', $this->getUserStateFromRequest($this->context.'.filter.tag', 'filter_tag', '', 'string'));
        $this->setState('filter.level', $this->getUserStateFromRequest($this->context.'.filter.level', 'filter_level', '', 'cmd'));

        $published = $this->getUserStateFromRequest($this->context.'filter.published', 'filter_published', '', 'string');
        $this->setState('filter.published', $published);

        $watchdogId = $this->getUserStateFromRequest($this->context.'.filter.watchdog_id', 'filter_watchdog_id', '', 'cmd');
        $this->setState('filter.watchdog_id', $watchdogId);

        $categoryID = $this->getUserStateFromRequest($this->context.'.filter.category_id', 'filter_category_id', '', 'cmd');
        $this->setState('filter.category_id', $categoryId);

        //TODO: Filter for the log detailed attributes.
        $insetId = $this->getUserStateFromRequest($this->context.'.filter.inset_id', 'filter_inset_id');
        $this->setState('filter.inset_id', $insetId);

        $bprintId = $this->getUserStateFromRequest($this->context.'.filter.bprint_id', 'filter_bprint_id');
        $this->setState('filter.bprint_id', $bprintId);

        $wcenterId = $this->getUserStateFromRequest($this->context.'.filter.wcenter_id', 'filter_wcenter_id');
        $this->setState('filter.wcenter_id', $wcenterId);

        $tintervalId = $this->getUserStateFromRequest($this->context.'.filter.tinterval_id', 'filter_tinterval_id');
        $this->setState('filter.tinterval_id', $tintervalId);

        // Load the parameters.
        $params = JComponentHelper::getParams('com_logbook');
        $this->setState('params', $params);

        // Force a language.
        if (!empty($forcedLanguage)) {
            $this->setState('filter.language', $forcedLanguage);
        }

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
        $id .= ':'.$this->getState('filter.published');
        $id .= ':'.$this->getState('filter.category_id');
        $id .= ':'.$this->getState('filter.language');
        $id .= ':'.$this->getState('filter.tag');
        $id .= ':'.$this->getState('filter.level');
        $id .= ':'.$this->getState('filter.watchdog_id');
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
                'l.id, l.title, l.alias, l.catid, l.wdid, l.file_type, l.downloads, l.hits'.
                ', l.access, l.checked_out, l.checked_out_time, l.created, l.modified, l.language'.
                ', l.state, l.publish_up, l.publish_down, l.ordering, l.created_by, l.created_by_alias, l.modified_by'
            )
        );

        $query->from('#__logbook_logs AS l');

        // Join over the language
        $query->select($db->quoteName('lg.title', 'language_title'))
            ->select($db->quoteName('lg.image', 'language_image'))
            ->join('LEFT', $db->quoteName('#__languages', 'lg').' ON '.$db->qn('lg.lang_code').' = '.$db->qn('l.language'));

        //Join over the watchdogs
        $query->select('wd.title AS watchdog_title, wd.isid, wd.bpid, wd.wcid, wd.tiid')
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

        // Join over the users for the checked out user.
        $query->select($db->quoteName('uc.name', 'editor'))
            ->join('LEFT', $db->quoteName('#__users', 'uc').' ON '.$db->qn('uc.id').' = '.$db->qn('l.checked_out'));

        // Join over the asset groups.
        $query->select('ag.title AS access_level');
        $query->join('LEFT', '#__viewlevels AS ag ON ag.id = l.access');

        // Join over the categories.
        $query->select('c.title AS category_title')
            ->join('LEFT', $db->quoteName('#__categories', 'c').' ON '.$db->qn('c.id').' = '.$db->qn('l.catid'));

        // Join over the associations.
        $assoc = JLanguageAssociations::isEnabled();

        if ($assoc) {
            $query->select('COUNT(asso2.id)>1 AS association')
                ->join('LEFT', $db->quoteName('#__associations', 'asso').' ON asso.id = l.id AND asso.context = '.$db->quote('com_logbook.item'))
                ->join('LEFT', $db->quoteName('#__associations', 'asso2').' ON asso2.key = asso.key')
                ->group('l.id, lg.title, lg.image, uc.name, ag.title, c.title');
        }
        // Filter by access level.
        if ($access = $this->getState('filter.access')) {
            $query->where('l.access = '.(int) $access);
        }

        // Implement View Level Access
        if (!$user->authorise('core.admin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where($db->quoteName('l.access').' IN ('.$groups.')');
        }

        // Filter by published state
        $published = $this->getState('filter.published');

        if (is_numeric($published)) {
            $query->where($db->quoteName('l.state').' = '.(int) $published);
        } elseif ($published === '') {
            $query->where('('.$db->quoteName('l.state').' IN (0, 1))');
        }

        // Filter by category.
        $categoryId = $this->getState('filter.category_id');

        if (is_numeric($categoryId)) {
            $query->where($db->quoteName('l.catid').' = '.(int) $categoryId);
        }

        // Filter by watchdog.
        $watchdogId = $this->getState('filter.watchdog_id');

        if (is_numeric($watchdogId)) {
            $query->where($db->quoteName('l.wdid').' = '.(int) $watchdogId);
        }
        /*
        // TODO: Filter by instruction sets.
        if ($insetId = $this->getState('filter.inset_id')) {
            $query->where('wd.isid = '.(int) $insetId);
        }
        // TODO: Filter by blueprints.
        if ($bprintId = $this->getState('filter.bprint_id')) {
            $query->where('wd.bpid = '.(int) $bprintId);
        }
        // TODO:  Filter by workcenter.
        if ($wcenterId = $this->getState('filter.wcenter_id')) {
            $query->where('wd.wcid = '.(int) $wcenterId);
        }
        //TODO:  Filter by frequency.
        if ($tinterval = $this->getState('filter.tinterval')) {
            $query->where('wd.tiid = '.(int) $tinterval);
        }
        */

        // Filter on the level.
        if ($level = $this->getState('filter.level')) {
            $query->where($db->quoteName('c.level').' <= '.(int) $level);
        }
        // Filter by search in title
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where($db->quoteName('l.id').' = '.(int) substr($search, 3));
            } else {
                $search = $db->quote('%'.str_replace(' ', '%', $db->escape(trim($search), true).'%'));
                $query->where('('.$db->quoteName('l.title').' LIKE '.$search.' OR '.$db->quoteName('l.alias').' LIKE '.$search.')');
            }
        }

        // Filter on the language.
        if ($language = $this->getState('filter.language')) {
            $query->where($db->quoteName('a.language').' = '.$db->quote($language));
        }

        $tagId = $this->getState('filter.tag');

        // Filter by a single tag.
        if (is_numeric($tagId)) {
            $query->where($db->quoteName('tagmap.tag_id').' = '.(int) $tagId)
                ->join(
                    'LEFT', $db->quoteName('#__contentitem_tag_map', 'tagmap')
                    .' ON '.$db->quoteName('tagmap.content_item_id').' = '.$db->quoteName('l.id')
                    .' AND '.$db->quoteName('tagmap.type_alias').' = '.$db->quote('com_logbook.log')
                );
        }

        //Add the list to the sort.
        $orderCol = $this->state->get('list.ordering', 'l.id');
        $orderDirn = $this->state->get('list.direction', 'DESC'); //asc or desc

        if ($orderCol == 'l.ordering' || $orderCol == 'category_title') {
            $orderCol = 'c.title '.$orderDirn.', l.ordering';
        }
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
