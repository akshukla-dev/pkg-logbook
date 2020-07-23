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

class plgContentLogmanager extends JPlugin
{
    public function onContentBeforeSave($context, $data, $isNew)
    {
        //When an item is created or edited we must first ensure that everything went
        //fine on the server (files uploading, folders creating etc...) before continuing
        //the saving process
        //Filter the sent event.
        if ($context == 'com_logmoniter.watchdog' || $context == 'com_logmoniter.form') {
            if (empty($data->wcid) || empty($data->isid) || empty($data->bpid)) {
                $data->setError(JText::_('COM_LOGMANAGER_FOLDER_NAME_IS_EMPTY'));

                return false;
            }
            $foldername = $data->wcid.'-'.$data->isid.'-'.$data->bpid;
            $logRootDir = 'logbookfiles';
            $data->log_folder = $foldername;
            $folderTree = JFolder::ListFolderTree(JPATH_ROOT.'/'.$logRootDir, '.', 1);
            //Existing folder.
            if (!$isNew) {
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);

                // Change is allowed only when the log_count is zero
                $query->select('log_count, log_folder');
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
                if ($prevSetting->log_count == 0 && strcmp($prevSetting->log_folder, $data->log_folder) !== 0) {
                    //Check first if the new folder  name doesn't already exist.
                    foreach ($folderTree as $dir) {
                        if ($data->log_folder == $dir['name']) {
                            $data->setError(JText::_('COM_LOGBOOK_FOLDER_NAME_ALREADY_EXISTS'));

                            return false;
                        }
                    }
                    //Set the flag.
                    $renamed = true;
                }

                if ($renamed) {
                    //Rename the folder.
                    if (JFolder::move(JPATH_ROOT.'/'.$logRootDir.'/'.$prevSetting->log_folder, JPATH_ROOT.'/'.$logRootDir.'/'.$data->log_folder) !== true) {
                        $data->setError(JText::_('COM_LOGMANAGER_FOLDER_COULD_NOT_BE_RENAMED'));

                        return false;
                    }
                }
            } else { //Create a new folder
                //Check first if the new folder name doesn't already exist.
                foreach ($folderTree as $dir) {
                    if ($data->title == $dir['name']) {
                        $data->setError(JText::_('COM_LOGMONITER_FOLDER_NAME_ALREADY_EXISTS'));

                        return false;
                    }
                }

                //Create the new folder in the log root directory.
                if (!JFolder::create(JPATH_ROOT.'/'.$logRootDir.'/'.$data->log_folder)) {
                    $data->setError(JText::sprintf('COM_LOGMONITER_FOLDER_COULD_NOT_BE_CREATED', $data->log_folder));

                    return false;
                }
            }
        } elseif ($context == 'com_logbook.log' || $context == 'com_logbook.form') { /////--DOCUMENT CREATION / EDITION--//////.
            //Check (again) if a component category is selected.
            if (empty($data->catid)) {
                $data->setError(JText::_('COM_LOGBOOK_NO_SELECTED_CATEGORY'));

                return false;
            }

            //Some variables must be retrieved directly from jform as they
            //are not passed by the $data parameter.
            $jinput = JFactory::getApplication()->input;
            $jform = $jinput->post->get('jform', array(), 'array');

            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            //Existing item.
            if (!$isNew) {
                //The previous setting of the log might be needed to check against the current setting.
                $query->select('catid, file, folder_id, f.title AS file_folder');
                $query->from('#__logbook_logs AS d');
                $query->join('LEFT', '#__logbook_folders AS f ON folder_id=f.id');
                $query->where('d.id='.(int) $data->id);
                $db->setQuery($query);
                $prevSetting = $db->loadObject();

                //The file is not replaced, so we have to empty linkMethod variable to avoid errors.
                if (!$jform['replace_file']) {
                    $linkMethod = '';
                }
            }

            //Get the component parameters.
            $params = JComponentHelper::getParams('com_logbook');

            //The file must be uploaded on the server.
            if ($jFrom['replace_file'] || $isNew) {
                //Upload the file.
                require_once JPATH_ADMINISTRATOR.'/components/com_logbook/helpers/logbook.php';
                $file = LogbookHelper::uploadFile($data->catid);

                if (empty($file['error'])) { //File upload has been successfull.
                    //Set the file fields.
                    $data->file = $file['file'];
                    $data->file_name = $file['file_name'];
                    $data->file_type = $file['file_type'];
                    $data->file_size = $file['file_size'];
                    $data->folder_id = $file['folder_id'];
                    $data->file_path = $file['file_path'];
                    $data->file_icon = $file['file_icon'];

                    //Increment the number of files in the folder.
                    if ($isNew || ($jform['replace_file'] && $data->folder_id != $prevSetting->folder_id)) {
                        $query->clear();
                        $query->update('#__logbook_folders');
                        $query->set('files=files+1');
                        $query->where('id='.(int) $data->folder_id);
                        $db->setQuery($query);
                        $db->query();

                        //That's it for a new item.
                        if ($isNew) {
                            return true;
                        }
                    }
                } else { //An issue has occured.
                    $data->setError(JText::_($file['error']));

                    return false;
                }
            }

            //Note: So far the log root directory is unchangeable but who knows in a futur version..
            $logRootDir = 'logbookfiles';

            //If the file was replaced, the previous file must be removed from the server.
            if ($jform['replace_file']) {
                //Remove the file from the server or generate an error message in case of failure.
                //Warning: Don't ever use the JFile delete function cause if a problem occurs with
                //the file, the returned value is undefined (nor boolean or whatever).
                //Stick to the unlink PHP function which is safer.
                if (!unlink(JPATH_ROOT.'/'.$logRootDir.'/'.$prevSetting->file_folder.'/'.$prevSetting->file)) {
                    $data->setError(JText::sprintf('COM_LOGBOOK_FILE_COULD_NOT_BE_DELETED', $prevSetting->file));

                    return false;
                }

                if ($data->folder_id != $prevSetting->folder_id) {
                    //Decrement the number of files in the folder which contained the file.
                    $query->clear();
                    $query->update('#__logbook_folders');
                    $query->set('files=files-1');
                    $query->where('id='.(int) $prevSetting->folder_id);
                    $db->setQuery($query);
                    $db->query();
                }

                //Don't need to go further with a replaced file.
                return true;
            }

            //If the file was not replaced we must check if category has changed and move the
            //file accordingly.

            //Component category id has changed and the log is located on the server.
            if ($data->catid != $prevSetting->catid) {
                //Retrieve the name and id of the folder linked to the new category.
                $query->clear();
                $query->select('f.title, fm.folder_id');
                $query->from('#__logbook_folder_map AS fm');
                $query->join('LEFT', '#__logbook_folders AS f ON f.id=fm.folder_id');
                $query->where('fm.catid='.$data->catid);
                $db->setQuery($query);
                $newFolder = $db->loadObject();

                //The new category is not linked to the same folder than the old one.
                if ($newFolder->folder_id != $prevSetting->folder_id) {
                    //Move the log into the right folder. Generate an error message if the move fails.
                    //Note: JFile::move() function doesn't return false when a fail occurs. So we must test it against "not true".
                    if (JFile::move(JPATH_ROOT.'/'.$logRootDir.'/'.$prevSetting->file_folder.'/'.$prevSetting->file,
                            JPATH_ROOT.'/'.$logRootDir.'/'.$newFolder->title.'/'.$prevSetting->file) !== true) {
                        $data->setError(JText::sprintf('COM_LOGBOOK_FILE_COULD_NOT_BE_MOVED', $prevSetting->file));

                        return false;
                    } else {
                        //Increment the number of files for the folder the log came in.
                        $query->clear();
                        $query->update('#__logbook_folders');
                        $query->set('files=files+1');
                        $query->where('id='.(int) $newFolder->folder_id);
                        $db->setQuery($query);
                        $db->query();

                        //Decrement the number of files for the folder the log came out.
                        $query->clear();
                        $query->update('#__logbook_folders');
                        $query->set('files=files-1');
                        $query->where('id='.(int) $data->folder_id);
                        $db->setQuery($query);
                        $db->query();

                        //Update the folder name and its id in the appropriate fields.
                        $data->file_path = $logRootDir.'/'.$newFolder->title;
                        $data->folder_id = $newFolder->folder_id;
                    }
                }
            }

            return true;
        } else {
            //We don't treat other events.
            return true;
        }
    }

    public function onContentAfterSave($context, $data, $isNew)
    {
    }

    public function onContentBeforeDelete($context, $data)
    {
        //When a log or folder is removed from the database we must first ensure that everything
        //went fine on the server (files / folders deleting) before continuing
        //the deleting process

        //Filter the sent event.
        if ($context == 'com_logbook.log') { /////--DELETE DOCUMENT--//////.
            //Get the path of the corresponding file.
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('file, file_path, folder_id, f.title AS file_folder');
            $query->from('#__logbook_logs AS d');
            $query->join('LEFT', '#__logbook_folders AS f ON folder_id=f.id');
            $query->where('d.id='.(int) $data->id);
            $db->setQuery($query);
            $log = $db->loadObject();

            //If the file is stored on the server we remove it.
            //Remove the file from the server or generate an error message in case of failure.
            //Warning: Don't ever use the JFile delete function cause if a problem occurs with
            //the file, the returned value is undefined (nor boolean or whatever).
            //Stick to the unlink PHP function which is safer.
            if (!unlink(JPATH_ROOT.'/'.$log->file_path.'/'.$log->file)) {
                $data->setError(JText::sprintf('COM_LOGBOOK_FILE_COULD_NOT_BE_DELETED', $log->file));

                return false;
            }

            //Decrement the number of the files in the folder.
            $query->clear();
            $query->update('#__logbook_folders');
            $query->set('files=files-1');
            $query->where('id='.(int) $log->folder_id);
            $db->setQuery($query);
            $db->query();

            return true;
        } elseif ($context == 'com_logbook.watchdog') { /////--DELETE FOLDER--//////.
            $logRootDir = 'logbookfiles';

            //Set as regular folder.
            $folderPath = JPATH_ROOT.'/'.$logRootDir.'/'.$data->log_folder;
            $folderName = $data->log_folder;

            //Check if the folder exists on the server.
            if (JFolder::exists($folderPath)) {
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
                    $data->setError(JText::plural('COM_LOGBOOK_DELETE_FOLDER_NOT_POSSIBLE', $count, $folderName));

                    return false;
                }

                //Remove the folder.
                if (JFolder::delete($folderPath)) {
                    return true;
                } else {
                    $data->setError(JText::sprintf('COM_LOGBOOK_FOLDER_COULD_NOT_BE_DELETED', $data->log_folder));

                    return false;
                }
            } else {
                $data->setError(JText::sprintf('COM_LOGBOOK_FOLDER_DOES_NOT_EXIST', $data->log_folder));

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

    public function onContentAfterDisplay($context, &$article, &$params, $limitstart = 0)
    {
    }
}
