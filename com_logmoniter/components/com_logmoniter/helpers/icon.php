<?php
/**
 * @package LMI/LogManager/LogBook/LogMoniter
 * @copyright Copyright (c)2020 Amit Kumar Shukla. All Rights Reserved.
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */
defined('_JEXEC') or die;

/**
 * Watchdog Component HTML Helper.
 *
 * @since  1.5
 */
class JHtmlIcon
{
    /**
     * Create a link to create a new watchdog.
     *
     * @param mixed $watchdog Unused
     * @param mixed $params   Unused
     *
     * @return string
     */
    public static function create($watchdog, $params)
    {
        JHtml::_('bootstrap.tooltip');

        $uri = JUri::getInstance();
        $url = JRoute::_(LogmoniterHelperRoute::getFormRoute(0, base64_encode($uri)));
        $text = JHtml::_('image', 'system/new.png', JText::_('JNEW'), null, true);
        $button = JHtml::_('link', $url, $text);

        return '<span class="hasTooltip" title="'.JHtml::tooltipText('COM_LOGMONITER_FORM_CREATE_WATCHDOG').'">'.$button.'</span>';
    }

    /**
     * Create a link to edit an existing watchdog.
     *
     * @param object                    $watchdog Watchdog data
     * @param \Joomla\Registry\Registry $params   Item params
     * @param array                     $attribs  Unused
     *
     * @return string
     */
    public static function edit($watchdog, $params, $attribs = array())
    {
        $uri = JUri::getInstance();

        //if ($params && $params->get('popup')) {
        //return;
        //}

        if ($watchdog->state < 0) {
            return;
        }

        JHtml::_('bootstrap.tooltip');

        $url = LogmoniterHelperRoute::getFormRoute($watchdog->id, base64_encode($uri));
        $icon = $watchdog->state ? 'edit.png' : 'edit_unpublished.png';
        $text = JHtml::_('image', 'system/'.$icon, JText::_('JGLOBAL_EDIT'), null, true);

        if ($watchdog->state == 0) {
            $overlib = JText::_('JUNPUBLISHED');
        } else {
            $overlib = JText::_('JPUBLISHED');
        }

        $date = JHtml::_('date', $watchdog->created);
        $author = $watchdog->created_by_alias ? $watchdog->created_by_alias : $watchdog->author;

        $overlib .= '&lt;br /&gt;';
        $overlib .= $date;
        $overlib .= '&lt;br /&gt;';
        $overlib .= htmlspecialchars($author, ENT_COMPAT, 'UTF-8');

        $button = JHtml::_('link', JRoute::_($url), $text);

        return '<span class="hasTooltip" title="'.JHtml::tooltipText('COM_LOGMONITER_EDIT').' :: '.$overlib.'">'.$button.'</span>';
    }
}
