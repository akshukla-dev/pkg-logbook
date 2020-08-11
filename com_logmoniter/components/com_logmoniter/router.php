<?php
/**
 * @package LMI/LogManager/LogBook/LogMoniter
 * @copyright Copyright (c)2020 Amit Kumar Shukla. All Rights Reserved.
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */
defined('_JEXEC') or die;

/**
 * Routing class of com_logmoniter.
 *
 * @since  3.3
 */
class LogmoniterRouter extends JComponentRouterView
{
    protected $noIDs = false;

    /**
     * Logmoniter Component router constructor.
     *
     * @param JApplicationCms $app  The application object
     * @param JMenu           $menu The menu object to work with
     */
    public function __construct($app = null, $menu = null)
    {
        $params = JComponentHelper::getParams('com_logmoniter');
        $this->noIDs = (bool) $params->get('sef_ids');
        $categories = new JComponentRouterViewconfiguration('categories');
        $categories->setKey('id');
        $this->registerView($categories);
        $category = new JComponentRouterViewconfiguration('category');
        $category->setKey('id')->setParent($categories, 'catid')->setNestable()->addLayout('list');
        $this->registerView($category);
        $watchdog = new JComponentRouterViewconfiguration('watchdog');
        $watchdog->setKey('id')->setParent($category, 'catid');
        $this->registerView($watchdog);
        $form = new JComponentRouterViewconfiguration('form');
        $form->setKey('wd_id');
        $this->registerView($form);

        parent::__construct($app, $menu);

        $this->attachRule(new JComponentRouterRulesMenu($this));

        if ($params->get('sef_advanced', 0)) {
            $this->attachRule(new JComponentRouterRulesStandard($this));
            $this->attachRule(new JComponentRouterRulesNomenu($this));
        } else {
            JLoader::register('LogmoniterRouterRulesLegacy', __DIR__.'/helpers/legacyrouter.php');
            $this->attachRule(new LogmoniterRouterRulesLegacy($this));
        }
    }

    /**
     * Method to get the segment(s) for a category.
     *
     * @param string $id    ID of the category to retrieve the segments for
     * @param array  $query The request that is built right now
     *
     * @return array|string The segments of this item
     */
    public function getCategorySegment($id, $query)
    {
        $category = JCategories::getInstance($this->getName())->get($id);

        if ($category) {
            $path = array_reverse($category->getPath(), true);
            $path[0] = '1:root';

            if ($this->noIDs) {
                foreach ($path as &$segment) {
                    list($id, $segment) = explode(':', $segment, 2);
                }
            }

            return $path;
        }

        return array();
    }

    /**
     * Method to get the segment(s) for a category.
     *
     * @param string $id    ID of the category to retrieve the segments for
     * @param array  $query The request that is built right now
     *
     * @return array|string The segments of this item
     */
    public function getCategoriesSegment($id, $query)
    {
        return $this->getCategorySegment($id, $query);
    }

    /**
     * Method to get the segment(s) for an watchdog.
     *
     * @param string $id    ID of the watchdog to retrieve the segments for
     * @param array  $query The request that is built right now
     *
     * @return array|string The segments of this item
     */
    public function getWatchdogSegment($id, $query)
    {
        if (!strpos($id, ':')) {
            $db = JFactory::getDbo();
            $dbquery = $db->getQuery(true);
            $dbquery->select($dbquery->qn('alias'))
                ->from($dbquery->qn('#__logbook_watchdogs'))
                ->where('id = '.$dbquery->q($id));
            $db->setQuery($dbquery);

            $id .= ':'.$db->loadResult();
        }

        if ($this->noIDs) {
            list($void, $segment) = explode(':', $id, 2);

            return array($void => $segment);
        }

        return array((int) $id => $id);
    }

    /**
     * Method to get the segment(s) for an form.
     *
     * @param string $id    ID of the watchdog form to retrieve the segments for
     * @param array  $query The request that is built right now
     *
     * @return array|string The segments of this item
     *
     * @since   3.7.3
     */
    public function getFormSegment($id, $query)
    {
        return $this->getWatchdogSegment($id, $query);
    }

    /**
     * Method to get the id for a category.
     *
     * @param string $segment Segment to retrieve the ID for
     * @param array  $query   The request that is parsed right now
     *
     * @return mixed The id of this item or false
     */
    public function getCategoryId($segment, $query)
    {
        if (isset($query['id'])) {
            $category = JCategories::getInstance($this->getName())->get($query['id']);

            foreach ($category->getChildren() as $child) {
                if ($this->noIDs) {
                    if ($child->alias == $segment) {
                        return $child->id;
                    }
                } else {
                    if ($child->id == (int) $segment) {
                        return $child->id;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Method to get the segment(s) for a category.
     *
     * @param string $segment Segment to retrieve the ID for
     * @param array  $query   The request that is parsed right now
     *
     * @return mixed The id of this item or false
     */
    public function getCategoriesId($segment, $query)
    {
        return $this->getCategoryId($segment, $query);
    }

    /**
     * Method to get the segment(s) for an watchdog.
     *
     * @param string $segment Segment of the watchdog to retrieve the ID for
     * @param array  $query   The request that is parsed right now
     *
     * @return mixed The id of this item or false
     */
    public function getWatchdogId($segment, $query)
    {
        if ($this->noIDs) {
            $db = JFactory::getDbo();
            $dbquery = $db->getQuery(true);
            $dbquery->select($dbquery->qn('id'))
                ->from($dbquery->qn('#__logbook_watchdogs'))
                ->where('alias = '.$dbquery->q($segment))
                ->where('catid = '.$dbquery->q($query['id']));
            $db->setQuery($dbquery);

            return (int) $db->loadResult();
        }

        return (int) $segment;
    }
}

/**
 * Logmoniter router functions.
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
function logmoniterBuildRoute(&$query)
{
    $app = JFactory::getApplication();
    $router = new LogmoniterRouter($app, $app->getMenu());

    return $router->build($query);
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
function logmoniterParseRoute($segments)
{
    $app = JFactory::getApplication();
    $router = new LogmoniterRouter($app, $app->getMenu());

    return $router->parse($segments);
}
