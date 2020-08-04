<?php
/**
 * @copyright Copyright (c)2017 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

// Base this model on the backend version.
JLoader::register('LogbookModelLog', JPATH_ADMINISTRATOR.'/components/com_logbook/models/log.php');
//JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/tables');

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
        $this->setState('log.catid', $app->input->getInt('catid'));

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
        $item = ArrayHelper::toObject($properties, 'JObject');

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
            // New item.
            $catId = (int) $this->getState('log.catid');

            if ($catId) {
                $item->params->set('access-change', $user->authorise('core.edit.state', 'com_logbook.category.'.$catId));
                $item->catid = $catId;
            } else {
                $item->params->set('access-change', $user->authorise('core.edit.state', 'com_logbook'));
            }
        }

        // Convert the metadata field to an array.
        $registry = new Registry($item->metadata);
        $item->metadata = $registry->toArray();

        if ($itemId) {
            $item->tags = new JHelperTags();
            $item->tags->getTagIds($item->id, 'com_logbook.log');
            $item->metadata['tags'] = $item->tags;
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
        // Associations are not edited in frontend ATM so we have to inherit them
        if (JLanguageAssociations::isEnabled() && !empty($data['id'])
            && $associations = JLanguageAssociations::getAssociations('com_logbook', '#__content', 'com_logbook.item', $data['id'])) {
            foreach ($associations as $tag => $associated) {
                $associations[$tag] = (int) $associated->id;
            }

            $data['associations'] = $associations;
        }

        return parent::save($data);
    }

    /**
     * Allows preprocessing of the JForm object.
     *
     * @param JForm  $form  The form object
     * @param array  $data  The data to be merged into the form object
     * @param string $group The plugin group to be executed
     *
     * @since   3.7.0
     */
    protected function preprocessForm(JForm $form, $data, $group = 'logbook')
    {
        $params = $this->getState()->get('params');

        if ($params && $params->get('enable_category') == 1) {
            $form->setFieldAttribute('catid', 'default', $params->get('catid', 1));
            $form->setFieldAttribute('catid', 'readonly', 'true');
        }

        return parent::preprocessForm($form, $data, $group);
    }
}
