<?php
/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

$fieldSets = $this->form->getFieldsets('attribs'); ?>
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
