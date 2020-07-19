<?php
/**
 * @copyright Copyright (c)2020 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 */
defined('_JEXEC') or die; //No direct access to this file.

class LogmoniterModelWatchdog extends JModelItem
{
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
    }

    //Returns a Table object, always creating it.
    public function getTable($type = 'Watchdog', $prefix = 'LogmoniterTable', $config = array())
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
        $pk = (!empty($pk)) ? $pk : (int) $this->getState('watchdog.id');

        if ($this->_item === null) {
            $this->_item = array();
        }

        if (!isset($this->_item[$pk])) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('w.*');
            $query->from('#__logbook_watchdogs AS w');

            // Join on category table.
            $query->select('c.title AS category_title, c.alias AS category_alias, c.access AS category_access')
                ->join('LEFT', '#__categories AS c on c.id = d.catid');

            // Join over the users.
            $query->select('u.name AS put_online_by')
                ->join('LEFT', '#__users AS u ON u.id = d.created_by');

            $query->where('w.id='.$pk);
            $db->setQuery($query);
            $data = $db->loadObject();

            if (is_null($data)) {
                JFactory::getApplication()->enqueueMessage(JText::_('COM_LOGMONITER_ERROR_WATCHDOG_NOT_FOUND'), 'error');

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

            // Get the tags
            $data->tags = new JHelperTags();
            $data->tags->getItemTags('com_logmoniter.watchdog', $data->id);

            $this->_item[$pk] = $data;
        }

        return $this->_item[$pk];
    }

    /**
     * Increment the hit counter for the document.
     *
     * @param int $pk optional primary key of the document to increment
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
