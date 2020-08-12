<?php
/**
 * @package LMI/LogManager/LogBook/LogMoniter
 * @copyright Copyright (c)2020 Amit Kumar Shukla. All Rights Reserved.
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

JLoader::register('LogmoniterHelperAssociation', JPATH_SITE.'/components/com_logmoniter/helpers/association.php');

/**
 * This models supports retrieving lists of watchdogs.
 *
 * @since  1.6
 */
class LogmoniterModelMoniter extends JModelList
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
        // Set the published column alias
        //$this->setColumnAlias('published', 'state');

        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'wd.id',
                'title', 'wd.title',
                'wcid', 'wd.wcid', 'wcenter_title',
                'isid', 'wd.isid', 'inset_title',
                'bpid', 'wd.bpid', 'bprint_title',
                'tiid', 'wd.tiid', 'tinterval_title',
                'hits', 'wd.hits',
                'logs', 'wd.log_count',
                'latest_log_date', 'wd.latest_log_date',
                'next_due_date', 'wd.next_due_date',
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
    protected function populateState($ordering = 'wd.title', $direction = 'ASC')
    {
        $app = JFactory::getApplication();

        $forcedLanguage = $app->input->get('forcedLanguage', '', 'cmd');

        // Adjust the context to support forced languages.
        if ($forcedLanguage) {
            $this->context .= '.'.$forcedLanguage;
        }

        // List state information
        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

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
     * saveListState: saves the present list parameters in the UserState.
     *
     * in een $(document).ready wordt <input id="js-stools-field-order" name="list[fullordering]">
     * on the fly aangemaakt die meekomt met de Joomla.tableOrdering('id','desc','') submit as
     * ["list"]=>  array(1) { ["fullordering"]=>string(8) "null ASC" }
     * See media/jui/js/jquery.searchtools.js
     * This is for the ProtoStar template and may work differently for other templates.
     *
     * so:
     * * for a 'submit', e.g. by clicking the sort column, the 'list' array does not help
     * as it only contains "fullordering" and not the column id. However, populateState also
     * checks the "old ordering fields" 'get' parameters and still sets 'filter_order' in State.
     * So selecting the order column should be fine.
     * * a page reload without a submit, for instance when returning after visiting the item
     * edit page has no list[fullordering]. In this case the saved 'list' values are restored
     * by the standard populateState, as intended.
     * * Note: do not call getUserStateFromRequest(list...) in the mean time, as this function not only
     * retrieves 'list' but also updates the UserState from the 'get' parameters as side effect!
     */
    /*public function saveListState()
    {
        $limit = $this->state->get('list.limit');
        $filter_order = $this->state->get('list.ordering');
        $filter_order_Dir = $this->state->get('list.direction');
        JFactory::getApplication()->setUserState($this->context.'.list', array(
            'limit' => $limit,
            'ordering' => $filter_order,
            'direction' => $filter_order_Dir,
            ));
    }*/

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
                'wd.state ,'.
                // Use created if modified is 0
                'CASE WHEN wd.modified = '.$db->quote($db->getNullDate()).' THEN wd.created ELSE wd.modified END as modified, '.
                'wd.modified_by, '.
                // Use created if publish_up is 0
                'CASE WHEN wd.publish_up = '.$db->quote($db->getNullDate()).' THEN wd.created ELSE wd.publish_up END as publish_up,'.
                'wd.publish_down, wd.params, wd.metadata, wd.metakey, wd.metadesc, wd.access, '.
                'wd.hits, wd.log_count, wd.language'
            )
        );

        $query->from('#__logbook_watchdogs AS wd');

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
        // Filter: like / search
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            $like = $db->quote('%'.$search.'%');
            $query->where('wd.title LIKE '.$like);
        }

        // Filter by published state
        $published = $this->getState('filter.published');

        if (is_numeric($published)) {
            $query->where('wd.state = '.(int) $published);
        } elseif ($published === '') {
            $query->where('(wd.state IN (0, 1))');
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

        /*// Define null and now dates
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
        }*/

        // Add the list ordering clause.
        $orderCol = $this->state->get('list.ordering', 'wd.title');
        $orderDirn = $this->state->get('list.direction', 'asc');
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
    /*public function getItems()
    {
        $items = parent::getItems();
        if (empty($items)) {
            JFactory::getApplication()->enqueueMessage('$items empty');
        }
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
    /*public function getStart()
    {
        return $this->getState('list.start');
    }*/
}
