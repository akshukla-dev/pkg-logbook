
<?php
/**
 * @package Logbook
 * @copyright Copyright (c)2020 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */


defined('_JEXEC') or die; // No direct access

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('behavior.tabstate');
JHtml::_('formbehavior.chosen', 'select');


$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
?>

<form action="<?php echo JRoute::_('index.php?option=com_logbook&view=folders');?>" method="post" name="adminForm" id="adminForm">

<?php if (!empty($this->sidebar)) : ?>
  <div id="j-sidebar-container" class="span2">
    <?php echo $this->sidebar; ?>
  </div>
  <div id="j-main-container" class="span10">
<?php else : ?>
  <div id="j-main-container">
<?php endif; //Note: The 2 divs above are closed by the system. ?>

<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); // Search tools bar ?>

    <div class="clr"> </div>
    <?php if (empty($this->items)) : ?>
        <div class="alert alert-no-items">
        <?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
        </div>
    <?php else : ?>
        <table class="table table-striped" id="folderList">
            <thead>
                <tr>
                <th width="1%" class="hidden-phone">
                <?php echo JHtml::_('grid.checkall'); ?>
                </th>
                <th>
                <?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'f.title', $listDirn, $listOrder); ?>
                </th>
                <th width="20%">
                <?php echo JText::_('JCATEGORIES'); ?>
                </th>
                <th width="5%">
                <?php echo JHtml::_('searchtools.sort', 'COM_LRM_HEADING_FILES', 'f.files', $listDirn, $listOrder); ?>
                </th>
                <th width="10%">
                <?php echo JHtml::_('searchtools.sort', 'JDATE', 'f.created', $listDirn, $listOrder); ?>
                </th>
                <th width="1%">
                <?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'f.id', $listDirn, $listOrder); ?>
                </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($this->items as $i => $item) :?>
                    <tr class="row<?php echo $i % 2; ?>">
                        <td class="center hidden-phone">
                            <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                        </td>
                        <td>
                        <?php echo $this->escape($item->title); ?>
                        </td>
                        <td class="nowrap small hidden-phone">
                            <?php echo $this->escape($item->cat_title); ?>
                        </td>
                        <td class="center">
                        <?php echo $this->escape($item->files); ?>
                        </td>
                        <td class="nowrap small hidden-phone">
                        <?php echo $this->escape($item->author); ?>
                        </td>
                        <td class="nowrap small hidden-phone">
                        <?php echo JHTML::_('date',$item->created, JText::_('DATE_FORMAT_LC4')); ?>
                        </td>
                        <td class="center">
                        <?php echo $this->escape($item->id); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

  <?php echo $this->pagination->getListFooter(); ?>

  <input type="hidden" name="boxchecked" value="0" />
  <input type="hidden" name="option" value="com_logbook" />
  <input type="hidden" name="task" value="" />
  <?php echo JHtml::_('form.token'); ?>
</form>