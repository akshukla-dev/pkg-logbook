<?php
/**
 * @package LMI Record Manager 1.x
 * @copyright Copyright (c)2017 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */

defined('_JEXEC') or die;

// Create a shortcut for params.
$params = $displayData->params;
JHtml::_('behavior.framework');

require_once JPATH_ADMINISTRATOR.'/components/com_logbook/helpers/logbook.php';
$conversion = LogbookHelper::byteConverter($displayData->file_size); ?>

?>

  <table class="table table-condensed">
      <tr>
        <td class="detail-label"><?php echo JText::_('COM_LOGBOOK_DETAILS_FILE_NAME'); ?></td>
        <td><?php echo $this->escape($displayData->file_name); ?></td>
      </tr>
      <tr>
        <td class="detail-label"><?php echo JText::_('COM_LOGBOOK_DETAILS_FILE_SIZE'); ?></td>
        <td><?php echo JText::sprintf('COM_LOGBOOK_BYTE_CONVERTER_'.$conversion['multiple'], $conversion['result']); ?></td>
      </tr>
      <tr>
        <td class="detail-label"><?php echo JText::_('COM_LOGBOOK_DETAILS_FILE_TYPE'); ?></td>
        <td><?php echo $this->escape($displayData->file_type); ?></td>
      </tr>
      <tr>
        <td class="detail-label"><?php echo JText::_('COM_LOGBOOK_DETAILS_CREATE_DATE'); ?></td>
        <td><?php echo JHTML::_('date',$displayData->created, JText::_('DATE_FORMAT_LC3')); ?></td>
      </tr>
      <tr>
        <td class="detail-label"><?php echo JText::_('COM_LOGBOOK_DETAILS_MODIFY_DATE'); ?></td>
        <td><?php echo JHTML::_('date',$displayData->modified, JText::_('DATE_FORMAT_LC3')); ?></td>
      </tr>
      <tr>
        <td class="detail-label"><?php echo JText::_('COM_LOGBOOK_DETAILS_PUBLISH_DATE'); ?></td>
        <td><?php echo JHTML::_('date',$displayData->closed, JText::_('DATE_FORMAT_LC3')); ?></td>
      </tr>
      <tr>
        <td class="detail-label"><?php echo JText::_('COM_LOGBOOK_DETAILS_HITS'); ?></td>
        <td><?php echo $displayData->hits; ?></td>
      </tr>
      <tr>
        <td class="detail-label"><?php echo JText::_('COM_LOGBOOK_DETAILS_DOWNLOADS'); ?></td>
        <td><?php echo $displayData->downloads; ?></td>
      </tr>
      <tr>
        <td class="detail-label"><?php echo JText::_('COM_LOGBOOK_DETAILS_PUT_ONLINE_BY'); ?></td>
        <td><?php echo $this->escape($displayData->put_online_by); ?></td>
      </tr>
  </table>

