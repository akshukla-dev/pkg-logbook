<?php
/**
 * @copyright   Copyright (C) 2020 Amit Kumar Shukla, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Logbook router functions.
 *
 * These functions are proxys for the new router interface
 * for old SEF extensions.
 *
 * @param array &$query An array of URL arguments
 *
 * @return array the URL arguments to use to assemble the subsequent URL
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function logbookBuildRoute(&$query)
{
    $segments = array();

    if (isset($query['view'])) {
        $segments[] = $query['view'];
        unset($query['view']);
    }
    if (isset($query['id'])) {
        $segments[] = $query['id'];
        unset($query['id']);
    }

    if (isset($query['wdid'])) {
        $segments[] = $query['wdid'];
        unset($query['wdid']);
    }

    if (isset($query['layout'])) {
        unset($query['layout']);
    }

    return $segments;
}

/**
 * Parse the segments of a URL.
 *
 * This function is a proxy for the new router interface
 * for old SEF extensions.
 *
 * @param array $segments the segments of the URL to parse
 *
 * @return array the URL attributes to be used by the application
 *
 * @since   3.3
 * @deprecated  4.0  Use Class based routers instead
 */
function logbookParseRoute($segments)
{
    $vars = array();
    switch ($segments[0]) {
        case 'log':
            $vars['view'] = 'log';
            $id = explode(':', $segmants[1]);
            $vars['id'] = (int) $id[0];
            $wdid = explode(':', segments[2]);
            $vars['wdid'] = (int) $wdid[0];
        break;
        case 'form':
            $vars['view'] = 'form';
            $vars['layout'] = 'edit';
        break;
    }

    return $vars;
}
