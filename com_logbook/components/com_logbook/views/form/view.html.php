<?php
/**
 * @copyright Copyright (c)2017 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */

// No direct access
defined('_JEXEC') or die;

class LogbookViewForm extends JViewLegacy
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
            JFactory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');

            return false;
        }

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JFactory::getApplication()->enqueueMessage($errors, 'error');

            return false;
        }
        // Create a shortcut to the parameters.
        $params = &$this->state->params;
        $this->params = $params;
        // Override global params with document specific params
        $this->params->merge($this->item->params);
        $this->user = $user;

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

    /*protected function setDocument()
    {
        //Include css file.
        $doc = JFactory::getDocument();
        $doc->addStyleSheet(JURI::base().'components/com_logbook/css/logbook.css');
    }*/
}
