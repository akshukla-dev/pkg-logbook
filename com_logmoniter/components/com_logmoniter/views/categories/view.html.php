<?php
/**
 * @package LMI/LogManager/LogBook/LogMoniter
 * @copyright Copyright (c)2020 Amit Kumar Shukla. All Rights Reserved.
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */
defined('_JEXEC') or die;

/**
 * Content categories view.
 *
 * @since  1.5
 */
class LogmoniterViewCategories extends JViewCategories
{
    protected $item = null;

    /**
     * @var string Default title to use for page title
     *
     * @since  3.2
     */
    protected $defaultPageTitle = 'COM_LOGMONITER_DEFAULT_PAGE_TITLE';

    /**
     * @var string The name of the extension for the category
     *
     * @since  3.2
     */
    protected $extension = 'com_logmoniter';

    /**
     * @var string The name of the view to link individual items to
     *
     * @since  3.2
     */
    protected $viewName = 'watchdog';

    /**
     * Execute and display a template script.
     *
     * @param string $tpl the name of the template file to parse; automatically searches through the template paths
     *
     * @return mixed a string if successful, otherwise a Error object
     */
    public function display($tpl = null)
    {
        $state = $this->get('State');
        $items = $this->get('Items');
        $parent = $this->get('Parent');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseWarning(500, implode("\n", $errors));

            return false;
        }

        if ($items === false) {
            return JError::raiseError(404, JText::_('JGLOBAL_CATEGORY_NOT_FOUND'));
        }

        if ($parent == false) {
            return JError::raiseError(404, JText::_('JGLOBAL_CATEGORY_NOT_FOUND'));
        }

        $params = &$state->params;

        $items = array($parent->id => $items);

        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));

        $this->maxLevelcat = $params->get('maxLevelcat', -1);
        $this->params = &$params;
        $this->parent = &$parent;
        $this->items = &$items;

        return parent::display($tpl);
    }
}
