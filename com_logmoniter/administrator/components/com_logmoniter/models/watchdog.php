<?php
/**
 * @package LMI/LogManager/LogBook/LogMoniter
 * @copyright Copyright (c)2020 Amit Kumar Shukla. All Rights Reserved.
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

JLoader::register('LogmoniterHelper', JPATH_ADMINISTRATOR.'/components/com_logmoniter/helpers/logmoniter.php');

/**
 * Item Model for an Watchdog.
 *
 * @since  1.6
 */
class LogmoniterModelWatchdog extends JModelAdmin
{
    /**
     * The prefix to use with controller messages.
     *
     * @var string
     *
     * @since  1.6
     */
    protected $text_prefix = 'COM_LOGMONITER';

    /**
     * The type alias for this content type (for example, 'com_logmoniter.watchdog').
     *
     * @var string
     *
     * @since  3.2
     */
    public $typeAlias = 'com_logmoniter.watchdog';

    /**
     * The context used for the associations table.
     *
     * @var string
     *
     * @since  3.4.4
     */
    protected $associationsContext = 'com_logmoniter.item';


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
                return JFactory::getUser()->authorise('core.delete', 'com_logmoniter.category.'.(int) $record->catid);
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
        if (!empty($record->catid)) {
            return JFactory::getUser()->authorise('core.edit.state', 'com_logmoniter.category.'.(int) $record->catid);
        }

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
                    ->from($db->quoteName('#__logbook_watchdogs'));

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
     *
     * @param string $type   The table type to instantiate
     * @param string $prefix A prefix for the table class name. Optional.
     * @param array  $config Configuration array for model. Optional.
     *
     * @return JTable A database object
     */
    public function getTable($type = 'Watchdog', $prefix = 'LogmoniterTable', $config = array())
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

            // Convert the metadata field to an array.
            $registry = new Registry($item->metadata);
            $item->metadata = $registry->toArray();

            // Load associated content items
            $assoc = JLanguageAssociations::isEnabled();

            if ($assoc) {
                $item->associations = array();

                if ($item->id != null) {
                    $associations = JLanguageAssociations::getAssociations('com_logmoniter', '#__logbook_watchdogs', 'com_logmoniter.item', $item->id);

                    foreach ($associations as $tag => $association) {
                        $item->associations[$tag] = $association->id;
                    }
                }
            }

            if (!empty($item->id)) {
                $item->tags = new JHelperTags();
                $item->tags->getTagIds($item->id, 'com_logmoniter.watchdog');
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
        $form = $this->loadForm('com_logmoniter.watchdog', 'watchdog', array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form)) {
            return false;
        }

        // Determine correct permissions to check.
        if ($this->getState('watchdog.id')) {
            // Existing record. Can only edit in selected categories.
            $form->setFieldAttribute('catid', 'action', 'core.edit');
        } else {
            // New record. Can only create in selected categories.
            $form->setFieldAttribute('catid', 'action', 'core.create');
        }

        // Modify the form based on access controls.
        if (!$this->canEditState((object) $data)) {
            // Disable fields for display.
            $form->setFieldAttribute('ordering', 'disabled', 'true');
            $form->setFieldAttribute('state', 'disabled', 'true');
            $form->setFieldAttribute('publish_up', 'disabled', 'true');
            $form->setFieldAttribute('publish_down', 'disabled', 'true');

            // Disable fields while saving.
            // The controller has already verified this is a record you can edit.
            $form->setFieldAttribute('ordering', 'filter', 'unset');
            $form->setFieldAttribute('state', 'filter', 'unset');
            $form->setFieldAttribute('publish_up', 'filter', 'unset');
            $form->setFieldAttribute('publish_down', 'filter', 'unset');
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
        $data = JFactory::getApplication()->getUserState('com_logmoniter.edit.watchdog.data', array());

        if (empty($data)) {
            $data = $this->getItem();

            // Prime some default values.
            if ($this->getState('watchdog.id') == 0) {
                $app = JFactory::getApplication();
                $data->set('catid', $app->input->get('catid', $app->getUserState('com_logmoniter.watchdogs.filter.category_id'), 'int'));
            }
        }

        $this->preprocessData('com_logmoniter.watchdog', $data);

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
    /*public function validate($form, $data, $group = null)
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
		$app = JFactory::getApplication();

		JLoader::register('CategoriesHelper', JPATH_ADMINISTRATOR . '/components/com_categories/helpers/categories.php');

        // Cast catid to integer for comparison
        $catid = (int) $data['catid'];

        // Check if New Category exists
        if ($catid > 0) {
            $catid = CategoriesHelper::validateCategoryId($data['catid'], 'com_logmoniter');
        }

        // Save New Categoryg
        if ($catid == 0 && $this->canCreateCategory()) {
            $table = array();
            $table['title'] = $data['catid'];
            $table['parent_id'] = 1;
            $table['extension'] = 'com_logmoniter';
            $table['language'] = $data['language'];
            $table['published'] = 1;

            // Create new category and get catid back
            $data['catid'] = CategoriesHelper::createCategory($table);
        }

        // Alter the title for save as copy
        if ($app->input->get('task') == 'save2copy') {
            list($name, $alias) = $this->generateNewTitle($data['catid'], $data['alias'], $data['title']);
			$data['title']	= $name;
			$data['alias']	= $alias;
			$data['state']	= 0;
        }

        return parent::save($data);
	}

	/**
	 * Method to change the title & alias.
	 *
	 * @param   integer  $category_id  The id of the parent.
	 * @param   string   $alias        The alias.
	 * @param   string   $name         The title.
	 *
	 * @return  array  Contains the modified title and alias.
	 *
	 * @since   3.1
	 */
	protected function generateNewTitle($category_id, $alias, $name)
	{
		// Alter the title & alias
		$table = $this->getTable();

		while ($table->load(array('alias' => $alias, 'catid' => $category_id)))
		{
			if ($name == $table->title)
			{
				$name = JString::increment($name);
			}

			$alias = JString::increment($alias, 'dash');
		}

		return array($name, $alias);
	}

    /**
     * A protected method to get a set of ordering conditions.
     *
     * @param object $table a record object
     *
     * @return array an array of conditions to add to add to ordering queries
     *
     * @since   1.6
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

        // Association content items
        if (JLanguageAssociations::isEnabled()) {
            $languages = JLanguageHelper::getLogmoniterLanguages(false, true, null, 'ordering', 'asc');

            if (count($languages) > 1) {
                $addform = new SimpleXMLElement('<form />');
                $fields = $addform->addChild('fields');
                $fields->addAttribute('name', 'associations');
                $fieldset = $fields->addChild('fieldset');
                $fieldset->addAttribute('name', 'item_associations');

                foreach ($languages as $language) {
                    $field = $fieldset->addChild('field');
                    $field->addAttribute('name', $language->lang_code);
                    $field->addAttribute('type', 'modal_watchdog');
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
     * Is the user allowed to create an on the fly category?
     *
     * @return bool
     *
     * @since   3.6.1
     */
    private function canCreateCategory()
    {
        return JFactory::getUser()->authorise('core.create', 'com_logmoniter');
    }
}
