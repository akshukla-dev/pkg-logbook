<?php
/**
 * @copyright Copyright (c)2020 Amit Kumar Shukla. All Rights Reserved.
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */
defined('_JEXEC') or die;

$app = JFactory::getApplication();

if ($app->isClient('site')) {
    JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));
}

JLoader::register('LogmoniterHelperRoute', JPATH_ROOT.'/components/com_logmoniter/helpers/route.php');

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

JHtml::_('behavior.core');
JHtml::_('behavior.polyfill', array('event'), 'lt IE 9');
JHtml::_('script', 'com_content/admin-articles-modal.min.js', array('version' => 'auto', 'relative' => true));
JHtml::_('bootstrap.tooltip', '.hasTooltip', array('placement' => 'bottom'));
JHtml::_('formbehavior.chosen', 'select');

// Special case for the search field tooltip.
$searchFilterDesc = $this->filterForm->getFieldAttribute('search', 'description', null, 'filter');
JHtml::_('bootstrap.tooltip', '#filter_search', array('title' => JText::_($searchFilterDesc), 'placement' => 'bottom'));

$function = $app->input->getCmd('function', 'jSelectWatchdog');
$editor = $app->input->getCmd('editor', '');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$onclick = $this->escape($function);

?>
<div class="container-popup">

    <form action="<?php echo JRoute::_('index.php?option=com_logmoniter&view=watchdogs&layout=modal&tmpl=component&function='.$function.'&'.JSession::getFormToken().'=1&editor='.$editor); ?>" method="post" name="adminForm" id="adminForm" class="form-inline">

        <?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

        <div class="clearfix"></div>

        <?php if (empty($this->items)) : ?>
            <div class="alert alert-no-items">
                <?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
            </div>
        <?php else : ?>
            <table class="table table-striped table-condensed">
                <thead>
                    <tr>
                        <th width="1%" class="center nowrap">
                            <?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'wd.state', $listDirn, $listOrder); ?>
                        </th>
                        <th class="title">
                            <?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'wd.title', $listDirn, $listOrder); ?>
                        </th>
                        <th width="10%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'wd.access', $listDirn, $listOrder); ?>
                        </th>
                        <th width="15%" class="nowrap">
                            <?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
                        </th>
                        <th width="5%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'JDATE', 'wd.created', $listDirn, $listOrder); ?>
                        </th>
                        <th width="1%" class="nowrap hidden-phone">
                        <?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'wd.id', $listDirn, $listOrder); ?>
                        </th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <td colspan="6">
                            <?php echo $this->pagination->getListFooter(); ?>
                        </td>
                    </tr>
                </tfoot>
                <tbody>
                <?php
                $iconStates = array(
                    -2 => 'icon-trash',
                    0 => 'icon-unpublish',
                    1 => 'icon-publish',
                    2 => 'icon-archive',
                );
                ?>
                <?php foreach ($this->items as $i => $item) : ?>
                    <?php if ($item->language && JLanguageMultilang::isEnabled()) {
                    $tag = strlen($item->language);
                    if ($tag == 5) {
                        $lang = substr($item->language, 0, 2);
                    } elseif ($tag == 6) {
                        $lang = substr($item->language, 0, 3);
                    } else {
                        $lang = '';
                    }
                } elseif (!JLanguageMultilang::isEnabled()) {
                    $lang = '';
                }
                    ?>
                    <tr class="row<?php echo $i % 2; ?>">
                        <td class="center">
                            <span class="<?php echo $iconStates[$this->escape($item->state)]; ?>" aria-hidden="true"></span>
                        </td>
                        <td>
                            <?php $params = 'data-function="'.$this->escape($onclick).'"'
                                .' data-id="'.$item->id.'"'
                                .' data-title="'.$this->escape(addslashes($item->title)).'"'
                                .' data-cat-id="'.$this->escape($item->catid).'"'
                                .' data-uri="'.$this->escape(LogmoniterHelperRoute::getWatchdogRoute($item->id, $item->catid, $item->language)).'"'
                                .' data-language="'.$this->escape($lang).'"';
                            ?>
                            <a class="select-link" href="javascript:void(0)" <?php echo $params; ?>>
                                <?php echo $this->escape($item->title); ?>
                            </a>
                            <div class="small">
                                <?php echo JText::_('JCATEGORY').': '.$this->escape($item->category_title); ?>
                            </div>
                        </td>
                        <td class="small hidden-phone">
                            <?php echo $this->escape($item->access_level); ?>
                        </td>
                        <td class="small">
                            <?php echo JLayoutHelper::render('joomla.content.language', $item); ?>
                        </td>
                        <td class="nowrap small hidden-phone">
                            <?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC')); ?>
                        </td>
                        <td class="nowrap small hidden-phone">
                            <?php echo (int) $item->id; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <input type="hidden" name="forcedLanguage" value="<?php echo $app->input->get('forcedLanguage', '', 'CMD'); ?>" />
        <?php echo JHtml::_('form.token'); ?>

    </form>
</div>
