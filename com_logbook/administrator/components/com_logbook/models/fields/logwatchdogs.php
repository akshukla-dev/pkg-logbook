<?php
/**
 * @copyright Copyright (c)2020 Amit Kumar Shukla. All Rights Reserved.
 * @license GNU General Public License version 3, or later
 */
defined('_JEXEC') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
// import the list field type
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

//Script which build the select html tag containing the name and the id of the
//workcenters.
//Note: This script is based on: libraries/legacy/form/fields/category.php

class JFormFieldLogWatchdogs extends JFormFieldList
{
    protected $type = 'logwatchdogs';

    protected function getOptions()
    {
        $options = array();

        // Get the current user object.
        $user = JFactory::getUser();
        $groups = implode(',', $user->getAuthorisedViewLevels());
        $userId = $user->get('id');
        //Retrieve all data from the mapping table.
        $db = JFactory::getDbo();
        // For Filter by start and end dates.
        $nullDate = $db->quote($db->getNullDate());
        $nowDate = $db->quote(JFactory::getDate()->toSql());
        $query = $db->getQuery(true);
        $query->select('id AS value, title AS text');
        $query->from('#__logbook_watchdogs');
        $query->where('access IN ('.$groups.')');
        $query->where('state='.(int) 1);
        $query->where('(publish_up = '.$nullDate.' OR publish_up <= '.$nowDate.')')
            ->where('(publish_down = '.$nullDate.' OR publish_down >= '.$nowDate.')');
        $db->setQuery($query);
        $items = $db->loadObjectList();

        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $items);

        return $options;
    }
}
