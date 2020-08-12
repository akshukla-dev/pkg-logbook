<?php
/**
 * @copyright Copyright (c)2020 Amit Kumar Shukla. All Rights Reserved.
 * @license GNU General Public License version 3, or later
*/
defined('_JEXEC') or die; // No direct access

/**
 * View class for a list of logs.
 *
 * @since  1.6
 */
class LogbookViewLogs extends JViewLegacy
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
        $this->items = $this->get('Items');
        $this->state = $this->get('State');
        $this->pagination = $this->get('Pagination');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        // Modal layout doesn't need the submenu.
        if ($this->getLayout() !== 'modal') {
            LogbookHelper::addSubmenu('logs');
        }
        //Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors), 500);
        }

        // We don't need toolbar in the modal layout.
        if ($this->getLayout() !== 'modal') {
            $this->addToolbar();
            $this->sidebar = JHtmlSidebar::render();
        } else {
            // In log associations modal we need to remove language filter if forcing a language.
            // We also need to change the category filter to show show categories with All or the forced language.
            if ($forcedLanguage = JFactory::getApplication()->input->get('forcedLanguage', '', 'CMD')) {
                // If the language is forced we can't allow to select the language, so transform the language selector filter into an hidden field.
                $languageXml = new SimpleXMLElement('<field name="language" type="hidden" default="'.$forcedLanguage.'" />');
                $this->filterForm->setField($languageXml, 'filter', true);

                // Also, unset the active language filter so the search tools is not open by default with this filter.
                unset($this->activeFilters['language']);

                // One last changes needed is to change the category filter to just show categories with All language or with the forced language.
                $this->filterForm->setFieldAttribute('category_id', 'language', '*,'.$forcedLanguage, 'filter');
            }
        }

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
        $state = $this->get('State');

        //Get the allowed actions list
        $canDo = LogbookHelper::getActions('com_logbook', 'logs', $this->state->get($item->id));
        $user = JFactory::getUser();

        // Get the toolbar object instance
        $bar = JToolbar::getInstance('toolbar');

        //Display the view title and the icon.
        JToolBarHelper::title(JText::_('COM_LOGBOOK_MANAGER_LOGS_TITLE'), 'stack log');

        //The user is allowed to create?
        if ($canDo->get('core.create')) {
            JToolBarHelper::addNew('log.add', 'JTOOLBAR_NEW');
        }

        //Notes: The Edit icon might not be displayed since it's not (yet ?) possible
        //to edit several items at a time.
        if ($canDo->get('core.edit') || $canDo->get('core.edit.own')) {
            JToolBarHelper::editList('log.edit', 'JTOOLBAR_EDIT');
        }

        //Check for state permission.
        if ($canDo->get('core.edit.state')) {
            JToolBarHelper::publish('logs.publish', 'JTOOLBAR_PUBLISH', true);
            JToolBarHelper::unpublish('logs.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            JToolBarHelper::archiveList('logs.archive', 'JTOOLBAR_ARCHIVE');
            JToolBarHelper::checkin('logs.checkin', 'JTOOLBAR_CHECKIN', true);
            JToolBarHelper::trash('logs.trash', 'JTOOLBAR_TRASH');
        }

        if ($state->get('filter.published') == -2 && $canDo->get('core.delete')) {
            JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'logs.delete', 'JTOOLBAR_EMPTY_TRASH');
        } elseif ($canDo->get('core.edit.state')) {
            JToolbarHelper::trash('logs.trash');
        }

        // Add a batch button
        if ($user->authorise('core.create', 'com_logbook') && $user->authorise('core.edit', 'com_logbook')
            && $user->authorise('core.edit.state', 'com_logbook')) {
            JHtml::_('bootstrap.modal', 'collapseModal');
            $title = JText::_('JTOOLBAR_BATCH');

            // Instantiate a new JLayoutFile instance and render the batch button
            $layout = new JLayoutFile('joomla.toolbar.batch');

            $dhtml = $layout->render(array('title' => $title));
            $bar->appendButton('Custom', $dhtml, 'batch');
        }

        if ($canDo->get('core.admin')) {
            JToolBarHelper::divider();
            JToolBarHelper::preferences('com_logbook', 550);
        }
        JToolbarHelper::help('JHELP_COMPONENTS_LOGBOOK_LOGS');
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
            'l.ordering' => JText::_('JGRID_HEADING_ORDERING'),
            'l.state' => JText::_('JSTATUS'),
            'l.title' => JText::_('JGLOBAL_TITLE'),
            'access_level' => JText::_('JGRID_HEADING_ACCESS'),
            'l.created_by' => JText::_('JAUTHOR'),
            'l.created' => JText::_('JDATE'),
            'l.hits' => JText::_('JGLOBAL_HITS'),
            'l.id' => JText::_('JGRID_HEADING_ID'),
        );
    }
}
