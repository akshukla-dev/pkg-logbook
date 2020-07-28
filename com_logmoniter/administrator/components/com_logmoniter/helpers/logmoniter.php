<?php
/**
 * @copyright Copyright (c)2020 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 */
defined('_JEXEC') or die; //No direct access to this file.

/**
 * Logmoniter helper.
 *
 * @since  1.6
 */
class LogmoniterHelper extends JHelperContent
{
    /**
     * Configure the Linkbar.
     *
     * @param string $vName the name of the active view
     *
     * @since   1.6
     */
    public static function addSubmenu($viewName)
    {
        JHtmlSidebar::addEntry(
            JText::_('COM_LOGMONITER_SUBMENU_WATCHDOGS'),
            'index.php?option=com_logmoniter&view=watchdogs',
            $viewName == 'watchdogs'
        );
        JHtmlSidebar::addEntry(
            JText::_('COM_LOGMONITER_SUBMENU_CATEGORIES'),
            'index.php?option=com_categories&extension=com_logmoniter',
            $viewName == 'categories'
        );
    }

    /**
     * Adds Count Items for Category Manager.
     *
     * @param stdClass[] &$items The banner category objects
     *
     * @return stdClass[]
     *
     * @since   3.5
     */
    public static function countItems(&$items)
    {
        $db = JFactory::getDbo();

        foreach ($items as $item) {
            $item->count_trashed = 0;
            $item->count_archived = 0;
            $item->count_unpublished = 0;
            $item->count_published = 0;

            $query = $db->getQuery(true);
            $query->select('state, count(*) AS count')
                  ->from($db->qn('#__logbook_watchdogs'))
                  ->where('catid = '.(int) $item->id)
                                                                        ->group('state');
            $db->setQuery($query);
            $records = $db->loadObjectList();

            foreach ($records as $record) {
                if ($record->state == 1) {
                    $item->count_published = $record->count;
                }

                if ($record->state == 0) {
                    $item->count_unpublished = $record->count;
                }

                if ($record->state == 2) {
                    $item->count_archived = $record->count;
                }

                if ($record->state == -2) {
                    $item->count_trashed = $record->count;
                }
            }
        }

        return $items;
    }

    /**
     * Adds Count Items for Tag Manager.
     *
     * @param stdClass[] &$items    The content objects
     * @param string     $extension the name of the active view
     *
     * @return stdClass[]
     *
     * @since   3.6
     */
    public static function countTagItems(&$items, $extension)
    {
        $db = JFactory::getDbo();
        $parts = explode('.', $extension);
        $section = null;

        if (count($parts) > 1) {
            $section = $parts[1];
        }

        $join = $db->qn('#__logbook_watchdogs').' AS c ON ct.content_item_id=c.id';
        $state = 'state';

        if ($section === 'category') {
            $join = $db->qn('#__categories').' AS c ON ct.content_item_id=c.id';
            $state = 'published as state';
        }

        foreach ($items as $item) {
            $item->count_trashed = 0;
            $item->count_archived = 0;
            $item->count_unpublished = 0;
            $item->count_published = 0;
            $query = $db->getQuery(true);
            $query->select($state.', count(*) AS count')
                ->from($db->qn('#__contentitem_tag_map').'AS ct ')
                ->where('ct.tag_id = '.(int) $item->id)
                ->where('ct.type_alias ='.$db->q($extension))
                ->join('LEFT', $join)
                ->group('state');
            $db->setQuery($query);
            $contents = $db->loadObjectList();

            foreach ($contents as $content) {
                if ($content->state == 1) {
                    $item->count_published = $content->count;
                }

                if ($content->state == 0) {
                    $item->count_unpublished = $content->count;
                }

                if ($content->state == 2) {
                    $item->count_archived = $content->count;
                }

                if ($content->state == -2) {
                    $item->count_trashed = $content->count;
                }
            }
        }

        return $items;
    }

    /*
     * Returns a valid section for watchdogs. If it is not valid then null
     * is returned.
     *
     * @param string $section The section to get the mapping for
     *
     * @return string|null The new section
     *
     * @since   3.7.0
     */
    /*public static function validateSection($section)
    {
        if (JFactory::getApplication()->isClient('site')) {
            // On the front end we need to map some sections
            switch ($section) {
        // Editing a watchdog.
        case 'form':

        // Category list view
        case 'category':
          $section = 'watchdog';
      }
        }

        if ($section != 'watchdog') {
            // We don't know other sections
            return null;
        }

        return $section;
    }

    /**
     * Returns valid contexts.
     *
     * @return array
     *
     * @since   3.7.0
     */
    /*public static function getContexts()
    {
        JFactory::getLanguage()->load('com_logmoniter', JPATH_ADMINISTRATOR);

        $contexts = array(
      'com_logmoniter.watchdog' => JText::_('COM_LOGMONITER'),
      'com_logmoniter.categories' => JText::_('JCATEGORY'),
    );

        return $contexts;
    }*/
}
