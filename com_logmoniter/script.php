<?php
/**
 * @copyright Copyright (c)AMit Kumar Shukla
 * @license GNU General Public License version 3, or later
 */

// No direct access to this file
defined('_JEXEC') or die;
 // import joomla's filesystem classes
 jimport('joomla.filesystem.folder');
 jimport('joomla.filesystem.file');

class com_logmoniterInstallerScript
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
        echo '<p>'.JText::_('COM_LOGMONITER_INSTALLING_COMPONENT_VERSION').$this->release;
        echo '<br />'.JText::_('COM_LOGMONITER_CURRENT_JOOMLA_VERSION').$jversion->getShortVersion().'</p>';

        //Abort if the component being installed is not newer than the
        //currently installed version.
        /* if($type == 'update') {
           $oldRelease = $this->getParam('version');
           $rel = ' v-'.$oldRelease.' -> v-'.$this->release;

           if(version_compare($this->release, $oldRelease, 'le')) {
                 JFactory::getApplication()->enqueueMessage(JText::_('COM_LOGMONITER_UPDATE_INCORRECT_VERSION').$rel, 'error');
                 return false;
           }
         }*/

        if ($type == 'install') {
            //Create a "logbookfiles" folder in the root directory of the site.
            if (JFolder::create(JPATH_ROOT.'/logbookfiles')) {
                echo '<p style="color:green;">'.JText::_('COM_LOGMONITER_FOLDER_CREATION_SUCCESS').'</p>';
            } else { //Stop the installation if the folder cannot be created.
                JFactory::getApplication()->enqueueMessage(JText::_('COM_LOGMONITER_FOLDER_CREATION_ERROR'), 'error');

                return false;
            }

            //Create a .htaccess file in the "logbookfiles" directory.
            $buffer = 'Options -Indexes';
            if (JFile::write(JPATH_ROOT.'/logbookfiles/.htaccess', $buffer)) {
                echo '<p style="color:green;">'.JText::_('COM_LOGMONITER_HTACCESS_CREATION_SUCCESS').'</p>';
            } else { //Stop the installation if the .htaccess file cannot be created.
                JFactory::getApplication()->enqueueMessage(JText::_('COM_LOGMONITER_HTACCESS_CREATION_ERROR'), 'error');

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
        //Remove tagging informations from the Joomla table.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->delete('#__content_types')
            ->where('type_alias="com_logmoniter.watchdog" OR type_alias="com_logmoniter.category"');
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
            $form = new JForm('logmoniter_config');
            //Note: The third parameter must be set or the xml file won't be loaded.
            $form->loadFile(JPATH_ROOT.'/administrator/components/com_logmoniter/config.xml', true, '/config');
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
            $query->where('element='.$db->Quote('com_logmoniter').' AND type='.$db->Quote('component'));
            $db->setQuery($query);
            $db->query();

            //In order to use the Joomla's tagging system we have to give to Joomla some
            //informations about the component items we want to tag.
            //Those informations should be inserted into the #__content_types table.

            //Informations about the Log Moniter watchdog items.
            $columns = array('type_title', 'type_alias', $db->quoteName('table'), 'field_mappings', 'router');
            $query->clear();
            $query->insert('#__content_types');
            $query->columns($columns);
            $query->values($db->Quote('Log Moniter').','.$db->Quote('com_logmoniter.watchdog').','.
            $db->Quote('{"special"{"dbtable":"#__Logmoniter_watchdogs","key":"id","type":"Watchdog","prefix":"LogmoniterTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}').','.
            $db->Quote('{"common"{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"introtext","core_hits":"hits","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access","core_params":"null","core_featured":"null","core_metadata":"null","core_language":"language","core_images":"null","core_urls":"null","core_version":"null","core_ordering":"ordering","core_metakey":"null","core_metadesc":"null","core_catid":"catid","core_xreference":"null","asset_id":"null"},"special": {}}').','.
            $db->Quote('LogmoniterHelperRoute::getWatchdogRoute'));
            $db->setQuery($query);
            $db->query();

            //Informations about the Log Moniter category items.
            $query->clear();
            $query->insert('#__content_types');
            $query->columns($columns);
            $query->values($db->Quote('Log Moniter Category').','.$db->Quote('com_logmoniter.category').','.
            $db->Quote('{"special"{"dbtable":"#__categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common"{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}').','.
            $db->Quote('{"common"{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"introtext","core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access","core_params":"params","core_featured":"null","core_metadata":"metadata","core_language":"language","core_images":"null","core_urls":"null","core_version":"version","core_ordering":"null","core_metakey":"metakey","core_metadesc":"metadesc","core_catid":"parent_id","core_xreference":"null","asset_id":"asset_id"},"special":{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}').','.
            $db->Quote('LogmoniterHelperRoute::getCategoryRoute'));
            $db->setQuery($query);
            $db->query();
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
      ->where('element = "com_logmoniter"');
        $db->setQuery($query);
        $manifest = json_decode($db->loadResult(), true);

        return $manifest[$name];
    }
}
