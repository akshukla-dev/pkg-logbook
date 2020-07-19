<?php
/**
 *
 * @copyright Copyright (c)2020 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 *
 */


defined('_JEXEC') or die; //No direct access to this file.



class LogmoniterHelper
{
  //Create the sidebar items ($viewName = name of the active view).
  public static function addSubmenu($viewName)
  {
    JHtmlSidebar::addEntry(JText::_('COM_LOGMONITER_SUBMENU_DOCUMENTS'),
				    'index.php?option=com_logmoniter&view=watchdogs', $viewName == 'watchdogs');

    JHtmlSidebar::addEntry(JText::_('COM_LOGMONITER_SUBMENU_CATEGORIES'),
				    'index.php?option=com_categories&extension=com_logmoniter', $viewName == 'categories');
  }


  //Get the list of the allowed actions for the user.
  public static function getActions($categoryId = 0)
  {
    $user = JFactory::getUser();
    $result = new JObject;

    if(empty($categoryId)) {
      //Check permissions against the component.
      $assetName = 'com_logmoniter';
    }
    else {
      //Check permissions against the component category.
      $assetName = 'com_logmoniter.category.'.(int) $categoryId;
    }

    $actions = array('core.admin', 'core.manage', 'core.create', 'core.edit',
		     'core.edit.own', 'core.edit.state', 'core.delete');

    //Get from the core the user's permission for each action.
    foreach($actions as $action) {
      $result->set($action, $user->authorise($action, $assetName));
    }

    return $result;
  }



  //Return categories in which the user is allowed to do a given action. ("create" by default).
  public static function getUserCategories($action = 'create', $watchdogs = false)
  {
    $subquery = '';
    if($watchdogs) {
      //Get the number of document items linked to each category.
      $subquery = ',(SELECT COUNT(*) FROM #__logbook_watchdogs WHERE catid=c.id) AS watchdogs';
    }

    //Get the component categories.
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select('c.id, c.level, c.parent_id, c.title'.$subquery);
    $query->from('#__categories AS c');
    $query->where('extension="com_logmoniter"');
    $query->order('c.lft ASC');
    $db->setQuery($query);
    $categories = $db->loadObjectList();

    $userCategories = array();

    if($categories) {
      foreach($categories as $category) {
				//Get the list of the actions allowed for the user on this category.
				$canDo = OkeydocHelper::getActions($category->id);

				if($canDo->get('core.'.$action)) {
					$userCategories[] = $category;
					//$userCategories[] = array('id' => $category->id, 'title' => $category->title);
				}
      }
    }

    return $userCategories;
  }
}


