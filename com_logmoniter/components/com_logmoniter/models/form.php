<?php
/**
 * @copyright Copyright (c)2020 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 */
defined('_JEXEC') or die;

// Base this model on the backend version.
JLoader::register('LogmoniterModelWatchdog', JPATH_COMPONENT_ADMINISTRATOR.'/models/watchdog.php');

//Inherit the backend version.
class LogmoniterModelForm extends LogmoniterModelWatchdog
{
    /**
     * Model typeAlias string. Used for version history.
     *
     * @var string
     */
    public $typeAlias = 'com_logmoniter.watchdog';

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     *
     * @since   1.6
     */
    protected function populateState()
    {
        $app = JFactory::getApplication();

        // Load state from the request.
        $pk = $app->input->getInt('wd_id');
        $this->setState('watchdog.id', $pk);

        // Add compatibility variable for default naming conventions.
        $this->setState('form.id', $pk);

        //Retrieve a possible category id from the url query.
        $this->setState('watchdog.catid', $app->input->getInt('catid'));

        //Retrieve a possible encoded return url from the url query.
        $return = $app->input->get('return', null, 'base64');
        if (!JUri::isInternal(base64_decode($return))) {
            $return = null;
        }
        $this->setState('return_page', base64_decode($return));

        // Load the global parameters of the component.
        $params = $app->getParams();
        $this->setState('params', $params);

        $this->setState('layout', $app->input->getString('layout'));
    }

    /**
     * Get the return URL.
     *
     * @return string the return URL
     *
     * @since   1.6
     */
    public function getReturnPage()
    {
        return base64_encode($this->getState('return_page'));
    }

    /**
     * Method to save the form data.
     *
     * @param array $data the form data
     *
     * @return bool true on success
     *
     * @since   3.2
     */
    public function save($data)
    {
        // Associations are not edited in frontend ATM so we have to inherit them
        if (JLanguageAssociations::isEnabled() && !empty($data['id'])
            && $associations = JLanguageAssociations::getAssociations('com_logmoniter', '#__logbook_watchdogs', 'com_logmoniter.item', $data['id'])) {
            foreach ($associations as $tag => $associated) {
                $associations[$tag] = (int) $associated->id;
            }

            $data['associations'] = $associations;
        }

        return parent::save($data);
    }

    /**
     * Allows preprocessing of the JForm object.
     *
     * @param JForm  $form  The form object
     * @param array  $data  The data to be merged into the form object
     * @param string $group The plugin group to be executed
     *
     * @since   3.7.0
     */
    protected function preprocessForm(JForm $form, $data, $group = 'content')
    {
        $params = $this->getState()->get('params');

        if ($params && $params->get('enable_category') == 1) {
            $form->setFieldAttribute('catid', 'default', $params->get('catid', 1));
            $form->setFieldAttribute('catid', 'readonly', 'true');
        }

        return parent::preprocessForm($form, $data, $group);
    }

    /**
     * Abstract method for getting the form from the model.
     *
     * @param array $data     data for the form
     * @param bool  $loadData true if the form is to load its own data (default case), false if not
     *
     * @return mixed A JForm object on success, false on failure
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm('com_logmoniter.form', 'watchdog', array('control' => 'jform', 'load_data' => $loadData));

        return $form;
    }
}
