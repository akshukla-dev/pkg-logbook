<?php
/**
 * @copyright   Copyright (C) 2020 AMit Kumar Shukla, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

?>

<form action="<?php echo JRoute::_('index.php?option=com_logbook&view=blueprint&layout=edit&id='.(int) $this->item->id); ?>" 
    method="post" name="adminForm" id="adminForm" class="form-validate">
    <div class="form-horizontal">
		<fieldset class="bluprintform">
			<legend><?php echo JText::_('COM_LOGBOOK_BLUEPRINT_DETAILS'); ?></legend>
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

    <input type="hidden" name="task" value="bluprint.edit" />
	<?php echo JHtml::_('form.token'); ?>
</form>
