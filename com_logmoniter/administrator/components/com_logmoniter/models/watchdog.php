<?php
/**
 *
 * @copyright Copyright (c)2020 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 *
 */


defined('_JEXEC') or die; //No direct access to this file.

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

JLoader::register('LogmoniterHelper', JPATH_COMPONENT_ADMINISTRATOR . '/helpers/logmoniter.php');


class LogmoniterModelWatchdog extends JModelAdmin
{
  /**
   * The prefix to use with controller messages.
   *
   * @var    string
   * @since  1.6
   */
  protected $text_prefix = 'COM_LOGMONITER';

  /**
   * The type alias for this logmoniter type (for example, 'com_logmoniter.watchdog').
   *
   * @var    string
   * @since  3.2
   */
  public $typeAlias = 'com_logmoniter.watchdog';

  /**
   * The context used for the associations table
   *
   * @var    string
   * @since  3.4.4
   */
  protected $associationsContext = 'com_logmoniter.item';

  /**
   * Batch copy items to a new category or current.
   *
   * @param   integer  $value     The new category.
   * @param   array    $pks       An array of row IDs.
   * @param   array    $contexts  An array of item contexts.
   *
   * @return  mixed  An array of new IDs on success, boolean false on failure.
   *
   * @since   11.1
   */
  protected function batchCopy($value, $pks, $contexts)
  {
    $categoryId = (int) $value;

    $newIds = array();

    if (!$this->checkCategoryId($categoryId))
    {
      return false;
    }

    // Parent exists so we let's proceed
    while (!empty($pks))
    {
      // Pop the first ID off the stack
      $pk = array_shift($pks);

      $this->table->reset();

      // Check that the row actually exists
      if (!$this->table->load($pk))
      {
        if ($error = $this->table->getError())
        {
          // Fatal error
          $this->setError($error);

          return false;
        }
        else
        {
          // Not fatal error
          $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
          continue;
        }
      }

      // Alter the title & alias
      $data = $this->generateNewTitle($categoryId, $this->table->alias, $this->table->title);
      $this->table->title = $data['0'];
      $this->table->alias = $data['1'];

      // Reset the ID because we are making a copy
      $this->table->id = 0;

      // Reset hits because we are making a copy
      $this->table->hits = 0;

      // Unpublish because we are making a copy
      $this->table->state = 0;

      // New category ID
      $this->table->catid = $categoryId;


      // Check the row.
      if (!$this->table->check())
      {
        $this->setError($this->table->getError());

        return false;
      }

      $this->createTagsHelper($this->tagsObserver, $this->type, $pk, $this->typeAlias, $this->table);

      // Store the row.
      if (!$this->table->store())
      {
        $this->setError($this->table->getError());

        return false;
      }

      // Get the new item ID
      $newId = $this->table->get('id');

      // Add the new ID to the array
      $newIds[$pk] = $newId;

    }

    // Clean the cache
    $this->cleanCache();

    return $newIds;
  }

  /**
   * Method to test whether a record can be deleted.
   *
   * @param   object  $record  A record object.
   *
   * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
   *
   * @since   1.6
   */
  protected function canDelete($record)
  {
    if (!empty($record->id))
    {
      if ($record->state != -2)
      {
        return;
      }

      if ($record->catid){
            return JFactory::getUser()->authorise('core.delete', 'com_logmoniter.category.' . (int) $record->catid);
        }
      return parent::canDelete($record);
    }
  }

  /**
   * Method to test whether a record can have its state edited.
   *
   * @param   object  $record  A record object.
   *
   * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
   *
   * @since   1.6
   */
    protected function canEditState($record)
    {
        $user = JFactory::getUser();

        // Check for existing watchdog.
        if (!empty($record->id))
        {
        return $user->authorise('core.edit.state', 'com_logmoniter.watchdog.' . (int) $record->id);
        }

        // New watchdog, so check against the category.
        if (!empty($record->catid))
        {
        return $user->authorise('core.edit.state', 'com_logmoniter.category.' . (int) $record->catid);
        }

        // Default to component settings if neither watchdog nor category known.
        return parent::canEditState($record);
    }

    /**
     * Prepare and sanitise the table data prior to saving.
     *
     * @param   JTable  $table  A JTable object.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function prepareTable($table)
    {
        // Set the publish date to now
        if ($table->state == 1 && (int) $table->publish_up == 0)
        {
            $table->publish_up = JFactory::getDate()->toSql();
        }

        if ($table->state == 1 && intval($table->publish_down) == 0)
        {
            $table->publish_down = $this->getDbo()->getNullDate();
        }

        // Increment the content version number.
        //$table->version++;

        // Reorder the watchdogs within the category so the new watchdog is first
        if (empty($table->id))
        {
            $table->reorder('catid = ' . (int) $table->catid . ' AND state >= 0');
        }
    }

    /**
     * Returns a Table object, always creating it.
     *
     * @param   string  $type    The table type to instantiate
     * @param   string  $prefix  A prefix for the table class name. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  JTable    A database object
     */
    public function getTable($type = 'Watchdog', $prefix = 'LogmoniterTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

  /**
     * Method to get a single record.
     *
     * @param   integer  $pk  The id of the primary key.
     *
     * @return  mixed  Object on success, false on failure.
     */
    public function getItem($pk = null)
    {
        if ($item = parent::getItem($pk))
        {
            // Convert the params field to an array.
            $registry = new Registry($item->attribs);
            $item->attribs = $registry->toArray();

            // Convert the metadata field to an array.
            $registry = new Registry($item->metadata);
            $item->metadata = $registry->toArray();

            if (!empty($item->id))
            {
                $item->tags = new JHelperTags;
                $item->tags->getTagIds($item->id, 'com_logmoniter.watchdog');
            }
        }

        // Load associated content items
        $assoc = JLanguageAssociations::isEnabled();

        if ($assoc)
        {
            $item->associations = array();

            if ($item->id != null)
            {
                $associations = JLanguageAssociations::getAssociations('com_logmoniter', '#__logbook_watchdogs', 'com_logmoniter.item', $item->id);

                foreach ($associations as $tag => $association)
                {
                    $item->associations[$tag] = $association->id;
                }
            }
        }

        return $item;
    }

  /**
     * Method to get the record form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  JForm|boolean  A JForm object on success, false on failure
     *
     * @since   1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_logmoniter.watchdog', 'watchdog', array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form))
        {
            return false;
        }

        $jinput = JFactory::getApplication()->input;

        /*
         * The front end calls this model and uses w_id to avoid id clashes so we need to check for that first.
         * The back end uses id so we use that the rest of the time and set it to 0 by default.
         */
        $id = $jinput->get('w_id', $jinput->get('id', 0));

        // Determine correct permissions to check.
        if ($this->getState('watchdog.id'))
        {
            $id = $this->getState('watchdog.id');

            // Existing record. Can only edit in selected categories.
            $form->setFieldAttribute('catid', 'action', 'core.edit');

            // Existing record. Can only edit own watchdogs in selected categories.
            $form->setFieldAttribute('catid', 'action', 'core.edit.own');
        }
        else
        {
            // New record. Can only create in selected categories.
            $form->setFieldAttribute('catid', 'action', 'core.create');
        }

        $user = JFactory::getUser();

        // Check for existing watchdog.
        // Modify the form based on Edit State access controls.
        if ($id != 0 && (!$user->authorise('core.edit.state', 'com_logmoniter.watchdog.' . (int) $id))
            || ($id == 0 && !$user->authorise('core.edit.state', 'com_logmoniter')))
        {
            // Disable fields for display.

            // Disable fields while saving.
            // The controller has already verified this is an watchdog you can edit.
            $form->setFieldAttribute('featured', 'filter', 'unset');
            $form->setFieldAttribute('ordering', 'filter', 'unset');
            $form->setFieldAttribute('publish_up', 'filter', 'unset');
            $form->setFieldAttribute('publish_down', 'filter', 'unset');
            $form->setFieldAttribute('state', 'filter', 'unset');
        }

        // Prevent messing with watchdog language and category when editing existing watchdog with associations
        $app = JFactory::getApplication();
        $assoc = JLanguageAssociations::isEnabled();

        // Check if watchdog is associated
        if ($this->getState('watchdog.id') && $app->isClient('site') && $assoc)
        {
            $associations = JLanguageAssociations::getAssociations('com_logmoniter', '#__logbook_watchdogs', 'com_logmoniter.item', $id);

            // Make fields read only
            if (!empty($associations))
            {
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
     * @return  mixed  The data for the form.
     *
     * @since   1.6
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $app  = JFactory::getApplication();
        $data = $app->getUserState('com_logmoniter.edit.watchdog.data', array());

        if (empty($data))
        {
            $data = $this->getItem();

            // Pre-select some filters (Status, Category, Language, Access) in edit form if those have been selected in Article Manager: Articles
            if ($this->getState('watchdog.id') == 0)
            {
                $filters = (array) $app->getUserState('com_logmoniter.watchdogs.filter');
                $data->set(
                    'state',
                    $app->input->getInt(
                        'state',
                        ((isset($filters['published']) && $filters['published'] !== '') ? $filters['published'] : null)
                    )
                );
                $data->set('catid', $app->input->getInt('catid', (!empty($filters['category_id']) ? $filters['category_id'] : null)));
                $data->set('language', $app->input->getString('language', (!empty($filters['language']) ? $filters['language'] : null)));
                $data->set('access',
                    $app->input->getInt('access', (!empty($filters['access']) ? $filters['access'] : JFactory::getConfig()->get('access')))
                );
            }
        }

        // If there are params fieldsets in the form it will fail with a registry object
        if (isset($data->params) && $data->params instanceof Registry)
        {
            $data->params = $data->params->toArray();
        }

        $this->preprocessData('com_logmoniter.watchdog', $data);

        return $data;
    }

  /**
     * Method to validate the form data.
     *
     * @param   JForm   $form   The form to validate against.
     * @param   array   $data   The data to validate.
     * @param   string  $group  The name of the field group to validate.
     *
     * @return  array|boolean  Array of filtered data if valid, false otherwise.
     *
     * @see     JFormRule
     * @see     JFilterInput
     * @since   3.7.0
     */
    public function validate($form, $data, $group = null)
    {
        // Don't allow to change the users if not allowed to access com_users.
        if (JFactory::getApplication()->isClient('administrator') && !JFactory::getUser()->authorise('core.manage', 'com_users'))
        {
            if (isset($data['created_by']))
            {
                unset($data['created_by']);
            }

            if (isset($data['modified_by']))
            {
                unset($data['modified_by']);
            }
        }

        return parent::validate($form, $data, $group);
    }

  /**
     * A protected method to get a set of ordering conditions.
     *
     * @param   object  $table  A record object.
     *
     * @return  array  An array of conditions to add to add to ordering queries.
     *
     * @since   1.6
     */
    protected function getReorderConditions($table)
    {
        return array('catid = ' . (int) $table->catid);
    }

    /**
     * Allows preprocessing of the JForm object.
     *
     * @param   JForm   $form   The form object
     * @param   array   $data   The data to be merged into the form object
     * @param   string  $group  The plugin group to be executed
     *
     * @return  void
     *
     * @since   3.0
     */
    protected function preprocessForm(JForm $form, $data, $group = 'content')
    {
        if ($this->canCreateCategory())
        {
            $form->setFieldAttribute('catid', 'allowAdd', 'true');
        }

        // Association content items
        if (JLanguageAssociations::isEnabled())
        {
            $languages = JLanguageHelper::getContentLanguages(false, true, null, 'ordering', 'asc');

            if (count($languages) > 1)
            {
                $addform = new SimpleXMLElement('<form />');
                $fields = $addform->addChild('fields');
                $fields->addAttribute('name', 'associations');
                $fieldset = $fields->addChild('fieldset');
                $fieldset->addAttribute('name', 'item_associations');

                foreach ($languages as $language)
                {
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
     * Void hit function for pagebreak when editing content from frontend
     *
     * @return  void
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
     * @return  boolean
     *
     * @since   3.6.1
     */
    private function canCreateCategory()
    {
        return JFactory::getUser()->authorise('core.create', 'com_logmoniter');
    }
}

