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
  if(task == 'log.close' & log.formvalidator.isValid(log.id('adminForm'))){
    // close log
    Joomla.submitform(task, log.getElementById('log-form'));
  }
}
</script>

<div class="edit item-page<?php echo $this->pageclass_sfx; ?>">
  <?php //if ($params->get('show_page_heading')) :?>
  <div class="page-header">
    <h1>
      <?php //echo $this->escape($params->get('page_heading'));?>
    </h1>
  </div>
  <?php //endif;?>

  <form action="<?php echo JRoute::_('index.php?option=com_logbook&l_id='.(int) $this->item->id); ?>"
  method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" class="form-validate form-vertical">

    <div class="btn-toolbar">
      <div class="btn-group">
        <button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('log.save')">
          <span class="icon-ok"></span><?php echo JText::_('JSAVE'); ?>
        </button>
      </div>
      <div class="btn-group">
        <button type="button" class="btn" onclick="Joomla.submitbutton('log.cancel')">
          <span class="icon-cancel"></span><?php echo JText::_('JCANCEL'); ?>
        </button>
      </div>
      <?php // if ($params->get('save_history', 0) && $this->item->id) :?>
      <div class="btn-group">
        <?php echo $this->form->getInput('contenthistory'); ?>
      </div>
      <?php //endif;?>
      <div class="btn-group">
        <button type="button" class="btn" onclick="Logbook.closebutton('log.close')">
          <span class="icon-close"></span>&#160;<?php echo JText::_('JCLOSE'); ?>
        </button>
      </div>
    </div>
    <fieldset>
      <?php echo JHtml::_('bootstrap.startTabSet', $this->tab_name, array('active' => 'editor')); ?>
        <?php echo JHtml::_('bootstrap.addTab', $this->tab_name, 'editor', JText::_('COM_LOGBOOK_LOG_CONTENT')); ?>
          <!-- -->
          <?php echo $this->form->renderField('title'); ?>
          <?php if (is_null($this->item->id)) : ?>
            <?php echo $this->form->renderField('alias'); ?>
          <?php endif; ?>
          <?php if ($this->form->getValue('id') != 0) : //Existing item.?>
            <div class="container-fluid">
              <div class="col-xs-1 col-sm-1 col-md-1 col-lg-1">
                <span class="label">
                  <?php echo JText::_('COM_LOGBOOK_FIELD_DOWNLOAD_LABEL'); ?>
                </span>
              </div>
              <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                <?php echo $this->form->renderField('file_name'); ?>
              </div>
              <div class="col-xs-1 col-sm-1 col-md-1 col-lg-1">
                <a class="btn btn-large btn-block btn-success" href="<?php echo $uri->root().'components/com_logbook/download/script.php?id='.$this->item->id; ?>"
                    role="button" target="_blank"><span class="icon-download"></span>&#160;<?php echo JText::_('COM_LOGBOOK_BUTTON_DOWNLOAD'); ?>
                </a>
              </div>
              <div class="col-xs-1 col-sm-1 col-md-1 col-lg-1">
                <a href="#" id="switch_replace" style="margin-bottom:10px;"
                    class="btn-info">
                  <span id="replace-title">
                    <?php echo JText::_('COM_LOGBOOK_REPLACE'); ?>
                  </span>
                  <span id="cancel-title">
                    <?php echo JText::_('JCANCEL'); ?>
                  </span>
                </a>
              </div>
            </div>
          <?php endif; ?>
          <?php echo $this->form->renderField('wdid'); ?>
          <?php echo $this->form->renderField('uploaded_file'); ?>
          <?php echo $this->form->renderField('signatories'); ?>
          <?php echo $this->form->renderField('remarks'); ?>
        <?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php echo JHtml::_('bootstrap.addTab', $this->tab_name, 'OtherInfo', JText::_('COM_LOGBOOK_LOG_CONTENT')); ?>
          <!-- -->
          <?php echo $this->form->renderField('catid'); ?>
          <?php echo $this->form->renderField('tags'); ?>
          <?php if ($this->item->params->get('access-change')) : ?>
            <?php echo $this->form->renderField('state'); ?>
          <?php endif; ?>
          <?php echo $this->form->renderField('access'); ?>
          <?php if (is_null($this->item->id)) : ?>
            <div class="control-group">
              <div class="control-label">
              </div>
              <div class="controls">
                <?php echo JText::_('COM_LOGBOOK_ORDERING'); ?>
              </div>
            </div>
          <?php endif; ?>
          <?php echo $this->form->renderField('language'); ?>
        <?php echo JHtml::_('bootstrap.endTab'); ?>
      <?php echo JHtml::_('bootstrap.endTabSet'); ?>

      <!--Hidden input flag to check if a file replacement is required.-->
        <?php if ($this->form->getValue('id') != 0) {
    echo $this->form->getInput('replace_file');
} ?>

        <?php echo $this->form->getInput('id'); ?>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="return" value="<?php echo $this->return_page; ?>" />
        <?php echo JHtml::_('form.token'); ?>
    </fieldset>
  </form>
</div>

<?php
$doc = JFactory::getDocument();
//Load the jQuery script(s).
$doc->addScript(JPATH_COMPONENT.'/js/log.js');
