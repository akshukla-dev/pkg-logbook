<?php
/**
 * @copyright Copyright (c)2017 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */
defined('_JEXEC') or die; //No direct access to this file.

require_once JPATH_ADMINISTRATOR.'/components/com_logbook/helpers/logbook.php';

class LogbookModelLog extends JModelAdmin
{
    //prefix used with the controller messages.
    protected $text_prefix = 'com_logbook';

    //Returns a Table object, always creating it.
    //Table can be defined/overrided in the file: tables/mycomponent.php
    public function getTable($type = 'Log', $prefix = 'LogbookTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    //We allow users to edit state of their own documents.
    protected function canEditState($record)
    {
        $user = JFactory::getUser();
        if ($user->authorise('core.edit.own', 'com_logbook.log.'.$record->id) && $record->created_by == $user->get('id')) {
            return true;
        }

        return parent::canEditState($record);
    }

    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm('com_logbook.log', 'log', array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    protected function loadFormData()
    {
        //Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState('com_logbook.edit.log.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    /**
     * Method to get a single record.
     *
     * @param int $pk the id of the primary key
     *
     * @return mixed object on success, false on failure
     *
     * @since   12.2
     */
    public function getItem($pk = null)
    {
        if ($item = parent::getItem($pk)) {
            if (!empty($item->id)) {
                //$item->wcenter = LogbookHelper::getWorkcenter($item->id);
                //$item->inset = LogbookHelper::getInset($item->id);
                //$item->bprint = LogbookHelper::getBprint($item->id);

                //We need the state of the document watchdog in case it has been trashed,
                //archived or unpublished.
                //Therefore we can warn the user about the watchdog state.
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);
                $query->select('state, title');
                $query->from('#__logbook_watchdogs');
                $query->where('id='.(int) $item->wdid);
                $db->setQuery($query);
                $watchdog = $db->loadObject();

                $item->wd_state = $watchdog->state;
                $item->wd_title = $watchdog->title;
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
        //Reorder the articles within the watchdog so the new article is first
        if (empty($table->id)) {
            $table->reorder('wdid = '.(int) $table->wdid.' AND published >= 0');
        }
    }

    /**
     * A protected method to get a set of ordering conditions.
     *
     * @param	object	a record object
     *
     * @return array an array of conditions to add to add to ordering queries
     *
     * @since	1.6
     */
    protected function getReorderConditions($table)
    {
        $condition = array();
        $condition[] = 'wdid = '.(int) $table->wdid;

        return $condition;
    }
}
