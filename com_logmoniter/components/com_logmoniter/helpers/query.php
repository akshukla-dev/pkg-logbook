<?php
/**
 * @copyright Copyright (c)2017 Amit Kumar Shukla
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
class LogmoniterHelperQuery
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
          $orderby = 'w.title';
          break;

      case 'ralpha':
          $orderby = 'w.title DESC';
          break;

      case 'hits':
          $orderby = 'w.hits';
          break;

      case 'rhits':
          $orderby = 'w.hits DESC';
          break;

      case 'logs':
          $orderby = 'w.logs';
          break;

      case 'rlogs':
          $orderby = 'w.logs DESC';
          break;

      default:
          $orderby = 'w.ordering';
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
          $queryDate = ' CASE WHEN w.modified = '.$db->quote($db->getNullDate()).' THEN w.created ELSE w.modified END';
          break;

      // use created if publish_up is not set
      case 'published':
          $queryDate = ' CASE WHEN w.publish_up = '.$db->quote($db->getNullDate()).' THEN w.created ELSE w.publish_up END ';
          break;
	  case 'unpublished' :
		  $queryDate = ' CASE WHEN w.publish_down = ' . $db->quote($db->getNullDate()) . ' THEN w.created ELSE w.publish_down END ';
		  break;
      case 'created':
      default:
          $queryDate = ' w.created ';
          break;
    }

        return $queryDate;
    }
}
