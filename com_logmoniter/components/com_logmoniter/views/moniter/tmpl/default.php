<?php
/**
 * @copyright Copyright (c)2020 Amit Kumar Shukla. All Rights Reserved.
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers');
JHtml::_('behavior.framework');

JHtml::_('bootstrap.tooltip');
//JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.caption');

$app = JFactory::getApplication();
$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$columns = 7;

$assoc = JLanguageAssociations::isEnabled();

// Check for at least one editablewatchdog
$isEditable = false;
$nullDate = JFactory::getDbo()->getNullDate();
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
<div class="archive<?php echo $this->pageclass_sfx; ?>">
<div class="page-header">
<h1>
    <?php echo JText::_('COM_LOGMONITER_WATCHDOGS'); ?>
</h1>
</div>
<form name="adminForm" id="adminForm" action="<?php echo JRoute::_('index.php?option=com_logmoniter&view=moniter'); ?>" method="post">
    <?php echo JText::_('COM_LOGMONITER_WATCHDOGS_FILTER'); ?>
        <?php
            echo JLayoutHelper::render(
                'joomla.searchtools.default',
                array('view' => $this)
            );
        ?>

        <?php if (empty($this->items)) : ?>
            <div class="alert alert-no-items">
                <?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
            </div>
        <?php else : ?>
        <div class="table-responsive w-auto">
            <table class="table table-striped table-hover" id="watchdogList">
                <thead>
                    <tr>
                        <th class="nowrap hidden-phone center" scope="col"><?php  echo JText::_('COM_LOGMONITER_NUM'); ?></th>
                        <th class="hidden-phone" scope="col"><?php echo JHtml::_('grid.sort', 'COM_LOGMONITER_WATCHDOG_DETAILS_TITLE', 'wd.title', $listDirn, $listOrder); ?></th>
                        <th class="nowrap hidden-phone center" scope="col"><?php  echo JHtml::_('grid.sort', 'COM_LOGMONITER_WATCHDOG_DETAILS_NEXT_DUE_DATE', 'wd.next_due_date', $listDirn, $listOrder); ?></th>
                        <th class="nowrap hidden-phone center" scope="col"><?php echo JHtml::_('grid.sort', 'COM_LOGMONITER_WATCHDOG_DETAILS_LATEST_LOG_DATE', 'wd.latest_log_date', $listDirn, $listOrder); ?></th>
                        <th class="nowrap hidden-phone center" scope="col"><?php  echo JHtml::_('grid.sort', 'COM_LOGMONITER_WATCHDOG_DETAILS_LOGS', 'wd.log_count', $listDirn, $listOrder); ?></th>
                        <th class="nowrap hidden-phone center" scope="col"><?php  echo JHtml::_('grid.sort', 'COM_LOGMONITER_WATCHDOG_DETAILS_HITS', 'wd.hits', $listDirn, $listOrder); ?></th>
                        <?php if ($isEditable) : ?>
                            <th class="nowrap hidden-phone center" scope="col" id="categorylist_header_edit"><?php echo JText::_('COM_LOGMONITER_FORM_EDIT_WATCHDOG'); ?></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <td colspan="<?php echo $columns; ?>">
                            <?php echo $this->pagination->getListFooter(); ?>
                        </td>
                    </tr>
                </tfoot>
                <tbody>
                    <?php if (!empty($this->items)) : ?>
                        <?php foreach ($this->items as $i => $item) :
                            $canEdit = $user->authorise('core.edit', 'com_logmoniter.watchdog.'.$item->id);
                            $canEditOwn = $user->authorise('core.edit.own', 'com_logmoniter.watchdog.'.$item->id) && $item->created_by == $userId;
                            $canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
                        ?>
                            <tr>
                                <td class="hidden-phone center"><?php echo $this->pagination->getRowOffset($i); ?></td>
                                <td class="hidden-phone">
                                    <div class="pull-left">
                                        <a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_logmoniter&view=watchdog&id='.$item->id); ?>" title="<?php echo JText::_('COM_LOGMONITER_VIEW_WATCHDOG'); ?>">
                                            <?php echo $this->escape($item->title); ?>
                                        </a>
                                        <blockquote class="blockquote text-wrap">
                                            <?php echo JText::_('COM_LOGMONITER_WATCHDOG_DETAILS_INSET').': '.$this->escape($item->inset_title); ?>
                                        </blockquote>
                                        <blockquote class="blockquote text-wrap">
                                            <?php echo JText::_('COM_LOGMONITER_WATCHDOG_DETAILS_BPRINT').': '.$this->escape($item->bprint_title); ?>
                                        </blockquote>
                                        <div class="badge badge-info">
                                            <?php echo JText::_('COM_LOGMONITER_WATCHDOG_DETAILS_TINTERVAL').': '.$this->escape($item->tinterval_title); ?>
                                        </div>
                                        <div class="badge badge-success">
                                            <?php echo JText::_('COM_LOGMONITER_WATCHDOG_DETAILS_WCENTER').': '.$this->escape($item->wcenter_title); ?>
                                        </div>
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
                                    </div>

                                </td>
                                <td class="hidden-phone center">
                                    <?php if ($item->next_due_date == $nullDate) : ?>
                                        <?php echo '---'; ?>
                                    <?php else : ?>
                                        <?php echo JHTML::_('date', $item->next_due_date, JText::_('DATE_FORMAT_LC')); ?>
                                    <?php endif; ?>
                                </td>
                                <td class="hidden-phone center">
                                    <?php if ($item->latest_log_date == $nullDate) : ?>
                                        <?php echo '---'; ?>
                                    <?php else : ?>
                                        <?php echo JHTML::_('date', $item->latest_log_date, JText::_('DATE_FORMAT_LC')); ?>
                                    <?php endif; ?>
                                </td>
                                <td class="hidden-phone center"><span class="badge badge-warning"><?php echo (int) $item->log_count; ?></span></td>
                                <td class="hidden-phone center"><span class="badge badge-info"><?php echo (int) $item->hits; ?></span></td>
                                <td class="has-context">
                                    <div class="hidden-phone center">
                                        <?php if ($canEdit || $canEditOwn) : ?>
                                            <?php if ($canCheckin) : ?>
                                                <?php if ($item->checked_out) : ?>
                                                    <?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'watchdogs.', $canCheckin); ?>
                                                    </br>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            <?php echo JHtml::_('icon.edit', $item, $item->params); ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    <input type="hidden" name="filter_order" value="" />
    <input type="hidden" name="filter_order_Dir" value="" />
    <input type="hidden" name="limitstart" value="" />
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="return" value="<?php echo $this->return_page; ?>" />
    <?php echo JHtml::_('form.token'); ?>

</form>

