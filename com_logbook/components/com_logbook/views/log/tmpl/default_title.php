<?php
/**
 * @copyright Copyright (c)2017 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */
defined('_JEXEC') or die;

JHtml::_('behavior.framework');

// Create a shortcuts.
$item = $this->item;
$params = $this->item->params;
?>

<?php // if ($params->get('show_title') || $item->published == 0 || ($params->get('show_author') && !empty($item->author))) :?>
  <div class="page-header">

		<?php // if ($params->get('show_extension_icon')) :?>
			<img src="media/com_logbook/extensions/<?php echo $item->file_icon; ?>" class="file-icon"
			alt="<?php echo $item->file_icon; ?>" width="16" height="16" />
		<?php // endif;?>
		<h2>
			<a href="<?php echo JRoute::_(LogbookHelperRoute::getLogRoute($item->slug, $item->catid)); ?>">
				<?php echo $this->escape($item->title); ?>
			</a>
		</h2>

    <?php if ($item->state === 0) : ?>
	    <span class="label label-warning"><?php echo JText::_('JUNPUBLISHED'); ?></span>
    <?php endif; ?>
  </div>
<?php // endif;?>
