<?php
/**
 * @copyright   Copyright (C) 2020 Amit Kumar Shukla, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * View to edit a log.
 *
 * @since  1.5
 */
class LogbookViewLog extends JViewLegacy
{
    protected $state;
    protected $item;

    public function display($tpl = null)
    {
        // Initialise variables
        $this->state = $this->get('State');
        $this->item = $this->get('Item');
        $user = JFactory::getUser();

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JFactory::getApplication()->enqueueMessage($errors, 'error');

            return false;
        }

        //Get the possible extra class name.
        //$this->pageclass_sfx = htmlspecialchars($this->item->params->get('pageclass_sfx'));
        //Get the user object and the current url.
        $user = JFactory::getUser();
        $uri = JUri::getInstance();
        //Variables needed in the document edit layout.
        $this->item->user_id = $user->get('id');
        $this->item->uri = $uri;

        //Increment the hits for this document.
        $model = $this->getModel();
        $model->hit();

        $this->setDocument();

        parent::display($tpl);
    }

    protected function setDocument()
    {
        //Include css files.
        $doc = JFactory::getDocument();
        //$doc->addStyleSheet(JURI::base().'components/com_lrm/css/lrm.css');
    }
}
