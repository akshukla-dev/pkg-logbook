<?php
/**
 * @package LMI/LogManager/LogBook/LogMoniter
 * @copyright Copyright (c)2020 Amit Kumar Shukla. All Rights Reserved.
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */
defined('_JEXEC') or die;
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
use Joomla\CMS\Date\Date;



/**
 * Logbook Component helper.
 *
 * @since  1.6
 */
class LogbookHelper extends JHelperContent
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
            $viewName == 'categories'
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

        $join = $db->qn('#__logbook_logs').' AS c ON ct.content_item_id=c.id';
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

    /**
     * Upload file on the server and return an array of uploaded file info..
     *
     * @return array()
     *
     * @since 1.0.0
     */
    public static function uploadFile($wdid)
    {
        //Array to store the log file data. Set an error index for a possible error error.
        $log = array('error' => '');

        //Get the name of the destination folder.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('log_path');
        $query->from('#__logbook_watchdogs');
        $query->where('id='.(int) $wdid);
        $db->setQuery($query);
        $destFolder = $db->loadResult();

        $jinput = JFactory::getApplication()->input;
        $files = $jinput->files->get('jform');
        $files = $files['uploaded_file'];

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
        if ($files['error'] == 0) {
            //Get the file extension and convert it to lowercase.
            $ext = strtolower(JFile::getExt($files['name']));

            //Check if the extension is allowed.
            if (!in_array($ext, $allowedExt) && !$allFiles) {
                $log['error'] = 'COM_LOGBOOK_EXTENSION_NOT_ALLOWED';

                return $log;
            }

            //Check the size of the file.
            if ($files['size'] > $maxFileSize) {
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
            preg_match('#(.+)\.[a-zA-Z0-9\#?!$~@()-_]{1,}$#', $files['name'], $matches);
            $fileName = $matches[1];

            //Sanitize the file name which will be used for downloading, (see stringURLSafe function for details).
            $fileName = JFilterOutput::stringURLSafe($fileName);

            //Create a table containing all data about the file.
            $log['file'] = $file;
            $log['file_name'] = $fileName.'.'.$ext;
            $log['file_type'] = $files['type'];
            $log['file_size'] = $files['size'];
            $log['file_path'] = $destFolder;

            //To obtain the appropriate icon file name, we get the file extension then we concatenate it with .gif.
            //If the extension doesn't have any appropriate extension icon, we display the generic icon.
            if (!in_array($ext, $iconsExt)) {
                $log['file_icon'] = 'generic.gif';
            } else {
                $log['file_icon'] = $ext.'.gif';
            }

            //Move the file on the server.
            if (!JFile::upload($files['tmp_name'], JPATH_ROOT.'/'.$destFolder.'/'.$log['file'])) {
                $log['error'] = 'COM_LOGBOOK_FILE_TRANSFER_ERROR';

                return $log;
            }

            //File transfert has been successful.
            return $log;
        } else { //The upload of the file has failed.
            //Return the error which has occured.
            switch ($files['error']) {
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
     * Restore Watchdog after deleteFile($id) returns true
     * Update watchdog database
     * 0. Find older log info and try to restore older info
     * 1. Decrement the number of logs in watchdogs table
     * 2. restore the latest log date to the previous log
     * 3. calculate the new next due date as per the replaced latest log date.
     *
     * Will Call this function if deleteFile says to proceed futher
     *
     * @return mixed
     *
     * @since 1.0.0
     */
    public static function restoreWatchdog($wdid, $logid)
    {
        //Get the path of the corresponding file.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('l.created')
            ->from('#__logbook_logs AS l')
            ->where($db->qn('l.id').' != '.$db->quote($logid))
            ->where($db->qn('l.wdid').' = '.$db->quote($wdid))
            ->order('l.created DESC')
            ->setlimit('1');
        $db->setQuery($query);
        $lastlogdate = $db->loadResult();

        //If a log found
        if (!empty($lastlogdate)) {
            //find the tinterval
            $query->clear();
            $query->select('ti.title')
                ->from('#__logbook_timeintervals AS ti')
                ->join('LEFT', '#__logbook_watchdogs as wd ON wd.tiid = ti.id')
                ->where('wd.id='.(int)$wdid);
            $db->setQuery($query);
            $tinterval = $db->loadResult();

            //Update Values in passed old wdid:
            $query->clear();
            $query->update('#__logbook_watchdogs');
            $query->set(
                array(
                    'log_count=log_count-1',
                    'latest_log_date='.$db->quote(new Date($lastlogdate)),
                    'next_due_date='.$db->quote(new Date($lastlogdate.'+'.$tinterval)),
                )
            );
            $query->where('id='.(int) $wdid);
            $db->setQuery($query);
            $db->execute();
        } else {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_LOGBOOK_WATCHDOG_NDD_SET_TO_NOW'), 'warning');
            //Update Values in passed old wdid:
            $query->clear();
            $query->update('#__logbook_watchdogs');
            $query->set(
                array(
                    'log_count=log_count-1',
                    'latest_log_date='.$db->quote($db->getNullDate()),
                    'next_due_date='.$db->quote(new Date('now')),
                )
            );
            $query->where('id='.(int) $wdid);
            $db->setQuery($query);
            $db->execute();
        }
    }

    /**
     * Update Watchdog with new dates and added log_count
     *
     * @return void
     *
     * @since 1.0.0
     */
    public static function updateWatchdog($wdid, $latestlogdate)
    {
        //find the tinterval
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('ti.title')
            ->from('#__logbook_timeintervals AS ti')
            ->join('LEFT', '`#__logbook_watchdogs` AS wd on wd.tiid=ti.id')
            ->where('wd.id = '.(int)$wdid);
        $db->setQuery($query);
        $tinterval = $db->loadResult();

        //Update Values in passed new wdid:
        $query->clear();
        $query->update('#__logbook_watchdogs');
        $query->set(
            array(
                'log_count=log_count+1',
                'latest_log_date='.$db->quote(new Date($latestlogdate)),
                'next_due_date='.$db->quote(new Date($latestlogdate.'+'.$tinterval)),
            )
        );
        $query->where('id='.(int) $wdid);
        $db->setQuery($query);
        $db->execute();

    }


    /**
     * Returns a valid section for logs. If it is not valid then null
     * is returned.
     *
     * @param string $section The section to get the mapping for
     *
     * @return string|null The new section
     *
     * @since   3.7.0
     */
    public static function validateSection($section)
    {
        if (JFactory::getApplication()->isClient('site')) {
            // On the front end we need to map some sections
            switch ($section) {
                // Editing an log
                case 'form':

                // logs list view
                case 'logs':
                    $section = 'log';
            }
        }

        if ($section != 'log') {
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
     * @since   1.0.0
     */
    public static function getContexts()
    {
        JFactory::getLanguage()->load('com_logbook', JPATH_ADMINISTRATOR);

        $contexts = array(
            'com_logbook.log' => JText::_('COM_LOGBOOK'),
            'com_logbook.categories' => JText::_('JCATEGORY'),
        );

        return $contexts;
    }
}
