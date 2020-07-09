<?php
/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Weblinks helper.
 *
 * @since  1.6
 */
class LogbookHelper extends JHelperContent
{
    /**
     * Configure the Linkbar.
     *
     * @param string $vName the name of the active view
     *
     * @since   1.6
     */
    public static function addSubmenu($vName = 'logbook')
    {
        JHtmlSidebar::addEntry(
            JText::_('COM_LOGBOOK_SUBMENU_LOGS'),
            'index.php?option=com_logbook&view=logs',
            $vName == 'logs'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_LOGBOOK_SUBMENU_CATEGORIES'),
            'index.php?option=com_categories&extension=com_logbook',
            $vName == 'categories'
        );
        JHtmlSidebar::addEntry(
            JText::_('COM_LOGBOOK_SUBMENU_LOCATIONS'),
            'index.php?option=com_logbook&view=locations',
            $vName == 'locations'
        );
        JHtmlSidebar::addEntry(
            JText::_('COM_LOGBOOK_SUBMENU_BLUEPRINTS'),
            'index.php?option=com_logbook&view=bluprints',
            $vName == 'blueprints'
        );
        JHtmlSidebar::addEntry(
            JText::_('COM_LOGBOOK_SUBMENU_SCHEDULES'),
            'index.php?option=com_logbook&view=schedules',
            $vName == 'schedules'
        );

        if (JComponentHelper::isEnabled('com_fields') && JComponentHelper::getParams('com_logbook')->get('custom_fields_enable', '1')) {
            JHtmlSidebar::addEntry(
                JText::_('JGLOBAL_FIELDS'),
                'index.php?option=com_fields&context=com_logbook.weblink',
                $vName == 'fields.fields'
            );

            JHtmlSidebar::addEntry(
                JText::_('JGLOBAL_FIELD_GROUPS'),
                'index.php?option=com_fields&view=groups&context=com_logbook.weblink',
                $vName == 'fields.groups'
            );
        }
    }

    /**
     * Adds Count Items for WebLinks Category Manager.
     *
     * @param stdClass[] &$items The logs category objects
     *
     * @return stdClass[] the logs category objects
     *
     * @since   3.6.0
     */
    public static function countItems(&$items)
    {
        $db = JFactory::getDbo();

        foreach ($items as $item) {
            $item->count_trashed = 0;
            $item->count_archived = 0;
            $item->count_unpublished = 0;
            $item->count_published = 0;

            $query = $db->getQuery(true)
                ->select('state, COUNT(*) AS count')
                ->from($db->qn('#__logs'))
                ->where($db->qn('catid').' = '.(int) $item->id)
                ->group('state');

            $db->setQuery($query);
            $logs = $db->loadObjectList();

            foreach ($logs as $weblink) {
                if ($weblink->state == 1) {
                    $item->count_published = $weblink->count;
                } elseif ($weblink->state == 0) {
                    $item->count_unpublished = $weblink->count;
                } elseif ($weblink->state == 2) {
                    $item->count_archived = $weblink->count;
                } elseif ($weblink->state == -2) {
                    $item->count_trashed = $weblink->count;
                }
            }
        }

        return $items;
    }

    /**
     * Adds Count Items for Tag Manager.
     *
     * @param stdClass[] &$items    The weblink tag objects
     * @param string     $extension the name of the active view
     *
     * @return stdClass[]
     *
     * @since   3.7.0
     */
    public static function countTagItems(&$items, $extension)
    {
        $db = JFactory::getDbo();

        foreach ($items as $item) {
            $item->count_trashed = 0;
            $item->count_archived = 0;
            $item->count_unpublished = 0;
            $item->count_published = 0;

            $query = $db->getQuery(true);
            $query->select('published as state, count(*) AS count')
                ->from($db->qn('#__contentitem_tag_map').'AS ct ')
                ->where('ct.tag_id = '.(int) $item->id)
                ->where('ct.type_alias ='.$db->q($extension))
                ->join('LEFT', $db->qn('#__categories').' AS c ON ct.content_item_id=c.id')
                ->group('state');

            $db->setQuery($query);
            $logs = $db->loadObjectList();

            foreach ($logs as $weblink) {
                if ($weblink->state == 1) {
                    $item->count_published = $weblink->count;
                }
                if ($weblink->state == 0) {
                    $item->count_unpublished = $weblink->count;
                }
                if ($weblink->state == 2) {
                    $item->count_archived = $weblink->count;
                }
                if ($weblink->state == -2) {
                    $item->count_trashed = $weblink->count;
                }
            }
        }

        return $items;
    }
}
