<?php
/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Loctions View.
 *
 * @since  0.0.1
 */
class LogbookViewLocations extends JViewLegacy
{
    /**
     * Display the Locations view.
     *
     * @param string $tpl the name of the template file to parse; automatically searches through the template paths
     */
    public function display($tpl = null)
    {
        // Get data from the model
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode('<br />', $errors));

            return false;
        }
        // Set the toolbar
        $this->addToolBar();

        // Display the template
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     *
     * @since   1.6
     */
    protected function addToolBar()
    {
        JToolbarHelper::title(JText::_('COM_LOGBOOK_MANAGER_LOCATIONS'));
        JToolbarHelper::addNew('location.add');
        JToolbarHelper::editList('location.edit');
        JToolbarHelper::deleteList('Are You Sure!', 'locations.delete');
    }
}
