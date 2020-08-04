<?php
/**
 * @copyright Copyright (c)2017 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */
defined('_JEXEC') or die; //No direct access to this file.

use Joomla\Registry\Registry;

JLoader::register('LogbookHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/logbook.php');

class LogbookModelLog extends JModelAdmin
{
    //prefix used with the controller messages.
    protected $text_prefix = 'COM_LOGBOOK';
    /**
     * The type alias for this logbook type (for example, 'com_logbook.log').
     *
     * @var string
     *
     * @since  3.2
     */
    public $typeAlias = 'com_logbook.log';

    /**
     * The context used for the associations table.
     *
     * @var string
     *
     * @since  3.4.4
     */
    protected $associationsContext = 'com_logbook.item';

    /**
     * Method to test whether a record can be deleted.
     *
     * @param object $record a record object
     *
     * @return bool True if allowed to delete the record. Defaults to the permission set in the component.
     *
     * @since   1.6
     */
    protected function canDelete($record)
    {
        if (!empty($record->id)) {
            if ($record->state != -2) {
                return;
            }

            if ($record->catid) {
                return JFactory::getUser()->authorise('core.delete', 'com_logbook.category.'.(int) $record->catid);
            }

            return parent::canDelete($record);
        }
    }

    /**
     * Method to test whether a record can have its state edited.
     *
     * @param object $record a record object
     *
     * @return bool True if allowed to change the state of the record. Defaults to the permission set in the component.
     *
     * @since   1.6
     */
    protected function canEditState($record)
    {
        $user = JFactory::getUser();

        // Check for existing log.
        if (!empty($record->id)) {
            return $user->authorise('core.edit.state', 'com_logbook.log.'.(int) $record->id);
        }

        // New log, so check against the category.
        if (!empty($record->catid)) {
            return $user->authorise('core.edit.state', 'com_logbook.category.'.(int) $record->catid);
        }

        // Default to component settings if neither log nor category known.
        return parent::canEditState($record);
    }

    /**
     * Prepare and sanitise the table data prior to saving.
     *
     * @param JTable $table a JTable object
     *
     * @since   1.6
     */
    protected function prepareTable($table)
    {
        $date = JFactory::getDate();
        $user = JFactory::getUser();

        $table->title = htmlspecialchars_decode($table->title, ENT_QUOTES);
        $table->alias = JApplicationHelper::stringURLSafe($table->alias);

        if (empty($table->alias)) {
            $table->alias = JApplicationHelper::stringURLSafe($table->title);
        }

        if (empty($table->id)) {
            // Set the values

            // Set ordering to the last item if not set
            if (empty($table->ordering)) {
                $db = $this->getDbo();
                $query = $db->getQuery(true)
                    ->select('MAX(ordering)')
                    ->from($db->quoteName('#__logbook_logs'));

                $db->setQuery($query);
                $max = $db->loadResult();

                $table->ordering = $max + 1;
            } else {
                // Set the values
                $table->modified = $date->toSql();
                $table->modified_by = $user->id;
            }
        }

        // Increment the log version number.
        ++$table->version;
    }

    /**
     * Returns a Table object, always creating it.
     * Table can be defined/overrided in the file: tables/mycomponent.php.
     *
     * @param string $type   The table type to instantiate
     * @param string $prefix A prefix for the table class name. Optional.
     * @param array  $config Configuration array for model. Optional.
     *
     * @return JTable A database object
     */
    public function getTable($type = 'Log', $prefix = 'LogbookTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get a single record.
     *
     * @param int $pk the id of the primary key
     *
     * @return mixed object on success, false on failure
     */
    public function getItem($pk = null)
    {
        if ($item = parent::getItem($pk)) {
            // Convert the params field to an array.
            $registry = new Registry($item->params);
            $item->params = $registry->toArray();

            // Convert the metadata field to an array.
            $registry = new Registry($item->metadata);
            $item->metadata = $registry->toArray();

            // Load associated web links items
            $assoc = JLanguageAssociations::isEnabled();

            if ($assoc) {
                $item->associations = array();

                if ($item->id != null) {
                    $associations = JLanguageAssociations::getAssociations('com_logbook', '#__logbook_logs', 'com_logbook.item', $item->id);

                    foreach ($associations as $tag => $association) {
                        $item->associations[$tag] = $association->id;
                    }
                }
            }

            if (!empty($item->id)) {
                $item->tags = new JHelperTags();
                $item->tags->getTagIds($item->id, 'com_logbook.log');
                $item->metadata['tags'] = $item->tags;
            }
        }

        return $item;
    }

    /**
     * Method to get the record form.
     *
     * @param array $data     data for the form
     * @param bool  $loadData true if the form is to load its own data (default case), false if not
     *
     * @return JForm|bool A JForm object on success, false on failure
     *
     * @since   1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_logbook.log', 'log', array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form)) {
            return false;
        }

        /*
         * The front end calls this model and uses l_id to avoid id clashes so we need to check for that first.
         * The back end uses id so we use that the rest of the time and set it to 0 by default.
         */
        $jinput = JFactory::getApplication()->input;
        $id = $jinput->get('l_id', $jinput->get('id', 0));

        // Determine correct permissions to check.
        if ($this->getState('log.id')) {
            $id = $this->getState('log.id');

            // Existing record. Can only edit in selected categories.
            $form->setFieldAttribute('catid', 'action', 'core.edit');

            // Existing record. Can only edit own logs in selected categories.
            $form->setFieldAttribute('catid', 'action', 'core.edit.own');
        } else {
            // New record. Can only create in selected categories.
            $form->setFieldAttribute('catid', 'action', 'core.create');
        }

        $user = JFactory::getUser();

        // Check for existing log.
        // Modify the form based on Edit State access controls.
        if ($id != 0 && (!$user->authorise('core.edit.state', 'com_logbook.log.'.(int) $id))
            || ($id == 0 && !$user->authorise('core.edit.state', 'com_logbook'))) {
            // Disable fields for display.
            $form->setFieldAttribute('ordering', 'disabled', 'true');
            $form->setFieldAttribute('state', 'disabled', 'true');
            $form->setFieldAttribute('publish_up', 'disabled', 'true');
            $form->setFieldAttribute('publish_down', 'disabled', 'true');

            // Disable fields while saving.
            // The controller has already verified this is an log you can edit.
            $form->setFieldAttribute('ordering', 'filter', 'unset');
            $form->setFieldAttribute('state', 'filter', 'unset');
            $form->setFieldAttribute('publish_up', 'filter', 'unset');
            $form->setFieldAttribute('publish_down', 'filter', 'unset');
        }

        // Prevent messing with log language and category when editing existing log with associations
        $app = JFactory::getApplication();
        $assoc = JLanguageAssociations::isEnabled();

        // Check if log is associated
        if ($this->getState('log.id') && $app->isClient('site') && $assoc) {
            $associations = JLanguageAssociations::getAssociations('com_logbook', '#__logbook_logs', 'com_logbook.item', $id);

            // Make fields read only
            if (!empty($associations)) {
                $form->setFieldAttribute('language', 'readonly', 'true');
                $form->setFieldAttribute('catid', 'readonly', 'true');
                $form->setFieldAttribute('language', 'filter', 'unset');
                $form->setFieldAttribute('catid', 'filter', 'unset');
            }
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return mixed the data for the form
     *
     * @since   1.6
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $app = JFactory::getApplication();
        $data = $app->getUserState('com_logbook.edit.log.data', array());

        if (empty($data)) {
            $data = $this->getItem();

            // Pre-select some filters (Status, Category, Language, Access) in edit form if those have been selected in Log Manager: Logs
            if ($this->getState('log.id') == 0) {
                $filters = (array) $app->getUserState('com_logbook.logs.filter');
                $data->set(
                    'state',
                    $app->input->getInt(
                        'state',
                        ((isset($filters['published']) && $filters['published'] !== '') ? $filters['published'] : null)
                    )
                );
                $data->set('catid', $app->input->getInt('catid', (!empty($filters['category_id']) ? $filters['category_id'] : null)));
                //TODO:Watchdog Filter for empty form
                $data->set('wdid', $app->input->getInt('wdid', (!empty($filters['watchdog_id']) ? $filters['watchdog_id'] : null)));
                $data->set('language', $app->input->getString('language', (!empty($filters['language']) ? $filters['language'] : null)));
                $data->set('access',
                    $app->input->getInt('access', (!empty($filters['access']) ? $filters['access'] : JFactory::getConfig()->get('access')))
                );
            }
        }

        // If there are params fieldsets in the form it will fail with a registry object
        if (isset($data->params) && $data->params instanceof Registry) {
            $data->params = $data->params->toArray();
        }

        $this->preprocessData('com_logbook.log', $data);

        return $data;
    }

    /**
     * Method to validate the form data.
     *
     * @param JForm  $form  the form to validate against
     * @param array  $data  the data to validate
     * @param string $group the name of the field group to validate
     *
     * @return array|bool array of filtered data if valid, false otherwise
     *
     * @see     JFormRule
     * @see     JFilterInput
     * @since   3.7.0
     */
    public function validate($form, $data, $group = null)
    {
        // Don't allow to change the users if not allowed to access com_users.
        if (JFactory::getApplication()->isClient('administrator') && !JFactory::getUser()->authorise('core.manage', 'com_users')) {
            if (isset($data['created_by'])) {
                unset($data['created_by']);
            }

            if (isset($data['modified_by'])) {
                unset($data['modified_by']);
            }
        }

        return parent::validate($form, $data, $group);
    }

    /**
     * Method to save the form data.
     *
     * @param array $data the form data
     *
     * @return bool true on success
     *
     * @since   1.6
     */
    public function save($data)
    {
        $input = JFactory::getApplication()->input;
        $filter = JFilterInput::getInstance();

        if (isset($data['created_by_alias'])) {
            $data['created_by_alias'] = $filter->clean($data['created_by_alias'], 'TRIM');
        }

        JLoader::register('CategoriesHelper', JPATH_ADMINISTRATOR.'/components/com_categories/helpers/categories.php');

        // Cast catid to integer for comparison
        $catid = (int) $data['catid'];

        // Check if New Category exists
        if ($catid > 0) {
            $catid = CategoriesHelper::validateCategoryId($data['catid'], 'com_logbook');
        }

        // Save New Categoryg
        if ($catid == 0 && $this->canCreateCategory()) {
            $table = array();
            $table['title'] = $data['catid'];
            $table['parent_id'] = 1;
            $table['extension'] = 'com_logbook';
            $table['language'] = $data['language'];
            $table['published'] = 1;

            // Create new category and get catid back
            $data['catid'] = CategoriesHelper::createCategory($table);
        }

        // Alter the title for save as copy
        if ($input->get('task') == 'save2copy') {
            $origTable = clone $this->getTable();
            $origTable->load($input->getInt('id'));

            if ($data['title'] == $origTable->title) {
                list($title, $alias) = $this->generateNewTitle($data['catid'], $data['alias'], $data['title']);
                $data['title'] = $title;
                $data['alias'] = $alias;
            } else {
                if ($data['alias'] == $origTable->alias) {
                    $data['alias'] = '';
                }
            }

            $data['state'] = 0;
        }

        // Automatic handling of alias for empty fields
        if (in_array($input->get('task'), array('apply', 'save', 'save2new')) && (!isset($data['id']) || (int) $data['id'] == 0)) {
            if ($data['alias'] == null) {
                if (JFactory::getConfig()->get('unicodeslugs') == 1) {
                    $data['alias'] = JFilterOutput::stringURLUnicodeSlug($data['title']);
                } else {
                    $data['alias'] = JFilterOutput::stringURLSafe($data['title']);
                }

                $table = JTable::getInstance('Log', 'LogbookTable');

                if ($table->load(array('alias' => $data['alias'], 'catid' => $data['catid']))) {
                    $msg = JText::_('COM_LOGBOOK_SAVE_WARNING');
                }

                list($title, $alias) = $this->generateNewTitle($data['catid'], $data['alias'], $data['title']);
                $data['alias'] = $alias;

                if (isset($msg)) {
                    JFactory::getApplication()->enqueueMessage($msg, 'warning');
                }
            }
        }

        return parent::save($data);
    }

    /**
     * A protected method to get a set of ordering conditions.
     *
     * @param	object	a record object
     *
     * @return array an array of conditions to add to add to ordering queries
     *
     * @since	1.6
     */
    protected function getReorderConditions($table)
    {
        return array('catid = '.(int) $table->catid);
    }

    /**
     * Allows preprocessing of the JForm object.
     *
     * @param JForm  $form  The form object
     * @param array  $data  The data to be merged into the form object
     * @param string $group The plugin group to be executed
     *
     * @since   3.0
     */
    protected function preprocessForm(JForm $form, $data, $group = 'content')
    {
        if ($this->canCreateCategory()) {
            $form->setFieldAttribute('catid', 'allowAdd', 'true');
        }

        // Association logbook items
        if (JLanguageAssociations::isEnabled()) {
            $languages = JLanguageHelper::getLogbookLanguages(false, true, null, 'ordering', 'asc');

            if (count($languages) > 1) {
                $addform = new SimpleXMLElement('<form />');
                $fields = $addform->addChild('fields');
                $fields->addAttribute('name', 'associations');
                $fieldset = $fields->addChild('fieldset');
                $fieldset->addAttribute('name', 'item_associations');

                foreach ($languages as $language) {
                    $field = $fieldset->addChild('field');
                    $field->addAttribute('name', $language->lang_code);
                    $field->addAttribute('type', 'modal_log');
                    $field->addAttribute('language', $language->lang_code);
                    $field->addAttribute('label', $language->title);
                    $field->addAttribute('translate_label', 'false');
                    $field->addAttribute('select', 'true');
                    $field->addAttribute('new', 'true');
                    $field->addAttribute('edit', 'true');
                    $field->addAttribute('clear', 'true');
                }

                $form->load($addform, false);
            }
        }

        parent::preprocessForm($form, $data, $group);
    }

    /**
     * Void hit function for pagebreak when editing content from frontend.
     *
     *
     * @since   3.6.0
     */
    public function hit()
    {
        return;
    }

    /**
     * Is the user allowed to create an on the fly category?
     *
     * @return bool
     *
     * @since   3.6.1
     */
    private function canCreateCategory()
    {
        return JFactory::getUser()->authorise('core.create', 'com_logbook');
    }
}
