<?php
/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JLoader::register('LogbookHelper', JPATH_ADMINISTRATOR.'/components/com_logbook/helpers/logbook.php');

/**
 * Log HTML helper class.
 *
 * @since  __DELPOY_VERSION__
 */
abstract class JHtmlLog
{
    /**
     * Get the associated language flags.
     *
     * @param int $logid The item id to search associations
     *
     * @return string The language HTML
     *
     * @throws Exception
     *
     * @since   ___DEPLOY_VERSION__
     */
    public static function association($logid)
    {
        // Defaults
        $html = '';
        $associations = JLanguageAssociations::getAssociations('com_logbook', '#__logbook_logs', 'com_logbook.item', $logid);

        // Get the associations
        if ($associations) {
            foreach ($associations as $tag => $associated) {
                $associations[$tag] = (int) $associated->id;
            }

            // Get the associated logbook items
            $db = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('c.id, c.title as title')
                ->select('l.sef as lang_sef, lang_code')
                ->from('#__logbook_logs as c')
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
                    $text = strtoupper($item->lang_sef);
                    $url = JRoute::_('index.php?option=com_logbook&task=log.edit&id='.(int) $item->id);

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
