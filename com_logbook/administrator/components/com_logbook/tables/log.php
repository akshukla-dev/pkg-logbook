<?php
/**
 * @package LMI/LogManager/LogBook/LogMoniter
 * @copyright Copyright (c)2020 Amit Kumar Shukla. All Rights Reserved.
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Log Table class.
 */
class LogbookTableLog extends JTable
{
    /**
     * Ensure the params and metadata in json encoded in the bind method.
     *
     * @var array
     *
     * @since  3.4
     */
    protected $_jsonEncode = array('params', 'metadata');

    /**
     * Constructor.
     *
     * @param object Database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__logbook_logs', 'id', $db);

        // Set the published column alias
        $this->setColumnAlias('published', 'state');

        //Needed to use the Joomla tagging system with the document items.
        JTableObserverTags::createObserver($this, array('typeAlias' => 'com_logbook.log'));
        JTableObserverContenthistory::createObserver($this, array('typeAlias' => 'com_logbook.log'));
    }

    /**
     * Overloaded bind function to pre-process the params.
     *
     * @param mixed $array  an associative array or object to bind to the JTable instance
     * @param mixed $ignore an optional array or space separated list of properties to ignore while binding
     *
     * @return bool true on success
     *
     * @see     JTable:bind
     * @since   1.5
     */
    public function bind($array, $ignore = '')
    {
        if (isset($array['params']) && is_array($array['params'])) {
            // Convert the params field to a string.
            $registry = new JRegistry();
            $registry->loadArray($array['params']);
            $array['params'] = (string) $registry;
        }

        // Bind the rules.
        if (isset($array['rules']) && is_array($array['rules'])) {
            $rules = new JAccessRules($array['rules']);
            $this->setRules($rules);
        }

        return parent::bind($array, $ignore);
    }

    /**
     * Overrides JTable::store to set modified data and user id.
     *
     * @param bool $updateNulls true to update fields even if they are null
     *
     * @return bool true on success
     *
     * @since   11.1
     */
    public function store($updateNulls = false)
    {
        $date = JFactory::getDate();
        $user = JFactory::getUser();

        if ($this->id) {
            // Existing item
            $this->modified = $date->toSql();
            $this->modified_by = $user->get('id');
        } else {
            // New Log. A log created and created_by field can be set by the user,
            // so we don't touch either of these if they are set.
            if (!(int) $this->created) {
                $this->created = $date->toSql();
            }

            if (empty($this->created_by)) {
                $this->created_by = $user->get('id');
            }
        }

        //TODO: Cleanup here Set publish_up to null date if not set
        if (!$this->publish_up) {
            $this->publish_up = $this->getDbo()->getNullDate();
        }

        //TODO: Set publish_down to null date if not set
        if (!$this->publish_down) {
            $this->publish_down = $this->getDbo()->getNullDate();
        }

        // Verify that the alias is unique
        $table = JTable::getInstance('Log', 'LogbookTable', array('dbo' => $this->getDbo()));

        if ($table->load(array('language' => $this->language, 'alias' => $this->alias, 'catid' => $this->catid)) && ($table->id != $this->id || $this->id == 0)) {
            $this->setError(JText::_('COM_LOGBOOK_ERROR_UNIQUE_ALIAS'));

            return false;
        }

        return parent::store($updateNulls);
    }

    /**
     * Method to return the title to use for the asset table.
     *
     * @return string
     *
     * @since   11.1
     */
    protected function _getAssetTitle()
    {
        return $this->title;
    }

    /**
     * Method to compute the default name of the asset.
     * The default name is in the form table_name.id
     * where id is the value of the primary key of the table.
     *
     * @return string
     *
     * @since   11.1
     */
    protected function _getAssetName()
    {
        $k = $this->_tbl_key;

        return 'com_logbook.log.'.(int) $this->$k;
    }

    /**
     * We provide our global ACL as parent.
     *
     * @see JTable::_getAssetParentId()
     */

    //Note: The component categories ACL override the items ACL, (whenever the ACL of a
    //      category is modified, changes are spread into the items ACL).
    //      This is the default COM_LOGBOOK behavior. see: libraries/legacy/table/content.php
    protected function _getAssetParentId(JTable $table = null, $id = null)
    {
        $assetId = null;

        // This is a document under a category.
        if ($this->catid) {
            // Build the query to get the asset id for the parent category.
            $query = $this->_db->getQuery(true)
              ->select($this->_db->quoteName('asset_id'))
              ->from($this->_db->quoteName('#__categories'))
              ->where($this->_db->quoteName('id').' = '.(int) $this->catid);

            // Get the asset id from the database.
            $this->_db->setQuery($query);

            if ($result = $this->_db->loadResult()) {
                $assetId = (int) $result;
            }
        }

        // Return the asset id.
        if ($assetId) {
            return $assetId;
        } else {
            return parent::_getAssetParentId($table, $id);
        }
    }
}
