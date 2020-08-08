<?php
/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * HTML View class for the Logmoniter component.
 *
 * @since  1.5
 */
class LogbookViewLogs extends JViewLegacy
{
    /**
     * Execute and display a template script.
     *
     * @param string $tpl the name of the template file to parse; automatically searches through the template paths
     *
     * @return mixed a string if successful, otherwise an Error object
     */
    public function display($tpl = null)
    {
        //Get data from the model
        $app = JFactory::getApplication();
        $inp = $app->input;
        $params = $this->params;
        $model = $this->getModel();
        $state = $this->get('State');
        $this->state = $state;
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        $this->pagination = $this->get('Pagination');

        $this->items = $this->get('Items');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode('<br />', $errors));

            return false;
        }

        $this->_prepareDocument();

        parent::display($tpl);
    }

    /**
     * Prepares the document.
     */
    protected function _prepareDocument()
    {
        $document = JFactory::getDocument();
        $site = '/components/com_logbook/';
        $document->setTitle(JText::_('COM_LOGBOOK_SITE'));
    }
}
