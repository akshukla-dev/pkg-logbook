<?php
/**
 * @copyright Copyright (c) 2020 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */

//Since this file is called directly and it doesn't belong to any component,
//module or plugin, we need first to initialize the Joomla framework in order to use
//the Joomla API methods.

//Initialize the Joomla framework
define('_JEXEC', 1);
//Note: Utterly useless here but it fits the JED expectations.
defined('_JEXEC') or die;
//First we get the number of letters we want to substract from the path.
$length = strlen('/components/com_logbook');
//Turn the length number into a negative value.
$length = $length - ($length * 2);
//Builds the path to the website root.
define('JPATH_BASE', substr(dirname(__DIR__), 0, $length));

//Get the required files
require_once JPATH_BASE.'/includes/defines.php';
require_once JPATH_BASE.'/includes/framework.php';
//Path to the factory.php file before the 3.8.0 Joomla's version.
$factoryFilePath = '/libraries/joomla/factory.php';
$jversion = new JVersion();
//Check Joomla's version.
if ($jversion->getShortVersion() >= '3.8.0') {
    //Set to the file new location.
    $factoryFilePath = '/libraries/src/Factory.php';
}
//We need to use Joomla's database class
require_once JPATH_BASE.$factoryFilePath;
//Create the application
$mainframe = JFactory::getApplication('site');

//Get the id number passed through the url.
$jinput = JFactory::getApplication()->input;
$id = $jinput->get('id', 0, 'uint');

if ($id) {
    //Retrieve some data from the log.
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select('state, access,file,file_path,file_name,file_type,file_size')
    ->from('#__logbook_logs')
    ->where('id='.$id);
    $db->setQuery($query);
    $log = $db->loadObject();

    //The log is unpublished/not colsed.
    if ($log->state != 1) {
        echo 'This log is not closed (published) yet.';

        return;
    }

    //Check the permissions of the user for this log.

    //Get the user's access view.
    $user = JFactory::getUser();

    $accessView = false;
    if (in_array($log->access, $user->getAuthorisedViewLevels())) {
        $accessView = true;
    }

    //The user has the required permission.
    if ($accessView) {
        if ($log->file_path) {
            //Increment the download counter for this log.
            $query->clear();
            $query->update('#__logbook_logs')
                  ->set('downloads=downloads+1')
                  ->where('id='.$id);
            $db->setQuery($query);
            $result = $db->execute();

            //$component = JComponentHelper::getComponent('com_lrm');
            //Build the path to the file.
            $download = JPATH_BASE.'/'.$log->file_path.'/'.$log->file;

            if (file_exists($download) === false) {
                echo 'The log file cannot be found.';

                return;
            }

            header('Content-Description: File Transfer');
            header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
            header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');   // Date in the past
            header('Content-type: '.$log->file_type);
            header('Content-Transfer-Encoding: binary');
            header('Content-length: '.$log->file_size);
            header('Content-Disposition: attachment; filename="'.$log->file_name.'"');
            ob_clean();
            flush();
            readfile($download);

            exit;
        } else { //The log url is empty.
            echo 'Wrong log url.';

            return;
        }
    } else { //The user doesn't have the required permission.
        echo 'You are not allowed to download this log.';

        return;
    }
} else { //The log id is unset.
    echo 'The log doesn\'t exist.';
}
