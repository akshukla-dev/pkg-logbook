<?php
/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Component Controller.
 *
 * @since  1.5
 */
class LogmoniterController extends JControllerLegacy
{
    /**
     * The default view.
     *
     * @var string
     *
     * @since  1.6
     */
    protected $default_view = 'watchdogs';

    /**
     * Method to display a view.
     *
     * @param bool  $cachable  If true, the view output will be cached
     * @param array $urlparams an array of safe URL parameters and their variable types, for valid values see {@link JFilterInput::clean()}
     *
     * @return LogmoniterController this object to support chaining
     *
     * @since   1.5
     */
    public function display($cachable = false, $urlparams = array())
    {
        $view = $this->input->get('view', 'watchdogs');
        $layout = $this->input->get('layout', 'watchdogs');
        $id = $this->input->getInt('id');

        // Check for edit form.
        if ($view == 'watchdog' && $layout == 'edit' && !$this->checkEditId('com_logmoniter.edit.watchdog', $id)) {
            // Somehow the person just went to the form - we don't allow that.
            $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
            $this->setMessage($this->getError(), 'error');
            $this->setRedirect(JRoute::_('index.php?option=com_logmoniter&view=watchdogs', false));

            return false;
        }

        return parent::display();
    }
}
