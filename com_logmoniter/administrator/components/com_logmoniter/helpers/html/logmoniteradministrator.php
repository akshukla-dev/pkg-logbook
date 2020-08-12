<?php
/**
 * @package LMI/LogManager/LogBook/LogMoniter
 * @copyright Copyright (c)2020 Amit Kumar Shukla. All Rights Reserved.
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */
defined('_JEXEC') or die;

JLoader::register('LogmoniterHelper', JPATH_ADMINISTRATOR.'/components/com_logmoniter/helpers/logmoniter.php');

/**
 * Logmoniter HTML helper.
 *
 * @since  3.0
 */
abstract class JHtmlLogmoniterAdministrator
{
    /**
     * Render the list of associated items.
     *
     * @param int $watchdogid The watchdog item id
     *
     * @return string The language HTML
     *
     * @throws Exception
     */
    public static function association($watchdogid)
    {
        // Defaults
        $html = '';

        // Get the associations
        if ($associations = JLanguageAssociations::getAssociations('com_logmoniter', '#__logbook_watchdogs', 'com_logmoniter.item', $watchdogid)) {
            foreach ($associations as $tag => $associated) {
                $associations[$tag] = (int) $associated->id;
            }

            // Get the associated menu items
            $db = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('c.*')
                ->select('l.sef as lang_sef')
                ->select('l.lang_code')
                ->from('#__logbook_watchdogs as c')
                ->select('cat.title as category_title')
                ->join('LEFT', '#__categories as cat ON cat.id=c.catid')
                ->where('c.id IN ('.implode(',', array_values($associations)).')')
                ->join('LEFT', '#__languages as l ON c.language=l.lang_code')
                ->select('l.image')
                ->select('l.title as language_title');
            $db->setQuery($query);

            try {
                $items = $db->loadObjectList('id');
            } catch (RuntimeException $e) {
                throw new Exception($e->getMessage(), 500, $e);
            }

            if ($items) {
                foreach ($items as &$item) {
                    $text = $item->lang_sef ? strtoupper($item->lang_sef) : 'XX';
                    $url = JRoute::_('index.php?option=com_logmoniter&task=watchdog.edit&id='.(int) $item->id);

                    $tooltip = htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8').'<br />'.JText::sprintf('JCATEGORY_SPRINTF', $item->category_title);
                    $classes = 'hasPopover label label-association label-'.$item->lang_sef;

                    $item->link = '<a href="'.$url.'" title="'.$item->language_title.'" class="'.$classes
                        .'" data-content="'.$tooltip.'" data-placement="top">'
                        .$text.'</a>';
                }
            }

            JHtml::_('bootstrap.popover');

            $html = JLayoutHelper::render('joomla.content.associations', $items);
        }

        return $html;
    }
}
