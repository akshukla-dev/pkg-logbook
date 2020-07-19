<?php
/**
 *
 * @copyright Copyright (c)2020 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 *
 */


defined('_JEXEC') or die; //No direct access to this file.

require_once JPATH_ADMINISTRATOR.'/components/com_logmoniter/helpers/logmoniter.php';


class LogmoniterModelWatchdog extends JModelAdmin
{
  //prefix used with the controller messages.
  protected $text_prefix = 'COM_LOGMONITER';

  //Returns a Table object, always creating it.
  //Table can be defined/overrided in the file: tables/mycomponent.php
  public function getTable($type = 'Watchdog', $prefix = 'LogmoniterTable', $config = array())
  {
    return JTable::getInstance($type, $prefix, $config);
  }


  //We allow users to edit state of their own watchdogs.
  protected function canEditState($record)
  {
    $user = JFactory::getUser();
    if($user->authorise('core.edit.own', 'com_logmoniter.watchdog.'.$record->id) && $record->created_by == $user->get('id')) {
      return true;
    }

    return parent::canEditState($record);
  }


  public function getForm($data = array(), $loadData = true)
  {
    $form = $this->loadForm('com_logmoniter.watchdog', 'watchdog', array('control' => 'jform', 'load_data' => $loadData));

    if(empty($form)) {
      return false;
    }

    return $form;
  }


  protected function loadFormData()
  {
    //Check the session for previously entered form data.
    $data = JFactory::getApplication()->getUserState('com_logmoniter.edit.watchdog.data', array());

    if(empty($data)) {
      $data = $this->getItem();
    }

    return $data;
  }


  /**
   * Method to get a single record.
   *
   * @param   integer  $pk  The id of the primary key.
   *
   * @return  mixed    Object on success, false on failure.
   *
   * @since   12.2
   */
  public function getItem($pk = null)
  {
    if($item = parent::getItem($pk)) {

      if(!empty($item->id)) {
				$item->tags = new JHelperTags;
				$item->tags->getTagIds($item->id, 'com_logmoniter.watchdog');

				//We need the state of the watchdog category in case it has been trashed,
				//archived or unpublished.
				//Therefore we can warn the user about the category state.
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select('published, title');
				$query->from('#__categories');
				$query->where('id='.(int)$item->catid);
				$db->setQuery($query);
				$category = $db->loadObject();

				$item->cat_state = $category->published;
				$item->cat_title = $category->title;
      }
    }

    return $item;
  }


  /**
   * Prepare and sanitise the table prior to saving.
   *
   * @since	1.6
   */
  protected function prepareTable($table)
  {
    //Reorder the articles within the category so the new article is first
    if(empty($table->id)) {
      $table->reorder('catid = '.(int) $table->catid.' AND published >= 0');
    }
  }


  /**
   * A protected method to get a set of ordering conditions.
   *
   * @param	object	A record object.
   * @return	array	An array of conditions to add to add to ordering queries.
   * @since	1.6
   */
  protected function getReorderConditions($table)
  {
    $condition = array();
    $condition[] = 'catid = '.(int) $table->catid;
    return $condition;
  }
}

