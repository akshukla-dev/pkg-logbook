<?php
/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Log Component HTML Helper.
 *
 * @since  1.5
 */
class JHtmlIcon
{
    /**
     * Create a link to create a new log.
     *
     * @param mixed $log    Unused
     * @param mixed $params Unused
     *
     * @return string
     */
    public static function create($log, $params)
    {
        JHtml::_('bootstrap.tooltip');

        $uri = JUri::getInstance();
        $url = JRoute::_(LogbookHelperRoute::getFormRoute(0, base64_encode($uri)));
        $text = JHtml::_('image', 'system/new.png', JText::_('JNEW'), null, true);
        $button = JHtml::_('link', $url, $text);

        return '<span class="hasTooltip" title="'.JHtml::tooltipText('COM_LOGBOOK_FORM_CREATE_LOG').'">'.$button.'</span>';
    }

    /**
     * Create a link to edit an existing log.
     *
     * @param object                    $log     Log data
     * @param \Joomla\Registry\Registry $params  Item params
     * @param array                     $attribs Unused
     *
     * @return string
     */
    public static function edit($log, $params, $attribs = array())
    {
        $uri = JUri::getInstance();

        if ($params && $params->get('popup')) {
            return;
        }

        if ($log->state < 0) {
            return;
        }

        JHtml::_('bootstrap.tooltip');

        $url = LogbookHelperRoute::getFormRoute($log->id, base64_encode($uri));
        $icon = $log->state ? 'edit.png' : 'edit_unpublished.png';
        $text = JHtml::_('image', 'system/'.$icon, JText::_('JGLOBAL_EDIT'), null, true);

        if ($log->state == 0) {
            $overlib = JText::_('JUNPUBLISHED');
        } else {
            $overlib = JText::_('JPUBLISHED');
        }

        $date = JHtml::_('date', $log->created);
        $author = $log->created_by_alias ? $log->created_by_alias : $log->author;

        $overlib .= '&lt;br /&gt;';
        $overlib .= $date;
        $overlib .= '&lt;br /&gt;';
        $overlib .= htmlspecialchars($author, ENT_COMPAT, 'UTF-8');

        $button = JHtml::_('link', JRoute::_($url), $text);

        return '<span class="hasTooltip" title="'.JHtml::tooltipText('COM_LOGBOOK_EDIT').' :: '.$overlib.'">'.$button.'</span>';
    }
}
