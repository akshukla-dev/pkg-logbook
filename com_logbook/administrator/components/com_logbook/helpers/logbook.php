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
     * Applies the logbook tag filters to arbitrary text as per settings for current user group
     *
     * @param   text  $text  The string to filter
     *
     * @return  string  The filtered string
     *
     * @deprecated  4.0  Use JComponentHelper::filterText() instead.
     */
    public static function filterText($text)
    {
        try
        {
            JLog::add(
                sprintf('%s() is deprecated. Use JComponentHelper::filterText() instead', __METHOD__),
                JLog::WARNING,
                'deprecated'
            );
        }
        catch (RuntimeException $exception)
        {
            // Informational log only
        }

        return JComponentHelper::filterText($text);
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
     * @param   stdClass[]  &$items     The content objects
     * @param   string      $extension  The name of the active view.
     *
     * @return  stdClass[]
     *
     * @since   3.6
     */
    public static function countTagItems(&$items, $extension)
    {
        $db = JFactory::getDbo();
        $parts     = explode('.', $extension);
        $section   = null;

        if (count($parts) > 1)
        {
            $section = $parts[1];
        }

        $join  = $db->qn('#__logbook_logs') . ' AS c ON ct.content_item_id=c.id';
        $state = 'state';

        if ($section === 'category')
        {
            $join = $db->qn('#__categories') . ' AS c ON ct.content_item_id=c.id';
            $state = 'published as state';
        }

        foreach ($items as $item)
        {
            $item->count_trashed = 0;
            $item->count_archived = 0;
            $item->count_unpublished = 0;
            $item->count_published = 0;
            $query = $db->getQuery(true);
            $query->select($state . ', count(*) AS count')
                ->from($db->qn('#__contentitem_tag_map') . 'AS ct ')
                ->where('ct.tag_id = ' . (int) $item->id)
                ->where('ct.type_alias =' . $db->q($extension))
                ->join('LEFT', $join)
                ->group('state');
            $db->setQuery($query);
            $contents = $db->loadObjectList();

            foreach ($contents as $content)
            {
                if ($content->state == 1)
                {
                    $item->count_published = $content->count;
                }

                if ($content->state == 0)
                {
                    $item->count_unpublished = $content->count;
                }

                if ($content->state == 2)
                {
                    $item->count_archived = $content->count;
                }

                if ($content->state == -2)
                {
                    $item->count_trashed = $content->count;
                }
            }
        }

        return $items;
    }

    /**
     * Load file on the server and return an array filled with the data related to uploaded file.
     *
     * @return array()
     *
     * @since 1.0.0
     */
    public static function uploadFile($wdid)
    {
        //Array to store the file data. Set an error index for a possible error message.
        $document = array('error' => '');

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
            /*if (!in_array($ext, $allowedExt) && !$allFiles) {
                $document['error'] = 'COM_LOGBOOK_EXTENSION_NOT_ALLOWED';

                return $document;
            }*/

            //Check the size of the file.
            /*if ($files['size'] > $maxFileSize) {
                $document['error'] = 'COM_LOGBOOK_FILE_SIZE_TOO_LARGE';

                return $document;
            }*/

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
            $document['file'] = $file;
            $document['file_name'] = $fileName.'.'.$ext;
            $document['file_type'] = $files['type'];
            $document['file_size'] = $files['size'];
            //Build the file path.
            $document['file_path'] = $destFolder;

            //To obtain the appropriate icon file name, we get the file extension then we concatenate it with .gif.
            //If the extension doesn't have any appropriate extension icon, we display the generic icon.
            if (!in_array($ext, $iconsExt)) {
                $document['file_icon'] = 'generic.gif';
            } else {
                $document['file_icon'] = $ext.'.gif';
            }

            //Move the file on the server.
            if (!JFile::upload($files['tmp_name'], JPATH_ROOT.'/'.$destFolder.'/'.$document['file'])) {
                $document['error'] = 'COM_LOGBOOK_FILE_TRANSFER_ERROR';

                return $document;
            }

            //File transfert has been successful.
            return $document;
        } else { //The upload of the file has failed.
            //Return the error which has occured.
            switch ($files['error']) {
            case 1:
                $document['error'] = 'COM_LOGBOOK_FILES_ERROR_1';
            break;
            case 2:
            $document['error'] = 'COM_LOGBOOK_FILES_ERROR_2';
            break;
            case 3:
                $document['error'] = 'COM_LOGBOOK_FILES_ERROR_3';
            break;
            case 4:
                $document['error'] = 'COM_LOGBOOK_FILES_ERROR_4';
            break;
        }

            return $document;
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
     * Returns a valid section for articles. If it is not valid then null
     * is returned.
     *
     * @param   string  $section  The section to get the mapping for
     *
     * @return  string|null  The new section
     *
     * @since   3.7.0
     */
    public static function validateSection($section)
    {
        if (JFactory::getApplication()->isClient('site')) {
            // On the front end we need to map some sections
            switch ($section)
            {
                // Editing an log
                case 'form':

                // Category list view
                case 'category':
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
