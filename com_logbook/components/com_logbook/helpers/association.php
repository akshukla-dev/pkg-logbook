<?php
/**
 * @package LMI/LogManager/LogBook/LogMoniter
 * @copyright Copyright (c)2020 Amit Kumar Shukla. All Rights Reserved.
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */
defined('_JEXEC') or die;

JLoader::register('LogbookHelper', JPATH_ADMINISTRATOR.'/components/com_logbook/helpers/logbook.php');
JLoader::register('LogbookHelperRoute', JPATH_SITE.'/components/com_logbook/helpers/route.php');
JLoader::register('CategoryHelperAssociation', JPATH_ADMINISTRATOR.'/components/com_categories/helpers/association.php');

/**
 * Logbook Component Association Helper.
 *
 * @since  3.0
 */
abstract class LogbookHelperAssociation extends CategoryHelperAssociation
{
    /**
     * Method to get the associations for a given item.
     *
     * @param int    $id   Id of the item
     * @param string $view Name of the view
     *
     * @return array Array of associations for the item
     *
     * @since  3.0
     */
    public static function getAssociations($id = 0, $view = null)
    {
        $jinput = JFactory::getApplication()->input;
        $view = $view === null ? $jinput->get('view') : $view;
        $id = empty($id) ? $jinput->getInt('id') : $id;

        if ($view === 'log') {
            if ($id) {
                $associations = JLanguageAssociations::getAssociations('com_logbook', '#__logbook_logs', 'com_logbook.item', $id);

                $return = array();

                foreach ($associations as $tag => $item) {
                    $return[$tag] = LogbookHelperRoute::getLogRoute($item->id, (int) $item->catid, $item->language);
                }

                return $return;
            }
        }

        if ($view === 'category' || $view === 'categories') {
            return self::getCategoryAssociations($id, 'com_logbook');
        }

        return array();
    }

    /**
     * Method to display in frontend the associations for a given log.
     *
     * @param int $id Id of the log
     *
     * @return array An array containing the association URL and the related language object
     *
     * @since  3.7.0
     */
    public static function displayAssociations($id)
    {
        $return = array();

        if ($associations = self::getAssociations($id, 'log')) {
            $levels = JFactory::getUser()->getAuthorisedViewLevels();
            $languages = JLanguageHelper::getLanguages();

            foreach ($languages as $language) {
                // Do not display language when no association
                if (empty($associations[$language->lang_code])) {
                    continue;
                }

                // Do not display language without frontend UI
                if (!array_key_exists($language->lang_code, JLanguageHelper::getInstalledLanguages(0))) {
                    continue;
                }

                // Do not display language without specific home menu
                if (!array_key_exists($language->lang_code, JLanguageMultilang::getSiteHomePages())) {
                    continue;
                }

                // Do not display language without authorized access level
                if (isset($language->access) && $language->access && !in_array($language->access, $levels)) {
                    continue;
                }

                $return[$language->lang_code] = array('item' => $associations[$language->lang_code], 'language' => $language);
            }
        }

        return $return;
    }
}
