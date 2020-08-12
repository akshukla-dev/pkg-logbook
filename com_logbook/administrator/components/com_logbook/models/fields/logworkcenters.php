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

class JFormFieldLogWorkcenters extends JFormFieldList
{
    protected $type = 'logworkcenters';

    protected function getOptions()
    {
        $options = array();
        //Retrieve all data from the mapping table.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id AS value, title AS text');
        $query->from('#__logbook_workcenters');
        $db->setQuery($query);
        $items = $db->loadObjectList();

        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $items);

        return $options;
    }
}
