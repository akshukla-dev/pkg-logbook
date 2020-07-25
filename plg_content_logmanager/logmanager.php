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
use Joomla\CMS\Date\Date;

class plgContentLogmanager extends JPlugin
{
    public function onContentBeforeSave($context, $data, $isNew)
    {
        //When an item is created or edited we must first ensure that everything went
        //fine on the server (files uploading, folders creating etc...) before continuing
        //the saving process
        //Filter the sent event.
        if ($context == 'com_logmoniter.watchdog' || $context == 'com_logmoniter.form') { /////--WATCHDOG CREATION / EDITION--//////.
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
                        //$data->setError(JText::_('COM_LOGMONITER_FOLDER_NAME_ALREADY_EXISTS'));

                        return false;
                    }
                }

                //Create the new folder in the log root directory.
                if (!JFolder::create(JPATH_ROOT.'/'.$data->log_path)) {
                    $data->setError(JText::sprintf('COM_LOGMONITER_FOLDER_COULD_NOT_BE_CREATED', $data->log_path));

                    return false;
                }
            }
        } elseif ($context == 'com_logbook.log' || $context == 'com_logbook.form') { /////--LOG CREATION / EDITION--//////.
            //Check (again) if a component category is selected.
            if (empty($data->wdid)) {
                $data->setError(JText::_('COM_LOGBOOK_NO_SELECTED_CATEGORY'));

                return false;
            }
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            //Existing item.
            //The previous setting of the log might be needed to check against the current setting.
            if (!$isNew) {

                $query->select('l.wdid AS wdid, l.file AS file, l.file_path AS file_path, l.created AS log_date', 'wd.tiid AS tiid');
                $query->from('#__logbook_logs AS l');
                $query->join('LEFT', '#__logbook_watchdogs AS wd ON wd.id=l.wdid');
                $query->where('l.id='.(int) $data->id);
                $db->setQuery($query);
                $prevSetting = $db->loadObject();

                //Some variables must be retrieved directly from jform as they
                //are not passed by the $data parameter.
                $jinput = JFactory::getApplication()->input;
                $jform = $jinput->post->get('jform', array(), 'array');
                if ($jform['replace_file']) {
                    //The file must be uploaded on the server.
                    //Upload the file.
                    require_once JPATH_ADMINISTRATOR.'/components/com_logbook/helpers/logbook.php';
                    $file = LogbookHelper::uploadFile($data->wdid);

                    if (empty($file['error'])) {//File upload was successful
                        //Set the file fields.
                        $data->file = $file['file'];
                        $data->file_name = $file['file_name'];
                        $data->file_type = $file['file_type'];
                        $data->file_size = $file['file_size'];
                        $data->folder_id = $file['folder_id'];
                        $data->file_path = $file['file_path'];
                        $data->file_icon = $file['file_icon'];

                        //Remove the file from the server or generate an error message in case of failure.
                        //Warning: Don't ever use the JFile delete function cause if a problem occurs with
                        //the file, the returned value is undefined (nor boolean or whatever).
                        //Stick to the unlink PHP function which is safer.
                        if (!unlink(JPATH_ROOT.'/'.$prevSetting->file_path.'/'.$prevSetting->file)) {
                            $data->setError(JText::sprintf('COM_LRM_FILE_COULD_NOT_BE_DELETED', $prevSetting->file));

                            return false;
                        }
                        //That's it for a replaced file if wdid has not changed.
                        if ($data->wdid != $prevSetting->wdid) {
                            //Get T interval for the new wdid:
                            $query->clear();
                            $query->select('t.title')
                                ->from('#__logbook_timeintervals AS t')
                                ->join('RIGHT', '`#__logbook_watchdogs` AS wd ON t.id=wd.tiid')
                                ->where('wd.id='.(int)$data->wdid);
                            $db->setQuery($query);
                            $newtinterval=$db->loadResult();
                            //Update Values in new wdid:
                            $query->clear();
                            $query->update('#__logbook_watchdogs');
                            $query->set(
                                array(
                                    'log_count=log_count+1',
                                    'latest_log_date='.$db->quote(new Date($data->created)),
                                    'next_due_date='.$db->quote(new Date($data->created.'=+1'.$newtinterval)),
                                )
                            );
                            $query->where('id='.(int) $data->wdid);
                            $db->setQuery($query);
                            $db->execute();
                            //Reset values related to $prevSetting->wdid
                            $query->clear();
                            $query->select('created')
                                ->from('#__logbook_logs')
                                ->where('id='.$data->id-1);
                            $db->setQuery($query);
                            $prevSetting->second_last_log_date=$db->loadResult();
                            $query->clear();
                            $query->select('t.title')
                                ->from('#__logbook_timeintervals AS t')
                                ->where('t.id='.$prevSetting->tiid);
                            $db->setQuery($query);
                            $prevSetting->tinterval=$db->loadResult();
                            $query->clear();
                            $query->update('#__logbook_watchdogs');
                            $query->set(
                                array(
                                    'log_count=log_count-1',
                                    'latest_log_date='.$db->quote(new Date($prevSetting>second_last_log_date)),
                                    'next_due_date='.$db->quote(new Date($prevSetting->second_last_log_date.'+1'.$prevSetting->tinterval))
                                )
                            );
                            $query->where('id='.(int) $prevSetting->wdid);
                            $db->setQuery($query);
                            $db->execute();
                        }
                        //Don't need to go further with a replaced file.
                        return true;
                    } else {
                        //An issue has occurend
                        $data->setError(JText::_($file['error']));

                        return false;
                    }
                } else {
                    // user might have changed the associated category
                    if ($data->wdid != $prevSetting->wdid) {
                        //Move the log into the new folder. Generate an error message if the move fails.
                        //Note: JFile::move() function doesn't return false when a fail occurs. So we must test it against "not true".
                        if (JFile::move(JPATH_ROOT.'/'.$prevSetting->file_path.'/'.$prevSetting->file,
                        JPATH_ROOT.'/'.$newPath.'/'.$prevSetting->file) !== true) {
                            $data->setError(JText::sprintf('COM_LOGBOOK_FILE_COULD_NOT_BE_MOVED', $prevSetting->file));

                            return false;
                        } else {
                            //Get T interval for the new wdid:
                                $query->clear();
                                $query->select('t.title')
                                    ->from('#__logbook_timeintervals AS t')
                                    ->join('RIGHT', '`#__logbook_watchdogs` AS wd ON t.id=wd.tiid')
                                    ->where('wd.id='.(int)$data->wdid);
                                $db->setQuery($query);
                                $newtinterval=$db->loadResult();
                                //Update Values in new wdid:
                                $query->clear();
                                $query->update('#__logbook_watchdogs');
                                $query->set(array(
                                    'log_count=log_count+1',
                                    'latest_log_date='.$db->quote(new Date($data->created)),
                                    'next_due_date='.$db->quote(new Date($data->created.'=+1'.$newtinterval))
                                    )
                                );
                                $query->where('id='.(int) $data->wdid);
                                $db->setQuery($query);
                                $db->execute();
                                //Reset values related to $prevSetting->wdid
                                $query->clear();
                                $query->select('created')
                                    ->from('#__logbook_logs')
                                    ->where('id='.$data->id-1);
                                $db->setQuery($query);
                                $prevSetting->second_last_log_date=$db->loadResult();
                                $query->clear();
                                $query->select('t.title')
                                    ->from('#__logbook_timeintervals AS t')
                                    ->where('t.id='.$prevSetting->tiid);
                                $db->setQuery($query);
                                $prevSetting->tinterval=$db->loadResult();
                                $query->clear();
                                $query->update('#__logbook_watchdogs');
                                $query->set(
                                    array(
                                        'log_count=log_count-1',
                                        'latest_log_date='.$db->quote(new Date($prevSetting>second_last_log_date)),
                                        'next_due_date='.$db->quote(new Date($prevSetting->second_last_log_date.'+1'.$prevSetting->tinterval))
                                    )
                                );
                                $query->where('id='.(int) $prevSetting->wdid);
                                $db->setQuery($query);
                                $db->execute();
                        }
                    }
                    //Don't need to go further...
                    return true;
                }
            } else {//NewItem
                //Upload the file.
                require_once JPATH_ADMINISTRATOR.'/components/com_logbook/helpers/logbook.php';
                $file = LogbookHelper::uploadFile($data->wdid);
                if (empty($file['error'])) {//File upload was successful
                    //Set the file fields.
                    $data->file = $file['file'];
                    $data->file_name = $file['file_name'];
                    $data->file_type = $file['file_type'];
                    $data->file_size = $file['file_size'];
                    $data->folder_id = $file['folder_id'];
                    $data->file_path = $file['file_path'];
                    $data->file_icon = $file['file_icon'];
                    //get the T interval text
                    $query->clear();
                    $query->select('t.title')
                        ->from('#__logbook_timeintervals AS t')
                        ->join('RIGHT', '`#__logbook_watchdogs` AS wd ON t.id=wd.tiid')
                        ->where('wd.id='.(int)$data->wdid);
                    $db->setQuery($query);
                    $tinterval=$db->loadResult();

                    //Update watchdog database (Increment the number of files in the folder & .....)
                    $query->clear();
                    $query->update('#__logbook_watchdogs');
                    $query->set(
                        array(
                        'log_count=log_count+1',
                        'latest_log_date='.$db->quote(new Date($data->created)),
                        'next_due_date='.$db->quote(new Date($data->created.'+1'.$tinterval)),
                        )
                    );
                    $query->where('id='.(int) $data->wdid);
                    $db->setQuery($query);
                    $db->execute();

                    //Don't need to go further.....
                    return true;
                } else {
                    //An issue has occurend
                    $data->setError(JText::_($file['error']));

                    return false;
                }
            }
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
            $folderPath = JPATH_ROOT.'/'.$data->log_path;

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
                    $data->setError(JText::plural('COM_LOGBOOK_DELETE_FOLDER_NOT_POSSIBLE', $count, $folderPath));

                    return false;
                }

                //Remove the folder.
                if (JFolder::delete($folderPath)) {
                    return true;
                } else {
                    $data->setError(JText::sprintf('COM_LOGBOOK_FOLDER_COULD_NOT_BE_DELETED', $data->log_path));

                    return false;
                }
            } else {
                $data->setError(JText::sprintf('COM_LOGBOOK_FOLDER_DOES_NOT_EXIST', $data->log_path));

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
