<?php
/**
 * @package LMI/LogManager/LogBook/LogMoniter
 * @copyright Copyright (c)2020 Amit Kumar Shukla. All Rights Reserved.
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */
defined('_JEXEC') or die;
$published = $this->state->get('filter.published');
?>

<div class="container-fluid">
    <div class="row-fluid">
        <div class="control-group span6">
            <div class="controls">
                <?php echo JHtml::_('batch.language'); ?>
            </div>
        </div>
        <div class="control-group span6">
            <div class="controls">
                <?php echo JHtml::_('batch.access'); ?>
            </div>
        </div>
    </div>
    <div class="row-fluid">
        <?php if ($published >= 0) : ?>
            <div class="control-group span6">
                <div class="controls">
                    <?php echo JHtml::_('batch.item', 'com_logmoniter'); ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="control-group span6">
            <div class="controls">
                <?php echo JHtml::_('batch.tag'); ?>
            </div>
        </div>
    </div>
</div>
