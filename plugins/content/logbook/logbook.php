<?php
/**
 * @package LMI Record Manager 1.x
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



class plgContentLogbook extends JPlugin
{
	public function onContentPrepare($context, &$row, &$params, $page = 0)
	{
		// Don't run this plugin when the content is being indexed
		if ($context === 'com_finder.indexer')
		{
			return true;
		}

		if (is_object($row))
		{
			//return $this->_cloak($row->text, $params);
		}

		//return $this->_cloak($row, $params);
	}


	public function onContentBeforeSave($context, $data, $isNew)
	{
		//When an item is created or edited we must first ensure that everything went
		//fine on the server (files uploading, folders creating etc...) before continuing
		//the saving process

		//Filter the sent event.
		if($context == 'com_logbook.log' || $context == 'com_logbook.form') { /////--DOCUMENT CREATION / EDITION--//////.
			//Check (again) if a component category is selected.
			if(empty($data->catid)) {
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
			if(!$isNew) {
				//The previous setting of the log might be needed to check against the current setting.
				$query->select('catid, file, folder_id, f.title AS file_folder');
				$query->from('#__logbook_logs AS d');
				$query->join('LEFT', '#__logbook_folders AS f ON folder_id=f.id');
				$query->where('d.id='.(int)$data->id);
				$db->setQuery($query);
				$prevSetting = $db->loadObject();

				//The file is not replaced, so we have to empty linkMethod variable to avoid errors.
				if(!$jform['replace_file']) {
					$linkMethod = '';
				}
			}

			//Get the component parameters.
			$params = JComponentHelper::getParams('com_logbook');

			//The file must be uploaded on the server.
			if($jFrom['replace_file'] || $isNew){
				//Upload the file.
				require_once JPATH_ADMINISTRATOR.'/components/com_logbook/helpers/logbook.php';
				$file = LogbookHelper::uploadFile($data->catid);

				if(empty($file['error'])) { //File upload has been successfull.
					//Set the file fields.
					$data->file = $file['file'];
					$data->file_name = $file['file_name'];
					$data->file_type = $file['file_type'];
					$data->file_size = $file['file_size'];
					$data->folder_id = $file['folder_id'];
					$data->file_path = $file['file_path'];
					$data->file_icon = $file['file_icon'];
					

					//Increment the number of files in the folder.
					if($isNew || ($jform['replace_file'] && $data->folder_id != $prevSetting->folder_id)) {
						$query->clear();
						$query->update('#__logbook_folders');
						$query->set('files=files+1');
						$query->where('id='.(int)$data->folder_id);
						$db->setQuery($query);
						$db->query();

						//That's it for a new item.
						if($isNew) {
							return true;
						}
					}
				}
				else { //An issue has occured.
					$data->setError(JText::_($file['error']));
					return false;
				}
			}
			

			//Note: So far the log root directory is unchangeable but who knows in a futur version..
			$docRootDir = 'logbook';

			//If the file was replaced, the previous file must be removed from the server.
			if($jform['replace_file']) {
				//Remove the file from the server or generate an error message in case of failure.
				//Warning: Don't ever use the JFile delete function cause if a problem occurs with
				//the file, the returned value is undefined (nor boolean or whatever).
				//Stick to the unlink PHP function which is safer.
				if(!unlink(JPATH_ROOT.'/'.$docRootDir.'/'.$prevSetting->file_folder.'/'.$prevSetting->file)) {
				$data->setError(JText::sprintf('COM_LOGBOOK_FILE_COULD_NOT_BE_DELETED', $prevSetting->file));
				return false;
				}

				if($data->folder_id != $prevSetting->folder_id) {
				//Decrement the number of files in the folder which contained the file.
				$query->clear();
				$query->update('#__logbook_folders');
				$query->set('files=files-1');
				$query->where('id='.(int)$prevSetting->folder_id);
				$db->setQuery($query);
				$db->query();
				}

				//Don't need to go further with a replaced file.
				return true;
			}

			//If the file was not replaced we must check if category has changed and move the
			//file accordingly.

			//Component category id has changed and the log is located on the server.
			if($data->catid != $prevSetting->catid) {
				//Retrieve the name and id of the folder linked to the new category.
				$query->clear();
				$query->select('f.title, fm.folder_id');
				$query->from('#__logbook_folder_map AS fm');
				$query->join('LEFT', '#__logbook_folders AS f ON f.id=fm.folder_id');
				$query->where('fm.catid='.$data->catid);
				$db->setQuery($query);
				$newFolder = $db->loadObject();

				//The new category is not linked to the same folder than the old one.
				if($newFolder->folder_id != $prevSetting->folder_id) {
					//Move the log into the right folder. Generate an error message if the move fails.
					//Note: JFile::move() function doesn't return false when a fail occurs. So we must test it against "not true".
					if(JFile::move(JPATH_ROOT.'/'.$docRootDir.'/'.$prevSetting->file_folder.'/'.$prevSetting->file,
							JPATH_ROOT.'/'.$docRootDir.'/'.$newFolder->title.'/'.$prevSetting->file) !== true) {
						$data->setError(JText::sprintf('COM_LOGBOOK_FILE_COULD_NOT_BE_MOVED', $prevSetting->file));
						return false;
					}
					else {
						//Increment the number of files for the folder the log came in.
						$query->clear();
						$query->update('#__logbook_folders');
						$query->set('files=files+1');
						$query->where('id='.(int)$newFolder->folder_id);
						$db->setQuery($query);
						$db->query();

						//Decrement the number of files for the folder the log came out.
						$query->clear();
						$query->update('#__logbook_folders');
						$query->set('files=files-1');
						$query->where('id='.(int)$data->folder_id);
						$db->setQuery($query);
						$db->query();

						//Update the folder name and its id in the appropriate fields.
						$data->file_path = $docRootDir.'/'.$newFolder->title;
						$data->folder_id = $newFolder->folder_id;
					}
				}
			}

			return true;
		}
		elseif($context == 'com_logbook.folder') { /////--FOLDER CREATION / EDITION--//////.
			//Remove the possible spaces from the begining and the end of the string.
			$data->title = trim($data->title);

			//Check (again) if the folder name is set.
			if(empty($data->title)) {
				$data->setError(JText::_('COM_LOGBOOK_FOLDER_NAME_IS_EMPTY'));
				return false;
			}

			//Safe the folder name. No special or accentued characters and no space, (see stringURLSafe function for details).
			$data->title = JFilterOutput::stringURLSafe($data->title);

			//Note: So far the log root directory is unchangeable but who knows in a futur version..
			$docRootDir = 'logbook';

			//Retrieve the folder and symlink names from the root directorie.
			//Note: - Only the first level directories are retrieved.
			//      - Symbolic links to a folder are also taken in account.
			$folderTree = JFolder::ListFolderTree(JPATH_ROOT.'/'.$docRootDir, '.', 1);

			//Check if we're dealing with a symlink item.
			$isSymlink = false;
			if(!empty($data->symlink_path)) {
				$isSymlink = true;
			}

			//Existing folder (or symlink).
			if(!$isNew) {
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);

				//The previous setting of the folder or symlink is needed to check against the current setting.
				$query->select('id, title');
				$query->from('#__logbook_folders');
				$query->where('id='.(int)$data->id);
				$db->setQuery($query);
				$prevSetting = $db->loadObject();

				$renamed = false;
				//The 2 names are different (the folder or symlink has been renamed).
				if(strcmp($prevSetting->title, $data->title) !== 0) {
					//Check first if the new folder or symlink name doesn't already exist.
					foreach($folderTree as $dir) {
						if($data->title == $dir['name']) {
						$data->setError(JText::_('COM_LOGBOOK_FOLDER_NAME_ALREADY_EXISTS'));
						return false;
						}
					}
						//Set the flag.
					$renamed = true;
				}

				//Symlinks are systematically updated since name and path have been checked before (path
				//has been checked in the controller).
				if($isSymlink) {
					if(!unlink(JPATH_ROOT.'/'.$docRootDir.'/'.$prevSetting->title)) {
						$data->setError(JText::sprintf('COM_LOGBOOK_SYMLINK_COULD_NOT_BE_DELETED', $prevSetting->title));
						return false;
					}

					if(!symlink($data->symlink_path, JPATH_ROOT.'/'.$docRootDir.'/'.$data->title)) {
						$data->setError(JText::sprintf('COM_LOGBOOK_SYMLINK_COULD_NOT_BE_CREATED', $data->title));
						return false;
					}
				}

				if($renamed) {
					//Rename the folder.
					if(!$isSymlink) {
						if(JFolder::move(JPATH_ROOT.'/'.$docRootDir.'/'.$prevSetting->title, JPATH_ROOT.'/'.$docRootDir.'/'.$data->title) !== true) {
							$data->setError(JText::_('COM_LOGBOOK_FOLDER_COULD_NOT_BE_RENAMED'));
							return false;
						}
					}

					//Update the folder or symlink name in the log file path.
					$query->clear();
					$query->update('#__logbook_logs');
					$query->set('file_path=REPLACE(file_path, '.$db->Quote($docRootDir.'/'.$prevSetting->title).', '.$db->Quote($docRootDir.'/'.$data->title).')');
					$db->setQuery($query);
					$db->query();
				}

				//Update the connections between the categories and the folder.

				//Some variables must be retrieved directly from jform as they
				//are not passed by the $data parameter.
				$jinput = JFactory::getApplication()->input;
				$jform = $jinput->post->get('jform', array(), 'array');

				//Initialize the variable as we need a valid empty array in case
				//no category has been selected.
				$newCatids = array();
				//Get the selected categories (if any).
				if($jform['categories']) {
					$newCatids = $jform['categories'];
				}

				//Get the previous selected category ids.
				$query->clear();
				$query->select('catid');
				$query->from('#__logbook_folder_map');
				$query->where('folder_id='.(int)$data->id);
				$db->setQuery($query);
				$prevCatids = $db->loadColumn();

				//Return the category ids which has been unchecked.
				$uncheckedCatids = array_diff($prevCatids, $newCatids);

				if(!empty($uncheckedCatids)) {
					$in = implode(',', $uncheckedCatids);
					$query->clear();
					$query->select('COUNT(*)');
					$query->from('#__logbook_logs');
					$query->where('catid IN('.$in.')');
					$db->setQuery($query);
					$count = (int)$db->loadResult();

					//If any logs are linked to those categories an error message is displayed.
					if($count) {
						$data->setError(JText::_('COM_LOGBOOK_UNLINKING_NOT_POSSIBLE'));
						return false;
					}
				}

				//Remove the previous link setting.
				$query->clear();
				$query->delete('#__logbook_folder_map');
				$query->where('folder_id='.(int)$data->id);
				$db->setQuery($query);
				$result = $db->query();

				//If no category has been selected the work is done.
				if(empty($newCatids)) {
					return true;
				}

				//Initialize the SQL clause variables.
				$columns = array('folder_id', 'catid');
				$values = array();

				//Build the VALUE SQL clause.
				foreach($newCatids as $newCatid) {
					$values[] = $data->id.','.$newCatid;
				}

				//Insert a new row for each category linked to the folder.
				$query->clear();
				$query->insert('#__logbook_folder_map');
				$query->columns($columns);
				$query->values($values);
				$db->setQuery($query);
				$result = $db->query();
			}
			else { //Create a new folder or symlink.
				//Check first if the new folder name doesn't already exist.
				foreach($folderTree as $dir) {
					if($data->title == $dir['name']) {
						$data->setError(JText::_('COM_LOGBOOK_FOLDER_NAME_ALREADY_EXISTS'));
						return false;
					}
				}

				//Create the new symlink in the log root directory.
				if($isSymlink) {
				if(!symlink($data->symlink_path, JPATH_ROOT.'/'.$docRootDir.'/'.$data->title)) {
					$data->setError(JText::sprintf('COM_LOGBOOK_SYMLINK_COULD_NOT_BE_CREATED', $data->title));
					return false;
				}
				//The job is done, we can leave.
				return true;
				}


				//Create the new folder in the log root directory.
				if(!JFolder::create(JPATH_ROOT.'/'.$docRootDir.'/'.$data->title)) {
				$data->setError(JText::sprintf('COM_LOGBOOK_FOLDER_COULD_NOT_BE_CREATED', $data->title));
				return false;
				}

			}

			return true;
		}
		else {
			//We don't treat other events.
			return true;
		}
	}


	public function onContentAfterSave($context, $data, $isNew)
	{
		//When a brand new item is created, its id is still unknown during the saving procedure.
		//This is the place to put code which need a new valid item id cause the saving operation
		//is done and the id is now available.

		//Filter the sent event.
		if($context == 'com_logbook.folder') { /////--FOLDER CREATION--//////.
			//New item.
			if($isNew) {
				$jinput = JFactory::getApplication()->input;
				$jform = $jinput->post->get('jform', array(), 'array');

				//Initialize the variable as we need a valid empty array in case
				//no category has been selected.
				$newCatids = array();
				//Get the selected categories ,(if any).
				if($jform['categories']) {
					$newCatids = $jform['categories'];
				}

				if(!empty($newCatids)) {
					//Initialize the SQL clause variables.
					$columns = array('folder_id', 'catid');
					$values = array();

					//Build the VALUE SQL clause.
					foreach($newCatids as $newCatid) {
						$values[] = $data->id.','.$newCatid;
					}

					//Insert a new row for each category linked to the folder.
					$db = JFactory::getDbo();
					$query = $db->getQuery(true);
					$query->insert('#__logbook_folder_map');
					$query->columns($columns);
					$query->values($values);
					$db->setQuery($query);
					$result = $db->query();
				}
			}
		}
		else {
			//We don't treat the other events.
			return true;
		}
	}


	public function onContentBeforeDelete($context, $data)
	{
		//When a log or folder is removed from the database we must first ensure that everything
		//went fine on the server (files / folders deleting) before continuing
		//the deleting process

		//Filter the sent event.
		if($context == 'com_logbook.log') { /////--DELETE DOCUMENT--//////.
			//Get the path of the corresponding file.
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('file, file_path, folder_id, f.title AS file_folder');
			$query->from('#__logbook_logs AS d');
			$query->join('LEFT', '#__logbook_folders AS f ON folder_id=f.id');
			$query->where('d.id='.(int)$data->id);
			$db->setQuery($query);
			$log = $db->loadObject();

			//If the file is stored on the server we remove it.
			//Remove the file from the server or generate an error message in case of failure.
			//Warning: Don't ever use the JFile delete function cause if a problem occurs with
			//the file, the returned value is undefined (nor boolean or whatever).
			//Stick to the unlink PHP function which is safer.
			if(!unlink(JPATH_ROOT.'/'.$log->file_path.'/'.$log->file)) {
				$data->setError(JText::sprintf('COM_LOGBOOK_FILE_COULD_NOT_BE_DELETED', $log->file));
				return false;
			}

			//Decrement the number of the files in the folder.
			$query->clear();
			$query->update('#__logbook_folders');
			$query->set('files=files-1');
			$query->where('id='.(int)$log->folder_id);
			$db->setQuery($query);
			$db->query();

			return true;
		}
		elseif($context == 'com_logbook.folder') { /////--DELETE FOLDER--//////.
			$docRootDir = 'logbook';

			//Set as regular folder.
			$folderPath = JPATH_ROOT.'/'.$docRootDir.'/'.$data->title;
			$folderName = $data->title;

			//Check if we're dealing with a symlink item and modify folder name and path accordingly.
			$isSymlink = false;
			if(!empty($data->symlink_path)) {
				$isSymlink = true;
				$folderPath = $data->symlink_path;
				$folderName = basename($data->symlink_path);
			}


			//Check if the folder exists on the server.
			if(JFolder::exists($folderPath)) {
				//Check if there are any log files into this folder.
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select('COUNT(*)');
				$query->from('#__logbook_logs');
				$query->where('folder_id='.(int)$data->id);
				$db->setQuery($query);
				$count = (int)$db->loadResult();

				//If it's the case an error message is displayed.
				if($count) {
					$data->setError(JText::plural('COM_LOGBOOK_DELETE_FOLDER_NOT_POSSIBLE', $count, $folderName));
					return false;
				}

				//Remove the folder.
				if(JFolder::delete($folderPath)) {
					//Remove all the records in connection with the folder id from the mapping table.
					$query->clear();
					$query->delete('#__logbook_folder_map');
					$query->where('folder_id='.(int)$data->id);
					$db->setQuery($query);
					$result = $db->query();

					//We have to delete the symbolic link as well.
					if($isSymlink) {
						if(!unlink(JPATH_ROOT.'/'.$docRootDir.'/'.$data->title)) {
							$data->setError(JText::sprintf('COM_LOGBOOK_SYMLINK_COULD_NOT_BE_DELETED', $data->title));
							return false;
						}
					}

					return true;
				}
				else {
					$data->setError(JText::sprintf('COM_LOGBOOK_FOLDER_COULD_NOT_BE_DELETED', $data->title));
					return false;
				}
			}
			else {
				$data->setError(JText::sprintf('COM_LOGBOOK_FOLDER_DOES_NOT_EXIST', $data->title));
				return false;
			}
		}
		elseif($context == 'com_categories.category') { /////--DELETE CATEGORY--//////.
			//Ensure this is a category of our component.
			if($data->extension == 'com_logbook') {
				//IMPORTANT: When a parent category is checked to be deleted, its
				//children ARE NOT deleted (as expected) unless they are also checked.
				//If they are not checked, children become parents.

				//Check if there are any logs linked to that category.
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select('COUNT(*)');
				$query->from('#__logbook_logs');
				$query->where('catid='.(int)$data->id);
				$db->setQuery($query);
				$count = (int)$db->loadResult();

				//If its the case an error message is displayed.
				if($count) {
					//Note: Since we don't have any control over the categories component we can't
					//display our own message. So we use the most appropriate messages available.
					$message = JText::sprintf('COM_CATEGORIES_DELETE_NOT_ALLOWED', $data->title).JText::plural('COM_CATEGORIES_N_ITEMS_ASSIGNED', $count);
					$data->setError($message);
					return false;
				}
				else { //There is no log linked to this category.
				//Remove this category from the mapping table.
					$query->clear();
					$query->delete('#__logbook_folder_map');
					$query->where('catid='.(int)$data->id);
					$db->setQuery($query);
					$result = $db->query();

					return true;
				}
			}
			else {
			//This is not a category of our component.
			return true;
			}
		}
		else {
			//The other events are not treated.
			return true;
		}
	}


	public function onContentAfterDelete($context, $data)
	{
		//
	}


	public function onContentChangeState($context, $pks, $value)
	{
		//
	}


	public function onContentAfterDisplay($context, &$article, &$params, $limitstart = 0)
	{
		//
	}
}
