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
class LogbookViewBlueprints extends JViewLegacy
{
    /**
     * Display the Blueprints view.
     *
     * @param string $tpl the name of the template file to parse; automatically searches through the template paths
     */
    public function display($tpl = null)
    {
        //Get Application
        $app = JFactory::getApplication();
        $context = "logbook.list.admin.blueprint";

        // Get data from the model
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state			= $this->get('State');
		$this->filter_order 	= $app->getUserStateFromRequest($context.'filter_order', 'filter_order', 'title', 'cmd');
		$this->filter_order_Dir = $app->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', 'asc', 'cmd');
		$this->filterForm    	= $this->get('FilterForm');
		$this->activeFilters 	= $this->get('ActiveFilters');
        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode('<br />', $errors));

            return false;
        }
        // Set the toolbar and number of found items
        $this->addToolBar();

        // Display the template
        parent::display($tpl);

        // Set the document
		$this->setDocument();
    }

    /**
     * Add the page title and toolbar.
     *
     *
     * @since   1.6
     */
    protected function addToolBar()
    {
        $title=JText::_('COM_LOGBOOK_MANAGER_BLUEPRINTS');
        if ($this->pagination->total)
		{
			$title .= "<span style='font-size: 0.5em; vertical-align: middle;'>(" . $this->pagination->total . ")</span>";
		}


        JToolbarHelper::title($title, 'blueprint');
        JToolbarHelper::addNew('blueprint.add');
        JToolbarHelper::editList('blueprint.edit');
        JToolbarHelper::deleteList('Are You Sure!', 'blueprints.delete');
    }

    /**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument() 
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_LOGBOOK_ADMINISTRATION'));
	}
}
