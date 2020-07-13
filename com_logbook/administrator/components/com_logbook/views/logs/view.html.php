<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_logbook
 * 
 * @copyright Copyright (c)2020 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
*/


defined('_JEXEC') or die; // No direct access
 
require_once JPATH_ADMINISTRATOR.'/components/com_logbook/helpers/logbook.php';
 

class LogbookViewLogs extends JViewLegacy
{
    protected $items;
    protected $state;
    protected $pagination;
    protected $filterForm;
    protected $sctiveFilters;
    protected $sideBar;

    /**
     * Display the view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  mixed  A string if successful, otherwise an Error object.
     */
    public function display($tpl = null)
    {
        LogbookHelper::addSubmenu('logs');
        
        $this->items = $this->get('Items');
        $this->state = $this->get('State');
        $this->pagination = $this->get('Pagination');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        //Check for errors.
        if(count($errors = $this->get('Errors'))) {
        JFactory::getApplication()->enqueueMessage($errors, 'error');
        return false;
        }

        //Check if the Logbook plugin is installed (or if it is enabled). If it doesn't we display an
        //information note.
        if(!JPluginHelper::isEnabled('content', 'logbook')) {
        JFactory::getApplication()->enqueueMessage(JText::_('COM_LOGBOOK_PLUGIN_NOT_INSTALLED'), 'warning');
        }

        // Add toolbar and render sidebar.
        $this->addToolbar();
        $this->sidebar = JHtmlSidebar::render();

        //Display the template.
        parent::display($tpl);
    }


    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since   1.6
    */
    protected function addToolBar() 
    {
        // Get the toolbar object instance
        $bar = JToolbar::getInstance('toolbar');
        //Display the view title and the icon.
        JToolBarHelper::title(JText::_('COM_LOGBOOK_MANAGER_LOGS_TITLE'), 'stack logs');

        //Get the allowed actions list
        $canDo = LogbookHelper::getActions();
        $user = JFactory::getUser();

        //The user is allowed to create or is able to create in one of the component
        //categories.
        if($canDo->get('core.create') || (count($user->getAuthorisedCategories('com_logbook', 'core.create'))) > 0) {
            JToolBarHelper::addNew('log.add', 'JTOOLBAR_NEW');
        }

        //Notes: The Edit icon might not be displayed since it's not (yet ?) possible 
        //to edit several items at a time.
        if($canDo->get('core.edit') || $canDo->get('core.edit.own') || 
            (count($user->getAuthorisedCategories('com_logbook', 'core.edit'))) > 0 || 
            (count($user->getAuthorisedCategories('com_logbook', 'core.edit.own'))) > 0) {
            JToolBarHelper::editList('log.edit', 'JTOOLBAR_EDIT');
        }

        //Check for state permission.
        if($canDo->get('core.edit.state') || (count($user->getAuthorisedCategories('com_logbook', 'core.edit.state'))) > 0) {
            JToolBarHelper::divider();
            JToolBarHelper::publish('logs.publish', 'JTOOLBAR_PUBLISH', true);
            JToolBarHelper::unpublish('logs.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            JToolBarHelper::divider();
            JToolBarHelper::archiveList('logs.archive','JTOOLBAR_ARCHIVE');

            if($canDo->get('core.edit.state')) { 
                JToolBarHelper::chekin('logs.checkin', 'JTOOLBAR_CHECKIN', true);
                JToolBarHelper::trash('logs.trash','JTOOLBAR_TRASH');
            }
        }

        //Check for delete permission.
        if($canDo->get('core.delete') || count($user->getAuthorisedCategories('com_logbook', 'core.delete'))) {
            JToolBarHelper::divider();
            JToolBarHelper::deleteList('', 'logs.delete', 'JTOOLBAR_DELETE');
        }

        if($canDo->get('core.admin')) {
            JToolBarHelper::divider();
            JToolBarHelper::preferences('com_logbook', 550);
        }
    }

    /**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(
			'a.ordering'     => JText::_('JGRID_HEADING_ORDERING'),
			'a.state'        => JText::_('JSTATUS'),
			'a.title'        => JText::_('JGLOBAL_TITLE'),
			'category_title' => JText::_('JCATEGORY'),
			'access_level'   => JText::_('JGRID_HEADING_ACCESS'),
			'a.created_by'   => JText::_('JAUTHOR'),
			'language'       => JText::_('JGRID_HEADING_LANGUAGE'),
			'a.created'      => JText::_('JDATE'),
			'a.id'           => JText::_('JGRID_HEADING_ID'),
		);
	}
}