<?php
/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * HTML View class for the Logmoniter component.
 *
 * @since  1.5
 */
class LogmoniterViewMoniter extends JViewLegacy
{
    protected $state = null;

    protected $item = null;

    protected $items = null;

    protected $pagination = null;

    protected $years = null;

    protected $wcenters = null;
    protected $insets = null;
    protected $bprints = null;
    protected $tintervals = null;

    /**
     * Execute and display a template script.
     *
     * @param string $tpl the name of the template file to parse; automatically searches through the template paths
     *
     * @return mixed a string if successful, otherwise an Error object
     */
    public function display($tpl = null)
    {
        $user = JFactory::getUser();
        $state = $this->get('State');
        $items = $this->get('Items');
        $pagination = $this->get('Pagination');

        // Get the page/component configuration
        $params = &$state->params;

        JPluginHelper::importPlugin('content');

        foreach ($items as $item) {
            $item->slug = $item->alias ? ($item->id.':'.$item->alias) : $item->id;
            $item->catslug = $item->category_alias ? ($item->catid.':'.$item->category_alias) : $item->catid;
            $item->parent_slug = $item->parent_alias ? ($item->parent_id.':'.$item->parent_alias) : $item->parent_id;

            JFactory::getApplication()->enqueueMessage('Moniter view .html. php item slug ; '.$item->slug);

            // No link for ROOT category
            if ($item->parent_alias === 'root') {
                $item->parent_slug = null;
            }

            $item->event = new stdClass();

            $dispatcher = JEventDispatcher::getInstance();

            $dispatcher->trigger('onContentPrepare', array('com_logmoniter.moniter', &$item, &$item->params, 0));

            $results = $dispatcher->trigger('onContentAfterTitle', array('com_logmoniter.moniter', &$item, &$item->params, 0));
            $item->event->afterDisplayTitle = trim(implode("\n", $results));

            $results = $dispatcher->trigger('onContentBeforeDisplay', array('com_logmoniter.moniter', &$item, &$item->params, 0));
            $item->event->beforeDisplayContent = trim(implode("\n", $results));

            $results = $dispatcher->trigger('onContentAfterDisplay', array('com_logmoniter.moniter', &$item, &$item->params, 0));
            $item->event->afterDisplayContent = trim(implode("\n", $results));
        }

        $form = new stdClass();

        // Month Field
        $months = array(
            '' => JText::_('COM_LOGMONITER_MONTH'),
            '01' => JText::_('JANUARY_SHORT'),
            '02' => JText::_('FEBRUARY_SHORT'),
            '03' => JText::_('MARCH_SHORT'),
            '04' => JText::_('APRIL_SHORT'),
            '05' => JText::_('MAY_SHORT'),
            '06' => JText::_('JUNE_SHORT'),
            '07' => JText::_('JULY_SHORT'),
            '08' => JText::_('AUGUST_SHORT'),
            '09' => JText::_('SEPTEMBER_SHORT'),
            '10' => JText::_('OCTOBER_SHORT'),
            '11' => JText::_('NOVEMBER_SHORT'),
            '12' => JText::_('DECEMBER_SHORT'),
        );
        $form->monthField = JHtml::_(
            'select.genericlist',
            $months,
            'month',
            array(
                'list.attr' => 'size="1" class="inputbox"',
                'list.select' => $state->get('filter.month'),
                'option.key' => null,
            )
        );

        // Year Field
        $this->years = $this->getModel()->getYears();
        $years = array();
        $years[] = JHtml::_('select.option', null, JText::_('JYEAR'));

        for ($i = 0, $iMax = count($this->years); $i < $iMax; ++$i) {
            $years[] = JHtml::_('select.option', $this->years[$i], $this->years[$i]);
        }

        $form->yearField = JHtml::_(
            'select.genericlist',
            $years,
            'year',
            array('list.attr' => 'size="1" class="inputbox"', 'list.select' => $state->get('filter.year'))
        );

        //Work Center field
        $this->wcenters = $this->getModel()->getWcenters();
        $wcenters = array();
        $wcenters[] = JHtml::_('select.option', null, JText::_('COM_LOGMONITER_WCENTER'));
        foreach ($this->wcenters as $i => $wcenter) {
            $wcenters[] = JHtml::_('select.option', $wcenter->id, $wcenter->title);
        }

        $form->wcenterField = JHtml::_(
            'select.genericlist',
            $wcenters,
            'wcenter',
            array('list.attr' => 'size="1" class="inputbox"', 'list.select' => $state->get('filter.wcenter'))
        );

        //INSET field
        $this->insets = $this->getModel()->getInsets();
        $insets = array();
        $insets[] = JHtml::_('select.option', null, JText::_('COM_LOGMONITER_INSET'));
        foreach ($this->insets as $i => $inset) {
            $insets[] = JHtml::_('select.option', $inset->id, $inset->title);
        }
        $form->insetField = JHtml::_(
            'select.genericlist',
            $insets,
            'inset',
            array('list.attr' => 'size="1" class="inputbox span8"', 'list.select' => $state->get('filter.inset'))
        );
        //Blueprints field
        $this->bprints = $this->getModel()->getBprints();
        $bprints = array();
        $bprints[] = JHtml::_('select.option', null, JText::_('COM_LOGMONITER_BPRINT'));
        foreach ($this->bprints as $i => $bprint) {
            $bprints[] = JHtml::_('select.option', $bprint->id, $bprint->title);
        }
        $form->bprintField = JHtml::_(
            'select.genericlist',
            $bprints,
            'bprint',
            array('list.attr' => 'size="1" class="inputbox span8"', 'list.select' => $state->get('filter.bprint'))
        );
        //Tinterval field
        $this->tintervals = $this->getModel()->getTintervals();
        $tintervals = array();
        $tintervals[] = JHtml::_('select.option', null, JText::_('COM_LOGMONITER_TINTERVAL'));
        foreach ($this->tintervals as $i => $tinterval) {
            $tintervals[] = JHtml::_('select.option', $tinterval->id, $tinterval->title);
        }
        $form->tintervalField = JHtml::_(
            'select.genericlist',
            $tintervals,
            'tinterval',
            array('list.attr' => 'size="1" class="inputbox"', 'list.select' => $state->get('filter.tinterval'))
        );

        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));

        $this->filter = $state->get('list.filter');
        $this->form = &$form;
        $this->items = &$items;
        $this->params = &$params;
        $this->user = &$user;
        $this->pagination = &$pagination;
        $this->pagination->setAdditionalUrlParam('month', $state->get('filter.month'));
        $this->pagination->setAdditionalUrlParam('year', $state->get('filter.year'));
        $this->pagination->setAdditionalUrlParam('wcenter', $state->get('filter.wcenter'));
        $this->pagination->setAdditionalUrlParam('inset', $state->get('filter.inset'));
        $this->pagination->setAdditionalUrlParam('bprint', $state->get('filter.bprint'));
        $this->pagination->setAdditionalUrlParam('tinterval', $state->get('filter.tinterval'));

        $this->_prepareDocument();

        parent::display($tpl);
    }

    /**
     * Prepares the document.
     */
    protected function _prepareDocument()
    {
        $app = JFactory::getApplication();
        $menus = $app->getMenu();
        $title = null;

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = $menus->getActive();

        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', JText::_('JGLOBAL_WATCHDOGS'));
        }

        $title = $this->params->get('page_title', '');

        if (empty($title)) {
            $title = $app->get('sitename');
        } elseif ($app->get('sitename_pagetitles', 0) == 1) {
            $title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
        } elseif ($app->get('sitename_pagetitles', 0) == 2) {
            $title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
        }

        $this->document->setTitle($title);

        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->params->get('robots')) {
            $this->document->setMetadata('robots', $this->params->get('robots'));
        }
    }
}
