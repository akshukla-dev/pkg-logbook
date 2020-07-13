<?php
/**
 * @copyright Copyright (c)2020 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */
defined('_JEXEC') or die; //No direct access to this file.

use Joomla\Utilities\ArrayHelper;

/**
 * Methods supporting a list of log records.
 *
 * @since  1.6
 */
class LogbookModelLogs extends JModelList
{
    /**
     * Constructor.
     *
     * @param	array	an optional associative array of configuration settings
     *
     * @see		JController
     * @since	1.6
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
          'id', 'l.id',
          'title', 'l.title',
          'alias', 'l.alias',
          'checked_out', 'l.checked_out',
          'checked_out_time', 'l.checked_out_time',
          'catid', 'l.catid', 'category_title',
          'author', 'l.author',
          'folder_id', 'l.folder_id',
          'state', 'l.state',
          'access', 'l.access', 'access_level',
          'created', 'l.created',
          'modified', 'l.modified',
          'created_by', 'l.created_by',
          'created_by_alias', 'a.created_by_alias',
          'ordering', 'l.ordering',
          'hits', 'l.hits',
          'downloads', 'l.downloads',
          'publish_up', 'l.publish_up',
          'publish_down', 'l.publish_down',
          'published', 'l.published',
          'category_id', 'tag',
          'user_id',
      );
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * This method should only be called once per instantiation and is designed
     * to be called on the first call to the getState() method unless the model
     * configuration flag to ignore the request is set.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param string $ordering  an optional ordering field
     * @param string $direction an optional direction (asc|desc)
     *
     * @since   1.6
     */
    protected function populateState($ordering = 'l.id', $direction = 'desc')
    {
        // Initialise variables.
        $app = JFactory::getApplication();
        $session = JFactory::getSession();

        // Adjust the context to support modal layouts.
        //if ($layout = JFactory::getApplication()->input->get('layout')) {
        // $this->context .= '.'.$layout;
        //}

        //Get the state values set by the user.
        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $access = $this->getUserStateFromRequest($this->context.'.filter.access', 'filter_access');
        $this->setState('filter.access', $access);

        $userId = $app->getUserStateFromRequest($this->context.'.filter.user_id', 'filter_user_id');
        $this->setState('filter.user_id', $userId);

        $published = $this->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);
        //Filter for the component categories.
        $categoryId = $this->getUserStateFromRequest($this->context.'.filter.category_id', 'filter_category_id');
        $this->setState('filter.category_id', $categoryId);
        //Filter for the Logbook folders.
        $folderId = $this->getUserStateFromRequest($this->context.'.filter.folder_id', 'filter_folder_id');
        $this->setState('filter.folder_id', $folderId);

        $language = $this->getUserStateFromRequest($this->context.'.filter.language', 'filter_language', '');
        $this->setState('filter.language', $language);

        $tag = $this->getUserStateFromRequest($this->context.'.filter.tag', 'filter_tag', '');
        $this->setState('filter.tag', $tag);

        // List state information.
        parent::populateState($ordering, $direction);
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param string $id a prefix for the store id
     *
     * @return string a store id
     *
     * @since	1.6
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':'.$this->getState('filter.search');
        $id .= ':'.$this->getState('filter.access');
        $id .= ':'.$this->getState('filter.published');
        $id .= ':'.$this->getState('filter.category_id');
        $id .= ':'.$this->getState('filter.user_id');
        $id .= ':'.$this->getState('filter.language');

        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data.These datas are retrieved in.
     * the view with the getItems function.
     *
     * @return JDatabaseQuery
     *
     * @since   1.6
     */
    protected function getListQuery()
    {
        //Create a new JDatabaseQuery object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $user = JFactory::getUser();

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'l.id, l.catid, l.title, l.alias, l.downloads, l.hits, l.author,'.
                    'l.folder_id, l.state, l.access, l.ordering,'.
                    'l.publish_up, l.publish_down, l.created, l.modified,'.
                    'l.created_by, l.created_by_alias'
            )
        );

        $query->from('#__logbook_logs AS l');

        // Join over the language
        //$query->select('l.title AS language_title');
        //$query->join('LEFT', '`#__languages` AS l ON l.lang_code = l.language');

        // Join over the asset groups.
        $query->select('ag.title AS access_level');
        $query->join('LEFT', '#__viewlevels AS ag ON ag.id = l.access');

        // Join over the users for the checked out user.
        $query->select('uc.name AS editor');
        $query->join('LEFT', '#__users AS uc ON uc.id=l.checked_out');

        //Get the category name.
        $query->select('c.title AS category_title');
        $query->join('LEFT OUTER', '#__categories AS c ON c.id = l.catid');

        // Join over the users for the uploaded_by.
        $query->select('ua.name AS uploaded_by')->join('LEFT', '#__users AS ua ON ua.id = l.created_by');

        // Filter by access level.
        if ($access = $this->getState('filter.access')) {
            $query->where('l.access = '.(int) $access);
        }

        // Filter by access level on categories.
        if (!$user->authorise('core.admin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('l.access IN ('.$groups.')');
            $query->where('l.access IN ('.$groups.')');
        }

        // Filter by published state
        $published = $this->getState('filter.published');

        if (is_numeric($published)) {
            $query->where('l.state = '.(int) $published);
        } elseif ($published === '') {
            $query->where('(l.state = 0 OR l.state = 1)');
        }

        // Filter by a single or group of categories.
        $baselevel = 1;
        $categoryId = $this->getState('filter.category_id');
        if (is_numeric($categoryId)) {
            $categoryTable = JTable::getInstance('Category', 'JTable');
            $categoryTable->load($categoryId);
            $rgt = $categoryTable->rgt;
            $lft = $categoryTable->lft;
            $baselevel = (int) $categoryTable->level;
            $query->where('c.lft >= '.(int) $lft)
                ->where('c.rgt <= '.(int) $rgt);
        } elseif (is_array($categoryId)) {
            $query->where('l.catid IN ('.implode(',', ArrayHelper::toInteger($categoryId)).')');
        }

        //Filter by folder.
        $folderId = $this->getState('filter.folder_id');
        if (is_numeric($folderId)) {
            $type = $this->getState('filter.folder_id.include', true) ? '= ' : '<>';
            $query->where('l.folder_id'.$type.(int) $folderId);
        }

        // Filter by search in title.
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('l.id = '.(int) substr($search, 3));
            } elseif (stripos($search, 'Uploaded_by:') === 0) {
                $search = $db->quote('%'.$db->escape(substr($search, 7), true).'%');
                $query->where('(ua.name LIKE '.$search.' OR ua.username LIKE '.$search.')');
            } else {
                $search = $db->quote('%'.str_replace(' ', '%', $db->escape(trim($search), true).'%'));
                $query->where('(l.title LIKE '.$search.' OR l.alias LIKE '.$search.')');
            }
        }

        //Filter by publication state.
        $published = $this->getState('filter.published');
        if (is_numeric($published)) {
            $query->where('l.published = '.(int) $published);
        } elseif ($published === '') {
            $query->where('(l.published IN (0, 1))');
        }

        //Filter by user.
        $userId = $this->getState('filter.user_id');
        if (is_numeric($userId)) {
            $type = $this->getState('filter.user_id.include', true) ? '= ' : '<>';
            $query->where('l.created_by'.$type.(int) $userId);
        }

        // Filter by a single tag.
        $tagId = $this->getState('filter.tag');

        if (is_numeric($tagId)) {
            $query->where($db->quoteName('tagmap.tag_id').' = '.(int) $tagId)
                ->join(
                    'LEFT',
                    $db->quoteName('#__logbookitem_tag_map', 'tagmap').
                       ' ON '.$db->quoteName('tagmap.logbook_item_id').' = '.$db->quoteName('l.id').
                           ' AND '.$db->quoteName('tagmap.type_alias').' = '.$db->quote('com_lrm.document')
                );
        }

        //Filter by language.
        if ($language = $this->getState('filter.language')) {
            $query->where('l.language = '.$db->quote($language));
        }

        //Add the list to the sort.
        $orderCol = $this->state->get('list.ordering', 'l.id');
        $orderDirn = $this->state->get('list.direction', 'DESC'); //asc or desc

        if ($orderCol == 'l.ordering' || $orderCol == 'category_title') {
            $orderCol = 'c.title '.$orderDirn.', l.ordering';
        }

        //sqlsrv change
        if ($orderCol == 'language') {
            $orderCol = 'l.title';
        }

        if ($orderCol == 'access_level') {
            $orderCol = 'ag.title';
        }

        $query->order($db->escape($orderCol).' '.$db->escape($orderDirn));

        return $query;
    }

    /**
     * Build a list of authors.
     *
     * @return stdClass
     *
     * @since   1.6
     */
    public function getAuthors()
    {
        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Construct the query
        $query->select('u.id AS value, u.name AS text')
            ->from('#__users AS u')
            ->join('INNER', '#__logbook_logs AS c ON c.created_by = u.id')
            ->group('u.id, u.name')
            ->order('u.name');

        // Setup the query
        $db->setQuery($query);

        // Return the result
        return $db->loadObjectList();
    }

    /**
     * Method to get a list of logs.
     * Overridden to add a check for access levels.
     *
     * @return mixed an array of data items on success, false on failure
     *
     * @since   1.6.1
     */
    public function getItems()
    {
        $items = parent::getItems();

        if (JFactory::getApplication()->isClient('site')) {
            $groups = JFactory::getUser()->getAuthorisedViewLevels();

            foreach (array_keys($items) as $x) {
                // Check the access level. Remove logs the user shouldn't see
                if (!in_array($items[$x]->access, $groups)) {
                    unset($items[$x]);
                }
            }
        }

        return $items;
    }
}
