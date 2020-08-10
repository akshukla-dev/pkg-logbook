<?php
/**
 * @copyright Copyright (c)2017 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */

// No direct access
defined('_JEXEC') or die;

/**
 * HTML Watchdog View class for the Content component.
 *
 * @since  1.5
 */
class LogbookViewForm extends JViewLegacy
{
    protected $form = null;
    protected $state = null;
    protected $item = null;
    protected $return_page = null;
    protected $isNew = 0;

    /**
     * Execute and display a template script.
     *
     * @param string $tpl the name of the template file to parse; automatically searches through the template paths
     *
     * @return mixed a string if successful, otherwise an Error object
     */
    public function display($tpl = null)
    {
        $user = JFactory::getUser();
        $app = JFactory::getApplication();

        //Redirect unregistered users to the login page.
        if ($user->guest) {
            $app->redirect('index.php?option=com_users&view=login');

            return true;
        }

        // Initialise variables
        $this->form = $this->get('Form');
        $this->state = $this->get('State');
        $this->item = $this->get('Item');
        $this->return_page = $this->get('ReturnPage');

        //Check if the user is allowed to create a new log.
        if (empty($this->item->id)) {
            $authorised = $user->authorise('core.create', 'com_logbook');
            $this->isNew = 1;
        } else { //Check if the user is allowed to edit this log.
            $authorised = $this->item->params->get('access-edit');
        }

        if ($authorised !== true) {
            $app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
            $app->setHeader('status', 403, true);

            return false;
        }

        $this->item->tags = new JHelperTags();

        if (!empty($this->item->id)) {
            $this->item->tags->getItemTags('com_logbook.log', $this->item->id);
        }

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseWarning(500, implode("\n", $errors));

            return false;
        }

        // Create a shortcut to the parameters.
        $params = &$this->state->params;

        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));

        $this->params = $params;

        // Override global params with document specific params
        $this->params->merge($this->item->params);
        $this->user = $user;

        // Propose current language as default when creating new log
        if (empty($this->item->id) && JLanguageMultilang::isEnabled()) {
            $lang = JFactory::getLanguage()->getTag();
            $this->form->setFieldAttribute('language', 'default', $lang);
        }

        $this->_prepareDocument();
        parent::display($tpl);
    }

    /**
     * Prepares the document.
     */
    protected function _prepareDocument()
    {
        $app = JFactory::getApplication();
        $menus = $app->getMenu();
        $title = null;

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = $menus->getActive();

        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', JText::_('COM_LOGBOOK_FORM_EDIT_LOG'));
        }

        $title = $this->params->def('page_title', JText::_('COM_LOGBOOK_FORM_EDIT_LOG'));

        if ($app->get('sitename_pagetitles', 0) == 1) {
            $title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
        } elseif ($app->get('sitename_pagetitles', 0) == 2) {
            $title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
        }

        $this->document->setTitle($title);

        $pathway = $app->getPathWay();
        $pathway->addItem($title, '');

        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->params->get('robots')) {
            $this->document->setMetadata('robots', $this->params->get('robots'));
        }
    }
}
