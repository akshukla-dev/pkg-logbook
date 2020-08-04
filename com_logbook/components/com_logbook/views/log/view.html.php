<?php
/**
 * @copyright   Copyright (C) 2020 Amit Kumar Shukla, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * View to edit a log.
 *
 * @since  1.5
 */
class LogbookViewLog extends JViewLegacy
{
    protected $state;

    protected $params;

    protected $item;

    /**
     * Execute and display a template script.
     *
     * @param string $tpl the name of the template file to parse; automatically searches through the template paths
     *
     * @return mixed a string if successful, otherwise an Error object
     *
     * @since   __DEPLOY_VERSION__
     */
    public function display($tpl = null)
    {
        $dispatcher = JEventDispatcher::getInstance();

        // Initialise variables
        $this->state = $this->get('State');
        $this->item = $this->get('Item');
        $this->params = $this->state->get('params');

        // Create a shortcut for $item.
        $item = $this->item;

        $offset = $this->state->get('list.offset');

        $dispatcher->trigger('onContentPrepare', array('com_logbook.log', &$item, &$item->params, $offset));

        $item->event = new stdClass();

        $results = $dispatcher->trigger('onContentAfterTitle', array('com_logbook.log', &$item, &$item->params, $offset));
        $item->event->afterDisplayTitle = trim(implode("\n", $results));

        $results = $dispatcher->trigger('onContentBeforeDisplay', array('com_logbook.log', &$item, &$item->params, $offset));
        $item->event->beforeDisplayContent = trim(implode("\n", $results));

        $results = $dispatcher->trigger('onContentAfterDisplay', array('com_logbook.log', &$item, &$item->params, $offset));
        $item->event->afterDisplayContent = trim(implode("\n", $results));

        parent::display($tpl);
    }
}
