<?php
/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.caption');
?>
<div class="archive<?php echo $this->pageclass_sfx; ?>">
<?php if ($this->params->get('show_page_heading')) : ?>
<div class="page-header">
<h1>
    <?php echo $this->escape($this->params->get('page_heading')); ?>
</h1>
</div>
<?php endif; ?>
<form id="adminForm" action="<?php echo JRoute::_('index.php'); ?>" method="post" class="form-inline">
    <fieldset class="filters">
    <div class="filter-search">
        <?php if ($this->params->get('filter_field') !== 'hide') : ?>
        <label class="filter-search-lbl element-invisible" for="filter-search"><?php echo JText::_('COM_LOGMONITER_TITLE_FILTER_LABEL').'&#160;'; ?></label>
        <input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->filter); ?>" class="inputbox span2" onchange="document.getElementById('adminForm').submit();" placeholder="<?php echo JText::_('COM_LOGMONITER_TITLE_FILTER_LABEL'); ?>" />
        <?php endif; ?>
        <div class="container">
            <div class="row">
                <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                    <?php //echo $this->form->renderField('logworkcenters');?>
                </div>
                <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                    <?php //echo $this->form->renderField('loginstructionsets');?>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                    <?php //echo $this->form->renderField('logblueprints');?>
                </div>
                <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                    <?php //echo $this->form->renderField('logtimeintervals');?>
                </div>
            </div>
            <div class="row">
                <?php echo $this->form->monthField; ?>
                <?php echo $this->form->yearField; ?>
                <?php echo $this->form->limitField; ?>
            </div>
            <button type="submit" class="btn btn-primary" style="horizontal-align: right;"><?php echo JText::_('JGLOBAL_FILTER_BUTTON'); ?></button>
        </div>
        <input type="hidden" name="view" value="moniter" />
        <input type="hidden" name="option" value="com_logmoniter" />
        <input type="hidden" name="limitstart" value="0" />
    </div>
    <br />
    </fieldset>

    <?php echo $this->loadTemplate('items'); ?>
</form>
</div>
