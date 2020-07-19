<?php
/**
 *
 * @copyright Copyright (c)2020 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 *
 */



defined('_JEXEC') or die;


/**
 * Build the route for the com_okeydoc component
 *
 * @param	array	An array of URL arguments
 *
 * @return	array	The URL arguments to use to assemble the subsequent URL.
 */
function LogmoniterBuildRoute(&$query)
{
  $segments = array();

  if(isset($query['view'])) {
    $segments[] = $query['view'];
    unset($query['view']);
  }

  if(isset($query['id'])) {
    $segments[] = $query['id'];
    unset($query['id']);
  }

  if(isset($query['catid'])) {
    $segments[] = $query['catid'];
    unset($query['catid']);
  }

  if(isset($query['layout'])) {
    unset($query['layout']);
  }

  return $segments;
}


/**
 * Parse the segments of a URL.
 *
 * @param	array	The segments of the URL to parse.
 *
 * @return	array	The URL attributes to be used by the application.
 */
function LogmoniterParseRoute($segments)
{
  $vars = array();

  switch($segments[0])
  {
    case 'categories':
	   $vars['view'] = 'categories';
	   break;
    case 'category':
	   $vars['view'] = 'category';
	   $id = explode(':', $segments[1]);
	   $vars['id'] = (int)$id[0];
	   break;
    case 'watchdog':
	   $vars['view'] = 'watchdog';
	   $id = explode(':', $segments[1]);
	   $vars['id'] = (int)$id[0];
	   $catid = explode(':', $segments[2]);
	   $vars['catid'] = (int)$catid[0];
	   break;
    case 'form':
	   $vars['view'] = 'form';
	   //Form layout is always set to 'edit'.
	   $vars['layout'] = 'edit';
	   break;
  }

  return $vars;
}

