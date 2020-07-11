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
class JFormFieldBlueprint extends JFormFieldList
{
    /**
     * The field type.
     *
     * @var string
     */
    protected $type = 'blueprint';

    /**
     * Method to get a list of options for a list input.
     *
     * @return array an array of JHtml options
     */
    protected function getOptions()
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('id,title');
        $query->from('#__logbook_blueprints');
        $db->setQuery((string) $query);
        $items = $db->loadObjectList();
        $options = array();

        if ($items) {
            foreach ($items as $item) {
                $options[] = JHtml::_('select.option', $item->id, $item->title);
            }
        }

        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}
