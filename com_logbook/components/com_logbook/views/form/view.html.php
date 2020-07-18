<?php
/**
 * @package Logbook
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
	protected $wcenter = null;
	protected $inset = null;
	protected $bprint = null;
	protected $wdog = null;

  function display($tpl = null)
  {
    $user = JFactory::getUser();

    //Redirect unregistered users to the login page.
    if($user->guest) {
      $app = JFactory::getApplication();
      $app->redirect('index.php?option=com_users&view=login');
      return true;
    }

    // Initialise variables
    $this->form = $this->get('Form');
    $this->state = $this->get('State');
    $this->item = $this->get('Item');
    $this->return_page	= $this->get('ReturnPage');

    //Check if the user is allowed to create a new log.
    if(empty($this->item->id)) {
      $authorised = $user->authorise('core.create', 'com_logbook');
      $this->isNew = true;
    }
    else { //Check if the user is allowed to edit this log.
      $authorised = $this->item->params->get('access-edit');
    }

    if($authorised !== true) {
      JFactory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
      return false;
    }

    // Check for errors.
    if(count($errors = $this->get('Errors'))) {
      JFactory::getApplication()->enqueueMessage($errors, 'error');
      return false;
    }

    // Create a shortcut to the parameters.
    $params = &$this->state->params;
    //Get the possible extra class name.
    $this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));

    $this->params = $params;

    // Override global params with log specific params
    $this->params->merge($this->item->params);
    $this->user = $user;

    $this->setDocument();

    parent::display($tpl);
  }


  protected function setDocument()
  {
    //Include css file.
    $doc = JFactory::getDocument();
    //$doc->addStyleSheet(JURI::base().'components/com_logbook/css/logbook.css');
  }
}
