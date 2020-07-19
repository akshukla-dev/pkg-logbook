<?php
/**
 * @copyright Copyright (c)2020 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 */
defined('_JEXEC') or die;

require_once JPATH_COMPONENT_SITE.'/helpers/query.php';

/**
 * Logmoniter Component Model.
 */
class LogmoniteriModelCategory extends JModelList
{
    /**
     * Category items data.
     *
     * @var array
     */
    protected $_item = null;

    protected $_watchdogs = null;

    protected $_siblings = null;

    protected $_children = null;

    protected $_parent = null;

    /**
     * The category that applies.
     *
     * @var object
     */
    protected $_category = null;

    /**
     * The list of other document categories.
     *
     * @var array
     */
    protected $_categories = null;

    /**
     * Method to get a list of items.
     *
     * @return mixed an array of objects on success, false on failure
     */

    /**
     * Constructor.
     *
     * @param   array  an optional associative array of configuration settings
     *
     * @see     JController
     * @since   1.6
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
          'id', 'w.id',
          'title', 'w.title',
          'created', 'w.created',
          'modified', 'w.modified',
          'published', 'w.published',
          'ordering', 'w.ordering',
          'publish_up', 'w.publish_up',
          'publish_down', 'w.publish_down',
          );
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since   1.6
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $app = JFactory::getApplication('site');

        //Get and set the current category id.
        $pk = $app->input->getInt('id');
        $this->setState('category.id', $pk);

        //getParams function return global parameters overrided by the menu parameters (if any).
        //Note: Some specific parameters of this menu are not returned.
        $params = $app->getParams();

        $menuParams = new JRegistry();

        //Get the menu with its specific parameters.
        if ($menu = $app->getMenu()->getActive()) {
            $menuParams->loadString($menu->params);
        }

        //Merge Global and Menu Item params into a new object.
        $mergedParams = clone $menuParams;
        $mergedParams->merge($params);

        // Load the parameters in the session.
        $this->setState('params', $mergedParams);

        // process show_noauth parameter

        //The user is not allowed to see the registered documents unless he has the proper view permissions.
        if (!$params->get('show_noauth')) {
            //Set the access filter to true. This way the SQL query checks against the user
            //view permissions and fetchs only the documents this user is allowed to see.
            $this->setState('filter.access', true);
        }
        //The user is allowed to see any of the registred documents.
        else {
            //The user is allowed to see all the documents or some of them.
            //All of the documents are returned and it's up to the
            //layout to deal with the access.
            $this->setState('filter.access', false);
        }

        // List state information
        //Get the number of documents to display per page.
        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'uint');
        $this->setState('list.limit', $limit);

        //Get the limitstart variable (used for the pagination) from the form variable.
        $limitstart = $app->input->get('limitstart', 0, 'uint');
        $this->setState('list.start', $limitstart);

        // Optional filter text
        $this->setState('list.filter', $app->input->getString('filter-search'));
        //Get the value of the select list and load it in the session.
        $this->setState('list.filter_ordering', $app->input->getString('filter-ordering'));

        //Get the current category id.
        $id = $app->input->get('id', 0, 'int');
        $this->setState('category.id', $id);

        $user = JFactory::getUser();
        $asset = 'com_logmoniter';

        if ($pk) {
            $asset .= '.category.'.$pk;
        }

        if ((!$user->authorise('core.edit.state', $asset)) && (!$user->authorise('core.edit', $asset))) {
            // limit to published for people who can't edit or edit.state.
            $this->setState('filter.published', 1);

            // Filter by start and end dates.
            $this->setState('filter.publish_date', true);
        } else {
            $this->setState('filter.published', array(0, 1, 2));
        }

        $this->setState('filter.language', JLanguageMultilang::isEnabled());
    }

    /**
     * Method to get a list of items.
     *
     * @return mixed an array of objects on success, false on failure
     */
    public function getItems()
    {
        // Invoke the parent getItems method (using the getListQuery method) to get the main list
        $items = parent::getItems();
        $input = JFactory::getApplication()->input;

        //Get some user data.
        $user = JFactory::getUser();
        $userId = $user->get('id');
        $guest = $user->get('guest');
        $groups = $user->getAuthorisedViewLevels();

        // Convert the params field into an object, saving original in _params
        foreach ($items as $item) {
            //Get the document parameters only.
            $documentParams = new JRegistry();
            $documentParams->loadString($item->params);
            //Set the params attribute, eg: the merged global and menu (if any) parameters set
            //in the populateState function.
            $item->params = clone $this->getState('params');

            //Default layout (list).
            // Merge all of the document params.
            //Note: Document params (if they are defined) override global/menu params.
            $item->params->merge($documentParams);


            // Compute the asset access permissions.
            // Technically guest could edit a document, but lets not check that to improve performance a little.
            if (!$guest) {
                $asset = 'com_logmoniter.watchdog.'.$item->id;

                // Check general edit permission first.
                if ($user->authorise('core.edit', $asset)) {
                    $item->params->set('access-edit', true);
                }
                // Now check if edit.own is available.
                elseif (!empty($userId) && $user->authorise('core.edit.own', $asset)) {
                    // Check for a valid user and that they are the owner.
                    if ($userId == $item->created_by) {
                        $item->params->set('access-edit', true);
                    }
                }
            }

            $access = $this->getState('filter.access');
            //Set the access view parameter.
            if ($access) {
                // If the access filter has been set, we already have only the documents this user can view.
                $item->params->set('access-view', true);
            } else { // If no access filter is set, the layout takes some responsibility for display of limited information.
                if ($item->catid == 0 || $item->category_access === null) {
                    //In case the document is not linked to a category, we just check permissions against the document access.
                    $item->params->set('access-view', in_array($item->access, $groups));
                } else {//Check the user permissions against the document access as well as the category access.
                    $item->params->set('access-view', in_array($item->access, $groups) && in_array($item->category_access, $groups));
                }
            }

            //Set the type of date to display, (default layout only).
            if ($this->getState('params')->get('list_show_date') && $this->getState('params')->get('order_date')) {
                switch ($this->getState('params')->get('order_date')) {
									case 'modified':
											$item->displayDate = $item->modified;
											break;

									case 'published':
											$item->displayDate = ($item->publish_up == 0) ? $item->created : $item->publish_up;
											break;

									default:
									case 'created':
											$item->displayDate = $item->created;
											break;
								}
            }

            // Get the tags
            $item->tags = new JHelperTags();
            $item->tags->getItemTags('com_logmoniter.watchdog', $item->id);
        }

        return $items;
    }

    /**
     * Method to build an SQL query to load the list data (document items).
     *
     * @return string An SQL query
     *
     * @since   1.6
     */
    protected function getListQuery()
    {
        $user = JFactory::getUser();
        $groups = implode(',', $user->getAuthorisedViewLevels());

        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Select required fields from the categories.
        $query->select($this->getState('list.select', 'w.*'))
      ->from($db->quoteName('#__logbook_watchdogs').' AS w');

        // Filter by category.
        if ($categoryId = $this->getState('category.id')) {
            $query->select('c.title AS category_title, c.path AS category_route, c.access AS category_access, c.alias AS category_alias')
        			->join('LEFT', '#__categories AS c ON c.id = d.catid')
        				->where('w.catid = '.(int) $categoryId);
        }

        // Join over the users.
        $query->select('u.name AS put_online_by')
      		->join('LEFT', '#__users AS u ON u.id = d.created_by');

        // Join over the asset groups.
        $query->select('ag.title AS access_level');
        $query->join('LEFT', '#__viewlevels AS ag ON ag.id = d.access');

        // Filter by access level.
        if ($access = $this->getState('filter.access')) {
            $query->where('w.access IN ('.$groups.')')
        		->where('c.access IN ('.$groups.')');
        }

        // Filter by state
        $state = $this->getState('filter.state');
        if (is_numeric($state)) {
            $query->where('w.published='.(int) $state);
        }

        //Do not show trashed or archived documents on the front-end.
        $query->where('w.published != -2');
        $query->where('w.published != 2');

        //Do not show unpublished documents to users who can't edit or edit.state.
        if ($this->getState('filter.published')) {
            $query->where('w.published != 0');
        }

        // Filter by start and end dates.
        $nullDate = $db->quote($db->getNullDate());
        $date = JFactory::getDate();
        $nowDate = $db->quote($date->toSql());

        //Do not show expired documents to users who can't edit or edit.state.
        if ($this->getState('filter.publish_date')) {
            $query->where('(d.publish_up = '.$nullDate.' OR d.publish_up <= '.$nowDate.')')
        			->where('(d.publish_down = '.$nullDate.' OR d.publish_down >= '.$nowDate.')');
        }

        // Filter by language
        if ($this->getState('filter.language')) {
            $query->where('w.language IN ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')');
        }

        // Filter by search in title
        $search = $this->getState('list.filter');
        //Get the field to search by.
        $field = $this->getState('params')->get('filter_field');
        if (!empty($search)) {
            $search = $db->quote('%'.$db->escape($search, true).'%');
            $query->where('(d.'.$field.' LIKE '.$search.')');
        }

        //Get the documents ordering by default set in the menu options. (Note: sec as secondary).
        $documentOrderBy = $this->getState('params')->get('orderby_sec', 'rdate');
        //If documents are sorted by date (ie: date, rdate), order_date defines
        //which type of date should be used (ie: created, modified or publish_up).
        $documentOrderDate = $this->getState('params')->get('order_date');
        //Get the field to use in the ORDER BY clause according to the orderby_sec option.
        $orderBy = OkeydocHelperQuery::orderbySecondary($documentOrderBy, $documentOrderDate);

        //Filter by order (eg: the selecte list set by the end user).
        $filterOrdering = $this->getState('list.filter_ordering');
        //If the end user has define an order, we override the ordering by default.
        if (!empty($filterOrdering)) {
            $orderBy = OkeydocHelperQuery::orderbySecondary($filterOrdering, $documentOrderDate);
        }

        $query->order($orderBy);

        return $query;
    }

    /**
     * Method to get category data for the current category.
     *
     * @param   int  An optional ID
     *
     * @return object
     *
     * @since   1.5
     */
    public function getCategory()
    {
        if (!is_object($this->_item)) {
            $app = JFactory::getApplication();
            $menu = $app->getMenu();
            $active = $menu->getActive();
            $params = new JRegistry();

            if ($active) {
                $params->loadString($active->params);
            }

            $options = array();
            $options['countItems'] = $params->get('show_cat_num_watchdogs', 1) || $params->get('show_empty_categories', 0);
            $categories = JCategories::getInstance('Okeydoc', $options);
            $this->_item = $categories->get($this->getState('category.id', 'root'));

            // Compute selected asset permissions.
            if (is_object($this->_item)) {
                $user = JFactory::getUser();
                $asset = 'com_logmoniter.category.'.$this->_item->id;

                // Check general create permission.
                if ($user->authorise('core.create', $asset)) {
                    $this->_item->getParams()->set('access-create', true);
                }

                $this->_children = $this->_item->getChildren();
                $this->_parent = false;

                if ($this->_item->getParent()) {
                    $this->_parent = $this->_item->getParent();
                }

                $this->_rightsibling = $this->_item->getSibling();
                $this->_leftsibling = $this->_item->getSibling(false);
            } else {
                $this->_children = false;
                $this->_parent = false;
            }
        }

        // Get the tags
        $this->_item->tags = new JHelperTags();
        $this->_item->tags->getItemTags('com_logmoniter.category', $this->_item->id);

        return $this->_item;
    }

    /**
     * Get the parent category.
     *
     * @param   int  An optional category id. If not supplied, the model state 'category.id' will be used.
     *
     * @return mixed an array of categories or false if an error occurs
     */
    public function getParent()
    {
        if (!is_object($this->_item)) {
            $this->getCategory();
        }

        return $this->_parent;
    }

    /**
     * Get the sibling (adjacent) categories.
     *
     * @return mixed an array of categories or false if an error occurs
     */
    public function &getLeftSibling()
    {
        if (!is_object($this->_item)) {
            $this->getCategory();
        }

        return $this->_leftsibling;
    }

    public function &getRightSibling()
    {
        if (!is_object($this->_item)) {
            $this->getCategory();
        }

        return $this->_rightsibling;
    }

    /**
     * Get the child categories.
     *
     * @param   int  An optional category id. If not supplied, the model state 'category.id' will be used.
     *
     * @return mixed an array of categories or false if an error occurs
     *
     * @since   1.6
     */
    public function &getChildren()
    {
        if (!is_object($this->_item)) {
            $this->getCategory();
        }

        // Order subcategories
        if (count($this->_children)) {
            $params = $this->getState()->get('params');

            if ($params->get('orderby_pri') == 'alpha' || $params->get('orderby_pri') == 'ralpha') {
                jimport('joomla.utilities.arrayhelper');
                JArrayHelper::sortObjects($this->_children, 'title', ($params->get('orderby_pri') == 'alpha') ? 1 : -1);
            }
        }

        return $this->_children;
    }

    /**
     * Increment the hit counter for the category.
     *
     * @param int $pk optional primary key of the category to increment
     *
     * @return bool true if successful; false otherwise and internal error set
     *
     * @since   3.2
     */
    public function hit($pk = 0)
    {
        $input = JFactory::getApplication()->input;
        $hitcount = $input->getInt('hitcount', 1);

        if ($hitcount) {
            $pk = (!empty($pk)) ? $pk : (int) $this->getState('category.id');

            $table = JTable::getInstance('Category', 'JTable');
            $table->load($pk);
            $table->hit($pk);
        }

        return true;
    }
}
