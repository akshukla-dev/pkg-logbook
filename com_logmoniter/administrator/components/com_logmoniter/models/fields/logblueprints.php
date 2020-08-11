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

class JFormFieldLogBlueprints extends JFormFieldList
{
    protected $type = 'logblueprints';

    protected function getInput()
    {
        //Get the item id directly from the form loaded with data.
        $itemId = $this->form->getValue('isid');

        if ($itemId) {
            //Get the inset ids previously selected.
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('id');
            $query->from('#__logbook_blueprints');
            $query->where('isid='.$itemId);
            $db->setQuery($query);
            $selected = $db->loadValue();

            //Assign the id array to the value attribute to get the selected items
            //displayed in the input field.
            //$this->value = $selected;
        }

        $input = parent::getInput();

        return $input;
    }

    protected function getOptions()
    {
        $app = JFactory::getApplication();
        $options = array();
        //Retrieve all data from the mapping table.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $insetId = $app->getUserStateFromRequest('com_logmoniter.moniter.filter.inset_id', 'filter_inset_id');
        if (is_numeric($insetId)) {
            $query->select('id AS value, title AS text');
            $query->from('#__logbook_blueprints')
                ->where('isid='.$insetId);
            $db->setQuery($query);
            $items = $db->loadObjectList();
        } else {
            $query->select('id AS value, title AS text');
            $query->from('#__logbook_blueprints');
            $db->setQuery($query);
            $items = $db->loadObjectList();
        }

        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $items);

        return $options;
    }

    /*protected function getOptions()
    {
        $app = JFactory::getApplication();
        $inset = $app->input->get('isid'); //isid is the dynamic value which is being used in the view
        if (empty($inset)) {
            JFactory::getApplication()->enqueueMessage('Please Select a LMI', 'warning');
        } else {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('bp.title')->from('`#__logbook_blueprints` AS bp')->where('bp.isid = "'.$inset.'" ');
            $rows = $db->setQuery($query)->loadObjectlist();
            foreach ($rows as $row) {
                $bprints[] = $row->title;
            }
            // Merge any additional options in the XML definition.
            $options = array_merge(parent::getOptions(), $bprints);

            return $options;
        }
    }*/
}
