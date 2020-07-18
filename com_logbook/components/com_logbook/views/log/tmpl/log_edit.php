<?php
/**
 * @copyright Copyright (c)2017 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */
defined('_JEXEC') or die;

// Create a shortcut for params.
$params = $displayData->params;
$canEdit = $displayData->params->get('access-edit');
JHtml::_('behavior.framework');
?>

    <?php if ($canEdit) :
        //First check if the document is checked out by a different user
        if ($displayData->checked_out > 0 && $displayData->checked_out != $displayData->user_id) :
          $checkoutUser = JFactory::getUser($displayData->checked_out);
          $button = JHtml::_('image', 'com_logbook/checked-out.png', null, null, true);
          $date = JHtml::_('date', $displayData->checked_out_time);
          $tooltip = JText::_('JLIB_HTML_CHECKED_OUT').' :: '.
             JText::sprintf('COM_LOGBOOK_CHECKED_OUT_BY', $checkoutUser->name).' <br /> '.$date;
        ?>
	      <div class="checked-out-icon">
		 <span class="hasTooltip" title="<?php echo JHtml::tooltipText($tooltip.'', 0); ?>"><?php echo $button; ?></span>
	      </div>
	   <?php else :
          //Build the edit link and display the edit button.
          $url = 'index.php?option=com_logbook&task=log.edit&l_id='.$displayData->id.'&return='.base64_encode($displayData->uri);
          ?>
	      <p class="button-edit"><a class="btn btn-primary" href="<?php echo JRoute::_($url); ?>"><span class="icon-edit"></span>
		<?php echo JText::_('COM_LOGBOOK_EDIT'); ?>
	      </a></p>
	  <?php endif; ?>
   <?php endif; ?>
