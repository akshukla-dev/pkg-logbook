<?php
/**
 * @package LMI/LogManager/LogBook/LogMoniter
 * @copyright Copyright (c)2020 Amit Kumar Shukla. All Rights Reserved.
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */
defined('_JEXEC') or die;

/**
 * Logbook Component Route Helper.
 *
 * @static
 *
 * @since       1.5
 */
abstract class LogbookHelperRoute
{
    /**
     * Get the log route.
     *
     * @param int $id       the route of the logbook item
     * @param int $catid    the category ID
     * @param int $language the language code
     *
     * @return string the log route
     *
     * @since   1.5
     */
    public static function getLogRoute($id, $catid = 0, $language = 0)
    {
        // Create the link
        $link = 'index.php?option=com_logbook&view=log&id='.$id;

        if ((int) $catid > 1) {
            $link .= '&catid='.$catid;
        }

        if ($language && $language !== '*' && JLanguageMultilang::isEnabled()) {
            $link .= '&lang='.$language;
        }

        return $link;
    }

    /**
     * Get the category route.
     *
     * @param int $catid    the category ID
     * @param int $language the language code
     *
     * @return string the log route
     *
     * @since   1.5
     */
    public static function getCategoryRoute($catid, $language = 0)
    {
        if ($catid instanceof JCategoryNode) {
            $id = $catid->id;
        } else {
            $id = (int) $catid;
        }

        if ($id < 1) {
            $link = '';
        } else {
            $link = 'index.php?option=com_logbook&view=category&id='.$id;

            if ($language && $language !== '*' && JLanguageMultilang::isEnabled()) {
                $link .= '&lang='.$language;
            }
        }

        return $link;
    }

    /**
     * Get the form route.
     *
     * @param int $id the form ID
     *
     * @return string the log route
     *
     * @since   1.5
     */
    public static function getFormRoute($id)
    {
        return 'index.php?option=com_logbook&task=log.edit&l_id='.(int) $id;
    }
}
