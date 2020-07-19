<?php
/**
 *
 * @copyright Copyright (c)2020 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 *
 */


defined('_JEXEC') or die; // No direct access.



class LogmoniterController extends JControllerLegacy
{
  public function display($cachable = false, $urlparams = false)
  {
    require_once JPATH_COMPONENT.'/helpers/logmoniter.php';

    //Display the submenu.
    LogmoniterHelper::addSubmenu($this->input->get('view', 'watchdogs'));

    //Set the default view.
    $this->input->set('view', $this->input->get('view', 'watchdogs'));

    //Display the view.
    parent::display();
  }

}


