<?php
/**
 * @copyright Copyright (c)2020 Amit Kumar Shukla. All Rights Reserved.
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */
defined('_JEXEC') or die;

// Create a shortcut for params.
// Create a shortcuts.
$item = $this->item;
$params = $this->item->params;
JHtml::_('behavior.framework');

$nullDate = JFactory::getDbo()->getNullDate();

require_once JPATH_ADMINISTRATOR.'/components/com_logbook/helpers/logbook.php';
$conversion = LogbookHelper::byteConverter((int) $item->file_size); ?>

  <table class="table table-condensed">
      <tr>
        <th class="detail-label"><?php echo JText::_('COM_LOGBOOK_DETAILS_FILE_NAME'); ?></th>
        <td>
          <?php echo $this->escape($item->file_name); ?>&#160;
          <a href="<?php echo JUri::root().'components/com_logbook/download/script.php?id='.$item->id; ?>" class="btn btn-success btn-narrow" target="_blank">
            <span class="icon-download"></span>&#160;<?php echo JText::_('COM_LOGBOOK_BUTTON_DOWNLOAD'); ?>
          </a>
        </td>
      </tr>
      <tr>
        <th class="detail-label"><?php echo JText::_('COM_LOGBOOK_DETAILS_FILE_SIZE'); ?></th>
        <td>
          <?php echo JText::_($conversion['result'].' '); ?>
          <?php echo JText::_('COM_LOGBOOK_BYTE_CONVERTER_'.$conversion['multiple']); ?>
        </td>
      </tr>
      <tr>
        <th class="detail-label"><?php echo JText::_('COM_LOGBOOK_DETAILS_FILE_TYPE'); ?></th>
        <td>
          <?php echo $this->escape($item->file_type); ?>&#160;
          <img src="media/com_logbook/extensions/<?php echo $item->file_icon; ?>" class="file-icon" alt="<?php echo $item->file_icon; ?>" width="16" height="16"/>
        </td>
      </tr>
      <tr>
        <th class="detail-label"><?php echo JText::_('COM_LOGBOOK_DETAILS_CREATE_DATE'); ?></th>
        <td><?php echo JHTML::_('date', $item->created, JText::_('DATE_FORMAT_LC')); ?></td>
      </tr>
      <tr>
        <th class="detail-label"><?php echo JText::_('COM_LOGBOOK_DETAILS_MODIFY_DATE'); ?></th>
        <td>
          <?php if ($item->modified == $nullDate) : ?>
            <?php echo '---'; ?>
          <?php else : ?>
            <?php echo JHTML::_('date', $item->modified, JText::_('DATE_FORMAT_LC')); ?>
          <?php endif; ?>
        </td>
      </tr>
      <tr>
        <th class="detail-label"><?php echo JText::_('COM_LOGBOOK_DETAILS_PUBLISH_DATE'); ?></th>
        <td>
          <?php if ($item->publish_up == $nullDate) : ?>
            <?php echo JHTML::_('date', $item->created, JText::_('DATE_FORMAT_LC')); ?>
          <?php else : ?>
            <?php echo JHTML::_('date', $item->publish_up, JText::_('DATE_FORMAT_LC')); ?>
          <?php endif; ?>
      </tr>
      <tr>
        <th class="detail-label"><?php echo JText::_('COM_LOGBOOK_DETAILS_HITS'); ?></th>
        <td><?php echo $item->hits; ?></td>
      </tr>
      <tr>
        <th class="detail-label"><?php echo JText::_('COM_LOGBOOK_DETAILS_DOWNLOADS'); ?></th>
        <td><?php echo $item->downloads; ?></td>
      </tr>
      <tr>
        <th class="detail-label"><?php echo JText::_('COM_LOGBOOK_DETAILS_PUT_ONLINE_BY'); ?></th>
        <td><?php echo $this->escape($item->author); ?></td>
      </tr>
  </table>

