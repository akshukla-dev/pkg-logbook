<?php
/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * The watchdog controller.
 *
 * @since  1.6
 */
class LogmoniterControllerWatchdog extends JControllerForm
{
    /**
     * Method override to check if you can add a new record.
     *
     * @param array $data an array of input data
     *
     * @return bool
     *
     * @since   1.6
     */
    protected function allowAdd($data = array())
    {
        $categoryId = ArrayHelper::getValue($data, 'catid', $this->input->getInt('filter_category_id'), 'int');
        $allow = null;

        if ($categoryId) {
            // If the category has been passed in the data or URL check it.
            $allow = JFactory::getUser()->authorise('core.create', 'com_logmoniter.category.'.$categoryId);
        }

        if ($allow === null) {
            // In the absense of better information, revert to the component permissions.
            return parent::allowAdd();
        }

        return $allow;
    }

    /**
     * Method override to check if you can edit an existing record.
     *
     * @param array  $data an array of input data
     * @param string $key  the name of the key for the primary key
     *
     * @return bool
     *
     * @since   1.6
     */
    protected function allowEdit($data = array(), $key = 'id')
    {
        $recordId = (int) isset($data[$key]) ? $data[$key] : 0;
        $user = JFactory::getUser();

        // Zero record (id:0), return component edit permission by calling parent controller method
        if (!$recordId) {
            return parent::allowEdit($data, $key);
        }

        // Check edit on the record asset (explicit or inherited)
        if ($user->authorise('core.edit', 'com_logmoniter.watchdog.'.$recordId)) {
            return true;
        }

        // Check edit own on the record asset (explicit or inherited)
        if ($user->authorise('core.edit.own', 'com_logmoniter.watchdog.'.$recordId)) {
            // Existing record already has an owner, get it
            $record = $this->getModel()->getItem($recordId);

            if (empty($record)) {
                return false;
            }

            // Grant if current user is owner of the record
            return $user->id == $record->created_by;
        }

        return false;
    }

    /**
     * Method to run batch operations.
     *
     * @param object $model the model
     *
     * @return bool true if successful, false otherwise and internal error is set
     *
     * @since   1.6
     */
    public function batch($model = null)
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Set the model
        /** @var LogmoniterModelWatchdog $model */
        $model = $this->getModel('Watchdog', '', array());

        // Preset the redirect
        $this->setRedirect(JRoute::_('index.php?option=com_logmoniter&view=watchdogs'.$this->getRedirectToListAppend(), false));

        return parent::batch($model);
    }

    /**
     * Function that allows child controller access to model data after the data has been saved.
     *
     * @param JModelLegacy $model     the data model object
     * @param array        $validData the validated data
     *
     * @since   1.6
     */
    protected function postSaveHook(JModelLegacy $model, $validData = array())
    {
        $task = $this->getTask();

        if ($task == 'save') {
            $this->setRedirect(JRoute::_('index.php?option=com_logmoniter&view=watchdogs', false));
        }
    }
}
