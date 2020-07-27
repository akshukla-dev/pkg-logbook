<?php
/**
 * @copyright Copyright (c)2020 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 */
defined('_JEXEC') or die;

/**
 * Logmoniter Component Category Tree.
 *
 * @static
 *
 * @since       1.6
 */
class LogmoniterCategories extends JCategories
{
    public function __construct($options = array())
    {
        $options['table'] = '#__logbook_watchdogs';
        $options['extension'] = 'com_logmoniter';

        parent::__construct($options);
    }
}
