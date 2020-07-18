<?php
/**
 * @package LMI Record Manager 1.x
 * @copyright Copyright (c)2017 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */

defined('_JEXEC') or die;

JHtml::_('behavior.framework');

// Create a shortcut for params.
$params = $displayData->params;
?>

<?php if($params->get('show_title') || $displayData->published == 0 || ($params->get('show_author') && !empty($displayData->author))) : ?>
  <div class="page-header">
    <?php if($params->get('show_extension_icon')) : ?>
      <img src="media/com_logbook/extensions/<?php echo $displayData->file_icon; ?>" class="file-icon"
      alt="<?php echo $displayData->file_icon; ?>" width="16" height="16" />
    <?php endif; ?>
    <h2>
      <a href="<?php echo JRoute::_(LogbookHelperRoute::getLogRoute($displayData->slug, $displayData->catid)); ?>">
        <?php echo $this->escape($displayData->title); ?></a>
    </h2>
    <?php endif; ?>

    <?php if ($displayData->state !== 99) : ?>
	    <span class="label label-warning"><?php echo JText::_('JUNPUBLISHED'); ?></span>
    <?php endif; ?>
  </div>
<?php endif; ?>
