<?php
/**
 * @copyright Copyright (c) Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */

// No direct access
defined('_JEXEC') or die;

/**
 * HTML Watchdog View class for the Logmoniter component.
 *
 * @since  1.5
 */
class LogmoniterViewForm extends JViewLegacy
{
    protected $form = null;
    protected $state = null;
    protected $item = null;
    protected $return_page = null;
    protected $isNew = 0;

    public function display($tpl = null)
    {
        $user = JFactory::getUser();

        //Redirect unregistered users to the login page.
        if ($user->guest) {
            $app = JFactory::getApplication();
            $app->redirect('index.php?option=com_users&view=login');

            return true;
        }

        // Get model data.
        $this->form = $this->get('Form');
        $this->state = $this->get('State');
        $this->item = $this->get('Item');
        $this->return_page = $this->get('ReturnPage');

        //Check if the user is allowed to create a new watchdog.
        if (empty($this->item->id)) {
            $authorised = $user->authorise('core.create', 'com_logmoniter') || count($user->getAuthorisedCategories('com_logmoniter', 'core.create'));
            $this->isNew = 1;
        } else { //Check if the user is allowed to edit this log.
            $authorised = $user->authorise('core.edit', 'com_logmoniter.category.'.$this->item->catid);
        }

        if ($authorised !== true) {
            $app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
            $app->setHeader('status', 403, true);

            return false;
        }

        if (!empty($this->item)) {
            // Override the base log data with any data in the session.
            $temp = (array) JFactory::getApplication()->getUserState('com_logmoniter.edit.watchdog.data', array());

            foreach ($temp as $k => $v) {
                $this->item->$k = $v;
            }

            $this->form->bind($this->item);
        }

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseWarning(500, implode("\n", $errors));

            return false;
        }
        //create shortcut for tags
        $this->item->tags = new JHelperTags();

        if (!empty($this->item->id)) {
            $this->item->tags->getItemTags('com_logmoniter.watchdog', $this->item->id);
        }

        // Create a shortcut to the parameters.
        $params = &$this->state->params;

        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));

        $this->params = $params;
        // Override global params with document specific params
        $this->params->merge($this->item->params);
        $this->user = $user;

        // Propose current language as default when creating new watchdog
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

        if (empty($this->item->id)) {
            $head = JText::_('COM_LOGMONITER_FORM_SUBMIT_WATCHDOG');
        } else {
            $head = JText::_('COM_LOGMONITER_FORM_EDIT_WATCHDOG');
        }

        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', $head);
        }

        $title = $this->params->def('page_title', $head);

        if ($app->get('sitename_pagetitles', 0) == 1) {
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
        }
    }
}
