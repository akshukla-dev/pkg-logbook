<?php
/**
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

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

        // Prepare the data
        $params = $this->params;

        JPluginHelper::importPlugin('content');

        // Compute the watchdog slugs and prepare introtext (runs content plugins).
        foreach ($this->items as $item) {
            $item->slug = $item->alias ? ($item->id.':'.$item->alias) : $item->id;

            $item->parent_slug = $item->parent_alias ? ($item->parent_id.':'.$item->parent_alias) : $item->parent_id;

            // No link for ROOT category
            if ($item->parent_alias === 'root') {
                $item->parent_slug = null;
            }

            $item->catslug = $item->category_alias ? ($item->catid.':'.$item->category_alias) : $item->catid;
            $item->event = new stdClass();

            $dispatcher = JEventDispatcher::getInstance();

            $dispatcher->trigger('onContentPrepare', array('com_logmoniter.category', &$item, &$item->params, 0));

            $results = $dispatcher->trigger('onContentAfterTitle', array('com_logmoniter.category', &$item, &$item->params, 0));
            $item->event->afterDisplayTitle = trim(implode("\n", $results));

            $results = $dispatcher->trigger('onContentBeforeDisplay', array('com_logmoniter.category', &$item, &$item->params, 0));
            $item->event->beforeDisplayContent = trim(implode("\n", $results));

            $results = $dispatcher->trigger('onContentAfterDisplay', array('com_logmoniter.category', &$item, &$item->params, 0));
            $item->event->afterDisplayContent = trim(implode("\n", $results));
        }

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $app = Factory::getApplication();
        $active = $app->getMenu()->getActive();

        if ($active
            && $active->component == 'com_logmoniter'
            && isset($active->query['view'], $active->query['id'])
            && $active->query['view'] == 'category'
            && $active->query['id'] == $this->category->id) {
            $this->params->def('page_heading', $this->params->get('page_title', $active->title));
            $title = $this->params->get('page_title', $active->title);
        } else {
            $this->params->def('page_heading', $this->category->title);
            $title = $this->category->title;
            $this->params->set('page_title', $title);
        }

        // Check for empty title and add site name if param is set
        if (empty($title)) {
            $title = $app->get('sitename');
        } elseif ($app->get('sitename_pagetitles', 0) == 1) {
            $title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
        } elseif ($app->get('sitename_pagetitles', 0) == 2) {
            $title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
        }

        if (empty($title)) {
            $title = $this->category->title;
        }

        $this->document->setTitle($title);

        if ($this->category->metadesc) {
            $this->document->setDescription($this->category->metadesc);
        } elseif ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->category->metakey) {
            $this->document->setMetadata('keywords', $this->category->metakey);
        } elseif ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->params->get('robots')) {
            $this->document->setMetadata('robots', $this->params->get('robots'));
        }

        if (!is_object($this->category->metadata)) {
            $this->category->metadata = new Registry($this->category->metadata);
        }

        if (($app->get('MetaAuthor') == '1') && $this->category->get('author', '')) {
            $this->document->setMetaData('author', $this->category->get('author', ''));
        }

        $mdata = $this->category->metadata->toArray();

        foreach ($mdata as $k => $v) {
            if ($v) {
                $this->document->setMetadata($k, $v);
            }
        }

        return parent::display($tpl);
    }

    /**
     * Prepares the document.
     */
    protected function prepareDocument()
    {
        parent::prepareDocument();
        $menu = $this->menu;
        $id = (int) @$menu->query['id'];

        if ($menu && (!isset($menu->query['option']) || $menu->query['option'] !== 'com_logmoniter' || $menu->query['view'] === 'watchdog'
            || $id != $this->category->id)) {
            $path = array(array('title' => $this->category->title, 'link' => ''));
            $category = $this->category->getParent();

            while ((!isset($menu->query['option']) || $menu->query['option'] !== 'com_logmoniter' || $menu->query['view'] === 'watchdog'
                || $id != $category->id) && $category->id > 1) {
                $path[] = array('title' => $category->title, 'link' => LogmoniterHelperRoute::getCategoryRoute($category->id));
                $category = $category->getParent();
            }

            $path = array_reverse($path);

            foreach ($path as $item) {
                $this->pathway->addItem($item['title'], $item['link']);
            }
        }
        parent::addFeed();
    }
}
