<?php
/**
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.framework');

// Create a shortcut for params.
$params = &$this->item->params;

// Get the user object.
$user = JFactory::getUser();

// Check if user is allowed to add/edit based on logmoniter permissinos.
$canEdit = $user->authorise('core.edit', 'com_logmoniter.category.'.$this->category->id);
$canCreate = $user->authorise('core.create', 'com_logmoniter');
$canEditState = $user->authorise('core.edit.state', 'com_logmoniter');

$n = count($this->items);
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));

// Check for at least one editable watchdog
$isEditable = false;

if (!empty($this->items)) {
    foreach ($this->items as $item) {
        $canEdit = $user->authorise('core.edit', 'com_logmoniter.watchdog.'.$item->id);
        $canEditOwn = $user->authorise('core.edit.own', 'com_logmoniter.watchdog.'.$item->id) && $item->created_by == $userId;

        if ($canEdit || $canEditOwn) {
            $isEditable = true;
            break;
        }
    }
}
?>

<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm" class="form-inline">
    <?php if ($this->params->get('filter_field') !== 'hide' || $this->params->get('show_pagination_limit')) : ?>
        <fieldset class="filters btn-toolbar clearfix">
            <legend class="hide"><?php echo JText::_('COM_LOGMONITER_FORM_FILTER_LEGEND'); ?></legend>
            <?php if ($this->params->get('filter_field') !== 'hide') : ?>
                <div class="btn-group">
                    <label class="filter-search-lbl element-invisible" for="filter-search">
                        <?php echo JText::_('COM_LOGMONITER_FILTER_LABEL').'&#160;'; ?>
                    </label>
                    <input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->state->get('list.filter')); ?>" class="inputbox" onchange="document.adminForm.submit();" title="<?php echo JText::_('COM_LOGMONITER_FILTER_SEARCH_TITLE'); ?>" placeholder="<?php echo JText::_('COM_LOGMONITER_FILTER_SEARCH_DESC'); ?>" />
                </div>
            <?php endif; ?>

            <?php if ($this->params->get('show_pagination_limit')) :?>
                <div class="btn-group pull-right">
                    <label for="limit" class="element-invisible">
                        <?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>
                    </label>
                    <?php echo $this->pagination->getLimitBox(); ?>
                </div>
            <?php endif; ?>
        </fieldset>
    <?php endif; ?>

<?php if (empty($this->items)) : ?>
    <?php if ($this->params->get('show_no_watchdogs', 1)) : ?>
        <p><?php echo JText::_('COM_LOGMONITER_NO_WATCHDOGS'); ?></p>
    <?php endif; ?>
<?php else : ?>
    <table class="category table table-striped table-bordered table-hover">
        <caption class="hide"><?php echo JText::sprintf('COM_LOGMONITER_CATEGORY_LIST_TABLE_CAPTION', $this->category->title); ?></caption>
        <thead>
            <tr>
                <th scope="col" id="categorylist_header_title">
                    <?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'wd.title', $listDirn, $listOrder, null, 'asc', '', 'adminForm'); ?>
                </th>
                <?php if ($date = $this->params->get('list_show_date')) : ?>
                    <th scope="col" id="categorylist_header_date">
                        <?php if ($date === 'created') : ?>
                            <?php echo JHtml::_('grid.sort', 'COM_LOGMONITER_'.$date.'_DATE', 'wd.created', $listDirn, $listOrder); ?>
                        <?php elseif ($date === 'modified') : ?>
                            <?php echo JHtml::_('grid.sort', 'COM_LOGMONITER_'.$date.'_DATE', 'wd.modified', $listDirn, $listOrder); ?>
                        <?php elseif ($date === 'published') : ?>
                            <?php echo JHtml::_('grid.sort', 'COM_LOGMONITER_'.$date.'_DATE', 'wd.publish_up', $listDirn, $listOrder); ?>
                        <?php elseif ($date === 'latest_log_date') : ?>
                            <?php echo JHtml::_('grid.sort', JText::_('COM_LOGMONITER_LATEST_LOG_DATE'), 'wd.latest_log_date', $listDirn, $listOrder); ?>
                        <?php elseif ($date === 'next_due_date') : ?>
                            <?php echo JHtml::_('grid.sort', JText::_('COM_LOGMONITER_NEXT_DUE_DATE'), 'wd.next_due_date', $listDirn, $listOrder); ?>
                        <?php endif; ?>
                    </th>
                <?php endif; ?>
                <?php if ($this->params->get('list_show_author')) : ?>
                    <th scope="col" id="categorylist_header_author">
                        <?php echo JHtml::_('grid.sort', 'JAUTHOR', 'author', $listDirn, $listOrder); ?>
                    </th>
                <?php endif; ?>
                <?php if ($this->params->get('list_show_hits')) : ?>
                    <th scope="col" id="categorylist_header_hits">
                        <?php echo JHtml::_('grid.sort', 'JGLOBAL_HITS', 'a.hits', $listDirn, $listOrder); ?>
                    </th>
                <?php endif; ?>
                <?php if ($isEditable) : ?>
                    <th scope="col" id="categorylist_header_edit"><?php echo JText::_('COM_LOGMONITER_EDIT_ITEM'); ?></th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
		<?php foreach ($this->items as $i => $item) :
             $canEdit = $user->authorise('core.edit', 'com_logmoniter.watchdog.'.$item->id);
             $canEditOwn = $user->authorise('core.edit.own', 'com_logmoniter.watchdog.'.$item->id) && $item->created_by == $userId;
        ?>
            <?php if ($this->items[$i]->state == 0) : ?>
                <tr class="system-unpublished cat-list-row<?php echo $i % 2; ?>">
            <?php else : ?>
                <tr class="cat-list-row<?php echo $i % 2; ?>" >
            <?php endif; ?>
            <td headers="categorylist_header_title" class="list-title">
                <?php if (in_array($item->access, $this->user->getAuthorisedViewLevels())) : ?>
                    <a href="<?php echo JRoute::_(LogmoniterHelperRoute::getWatchdogRoute($item->slug, $item->catid, $item->language)); ?>">
                        <?php echo $this->escape($item->title); ?>
                    </a>
                    <?php if (JLanguageAssociations::isEnabled() && $this->params->get('show_associations')) : ?>
                        <?php $associations = LogmoniterHelperAssociation::displayAssociations($item->id); ?>
                        <?php foreach ($associations as $association) : ?>
                            <?php if ($this->params->get('flags', 1) && $association['language']->image) : ?>
                                <?php $flag = JHtml::_('image', 'mod_languages/'.$association['language']->image.'.gif', $association['language']->title_native, array('title' => $association['language']->title_native), true); ?>
                                &nbsp;<a href="<?php echo JRoute::_($association['item']); ?>"><?php echo $flag; ?></a>&nbsp;
                            <?php else : ?>
                                <?php $class = 'label label-association label-'.$association['language']->sef; ?>
                                &nbsp;<a class="<?php echo $class; ?>" href="<?php echo JRoute::_($association['item']); ?>"><?php echo strtoupper($association['language']->sef); ?></a>&nbsp;
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php else : ?>
                    <?php
                    echo $this->escape($item->title).' : ';
                    $menu = JFactory::getApplication()->getMenu();
                    $active = $menu->getActive();
                    $itemId = $active->id;
                    $link = new JUri(JRoute::_('index.php?option=com_users&view=login&Itemid='.$itemId, false));
                    $link->setVar('return', base64_encode(LogmoniterHelperRoute::getWatchdogRoute($item->slug, $item->catid, $item->language)));
                    ?>
                    <a href="<?php echo $link; ?>" class="register">
                        <?php echo JText::_('COM_LOGMONITER_REGISTER_TO_READ_MORE'); ?>
                    </a>
                    <?php if (JLanguageAssociations::isEnabled() && $this->params->get('show_associations')) : ?>
                        <?php $associations = LogmoniterHelperAssociation::displayAssociations($item->id); ?>
                        <?php foreach ($associations as $association) : ?>
                            <?php if ($this->params->get('flags', 1)) : ?>
                                <?php $flag = JHtml::_('image', 'mod_languages/'.$association['language']->image.'.gif', $association['language']->title_native, array('title' => $association['language']->title_native), true); ?>
                                &nbsp;<a href="<?php echo JRoute::_($association['item']); ?>"><?php echo $flag; ?></a>&nbsp;
                            <?php else : ?>
                                <?php $class = 'label label-association label-'.$association['language']->sef; ?>
                                &nbsp;<a class="' . <?php echo $class; ?> . '" href="<?php echo JRoute::_($association['item']); ?>"><?php echo strtoupper($association['language']->sef); ?></a>&nbsp;
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if ($item->state == 0) : ?>
                    <span class="list-published label label-warning">
                                <?php echo JText::_('JUNPUBLISHED'); ?>
                            </span>
                <?php endif; ?>
                <?php if (strtotime($item->publish_up) > strtotime(JFactory::getDate())) : ?>
                    <span class="list-published label label-warning">
                                <?php echo JText::_('JNOTPUBLISHEDYET'); ?>
                            </span>
                <?php endif; ?>
                <?php if ((strtotime($item->publish_down) < strtotime(JFactory::getDate())) && $item->publish_down != JFactory::getDbo()->getNullDate()) : ?>
                    <span class="list-published label label-warning">
                                <?php echo JText::_('JEXPIRED'); ?>
                            </span>
                <?php endif; ?>
            </td>
            <?php if ($this->params->get('list_show_date')) : ?>
                <td headers="categorylist_header_date" class="list-date small">
                    <?php
                    echo JHtml::_(
                        'date', $item->displayDate,
                        $this->escape($this->params->get('date_format', JText::_('DATE_FORMAT_LC3')))
                    ); ?>
                </td>
            <?php endif; ?>
            <?php if ($this->params->get('list_show_author', 1)) : ?>
                <td headers="categorylist_header_author" class="list-author">
                    <?php if (!empty($item->author) || !empty($item->created_by_alias)) : ?>
                        <?php $author = $item->author; ?>
                        <?php $author = $item->created_by_alias ?: $author; ?>
                        <?php if (!empty($item->contact_link) && $this->params->get('link_author') == true) : ?>
                            <?php echo JText::sprintf('COM_LOGMONITER_WRITTEN_BY', JHtml::_('link', $item->contact_link, $author)); ?>
                        <?php else : ?>
                            <?php echo JText::sprintf('COM_LOGMONITER_WRITTEN_BY', $author); ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
            <?php endif; ?>
            <?php if ($this->params->get('list_show_hits', 1)) : ?>
                <td headers="categorylist_header_hits" class="list-hits">
                            <span class="badge badge-info">
                                <?php echo JText::sprintf('JGLOBAL_HITS_COUNT', $item->hits); ?>
                            </span>
                        </td>
            <?php endif; ?>
            <?php if ($this->params->get('list_show_logs', 0) && $this->vote) : ?>
                <td headers="categorylist_header_votes" class="list-votes">
                    <span class="badge badge-success">
                        <?php echo JText::sprintf('COM_LOGMONITER_LOGS_COUNT', $item->log_count); ?>
                    </span>
                </td>
            <?php endif; ?>
            <?php if ($isEditable) : ?>
                <td headers="categorylist_header_edit" class="list-edit">
                    <?php if ($canEdit || $canEditOwn) : ?>
                        <?php echo JHtml::_('icon.edit', $item, $item->params); ?>
                    <?php endif; ?>
                </td>
            <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php // Code to add a link to submit an item.?>
<?php if ($this->category->getParams()->get('access-create')) : ?>
    <?php echo JHtml::_('icon.create', $this->category, $this->category->params); ?>
<?php endif; ?>

<?php // Add pagination links?>
<?php if (!empty($this->items)) : ?>
    <?php if (($this->params->def('show_pagination', 2) == 1 || ($this->params->get('show_pagination') == 2)) && ($this->pagination->pagesTotal > 1)) : ?>
        <div class="pagination">

            <?php if ($this->params->def('show_pagination_results', 1)) : ?>
                <p class="counter pull-right">
                    <?php echo $this->pagination->getPagesCounter(); ?>
                </p>
            <?php endif; ?>

            <?php echo $this->pagination->getPagesLinks(); ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
</form>
