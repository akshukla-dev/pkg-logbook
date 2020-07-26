<?php
/**
 * @copyright Copyright (c)2017 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */

// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.tabstate');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.calendar');
JHtml::_('formbehavior.chosen', 'select');
$this->tab_name = 'com-logmoniter-form';

// Create shortcut to parameters.
$params = $this->state->get('params');
?>


<script type="text/javascript">
  Joomla.submitbutton = function(task)
  {
    if(task == 'watchdog.cancel' || document.formvalidator.isValid(document.getElementById('adminForm')))
    {
      Joomla.submitform(task);
    }
  }
</script>
<div class="edit item-page<?php echo $this->pageclass_sfx; ?>">
  <?php if ($params->get('show_page_heading', 1)) : ?>
      <div class="page-header">
        <h1>
          <?php echo $this->escape($params->get('page_heading')); ?>
        </h1>
    </div>
  <?php endif; ?>

  <form action="<?php echo JRoute::_('index.php?option=com_logmoniter&view=form&wd_id='.(int) $this->item->id); ?>"
  method="post" name="adminForm" id="adminForm" class="form-validate form-vertical">
    <fieldset>
      <?php echo JHtml::_('bootstrap.startTabSet', $this->tab_name, array('active' => 'editor')); ?>
        <?php echo JHtml::_('bootstrap.addTab', $this->tab_name, 'editor', JText::_('COM_LOGMONITER_WATCHDOG_CONTENT')); ?>
          <?php echo $this->form->renderField('title'); ?>
          <?php if (is_null($this->item->id)) : ?>
            <?php echo $this->form->renderField('alias'); ?>
          <?php endif; ?>
          <?php echo $this->form->renderField('wcid'); ?>
          <?php echo $this->form->renderField('isid'); ?>
          <?php echo $this->form->renderField('bpid'); ?>
          <?php echo $this->form->renderField('tiid'); ?>
          <?php echo $this->form->renderField('lwid'); ?>
          <?php if ($this->user->authorise('core.edit.state', 'com_logmoniter.watchdog')) : ?>
            <?php echo $this->form->renderField('state'); ?>
          <?php endif; ?>
          <?php echo $this->form->renderField('publish_up'); ?>
          <?php echo $this->form->renderField('publish_down'); ?>
        <?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php echo JHtml::_('bootstrap.addTab', $this->tab_name, 'info', JText::_('COM_LOGMONITER_WATCHDOG_INFO')); ?>
          <?php echo $this->form->renderField('catid'); ?>
          <?php echo $this->form->renderField('language'); ?>
          <?php echo $this->form->renderField('tags'); ?>
          <?php echo $this->form->renderField('access'); ?>
        <?php echo JHtml::_('bootstrap.endTab'); ?>
      <?php echo JHtml::_('bootstrap.endTabSet'); ?>

      <input type="hidden" name="task" value="" />
      <input type="hidden" name="return" value="<?php echo $this->return_page; ?>" />
      <?php echo JHtml::_('form.token'); ?>
    </fieldset>

    <div class="btn-toolbar">
      <div class="btn-group">
        <button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('watchdog.save')">
          <span class="icon-ok"></span><?php echo JText::_('JSAVE'); ?>
        </button>
      </div>
      <div class="btn-group">
        <button type="button" class="btn" onclick="Joomla.submitbutton('watchdog.cancel')">
          <span class="icon-cancel"></span><?php echo JText::_('JCANCEL'); ?>
        </button>
      </div>
    </div>
  </form>
</div>

<?php

$doc = JFactory::getDocument();
//Load the jQuery script(s).
$doc->addScript(JURI::base().'administrator/components/com_logmoniter/js/watchdog.js');
