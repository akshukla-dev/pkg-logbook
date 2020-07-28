<?php
/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

JLoader::register('LogmoniterHelperAssociation', JPATH_SITE.'/components/com_logmoniter/helpers/association.php');

/**
 * This models supports retrieving lists of watchdogs.
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
     * @see     JController
     * @since   1.6
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
                'catid', 'wd.catid', 'category_title',
                'wcid', 'wd.wcid', 'wcenter_title',
                'isid', 'wd.isid', 'inset_title',
                'bpid', 'wd.bpid', 'bprint_title',
                'tiid', 'wd.tiid', 'tinterval_title',
                'state', 'wd.state',
                'access', 'wd.access', 'access_level',
                'created', 'wd.created',
                'created_by', 'wd.created_by',
                'ordering', 'wd.ordering',
                'language', 'wd.language',
                'hits', 'wd.hits',
                'logs', 'wd.log_count',
                'latest_log_date', 'wd.latest_log_date',
                'next-due_date', 'wd.next_due_date',
                'publish_up', 'wd.publish_up',
                'publish_down', 'wd.publish_down',
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
    protected function populateState($ordering = 'ordering', $direction = 'ASC')
    {
        $app = JFactory::getApplication();

        // List state information
        $value = $app->input->get('limit', $app->get('list_limit', 0), 'uint');
        $this->setState('list.limit', $value);

        $value = $app->input->get('limitstart', 0, 'uint');
        $this->setState('list.start', $value);

        $value = $app->input->get('filter_tag', 0, 'uint');
        $this->setState('filter.tag', $value);

        $orderCol = $app->input->get('filter_order', 'wd.ordering');

        if (!in_array($orderCol, $this->filter_fields)) {
            $orderCol = 'wd.ordering';
        }

        $this->setState('list.ordering', $orderCol);

        $listOrder = $app->input->get('filter_order_Dir', 'ASC');

        if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', ''))) {
            $listOrder = 'ASC';
        }

        $this->setState('list.direction', $listOrder);

        $params = $app->getParams();
        $this->setState('params', $params);
        $user = JFactory::getUser();

        if ((!$user->authorise('core.edit.state', 'com_logmoniter')) && (!$user->authorise('core.edit', 'com_logmoniter'))) {
            // Filter on published for those who do not have edit or edit.state rights.
            $this->setState('filter.published', 1);
        }

        $this->setState('filter.language', JLanguageMultilang::isEnabled());

        // Process show_noauth parameter
        if (!$params->get('show_noauth')) {
            $this->setState('filter.access', true);
        } else {
            $this->setState('filter.access', false);
        }

        $this->setState('layout', $app->input->getString('layout'));
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
        $id .= ':'.serialize($this->getState('filter.published'));
        $id .= ':'.$this->getState('filter.access');
        $id .= ':'.serialize($this->getState('filter.watchdog_id'));
        $id .= ':'.$this->getState('filter.watchdog_id.include');
        $id .= ':'.serialize($this->getState('filter.category_id'));
        $id .= ':'.$this->getState('filter.category_id.include');
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

        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'wd.id, wd.title, wd.alias, wd.wcid, wd.isid, '.
                    'wd.bpid, wd.tiid, wd.tiid, '.
                    'wd.latest_log_date, wd.next_due_date, '.
                    'wd.checked_out, wd.checked_out_time, '.
                    'wd.catid, wd.created, wd.created_by, wd.created_by_alias, '.
                    // Published/archived watchdog in archive category is treats as archive watchdog
                    // If category is not published then force 0
                    'CASE WHEN c.published = 2 AND wd.state > 0 THEN 2 WHEN c.published != 1 THEN 0 ELSE wd.state END as state,'.
                    // Use created if modified is 0
                    'CASE WHEN wd.modified = '.$db->quote($db->getNullDate()).' THEN wd.created ELSE wd.modified END as modified, '.
                    'wd.modified_by, uam.name as modified_by_name,'.
                    // Use created if publish_up is 0
                    'CASE WHEN wd.publish_up = '.$db->quote($db->getNullDate()).' THEN wd.created ELSE wd.publish_up END as publish_up,'.
                    'wd.publish_down, wd.attribs, wd.metadata, wd.metakey, wd.metadesc, wd.access, '.
                    'wd.hits, wd.log_count, wd.language'
            )
        );

        $query->from('#__logbook_watchdogs AS wd');

        $params = $this->getState('params');
        $orderby_sec = $params->get('orderby_sec');

        // Join over the categories.
        $query->select('c.title AS category_title, c.path AS category_route, c.access AS category_access, c.alias AS category_alias')
            ->select('c.published, c.published AS parents_published')
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

        // Join over the users for the author and modified_by names.
        $query->select("CASE WHEN wd.created_by_alias > ' ' THEN wd.created_by_alias ELSE ua.name END AS author")
            ->select('ua.email AS author_email')
            ->join('LEFT', '#__users AS ua ON ua.id = wd.created_by')
            ->join('LEFT', '#__users AS uam ON uam.id = wd.modified_by');

        // Join over the categories to get parent category titles
        $query->select('parent.title as parent_title, parent.id as parent_id, parent.path as parent_route, parent.alias as parent_alias')
            ->join('LEFT', '#__categories as parent ON parent.id = c.parent_id');

        // Filter by access level.
        if ($access = $this->getState('filter.access')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('wd.access IN ('.$groups.')')
                ->where('c.access IN ('.$groups.')');
        }

        // Filter by published state
        $published = $this->getState('filter.published');

        if (is_numeric($published) && $published == 2) {
            // If category is archived then watchdog has to be published or archived.
            // If categogy is published then watchdog has to be archived.
            $query->where('(c.published = 2 AND wd.state > 0) OR (c.published = 1 AND wd.state = 2)');
        } elseif (is_numeric($published)) {
            // Category has to be published
            $query->where('c.published = 1 AND wd.state = '.(int) $published);
        } elseif (is_array($published)) {
            $published = ArrayHelper::toInteger($published);
            $published = implode(',', $published);

            // Category has to be published
            $query->where('c.published = 1 AND wd.state IN ('.$published.')');
        }

        // Filter by a single or group of watchdogs.
        $watchdogId = $this->getState('filter.watchdog_id');

        if (is_numeric($watchdogId)) {
            $type = $this->getState('filter.watchdog_id.include', true) ? '= ' : '<> ';
            $query->where('wd.id '.$type.(int) $watchdogId);
        } elseif (is_array($watchdogId)) {
            $watchdogId = ArrayHelper::toInteger($watchdogId);
            $watchdogId = implode(',', $watchdogId);
            $type = $this->getState('filter.watchdog_id.include', true) ? 'IN' : 'NOT IN';
            $query->where('wd.id '.$type.' ('.$watchdogId.')');
        }

        // Filter by a single or group of categories
        $categoryId = $this->getState('filter.category_id');

        if (is_numeric($categoryId)) {
            $type = $this->getState('filter.category_id.include', true) ? '= ' : '<> ';

            // Add subcategory check
            $includeSubcategories = $this->getState('filter.subcategories', false);
            $categoryEquals = 'wd.catid '.$type.(int) $categoryId;

            if ($includeSubcategories) {
                $levels = (int) $this->getState('filter.max_category_levels', '1');

                // Create a subquery for the subcategory list
                $subQuery = $db->getQuery(true)
                    ->select('sub.id')
                    ->from('#__categories as sub')
                    ->join('INNER', '#__categories as this ON sub.lft > this.lft AND sub.rgt < this.rgt')
                    ->where('this.id = '.(int) $categoryId);

                if ($levels >= 0) {
                    $subQuery->where('sub.level <= this.level + '.$levels);
                }

                // Add the subquery to the main query
                $query->where('('.$categoryEquals.' OR wd.catid IN ('.(string) $subQuery.'))');
            } else {
                $query->where($categoryEquals);
            }
        } elseif (is_array($categoryId) && (count($categoryId) > 0)) {
            $categoryId = ArrayHelper::toInteger($categoryId);
            $categoryId = implode(',', $categoryId);

            if (!empty($categoryId)) {
                $type = $this->getState('filter.category_id.include', true) ? 'IN' : 'NOT IN';
                $query->where('wd.catid '.$type.' ('.$categoryId.')');
            }
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

        // Filter by author
        $authorId = $this->getState('filter.author_id');
        $authorWhere = '';

        if (is_numeric($authorId)) {
            $type = $this->getState('filter.author_id.include', true) ? '= ' : '<> ';
            $authorWhere = 'wd.created_by '.$type.(int) $authorId;
        } elseif (is_array($authorId)) {
            $authorId = ArrayHelper::toInteger($authorId);
            $authorId = implode(',', $authorId);

            if ($authorId) {
                $type = $this->getState('filter.author_id.include', true) ? 'IN' : 'NOT IN';
                $authorWhere = 'wd.created_by '.$type.' ('.$authorId.')';
            }
        }

        // Filter by author alias
        $authorAlias = $this->getState('filter.author_alias');
        $authorAliasWhere = '';

        if (is_string($authorAlias)) {
            $type = $this->getState('filter.author_alias.include', true) ? '= ' : '<> ';
            $authorAliasWhere = 'wd.created_by_alias '.$type.$db->quote($authorAlias);
        } elseif (is_array($authorAlias)) {
            $first = current($authorAlias);

            if (!empty($first)) {
                foreach ($authorAlias as $key => $alias) {
                    $authorAlias[$key] = $db->quote($alias);
                }

                $authorAlias = implode(',', $authorAlias);

                if ($authorAlias) {
                    $type = $this->getState('filter.author_alias.include', true) ? 'IN' : 'NOT IN';
                    $authorAliasWhere = 'wd.created_by_alias '.$type.' ('.$authorAlias.
                        ')';
                }
            }
        }

        if (!empty($authorWhere) && !empty($authorAliasWhere)) {
            $query->where('('.$authorWhere.' OR '.$authorAliasWhere.')');
        } elseif (empty($authorWhere) && empty($authorAliasWhere)) {
            // If both are empty we don't want to add to the query
        } else {
            // One of these is empty, the other is not so we just add both
            $query->where($authorWhere.$authorAliasWhere);
        }

        // Define null and now dates
        $nullDate = $db->quote($db->getNullDate());
        $nowDate = $db->quote(JFactory::getDate()->toSql());

        // Filter by start and end dates.
        if ((!$user->authorise('core.edit.state', 'com_logmoniter')) && (!$user->authorise('core.edit', 'com_logmoniter'))) {
            $query->where('(wd.publish_up = '.$nullDate.' OR wd.publish_up <= '.$nowDate.')')
                ->where('(wd.publish_down = '.$nullDate.' OR wd.publish_down >= '.$nowDate.')');
        }

        // Filter by Date Range or Relative Date
        $dateFiltering = $this->getState('filter.date_filtering', 'off');
        $dateField = $this->getState('filter.date_field', 'wd.created');

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

        // Process the filter for list views with user-entered filters
        if (is_object($params) && ($params->get('filter_field') !== 'hide') && ($filter = $this->getState('list.filter'))) {
            // Clean filter variable
            $filter = StringHelper::strtolower($filter);
            $hitsFilter = (int) $filter;
            $logsFilter = (int) $filter;
            $filter = $db->quote('%'.$db->escape($filter, true).'%', false);

            switch ($params->get('filter_field')) {
                case 'author':
                    $query->where(
                        'LOWER( CASE WHEN wd.created_by_alias > '.$db->quote(' ').
                            ' THEN wd.created_by_alias ELSE uw.name END ) LIKE '.$filter.' '
                    );
                    break;

                case 'hits':
                    $query->where('wd.hits >= '.$hitsFilter.' ');
                    break;

                case 'logs':
                    $query->where('wd.log_count <= '.$logsFilter.' ');
                    break;

                case 'title':
                default:
                    // Default to 'title' if parameter is not valid
                    $query->where('LOWER( wd.title ) LIKE '.$filter);
                    break;
            }
        }

        // Filter by language
        if ($this->getState('filter.language')) {
            $query->where('wd.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')');
        }

        // Filter by a single tag.
        $tagId = $this->getState('filter.tag');

        if (!empty($tagId) && is_numeric($tagId)) {
            $query->where($db->quoteName('tagmap.tag_id').' = '.(int) $tagId)
                ->join(
                    'LEFT', $db->quoteName('#__contentitem_tag_map', 'tagmap')
                    .' ON '.$db->quoteName('tagmap.content_item_id').' = '.$db->quoteName('wd.id')
                    .' AND '.$db->quoteName('tagmap.type_alias').' = '.$db->quote('com_logmoniter.watchdog')
                );
        }

        // Add the list ordering clause.
        $query->order($this->getState('list.ordering', 'wd.ordering').' '.$this->getState('list.direction', 'ASC'));

        return $query;
    }

    /**
     * Method to get a list of watchdogs.
     *
     * Overriden to inject convert the params field into a JParameter object.
     *
     * @return mixed an array of objects on success, false on failure
     *
     * @since   1.6
     */
    public function getItems()
    {
        $items = parent::getItems();
        $user = JFactory::getUser();
        $userId = $user->get('id');
        $guest = $user->get('guest');
        $groups = $user->getAuthorisedViewLevels();
        $input = JFactory::getApplication()->input;

        // Get the global params
        $globalParams = JComponentHelper::getParams('com_logmoniter', true);

        // Convert the parameter fields into objects.
        foreach ($items as &$item) {
            $watchdogParams = new Registry($item->params);

            // Unpack layout params
            $item->layout = $watchdogParams->get('layout');

            $item->params = clone $this->getState('params');

            // menu item params control the layout
            // hence merge all of the watchdog params
            $item->params->merge($watchdogParams);

            // Get display date
            switch ($item->params->get('list_show_date')) {
                case 'modified':
                    $item->displayDate = $item->modified;
                    break;

                case 'published':
                    $item->displayDate = ($item->publish_up == 0) ? $item->created : $item->publish_up;
                    break;

                case 'latest_log_date':
                    $item->displayDate = ($item->latest_log_date == 0) ? $item->created : $item->latest_log_date;
                    break;

                case 'next_due_date':
                    $item->displayDate = ($item->next_due_date == 0) ? $item->created : $item->next_due_date;
                    break;

                default:
                case 'created':
                    $item->displayDate = $item->created;
                    break;
            }

            // Compute the asset access permissions.
            // Technically guest could edit an watchdog, but lets not check that to improve performance a little.
            if (!$guest) {
                $asset = 'com_logmoniter.watchdog.'.$item->id;

                // Check general edit permission first.
                if ($user->authorise('core.edit', $asset)) {
                    $item->params->set('access-edit', true);
                }

                // Now check if edit.own is available.
                elseif (!empty($userId) && $user->authorise('core.edit.own', $asset)) {
                    // Check for a valid user and that they are the owner.
                    if ($userId == $item->created_by) {
                        $item->params->set('access-edit', true);
                    }
                }
            }

            $access = $this->getState('filter.access');

            if ($access) {
                // If the access filter has been set, we already have only the watchdogs this user can view.
                $item->params->set('access-view', true);
            } else {
                // If no access filter is set, the layout takes some responsibility for display of limited information.
                if ($item->catid == 0 || $item->category_access === null) {
                    $item->params->set('access-view', in_array($item->access, $groups));
                } else {
                    $item->params->set('access-view', in_array($item->access, $groups) && in_array($item->category_access, $groups));
                }
            }

            // Some contexts may not use tags data at all, so we allow callers to disable loading tag data
            if ($this->getState('load_tags', $item->params->get('show_tags', '1'))) {
                $item->tags = new JHelperTags();
                $item->tags->getItemTags('com_logmoniter.watchdog', $item->id);
            }

            if ($item->params->get('show_associations')) {
                $item->associations = LogmoniterHelperAssociation::displayAssociations($item->id);
            }
        }

        return $items;
    }

    /**
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
