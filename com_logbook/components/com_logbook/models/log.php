<?php
/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Logbook_Log Model.
 *
 * @since  0.0.1
 */
class LogbookModelLog extends JModelItem
{
    /**
     * @var string log
     */
    protected $log;

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
    public function getTable($type = 'log', $prefix = 'LogTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Get the log.
     *
     * @return string The log to be displayed to the user
     */
    public function getLog($id = 1)
    {
        if (!is_array($this->logs)) {
            $this->logs = array();
        }
        if (!isset($this->logs[$id])) {
            //Request the selection ID
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->get('id', 1, 'INT');

            //Get a Table Log Instance
            $table = $this->getTable();
            //Load the Log
            $table->load($id);

            //Assign the Log
            $this->logs[$id] = $table->log;
        }

        return $this->logs[$id];
    }
}
