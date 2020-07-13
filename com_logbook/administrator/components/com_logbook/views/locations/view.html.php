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
    protected $items;
    protected $state;
    protected $pagination;
    public    $filterForm;
    public    $activeFilters;
    /**
     * Display the Locations view.
     *
     * @param string $tpl the name of the template file to parse; automatically searches through the template paths
     */
    public function display($tpl = null)
    {
        //Get Application
        $app = JFactory::getApplication();
        $context = "logbook.list.admin.location";

        // Get data from the model
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state			= $this->get('State');
		$this->filter_order 	= $app->getUserStateFromRequest($context.'filter_order', 'filter_order', 'name', 'cmd');
		$this->filter_order_Dir = $app->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', 'asc', 'cmd');
		$this->filterForm    	= $this->get('FilterForm');
		$this->activeFilters 	= $this->get('ActiveFilters');
        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode('<br />', $errors));

            return false;
        }

        //Check if the LMI Record Manager plugin is installed (or if it is enabled). If it doesn't we display an
        //information note.
        if(!JPluginHelper::isEnabled('content', 'lrm')) {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_LOGBOOK_PLUGIN_NOT_INSTALLED'), 'warning');
        }

        //Display the toolbar and the sidebar.
        $this->addToolBar();
        $this->sidebar = JHtmlSidebar::render();

        //Display the template.
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
        JToolBarHelper::title(JText::_('COM_LOGBOOK_MANAGER_LOCATIONS'), 'location');

        require_once JPATH_COMPONENT.'/helpers/logbook.php';
        $user = JFactory::getUser();
        $canDo = LogbookHelper::getActions();

        if($canDo->get('core.create')) {
        JToolBarHelper::addNew('location.add', 'JTOOLBAR_NEW');
        JToolBarHelper::divider();
        }

        if($canDo->get('core.edit')) {
        JToolBarHelper::editList('location.edit', 'JTOOLBAR_EDIT');
        JToolBarHelper::divider();
        }

        if($canDo->get('core.edit.state')) { 
        JToolBarHelper::custom('location.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
        JToolBarHelper::divider();
        }

        if($canDo->get('core.delete')) {
        JToolBarHelper::deleteList(JText::_('COM_LOGBOOK_DELETE_CONFIRMATION'), 'location.delete', 'JTOOLBAR_DELETE');
        JToolBarHelper::divider();
        }

        if($canDo->get('core.admin')) {
        JToolBarHelper::preferences('com_logbook', 550);
        }
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


