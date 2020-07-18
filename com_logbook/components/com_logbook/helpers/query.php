<?php
/**
 * @package Logbook.site
 * @copyright Copyright (c)2017 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */

defined('_JEXEC') or die;

/**
 * Logbook Component Query Helper
 *
 * @static
 * @package     Joomla.Site
 * @subpackage  com_logbook
 * @since       1.5
 */
class LogbookHelperQuery
{
  /**
   * Translate an order code to a field for primary category ordering.
   *
   * @param   string	$orderby	The ordering code.
   *
   * @return  string	The SQL field(s) to order by.
   * @since   1.5
   */
  public static function orderbyPrimary($orderby)
  {
    switch ($orderby)
    {
      case 'alpha' :
	      $orderby = 'c.path, ';
	      break;

      case 'ralpha' :
	      $orderby = 'c.path DESC, ';
	      break;

      case 'order' :
	      $orderby = 'c.lft, ';
	      break;

      default :
	      $orderby = '';
	      break;
    }

    return $orderby;
  }

  /**
   * Translate an order code to a field for secondary category ordering.
   *
   * @param   string	$orderby	The ordering code.
   * @param   string	$orderDate	The ordering code for the date.
   *
   * @return  string	The SQL field(s) to order by.
   * @since   1.5
   */
  public static function orderbySecondary($orderby, $orderDate = 'created')
  {
    $queryDate = self::getQueryDate($orderDate);

    switch ($orderby)
    {
      case 'date' :
	      $orderby = $queryDate;
	      break;

      case 'rdate' :
	      $orderby = $queryDate.' DESC ';
	      break;

      case 'alpha' :
	      $orderby = 'l.title';
	      break;

      case 'ralpha' :
	      $orderby = 'l.title DESC';
	      break;

      case 'downloads' :
	      $orderby = 'l.downloads';
	      break;

      case 'rdownloads' :
	      $orderby = 'l.downloads DESC';
	      break;

      case 'signatories' :
	      $orderby = 'l.signatories';
	      break;

      case 'rsignatories' :
	      $orderby = 'l.signatories DESC';
	      break;

      default :
	      $orderby = 'l.created DESC';
	      break;
    }

    return $orderby;
  }

  /**
   * Translate an order code to a field for primary category ordering.
   *
   * @param   string	$orderDate	The ordering code.
   *
   * @return  string	The SQL field(s) to order by.
   * @since   1.6
   */
  public static function getQueryDate($orderDate)
  {
    $db = JFactory::getDbo();

    switch($orderDate) {
      case 'modified' :
	      $queryDate = ' CASE WHEN l.modified = '.$db->quote($db->getNullDate()).' THEN l.created ELSE l.modified END';
	      break;

      // use created if closed is not set
      case 'closed' :
	      $queryDate = ' CASE WHEN d.closed = '.$db->quote($db->getNullDate()).' THEN l.created ELSE l.closed END ';
	      break;

      case 'created' :
      default :
	      $queryDate = ' d.created ';
	      break;
    }

    return $queryDate;
  }
}

