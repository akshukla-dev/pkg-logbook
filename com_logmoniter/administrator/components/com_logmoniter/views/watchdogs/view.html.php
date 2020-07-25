<?php
/**
 * @copyright Copyright (c)2020 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
*/
defined('_JEXEC') or die; // No direct access

/**
 * View class for a list of watchdogs.
 *
 * @since  1.6
 */
class LogmoniterViewWatchdogs extends JViewLegacy
{
    protected $items;
    protected $state;
    protected $pagination;
    protected $canDo;
    public $filterForm;
    public $activeFilters;
    protected $sideBar;

    /**
     * Display the view.
     *
     * @param string $tpl the name of the template file to parse; automatically searches through the template paths
     *
     * @return mixed a string if successful, otherwise an Error object
     */
    public function display($tpl = null)
    {
        LogmoniterHelper::addSubmenu('watchdogs');

        $this->items = $this->get('Items');
        $this->state = $this->get('State');
        $this->pagination = $this->get('Pagination');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        //Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors), 500);
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
     * @since   1.6
     */
    protected function addToolBar()
    {
        //Get the allowed actions list
        $canDo = LogmoniterHelper::getActions('COM_LOGMONITER', 'watchdogs', $this->state->get($item->id));
        $user = JFactory::getUser();

        // Get the toolbar object instance
        $bar = JToolbar::getInstance('toolbar');

        //Display the view title and the icon.
        JToolBarHelper::title(JText::_('COM_LOGMONITER_MANAGER_WATCHDOGS_TITLE'), 'stack watchdogs');

        //The user is allowed to create?
        if ($canDo->get('core.create')) {
            JToolBarHelper::addNew('watchdog.add', 'JTOOLBAR_NEW');
        }

        //Notes: The Edit icon might not be displayed since it's not (yet ?) possible
        //to edit several items at a time.
        if ($canDo->get('core.edit') || $canDo->get('core.edit.own')) {
            JToolBarHelper::editList('watchdog.edit', 'JTOOLBAR_EDIT');
        }

        //Check for state permission.
        if ($canDo->get('core.edit.state')) {
            JToolBarHelper::publish('watchdogs.publish', 'JTOOLBAR_PUBLISH', true);
            JToolBarHelper::unpublish('watchdogs.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            JToolBarHelper::archiveList('watchdogs.archive', 'JTOOLBAR_ARCHIVE');
            JToolBarHelper::checkin('watchdogs.checkin', 'JTOOLBAR_CHECKIN', true);
            JToolBarHelper::trash('watchdogs.trash', 'JTOOLBAR_TRASH');
        }

        //Check for delete permission.
        if ($canDo->get('core.delete')) {
            JToolBarHelper::divider();
            JToolBarHelper::deleteList(JText::_('COM_LOGMONITER_CONFIRM_DELETE'), 'watchdogs.delete', 'JTOOLBAR_DELETE');
        }

        if ($canDo->get('core.admin')) {
            JToolBarHelper::divider();
            JToolBarHelper::preferences('COM_LOGMONITER', 550);
        }
    }

    /**
     * Returns an array of fields the table can be sorted by.
     *
     * @return array Array containing the field name to sort by as the key and display text as value
     *
     * @since   3.0
     */
    protected function getSortFields()
    {
        return array(
            'wd.ordering' => JText::_('JGRID_HEADING_ORDERING'),
            'wd.state' => JText::_('JSTATUS'),
            'wd.created' => JText::_('JDATE'),
            'wd.id' => JText::_('JGRID_HEADING_ID'),
            'wd.created_by' => JText::_('JAUTHOR'),
            'access_level' => JText::_('JGRID_HEADING_ACCESS'),
        );
    }
}
