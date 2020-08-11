<?php
/**
 * @package LMI/LogManager/LogBook/LogMoniter
 * @copyright Copyright (c)2020 Amit Kumar Shukla. All Rights Reserved.
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */
defined('_JEXEC') or die;

/**
 * HTML Watchdog View class for the Logmoniter component.
 *
 * @since  1.5
 */
class LogmoniterViewWatchdog extends JViewLegacy
{
    protected $item;

    protected $params;

    protected $state;

    /**
     * Execute and display a template script.
     *
     * @param string $tpl the name of the template file to parse; automatically searches through the template paths
     *
     * @return mixed a string if successful, otherwise an Error object
     */
    public function display($tpl = null)
    {
        $dispatcher = JEventDispatcher::getInstance();

        $this->item = $this->get('Item');
        $this->state = $this->get('State');
        $this->params = $this->state->get('params');

        // Create a shortcut for $item.
        $item = $this->item;

        $offset = $this->state->get('list.offset');

        $dispatcher->trigger('onContentPrepare', array('com_logmoniter.watchdog', &$item, &$item->params, $offset));

        $item->event = new stdClass();

        $results = $dispatcher->trigger('onContentAfterTitle', array('com_logmoniter.watchdog', &$item, &$item->params, $offset));
        $item->event->afterDisplayTitle = trim(implode("\n", $results));

        $results = $dispatcher->trigger('onContentBeforeDisplay', array('com_logmoniter.watchdog', &$item, &$item->params, $offset));
        $item->event->beforeDisplayContent = trim(implode("\n", $results));

        $results = $dispatcher->trigger('onContentAfterDisplay', array('com_logmoniter.watchdog', &$item, &$item->params, $offset));
        $item->event->afterDisplayContent = trim(implode("\n", $results));

        parent::display($tpl);
    }
}
