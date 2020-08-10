<?php
/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Methods supporting a list of watchdog records.
 *
 * @since  1.6
 */
class LogmoniterModelWatchdogs extends JModelList
{
    /**
     * Constructor.
     *
     * @param array $config an optional associative array of configuration settings
     *
     * @since   1.6
     * @see     JControllerLegacy
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'wd.id',
                'title', 'wd.title',
                'alias', 'wd.alias',
                'checked_out', 'wd.checked_out',
                'checked_out_time', 'wd.checked_out_time',
                'catid', 'wd.catid', 'category_id',
                'c.title', 'category_title',
                'wcid', 'wd.wcid', 'wcenter_id',
                'wc.title', 'wcenter_title',
                'isid', 'wd.isid', 'inset_id',
                'inset.title', 'inset_title',
                'bpid', 'wd.bpid', 'bprint_id',
                'bp.title', 'bprint_title',
                'tiid', 'wd.tiid', 'tinterval_title',
                'ti.title', 'tinterval_title',
                'state', 'wd.state',
                'access', 'wd.access',
                'ag.title', 'access_level',
                'created', 'wd.created',
                'created_by', 'wd.created_by',
                'ordering', 'wd.ordering',
                'language', 'wd.language',
                'hits', 'wd.hits',
                'logs', 'wd.log_count',
                'latest_log_date', 'wd.latest_log_date',
                'next_due_date', 'wd.next_due_date',
                'publish_up', 'wd.publish_up',
                'publish_down', 'wd.publish_down',
                'level', 'c.level',
                'author_id',
                'tag',
            );

            if (JLanguageAssociations::isEnabled()) {
                $config['filter_fields'][] = 'association';
            }
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param string $ordering  an optional ordering field
     * @param string $direction an optional direction (asc|desc)
     *
     * @since   1.6
     */
    protected function populateState($ordering = 'wd.id', $direction = 'desc')
    {
        $app = JFactory::getApplication();

        $forcedLanguage = $app->input->get('forcedLanguage', '', 'cmd');

        // Adjust the context to support modal layouts.
        if ($layout = $app->input->get('layout')) {
            $this->context .= '.'.$layout;
        }

        // Adjust the context to support forced languages.
        if ($forcedLanguage) {
            $this->context .= '.'.$forcedLanguage;
        }

        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $access = $this->getUserStateFromRequest($this->context.'.filter.access', 'filter_access');
        $this->setState('filter.access', $access);

        $authorId = $app->getUserStateFromRequest($this->context.'.filter.author_id', 'filter_author_id');
        $this->setState('filter.author_id', $authorId);

        $published = $this->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        $categoryId = $this->getUserStateFromRequest($this->context.'.filter.category_id', 'filter_category_id');
        $this->setState('filter.category_id', $categoryId);

        $wcenterId = $this->getUserStateFromRequest($this->context.'.filter.wcenter_id', 'filter_wcenter_id');
        $this->setState('filter.wcenter_id', $wcenterId);

        $insetId = $this->getUserStateFromRequest($this->context.'.filter.inset_id', 'filter_inset_id');
        $this->setState('filter.inset_id', $insetId);

        $bprintId = $this->getUserStateFromRequest($this->context.'.filter.bprint_id', 'filter_bprint_id');
        $this->setState('filter.bprint_id', $bprintId);

        $tintervalId = $this->getUserStateFromRequest($this->context.'.filter.tinterval_id', 'filter_tinterval_id');
        $this->setState('filte.tinterval_id', $tintervalId);

        $level = $this->getUserStateFromRequest($this->context.'.filter.level', 'filter_level');
        $this->setState('filter.level', $level);

        $language = $this->getUserStateFromRequest($this->context.'.filter.language', 'filter_language', '');
        $this->setState('filter.language', $language);

        $tag = $this->getUserStateFromRequest($this->context.'.filter.tag', 'filter_tag', '');
        $this->setState('filter.tag', $tag);

        // Load the parameters.
        $params = JComponentHelper::getParams('com_logmoniter');
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
     * @since   1.6
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':'.$this->getState('filter.search');
        $id .= ':'.$this->getState('filter.access');
        $id .= ':'.$this->getState('filter.published');
        $id .= ':'.$this->getState('filter.category_id');
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
        $id .= ':'.serialize($this->getState('filter.author_id'));
        $id .= ':'.$this->getState('filter.author_id.include');
        $id .= ':'.serialize($this->getState('filter.author_alias'));
        $id .= ':'.$this->getState('filter.author_alias.include');
        $id .= ':'.$this->getState('filter.date_filtering');
        $id .= ':'.$this->getState('filter.date_field');
        $id .= ':'.$this->getState('filter.start_date_range');
        $id .= ':'.$this->getState('filter.end_date_range');
        $id .= ':'.$this->getState('filter.relative_date');
        $id .= ':'.$this->getState('filter.language');

        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return JDatabaseQuery
     *
     * @since   1.6
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $user = JFactory::getUser();

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'wd.id, wd.title, wd.alias, wd.wcid, wd.isid, '.
                'wd.bpid, wd.tiid, wd.lwid, '.
                'wd.latest_log_date, wd.next_due_date, '.
                'wd.checked_out, wd.checked_out_time, '.
                'wd.catid, wd.created, wd.created_by, wd.created_by_alias, '.
                'wd.modified, wd.publish_up, wd.publish_down, wd.modified_by, '.
                'wd.access, wd.hits, wd.log_count, wd.language, wd.state, wd.ordering'
            )
        );
        $query->from('#__logbook_watchdogs AS wd');

        // Join over the language
        $query->select('l.title AS language_title, l.image AS language_image')
            ->join('LEFT', $db->quoteName('#__languages').' AS l ON l.lang_code = wd.language');

        // Join over the users for the checked out user.
        $query->select('uc.name AS editor')
            ->join('LEFT', '#__users AS uc ON uc.id=wd.checked_out');

        // Join over the asset groups.
        $query->select('ag.title AS access_level')
            ->join('LEFT', '#__viewlevels AS ag ON ag.id = wd.access');

        // Join over the categories.
        $query->select('c.title AS category_title')
            ->join('LEFT', '#__categories AS c ON c.id = wd.catid');

        // Join over the work-centers.
        $query->select('wc.title AS wcenter_title')
            ->join('LEFT', '#__logbook_workcenters AS wc ON wc.id = wd.wcid');

        // Join over the instrcution sets.
        $query->select('inset.title AS inset_title')
            ->join('LEFT', '#__logbook_instructionsets AS inset ON inset.id = wd.isid');

        // Join over the Blueprints.
        $query->select('bp.title AS bprint_title')
            ->join('LEFT', '#__logbook_blueprints AS bp ON bp.id = wd.bpid');

        // Join over the Time Intervals.
        $query->select('ti.title AS tinterval_title')
            ->join('LEFT', '#__logbook_timeintervals AS ti ON ti.id = wd.tiid');

        // Join over the users for the author.
        $query->select('ua.name AS author_name')
            ->join('LEFT', '#__users AS ua ON ua.id = wd.created_by');

        // Join over the associations.
        if (JLanguageAssociations::isEnabled()) {
            $query->select('COUNT(asso2.id)>1 as association')
                ->join('LEFT', '#__associations AS asso ON asso.id = wd.id AND asso.context='.$db->quote('com_logmoniter.item'))
                ->join('LEFT', '#__associations AS asso2 ON asso2.key = asso.key')
                ->group($assogroup);
        }

        // Filter by access level.
        if ($access = $this->getState('filter.access')) {
            $query->where('wd.access = '.(int) $access);
        }

        // Filter by access level on categories.
        if (!$user->authorise('core.admin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('wd.access IN ('.$groups.')');
            $query->where('c.access IN ('.$groups.')');
        }

        // Filter by published state
        $published = $this->getState('filter.published');

        if (is_numeric($published)) {
            $query->where('wd.state = '.(int) $published);
        } elseif ($published === '') {
            $query->where('(wd.state = 0 OR wd.state = 1)');
        }

        // Filter by a single or group of categories.
        $baselevel = 1;
        $categoryId = $this->getState('filter.category_id');

        if (is_numeric($categoryId)) {
            $categoryTable = JTable::getInstance('Category', 'JTable');
            $categoryTable->load($categoryId);
            $rgt = $categoryTable->rgt;
            $lft = $categoryTable->lft;
            $baselevel = (int) $categoryTable->level;
            $query->where('c.lft >= '.(int) $lft)
                ->where('c.rgt <= '.(int) $rgt);
        } elseif (is_array($categoryId)) {
            $query->where('wd.catid IN ('.implode(',', ArrayHelper::toInteger($categoryId)).')');
        }

        // Filter by a single or group of work-centers
        $wcenterId = $this->getState('filter.wcenter_id');

        if (is_numeric($wcenterId)) {
            $type = $this->getState('filter.wcenter_id.include', true) ? '= ' : '<> ';
            $query->where('wd.id '.$type.(int) $wcenterId);
        } elseif (is_array($wcenterId)) {
            $wcenterId = ArrayHelper::toInteger($wcenterId);
            $wcenterId = implode(',', $wcenterId);
            $type = $this->getState('filter.wcenter_id.include', true) ? 'IN' : 'NOT IN';
            $query->where('wd.id '.$type.' ('.$wcenterId.')');
        }

        // Filter by a single or group of instruction sets
        $insetId = $this->getState('filter.inset_id');

        if (is_numeric($insetId)) {
            $type = $this->getState('filter.inset_id.include', true) ? '= ' : '<> ';
            $query->where('wd.id '.$type.(int) $insetId);
        } elseif (is_array($insetId)) {
            $insetId = ArrayHelper::toInteger($insetId);
            $insetId = implode(',', $insetId);
            $type = $this->getState('filter.inset_id.include', true) ? 'IN' : 'NOT IN';
            $query->where('wd.id '.$type.' ('.$insetId.')');
        }

        // Filter by a single or group of blueprints
        $bprintId = $this->getState('filter.bprint_id');

        if (is_numeric($bprintId)) {
            $type = $this->getState('filter.bprint_id.include', true) ? '= ' : '<> ';
            $query->where('wd.id '.$type.(int) $bprintId);
        } elseif (is_array($bprintId)) {
            $bprintId = ArrayHelper::toInteger($bprintId);
            $bprintId = implode(',', $bprintId);
            $type = $this->getState('filter.bprint_id.include', true) ? 'IN' : 'NOT IN';
            $query->where('wd.id '.$type.' ('.$bprintId.')');
        }

        // Filter by a single or group of time intervals
        $tintervalId = $this->getState('filter.tinterval_id');

        if (is_numeric($tintervalId)) {
            $type = $this->getState('filter.tinterval_id.include', true) ? '= ' : '<> ';
            $query->where('wd.id '.$type.(int) $tintervalId);
        } elseif (is_array($tintervalId)) {
            $tintervalId = ArrayHelper::toInteger($tintervalId);
            $tintervalId = implode(',', $tintervalId);
            $type = $this->getState('filter.tinterval_id.include', true) ? 'IN' : 'NOT IN';
            $query->where('wd.id '.$type.' ('.$tintervalId.')');
        }

        // Filter on the level.
        if ($level = $this->getState('filter.level')) {
            $query->where('c.level <= '.((int) $level + (int) $baselevel - 1));
        }

        // Filter by author
        $authorId = $this->getState('filter.author_id');

        if (is_numeric($authorId)) {
            $type = $this->getState('filter.author_id.include', true) ? '= ' : '<>';
            $query->where('wd.created_by '.$type.(int) $authorId);
        }

        // Filter by search in title.
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('wd.id = '.(int) substr($search, 3));
            } elseif (stripos($search, 'author:') === 0) {
                $search = $db->quote('%'.$db->escape(substr($search, 7), true).'%');
                $query->where('(ua.name LIKE '.$search.' OR ua.username LIKE '.$search.')');
            } else {
                $search = $db->quote('%'.str_replace(' ', '%', $db->escape(trim($search), true).'%'));
                $query->where('(wd.title LIKE '.$search.' OR wd.alias LIKE '.$search.')');
            }
        }

        // Filter on the language.
        if ($language = $this->getState('filter.language')) {
            $query->where('wd.language = '.$db->quote($language));
        }

        // Filter by a single tag.
        $tagId = $this->getState('filter.tag');

        if (is_numeric($tagId)) {
            $query->where($db->quoteName('tagmap.tag_id').' = '.(int) $tagId)
                ->join(
                    'LEFT',
                    $db->quoteName('#__contentitem_tag_map', 'tagmap')
                    .' ON '.$db->quoteName('tagmap.content_item_id').' = '.$db->quoteName('wd.id')
                    .' AND '.$db->quoteName('tagmap.type_alias').' = '.$db->quote('com_logmoniter.watchdog')
                );
        }

        // Add the list ordering clause.
        $orderCol = $this->state->get('list.ordering', 'wd.id');
        $orderDirn = $this->state->get('list.direction', 'DESC');

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
            ->join('INNER', '#__logbook_watchdogs AS c ON c.created_by = u.id')
            ->group('u.id, u.name')
            ->order('u.name');

        // Setup the query
        $db->setQuery($query);

        // Return the result
        return $db->loadObjectList();
    }
}
