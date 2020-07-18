<?php
/**
 * @copyright Copyright (c)2017 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */
defined('_JEXEC') or die;

/**
 * Logbook Component Route Helper.
 *
 * @static
 *
 * @since       1.5
 */
abstract class LogbookHelperRoute
{
    protected static $lookup;

    protected static $lang_lookup = array();

    /**
     * @param   int  The route of the log
     */
    public static function getLogRoute($id, $wdid, $language = 0)
    {
        $needles = array('log' => array((int) $id));

        //Create the link
        $link = 'index.php?option=com_logbook&view=log&id='.$id;

        /*if($wdid > 1) {
          $watchdogs = JCategories::getInstance('Logbook');
          $watchdog = $watchdogs->get($wdid);

          if($watchdog) {
              $needles['watchdog'] = array_reverse($watchdog->getPath());
              $needles['watchdogs'] = $needles['watchdog'];
              $link .= '&wdid='.$wdid;
          }
        }

        if ($language && $language != '*' && JLanguageMultilang::isEnabled()) {
            self::buildLanguageLookup();

            if (isset(self::$lang_lookup[$language])) {
                $link .= '&lang='.self::$lang_lookup[$language];
                $needles['language'] = $language;
            }
        }*/

        if ($item = self::_findItem($needles)) {
            $link .= '&Itemid='.$item;
        }

        return $link;
    }

    /**
     * @param int    $id     the id of the log
     * @param string $return the return page variable
     */
    public static function getFormRoute($id, $return = null)
    {
        // Create the link.
        if ($id) {
            $link = 'index.php?option=com_logbook&task=log.edit&l_id='.$id;
        } else {
            $link = 'index.php?option=com_logbook&task=log.add&l_id=0';
        }

        if ($return) {
            $link .= '&return='.$return;
        }

        return $link;
    }

    /*public static function getCategoryRoute($wdid, $language = 0)
    {
      if($wdid instanceof JCategoryNode) {
        $id = $wdid->id;
        $watchdog = $wdid;
      }
      else {
        $id = (int) $wdid;
        $watchdog = JCategories::getInstance('Logbook')->get($id);
      }

      if($id < 1 || !($watchdog instanceof JCategoryNode)) {
        $link = '';
      }
      else {
        $needles = array();

        // Create the link
        $link = 'index.php?option=com_lrm&view=watchdog&id='.$id;

        $wdids = array_reverse($watchdog->getPath());
        $needles['watchdog'] = $wdids;
        $needles['watchdogs'] = $wdids;

        if($language && $language != "*" && JLanguageMultilang::isEnabled()) {
            self::buildLanguageLookup();

          if(isset(self::$lang_lookup[$language])) {
            $link .= '&lang=' . self::$lang_lookup[$language];
            $needles['language'] = $language;
          }
        }

        if ($item = self::_findItem($needles)) {
            $link .= '&Itemid='.$item;
        }
      }

      return $link;
    }

    protected static function buildLanguageLookup()
    {
        if (count(self::$lang_lookup) == 0) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true)
          ->select('a.sef AS sef')
          ->select('a.lang_code AS lang_code')
          ->from('#__languages AS a');

            $db->setQuery($query);
            $langs = $db->loadObjectList();

            foreach ($langs as $lang) {
                self::$lang_lookup[$lang->lang_code] = $lang->sef;
            }
        }
    }

    protected static function _findItem($needles = null)
    {
        $app = JFactory::getApplication();
        $menus = $app->getMenu('site');
        $language = isset($needles['language']) ? $needles['language'] : '*';

        // Prepare the reverse lookup array.
        if (!isset(self::$lookup[$language])) {
            self::$lookup[$language] = array();

            $component = JComponentHelper::getComponent('com_logbook');

            $attributes = array('component_id');
            $values = array($component->id);

            if ($language != '*') {
                $attributes[] = 'language';
                $values[] = array($needles['language'], '*');
            }

            $items = $menus->getItems($attributes, $values);

            if ($items) {
                foreach ($items as $item) {
                    if (isset($item->query) && isset($item->query['view'])) {
                        $view = $item->query['view'];
                        if (!isset(self::$lookup[$language][$view])) {
                            self::$lookup[$language][$view] = array();
                        }
                        if (isset($item->query['id'])) {
                            // here it will become a bit tricky
                            // language != * can override existing entries
                            // language == * cannot override existing entries
                            if (!isset(self::$lookup[$language][$view][$item->query['id']]) || $item->language != '*') {
                                self::$lookup[$language][$view][$item->query['id']] = $item->id;
                            }
                        }
                    }
                }
            }
        }

        if ($needles) {
            foreach ($needles as $view => $ids) {
                if (isset(self::$lookup[$language][$view])) {
                    foreach ($ids as $id) {
                        if (isset(self::$lookup[$language][$view][(int) $id])) {
                            return self::$lookup[$language][$view][(int) $id];
                        }
                    }
                }
            }
        }

        // Check if the active menuitem matches the requested language
        $active = $menus->getActive();
        if ($active && ($language == '*' || in_array($active->language, array('*', $language)) || !JLanguageMultilang::isEnabled())) {
            return $active->id;
        }

        // If not found, return language specific home link
        $default = $menus->getDefault($language);

        return !empty($default->id) ? $default->id : null;
    }*/
}
