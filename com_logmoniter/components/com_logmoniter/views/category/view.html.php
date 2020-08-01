<?php
/**
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * HTML View class for the Logmoniter component.
 *
 * @since  1.5
 */
class LogmoniterViewCategory extends JViewCategory
{
    /**
     * @var string The name of the extension for the category
     *
     * @since  3.2
     */
    protected $extension = 'com_logmoniter';

    /**
     * @var string Default title to use for page title
     *
     * @since  3.2
     */
    protected $defaultPageTitle = 'COM_LOGMONITER_WATCHDOGS';

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
     * @return mixed a string if successful, otherwise an Error object
     */
    public function display($tpl = null)
    {
        parent::commonCategoryDisplay();

        // Prepare the data.
        // Compute the weblink slug & link url.
        foreach ($this->items as $item) {
            $item->slug = $item->alias ? ($item->id.':'.$item->alias) : $item->id;

            $temp = new JRegistry();
            $temp->loadString($item->params);
            $item->params = clone $this->params;
            $item->params->merge($temp);
        }

        return parent::display($tpl);
    }

    /**
     * Prepares the document.
     */
    protected function prepareDocument()
    {
        parent::prepareDocument();

        $app = JFactory::getApplication();
        $menus = $app->getMenu();
        $pathway = $app->getPathway();
        $title = null;

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = $menus->getActive();

        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', JText::_('COM_LOGMONITER_DEFAULT_PAGE_TITLE'));
        }

        $id = (int) @$menu->query['id'];

        if ($menu && ($menu->query['option'] != 'com_logmoniter' || $id != $this->category->id)) {
            $this->params->set('page_subheading', $this->category->title);
            $path = array(array('title' => $this->category->title, 'link' => ''));
            $category = $this->category->getParent();

            while (($menu->query['option'] != 'com_logmoniter' || $id != $category->id) && $category->id > 1) {
                $path[] = array('title' => $category->title, 'link' => LogmoniterHelperRoute::getCategoryRoute($category->id));
                $category = $category->getParent();
            }

            $path = array_reverse($path);

            foreach ($path as $item) {
                $pathway->addItem($item['title'], $item['link']);
            }
        }

        parent::addFeed();
    }
}
