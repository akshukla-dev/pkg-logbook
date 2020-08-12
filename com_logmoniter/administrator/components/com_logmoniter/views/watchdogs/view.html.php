<?php
/**
 * @package LMI/LogManager/LogBook/LogMoniter
 * @copyright Copyright (c)2020 Amit Kumar Shukla. All Rights Reserved.
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */
defined('_JEXEC') or die;

/**
 * View class for a list of watchdogs.
 *
 * @since  1.6
 */
class LogmoniterViewWatchdogs extends JViewLegacy
{
    /**
     * The item authors.
     *
     * @var stdClass
     */
    protected $authors;

    /**
     * An array of items.
     *
     * @var array
     */
    protected $items;

    /**
     * The pagination object.
     *
     * @var JPagination
     */
    protected $pagination;

    /**
     * The model state.
     *
     * @var object
     */
    protected $state;

    /**
     * Form object for search filters.
     *
     * @var JForm
     */
    public $filterForm;

    /**
     * The active search filters.
     *
     * @var array
     */
    public $activeFilters;

    /**
     * The sidebar markup.
     *
     * @var string
     */
    protected $sidebar;

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
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->authors = $this->get('Authors');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        if ($this->getLayout() !== 'modal') {
            LogmoniterHelper::addSubmenu('watchdogs');
        }

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors), 500);
        }

        // We don't need toolbar in the modal window.
        if ($this->getLayout() !== 'modal') {
            $this->addToolbar();
            $this->sidebar = JHtmlSidebar::render();
        } else {
            // In watchdog associations modal we need to remove language filter if forcing a language.
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

        return parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     *
     * @since   1.6
     */
    protected function addToolbar()
    {
        //require_once JPATH_COMPONENT.'/helpers/logmoniter.php';

        $canDo = LogmoniterHelper::getActions('com_logmoniter', 'category', $this->state->get('filter.category_id'));
        $user = JFactory::getUser();

        // Get the toolbar object instance
        $bar = JToolbar::getInstance('toolbar');

        JToolbarHelper::title(JText::_('COM_LOGMONITER_WATCHDOGS_TITLE'), 'stack watchdogs');

        if ($canDo->get('core.create') || count($user->getAuthorisedCategories('com_logmoniter', 'core.create')) > 0) {
            JToolbarHelper::addNew('watchdog.add');
        }

        if ($canDo->get('core.edit') || $canDo->get('core.edit.own')) {
            JToolbarHelper::editList('watchdog.edit');
        }

        if ($canDo->get('core.edit.state')) {
            JToolbarHelper::publish('watchdogs.publish', 'JTOOLBAR_PUBLISH', true);
            JToolbarHelper::unpublish('watchdogs.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            JToolbarHelper::archiveList('watchdogs.archive');
            JToolbarHelper::checkin('watchdogs.checkin');
        }

        if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete')) {
            JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'watchdogs.delete', 'JTOOLBAR_EMPTY_TRASH');
        } elseif ($canDo->get('core.edit.state')) {
            JToolbarHelper::trash('watchdogs.trash');
        }

        // Add a batch button
        if ($user->authorise('core.create', 'com_logmoniter')
            && $user->authorise('core.edit', 'com_logmoniter')
            && $user->authorise('core.edit.state', 'com_logmoniter')) {
            JHtml::_('bootstrap.modal', 'collapseModal');
            $title = JText::_('JTOOLBAR_BATCH');

            // Instantiate a new JLayoutFile instance and render the batch button
            $layout = new JLayoutFile('joomla.toolbar.batch');

            $dhtml = $layout->render(array('title' => $title));
            $bar->appendButton('Custom', $dhtml, 'batch');
        }

        if ($user->authorise('core.admin', 'com_logmoniter') || $user->authorise('core.options', 'com_logmoniter')) {
            JToolbarHelper::preferences('com_logmoniter');
        }

        JToolbarHelper::help('JHELP_LOGMONITER_WATCHDOG_MANAGER');
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
            'wd.title' => JText::_('JGLOBAL_TITLE'),
            'category_title' => JText::_('JCATEGORY'),
            'inset_title' => JText::_('COM_LOGMONITER_INSET'),
            'bprint_title' => JText::_('COM_LOGMONITER_BPRINT'),
            'access_level' => JText::_('JGRID_HEADING_ACCESS'),
            'wd.created_by' => JText::_('JAUTHOR'),
            'language' => JText::_('JGRID_HEADING_LANGUAGE'),
            'wd.created' => JText::_('JDATE'),
            'wd.hits' => JText::_('JHITS'),
            'wd.log_count' => JText::_('COM_LOGMONITER_LOGS'),
            'wd.id' => JText::_('JGRID_HEADING_ID'),
        );
    }
}
