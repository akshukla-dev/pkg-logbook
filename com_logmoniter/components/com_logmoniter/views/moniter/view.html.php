<?php
/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * HTML View class for the Logmoniter component.
 *
 * @since  1.5
 */
class LogmoniterViewMoniter extends JViewLegacy
{
    /**
     * Execute and display a template script.
     *
     * @param string $tpl the name of the template file to parse; automatically searches through the template paths
     *
     * @return mixed a string if successful, otherwise an Error object
     */
    public function display($tpl = null)
    {
        // include settings from the admin backend
        $this->includeAdminEnv();

        // Get data from the model
        $app = JFactory::getApplication();
        $inp = $app->input;
        $params = $this->params;
        $model = $this->getModel();
        $state = $this->get('State');
        $this->state = $state;
        //rom hello world 2 lines below
        //$this->filter_order = $state->get('list.ordering');
        //$this->filter_order_Dir = $state->get('list.direction');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        $this->pagination = $this->get('Pagination');
        //$this->script = $this->get('Script');

        //$model->saveListState();
        //	read array of all records from the database
        $this->items = $this->get('Items');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode('<br />', $errors));

            return false;
        }

        $this->_prepareDocument();

        parent::display($tpl);
    }

    /**
     * Prepares the document.
     */
    protected function _prepareDocument()
    {
        $document = JFactory::getDocument();
        $site = '/components/com_logmoniter/';
        $document->setTitle(JText::_('COM_LOGMONITER_SITE'));
        /*$app = JFactory::getApplication();
        $menus = $app->getMenu();
        $title = null;
        $params = $this->params;

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = $menus->getActive();

        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', JText::_('JGLOBAL_WATCHDOGS'));
        }

        $title = $this->params->get('page_title', '');

        if (empty($title)) {
            $title = $app->get('sitename');
        } elseif ($app->get('sitename_pagetitles', 0) == 1) {
            $title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
        } elseif ($app->get('sitename_pagetitles', 0) == 2) {
            $title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
        }

        $this->document->setTitle($title);

        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->params->get('robots')) {
            $this->document->setMetadata('robots', $this->params->get('robots'));
        }*/
    }

    /**
     * Add the page title and toolbar.
     *
     *
     * @since   1.6
     */
    protected function addToolBar()
    {
        // What Access Permissions does this user have? What can (s)he do?
        $this->canDo = LogmoniterHelper::getActions();

        $title = JText::_('COM_LOGMONITER_WATCHDOGS');

        if ($this->pagination->total) {
            $title .= "<span style='font-size: 0.5em; vertical-align: middle;'>(".$this->pagination->total.')</span>';
        }

        JToolBarHelper::title($title, 'watchdog');

        if ($this->canDo->get('core.create')) {
            JToolBarHelper::addNew('watchdog.add', 'JTOOLBAR_NEW');
        }
        if ($this->canDo->get('core.edit')) {
            JToolBarHelper::editList('watchdog.edit', 'JTOOLBAR_EDIT');
        }
    }

    /**
     * Include the functionality from the administrative backend.
     *
     * @return
     */
    private function includeAdminEnv()
    {
        // load the language files for the admin messages as well
        $language = JFactory::getLanguage();
        $language->load('joomla', JPATH_ADMINISTRATOR, null, true);
        //$language->load('com_logmoniter', JPATH_ADMINISTRATOR, null, true);

        //JLoader::register('JToolBarHelper', JPATH_ADMINISTRATOR.'/includes/toolbar.php');
        JLoader::register('JSubMenuHelper', JPATH_ADMINISTRATOR.'/includes/subtoolbar.php');
        JLoader::register('LogmoniterHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/logmoniter.php');
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
            'wd.state' => JText::_('JSTATUS'),
            'wd.title' => JText::_('JGLOBAL_TITLE'),
            'category_title' => JText::_('JCATEGORY'),
            'wcenter_title' => JText::_('COM_LOGMONITER_WCENTER'),
            'inset_title' => JText::_('COM_LOGMONITER_INSET'),
            'bprint_title' => JText::_('COM_LOGMONITER_BPRINT'),
            'tinterval_title' => JText::_('COM_LOGMONITER_TINTERVAL'),
            'language' => JText::_('JGRID_HEADING_LANGUAGE'),
            'wd.created' => JText::_('JDATE'),
            'wd.id' => JText::_('JGRID_HEADING_ID'),
        );
    }
}
