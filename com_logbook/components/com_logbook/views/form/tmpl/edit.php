<?php
/**
 * @package LMI Record Manager 1.x
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
  if(task == 'log.cancel' || log.formvalidator.isValid(log.id('log-form'))) {
    Joomla.submitform(task, log.getElementById('log-form'));
  }
  else {
    alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
  }
}
</script>

<div class="edit-log <?php echo $this->pageclass_sfx; ?>">
	<?php if ($params->get('show_page_heading', 1)) : ?>
		<div class="page-header">
			<h1>
				<?php echo $this->escape($params->get('page_heading')); ?>
			</h1>
		</div>
	<?php endif; ?>

	<form action="<?php echo JRoute::_('index.php?option=com_logbook&l_id='.(int)$this->item->id); ?>"
	method="post" name="adminForm" id="log-form" enctype="multipart/form-data" class="form-validate form-vertical">

		<div class="btn-toolbar">
			<div class="btn-group">
				<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('log.save')">
					<span class="icon-ok"></span>&#160;<?php echo JText::_('JSAVE') ?>
				</button>
			</div>
			<div class="btn-group">
				<button type="button" class="btn" onclick="Joomla.submitbutton('log.cancel')">
					<span class="icon-cancel"></span>&#160;<?php echo JText::_('JCANCEL') ?>
				</button>
			</div>
			<?php if ($params->get('save_history', 0)) : ?>
				<div class="btn-group">
					<?php echo $this->form->getInput('contenthistory'); ?>
				</div>
			<?php endif; ?>
		</div>

		<fieldset>

			<ul class="nav nav-tabs">
				<li class="active"><a href="#details" data-toggle="tab"><?php echo JText::_('COM_LRM_TAB_DETAILS') ?></a></li>
				<li><a href="#publishing" data-toggle="tab"><?php echo JText::_('COM_LRM_TAB_PUBLISHING') ?></a></li>
				<li><a href="#metadata" data-toggle="tab"><?php echo JText::_('COM_LRM_TAB_METADATA') ?></a></li>
			</ul>

			<div class="tab-content">
				<div class="tab-pane active" id="details">
					<?php echo $this->form->renderField('title'); ?>
					<?php echo $this->form->renderField('alias'); ?>

					<?php if($this->form->getValue('id') != 0) : //Existing item. ?>
						<div class="control-group">
							<div class="control-label">
								<?php echo JText::_('COM_LRM_FIELD_DOWNLOAD_LABEL'); ?>
							</div>
							<div class="controls">
								<a href="<?php echo $uri->root().'components/com_logbook/download/script.php?id='.$this->item->id; ?>" class="btn btn-success" target="_blank">
									<span class="icon-download"></span>&#160;<?php echo JText::_('COM_LRM_BUTTON_DOWNLOAD'); ?>
								</a>
							</div>
						</div>
						<?php echo $this->form->getControlGroup('file_name'); ?>
						<?php //Toggle button which hide/show the file upload fields to replace the original file. ?>
						<span class="form-space"></span>
						<a href="#" id="switch_replace" style="margin-bottom:10px;" class="btn">
							<span id="replace-title"><?php echo JText::_('COM_LRM_REPLACE'); ?></span>
							<span id="cancel-title"><?php echo JText::_('JCANCEL'); ?></span></a>
						<span class="form-space"></span>
					<?php endif; ?>

					<?php
						echo $this->form->getControlGroup('catid');
						echo $this->form->getControlGroup('uploaded_file');
						echo $this->form->getControlGroup('author');
						echo $this->form->getControlGroup('logtext');
					?>
				</div>

				<div class="tab-pane" id="publishing">
					<?php echo $this->form->getControlGroup('tags'); ?>
					<?php echo $this->form->getControlGroup('access'); ?>
					<?php echo $this->form->getControlGroup('published'); ?>
					<?php echo $this->form->getControlGroup('language'); ?>
					<?php echo $this->form->getControlGroup('publish_up'); ?>
					<?php echo $this->form->getControlGroup('publish_down'); ?>
				</div>


				<div class="tab-pane" id="metadata">
					<?php echo $this->form->getControlGroup('metadesc'); ?>
					<?php echo $this->form->getControlGroup('metakey'); ?>
				</div>
			</div>

			<?php if($this->form->getValue('id') != 0) {
				//Hidden input flag to check if a file replacement is required.
				echo $this->form->getInput('replace_file');
			} ?>

			<?php echo $this->form->getInput('id'); ?>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="return" value="<?php echo $this->return_page; ?>" />
			<?php if($this->params->get('enable_category', 0) == 1) :?>
				<input type="hidden" name="jform[catid]" value="<?php echo $this->params->get('catid', 1); ?>" />
			<?php endif; ?>
			<?php echo JHtml::_('form.token'); ?>
		</fieldset>
	</form>
</div>
<?php

$doc = JFactory::getDocument();
//Load the jQuery script(s).
$doc->addScript(JURI::base().'administrator/components/com_logbook/js/log.js');


