<?php
/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Blueprint Table class.
 *
 * @since  0.0.1
 */
class LogbookTableBlueprint extends JTable
{
    /**
     * Constructor.
     *
     * @param JDatabaseDriver &$db A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__logbook_blueprints', 'id', $db);
    }
}
