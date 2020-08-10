<?php
/**
 * @copyright Copyright (c)2017 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
// Import the JPlugin class
jimport('joomla.plugin.plugin');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

//JLoader::register('LogbookHelper', JPATH_ADMINISTRATOR.'/com_logbook/helpers/logbook.php');
//require_once JPATH_ADMINISTRATOR.'/components/com_logbook/helpers/logbook.php';

class plgContentLogmanager extends JPlugin
{
    public function onContentBeforeSave($context, $data, $isNew)
    {
        //Catch your contexts
        if ($context == 'com_logbook.log' || $context == 'com_logbook.form') {
            //make sure that wdid is selected
            if (empty($data->wdid)) {
                $data->setError(JText::_('COM_LOGBOOK_NO_WATCHDOG_SELECTED'));

                return false;
            }

            //Binary $isNew : 2 Cases
            if ($isNew) { // when new log is being submitted
                //1. Upload the file
                //2. Update $data Object
                //3. Update Watchdog Table

                $file = LogbookHelper::uploadFile($data->wdid);

                if (empty($file['error'])) {//File upload was successful
                    //Set the file fields.
                    $data->file = $file['file'];
                    $data->file_name = $file['file_name'];
                    $data->file_type = $file['file_type'];
                    $data->file_size = $file['file_size'];
                    $data->file_path = $file['file_path'];
                    $data->file_icon = $file['file_icon'];

                    //Update watchdog database
                    LogbookHelper::updateWatchdog($data->wdid, $data->created);

                    //Don't need to go further.....
                    return true;
                } else {
                    //An issue has occurend
                    $data->setError(JText::_($file['error']));

                    return false;
                }
            } else {// When the old log is being edited
                // 2 binary variables: jfrom.replace_file & submitted.wdid.changed: 4 cases

                //Catch the values of the control variables
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);
                $query->select('l.wdid, l.file, l.file_path, l.created , wd.tiid');
                $query->from('#__logbook_logs AS l');
                $query->join('LEFT', '#__logbook_watchdogs AS wd ON wd.id=l.wdid');
                $query->where('l.id='.(int) $data->id);
                $db->setQuery($query);
                $prevSetting = $db->loadObject();

                //Some variables must be retrieved directly from jform as they
                //are not passed by the $data parameter.
                $jinput = JFactory::getApplication()->input;
                $jform = $jinput->post->get('jform', array(), 'array');

                if ($jform['replace_file'] && $data->wdid == $prevSetting->wdid) {
                    //1. Delete old file first
                    //2. Then Upload the new file
                    //3. Update $data will file info

                    //Remove the file from the server or generate an error message in case of failure.
                    //Warning: Don't ever use the JFile delete function cause if a problem occurs with
                    //the file, the returned value is undefined (nor boolean or whatever).
                    //Stick to the unlink PHP function which is safer.

                    if (!unlink(JPATH_ROOT.'/'.$prevSetting->file_path.'/'.$prevSetting->file)) {
                        $data->setError(JText::sprintf('COM_LOGBOOK_FILE_COULD_NOT_BE_DELETED', $prevSetting->file));

                        return false;
                    }

                    //Upload replacement file
                    $file = LogbookHelper::uploadFile($data->wdid);

                    if (empty($file['error'])) {//File upload was successful
                        //Set the file fields.
                        $data->file = $file['file'];
                        $data->file_name = $file['file_name'];
                        $data->file_type = $file['file_type'];
                        $data->file_size = $file['file_size'];
                        $data->file_path = $file['file_path'];
                        $data->file_icon = $file['file_icon'];

                        //Watchdog table already had relevant info.

                        //Don't need to go further.....
                        return true;
                    } else {
                        //An issue has occurend
                        $data->setError(JText::_($file['error']));

                        return false;
                    }
                } elseif ($jform['replace_file'] && $data->wdid != $prevSetting->wdid) {
                    //1. Delete old file
                    //2. Upload file
                    //3. Update $data with file info
                    //4. restore old watchdog
                    //5. update new watchdog

                    //Remove the file from the server or generate an error message in case of failure.
                    //Warning: Don't ever use the JFile delete function cause if a problem occurs with
                    //the file, the returned value is undefined (nor boolean or whatever).
                    //Stick to the unlink PHP function which is safer.

                    if (!unlink(JPATH_ROOT.'/'.$prevSetting->file_path.'/'.$prevSetting->file)) {
                        $data->setError(JText::sprintf('COM_LOGBOOK_FILE_COULD_NOT_BE_DELETED', $prevSetting->file));

                        return false;
                    }

                    //Upload File
                    $file = LogbookHelper::uploadFile($data->wdid);
                    //Update data with file info
                    if (empty($file['error'])) {//File upload was successful
                        //Set the file fields.
                        $data->file = $file['file'];
                        $data->file_name = $file['file_name'];
                        $data->file_type = $file['file_type'];
                        $data->file_size = $file['file_size'];
                        $data->file_path = $file['file_path'];
                        $data->file_icon = $file['file_icon'];

                        //Restore old Watchdog & inform user of failure
                        LogbookHelper::restoreWatchdog($prevSetting->wdid, $data->id);

                        //Update new Watchdog
                        LogbookHelper::updateWatchdog($data->wdid, $data->created);

                        //Don't need to go further.....
                        return true;
                    } else {
                        //An issue has occurend
                        $data->setError(JText::_($file['error']));

                        return false;
                    }
                } elseif (!$jform['replace_file'] && $data->wdid == $prevSetting->wdid) {
                    // Probably other fields being changed
                    return true;
                } elseif (!$jform['replace_file'] && $data->wdid != $prevSetting->wdid) {
                    //1. Move the file, break if error
                    //2. Update Data Obejct with new log path
                    //3. Restore Old Watchdog
                    //4. Update new Watchdog
                    //Get the path of the corresponding file.

                    $thefile = JPATH_ROOT.'/'.$prevSetting->file_path.'/'.$prevSetting->file;

                    $query->clear();
                    $query->select('wd.log_path')
                        ->from('#__logbook_watchdogs AS wd')
                        ->where('wd.id='.(int) $data->wdid);
                    $db->setQuery($query);
                    $newpath = $db->loadResult();

                    // set the new file_path
                    $data->file_path = $newpath;

                    //Move the log into the new folder. Generate an error message if the move fails.
                    //Note: JFile::move() function doesn't return false when a fail occurs. So we must test it against "not true".
                    if (JFile::move($thefile, JPATH_ROOT.'/'.$newpath.'/'.$prevSetting->file) !== true) {
                        $data->setError(JText::sprintf('COM_LOGBOOK_FILE_COULD_NOT_BE_MOVED', $thefile));

                        return false;
                    } else {// Moving file was successfull
                        //require_once JPATH_ADMINISTRATOR.'/components/com_logbook/helpers/logbook.php';
                        //estoreR old watxhdog
                        LogbookHelper::restoreWatchdog($prevSetting->wdid, $data->id);
                        //Update new Watchdog
                        LogbookHelper::updateWatchdog($data->wdid, $data->created);

                        //Don't need to go further.....
                        return true;
                    }
                }
            }
        } elseif ($context == 'com_logmoniter.watchdog' || $context == 'com_logmoniter.form') {
            if (empty($data->wcid) || empty($data->isid) || empty($data->bpid)) {
                $data->setError(JText::_('COM_LOGMANAGER_FOLDER_NAME_IS_EMPTY'));

                return false;
            }
            $foldername = $data->wcid.'-'.$data->isid.'-'.$data->bpid;
            $logRootDir = 'logbookfiles';
            $data->log_path = $logRootDir.'/'.$foldername;
            $folderTree = JFolder::ListFolderTree(JPATH_ROOT.'/'.$logRootDir, '.', 1);
            //Existing folder.
            if (!$isNew) {
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);

                // Change is allowed only when the log_count is zero
                $query->select('log_count, log_path');
                $query->from('#__logbook_watchdogs');
                $query->where('id='.(int) $data->id);
                $db->setQuery($query);
                $prevSetting = $db->loadObject();

                if ((int) $prevSetting->log_count !== 0) {
                    $data->setError(JText::_('COM_LOGMANAGER_FOLDER_IS_NOT_EMPTY'));

                    return false;
                }

                $renamed = false;
                //The 2 names are different (the folder  has been renamed).
                if ($prevSetting->log_count == 0 && strcmp($prevSetting->log_path, $data->log_path) !== 0) {
                    //Check first if the new folder  name doesn't already exist.
                    foreach ($folderTree as $dir) {
                        if ($data->log_path == $logRootDir.'/'.$dir['name']) {
                            $data->setError(JText::_('COM_LOGBOOK_FOLDER_NAME_ALREADY_EXISTS'));

                            return false;
                        }
                    }
                    //Set the flag.
                    $renamed = true;
                }

                if ($renamed) {
                    //Rename the folder.
                    if (JFolder::move(JPATH_ROOT.'/'.$prevSetting->log_path, JPATH_ROOT.'/'.$data->log_path) !== true) {
                        $data->setError(JText::_('COM_LOGMANAGER_FOLDER_COULD_NOT_BE_RENAMED'));

                        return false;
                    }
                }
            } else { //Create a new folder
                //Check first if the new folder name doesn't already exist.
                foreach ($folderTree as $dir) {
                    if ($data->log_path == $logRootDir.'/'.$dir['name']) {
                        $data->setError(JText::_('COM_LOGMONITER_FOLDER_NAME_ALREADY_EXISTS'));

                        return false;
                    }
                }

                //Create the new folder in the log root directory.
                if (!JFolder::create(JPATH_ROOT.'/'.$data->log_path)) {
                    $data->setError(JText::sprintf('COM_LOGMONITER_FOLDER_COULD_NOT_BE_CREATED', $data->log_path));

                    return false;
                }
            }
        } else {
            // We don't deal with other cases
            return true;
        }
    }

    public function onContentAfterSave($context, $data, $isNew)
    {
    }

    public function onContentBeforeDelete($context, $data)
    {
        //Catch your context--delete is only available in back end
        if ($context == 'com_logbook.log') {
            //1. Delete the log
            //2. Restore the Watchdog

            //Get the path of the corresponding file.
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('file, file_path, wdid');
            $query->from('#__logbook_logs AS l');
            $query->where('l.id='.(int) $data->id);
            $db->setQuery($query);
            $log = $db->loadObject();

            //Remove the file from the server or generate an error message in case of failure.
            //Warning: Don't ever use the JFile delete function cause if a problem occurs with
            //the file, the returned value is undefined (nor boolean or whatever).
            //Stick to the unlink PHP function which is safer.
            if (!unlink(JPATH_ROOT.'/'.$log->file_path.'/'.$log->file)) {
                $data->setError(JText::sprintf('COM_LOGBOOK_FILE_COULD_NOT_BE_DELETED', $log->file));

                return false;
            }

            //Restore watchdog database
            LogbookHelper::restoreWatchdog($log->wdid, $data->id);

            return true;
        } elseif ($context == 'com_logmoniter.watchdog') {
            //Set as regular folder.
            $thefolder = JPATH_ROOT.'/'.$data->log_path;

            //Check if the folder exists on the server.
            if (JFolder::exists($thefolder)) {
                //Check if there are any log files into this folder.
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);
                $query->select('log_count');
                $query->from('#__logbook_watchdogs');
                $query->where('id='.(int) $data->id);
                $db->setQuery($query);
                $count = (int) $db->loadResult();

                //If it's the case an error message is displayed.
                if ($count) {
                    $data->setError(JText::plural('COM_MONITER_WATCHDOG_FOLDER_DELETE_NOT_POSSIBEL', $count, $thefolder));

                    return false;
                }

                //Remove the folder.
                if (JFolder::delete($thefolder)) {
                    return true;
                } else {
                    $data->setError(JText::sprintf('COM_LOGMONITER_WATCHDOG_FOLDER_COULD_NOT_BE_DELETED', $data->log_path));

                    return false;
                }
            } else {
                $data->setError(JText::sprintf('COM_LOGMONITER_WATCHDOG_FOLDER_DOES_NOT_EXIST', $data->log_path));

                return false;
            }
        } else {
            //The other events are not treated.
            return true;
        }
    }

    public function onContentAfterDelete($context, $data)
    {
    }

    public function onContentChangeState($context, $pks, $value)
    {
    }

    public function onContentAfterDisplay($context, &$log, &$params, $limitstart = 0)
    {
    }
}
