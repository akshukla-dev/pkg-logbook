<?php
/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', '#jform_catid', null, array('disable_search_threshold' => 0));
JHtml::_('formbehavior.chosen', 'select');

$this->configFieldsets = array('editorConfig');
$this->hiddenFieldsets = array('basic-limited');
$this->ignore_fieldsets = array('jmetadata', 'item_associations');

// Create shortcut to parameters.
$params = clone $this->state->get('params');
$params->merge(new Registry($this->item->attribs));

$app = JFactory::getApplication();
$input = $app->input;

$assoc = JLanguageAssociations::isEnabled();

JFactory::getDocument()->addScriptDeclaration('
    Joomla.submitbutton = function(task)
    {
        if (task == "watchdog.cancel" || document.formvalidator.isValid(document.getElementById("item-form")))
        {
            jQuery("#permissions-sliders select").attr("disabled", "disabled");
            '.$this->form->getField('watchdogtext')->save().'
            Joomla.submitform(task, document.getElementById("item-form"));
        }
    };
');

// In case of modal
$isModal = $input->get('layout') == 'modal' ? true : false;
$layout = $isModal ? 'modal' : 'edit';
$tmpl = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';
?>

<form action="<?php echo JRoute::_('index.php?option=com_logmoniter&layout='.$layout.$tmpl.'&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

    <?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

    <div class="form-horizontal">
        <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('COM_LOGMONITER_WATCHDOG_CONTENT')); ?>
        <div class="row-fluid">
            <div class="span9">
                <div class="form-vertical">
                    <?php echo $this->form->renderField('wcenter_id'); ?>
                    <?php echo $this->form->renderField('inset_id'); ?>
                    <?php echo $this->form->renderField('bprint_id'); ?>
                    <?php echo $this->form->renderField('tinterval_id'); ?>
                    <?php echo $this->form->renderField('lwindow_id'); ?>
                </div>
            </div>
            <div class="span3">
                <?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>
            </div>
        </div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>

        <?php $this->show_options = $params->get('show_watchdog_options', 1); ?>
        <?php echo JLayoutHelper::render('joomla.edit.params', $this); ?>

        <?php if ($params->get('show_publishing_options', 1) == 1) : ?>
            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('COM_LOGMONITER_FIELDSET_PUBLISHING')); ?>
            <div class="row-fluid form-horizontal-desktop">
                <div class="span6">
                    <?php echo JLayoutHelper::render('joomla.edit.publishingdata', $this); ?>
                </div>
                <div class="span6">
                    <?php echo JLayoutHelper::render('joomla.edit.metadata', $this); ?>
                </div>
            </div>
            <?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php endif; ?>


        <?php if (!$isModal && $assoc) : ?>
            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'associations', JText::_('JGLOBAL_FIELDSET_ASSOCIATIONS')); ?>
            <?php echo $this->loadTemplate('associations'); ?>
            <?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php elseif ($isModal && $assoc) : ?>
            <div class="hidden"><?php echo $this->loadTemplate('associations'); ?></div>
        <?php endif; ?>

        <?php if ($this->canDo->get('core.admin')) : ?>
            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'editor', JText::_('COM_LOGMONITER_SLIDER_EDITOR_CONFIG')); ?>
            <?php echo $this->form->renderFieldset('editorConfig'); ?>
            <?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php endif; ?>

        <?php if ($this->canDo->get('core.admin')) : ?>
            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'permissions', JText::_('COM_LOGMONITER_FIELDSET_RULES')); ?>
                <?php echo $this->form->getInput('rules'); ?>
            <?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php endif; ?>

        <?php echo JHtml::_('bootstrap.endTabSet'); ?>
    </div>

        <input type="hidden" name="task" value="" />
        <input type="hidden" name="forcedLanguage" value="<?php echo $input->get('forcedLanguage', '', 'cmd'); ?>" />
        <?php echo JHtml::_('form.token'); ?>
</form>
