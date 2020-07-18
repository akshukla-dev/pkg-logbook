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
?>

<script type="text/javascript">
Joomla.submitbutton = function(task)
{
  if(task == 'log.cancel' || log.formvalidator.isValid(log.id('log-form'))) {
    Joomla.submitform(task, log.getElementById('log-form'));
  }
  else {
    alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
  }
}
Logbook.closebutton = function(task){
	if(task == 'log.close' & log.formvalidator.isValid(log.id('log-form'))){
		// close log
		Joomla.submitform(task, log.getElementById('log-form'));
	}
}
</script>

<div class="edit-log">
  <div class="page-header">
    <h1>
      <?php JText::_('COM_LOGBOOK_PAGE_HEADING'); ?>
    </h1>
</div>

<form action="<?php echo JRoute::_('index.php?option=com_logbook&l_id='.(int) $this->item->id); ?>"
method="post" name="adminForm" id="log-form" enctype="multipart/form-data" class="form-validate form-vertical">

  <div class="btn-toolbar">
    <div class="btn-group">
      <button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('log.save')">
        <span class="icon-ok"></span>&#160;<?php echo JText::_('JSAVE'); ?>
      </button>
    </div>
    <div class="btn-group">
      <button type="button" class="btn" onclick="Joomla.submitbutton('log.cancel')">
        <span class="icon-cancel"></span>&#160;<?php echo JText::_('JCANCEL'); ?>
      </button>
	</div>
	<div class="btn-group">
      <button type="button" class="btn" onclick="Logbook.closebutton('log.close')">
        <span class="icon-close"></span>&#160;<?php echo JText::_('JCLOSE'); ?>
      </button>
	</div>
  </div>

  <fieldset>
    <div class="container-fluid" id="details">
        <?php echo $this->form->renderField('title'); ?>
        <?php echo $this->form->renderField('alias'); ?>

        <?php if ($this->form->getValue('id') != 0) : //Existing item.?>
          <div class="info">
            <div class="row">
              <div class="row control-label">
                  <?php echo JText::_('COM_LOGBOOK_FIELD_DOWNLOAD_LABEL'); ?>
              </div>
              <div class="controls">
                  <a href="<?php echo $uri->root().'components/com_logbook/download/script.php?id='.$this->item->id; ?>" class="btn btn-success" target="_blank">
                    <span class="icon-download"></span>&#160;<?php echo JText::_('COM_LOGBOOK_BUTTON_DOWNLOAD'); ?>
                  </a>
			  </div>
			  <div class="col-3">
			  	<?php echo $this->form->getControlGroup('file_name'); ?>
			  </div>
			  <div>
			  	<?php echo $this->form->getControlGroup('file_type'); ?>
			  </div>
			  <div>
			  	<?php echo $this->form->getControlGroup('file_size'); ?>
			  </div>
			  <div>
			    <?php //Toggle button which hide/show the file upload fields to replace the original file.?>
			    <a href="#" id="switch_replace" style="margin-bottom:10px;" class="btn">
				<span id="replace-title"><?php echo JText::_('COM_LOGBOOK_REPLACE'); ?></span>
				<span id="cancel-title"><?php echo JText::_('JCANCEL'); ?></span></a>
			  </div>
            </div>
		  </div>
		<?php endif; ?>

		<?php
            echo $this->form->getControlGroup('wcid');
            echo $this->form->getControlGroup('wcid');
            echo $this->form->getControlGroup('isid');
            echo $this->form->getControlGroup('bpid');
            echo $this->form->getControlGroup('wdid');
        ?>
			<span class="form-space"></span>

		<?php
            echo $this->form->getControlGroup('uploaded_file');
            echo $this->form->getControlGroup('signatories');
            echo $this->form->getControlGroup('remarks');
        ?>
    </div>

			<?php if ($this->form->getValue('id') != 0) {
            //Hidden input flag to check if a file replacement is required.
            echo $this->form->getInput('replace_file');
        } ?>

			<?php echo $this->form->getInput('id'); ?>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="return" value="<?php echo $this->return_page; ?>" />
			<input type="hidden" name="wdog" value="<?php echo $this->wdid; ?>" />
			<?php echo JHtml::_('form.token'); ?>
  </fieldset>
</form>

<?php

$doc = JFactory::getDocument();
//Load the jQuery script(s).
$doc->addScript(JPATH_COMPONENT.'/js/log.js');
