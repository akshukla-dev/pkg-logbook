<?php
/**
 * @package LMI/LogManager/LogBook/LogMoniter
 * @copyright Copyright (c)2020 Amit Kumar Shukla. All Rights Reserved.
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
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
        $canDo = JHelperContent::getActions('com_logmoniter', 'category', $this->item->catid);

        JToolbarHelper::title($isNew ? JText::_('COM_LOGMONITER_MANAGER_WATCHDOG_NEW') : JText::_('COM_LOGMONITER_MANAGER_WATCHDOG_EDIT'), 'stack watchdogs');

        // If not checked out, can save the item.
        if (!$checkedOut && ($canDo->get('core.edit')||(count($user->getAuthorisedCategories('com_logmoniter', 'core.create')))))
        {
            JToolbarHelper::apply('watchdog.apply');
            JToolbarHelper::save('watchdog.save');
        }
        if (!$checkedOut && (count($user->getAuthorisedCategories('com_logmoniter', 'core.create'))))
        {
            JToolbarHelper::save2new('watchdog.save2new');
        }
        // If an existing item, can save to a copy.
        if (!$isNew && (count($user->getAuthorisedCategories('com_logmoniter', 'core.create')) > 0))
        {
            JToolbarHelper::save2copy('watchdog.save2copy');
        }
        if (empty($this->item->id))
        {
            JToolbarHelper::cancel('watchdog.cancel');
        }
        else
        {
            if ($this->state->params->get('save_history', 0) && $user->authorise('core.edit'))
            {
                JToolbarHelper::versions('com_logmoniter.watchdog', $this->item->id);
            }

            JToolbarHelper::cancel('watchdog.cancel', 'JTOOLBAR_CLOSE');
        }

        JToolbarHelper::divider();
        JToolbarHelper::help('JHELP_COMPONENTS_WATCHDOGS_LINKS_EDIT');
    }
}
