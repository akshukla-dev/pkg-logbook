<?php
/**
 *
 * @copyright Copyright (c)
 * @license GNU General Public License version 3, or later
 *
 */


defined('_JEXEC') or die; // No direct access.



class LogmoniterController extends JControllerLegacy
{
  /**
   * Constructor.
   *
   * @param   array  $config  An optional associative array of configuration settings.
   * Recognized key values include 'name', 'default_task', 'model_path', and
   * 'view_path' (this list is not meant to be comprehensive).
   *
   * @since   12.2
   */
  public function __construct($config = array())
  {
    $this->input = JFactory::getApplication()->input;

    parent::__construct($config);
  }


  public function display($cachable = false, $urlparams = false)
  {

    // Set the default view name and format from the Request.
    // Note we are using w_id to avoid collisions with the router and the return page.
    // Frontend is a bit messier than the backend.
    $id = $this->input->getInt('w_id');
    //Set the view, (categories by default).
    $vName = $this->input->getCmd('view', 'categories');
    $this->input->set('view', $vName);

    // Check for edit form.
    if($vName == 'form' && !$this->checkEditId('com_logmoniter.edit.watchdog', $id)) {
      // Somehow the person just went to the form - we don't allow that.
      JFactory::getApplication()->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
      return false;
    }

    //Make sure the parameters passed in the input by the component are safe.
    $safeurlparams = array('catid' => 'INT', 'id' => 'INT',
			    'cid' => 'ARRAY', 'limit' => 'UINT',
			    'limitstart' => 'UINT', 'return' => 'BASE64',
			    'filter' => 'STRING', 'filter-search' => 'STRING',
			    'filter-ordering' => 'STRING', 'lang' => 'CMD',
			    'Itemid' => 'INT');

    //Display the view.
    parent::display($cachable, $safeurlparams);
  }

}


