<?php
/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * View to edit an watchdog.
 *
 * @since  1.6
 */
class LogmoniterViewWatchdog extends JViewLegacy
{
    /**
     * The JForm object.
     *
     * @var JForm
     */
    protected $form;

    /**
     * The active item.
     *
     * @var object
     */
    protected $item;

    /**
     * The model state.
     *
     * @var object
     */
    protected $state;

    /**
     * Execute and display a template script.
     *
     * @param string $tpl the name of the template file to parse; automatically searches through the template paths
     *
     * @return mixed a string if successful, otherwise an Error object
     *
     * @since   1.6
     */
    public function display($tpl = null)
    {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->state = $this->get('State');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors), 500);
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

        return parent::display($tpl);
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
        $userId = $user->id;
        $isNew = ($this->item->id == 0);
        $checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);

        // Since we don't track these assets at the item level, use the category id.
        $canDo = JHelperContent::getActions('com_logmoniter', 'category', $this->item->catid);

        JToolbarHelper::title(
            JText::_('COM_LOGMONITER_PAGE_'.($checkedOut ? 'VIEW_WATCHDOG' : ($isNew ? 'ADD_WATCHDOG' : 'EDIT_WATCHDOG'))),
            'pencil-2 watchdog-add'
        );

        // For new records, check the create permission.
        if ($isNew && (count($user->getAuthorisedCategories('com_logmoniter', 'core.create')) > 0)) {
            JToolbarHelper::apply('watchdog.apply');
            JToolbarHelper::save('watchdog.save');
            JToolbarHelper::save2new('watchdog.save2new');
            JToolbarHelper::cancel('watchdog.cancel');
        } else {
            // Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
            $itemEditable = $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId);

            // Can't save the record if it's checked out and editable
            if (!$checkedOut && $itemEditable) {
                JToolbarHelper::apply('watchdog.apply');
                JToolbarHelper::save('watchdog.save');

                // We can save this record, but check the create permission to see if we can return to make a new one.
                if ($canDo->get('core.create')) {
                    JToolbarHelper::save2new('watchdog.save2new');
                }
            }

            // If checked out, we can still save
            if ($canDo->get('core.create')) {
                JToolbarHelper::save2copy('watchdog.save2copy');
            }

            if (JComponentHelper::isEnabled('com_contenthistory') && $this->state->params->get('save_history', 0) && $itemEditable) {
                JToolbarHelper::versions('com_logmoniter.watchdog', $this->item->id);
            }

            JToolbarHelper::cancel('watchdog.cancel', 'JTOOLBAR_CLOSE');
        }

        JToolbarHelper::divider();
        JToolbarHelper::help('JHELP_LOGMONITER_WATCHDOG_MANAGER_EDIT');
    }
}
