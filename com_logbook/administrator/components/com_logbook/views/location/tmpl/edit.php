<?php
/**
 * @copyright   Copyright (C) 2020 AMit Kumar Shukla, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/*JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', 'select', null, array('disable_search_threshold' => 0));

$app = JFactory::getApplication();
$input = $app->input;

$assoc = JLanguageAssociations::isEnabled();

// Fieldsets to not automatically render by /layouts/joomla/edit/params.php
$this->ignore_fieldsets = array('details', 'item_associations', 'jmetadata');

JFactory::getDocument()->addScriptDeclaration("
    Joomla.submitbutton = function(task)
    {
        if (task == 'location.cancel' || document.formvalidator.isValid(document.getElementById('location-form'))) {
            ".$this->form->getField('description')->save()."
            Joomla.submitform(task, document.getElementById('location-form'));
        }
    };
");

// In case of modal
$isModal = $input->get('layout') == 'modal' ? true : false;
$layout = $isModal ? 'modal' : 'edit';
$tmpl = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';

*/?>

<form action="<?php echo JRoute::_('index.php?option=com_logbook&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="locationForm" id="locationForm">

    <div class="form-horizontal">
		<fieldset class="locationform">
			<legend><?php echo JText::_('COM_LOGBOOK_LOCATION_DETAILS'); ?></legend>
			<div class="row-fluid">
				<div class="span6">
					<?php
                        foreach ($this->form->getFieldset() as $field) {
                            echo $field->renderField();
                        }
                    ?>

				</div>
			</div>
		</fieldset>

		<!--
        <?php // echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('JGLOBAL_FIELDSET_PUBLISHING', true));?>
        <div class="row-fluid form-horizontal-desktop">
            <div class="span6">
                <?php // echo JLayoutHelper::render('joomla.edit.publishingdata', $this);?>
            </div>
            <div class="span6">
                <?php  //echo JLayoutHelper::render('joomla.edit.metadata', $this);?>
            </div>
        </div>
        <?php //echo JHtml::_('bootstrap.endTab');?>

        <?php //echo JLayoutHelper::render('joomla.edit.params', $this);?>

        <?php // if (!$isModal && $assoc) :?>
            <?php // echo JHtml::_('bootstrap.addTab', 'myTab', 'associations', JText::_('JGLOBAL_FIELDSET_ASSOCIATIONS'));?>
            <?php // echo $this->loadTemplate('associations');?>
            <?php  // echo JHtml::_('bootstrap.endTab');?>
        <?php //elseif ($isModal && $assoc) :?>
            <div class="hidden"><?php // echo $this->loadTemplate('associations');?></div>
        <?php // endif;?>

        <?php //echo JHtml::_('bootstrap.endTabSet');?>

    </div>
*/-->

    <input type="hidden" name="task" value="location.edit" />
	<!--<input type="hidden" name="forcedLanguage" value="<?php //echo $input->get('forcedLanguage', '', 'cmd');?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
