<?php
/**
 * @package LMI/LogManager/LogBook/LogMoniter
 * @copyright Copyright (c)2020 Amit Kumar Shukla. All Rights Reserved.
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */
defined('_JEXEC') or die;

/**
 * Logbook Component Controller.
 *
 * @since  1.5
 */
class LogbookController extends JControllerLegacy
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

        // Watchdog frontpage Editor pagebreak proxying:
        if ($this->input->get('view') === 'log' && $this->input->get('layout') === 'pagebreak') {
            $config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;
        }
        // Watchdog frontpage Editor log proxying:
        elseif ($this->input->get('view') === 'logs' && $this->input->get('layout') === 'modal') {
            JHtml::_('stylesheet', 'system/adminlist.css', array('version' => 'auto', 'relative' => true));
            $config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;
        }

        parent::__construct($config);
    }

    /**
     * Method to display a view.
     *
     * @param bool $cachable  if true, the view output will be cached
     * @param bool $urlparams an array of safe URL parameters and their variable types, for valid values see {@link JFilterInput::clean()}
     *
     * @return JController this object to support chaining
     *
     * @since   1.5
     */
    public function display($cachable = false, $urlparams = false)
    {
        $cachable = true;

        /**
         * Set the default view name and format from the Request.
         * Note we are using l_id to avoid collisions with the router and the return page.
         * Frontend is a bit messier than the backend.
         */
        $id = $this->input->getInt('l_id');
        $vName = $this->input->getCmd('view', 'categories');
        $this->input->set('view', $vName);

        $user = JFactory::getUser();

        if ($user->get('id')
            || ($this->input->getMethod() === 'POST'
            && (($vName === 'category' && $this->input->get('layout') !== 'blog') || $vName === 'archive'))) {
            $cachable = false;
        }

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
            'filter_order' => 'CMD',
            'filter_order_Dir' => 'CMD',
            'filter-search' => 'STRING',
            'print' => 'BOOLEAN',
            'lang' => 'CMD',
            'Itemid' => 'INT', );

        // Check for edit form.
        if ($vName === 'form' && !$this->checkEditId('com_logbook.edit.log', $id)) {
            // Somehow the person just went to the form - we don't allow that.
            return JError::raiseError(403, JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
        }

        if ($vName === 'log') {
            // Get/Create the model
            if ($model = $this->getModel($vName)) {
                $model->hit();
            }
        }

        parent::display($cachable, $safeurlparams);

        return $this;
    }
}
