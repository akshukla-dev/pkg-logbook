<?php
/**
 * @copyright Copyright (c)2017 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */
defined('_JEXEC') or die;

/**
 * Logmoniter Component Route Helper.
 *
 * @static
 *
 * @since       1.5
 */
abstract class LogmoniterHelperRoute
{
    /**
     * Get the watchdog route.
     *
     * @param   integer  $id        The route of the content item.
     * @param   integer  $catid     The category ID.
     * @param   integer  $language  The language code.
     *
     * @return  string  The watchdog route.
     *
     * @since   1.5
     */
    public static function getWatchdogRoute($id, $catid=0, $language = 0)
    {
        //Create the link
        $link = 'index.php?option=com_logmoniter&view=watchdog&id='.$id;

        if ((int) $catid > 1)
        {
            $link .= '&catid=' . $catid;
        }

        if ($language && $language !== '*' && JLanguageMultilang::isEnabled())
        {
            $link .= '&lang=' . $language;
        }

        return $link;
	}

	/**
	 * Get the category route.
	 *
	 * @param   integer  $catid     The category ID.
	 * @param   integer  $language  The language code.
	 *
	 * @return  string  The watchdog route.
	 *
	 * @since   1.5
	 */
	public static function getCategoryRoute($catid, $language = 0)
	{
		if ($catid instanceof JCategoryNode)
		{
			$id = $catid->id;
		}
		else
		{
			$id = (int) $catid;
		}

		if ($id < 1)
		{
			$link = '';
		}
		else
		{
			$link = 'index.php?option=com_logmoniter&view=category&id=' . $id;

			if ($language && $language !== '*' && JLanguageMultilang::isEnabled())
			{
				$link .= '&lang=' . $language;
			}
		}

		return $link;
	}


   /**
	 * Get the form route.
	 *
	 * @param   integer  $id  The form ID.
	 *
	 * @return  string  The watchdog route.
	 *
	 * @since   1.5
	 */
	public static function getFormRoute($id)
	{
		return 'index.php?option=com_logmoniter&task=watchdog.edit&w_id=' . (int) $id;
	}
}
