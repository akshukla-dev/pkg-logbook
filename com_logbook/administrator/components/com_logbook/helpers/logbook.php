<?php
/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Logbook Component helper.
 *
 * @since  1.6
 */
class LogbookHelper extends JHelperlogbook
{
    public static $extension = 'com_logbook';

    /**
     * Configure the Linkbar.
     *
     * @param string $vName the name of the active view
     *
     * @since   1.6
     */
    public static function addSubmenu($vName = 'logs')
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
            JText::_('COM_LOGBOOK_SUBMENU_FOLDERS'),
            'index.php?option=com_logbook&view=folders',
            $vName == 'folders'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_LOGBOOK_SUBMENU_FOLDERS'),
            'index.php?option=com_logbook&view=logrefs',
            $vName == 'logrefs'
        );
    }

    /**
     * Adds Count Items for Logbook Category Manager.
     *
     * @param stdClass[] &$items The banner category objects
     *
     * @return stdClass[]
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
                ->from($db->qn('#__logbook_logs'))
                ->where($db->qn('catid').' = '.(int) $item->id)
                ->group('state');

            $db->setQuery($query);
            $logs = $db->loadObjectList();

            foreach ($logs as $log) {
                if ($log->state == 1) {
                    $item->count_published = $log->count;
                } elseif ($log->state == 0) {
                    $item->count_unpublished = $log->count;
                } elseif ($log->state == 2) {
                    $item->count_archived = $log->count;
                } elseif ($log->state == -2) {
                    $item->count_trashed = $log->count;
                }
            }
        }

        return $items;
    }

    /**
     * Adds Count Items for Tag Manager.
     *
     * @param stdClass[] &$items    The Logbook objects
     * @param string     $extension the name of the active view
     *
     * @return stdClass[]
     *
     * @since   3.7.0
     */
    public static function countTagItems(&$items, $extension)
    {
        $db = JFactory::getDbo();
        $parts = explode('.', $extension);
        $section = null;

        if (count($parts) > 1) {
            $section = $parts[1];
        }

        $join = $db->qn('#__logbook_logs').' AS c ON ct.logbook_item_id=c.id';
        $state = 'state';

        if ($section === 'category') {
            $join = $db->qn('#__categories').' AS c ON ct.logbook_item_id=c.id';
            $state = 'published as state';
        }

        foreach ($items as $item) {
            $item->count_trashed = 0;
            $item->count_archived = 0;
            $item->count_unpublished = 0;
            $item->count_published = 0;

            $query = $db->getQuery(true);
            $query->select($state.', count(*) AS count')
                ->from($db->qn('#__logbookitem_tag_map').'AS ct ')
                ->where('ct.tag_id = '.(int) $item->id)
                ->where('ct.type_alias ='.$db->q($extension))
                ->join('LEFT', $join)
                ->group('state');

            $db->setQuery($query);
            $logs = $db->loadObjectList();

            foreach ($logs as $log) {
                if ($log->state == 1) {
                    $item->count_published = $log->count;
                }
                if ($log->state == 0) {
                    $item->count_unpublished = $log->count;
                }
                if ($log->state == 2) {
                    $item->count_archived = $log->count;
                }
                if ($log->state == -2) {
                    $item->count_trashed = $log->count;
                }
            }
        }

        return $items;
    }

    /**
     * Get the list of the allowed actions for the user.
     *
     * @return array
     *
     * @since   1.0.0
     */
    public static function getActions($categoryId = 0)
    {
        $user = JFactory::getUser();
        $result = new JObject();

        if (empty($categoryId)) {
            //Check permissions against the component.
            $assetName = 'com_logbook';
        } else {
            //Check permissions against the component category.
            $assetName = 'com_logbook.category.'.(int) $categoryId;
        }

        $actions = array('core.admin', 'core.manage', 'core.create', 'core.edit',
                'core.edit.own', 'core.edit.state', 'core.delete', );

        //Get from the core the user's permission for each action.
        foreach ($actions as $action) {
            $result->set($action, $user->authorise($action, $assetName));
        }

        return $result;
    }

    /**
     * Return categories in which the user is allowed to do a given action. ("create" by default).
     *
     * @return array
     *
     * @since   1.0.0
     */
    public static function getUserCategories($action = 'create', $logs = false)
    {
        $subquery = '';
        if ($logs) {
            //Get the number of document items linked to each category.
            $subquery = ',(SELECT COUNT(*) FROM #__logbook_logs WHERE catid=c.id) AS logs';
        }

        //Get the component categories.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('c.id, c.level, c.parent_id, c.title'.$subquery);
        $query->from('#__categories AS c');
        $query->where('extension="com_logbook"');
        $query->order('c.lft ASC');
        $db->setQuery($query);
        $categories = $db->loadObjectList();

        $userCategories = array();

        if ($categories) {
            foreach ($categories as $category) {
                //Get the list of the actions allowed for the user on this category.
                $canDo = LogbookHelper::getActions($category->id);

                if ($canDo->get('core.'.$action)) {
                    $userCategories[] = $category;
                    //$userCategories[] = array('id' => $category->id, 'title' => $category->title);
                }
            }
        }

        return $userCategories;
    }

    /**
     * Load file on the server and return an array filled with the data file.
     *
     * @return array()
     *
     * @since 1.0.0
     */
    public static function uploadFile($catid)
    {
        //Array to store the file data. Set an error index for a possible error message.
        $log = array('error' => '');

        //Get the name and the id of the destination folder.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('f.title, m.folder_id');
        $query->from('#__logbook_folders AS f');
        $query->join('LEFT', '#__logbook_folder_map AS m ON m.catid='.(int) $catid);
        $query->where('f.id=m.folder_id');
        $db->setQuery($query);
        $destFolder = $db->loadObject();

        $jinput = JFactory::getApplication()->input;
        $uploaded_file = $jinput->uploaded_file->get('jform');
        $uploaded_file = $uploaded_file['uploaded_file'];

        //Get the component parameters:
        $params = JComponentHelper::getParams('com_logbook');
        //- The allowed extensions table
        $allowedExt = explode(';', $params->get('allowed_extensions'));
        // - The available extension icons table
        $iconsExt = explode(';', $params->get('extensions_list'));
        //- Allow or not all types of file.
        $allFiles = $params->get('all_files');
        //- The authorised file size (in megabyte) for upload.
        $maxFileSize = $params->get('max_file_size');
        //Convert in byte.
        $maxFileSize = $maxFileSize * 1048576;

        //Check if the file exists and if no error occurs.
        if ($uploaded_file['error'] == 0) {
            //Get the file extension and convert it to lowercase.
            $ext = strtolower(JFile::getExt($uploaded_file['name']));

            //Check if the extension is allowed.
            if (!in_array($ext, $allowedExt) && !$allFiles) {
                $log['error'] = 'COM_LOGBOOK_EXTENSION_NOT_ALLOWED';

                return $log;
            }

            //Check the size of the file.
            if ($uploaded_file['size'] > $maxFileSize) {
                $log['error'] = 'COM_LOGBOOK_FILE_SIZE_TOO_LARGE';

                return $log;
            }

            $count = 1;
            while ($count > 0) {
                //Create an unique id for this file.
                $file = uniqid();
                $file = $file.'.'.$ext;

                //To ensure it is unique check against the database.
                //If the id is not unique the loop goes on and a new id is generated.
                $query->clear();
                $query->select('COUNT(*)');
                $query->from('#__logbook_logs');
                $query->where('file='.$db->Quote($file));
                $db->setQuery($query);
                $count = (int) $db->loadResult();
            }

            //Get the file name without its extension.
            preg_match('#(.+)\.[a-zA-Z0-9\#?!$~@()-_]{1,}$#', $uploaded_file['name'], $matches);
            $fileName = $matches[1];

            //Sanitize the file name which will be used for downloading, (see stringURLSafe function for details).
            $fileName = JFilterOutput::stringURLSafe($fileName);

            //Note: So far the log root directory is unchangeable but who knows in a futur version..
            $docRootDir = 'logbook_logs';

            //Create a table containing all data about the file.
            $log['file'] = $file;
            $log['file_name'] = $fileName.'.'.$ext;
            $log['file_type'] = $uploaded_file['type'];
            $log['file_size'] = $uploaded_file['size'];
            $log['folder_id'] = $destFolder->folder_id; //id of the folder which will contain the file.
            //Build the file path.
            $log['file_path'] = $docRootDir.'/'.$destFolder->title;

            //To obtain the appropriate icon file name, we get the file extension then we concatenate it with .gif.
            //If the extension doesn't have any appropriate extension icon, we display the generic icon.
            if (!in_array($ext, $iconsExt)) {
                $log['file_icon'] = 'generic.gif';
            } else {
                $log['file_icon'] = $ext.'.gif';
            }

            //Move the file on the server.
            if (!JFile::upload($uploaded_file['tmp_name'], JPATH_ROOT.'/'.$docRootDir.'/'.$destFolder->title.'/'.$log['file'])) {
                $log['error'] = 'COM_LOGBOOK_FILE_TRANSFER_ERROR';

                return $log;
            }

            //File transfert has been successful.
            return $log;
        } else { //The upload of the file has failed.
            //Return the error which has occured.
            switch ($uploaded_file['error']) {
            case 1:
                $log['error'] = 'COM_LOGBOOK_FILES_ERROR_1';
            break;
            case 2:
            $log['error'] = 'COM_LOGBOOK_FILES_ERROR_2';
            break;
            case 3:
                $log['error'] = 'COM_LOGBOOK_FILES_ERROR_3';
            break;
            case 4:
                $log['error'] = 'COM_LOGBOOK_FILES_ERROR_4';
            break;
        }

            return $log;
        }
    }

    /**
     * Convert the number of bytes to kilo or mega bytes.
     *
     * @return string
     *
     * @since   1.0.0
     */
    public static function byteConverter($nbBytes)
    {
        $conversion = array();

        if ($nbBytes > 1023 && $nbBytes < 1048576) {  //Convert to kilobyte.
            $result = $nbBytes / 1024;
            $conversion['result'] = round($result, 2);
            $conversion['multiple'] = 'KILOBYTE';
        } elseif ($nbBytes > 1048575) { //Convert to megabyte.
            $result = $nbBytes / 1048576;
            $conversion['result'] = round($result, 2);
            $conversion['multiple'] = 'MEGABYTE';
        } else { //No convertion.
            $conversion['result'] = $nbBytes;
            $conversion['multiple'] = 'BYTE';
        }

        return $conversion;
    }

    /**
     * Returns valid contexts.
     *
     * @return array
     *
     * @since   1.0.0
     */
    public static function getContexts()
    {
        JFactory::getLanguage()->load('com_logbook', JPATH_ADMINISTRATOR);

        $contexts = array(
            'com_logbook.log' => JText::_('COM_LOGBOOK_LOG'),
            'com_logbook.location' => JText::_('COM_LOGBOOK_LOCATION'),
            'com_logbook.blueprint' => JText::_('COM_LOGBOOK_BLUEPRINT'),
            'com_logbook.logrefs' => JText::_('COM_LOGBOOK_LOGREF'),
            'com_logbook.folder' => JText::_('COM_LOGBOOK_fOLDER'),
            'com_logbook.categories' => JText::_('JCATEGORY'),
        );

        return $contexts;
    }
}
