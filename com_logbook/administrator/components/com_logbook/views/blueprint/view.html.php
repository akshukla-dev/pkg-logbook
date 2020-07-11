<?php
/**
 * @copyright   Copyright (C) 2020 Amit Kumar Shukla, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * View to edit a blueprint.
 *
 * @since  1.5
 */
class LogbookViewLocation extends JViewLegacy
{
    //protected $state;

    //protected $item;

    protected $form = null;

    /**
     * Display the view.
     *
     * @param string $tpl the name of the template file to parse; automatically searches through the template paths
     *
     * @return mixed a string if successful, otherwise an Error object
     */
    public function display($tpl = null)
    {
        //$this->state = $this->get('State');
        $this->item = $this->get('Item');
        $this->form = $this->get('Form');
        $this->script = $this->get('Script');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));

            return false;
        }
        
        $this->addToolbar();

        parent::display($tpl);

        //Set Document
        $this->setDocument();
    }

    /**
     * Add the page title and toolbar.
     *
     *
     * @since   1.6
     */
    protected function addToolbar()
    {
        JFactory::getApplication()->input->set('hidemainmenu', true);

        JToolbarHelper::title($isNew ? JText::_('COM_LOGBOOK_MANAGER_BLUEPRINT_NEW') : JText::_('COM_LOGBOOK_MANAGER_BLUEPRINT_EDIT'), 'blueprint locations');

        JToolbarHelper::save('blueprint.save');
        JToolbarHelper::cancel(
            'blueprint.cancel',
            $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE'
        );
    }

    /**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument() 
	{
        JHtml::_('behavior.framework');
        JHtml::_('behavior.formvalidator');

        $isNew = ($this->item->id < 1);
		$document = JFactory::getDocument();
		$document->setTitle($isNew ? JText::_('COM_LOGBOOK_BLUEPRINT_CREATING') :
                JText::_('COM_LOGBOOK_BLUEPRINT_EDITING'));
        $document->addScript(JURI::root().$this->script);
        $document->addScript(JURI::root()."administrator/components/com_logbook/views/blueprint/submitbutton.js");
        JText::script('COM_LOGBOOK_BLUEPRINT_ERROR_UNACCEPTABLE');
	}
}

