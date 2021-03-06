<?php
/**
 * @package LMI/LogManager/LogBook/LogMoniter
 * @copyright Copyright (c)2020 Amit Kumar Shukla. All Rights Reserved.
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */
defined('_JEXEC') or die;

require_once JPATH_COMPONENT.'/helpers/logbook.php';

/**
 * View to edit a log.
 *
 * @since  1.5
 */
class LogbookViewLog extends JViewLegacy
{
    protected $state;

    protected $item;

    protected $form;

    /**
     * Display the view.
     *
     * @param string $tpl the name of the template file to parse; automatically searches through the template paths
     *
     * @return mixed a string if successful, otherwise an Error object
     */
    public function display($tpl = null)
    {
        $this->state = $this->get('State');
        $this->item = $this->get('Item');
        $this->form = $this->get('Form');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));

            return false;
        }

        // If we are forcing a language in modal (used for associations).
        if ($this->getLayout() === 'modal' && $forcedLanguage = JFactory::getApplication()->input->get('forcedLanguage', '', 'cmd')) {
            // Set the language field to the forcedLanguage and disable changing it.
            $this->form->setValue('language', null, $forcedLanguage);
            $this->form->setFieldAttribute('language', 'readonly', 'true');

            // Only allow to select categories with All language or with the forced language.
            $this->form->setFieldAttribute('catid', 'language', '*,'.$forcedLanguage);

            // Only allow to select tags with All language or with the forced language.
            $this->form->setFieldAttribute('tags', 'language', '*,'.$forcedLanguage);
        }

        $this->addToolbar();

        parent::display($tpl);
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

        $user = JFactory::getUser();
        $isNew = ($this->item->id == 0);
        $checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));

        // Since we don't track these assets at the item level, use the category id.
        $canDo = JHelperContent::getActions('com_logbook', 'category', $this->item->catid);

        JToolbarHelper::title($isNew ? JText::_('COM_LOGBOOK_MANAGER_LOG_NEW') : JText::_('COM_LOGBOOK_MANAGER_LOG_EDIT'), 'stack logs');

        // If not checked out, can save the item.
        if (!$checkedOut && ($canDo->get('core.edit') || (count($user->getAuthorisedCategories('com_logbook', 'core.create'))))) {
            JToolbarHelper::apply('log.apply');
            JToolbarHelper::save('log.save');
        }
        if (!$checkedOut && (count($user->getAuthorisedCategories('com_logbook', 'core.create')))) {
            JToolbarHelper::save2new('log.save2new');
        }
        // If an existing item, can save to a copy.
        if (!$isNew && (count($user->getAuthorisedCategories('com_logbook', 'core.create')) > 0)) {
            JToolbarHelper::save2copy('log.save2copy');
        }
        if (empty($this->item->id)) {
            JToolbarHelper::cancel('log.cancel');
        } else {
            if ($this->state->params->get('save_history', 0) && $user->authorise('core.edit')) {
                JToolbarHelper::versions('com_logbook.log', $this->item->id);
            }

            JToolbarHelper::cancel('log.cancel', 'JTOOLBAR_CLOSE');
        }

        JToolbarHelper::divider();
        JToolbarHelper::help('JHELP_COMPONENTS_LOGS_LINKS_EDIT');
    }
}
