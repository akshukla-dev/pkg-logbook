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
 * Content log class.
 *
 * @since  1.6.0
 */
class LogbookControllerLog extends JControllerForm
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
    protected $urlVar = 'l.id';

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
                'index.php?option='.$this->option.'&view='.$this->view_item.'&l_id=0'
                .$this->getRedirectToItemAppend(), false
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
        $user = JFactory::getUser();
        $categoryId = ArrayHelper::getValue($data, 'catid', $this->input->getInt('catid'), 'int');
        $allow = null;

        if ($categoryId) {
            // If the category has been passed in the data or URL check it.
            $allow = $user->authorise('core.create', 'com_logbook.category.'.$categoryId);
        }

        if ($allow === null) {
            // In the absense of better information, revert to the component permissions.
            return parent::allowAdd();
        } else {
            return $allow;
        }
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

        // Zero record (id:0), return component edit permission by calling parent controller method
        if (!$recordId) {
            return parent::allowEdit($data, $key);
        }

        // Check edit on the record asset (explicit or inherited)
        if ($user->authorise('core.edit', 'com_logbook.log.'.$recordId)) {
            return true;
        }

        // Check edit own on the record asset (explicit or inherited)
        if ($user->authorise('core.edit.own', 'com_logbook.log.'.$recordId)) {
            // Existing record already has an owner, get it
            $record = $this->getModel()->getItem($recordId);

            if (empty($record)) {
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
    public function cancel($key = 'l_id')
    {
        parent::cancel($key);

        // Redirect to the return page.
        $this->setRedirect($this->getReturnPage());
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
    public function edit($key = null, $urlVar = 'l_id')
    {
        $result = parent::edit($key, $urlVar);

        if (!$result) {
            $this->setRedirect(JRoute::_($this->getReturnPage()));
        }

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
        return parent::getModel($name, $prefix, $config);
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
    protected function getRedirectToItemAppend($recordId = null, $urlVar = 'l_id')
    {
        // Need to override the parent method completely.
        $tmpl = $this->input->get('tmpl');

        $append = '';

        // Setup redirect info.
        if ($tmpl) {
            $append .= '&tmpl='.$tmpl;
        }

        // TODO This is a bandaid, not a long term solution.
        /*
         * if ($layout)
         * {
         *	$append .= '&layout=' . $layout;
         * }
         */

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
     * Method to save a record.
     *
     * @param string $key    the name of the primary key of the URL variable
     * @param string $urlVar the name of the URL variable if different from the primary key (sometimes required to avoid router collisions)
     *
     * @return bool true if successful, false otherwise
     *
     * @since   1.6
     */
    public function save($key = null, $urlVar = 'l_id')
    {
        $result = parent::save($key, $urlVar);
        $app = JFactory::getApplication();
        $logId = $app->input->getInt('l_id');

        // Load the parameters.
        $params = $app->getParams();
        $menuitem = (int) $params->get('redirect_menuitem');

        // Check for redirection after submission when creating a new log only
        if ($menuitem > 0 && $logId == 0) {
            $lang = '';

            if (JLanguageMultilang::isEnabled()) {
                $item = $app->getMenu()->getItem($menuitem);
                $lang = !is_null($item) && $item->language != '*' ? '&lang='.$item->language : '';
            }

            // If ok, redirect to the return page.
            if ($result) {
                $this->setRedirect(JRoute::_('index.php?Itemid='.$menuitem.$lang));
            }
        } else {
            // If ok, redirect to the return page.
            if ($result) {
                $this->setRedirect(JRoute::_($this->getReturnPage()));
            }
        }

        return $result;
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
}
