<?php
/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
require_once JPATH_ADMINISTRATOR.'/components/com_logbook_helpers/logbook.php';

/**
 * Loctions View.
 *
 * @since  0.0.1
 */
class LogbookViewBlueprints extends JViewLegacy
{
    
    protected $items;
    protected $state;
    protected $pagination;
    public    $filterForm;
    public    $activeFilters;
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
		$this->filterForm    	= $this->get('FilterForm');
		$this->activeFilters 	= $this->get('ActiveFilters');
        
        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode('<br />', $errors));

            return false;
        }

        //Check if the Logbook plugin is installed (or if it is enabled). If it doesn't we display an
        //information note.
        if(!JPluginHelper::isEnabled('content', 'logbook')) {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_LOGBOOK_PLUGIN_NOT_INSTALLED'), 'warning');
        }

        // Set the toolbar and sidebar
        $this->addToolBar();
        $this->sidebar = JHtmlSidebar::render();

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
        //Display the view title and the icon.
        JToolBarHelper::title(JText::_('COM_LOGBOOK_MANAGER_DOCUMENTS'), 'stack blueprint');

        //Get the allowed actions list
        $canDo = LogbookHelper::getActions();
        $user = JFactory::getUser();

        //The user is allowed to create or is able to create in one of the component
        //categories.
        if($canDo->get('core.create') || (count($user->getAuthorisedCategories('com_logbook', 'core.create'))) > 0) {
            JToolBarHelper::addNew('blueprint.add', 'JTOOLBAR_NEW');
        }

        //Notes: The Edit icon might not be displayed since it's not (yet ?) possible 
        //to edit several items at a time.
        if($canDo->get('core.edit') || $canDo->get('core.edit.own') || 
            (count($user->getAuthorisedCategories('com_logbook', 'core.edit'))) > 0 || 
            (count($user->getAuthorisedCategories('com_logbook', 'core.edit.own'))) > 0) {
            JToolBarHelper::editList('blueprint.edit', 'JTOOLBAR_EDIT');
        }

        //Check for state permission.
        if($canDo->get('core.edit.state') || (count($user->getAuthorisedCategories('com_logbook', 'core.edit.state'))) > 0) {
            JToolBarHelper::divider();
            JToolBarHelper::custom('blueprints.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
            JToolBarHelper::custom('blueprints.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
            JToolBarHelper::divider();
            JToolBarHelper::archiveList('blueprints.archive','JTOOLBAR_ARCHIVE');

            if($canDo->get('core.edit.state')) { 
                JToolBarHelper::custom('blueprints.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
                JToolBarHelper::trash('blueprints.trash','JTOOLBAR_TRASH');
            }
            JToolbarHelper::title($title, 'blueprint');
            JToolbarHelper::addNew('blueprint.add');
            JToolbarHelper::editList('blueprint.edit');
            JToolbarHelper::deleteList('Are You Sure!', 'blueprints.delete');
        }

        //Check for delete permission.
        if($canDo->get('core.delete') || count($user->getAuthorisedCategories('com_logbook', 'core.delete'))) {
            JToolBarHelper::divider();
            JToolBarHelper::deleteList('', 'blueprints.delete', 'JTOOLBAR_DELETE');
        }
    
        if($canDo->get('core.admin')) {
            JToolBarHelper::divider();
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
