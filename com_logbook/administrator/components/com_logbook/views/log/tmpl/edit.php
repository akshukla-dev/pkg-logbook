<?php
/**
 * @package LMI/LogManager/LogBook/LogMoniter
 * @copyright Copyright (c)2020 Amit Kumar Shukla. All Rights Reserved.
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

JHtml::_('behavior.formvalidation');
JHtml::_('behavior.tabstate');
JHtml::_('formbehavior.chosen', 'select');

// Create Shortcuts
$input = JFactory::getApplication()->input;

// Fieldsets to not automatically render by /layouts/joomla/edit/params.php
$this->ignore_fieldsets = array('details', 'item_associations', 'jmetadata', 'permissions');

JFactory::getDocument()->addScriptDeclaration("
    Joomla.submitbutton = function(task)
    {
        if (task == 'log.cancel' || document.formvalidator.isValid(document.getElementById('log-form'))) {
            Joomla.submitform(task, document.getElementById('log-form'));
        }
    };
");

// In case of modal
$isModal = $input->get('layout') == 'modal' ? true : false;
$layout = $isModal ? 'modal' : 'edit';
$tmpl = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';
?>

<form action="<?php echo JRoute::_('index.php?option=com_logbook&layout='.$layout.$tmpl.'&id='.(int) $this->item->id); ?>"
	method="post" name="adminForm" id="log-form" enctype="multipart/form-data" class="form-validate">

    <?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

    <div class="form-horizontal">
        <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', empty($this->item->id) ? JText::_('COM_LOGBOOK_NEW_LOG', true) : JText::_('COM_LOGBOOK_EDIT_LOG', true)); ?>
        <div class="row-fluid">
            <div class="span9">
                <div class="form-vertical">
					<?php echo $this->form->renderField('wdid'); ?>
                    <?php if ($this->form->getValue('id') != 0) : //Existing item.?>
                        <div class="control-group">
                            <div class="control-label">
                                <?php echo JText::_('COM_LOGBOOK_FIELD_DOWNLOAD_LABEL'); ?>
                            </div>
                            <div class="controls">
                                <a class="btn btn-success" href="<?php JUri::root().'components/com_logbook/download/script.php?id='.$this->item->id; ?>"
                                    target="_blank"><span class="icon-download"></span>&#160;<?php echo JText::_('COM_LOGBOOK_BUTTON_DOWNLOAD'); ?>
                                </a>
                            </div>
                        </div>
                        <?php echo $this->form->getControlGroup('file_name'); ?>
                        <?php //Toggle button which hide/show the link method fields to replace the original file.?>
                        <a href="#" id="switch_replace" style="margin-bottom:10px;" class="btn">
                            <span id="replace-title">
                                <?php echo JText::_('COM_LOGBOOK_REPLACE'); ?>
                            </span>
                            <span id="cancel-title">
                                <?php echo JText::_('JCANCEL'); ?>
                            </span>
                        </a>
                    <?php endif; ?>
					<?php echo $this->form->getControlGroup('uploaded_file'); ?>
                    <?php echo $this->form->getControlGroup('signatories'); ?>
                    <?php echo $this->form->getControlGroup('remarks'); ?>
					<?php echo $this->form->getControlGroup('downloads'); ?>
                </div>
            </div>
            <div class="span3">
                <?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>
            </div>
        </div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>

        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('JGLOBAL_FIELDSET_PUBLISHING', true)); ?>
        <div class="row-fluid form-horizontal-desktop">
            <div class="span6">
                <?php echo JLayoutHelper::render('joomla.edit.publishingdata', $this); ?>
            </div>
            <div class="span6">
                <?php echo JLayoutHelper::render('joomla.edit.metadata', $this); ?>
            </div>
        </div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>

        <?php echo JLayoutHelper::render('joomla.edit.params', $this); ?>

        <?php if (!$isModal && $assoc) : ?>
            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'associations', JText::_('JGLOBAL_FIELDSET_ASSOCIATIONS')); ?>
            <?php echo $this->loadTemplate('associations'); ?>
            <?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php elseif ($isModal && $assoc) : ?>
            <div class="hidden"><?php echo $this->loadTemplate('associations'); ?></div>
        <?php endif; ?>
        <?php echo JHtml::_('bootstrap.endTabSet'); ?>
    </div>

    <?php
        if ($this->form->getValue('id') != 0) {
            //Hidden input flag to check if a file replacement is required.
        echo $this->form->getInput('replace_file');
        }
    ?>
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="forcedLanguage" value="<?php echo $input->get('forcedLanguage', '', 'cmd'); ?>" />
    <?php echo JHtml::_('form.token'); ?>
</form>

<?php
$doc = JFactory::getDocument();
//Load the jQuery script(s).
$doc->addScript(JUri::root().'/media/com_logbook/js/log.js');
