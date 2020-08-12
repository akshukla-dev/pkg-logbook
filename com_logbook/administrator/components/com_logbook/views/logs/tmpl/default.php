<?php
/**
 * @package LMI/LogManager/LogBook/LogMoniter
 * @copyright Copyright (c)2020 Amit Kumar Shukla. All Rights Reserved.
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$canOrder = $user->authorise('core.edit.state', 'com_logbook.category');
$saveOrder = $listOrder == 'l.ordering';
$assoc = JLanguageAssociations::isEnabled();

if ($saveOrder) {
    $saveOrderingUrl = 'index.php?option=com_logbook&task=logs.saveOrderAjax&tmpl=component';
    JHtml::_('sortablelist.sortable', 'logList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
?>
<form action="<?php echo JRoute::_('index.php?option=com_logbook&view=logs'); ?>" method="post" name="adminForm" id="adminForm">
    <?php if (!empty($this->sidebar)) : ?>
    <div id="j-sidebar-container" class="span2">
        <?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10">
    <?php else : ?>
    <div id="j-main-container">
    <?php endif; ?>
        <?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
        <div class="clearfix"> </div>
        <?php if (empty($this->items)) : ?>
            <div class="alert alert-no-items">
                <?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
            </div>
        <?php else : ?>
            <table class="table table-striped" id="logList">
                <thead>
                    <tr>
                        <th width="1%" class="nowrap center hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', '', 'l.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
                        </th>
                        <th width="1%" class="nowrap center">
                            <?php echo JHtml::_('grid.checkall'); ?>
                        </th>
                        <th width="1%" class="nowrap center">
                            <?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'l.state', $listDirn, $listOrder); ?>
                        </th>
                        <th class="title">
                            <?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'l.title', $listDirn, $listOrder); ?>
                        </th>
                        <th width="10%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'access_level', $listDirn, $listOrder); ?>
                        </th>
                        <th width="5%" class="nowrap center hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'JGLOBAL_HITS', 'l.hits', $listDirn, $listOrder); ?>
                        </th>
                        <?php if ($assoc) : ?>
                        <th width="5%" class="nowrap hidden-phone hidden-tablet">
                            <?php echo JHtml::_('searchtools.sort', 'COM_LOGBOOK_HEADING_ASSOCIATION', 'association', $listDirn, $listOrder); ?>
                        </th>
                        <?php endif; ?>
                        <th width="10%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language_title', $listDirn, $listOrder); ?>
                        </th>
                        <th width="1%" class="nowrap center hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'l.id', $listDirn, $listOrder); ?>
                        </th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <td colspan="8">
                            <?php echo $this->pagination->getListFooter(); ?>
                        </td>
                    </tr>
                </tfoot>
                <tbody>
                <?php foreach ($this->items as $i => $item) : ?>
                    <?php $ordering = ($listOrder == 'l.ordering'); ?>
                    <?php $item->cat_link = JRoute::_('index.php?option=com_categories&extension=com_logbook&task=edit&type=other&cid[]='.$item->catid); ?>
                    <?php $canCreate = $user->authorise('core.create', 'com_logbook.category.'.$item->catid); ?>
                    <?php $canEdit = $user->authorise('core.edit', 'com_logbook.category.'.$item->catid); ?>
                    <?php $canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $user->id || $item->checked_out == 0; ?>
                    <?php $canEditOwn = $user->authorise('core.edit.own', 'com_logbook.category.'.$item->catid) && $item->created_by == $user->id; ?>
                    <?php $canChange = $user->authorise('core.edit.state', 'com_logbook.category.'.$item->catid) && $canCheckin; ?>
                    <tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->catid; ?>">
                        <td class="order nowrap center hidden-phone">
                            <?php $iconClass = ''; ?>
                            <?php if (!$canChange) : ?>
                                <?php $iconClass = ' inactive'; ?>
                            <?php elseif (!$saveOrder) : ?>
                                <?php $iconClass = ' inactive tip-top hasTooltip" title="'.JHtml::tooltipText('JORDERINGDISABLED'); ?>
                            <?php endif; ?>
                            <span class="sortable-handler<?php echo $iconClass; ?>">
                                <i class="icon-menu" aria-hidden="true"></i>
                            </span>
                            <?php if ($canChange && $saveOrder) : ?>
                                <input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order " />
                            <?php endif; ?>
                        </td>
                        <td class="center">
                            <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                        </td>
                        <td class="center">
                            <div class="btn-group">
                                <?php echo JHtml::_('jgrid.published', $item->state, $i, 'logs.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
                                <?php // Create dropdown items and render the dropdown list.?>
                                <?php if ($canChange) : ?>
                                    <?php JHtml::_('actionsdropdown.'.((int) $item->state === 2 ? 'un' : '').'archive', 'cb'.$i, 'logs'); ?>
                                    <?php JHtml::_('actionsdropdown.'.((int) $item->state === -2 ? 'un' : '').'trash', 'cb'.$i, 'logs'); ?>
                                    <?php echo JHtml::_('actionsdropdown.render', $this->escape($item->title)); ?>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="nowrap has-context">
                            <?php if ($item->checked_out) : ?>
                                <?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'logs.', $canCheckin); ?>
                            <?php endif; ?>
                            <?php if ($canEdit || $canEditOwn) : ?>
                                <a href="<?php echo JRoute::_('index.php?option=com_logbook&task=log.edit&id='.(int) $item->id); ?>">
                                    <?php echo $this->escape($item->title); ?></a>
                            <?php else : ?>
                                    <?php echo $this->escape($item->title); ?>
                            <?php endif; ?>
                            <span class="small">
                                <?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
                            </span>
                            <div class="small">
                                <?php echo JText::_('JCATEGORY').': '.$this->escape($item->category_title); ?>
                            </div>
                        </td>
                        <td class="small hidden-phone">
                            <?php echo $this->escape($item->access_level); ?>
                        </td>
                        <td class="center hidden-phone">
                            <?php echo $item->hits; ?>
                        </td>
                        <?php if ($assoc) : ?>
                            <td class="hidden-phone hidden-tablet">
                                <?php if ($item->association) : ?>
                                    <?php echo JHtml::_('log.association', $item->id); ?>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                        <td class="small hidden-phone">
                            <?php echo JLayoutHelper::render('joomla.content.language', $item); ?>
                        </td>
                        <td class="center hidden-phone">
                            <?php echo (int) $item->id; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php // Load the batch processing form.?>
            <?php if ($user->authorise('core.create', 'com_logbook')
                && $user->authorise('core.edit', 'com_logbook')
                && $user->authorise('core.edit.state', 'com_logbook')) : ?>
                <?php echo JHtml::_(
                    'bootstrap.renderModal',
                    'collapseModal',
                    array(
                        'title' => JText::_('COM_LOGBOOK_BATCH_OPTIONS'),
                        'footer' => $this->loadTemplate('batch_footer'),
                    ),
                    $this->loadTemplate('batch_body')
                ); ?>
            <?php endif; ?>
        <?php endif; ?>

        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
