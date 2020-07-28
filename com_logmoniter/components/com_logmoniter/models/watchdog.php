<?php
/**
 * @copyright Copyright (c)2020 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 */
defined('_JEXEC') or die; //No direct access to this file.

use Joomla\Registry\Registry;

/**
 * Logmoniter Component Watchdog Model.
 *
 * @since  1.5
 */
class LogmoniterModelWatchdog extends JModelItem
{
    /**
     * Model context string.
     *
     * @var string
     */
    protected $_context = 'com_logmoniter.watchdog';

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since   1.6
     */
    protected function populateState()
    {
        $app = JFactory::getApplication('site');

        // Load state from the request.
        $pk = $app->input->getInt('id');
        $this->setState('watchdog.id', $pk);

        //Load the global parameters of the component.
        $params = $app->getParams();
        $this->setState('params', $params);

        // TODO: Tune these values based on other permissions.
        $user = JFactory::getUser();

        if ((!$user->authorise('core.edit.state', 'com_logmoniter')) && (!$user->authorise('core.edit', 'com_logmoniter'))) {
            $this->setState('filter.published', 1);
            $this->setState('filter.archived', 2);
        }

        $this->setState('filter.language', JLanguageMultilang::isEnabled());
    }

    /**
     * Method to get watchdog data.
     *
     * @param int $pk the id of the watchdog
     *
     * @return object|bool|JException Menu item data object on success, boolean false or JException instance on error
     */
    public function getItem($pk = null)
    {
        $user = JFactory::getUser();

        $pk = (!empty($pk)) ? $pk : (int) $this->getState('watchdog.id');

        if ($this->_item === null) {
            $this->_item = array();
        }

        if (!isset($this->_item[$pk])) {
            try {
                $db = $this->getDbo();
                $query = $db->getQuery(true)
                    ->select(
                        $this->getState(
                            'item.select',
                            'wd.id, wd.asset_id, wd.title, wd.alias, wd.wcid, wd.isid, '.
                            'wd.bpid, wd.tiid, wd.tiid, '.
                            'wd.latest_log_date, wd.next_due_date, '.
                            'wd.state, wd.catid, wd.created, wd.created_by, wd.created_by_alias, '.
                            // Published/archived watchdog in archive category is treats as archive watchdog
                            // If category is not published then force 0
                            'CASE WHEN c.published = 2 AND wd.state > 0 THEN 2 WHEN c.published != 1 THEN 0 ELSE wd.state END as state,'.
                            // Use created if modified is 0
                            'CASE WHEN wd.modified = '.$db->quote($db->getNullDate()).' THEN wd.created ELSE wd.modified END as modified, '.
                            // Use created if publish_up is 0
                            'CASE WHEN wd.publish_up = '.$db->quote($db->getNullDate()).' THEN wd.created ELSE wd.publish_up END as publish_up,'.
                            'wd.modified_by, wd.checked_out, wd.checked_out_time, wd.publish_down, '.
                            'wd.publish_down, wd.attribs, wd.metadata, wd.metakey, wd.metadesc, wd.access, '.
                            'wd.hits, wd.log_count, wd.language, wd.version, wd.ordering'
                        )
                    );
                $query->from('#__logbook_watchdogs AS wd')
                    ->where('wd.id = '.(int) $pk);

                // Join on category table.
                $query->select('c.title AS category_title, c.alias AS category_alias, c.access AS category_access')
                    ->innerJoin('#__categories AS c on c.id = wd.catid')
                    ->where('c.published > 0');

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

                // Join on user table.
                $query->select('u.name AS author')
                    ->join('LEFT', '#__users AS u on u.id = wd.created_by');

                // Filter by language
                if ($this->getState('filter.language')) {
                    $query->where('wd.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')');
                }

                // Join over the categories to get parent category titles
                $query->select('parent.title as parent_title, parent.id as parent_id, parent.path as parent_route, parent.alias as parent_alias')
                    ->join('LEFT', '#__categories as parent ON parent.id = c.parent_id');

                if ((!$user->authorise('core.edit.state', 'com_logmoniter')) && (!$user->authorise('core.edit', 'com_logmoniter'))) {
                    // Filter by start and end dates.
                    $nullDate = $db->quote($db->getNullDate());
                    $date = JFactory::getDate();

                    $nowDate = $db->quote($date->toSql());

                    $query->where('(wd.publish_up = '.$nullDate.' OR wd.publish_up <= '.$nowDate.')')
                        ->where('(wd.publish_down = '.$nullDate.' OR wd.publish_down >= '.$nowDate.')');
                }

                // Filter by published state.
                $published = $this->getState('filter.published');
                $archived = $this->getState('filter.archived');

                if (is_numeric($published)) {
                    $query->where('(wd.state = '.(int) $published.' OR wd.state ='.(int) $archived.')');
                }

                $db->setQuery($query);

                $data = $db->loadObject();

                if (empty($data)) {
                    return JError::raiseError(404, JText::_('COM_LOGMONITER_ERROR_WATCHDOG_NOT_FOUND'));
                }

                // Check for published state if filter set.
                if ((is_numeric($published) || is_numeric($archived)) && (($data->state != $published) && ($data->state != $archived))) {
                    return JError::raiseError(404, JText::_('COM_LOGMONITER_ERROR_WATCHDOG_NOT_FOUND'));
                }

                // Convert parameter fields to objects.
                $registry = new Registry($data->attribs);

                $data->params = clone $this->getState('params');
                $data->params->merge($registry);

                $data->metadata = new Registry($data->metadata);

                // Technically guest could edit an watchdog, but lets not check that to improve performance a little.
                if (!$user->get('guest')) {
                    $userId = $user->get('id');
                    $asset = 'com_logmoniter.watchdog.'.$data->id;

                    // Check general edit permission first.
                    if ($user->authorise('core.edit', $asset)) {
                        $data->params->set('access-edit', true);
                    }

                    // Now check if edit.own is available.
                    elseif (!empty($userId) && $user->authorise('core.edit.own', $asset)) {
                        // Check for a valid user and that they are the owner.
                        if ($userId == $data->created_by) {
                            $data->params->set('access-edit', true);
                        }
                    }
                }

                // Compute view access permissions.
                if ($access = $this->getState('filter.access')) {
                    // If the access filter has been set, we already know this user can view.
                    $data->params->set('access-view', true);
                } else {
                    // If no access filter is set, the layout takes some responsibility for display of limited information.
                    $user = JFactory::getUser();
                    $groups = $user->getAuthorisedViewLevels();

                    if ($data->catid == 0 || $data->category_access === null) {
                        $data->params->set('access-view', in_array($data->access, $groups));
                    } else {
                        $data->params->set('access-view', in_array($data->access, $groups) && in_array($data->category_access, $groups));
                    }
                }

                $this->_item[$pk] = $data;
            } catch (Exception $e) {
                if ($e->getCode() == 404) {
                    // Need to go thru the error handler to allow Redirect to work.
                    JError::raiseError(404, $e->getMessage());
                } else {
                    $this->setError($e);
                    $this->_item[$pk] = false;
                }
            }
        }

        return $this->_item[$pk];
    }

    /**
     * Increment the hit counter for the watchdog.
     *
     * @param int $pk optional primary key of the watchdog to increment
     *
     * @return bool true if successful; false otherwise and internal error set
     */
    public function hit($pk = 0)
    {
        $input = JFactory::getApplication()->input;
        $hitcount = $input->getInt('hitcount', 1);

        if ($hitcount) {
            $pk = (!empty($pk)) ? $pk : (int) $this->getState('watchdog.id');

            $table = JTable::getInstance('Watchdog', 'LogmoniterTable');
            $table->load($pk);
            $table->hit($pk);
        }

        return true;
    }
}
