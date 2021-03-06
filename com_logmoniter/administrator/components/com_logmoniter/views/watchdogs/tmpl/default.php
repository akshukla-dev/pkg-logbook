<?php
/**
 * @copyright Copyright (c)2020 Amit Kumar Shukla. All Rights Reserved.
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$app = JFactory::getApplication();
$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'wd.ordering';
$columns = 15;

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
    $saveOrderingUrl = 'index.php?option=com_logmoniter&task=watchdogs.saveOrderAjax&tmpl=component';
    JHtml::_('sortablelist.sortable', 'watchdogList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

$assoc = JLanguageAssociations::isEnabled();
?>

<form action="<?php echo JRoute::_('index.php?option=com_logmoniter&view=watchdogs'); ?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty($this->sidebar)) : ?>
    <div id="j-sidebar-container" class="span2">
        <?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10">
<?php else : ?>
    <div id="j-main-container">
<?php endif; ?>
        <?php
        // Search tools bar
        echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
        ?>
        <?php if (empty($this->items)) : ?>
            <div class="alert alert-no-items">
                <?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
            </div>
        <?php else : ?>
            <table class="table table-striped" id="watchdogList">
                <thead>
                    <tr>
                        <th width="1%" class="nowrap center hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', '', 'wd.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
                        </th>
                        <th width="1%" class="center">
                            <?php echo JHtml::_('grid.checkall'); ?>
                        </th>
                        <th width="1%" class="nowrap center">
                            <?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'wd.state', $listDirn, $listOrder); ?>
                        </th>
                        <th style="min-width:100px" class="nowrap">
                            <?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'wd.title', $listDirn, $listOrder); ?>
                        </th>
                        <th width="5%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'wd.access', $listDirn, $listOrder); ?>
                        </th>
                        <?php if ($assoc) : ?>
                            <?php ++$columns; ?>
                            <th width="5%" class="nowrap hidden-phone">
                                <?php echo JHtml::_('searchtools.sort', 'COM_LOGMONITER_HEADING_ASSOCIATION', 'association', $listDirn, $listOrder); ?>
                            </th>
                        <?php endif; ?>
                        <th width="5%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'JAUTHOR', 'wd.created_by', $listDirn, $listOrder); ?>
                        </th>
                        <th width="5%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
                        </th>
                        <th width="5%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'COM_LOGMONITER_HEADING_DATE_'.strtoupper($orderingColumn), 'wd.'.$orderingColumn, $listDirn, $listOrder); ?>
                        </th>
                        <th width="1%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'JGLOBAL_HITS', 'wd.hits', $listDirn, $listOrder); ?>
                        </th>
                        <th width="1%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'COM_LOGMONITER_LOGS', 'wd.log_count', $listDirn, $listOrder); ?>
                        </th>
                        <th width="1%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'wd.id', $listDirn, $listOrder); ?>
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
                    $ordering = ($listOrder == 'wd.ordering');
                    $canCreate = $user->authorise('core.create', 'com_logmoniter.category.'.$item->catid);
                    $canEdit = $user->authorise('core.edit', 'com_logmoniter.watchdog.'.$item->id);
                    $canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
                    $canEditOwn = $user->authorise('core.edit.own', 'com_logmoniter.watchdog.'.$item->id) && $item->created_by == $userId;
                    $canChange = $user->authorise('core.edit.state', 'com_logmoniter.watchdog.'.$item->id) && $canCheckin;
                    ?>
                    <tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->catid; ?>">
                        <td class="order nowrap center hidden-phone">
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
                        <td class="center">
                            <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                        </td>
                        <td class="center">
                            <div class="btn-group">
                                <?php echo JHtml::_('jgrid.published', $item->state, $i, 'watchdogs.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
                                <?php // Create dropdown items and render the dropdown list.
                                if ($canChange) {
                                    JHtml::_('actionsdropdown.'.((int) $item->state === 2 ? 'un' : '').'archive', 'cb'.$i, 'watchdogs');
                                    JHtml::_('actionsdropdown.'.((int) $item->state === -2 ? 'un' : '').'trash', 'cb'.$i, 'watchdogs');
                                    echo JHtml::_('actionsdropdown.render', $this->escape($item->title));
                                }
                                ?>
                            </div>
                        </td>
                        <td class="has-context">
                            <div class="pull-left break-word">
                                <?php if ($item->checked_out) : ?>
                                    <?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'watchdogs.', $canCheckin); ?>
                                <?php endif; ?>
                                <?php if ($canEdit || $canEditOwn) : ?>
                                    <a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_logmoniter&task=watchdog.edit&id='.$item->id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?>">
                                        <?php echo $this->escape($item->title); ?></a>
                                <?php else : ?>
                                    <span title="<?php echo JText::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->alias)); ?>"><?php echo $this->escape($item->title); ?></span>
                                <?php endif; ?>
                                <span class="small break-word">
                                    <?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
                                </span>
                                <div class="small">
                                    <?php echo JText::_('JCATEGORY').': '.$this->escape($item->category_title); ?>
                                </div>
                                <div class="small">
                                    <?php echo JText::_('COM_LOGMONITER_INSET').': '.$this->escape($item->inset_title); ?>
                                </div>
                                <div class="small">
                                    <?php echo JText::_('COM_LOGMONITER_BPRINT').': '.$this->escape($item->bprint_title); ?>
                                </div>
                                <div class="small">
                                    <?php echo JText::_('COM_LOGMONITER_WCENTER').': '.$this->escape($item->wcenter_title); ?>
                                </div>
                                <div class="small">
                                    <?php echo JText::_('COM_LOGMONITER_TINTERVAL').': '.$this->escape($item->tinterval_title); ?>
                                </div>
                                <div class="small">
                                    <?php
                                        $date = $item->latest_log_date;
                                        echo $date > 0 ? JText::_('COM_LOGMONITER_LATEST_LOG_DATE').': '.JHtml::_('date', $date, JText::_('DATE_FORMAT_LC')) : JText::_('COM_LOGMONITER_LATEST_LOG_DATE').': '.'-';
                                    ?>
                                </div>
                                <div class="small">
                                    <?php
                                            $date = $item->next_due_date;
                                            echo $date > 0 ? JText::_('COM_LOGMONITER_NEXT_DUE_DATE').': '.JHtml::_('date', $date, JText::_('DATE_FORMAT_LC')) : JText::_('COM_LOGMONITER_NEXT_DUE_DATE').': '.'-';
                                        ?>
                                    </div>
                            </div>
                        </td>
                        <td class="small hidden-phone">
                            <?php echo $this->escape($item->access_level); ?>
                        </td>
                        <?php if ($assoc) : ?>
                        <td class="hidden-phone">
                            <?php if ($item->association) : ?>
                                <?php echo JHtml::_('contentadministrator.association', $item->id); ?>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                        <td class="small hidden-phone">
                            <?php if ($item->created_by_alias) : ?>
                                <a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_users&task=user.edit&id='.(int) $item->created_by); ?>" title="<?php echo JText::_('JAUTHOR'); ?>">
                                <?php echo $this->escape($item->author_name); ?></a>
                                <div class="small"><?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->created_by_alias)); ?></div>
                            <?php else : ?>
                                <a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_users&task=user.edit&id='.(int) $item->created_by); ?>" title="<?php echo JText::_('JAUTHOR'); ?>">
                                <?php echo $this->escape($item->author_name); ?></a>
                            <?php endif; ?>
                        </td>
                        <td class="small hidden-phone">
                            <?php echo JLayoutHelper::render('joomla.content.language', $item); ?>
                        </td>
                        <td class="nowrap small hidden-phone">
                            <?php
                            $date = $item->{$orderingColumn};
                            echo $date > 0 ? JHtml::_('date', $date, JText::_('DATE_FORMAT_LC')) : '-';
                            ?>
                        </td>
                        <td class="hidden-phone center">
                            <span class="badge badge-info">
                                <?php echo (int) $item->hits; ?>
                            </span>
                        </td>
                        <td class="hidden-phone center">
                            <span class="badge badge-info">
                                <?php echo (int) $item->log_count; ?>
                            </span>
                        </td>
                        <td class="hidden-phone">
                            <?php echo (int) $item->id; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php // Load the batch processing form.?>
            <?php if ($user->authorise('core.create', 'com_logmoniter')
                && $user->authorise('core.edit', 'com_logmoniter')
                && $user->authorise('core.edit.state', 'com_logmoniter')) : ?>
                <?php echo JHtml::_(
                    'bootstrap.renderModal',
                    'collapseModal',
                    array(
                        'title' => JText::_('COM_LOGMONITER_BATCH_OPTIONS'),
                        'footer' => $this->loadTemplate('batch_footer'),
                    ),
                    $this->loadTemplate('batch_body')
                ); ?>
            <?php endif; ?>
        <?php endif; ?>

        <?php echo $this->pagination->getListFooter(); ?>

        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
