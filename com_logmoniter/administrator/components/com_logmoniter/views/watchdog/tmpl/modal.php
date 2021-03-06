<?php
/**
 * @package LMI/LogManager/LogBook/LogMoniter
 * @copyright Copyright (c)2020 Amit Kumar Shukla. All Rights Reserved.
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */
defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip', '.hasTooltip', array('placement' => 'bottom'));

// @deprecated 4.0 the function parameter, the inline js and the buttons are not needed since 3.7.0.
$function = JFactory::getApplication()->input->getCmd('function', 'jEditWatchdog_'.(int) $this->item->id);

// Function to update input title when changed
JFactory::getDocument()->addScriptDeclaration('
    function jEditWatchdogModal() {
        if (window.parent && document.formvalidator.isValid(document.getElementById("item-form"))) {
            return window.parent.'.$this->escape($function).'(document.getElementById("jform_title").value);
        }
    }
');
?>
<button id="applyBtn" type="button" class="hidden" onclick="Joomla.submitbutton('watchdog.apply'); jEditWatchdogModal();"></button>
<button id="saveBtn" type="button" class="hidden" onclick="Joomla.submitbutton('watchdog.save'); jEditWatchdogModal();"></button>
<button id="closeBtn" type="button" class="hidden" onclick="Joomla.submitbutton('watchdog.cancel');"></button>

<div class="container-popup">
    <?php $this->setLayout('edit'); ?>
    <?php echo $this->loadTemplate(); ?>
</div>
