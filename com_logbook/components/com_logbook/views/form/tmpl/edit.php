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
JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', '#jform_catid', null, array('disable_search_threshold' => 0));
JHtml::_('formbehavior.chosen', 'select');
$this->tab_name = 'com-content-form';
$this->ignore_fieldsets = array('jmetadata', 'item_associations');

//Load the jQuery script(s).
$doc = JFactory::getDocument();
$doc->addScript(JURI::root().'/media/com_logbook/js/log.js');

// Fieldsets to not automatically render by /layouts/joomla/edit/params.php
$this->ignore_fieldsets = array('details', 'item_associations', 'jmetadata');

// Create shortcut to parameters.
$params = $this->state->get('params');
?>

<script type="text/javascript">
  Joomla.submitbutton = function(task)
  {
    if(task == 'log.cancel' || document.formvalidator.isValid(document.getElementById('adminForm'))) {
      Joomla.submitform(task);
    }
    else {
      alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
    }
  }
</script>

<div class="edit item-page<?php echo $this->pageclass_sfx; ?>">
  <?php if ($params->get('show_page_heading')) :?>
  <div class="page-header">
    <h1>
      <?php echo $this->escape($params->get('page_heading')); ?>
    </h1>
  </div>
  <?php endif; ?>

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
      <?php if ($params->get('save_history', 0) && $this->item->id) :?>
      <div class="btn-group">
        <?php echo $this->form->getInput('contenthistory'); ?>
      </div>
      <?php endif; ?>
    </div>
    <fieldset>
      <?php echo JHtml::_('bootstrap.startTabSet', $this->tab_name, array('active' => 'editor')); ?>
        <?php echo JHtml::_('bootstrap.addTab', $this->tab_name, 'editor', JText::_('COM_LOGBOOK_LOG_CONTENT')); ?>
          <?php echo $this->form->renderField('title'); ?>
          <?php if (is_null($this->item->id)) : ?>
            <?php echo $this->form->renderField('alias'); ?>
          <?php endif; ?>
          <?php echo $this->form->renderField('wdid'); ?>
          <?php if ($this->form->getValue('id') != 0) : //Existing item.?>
            <div class="control-group">
							<div class="control-label">
								<?php echo JText::_('COM_LOGBOOK_FIELD_DOWNLOAD_LABEL'); ?>
							</div>
							<div class="controls">
								<a class="btn btn-success" href="<?php echo JUri::root().'components/com_logbook/download/script.php?id='.$this->item->id; ?>"
									role="button" target="_blank"><span class="icon-download"></span>&#160;<?php echo JText::_('COM_LOGBOOK_BUTTON_DOWNLOAD'); ?>
								</a>
							</div>
						</div>
            <div>
              <?php echo $this->form->renderField('file_name'); ?>
            </div>
            <div>
              <a href="#" id="switch_replace" style="margin-bottom:10px;" class="btn btn-warning">
                <span id="replace-title">
                  <?php echo JText::_('COM_LOGBOOK_REPLACE'); ?>
                </span>
                <span id="cancel-title">
                  <?php echo JText::_('JCANCEL'); ?>
                </span>
              </a>
            </div>


          <?php endif; ?>
          <?php echo $this->form->renderField('uploaded_file'); ?>
          <?php echo $this->form->renderField('signatories'); ?>
          <?php echo $this->form->renderField('remarks'); ?>
        <?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php // echo JLayoutHelper::render('joomla.edit.params', $this);?>
        <?php echo JHtml::_('bootstrap.addTab', $this->tab_name, 'OtherInfo', JText::_('COM_LOGBOOK_LOG_OTHERINFO')); ?>
          <?php echo $this->form->renderField('catid'); ?>
          <?php echo $this->form->renderField('tags'); ?>
          <?php if ($params->get('save_history', 0)) : ?>
            <?php echo $this->form->renderField('version_note'); ?>
          <?php endif; ?>
          <?php if ($params->get('show_publishing_options', 1) == 1) : ?>
            <?php echo $this->form->renderField('created_by_alias'); ?>
          <?php endif; ?>
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
        <?php if ($params->get('show_publishing_options', 1) == 1) : ?>
          <?php echo JHtml::_('bootstrap.addTab', $this->tab_name, 'metadata', JText::_('COM_LOGBOOK_METADATA')); ?>
            <?php echo $this->form->renderField('metadesc'); ?>
            <?php echo $this->form->renderField('metakey'); ?>
          <?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php endif; ?>
      <?php echo JHtml::_('bootstrap.endTabSet'); ?>
      <!--Hidden input flag to check if a file replacement is required.-->
      <?php echo $this->form->getInput('id'); ?>
      <?php
        if ($this->form->getValue('id') != 0) {
            echo $this->form->getInput('replace_file');
        }
      ?>
      <input type="hidden" name="task" value="" />
      <input type="hidden" name="return" value="<?php echo $this->return_page; ?>" />
      <?php echo JHtml::_('form.token'); ?>
    </fieldset>
  </form>
</div>
