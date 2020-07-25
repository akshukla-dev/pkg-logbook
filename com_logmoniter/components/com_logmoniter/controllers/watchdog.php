<?php
/**
 * @copyright Copyright (c)2020 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */

//Note: Override some parent form methods (libraries/legacy/controllers/form.php).
//      See the file for more details.

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Logmoniter Watchdog class.
 *
 * @since  1.6.0
 */

class LogmoniterControllerWatchdog extends JControllerForm
{
    /**
     * The URL view item variable.
     *
     * @var string
     *
     * @since  1.6
     */
    protected $view_item = 'form';

    /**
     * The URL view list variable.
     *
     * @var string
     *
     * @since  1.6
     */
    protected $view_list = 'categories';

    /**
     * The URL edit variable.
     *
     * @var string
     *
     * @since  3.2
     */
    protected $urlVar = 'w.id';

    /**
     * Method to add a new record.
     *
     * @return mixed true if the record can be added, a error object if not
     *
     * @since   1.6
     */
    public function add()
    {
        if (!parent::add()) {
            // Redirect to the return page.
            $this->setRedirect($this->getReturnPage());
        }

        // Redirect to the edit screen.
        $this->setRedirect(
            JRoute::_(
                'index.php?option=' . $this->option . '&view=' . $this->view_item . '&w_id=0'
                . $this->getRedirectToItemAppend(), false
            )
        );

        return true;
    }

    /**
     * Method override to check if you can add a new record.
     *
     * @param array $data an array of input data
     *
     * @return bool
     *
     * @since   1.6
     */
    protected function allowAdd($data = array())
    {
        $user       = JFactory::getUser();
        //Get a possible category id passed in the data or URL.
        $catId = ArrayHelper::getValue($data, 'catid', $this->input->getInt('id'), 'int');
        $allow = null;

        if ($catId) {
            // If the category has been passed in the data or URL check it.
            $allow = JFactory::getUser()->authorise('core.create', $this->option.'.category.'.$catId);
        }

        if ($allow !== null) {
            return $allow;
        }
        // In the absense of better information, revert to the component permissions.
        return parent::allowAdd();
    }

    /**
     * Method override to check if you can edit an existing record.
     *
     * @param array  $data an array of input data
     * @param string $key  the name of the key for the primary key; default is id
     *
     * @return bool
     *
     * @since   1.6
     */
    protected function allowEdit($data = array(), $key = 'id')
    {
        $recordId = (int) isset($data[$key]) ? $data[$key] : 0;
        $user = JFactory::getUser();
        $userId = $user->get('id');
        $asset = 'com_logmoniter.watchdog.'.$recordId;

        // Zero record (id:0), return component edit permission by calling parent controller method
        if (!$recordId)
        {
            return parent::allowEdit($data, $key);
        }


        // Check general edit permission first.
        if ($user->authorise('core.edit', $asset)) {
            return true;
        }

        // Fallback on edit.own.
        // First test if the permission is available.
        if ($user->authorise('core.edit.own', $asset)) {
            // Existing record already has an owner, get it
            $record = $this->getModel()->getItem($recordId);

            if (empty($record))
            {
                return false;
            }

            // Grant if current user is owner of the record
            return $user->get('id') == $record->created_by;
        }
        return false;
    }

    /**
     * Method to cancel an edit.
     *
     * @param string $key the name of the primary key of the URL variable
     *
     * @return bool true if access level checks pass, false otherwise
     *
     * @since   1.6
     */
    public function cancel($key = 'w_id')
    {
        // Redirect to the return page.
		$this->setRedirect(JRoute::_($this->getReturnPage()));
		return parent::cancel($key);
    }

    /**
     * Method to edit an existing record.
     *
     * @param string $key    the name of the primary key of the URL variable
     * @param string $urlVar the name of the URL variable if different from the primary key
     *                       (sometimes required to avoid router collisions)
     *
     * @return bool true if access level check and checkout passes, false otherwise
     *
     * @since   1.6
     */
    public function edit($key = null, $urlVar = 'w_id')
    {
        $result = parent::edit($key, $urlVar);

        return $result;
    }

    /**
     * Method to get a model object, loading it if required.
     *
     * @param string $name   The model name. Optional.
     * @param string $prefix The class prefix. Optional.
     * @param array  $config Configuration array for model. Optional.
     *
     * @return object the model
     *
     * @since   1.5
     */
    public function getModel($name = 'form', $prefix = '', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    /**
     * Gets the URL arguments to append to an item redirect.
     *
     * @param int    $recordId the primary key id for the item
     * @param string $urlVar   the name of the URL variable for the id
     *
     * @return string the arguments to append to the redirect URL
     *
     * @since   1.6
     */
    protected function getRedirectToItemAppend($recordId = null, $urlVar = 'w_id')
    {
        // Need to override the parent method completely.
        $tmpl = $this->input->get('tmpl');
        // $layout = $this->input->get('layout', 'edit');
        $append = '';

        // Setup redirect info.
        if ($tmpl) {
            $append .= '&tmpl='.$tmpl;
        }

        // TODO This is a bandaid, not a long term solution.
        // if ($layout)
        // {
        //   $append .= '&layout=' . $layout;
        // }
        $append .= '&layout=edit';

        if ($recordId) {
            $append .= '&'.$urlVar.'='.$recordId;
        }

        $itemId = $this->input->getInt('Itemid');
        $return = $this->getReturnPage();
        $catId = $this->input->getInt('catid', null, 'get');

        if ($itemId) {
            $append .= '&Itemid='.$itemId;
        }

        if ($catId) {
            $append .= '&catid='.$catId;
        }

        if ($return) {
            $append .= '&return='.base64_encode($return);
        }

        return $append;
    }

    /**
     * Get the return URL.
     *
     * If a "return" variable has been passed in the request
     *
     * @return string the return URL
     *
     * @since   1.6
     */
    protected function getReturnPage()
    {
        $return = $this->input->get('return', null, 'base64');

        if (empty($return) || !JUri::isInternal(base64_decode($return))) {
            return JUri::base();
        } else {
            return base64_decode($return);
        }
    }

    /**
     * Function that allows child controller access to model data after the data has been saved.
     *
     * @param JModelLegacy $model     the data model object
     * @param array        $validData the validated data
     *
     * @since   1.6
     */
    protected function postSaveHook(JModelLegacy $model, $validData = array())
    {
        return;
    }

    /**
     * Method to save a record.
     *
     * @param string $key    the name of the primary key of the URL variable
     * @param string $urlVar the name of the URL variable if different from the primary key (sometimes required to avoid router collisions)
     *
     * @return bool true if successful, false otherwise
     *
     * @since   1.6
     */
    public function save($key = null, $urlVar = 'w_id')
    {
        $app = JFactory::getApplication();
        $recordId = $this->input->getInt($urlVar);

        // Load the parameters.
        $params   = $app->getParams();
        $menuitem = (int) $params->get('redirect_menuitem');

        // Check for redirection after submission when creating a new watchdog only
        if ($menuitem > 0 && $recordId == 0)
        {
            $lang = '';

            if (JLanguageMultilang::isEnabled())
            {
                $item = $app->getMenu()->getItem($menuitem);
                $lang =  !is_null($item) && $item->language != '*' ? '&lang=' . $item->language : '';
            }

            // If ok, redirect to the return page.
            if ($result)
            {
                $this->setRedirect(JRoute::_('index.php?Itemid=' . $menuitem . $lang));
            }
        }
        else
        {
            // If ok, redirect to the return page.
            if ($result)
            {
                $this->setRedirect(JRoute::_($this->getReturnPage()));
            }
        }


        //Get the jform data.
        $data = $this->input->post->get('jform', array(), 'array');

        //Set the alias of the document.

        //Remove possible spaces.
        $data['alias'] = trim($data['alias']);
        if (empty($data['alias'])) {
            //Created a sanitized alias from the title field, (see stringURLSafe function for details).
            $data['alias'] = JFilterOutput::stringURLSafe($data['title']);
        }

        // Verify that the alias is unique

        //Note: Usually this code goes into the overrided store JTable function but the file
        //would already be uploaded on the server if any duplicate alias is found.
        //To avoid this situation we check the alias unicity here as the file uploading
        //is not still triggered.

        $model = $this->getModel();
        $table = $model->getTable();

        if ($table->load(array('alias' => $data['alias'], 'catid' => $data['catid'])) && ($table->id != $recordId || $recordId == 0)) {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_LOGMONITER_DATABASE_ERROR_LOG_UNIQUE_ALIAS'), 'error');

            // Save the data in the session.
            //Note: It allows to preserve the data previously set by the user after the redirection.
            $app->setUserState($this->option.'.edit.'.$this->context.'.data', $data);

            $this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend($recordId, $urlVar), false));

            return false;
        }

        //Update jform with the modified data.
        $this->input->post->set('jform', $data);

        $result = parent::save($key, $urlVar);

        // If ok, redirect to the return page.
        if ($result) {
            $this->setRedirect($this->getReturnPage());
        }

        return $result;
    }
}
