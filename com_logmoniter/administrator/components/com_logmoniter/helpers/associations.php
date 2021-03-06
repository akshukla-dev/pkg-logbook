<?php
/**
 * @package LMI/LogManager/LogBook/LogMoniter
 * @copyright Copyright (c)2020 Amit Kumar Shukla. All Rights Reserved.
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */
defined('_JEXEC') or die;

JTable::addIncludePath(__DIR__.'/../tables');

/**
 * Logmoniter associations helper.
 *
 * @since  3.7.0
 */
class LogmoniterAssociationsHelper extends JAssociationExtensionHelper
{
    /**
     * The extension name.
     *
     * @var array
     *
     * @since   3.7.0
     */
    protected $extension = 'com_logmoniter';

    /**
     * Array of item types.
     *
     * @var array
     *
     * @since   3.7.0
     */
    protected $itemTypes = array('watchdog', 'category');

    /**
     * Has the extension association support.
     *
     * @var bool
     *
     * @since   3.7.0
     */
    protected $associationsSupport = true;

    /**
     * Get the associated items for an item.
     *
     * @param string $typeName The item type
     * @param int    $id       The id of item for which we need the associated items
     *
     * @return array
     *
     * @since   3.7.0
     */
    public function getAssociations($typeName, $id)
    {
        $type = $this->getType($typeName);

        $context = $this->extension.'.item';
        $catidField = 'catid';

        if ($typeName === 'category') {
            $context = 'com_categories.item';
            $catidField = '';
        }

        // Get the associations.
        $associations = JLanguageAssociations::getAssociations(
            $this->extension,
            $type['tables']['a'],
            $context,
            $id,
            'id',
            'alias',
            $catidField
        );

        return $associations;
    }

    /**
     * Get item information.
     *
     * @param string $typeName The item type
     * @param int    $id       The id of item for which we need the associated items
     *
     * @return JTable|null
     *
     * @since   3.7.0
     */
    public function getItem($typeName, $id)
    {
        if (empty($id)) {
            return null;
        }

        $table = null;

        switch ($typeName) {
            case 'watchdog':
                $table = JTable::getInstance('Watchdog', 'LogmoniterTable');
                break;

            case 'category':
                $table = JTable::getInstance('Category');
                break;
        }

        if (empty($table)) {
            return null;
        }

        $table->load($id);

        return $table;
    }

    /**
     * Get information about the type.
     *
     * @param string $typeName The item type
     *
     * @return array Array of item types
     *
     * @since   3.7.0
     */
    public function getType($typeName = '')
    {
        $fields = $this->getFieldsTemplate();
        $tables = array();
        $joins = array();
        $support = $this->getSupportTemplate();
        $title = '';

        if (in_array($typeName, $this->itemTypes)) {
            switch ($typeName) {
                case 'watchdog':

                    $support['state'] = true;
                    $support['acl'] = true;
                    $support['checkout'] = true;
                    $support['category'] = true;
                    $support['save2copy'] = true;

                    $tables = array(
                        'a' => '#__logbook_watchdogs',
                    );

                    $title = 'watchdog';
                    break;

                case 'category':
                    $fields['created_user_id'] = 'a.created_user_id';
                    $fields['ordering'] = 'a.lft';
                    $fields['level'] = 'a.level';
                    $fields['catid'] = '';
                    $fields['state'] = 'a.published';

                    $support['state'] = true;
                    $support['acl'] = true;
                    $support['checkout'] = true;
                    $support['level'] = true;

                    $tables = array(
                        'a' => '#__categories',
                    );

                    $title = 'category';
                    break;
            }
        }

        return array(
            'fields' => $fields,
            'support' => $support,
            'tables' => $tables,
            'joins' => $joins,
            'title' => $title,
        );
    }
}
