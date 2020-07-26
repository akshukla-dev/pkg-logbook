<?php
/**
 * @copyright Copyright (c)
 * @license GNU General Public License version 3, or later
 */
defined('_JEXEC') or die; // No direct access.

class LogmoniterController extends JControllerLegacy
{
    /**
     * Constructor.
     *
     * @param array $config An optional associative array of configuration settings.
     *                      Recognized key values include 'name', 'default_task', 'model_path', and
     *                      'view_path' (this list is not meant to be comprehensive).
     *
     * @since   12.2
     */
    public function __construct($config = array())
    {
        $this->input = JFactory::getApplication()->input;

        // Watchdog frontpage Editor watchdog proxying:

        if ($this->input->get('view') === 'watchdogs' && $this->input->get('layout') === 'modal') {
            JHtml::_('stylesheet', 'system/adminlist.css', array('version' => 'auto', 'relative' => true));
            $config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;
        }

        parent::__construct($config);
    }

    public function display($cachable = false, $urlparams = false)
    {
        /**
         * Set the default view name and format from the Request.
         * Note we are using wd_id to avoid collisions with the router and the return page.
         * Frontend is a bit messier than the backend.
         */
        $id = $this->input->getInt('wd_id');
        //Set the view, (categories by default).
        $vName = $this->input->getCmd('view', 'categories');
        $this->input->set('view', $vName);

        //Make sure the parameters passed in the input by the component are safe.
        $safeurlparams = array(
        'catid' => 'INT',
        'id' => 'INT',
        'cid' => 'ARRAY',
        'year' => 'INT',
        'month' => 'INT',
        'limit' => 'UINT',
        'limitstart' => 'UINT',
        'showall' => 'INT',
        'return' => 'BASE64',
        'filter' => 'STRING',
        'filter-search' => 'STRING',
        'filter_order' => 'CMD',
        'filter_order_Dir' => 'CMD',
        'lang' => 'CMD',
        'print' => 'BOOLEAN',
        'Itemid' => 'INT',
    );

        // Check for edit form.
        if ($vName == 'form' && !$this->checkEditId('com_logmoniter.edit.watchdog', $id)) {
            // Somehow the person just went to the form - we don't allow that.
            return JError::raiseError(403, JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
        }

        if ($vName === 'watchdog') {
            // Get/Create the model
            if ($model = $this->getModel($vName)) {
                $model->hit();
            }
        }

        //Display the view.
        parent::display($cachable, $safeurlparams);

        return $this;
    }
}
