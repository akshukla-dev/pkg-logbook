<?php
/**
 * @copyright Copyright (c)2020 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */

// No direct access to this file
defined('_JEXEC') or die;
// import joomla's filesystem classes
jimport('joomla.filesystem.folder');

class com_logbookInstallerScript
{
    /**
     * method to run before an install/update/uninstall method.
     */
    public function preflight($type, $parent)
    {
        $jversion = new JVersion();

        // Installing component manifest file version
        $this->release = $parent->get('manifest')->version;

        // Show the essential information at the install/update back-end
        echo '<p>'.JText::_('COM_LRM_INSTALLING_COMPONENT_VERSION').$this->release;
        echo '<br />'.JText::_('COM_LRM_CURRENT_JOOMLA_VERSION').$jversion->getShortVersion().'</p>';

        //Abort if the component being installed is not newer than the
        //currently installed version.
        /*if($type == 'update') {
            $oldRelease = $this->getParam('version');
            $rel = ' v-'.$oldRelease.' -> v-'.$this->release;
            if(version_compare($this->release, $oldRelease, 'le')) {
              JFactory::getApplication()->enqueueMessage(JText::_('COM_LRM_UPDATE_INCORRECT_VERSION').$rel, 'error');
              return false;
            }
        }*/

        if ($type == 'install') {
            //Create a "logbook_logs" folder in the root directory of the site.
            if (JFolder::create(JPATH_ROOT.'/logbook_logs')) {
                echo '<p style="color:green;">'.JText::_('COM_LOGBOOK_FOLDER_CREATION_SUCCESS').'</p>';
            } else { //Stop the installation if the folder cannot be created.
                JFactory::getApplication()->enqueueMessage(JText::_('COM_LOGBOOK_FOLDER_CREATION_ERROR'), 'error');

                return false;
            }

            //Create a .htaccess file in the "logbook_logs" directory.
            $buffer = 'Options -Indexes';
            if (JFile::write(JPATH_ROOT.'/logbook_logs/.htaccess', $buffer)) {
                echo '<p style="color:green;">'.JText::_('COM_LOGBOOK_HTACCESS_CREATION_SUCCESS').'</p>';
            } else { //Stop the installation if the .htaccess file cannot be created.
                JFactory::getApplication()->enqueueMessage(JText::_('COM_LOGBOOK_HTACCESS_CREATION_ERROR'), 'error');

                return false;
            }
        }
    }

    /**
     * method to install the component.
     */
    public function install($parent)
    {
    }

    /**
     * method to uninstall the component.
     */
    public function uninstall($parent)
    {
        //Check if file root directory must be removed.
        //Note: Uninstall function cannot cause an abort of the Joomla uninstall action, so returning
        //false would be a waste of time.
        if (JComponentHelper::getParams('com_logbook')->get('uninstall_remove_all')) {
            JFolder::delete(JPATH_ROOT.'/logbook_logs'); //Remove file root directory and all its content.
        } else { //Keep the file root directory untouched.
      //Before the component is uninstalled we gather any relevant data about files then
      //put it into a csv file.
      $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('d.file, d.file_name, d.file_size, d.file_path, d.created, d.wdid');
            $query->from('#__logbook_logs AS d');
            $query->join('LEFT', '#__logbook_watchdogs AS wd ON wd.id=d.wdid');
            $db->setQuery($query);
            $logs = $db->loadObjectList();

            $cR = "\r\n"; //Carriage return.
            //Create the csv header.
            $buffer = 'file,file_name,file_size,folder_name,created,catid,cat_level,cat_parent_id,category_alias'.$cR;
            foreach ($logs as $log) {
                $buffer .= $log->file.','.$log->file_name.','.$log->file_size.','.
        //Remove "logbook_logs/" from the beginning of the path.
        substr($log->file_path, 13).','.$log->created.','.$log->catid.','.
        $log->cat_level.','.$log->cat_parent_id.','.$log->category_alias.$cR;
            }
            //Create the csv file.
            JFile::write(JPATH_ROOT.'/logbook_logs/file_info.csv', $buffer);
        }

        //Remove tagging informations from the Joomla table.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->delete('#__content_types')
            ->where('type_alias="com_logbook.log" OR type_alias="com_logbook.watchdog"');
        $db->setQuery($query);
        $db->query();
    }

    /**
     * method to update the component.
     */
    public function update($parent)
    {
    }

    /**
     * method to run after an install/update/uninstall method.
     */
    public function postflight($type, $parent)
    {
        if ($type == 'install') {
            //The component parameters are not inserted into the table until the user open up the Options panel then click on the save button.
            //The workaround is to update manually the extensions table with the parameters just after the component is installed.

            //Get the component config xml file
            $form = new JForm('logbook_config');
            //Note: The third parameter must be set or the xml file won't be loaded.
            $form->loadFile(JPATH_ROOT.'/administrator/components/com_logbook/config.xml', true, '/config');
            $JsonValues = '';
            foreach ($form->getFieldsets() as $fieldset) {
                foreach ($form->getFieldset($fieldset->name) as $field) {
                    //Concatenate every field as Json values.
                    $JsonValues .= '"'.$field->name.'":"'.$field->getAttribute('default', '').'",';
                }
            }

            //Remove comma from the end of the string.
            $JsonValues = substr($JsonValues, 0, -1);

            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->update('#__extensions');
            $query->set('params='.$db->Quote('{'.$JsonValues.'}'));
            $query->where('element='.$db->Quote('com_logbook').' AND type='.$db->Quote('component'));
            $db->setQuery($query);
            $db->query();
            /*
            //In order to use the Joomla's tagging system we have to give to Joomla some
            //informations about the component items we want to tag.
            //Those informations should be inserted into the #__content_types table.

            //Informations about the Logbook log items.
            $columns = array('type_title', 'type_alias', $db->quoteName('table'), 'field_mappings', 'router');
            $query->clear();
            $query->insert('#__content_types');
            $query->columns($columns);
            $query->values($db->Quote('Logbook').','.$db->Quote('com_logbook.log').','.
      $db->Quote('{"special"{"dbtable":"#__logbook_logs","key":"id","type":"Log","prefix":"LogbookTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}').','.
      $db->Quote('{"common"{"core_content_item_id":"id","core_title":"title","core_state":"state","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"null","core_hits":"hits","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access","core_params":"params","core_featured":"null","core_metadata":"null","core_language":"language","core_images":"null","core_urls":"null","core_version":"null","core_ordering":"ordering","core_metakey":"null","core_metadesc":"null","core_catid":"catid","core_xreference":"null","asset_id":"null"},"special": {}}').','.
      $db->Quote('LogbookHelperRoute::getLogRoute'));
            $db->setQuery($query);
            $db->query();

            //Informations about the Logbook category items.
            $query->clear();
            $query->insert('#__content_types');
            $query->columns($columns);
            $query->values($db->Quote('Logbook Category').','.$db->Quote('com_logbook.category').','.
      $db->Quote('{"special"{"dbtable":"#__categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common"{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}').','.
      $db->Quote('{"common"{"core_content_item_id":"id","core_title":"title","core_state":"state","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"introtext","core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access","core_params":"params","core_featured":"null","core_metadata":"metadata","core_language":"language","core_images":"null","core_urls":"null","core_version":"version","core_ordering":"null","core_metakey":"metakey","core_metadesc":"metadesc","core_catid":"parent_id","core_xreference":"null","asset_id":"asset_id"},"special":{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}').','.
      $db->Quote('LogbookHelperRoute::getCategoryRoute'));
            $db->setQuery($query);
            $db->query();
            */
        }
    }

    /*
    * get a variable from the manifest file (actually, from the manifest cache).
    */
    public function getParam($name)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('manifest_cache')
                ->from('#__extensions')
                        ->where('element = "com_logbook"');
        $db->setQuery($query);
        $manifest = json_decode($db->loadResult(), true);

        return $manifest[$name];
    }
}
