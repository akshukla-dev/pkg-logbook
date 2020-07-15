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
class LogbookViewWatchdogs extends JViewLegacy
{
    protected $items;
    protected $state;
    protected $pagination;
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
        LogbookHelper::addSubmenu('watchdogs');

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
        $canDo = LogbookHelper::getActions('com_logbook', 'watchdogs', $this->state->get($item->id));
        $user = JFactory::getUser();

        // Get the toolbar object instance
        $bar = JToolbar::getInstance('toolbar');

        //Display the view title and the icon.
        JToolBarHelper::title(JText::_('COM_LOGBOOK_MANAGER_WATCHDOGS_TITLE'), 'stack watchdogs');

        if ($canDo->get('core.admin')) {
            JToolBarHelper::addnew('watchdog.addnew', 'JTOOLBAR_NEW');
            JToolBarHelper::archiveList('watchdog.archive', 'JTOOLBAR_ARCHIVE');
            JToolBarHelper::checkin('watchdog.checkin', 'JTOOLBAR_CHECKIN', true);
            JToolBarHelper::trash('watchdog.trash', 'JTOOLBAR_TRASH');
            JToolBarHelper::divider();
            JToolBarHelper::deleteList(JText::_('COM_LOGBOOK_CONFIRM_DELETE'), 'watchdog.delete', 'JTOOLBAR_DELETE');
            JToolBarHelper::divider();
            JToolBarHelper::preferences('com_logbook', 550);
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
        );
    }
}
