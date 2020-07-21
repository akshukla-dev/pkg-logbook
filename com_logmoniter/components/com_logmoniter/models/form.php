<?php
/**
 * @package com_logmoniter
 * @copyright Copyright (c)2020 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 *
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

// Base this model on the backend version.
JLoader::register('LogmoniterModelWatchdog', JPATH_COMPONENT_ADMINISTRATOR . '/models/watchdog.php');

//Inherit the backend version.
class LogmoniterModelForm extends LogmoniterModelWatchdog
{
  /**
   * Model typeAlias string. Used for version history.
   *
   * @var        string
   */
  public $typeAlias = 'com_logmoniter.watchdog';


  /**
   * Method to auto-populate the model state.
   *
   * Note. Calling getState in this method will result in recursion.
   *
   * @return  void
   *
   * @since   1.6
   */
  protected function populateState()
  {
    $app = JFactory::getApplication();

    // Load state from the request.
    $pk = $app->input->getInt('w_id');
		$this->setState('watchdog.id', $pk);

		// Add compatibility variable for default naming conventions.
		$this->setState('form.id', $pk);

    //Retrieve a possible category id from the url query.
    $this->setState('watchdog.catid', $app->input->getInt('catid'));

    //Retrieve a possible encoded return url from the url query.
    $return = $app->input->get('return', null, 'base64');
    $this->setState('return_page', base64_decode($return));

    // Load the global parameters of the component.
    $params = $app->getParams();
    $this->setState('params', $params);

    $this->setState('layout', $app->input->getString('layout'));
  }


  /**
   * Method to get watchdog data.
   *
   * @param   integer  $itemId  The id of the watchdog.
   *
   * @return  mixed  Content item data object on success, false on failure.
   */
  public function getItem($itemId = null)
  {
    $itemId = (int) (!empty($itemId)) ? $itemId : $this->getState('watchdog.id');

    // Get a row instance.
    $table = $this->getTable();

    // Attempt to load the row.
    //Notes: If it's a new item, load function just return true.
    $return = $table->load($itemId);

    // Check for a table object error.
    if($return === false && $table->getError()) {
      $this->setError($table->getError());
      return false;
    }

    //Get the fields of the table as an array
    $properties = $table->getProperties(1);
    //then convert the array into an object.
    $item = ArrayHelper::toObject($properties, 'JObject');

    //Note: params fields are missing on purpose in the xml form as
    //params cannot be set on frontend.
    //All of the watchdog items created on frontend has an empty
    //params value.

    // Convert params field to Registry.
    $item->params = new Registry($item->attribs);

    // Compute selected asset permissions.
    $user = JFactory::getUser();
    $userId = $user->get('id');
    $asset = 'com_logmoniter.watchdog.'.$item->id;

    // Check general edit permission first.
    if($user->authorise('core.edit', $asset)) {
      $item->params->set('access-edit', true);
    }
    // Now check if edit.own is available.
    elseif(!empty($userId) && $user->authorise('core.edit.own', $asset)) {
      // Check for a valid user and that they are the owner.
      if($userId == $item->created_by) {
			$item->params->set('access-edit', true);
      }
    }

		// Check edit state permission.
    if($itemId) { //existing item
      // Check edit state permission.
      $item->params->set('access-change', $user->authorise('core.edit.state', $asset));
    }
    else { // New item.

      $catId = (int) $this->getState('watchdog.catid');

      if($catId) {
				//Check the change access in this specific category.
				$item->params->set('access-change', $user->authorise('core.edit.state', 'com_logmoniter.category.'.$catId));
				$item->catid = $catId;
      }
      else { //Check the general change access.
			$item->params->set('access-change', $user->authorise('core.edit.state', 'com_logmoniter'));
      }
    }

    // Convert the metadata field to an array.
    $registry = new Registry($item->metadata);
    $item->metadata = $registry->toArray();

		if ($itemId)
		{
			//Get the watchdog tags.
			$item->tags = new JHelperTags;
			$item->tags->getTagIds($item->id, 'com_logmoniter.watchdog');
			$item->metadata['tags'] = $item->tags;
		}
    return $item;
  }


  /**
   * Get the return URL.
   *
   * @return  string	The return URL.
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
   * @param   array  $data  The form data.
   *
   * @return  boolean  True on success.
   *
   * @since   3.2
   */
  public function save($data)
  {
		// Associations are not edited in frontend ATM so we have to inherit them
		if (JLanguageAssociations::isEnabled() && !empty($data['id'])
			&& $associations = JLanguageAssociations::getAssociations('com_logmoniter', '#__logbook_watchdogs', 'com_logmoniter.item', $data['id']))
		{
			foreach ($associations as $tag => $associated)
			{
				$associations[$tag] = (int) $associated->id;
			}

			$data['associations'] = $associations;
		}

    return parent::save($data);
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
	 * @since   3.7.0
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'content')
	{
		$params = $this->getState()->get('params');

		if ($params && $params->get('enable_category') == 1)
		{
			$form->setFieldAttribute('catid', 'default', $params->get('catid', 1));
			$form->setFieldAttribute('catid', 'readonly', 'true');
		}

		return parent::preprocessForm($form, $data, $group);
	}
}
