<?php
/**
 * @copyright   Copyright (C) 2020 Amit Kumar Shukla, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Location Model.
 *
 * @since  0.0.1
 */
class LogbookModelLocation extends JModelAdmin
{
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
    public function getTable($type = 'Location', $prefix = 'LogbookTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get the record form.
     *
     * @param array $data     data for the form
     * @param bool  $loadData true if the form is to load its own data (default case), false if not
     *
     * @return mixed A JForm object on success, false on failure
     *
     * @since   1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm(
            'com_logbook.location',
            'location',
            array(
                'control' => 'jform',
                'load_data' => $loadData,
            )
        );

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return mixed the data for the form
     *
     * @since   1.6
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState(
            'com_logbook.edit.location.data',
            array()
        );

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }
}
