<?php
/**
 * @package LMI/LogManager/LogBook/LogMoniter
 * @copyright Copyright (c)2020 Amit Kumar Shukla. All Rights Reserved.
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */
defined('_JEXEC') or die;

/**
 * Logbook Component Query Helper.
 *
 * @static
 *
 * @since       1.5
 */
class LogbookHelperQuery
{
    /**
     * Translate an order code to a field for primary category ordering.
     *
     * @param string $orderby the ordering code
     *
     * @return string the SQL field(s) to order by
     *
     * @since   1.5
     */
    public static function orderbyPrimary($orderby)
    {
        switch ($orderby) {
      case 'alpha':
          $orderby = 'c.path, ';
          break;

      case 'ralpha':
          $orderby = 'c.path DESC, ';
          break;

      case 'order':
          $orderby = 'c.lft, ';
          break;

      default:
          $orderby = '';
          break;
    }

        return $orderby;
    }

    /**
     * Translate an order code to a field for secondary category ordering.
     *
     * @param string $orderby   the ordering code
     * @param string $orderDate the ordering code for the date
     *
     * @return string the SQL field(s) to order by
     *
     * @since   1.5
     */
    public static function orderbySecondary($orderby, $orderDate = 'created')
    {
        $queryDate = self::getQueryDate($orderDate);

        switch ($orderby) {
        case 'date':
            $orderby = $queryDate;
            break;

        case 'rdate':
            $orderby = $queryDate.' DESC ';
            break;

        case 'alpha':
            $orderby = 'l.title';
            break;

        case 'ralpha':
            $orderby = 'l.title DESC';
        break;

        case 'order':
            $orderby = 'l.ordering';
            break;

        case 'rorder':
            $orderby = 'l.ordering DESC';
            break;
        case 'hits':
            $orderby = 'l.hits DESC';
            break;

        case 'rhits':
            $orderby = 'l.hits';
            break;

        default:
            $orderby = 'l.ordering';
            break;
        }

        return $orderby;
    }

    /**
     * Translate an order code to a field for primary category ordering.
     *
     * @param string $orderDate the ordering code
     *
     * @return string the SQL field(s) to order by
     *
     * @since   1.6
     */
    public static function getQueryDate($orderDate)
    {
        $db = JFactory::getDbo();

        switch ($orderDate) {
        case 'modified':
            $queryDate = ' CASE WHEN l.modified = '.$db->quote($db->getNullDate()).' THEN l.created ELSE l.modified END';
            break;

        // use created if closed is not set
        case 'published':
            $queryDate = ' CASE WHEN l.publish_up = '.$db->quote($db->getNullDate()).' THEN l.created ELSE l.publish_up END ';
            break;

        case 'unpublished':
            $queryDate = ' CASE WHEN l.publish_down = '.$db->quote($db->getNullDate()).' THEN l.created ELSE l.publish_down END ';
            break;

        case 'created':
        default:
            $queryDate = ' l.created ';
            break;
        }

        return $queryDate;
    }
}
