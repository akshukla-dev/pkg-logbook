<?php
/**
 * @copyright   Copyright (C) 2020 Amit Kumar Shukla, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
/**
 * Loationc Form Field class for the Logbook component.
 *
 * @since  0.0.1
 */
class JFormFieldLocation extends JFormFieldList
{
    /**
     * The field type.
     *
     * @var string
     */
    protected $type = 'location';

    /**
     * Method to get a list of options for a list input.
     *
     * @return array an array of JHtml options
     */
    protected function getOptions()
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('id,name');
        $query->from('#__logbook_locations');
        $db->setQuery((string) $query);
        $items = $db->loadObjectList();
        $options = array();

        if ($items) {
            foreach ($items as $item) {
                $options[] = JHtml::_('select.option', $item->id, $item->name);
            }
        }

        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}
