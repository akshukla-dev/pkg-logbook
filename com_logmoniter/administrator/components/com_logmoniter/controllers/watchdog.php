<?php
/**
 * @package LMI/LogManager/LogBook/LogMoniter
 * @copyright Copyright (c)2020 Amit Kumar Shukla. All Rights Reserved.
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
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

        if ($allow !== null) {
            return $allow;
        }

        // In the absense of better information, revert to the component permissions.
        return parent::allowAdd($data);
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

        // Zero record (id:0), return component edit permission by calling parent controller method
        if (!$recordId) {
            return parent::allowEdit($data, $key);
        }

        // Get the item.
        $item = $this->getModel()->getItem($recordId);

        // Get the item.
        $item = $this->getModel()->getItem($recordId);

        // Since there is no item, return false.
        if (empty($item)) {
            return false;
        }

        $user = JFactory::getUser();

        // Check if can edit own core.edit.own.
        $canEditOwn = $user->authorise('core.edit.own', $this->option.'.category.'.(int) $item->catid) && $item->created_by == $user->id;

        // Check the category core.edit permissions.
        return $canEditOwn || $user->authorise('core.edit', $this->option.'.category.'.(int) $item->catid);
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
