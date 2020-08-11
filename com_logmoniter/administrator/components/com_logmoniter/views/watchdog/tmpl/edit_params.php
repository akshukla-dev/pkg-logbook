<?php
/**
 * @package LMI/LogManager/LogBook/LogMoniter
 * @copyright Copyright (c)2020 Amit Kumar Shukla. All Rights Reserved.
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */
defined('_JEXEC') or die;

$fieldSets = $this->form->getFieldsets('params'); ?>
<?php foreach ($fieldSets as $name => $fieldSet) : ?>
    <div class="tab-pane" id="params-<?php echo $name; ?>">
    <?php foreach ($this->form->getFieldset($name) as $field) : ?>
        <div class="control-group">
            <div class="control-label"><?php echo $field->label; ?></div>
            <div class="controls"><?php echo $field->input; ?></div>
        </div>
    <?php endforeach; ?>
    </div>
<?php endforeach; ?>
