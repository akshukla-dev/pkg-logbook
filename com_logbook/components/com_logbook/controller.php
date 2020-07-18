<?php
/**
 * @copyright   Copyright (C) 2020 Amit Kumar Shukla, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Logbook Main Controller.
 *
 * @since  1.5
 */
class LogbookController extends JControllerLegacy
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

		//Document frontpage log proxying:
		if($this->input->get('view') === 'logs' && $this->input->get('layout') === 'modal') {
		JHtml::_('stylesheet', 'system/adminlist.css', array(), true);
		$config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;
		}

		parent::__construct($config);
	}
	/*
     * Method to display a view.
     *
     * @param bool  $cacheable If true, the view output will be cached
     * @param array $urlparams an array of safe url parameters and their variable types,
     *                         for valid values see {@link JFilterInput::clean()}
     *
     * @return JControllerLegacy this object to support chaining
     *
     * @since   1.5
     */
    public function display($cacheable = false, $urlparams = false)
    {
		// Set the default view name and format from the Request.
    	// Note we are using d_id to avoid collisions with the router and the return page.
    	// Frontend is a bit messier than the backend.
    	$id = $this->input->getInt('l_id');
    	//Set the view, (logs by default).
    	$vName = $this->input->getCmd('view', 'logs');
    	$this->input->set('view', $vName);

		$user = JFactory::getUser();

		//Make sure the parameters passed in the input by the component are safe.
		$safeurlparams = array(
			'wdid' => 'INT',
			'id' => 'INT',
			'cid' => 'ARRAY',
			'limit' => 'UINT',
			'limitstart' => 'UINT',
			'showall' => 'INT',
			'return' => 'BASE64',
			'filter' => 'STRING',
			'filter_order' => 'CMD',
			'filter_order_Dir' => 'CMD',
			'filter-search' => 'STRING',
			'lang' => 'CMD',
			'Itemid' => 'INT');


        // Check for edit form.
        if ($vName == 'form' && !$this->checkEditId('com_logbook.edit.log', $id)) {
            // Somehow the person just went to the form - we don't allow that.
     		JFactory::getApplication()->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
      		return false;
        }

        return parent::display($cachable, $safeurlparams);
    }
}
