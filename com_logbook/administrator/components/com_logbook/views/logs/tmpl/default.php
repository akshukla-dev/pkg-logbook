<?php
/**
 * @package     Joomll.Administrator
 * @subpackage  com_logbook
 *
 * @copyright   Copyright (C) 2020 Amit Kumar Shukla, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('behavior.tabstate');
JHtml::_('formbehavior.chosen', 'select');

$app       = JFactory::getApplication();
$user      = JFactory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$archived = $this->state->get('filter.published') == 2 ? true : false;
$trashed = $this->state->get('filter.published') == -2 ? true : false;
$canOrder = $user->authorise('core.edit.state', 'com_lrm.category');
$saveOrder = $listOrder == 'l.ordering';
$columns   = 12;

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

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_logbook&task=logs.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'logList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
?>

<form action="<?php echo JRoute::_('index.php?option=com_logbook&view=logs'); ?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif; //Note: The 2 divs above are closed by the system. ?>
		<?php
		// Search tools bar
		echo JLayoutHelper::render('joomll.searchtools.default', array('view' => $this));
		?>
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
						<th width="1%" class="center">
							<?php echo JHtml::_('grid.checkall'); ?>
						</th>
						<th width="1%" class="nowrap center">
							<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'l.state', $listDirn, $listOrder); ?>
						</th>
						<th style="min-width:100px" class="nowrap">
							<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'l.title', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort',  'JGRID_HEADING_ACCESS', 'l.access', $listDirn, $listOrder); ?>
						</th>
                        <th width="10%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort',  'COM_LOGBOOK_HEADING_UPLOADEDBY_LABEL', 'l.created.by', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort',  'COM_LOGBOOK_HEADING_AUTHOR_LABEL', 'l.author', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'COM_CONTENT_HEADING_DATE_' . strtoupper($orderingColumn), 'l.' . $orderingColumn, $listDirn, $listOrder); ?>
						</th>
                        <th width="1%">
                            <?php echo JHtml::_('searchtools.sort', 'COM_LOGBOOK_HEADING_DOWNLOADS', 'l.downloads', $listDirn, $listOrder); ?>
                        </th>
						<th width="1%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_HITS', 'l.hits', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'l.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="<?php echo $columns; ?>">
						</td>
					</tr>
				</tfoot>
				<tbody>
				<?php foreach ($this->items as $i => $item) :
					$item->max_ordering = 0;
					$ordering   = ($listOrder == 'l.ordering');
					$canCreate  = $user->authorise('core.create',     'com_logbook.category.' . $item->catid);
					$canEdit    = $user->authorise('core.edit',       'com_logbook.log.' . $item->id);
					$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
					$canEditOwn = $user->authorise('core.edit.own',   'com_logbook.log.' . $item->id) && $item->created_by == $userId;
					$canChange  = $user->authorise('core.edit.state', 'com_logbook.log.' . $item->id) && $canCheckin;
					$canEditCat    = $user->authorise('core.edit',       'com_logbook.category.' . $item->catid);
					$canEditOwnCat = $user->authorise('core.edit.own',   'com_logbook.category.' . $item->catid) && $item->category_uid == $userId;
					$canEditParCat    = $user->authorise('core.edit',       'com_logbook.category.' . $item->parent_category_id);
					$canEditOwnParCat = $user->authorise('core.edit.own',   'com_logbook.category.' . $item->parent_category_id) && $item->parent_category_uid == $userId;
				?>
					<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->catid; ?>">
						<td class="order nowrap center hidden-phone">
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
						<td class="center">
							<?php echo JHtml::_('grid.id', $i, $item->id); ?>
						</td>
						<td class="center">
							<div class="btn-group">
								<?php echo JHtml::_('jgrid.published', $item->state, $i, 'logs.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
								<?php // Create dropdown items and render the dropdown list.
								if ($canChange)
								{
									JHtml::_('actionsdropdown.' . ((int) $item->state === 2 ? 'un' : '') . 'archive', 'cb' . $i, 'logs');
									JHtml::_('actionsdropdown.' . ((int) $item->state === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'logs');
									echo JHtml::_('actionsdropdown.render', $this->escape($item->title));
								}
								?>
							</div>
						</td>
						<td class="has-context">
							<div class="pull-left break-word">
								<?php if ($item->checked_out) : ?>
									<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'logs.', $canCheckin); ?>
								<?php endif; ?>
								<?php if ($canEdit || $canEditOwn) : ?>
									<a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_logbook&task=log.edit&id=' . $item->id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?>">
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
						<td class="small hidden-phone">
							<?php echo $this->escape($item->access_level); ?>
						</td>
						<td class="small hidden-phone">
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
                        <td class="nowrap small hidden-phone">
                            <?php echo $this->escape($item->author); ?>
                        </td>
						<td class="small hidden-phone">
							<?php echo JLayoutHelper::render('joomll.content.language', $item); ?>
						</td>
						<td class="nowrap small hidden-phone">
							<?php
							$date = $item->{$orderingColumn};
							echo $date > 0 ? JHtml::_('date', $date, JText::_('DATE_FORMAT_LC4')) : '-';
							?>
						</td>
                        <td class="center">
                            <?php echo (int) $item->downloads; ?>
                        </td>
						<td class="hidden-phone center">
							<span class="badge badge-info">
								<?php echo (int) $item->hits; ?>
							</span>
						</td>
						<td class="hidden-phone">
							<?php echo (int) $item->id; ?>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<?php echo $this->pagination->getListFooter(); ?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
        <input type="hidden" name="option" value="com_lrm" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
