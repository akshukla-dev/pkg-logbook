<?php
/**
 * @copyright Copyright (c)2020 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Document Table class.
 */
class LogmoniterTableWatchdog extends JTable
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
        parent::__construct('#__logbook_watchdogs', 'id', $db);
        //Needed to use the Joomla tagging system with the watchdog items.
        JTableObserverTags::createObserver($this, array('typeAlias' => 'com_logmoniter.watchdog'));
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

        if (isset($array['metadata']) && is_array($array['metadata'])) {
            $registry = new JRegistry();
            $registry->loadArray($array['metadata']);
            $array['metadata'] = (string) $registry;
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
            // New watchdog. A watchdog created and created_by field can be set by the user,
            // so we don't touch either of these if they are set.
            if (!(int) $this->created) {
                $this->created = $date->toSql();
            }

            if (empty($this->created_by)) {
                $this->created_by = $user->get('id');
            }
        }

        // Verify that the alias is unique
        $table = JTable::getInstance('Watchdog', 'LogmoniterTable', array('dbo' => $this->getDbo()));

        if ($table->load(array('language' => $this->language, 'alias' => $this->alias, 'catid' => $this->catid)) && ($table->id != $this->id || $this->id == 0)) {
            $this->setError(JText::_('COM_LOGMONITER_ERROR_UNIQUE_ALIAS'));

            return false;
        }

        return parent::store($updateNulls);
    }

    /**
     * Overloaded check method to ensure data integrity.
     *
     * @return bool true on success
     *
     * @since   1.5
     */
    public function check()
    {
        // Check for valid name
        if (trim($this->title) == '') {
            $this->setError(JText::_('COM_LOGMONITER_ERR_TABLES_TITLE'));

            return false;
        }

        // Check for existing name
        $db = $this->getDbo();

        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__logbook_watchdogs'))
            ->where($db->quoteName('wcid').' = '.(int) $this->wcid)
            ->where($db->quoteName('isid').' = '.(int) $this->isid)
            ->where($db->quoteName('bpid').' = '.(int) $this->bpid);

        $db->setQuery($query);

        $xid = (int) $db->loadResult();

        if ($xid && $xid != (int) $this->id) {
            $this->setError(JText::_('COM_LOGMONITER_ERR_WATCHDOG_EXISTING'));

            return false;
        }

        if (empty($this->alias)) {
            $this->alias = $this->title;
        }

        $this->alias = JApplicationHelper::stringURLSafe($this->alias, $this->language);

        if (trim(str_replace('-', '', $this->alias)) == '') {
            $this->alias = JFactory::getDate()->format('Y-m-d-H-i-s');
        }

        // Check the publish down date is not earlier than publish up.
        if ($this->publish_down > $db->getNullDate() && $this->publish_down < $this->publish_up) {
            $this->setError(JText::_('JGLOBAL_START_PUBLISH_AFTER_FINISH'));

            return false;
        }

        /*
         * Clean up keywords -- eliminate extra spaces between phrases
         * and cr (\r) and lf (\n) characters from string
         */
        if (!empty($this->metakey)) {
            // Array of characters to remove
            $bad_characters = array("\n", "\r", '"', '<', '>');
            $after_clean = JString::str_ireplace($bad_characters, '', $this->metakey);
            $keys = explode(',', $after_clean);
            $clean_keys = array();

            foreach ($keys as $key) {
                // Ignore blank keywords
                if (trim($key)) {
                    $clean_keys[] = trim($key);
                }
            }

            // Put array back together delimited by ", "
            $this->metakey = implode(', ', $clean_keys);
        }

        return true;
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

        return 'com_logmoniter.watchdog.'.(int) $this->$k;
    }

    /**
     * We provide our global ACL as parent.
     *
     * @see JTable::_getAssetParentId()
     */

    //Note: The component categories ACL override the items ACL, (whenever the ACL of a
    //      category is modified, changes are spread into the items ACL).
    //      This is the default COM_LOGMONITER behavior. see: libraries/legacy/table/content.php
    protected function _getAssetParentId(JTable $table = null, $id = null)
    {
        $assetId = null;

        // This is a watchdog under a category.
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
