<?php
/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reservel.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

/**
 * Logbook_Log Model.
 *
 * @since  0.0.1
 */
class LogbookModelLog extends JModelItem
{
    protected $_context = 'com_logbook.log';

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
        $this->setState('log.id', $pk);

        //Load the global parameters of the component.
        $params = $app->getParams();
        $this->setState('params', $params);
    }

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param string $type   The table name. Optional.
     * @param string $prefix The class prefix. Optional.
     * @param array  $config Configuration array for model. Optional.
     *
     * @return JTable A JTable object
     *
     * @since   1.6
     */
    public function getTable($type = 'Log', $prefix = 'LogbookTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get a single record.
     *
     * @param int $pk the id of the primary key
     *
     * @return mixed object on success, false on failure
     *
     * @since   12.2
     */
    public function getItem($pk = null)
    {
        $pk = (!empty($pk)) ? $pk : (int) $this->getState('log.id');

        if ($this->_item === null) {
            $this->_item = array();
        }

        if (!isset($this->_item[$pk])) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('l.*');
            $query->from('#__logbook_logs AS l');

            // Join on watchdog table.
            $query->select('wd.title AS wdog_title, wd.alias AS wdog_alias, wd.access AS wd_access', 'wd.isid', 'wd.wcid', 'wd.bprint', 'wd.tiid')
                ->join('LEFT', '#__logbook_watchdogs AS wd ON wd.id = l.wdid');

            //Join over Instruction Sets Table
            $query->select('inset.title AS inset_title')
                ->join('LEFT', '#__logbook_instructionsets AS inset ON inset.id = wd.isid');
            //Join over Workcenters Table
            $query->select('wc.title AS wcenter_title')
                ->join('LEFT', '#__logbook_workcenters AS wc ON wc.id = wd.wcid');
            //Join over Blueprints Sets Table
            $query->select('bp.title AS blueprint_title')
                ->join('LEFT', '#__logbook_blueprints AS bp ON bp.id = wd.bpid');
            //Join over Time Intervals Table
            $query->select('ti.title AS tinterval_title')
                ->join('LEFT', '#__logbook_timeintervals AS ti ON ti.id = wd.tiid');

            // Join over the users.
            $query->select('u.name AS put_online_by')
                ->join('LEFT', '#__users AS u ON u.id = l.created_by');

            $query->where('l.id='.$pk);
            $db->setQuery($query);
            $data = $db->loadObject();

            if (is_null($data)) {
                JFactory::getApplication()->enqueueMessage(JText::_('COM_LOGBOOK_ERROR_LOG_NOT_FOUND'), 'error');

                return false;
            }

            // Convert parameter fields to objects.
            $registry = new JRegistry();
            $registry->loadString($data->params);

            $data->params = clone $this->getState('params');
            $data->params->merge($registry);

            $user = JFactory::getUser();
            // Technically guest could edit an article, but lets not check that to improve performance a little.
            if (!$user->get('guest')) {
                $userId = $user->get('id');
                $asset = 'com_logbook.log.'.$data->id;

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
            $this->_item[$pk] = $data;
        }

        return $this->_item[$pk];
    }

    /**
     * Increment the hit counter for the log.
     *
     * @param int $pk optional primary key of the log to increment
     *
     * @return bool true if successful; false otherwise and internal error set
     */
    public function hit($pk = 0)
    {
        $input = JFactory::getApplication()->input;
        $hitcount = $input->getInt('hitcount', 1);

        if ($hitcount) {
            $pk = (!empty($pk)) ? $pk : (int) $this->getState('log.id');

            $table = JTable::getInstance('Log', 'LogbookTable');
            $table->load($pk);
            $table->hit($pk);
        }

        return true;
    }
}
