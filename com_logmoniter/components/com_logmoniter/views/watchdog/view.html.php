<?php
/**
 * @package Log Moniter 1.x
 * @copyright Copyright (c)
 * @license GNU General Public License version 3, or later
 *
 */

defined('_JEXEC') or die;

require_once JPATH_COMPONENT_SITE.'/helpers/route.php';

/**
 * HTML View class for the Log Moniter component
 */
class LogmoniterViewWatchdog extends JViewLegacy
{
  protected $state;
  protected $item;

  public function display($tpl = null)
  {
    // Initialise variables
    $this->state = $this->get('State');
    $this->item = $this->get('Item');

    // Check for errors.
    if(count($errors = $this->get('Errors'))) {
      JFactory::getApplication()->enqueueMessage($errors, 'error');
      return false;
    }

    // Compute the category slug.
    $this->item->catslug = $this->item->category_alias ? ($this->item->catid.':'.$this->item->category_alias) : $this->item->catid;
    //Get the possible extra class name.
    $this->pageclass_sfx = htmlspecialchars($this->item->params->get('pageclass_sfx'));
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
    //$doc->addStyleSheet(JURI::base().'components/com_logmoniter/css/logmoniter.css');
  }
}
