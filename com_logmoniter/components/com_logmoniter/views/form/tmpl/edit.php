<?php
/**
 * @copyright Copyright (c)2017 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */

// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tabstate');
JHtml::_('behavior.calendar');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');

// Create shortcut to parameters.
$params = $this->state->get('params');
$uri = JUri::getInstance();
?>

<script type="text/javascript">
  Joomla.submitbutton = function(task)
  {
    if(task == 'watchdog.cancel' || watchdog.formvalidator.isValid(watchdog.id('watchdog-form'))) {
      Joomla.submitform(task, watchdog.getElementById('watchdog-form'));
    }
    else {
      alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
    }
  }
</script>
<div class="edit<?php echo $this->pageclass_sfx; ?>">
  <?php if ($params->get('show_page_heading', 1)) : ?>
    <div class="edit-watchdog">
      <div class="page-header">
        <h1>
          <?php JText::_('COM_LOGMONITER_PAGE_HEADING'); ?>
        </h1>
    </div>
  <?php endif; ?>

  <form action="<?php echo JRoute::_('index.php?option=com_logmoniter&view=form&w_id='.(int) $this->item->id); ?>"
  method="post" name="adminForm" id="watchdog-form" class="form-validate form-vertical">
    <div class="btn-toolbar">
      <div class="btn-group">
        <button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('watchdog.save')">
          <span class="icon-ok"></span>&#160;<?php echo JText::_('JSAVE'); ?>
        </button>
      </div>
      <div class="btn-group">
        <button type="button" class="btn" onclick="Joomla.submitbutton('watchdog.cancel')">
          <span class="icon-cancel"></span>&#160;<?php echo JText::_('JCANCEL'); ?>
      </button>
     </div>
     </div>
    <hr class="hr-condensed">
    <?php echo $this->form->renderField('title'); ?>
    <?php echo $this->form->renderField('alias'); ?>
    <?php echo $this->form->renderField('catid'); ?>
    <span class="form-space"></span>
    <?php echo $this->form->renderField('wcid'); ?>
    <?php echo $this->form->renderField('isid'); ?>
    <?php echo $this->form->renderField('bpid'); ?>
    <?php echo $this->form->renderField('tiid'); ?>
    <?php echo $this->form->renderField('lwid'); ?>
    <?php if ($this->user->authorise('core.edit.state', 'com_logmoniter.watchdog')) : ?>
        <?php echo $this->form->renderField('published'); ?>
    <?php endif; ?>
    <?php echo $this->form->renderField('publish_up'); ?>
    <?php echo $this->form->renderField('publish_down'); ?>
    <span class="form-space"></span>
    <?php echo $this->form->renderField('language'); ?>
    <?php echo $this->form->renderField('tags'); ?>

    <input type="hidden" name="task" value="" />
    <input type="hidden" name="return" value="<?php echo $this->return_page; ?>" />
    <?php echo JHtml::_('form.token'); ?>
</form>
</div>

