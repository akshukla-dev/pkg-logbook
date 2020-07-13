<?php
/**
 * @copyright   Copyright (C) 2020 Amit Kumar Shukla, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', '.multipleLocattions', null, array('placeholder_text_multiple' => JText::_('COM_LOGBOOK_OPTION_SELECT_LOCATIONS')));
JHtml::_('formbehavior.chosen', 'select');

$app       = JFactory::getApplication();
$user      = JFactory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'b.ordering';
$archived = $this->state->get('filter.published') == 2 ? true : false;
$trashed = $this->state->get('filter.published') == -2 ? true : false;
$canOrder = $user->authorise('core.edit.state', 'com_logbook.category');
$saveOrder = $listOrder == 'b.ordering';

if (strpos($listOrder, 'publish_up') !== false)
{
	$orderingColumn = 'publish_up';
}
elseif (strpos($listOrder, 'publish_down') !== false)
{
	$orderingColumn = 'publish_down';
}
elseif (strpos($listOrder, 'modified') !== false)
{
	$orderingColumn = 'modified';
}
else
{
	$orderingColumn = 'created';
}

if($saveOrder) {
  $saveOrderingUrl = 'index.php?option=com_logbook&task=blueprints.saveOrderAjax&tmpl=component';
  JHtml::_('sortablelist.sortable', 'bluprintList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

?>
<form action="index.php?option=com_logbook&view=blueprints" method="post" id="adminForm" name="adminForm">
	<?php if (!empty( $this->sidebar)) : ?>
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
	<?php if (empty($this->items)) : ?>
		<div class="alert alert-no-items">
			<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
		</div>
	<?php else : ?>
		<table class="table table-striped" id="blueprintList">
			<thead>
				<tr>
					<th width="1%" class="nowrap center hidden-phone"> <!--Odering Handle -->
						<?php echo JHtml::_('searchtools.sort', '', 'b.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
					</th>
					<th width="1%" class="right"> <!--Numbering -->
						<?php echo JText::_('COM_LOGBOOK_NUM'); ?>
					</th>
					<th width="1%" class="center"> <!-- Selection Checkboxes -->
						<?php echo JHtml::_('grid.checkall'); ?>
					</th>
					<th width="1%" class="nowrap center"><!--Status -->
						<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'b.state', $listDirn, $listOrder); ?>
					</th>
					<th style="min-width:100px" class="nowrap"><!--Title-->
						<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'b.title', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone"><!--Created By -->
						<?php echo JHtml::_('searchtools.sort',  'JAUTHOR', 'b.created_by', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone"><!--Date -->
						<?php echo JHtml::_('searchtools.sort', 'COM_CONTENT_HEADING_DATE_' . strtoupper($orderingColumn), 'b.' . $orderingColumn, $listDirn, $listOrder); ?>
					</th>
					<th width="10%"><!--Associated Locations -->
						<?php echo JText::_('COM_LOGBOOK_BLUPRINT_LOCATIONS'); ?>
					</th>
					<th width="10%"><!--Frequency -->
						<?php echo JHtml::_('searchtools.sort', 'COM_LOGBOOK_BLUPRINT_FREQUENCY', 'b.frequency',$listDirn, $listOrder); ?>
					</th>
					<th width="1%"><!--ID -->
						<?php echo JHtml::_('grid.sort', 'J_ID', 'id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="10">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php if (!empty($this->items)) : ?>
					<?php foreach ($this->items as $i => $row) :
						$item->max_ordering = 0;
						$ordering   = ($listOrder == 'b.ordering');
						$canCreate  = $user->authorise('core.create',     'com_logbook.category.' . $item->catid);
						$canEdit    = $user->authorise('core.edit',       'com_logbook.blueprint.' . $item->id);
						$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
						$canEditOwn = $user->authorise('core.edit.own',   'com_logbook.blueprint.' . $item->id) && $item->created_by == $userId;
						$canChange  = $user->authorise('core.edit.state', 'com_logbook.blueprint.' . $item->id) && $canCheckin;
						$canEditCat    = $user->authorise('core.edit',       'com_logbook.category.' . $item->catid);
						$canEditOwnCat = $user->authorise('core.edit.own',   'com_logbook.category.' . $item->catid) && $item->category_uid == $userId;
						$canEditParCat    = $user->authorise('core.edit',       'com_logbook.category.' . $item->parent_category_id);
						$canEditOwnParCat = $user->authorise('core.edit.own',   'com_logbook.category.' . $item->parent_category_id) && $item->parent_category_uid == $userId;
					?>
						<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->catid; ?>">
							<td class="order nowrap center hidden-phone"><!--Ordering Handle -->
								<?php
									$iconClass = '';
									if (!$canChange)
									{
										$iconClass = ' inactive';
									}
									elseif (!$saveOrder)
									{
										$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::_('tooltipText', 'JORDERINGDISABLED');
									}
								?>
								<span class="sortable-handler<?php echo $iconClass ?>">
									<span class="icon-menu" aria-hidden="true"></span>
								</span>
								<?php if ($canChange && $saveOrder) : ?>
									<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order" />
								<?php endif; ?>
							</td>
							<td class="right"><!--Numbering -->
								<?php echo $this->pagination->getRowOffset($i); ?>
							</td>
							<td class="center"><!--Selection Checkboxes -->
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							</td>
							<td class="center"><!--Status -->
								<div class="btn-group">
									<?php echo JHtml::_('jgrid.published', $item->state, $i, 'blueprints.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
									<?php // Create dropdown items and render the dropdown list.
									if ($canChange)
									{
										JHtml::_('actionsdropdown.' . ((int) $item->state === 2 ? 'un' : '') . 'archive', 'cb' . $i, 'blueprints');
										JHtml::_('actionsdropdown.' . ((int) $item->state === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'blueprints');
										echo JHtml::_('actionsdropdown.render', $this->escape($item->title));
									}
									?>
								</div>
							</td>
							<td class="has-context"><!--Title-->
								<div class="pull-left break-word">
									<?php if ($item->checked_out) : ?>
										<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'blueprints.', $canCheckin); ?>
									<?php endif; ?>
									<?php if ($canEdit || $canEditOwn) : ?>
										<a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_logbook&task=blueprint.edit&id=' . $item->id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?>">
											<?php echo $this->escape($item->title); ?></a>
									<?php else : ?>
										<span title="<?php echo JText::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->alias)); ?>"><?php echo $this->escape($item->title); ?></span>
									<?php endif; ?>
									<span class="small break-word">
										<?php if (empty($item->note)) : ?>
											<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
										<?php else : ?>
											<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS_NOTE', $this->escape($item->alias), $this->escape($item->note)); ?>
										<?php endif; ?>
									</span>
									<div class="small">
										<?php
										$ParentCatUrl = JRoute::_('index.php?option=com_categories&task=category.edit&id=' . $item->parent_category_id . '&extension=com_logbook');
										$CurrentCatUrl = JRoute::_('index.php?option=com_categories&task=category.edit&id=' . $item->catid . '&extension=com_logbook');
										$EditCatTxt = JText::_('COM_CONTENT_EDIT_CATEGORY');

											echo JText::_('JCATEGORY') . ': ';

											if ($item->category_level != '1') :
												if ($item->parent_category_level != '1') :
													echo ' &#187; ';
												endif;
											endif;

											if (JFactory::getLanguage()->isRtl())
											{
												if ($canEditCat || $canEditOwnCat) :
													echo '<a class="hasTooltip" href="' . $CurrentCatUrl . '" title="' . $EditCatTxt . '">';
												endif;
												echo $this->escape($item->category_title);
												if ($canEditCat || $canEditOwnCat) :
													echo '</a>';
												endif;

												if ($item->category_level != '1') :
													echo ' &#171; ';
													if ($canEditParCat || $canEditOwnParCat) :
														echo '<a class="hasTooltip" href="' . $ParentCatUrl . '" title="' . $EditCatTxt . '">';
													endif;
													echo $this->escape($item->parent_category_title);
													if ($canEditParCat || $canEditOwnParCat) :
														echo '</a>';
													endif;
												endif;
											}
											else
											{
												if ($item->category_level != '1') :
													if ($canEditParCat || $canEditOwnParCat) :
														echo '<a class="hasTooltip" href="' . $ParentCatUrl . '" title="' . $EditCatTxt . '">';
													endif;
													echo $this->escape($item->parent_category_title);
													if ($canEditParCat || $canEditOwnParCat) :
														echo '</a>';
													endif;
													echo ' &#187; ';
												endif;
												if ($canEditCat || $canEditOwnCat) :
													echo '<a class="hasTooltip" href="' . $CurrentCatUrl . '" title="' . $EditCatTxt . '">';
												endif;
												echo $this->escape($item->category_title);
												if ($canEditCat || $canEditOwnCat) :
													echo '</a>';
												endif;
											}
										?>
									</div>
								</div>
							</td>
							<td class="small hidden-phone"><!--Created By -->
								<?php if ((int) $item->created_by != 0) : ?>
									<?php if ($item->created_by_alias) : ?>
										<a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_users&task=user.edit&id=' . (int) $item->created_by); ?>" title="<?php echo JText::_('JAUTHOR'); ?>">
										<?php echo $this->escape($item->author_name); ?></a>
										<div class="smallsub"><?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->created_by_alias)); ?></div>
									<?php else : ?>
										<a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_users&task=user.edit&id=' . (int) $item->created_by); ?>" title="<?php echo JText::_('JAUTHOR'); ?>">
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
							<td class="nowrap small hidden-phone"><!--Date -->
								<?php
								$date = $item->{$orderingColumn};
								echo $date > 0 ? JHtml::_('date', $date, JText::_('DATE_FORMAT_LC4')) : '-';
								?>
							</td>
							<td class="hidden-phone"><!--Associated Locations -->
								<span class="badge badge-info">
									<?php echo $item->locations; ?>
								</span>
							</td>
							<td class="hidden-phone"><!--Frequency -->
								<span class="badge badge-info" >
									<?php echo (int) $item->frequency; ?>
								</span>
							</td>
							<td class="hidden-phone"><!--ID -->
								<?php echo (int) $item->id; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
	</table>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name='filter_order_Dir' value="<?php echo $listDirn; ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</form>
