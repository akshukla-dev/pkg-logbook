<?php
/**
 * @package Logbook
 * @copyright Copyright (c)2017 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */

// no direct access
defined('_JEXEC') or die;

// Create shortcuts to some parameters.
$params = $this->item->params;
$item = $this->item;
?>

<div class="log-page <?php echo $this->pageclass_sfx; ?>">
  <?php if ($item->params->get('show_page_heading', 1)) : ?>
    <div class="page-header">
      <h1>
          <?php echo $this->escape($params->get('page_heading')); ?>
      </h1>
    </div>
  <?php endif; ?>

  <div class="log-general span8">
    <?php echo JLayoutHelper::render('log_title', $item, ''); ?>

    <div class="introtext">
        <?php echo $item->remarks; ?>
    </div>

    <div class="signatories">
      <div class="signatories-label">
           <?php echo JText::_('COM_LOGBOOK_FIELD_SIGNATORIES_LABEL'); ?>
      </div>
      <div class="info">
           <?php echo $this->escape($item->signatories); ?>
      </div>
    </div>

    <p class="download-button">
      <a href="<?php echo $item->uri->root().'components/com_logbook/download/script.php?id='.$item->id; ?>" class="btn btn-success" target="_blank">
        <span class="icon-download"></span>&#160;<?php echo JText::_('COM_LOGBOOK_BUTTON_DOWNLOAD'); ?>
      </a>
    </p>
  </div>

  <div class="log-details span4">
    <?php echo JLayoutHelper::render('log_edit', $item, ''); ?>
    <?php echo JLayoutHelper::render('log_details', $item, ''); ?>
  </div>
</div>
