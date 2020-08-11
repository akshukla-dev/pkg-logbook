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

class JFormFieldLogYears extends JFormFieldList
{
    protected $type = 'logyears';

    protected function getOptions()
    {
        $options = array();

        $db = JFactory::getDbo();
        $nullDate = $db->quote($db->getNullDate());
        $nowDate = $db->quote(JFactory::getDate()->toSql());

        $query = $db->getQuery(true);
        $yearsqn = $query->year($db->qn('created'));
        $query->select('DISTINCT ('.$yearsqn.')')
            ->from($db->qn('#__logbook_logs'))
            ->where('(publish_up = '.$nullDate.' OR publish_up <= '.$nowDate.')')
            ->where('(publish_down = '.$nullDate.' OR publish_down >= '.$nowDate.')')
            ->order('1 ASC');
        $db->setQuery($query);
        $years = $db->loadColumn();

        $yearsoptions = array('value' => '', 'text' => '');
        foreach ($years as $i => $value) {
            $yearsoptions[$i] = new stdClass();
            $yearsoptions[$i]->value = $value;
            $yearsoptions[$i]->text = $value;
        }

        // Merge any additional options in the XML definition.

        $options = array_merge(parent::getOptions(), $yearsoptions);

        return $options;
    }
}
