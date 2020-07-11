<?php
/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

JHtml::_('formbehavior.chosen', 'select');

$listOrder     = $this->escape($this->filter_order);
$listDirn      = $this->escape($this->filter_order_Dir);
?>
<form action="index.php?option=com_logbook&view=locations" method="post" id="adminForm" name="adminForm">
	<div class="row-fluid">
		<div class="span9">
			<?php echo JText::_('COM_LOGBOOK_LOCATIONS_FILTER'); ?>
			<?php
				echo JLayoutHelper::render(
					'joomla.searchtools.default',
					array('view' => $this)
				);
			?>
		</div>
	</div>
	<table class="table table-striped table-hover">
		<thead>
		<tr>
			<th width="2%"><?php echo JText::_('COM_LOGBOOK_NUM'); ?></th>
			<th width="3%">
				<?php echo JHtml::_('grid.checkall'); ?>
			</th>
			<th width="90%">
				<?php echo JHtml::_('grid.sort', 'COM_LOGBOOK_LOCATIONS_NAME', 'name', $listDirn, $listOrder); ?>
			</th>
			<th width="8%">
				<?php echo JHtml::_('grid.sort', 'COM_LOGBOOK_PUBLISHED', 'state', $listDirn, $listOrder); ?>
			</th>
			<th width="2%">
				<?php echo JHtml::_('grid.sort', 'COM_LOGBOOK_ID', 'id', $listDirn, $listOrder); ?>
			</th>
		</tr>
		</thead>
		<tbody>
			<?php if (!empty($this->items)) : ?>
				<?php foreach ($this->items as $i => $row) :
                    $link = JRoute::_('index.php?
					option=com_logbook&task=location.edit&id='.$row->id);
                    ?>

					<tr>
						<td>
							<?php echo $this->pagination->getRowOffset($i); ?>
						</td>
						<td>
							<?php echo JHtml::_('grid.id', $i, $row->id); ?>
						</td>
						<td>
							<a href="<?php echo $link; ?>" title="<?php echo JText::_('COM_LOGBOOK_EDIT_LOCATION'); ?>">
							<?php echo $row->name; ?>
						</td>
						<td align="center">
							<?php echo JHtml::_('jgrid.published', $row->state, $i, 'locations.', true, 'cb'); ?>
						</td>
						<td align="center">
							<?php echo $row->id; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="5">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
	</table>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name='filter_order_Dir' value="<?php echo $listDirn; ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</form>
