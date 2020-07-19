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
        $options['table'] = '#__logbook_wathdogs';
        $options['extension'] = 'com_logmoniter';

        /* IMPORTANT: By default publish parent function invoke a field called "state" to
         *            publish/unpublish (but also archived, trashed etc...) an item.
         *            Since our field is called "published" we must informed the
         *            JCategories publish function in setting the "statefield" index of the
         *            options array
        */
        $options['statefield'] = 'published';

        parent::__construct($options);
    }
}
