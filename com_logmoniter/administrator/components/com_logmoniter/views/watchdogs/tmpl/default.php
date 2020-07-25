<?php
/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

JHtml::_('formbehavior.chosen', 'select');
JHtml::_('bootstrap.tooltip');

$app = JFactory::getApplication();
$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'l.ordering';
$archived = $this->state->get('filter.published') == 2 ? true : false;
$trashed = $this->state->get('filter.published') == -2 ? true : false;

if (strpos($listOrder, 'publish_up') !== false) {
    $orderingColumn = 'publish_up';
} elseif (strpos($listOrder, 'publish_down') !== false) {
    $orderingColumn = 'publish_down';
} elseif (strpos($listOrder, 'modified') !== false) {
    $orderingColumn = 'modified';
} else {
    $orderingColumn = 'created';
}

if ($saveOrder) {
    $saveOrderingUrl = 'index.php?option=COM_LOGMONITER&task=watchdogs.saveOrderAjax&tmpl=component';
    JHtml::_('sortablelist.sortable', 'watchdogList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
?>
<form action="index.php?option=COM_LOGMONITER&view=watchdogs" method="post" id="adminForm" name="adminForm">
    <?php if (!empty($this->sidebar)) : ?>
      <div id="j-sidebar-container" class="span2">
          <?php echo $this->sidebar; ?>
      </div>
      <div id="j-main-container" class="span10">
    <?php else : ?>
      <div id="j-main-container">
    <?php endif; //Note: The 2 divs above are closed by the system.?>
    <?php // Search tools bar
        echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
    ?>
	<div class="clr"> </div>
    <?php if (empty($this->items)) : ?>
      <div class="alert alert-no-items">
          <?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
      </div>
    <?php else : ?>
      <table class="table table-striped" id="watchdogList">
        <thead>
          <tr>
            <th width="1%" class="nowrap center hidden-phone"> <!--Odering Handle -->
                <?php echo JHtml::_('searchtools.sort', '', 'wd.ordering', $listDirn, $listOrder, null, 'DESC', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
            </th>
            <th width="1%" class="center"> <!-- Selection Checkboxes -->
                <?php echo JHtml::_('grid.checkall'); ?>
            </th>
            <th width="1%" class="nowrap center"><!--Status -->
                <?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'l.state', $listDirn, $listOrder); ?>
            </th>
			<th width="1%"><!--ID -->
                <?php echo JHtml::_('grid.sort', 'J_ID', 'id', $listDirn, $listOrder); ?>
            </th>
            <th width="20%" class="wrap hidden-phone"><!--INSTRUCTION SET-->
                <?php echo JHtml::_('searchtools.sort', 'COM_LOGMONITER_INSET_TITLE', 'inset', $listDirn, $listOrder); ?>
            </th>
            <th width="20%" class="wrap hidden-phone"><!--Blueprint TITLE-->
                <?php echo JHtml::_('searchtools.sort', 'COM_LOGMONITER_BLUEPRINT_TITLE', 'bprint', $listDirn, $listOrder); ?>
            </th>
            <th width="10%" class="nowrap hidden-phone"><!--Work Center Title -->
              <?php echo JHtml::_('searchtools.sort', 'COM_LOGMONITER_WCENTER_TITLE', 'wcenter', $listDirn, $listOrder); ?>
            </th>
            <th width="5%" class="nowrap hidden-phone"><!--FREQUENCY-->
                <?php echo JHtml::_('searchtools.sort', 'COM_LOGMONITER_TINTERVAL_TITLE', 'tinterval', $listDirn, $listOrder); ?>
            </th>
            <th width="5%" class="nowrap hidden-phone"><!--WD START DATE-->
                <?php echo JHtml::_('searchtools.sort', 'COM_LOGMONITER_START_DATE', 'wd.log_start_date', $listDirn, $listOrder); ?>
            </th>
            <th width="5%" class="nowrap hidden-phone"><!--WD END DUE DATE-->
                <?php echo JHtml::_('searchtools.sort', 'COM_LOGMONITER_END_DATE', 'wd.log_end_date', $listDirn, $listOrder); ?>
            </th>
            <th width="5%" class="nowrap hidden-phone"><!--Acess-->
                <?php echo JHtml::_('searchtools.sort', 'J_ACESS', 'access', $listDirn, $listOrder); ?>
            </th>
			<th width="5%" class="nowrap hidden-phone"><!--CREATED BY -->
                <?php echo JHtml::_('searchtools.sort', 'COM_LOGMONITER_CREATED_BY', 'wd.created_by', $listDirn, $listOrder); ?>
            </th>
          </tr>
		</thead>
			<tfoot>
				<tr>
					<td colspan="12">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php if (!empty($this->items)) : ?>
					<?php foreach ($this->items as $i => $row) :
                        $item->max_ordering = 0;
                        $ordering = ($listOrder == 'wd.ordering');
                        $canEdit = $user->authorise('core.edit', 'COM_LOGMONITER.watchdog.'.$item->id);
                        $canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
                        $canEditOwn = $user->authorise('core.edit.own', 'COM_LOGMONITER.watchdog.'.$item->id) && $item->created_by == $userId;
                        $canChange = $user->authorise('core.edit.state', 'COM_LOGMONITER.watchdog.'.$item->id) && $canCheckin;
                    ?>
						<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->isid; ?>">
							<td class="order nowrap center hidden-phone"><!--Ordering Handle -->
								<?php
                                    $iconClass = '';
                                    if (!$canChange) {
                                        $iconClass = ' inactive';
                                    } elseif (!$saveOrder) {
                                        $iconClass = ' inactive tip-top hasTooltip" title="'.JHtml::_('tooltipText', 'JORDERINGDISABLED');
                                    }
                                ?>
								<span class="sortable-handler<?php echo $iconClass; ?>">
									<span class="icon-menu" aria-hidden="true"></span>
								</span>
								<?php if ($canChange && $saveOrder) : ?>
									<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order" />
								<?php endif; ?>
							</td>
							<td class="center"><!--Selection Checkboxes -->
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							</td>
							<td class="center"><!--Status -->
								<div class="btn-group">
									<?php echo JHtml::_('jgrid.published', $item->state, $i, 'locations.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
									<?php // Create dropdown items and render the dropdown list.
                                    if ($canChange) {
                                        JHtml::_('actionsdropdown.'.((int) $item->state === 2 ? 'un' : '').'archive', 'cb'.$i, 'locations');
                                        JHtml::_('actionsdropdown.'.((int) $item->state === -2 ? 'un' : '').'trash', 'cb'.$i, 'locations');
                                        echo JHtml::_('actionsdropdown.render', $this->escape($item->title));
                                    }
                                    ?>
								</div>
							</td>
							<td class="has-context"><!--ID -->
								<div class="pull-left break-word">
									<?php if ($item->checked_out) : ?>
										<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'watchdogs.', $canCheckin); ?>
									<?php endif; ?>
									<?php if ($canEdit || $canEditOwn) : ?>
										<a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=COM_LOGMONITER&task=watchdog.edit&id='.$item->id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?>">
											<?php echo (int) $item->id; ?></a>
									<?php endif; ?>
								</div>
							</td>
							<td class="left"><!--INSET-->
								<?php $this->escape($item->inset); ?>
							</td>
							<td class="left"><!--BLUEPRINT-->
								<?php $this->escape($item->bprint); ?>
							</td>
							<td class="left"><!--WORKCENTER-->
								<?php $this->escape($item->wcenter); ?>
							</td>
							<td class="left"><!--freq.-->
								<?php $this->escape($item->tinterval); ?>
							</td>
							<td class="nowrap small hidden-phone"><!--Start Date -->
								<?php
                                $date = $item->{$orderingColumn};
                                echo $date > 0 ? JHtml::_('date', $date, JText::_('DATE_FORMAT_LC4')) : '-';
                                ?>
							</td>
							<td class="nowrap small hidden-phone"><!--End Date -->
								<?php
                                $date = $item->{$orderingColumn};
                                echo $date > 0 ? JHtml::_('date', $date, JText::_('DATE_FORMAT_LC4')) : '-';
                                ?>
							</td>
							<td class="left"><!--access-->
								<?php $this->escape($item->access); ?>
							</td>
							<td class="small hidden-phone"><!--Created By -->
								<?php if ((int) $item->created_by != 0) : ?>
									<?php if ($item->created_by_alias) : ?>
										<a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_users&task=user.edit&id='.(int) $item->created_by); ?>" title="<?php echo JText::_('JAUTHOR'); ?>">
										<?php echo $this->escape($item->author_name); ?></a>
										<div class="smallsub"><?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->created_by_alias)); ?></div>
									<?php else : ?>
										<a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_users&task=user.edit&id='.(int) $item->created_by); ?>" title="<?php echo JText::_('JAUTHOR'); ?>">
										<?php echo $this->escape($item->author_name); ?></a>
									<?php endif; ?>
								<?php else : ?>
									<?php if ($item->created_by_alias) : ?>
										<?php echo JText::_('JNONE'); ?>
										<div class="smallsub"><?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->created_by_alias)); ?></div>
									<?php else : ?>
										<?php echo JText::_('JNONE'); ?>
									<?php endif; ?>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	<?php endif; ?>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name='filter_order_Dir' value="<?php echo $listDirn; ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</div>
</form>
