<?php
/**
 * @copyright Copyright (c)2017 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */
defined('_JEXEC') or die;

// Base this model on the backend version.
require_once JPATH_ADMINISTRATOR.'/components/com_logbook/models/log.php';

//Inherit the backend version.
class LogbookModelForm extends LogbookModelLog
{
    /**
     * Model typeAlias string. Used for version history.
     *
     * @var string
     */
    public $typeAlias = 'com_logbook.log';

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     *
     * @since   1.6
     */
    protected function populateState()
    {
        $app = JFactory::getApplication();

        // Load state from the request.
        $pk = $app->input->getInt('l_id');
        $this->setState('log.id', $pk);

        //Retrieve a possible watchdog id from the url query.
        $this->setState('log.wdid', $app->input->getInt('wdid'));

        //Retrieve a possible encoded return url from the url query.
        $return = $app->input->get('return', null, 'base64');
        $this->setState('return_page', base64_decode($return));

        // Load the global parameters of the component.
        $params = $app->getParams();
        $this->setState('params', $params);

        $this->setState('layout', $app->input->getString('layout'));
    }

    /**
     * Method to get log data.
     *
     * @param int $itemId the id of the log
     *
     * @return mixed content item data object on success, false on failure
     */
    public function getItem($itemId = null)
    {
        $itemId = (int) (!empty($itemId)) ? $itemId : $this->getState('log.id');

        // Get a row instance.
        $table = $this->getTable();

        // Attempt to load the row.
        //Notes: If it's a new item, load function just return true.
        $return = $table->load($itemId);

        // Check for a table object error.
        if ($return === false && $table->getError()) {
            $this->setError($table->getError());

            return false;
        }

        //Get the fields of the table as an array
        $properties = $table->getProperties(1);
        //then convert the array into an object.
        $item = JArrayHelper::toObject($properties, 'JObject');

        // Convert params field to Registry.
        $item->params = new JRegistry();
        $item->params->loadString($item->params);

        // Compute selected asset permissions.
        $user = JFactory::getUser();
        $userId = $user->get('id');
        $asset = 'com_logbook.log.'.$item->id;

        // Check general edit permission first.
        if ($user->authorise('core.edit', $asset)) {
            $item->params->set('access-edit', true);
        }
        // Now check if edit.own is available.
        elseif (!empty($userId) && $user->authorise('core.edit.own', $asset)) {
            // Check for a valid user and that they are the owner.
            if ($userId == $item->created_by) {
                $item->params->set('access-edit', true);
            }
        }

        // Existing item
        if ($itemId) {
            // Check edit state permission.
            $item->params->set('access-change', $user->authorise('core.edit.state', $asset));

            //Set up the text to display in the editor.
            $item->remarks = $item->remarks;
        } else { // New item.
            //Check the general change access.
            $item->params->set('access-change', $user->authorise('core.edit.state', 'com_logbook'));
        }

        return $item;
    }

    /**
     * Get the return URL.
     *
     * @return string the return URL
     *
     * @since   1.6
     */
    public function getReturnPage()
    {
        return base64_encode($this->getState('return_page'));
    }

    /**
     * Method to save the form data.
     *
     * @param array $data the form data
     *
     * @return bool true on success
     *
     * @since   3.2
     */
    public function save($data)
    {
        return parent::save($data);
    }
}
