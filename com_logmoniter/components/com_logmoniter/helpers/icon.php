<?php
/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Logmoniter Component HTML Helper.
 *
 * @since  1.5
 */
abstract class JHtmlIcon
{
    /**
     * Method to generate a link to the create item page for the given category.
     *
     * @param object   $category The category information
     * @param Registry $params   The item parameters
     * @param array    $params   Optional attributes for the link
     * @param bool     $legacy   True to use legacy images, false to use icomoon based graphic
     *
     * @return string The HTML markup for the create item link
     */
    public static function create($category, $params, $params = array(), $legacy = false)
    {
        $uri = JUri::getInstance();

        $url = 'index.php?option=com_logmoniter&task=watchdog.add&return='.base64_encode($uri).'&w_id=0&catid='.$category->id;

        $text = JLayoutHelper::render('joomla.content.icons.create', array('params' => $params, 'legacy' => $legacy));

        // Add the button classes to the params array
        if (isset($params['class'])) {
            $params['class'] .= ' btn btn-primary';
        } else {
            $params['class'] = 'btn btn-primary';
        }

        $button = JHtml::_('link', JRoute::_($url), $text, $params);

        $output = '<span class="hasTooltip" title="'.JHtml::_('tooltipText', 'COM_LOGMONITER_CREATE_WATCHDOG').'">'.$button.'</span>';

        return $output;
    }

    /**
     * Method to generate a link to the email item page for the given watchdog.
     *
     * @param object   $watchdog The watchdog information
     * @param Registry $params   The item parameters
     * @param array    $params   Optional attributes for the link
     * @param bool     $legacy   True to use legacy images, false to use icomoon based graphic
     *
     * @return string The HTML markup for the email item link
     */
    public static function email($watchdog, $params, $params = array(), $legacy = false)
    {
        JLoader::register('MailtoHelper', JPATH_SITE.'/components/com_mailto/helpers/mailto.php');

        $uri = JUri::getInstance();
        $base = $uri->toString(array('scheme', 'host', 'port'));
        $template = JFactory::getApplication()->getTemplate();
        $link = $base.JRoute::_(LogmoniterHelperRoute::getWatchdogRoute($watchdog->slug, $watchdog->catid, $watchdog->language), false);
        $url = 'index.php?option=com_mailto&tmpl=component&template='.$template.'&link='.MailtoHelper::addLink($link);

        $status = 'width=400,height=350,menubar=yes,resizable=yes';

        $text = JLayoutHelper::render('joomla.content.icons.email', array('params' => $params, 'legacy' => $legacy));

        $params['title'] = JText::_('JGLOBAL_EMAIL_TITLE');
        $params['onclick'] = "window.open(this.href,'win2','".$status."'); return false;";
        $params['rel'] = 'nofollow';

        return JHtml::_('link', JRoute::_($url), $text, $params);
    }

    /**
     * Display an edit icon for the watchdog.
     *
     * This icon will not display in a popup window, nor if the watchdog is trashed.
     * Edit access checks must be performed in the calling code.
     *
     * @param object   $watchdog The watchdog information
     * @param Registry $params   The item parameters
     * @param array    $params   Optional attributes for the link
     * @param bool     $legacy   True to use legacy images, false to use icomoon based graphic
     *
     * @return string the HTML for the watchdog edit icon
     *
     * @since   1.6
     */
    public static function edit($watchdog, $params, $params = array(), $legacy = false)
    {
        $user = JFactory::getUser();
        $uri = JUri::getInstance();

        // Ignore if in a popup window.
        if ($params && $params->get('popup')) {
            return;
        }

        // Ignore if the state is negative (trashed).
        if ($watchdog->state < 0) {
            return;
        }

        // Show checked_out icon if the watchdog is checked out by a different user
        if (property_exists($watchdog, 'checked_out')
            && property_exists($watchdog, 'checked_out_time')
            && $watchdog->checked_out > 0
            && $watchdog->checked_out != $user->get('id')) {
            $checkoutUser = JFactory::getUser($watchdog->checked_out);
            $date = JHtml::_('date', $watchdog->checked_out_time);
            $tooltip = JText::_('JLIB_HTML_CHECKED_OUT').' :: '.JText::sprintf('COM_CONTENT_CHECKED_OUT_BY', $checkoutUser->name)
                .' <br /> '.$date;

            $text = JLayoutHelper::render('joomla.content.icons.edit_lock', array('tooltip' => $tooltip, 'legacy' => $legacy));

            $output = JHtml::_('link', '#', $text, $params);

            return $output;
        }

        $contentUrl = LogmoniterHelperRoute::getWatchdogRoute($watchdog->slug, $watchdog->catid, $watchdog->language);
        $url = $contentUrl.'&task=watchdog.edit&w_id='.$watchdog->id.'&return='.base64_encode($uri);

        if ($watchdog->state == 0) {
            $overlib = JText::_('JUNPUBLISHED');
        } else {
            $overlib = JText::_('JPUBLISHED');
        }

        $date = JHtml::_('date', $watchdog->created);
        $author = $watchdog->created_by_alias ?: $watchdog->author;

        $overlib .= '&lt;br /&gt;';
        $overlib .= $date;
        $overlib .= '&lt;br /&gt;';
        $overlib .= JText::sprintf('COM_CONTENT_WRITTEN_BY', htmlspecialchars($author, ENT_COMPAT, 'UTF-8'));

        $text = JLayoutHelper::render('joomla.content.icons.edit', array('watchdog' => $watchdog, 'overlib' => $overlib, 'legacy' => $legacy));

        $params['title'] = JText::_('JGLOBAL_EDIT_TITLE');
        $output = JHtml::_('link', JRoute::_($url), $text, $params);

        return $output;
    }

    /**
     * Method to generate a popup link to print an watchdog.
     *
     * @param object   $watchdog The watchdog information
     * @param Registry $params   The item parameters
     * @param array    $params   Optional attributes for the link
     * @param bool     $legacy   True to use legacy images, false to use icomoon based graphic
     *
     * @return string The HTML markup for the popup link
     */
    public static function print_popup($watchdog, $params, $params = array(), $legacy = false)
    {
        $app = JFactory::getApplication();
        $input = $app->input;
        $request = $input->request;

        $url = LogmoniterHelperRoute::getWatchdogRoute($watchdog->slug, $watchdog->catid, $watchdog->language);
        $url .= '&tmpl=component&print=1&layout=default&page='.@$request->limitstart;

        $status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';

        $text = JLayoutHelper::render('joomla.content.icons.print_popup', array('params' => $params, 'legacy' => $legacy));

        $params['title'] = JText::sprintf('JGLOBAL_PRINT_TITLE', htmlspecialchars($watchdog->title, ENT_QUOTES, 'UTF-8'));
        $params['onclick'] = "window.open(this.href,'win2','".$status."'); return false;";
        $params['rel'] = 'nofollow';

        return JHtml::_('link', JRoute::_($url), $text, $params);
    }

    /**
     * Method to generate a link to print an watchdog.
     *
     * @param object   $watchdog Not used, @deprecated for 4.0
     * @param Registry $params   The item parameters
     * @param array    $params   Not used, @deprecated for 4.0
     * @param bool     $legacy   True to use legacy images, false to use icomoon based graphic
     *
     * @return string The HTML markup for the popup link
     */
    public static function print_screen($watchdog, $params, $params = array(), $legacy = false)
    {
        $text = JLayoutHelper::render('joomla.content.icons.print_screen', array('params' => $params, 'legacy' => $legacy));

        return '<a href="#" onclick="window.print();return false;">'.$text.'</a>';
    }
}
