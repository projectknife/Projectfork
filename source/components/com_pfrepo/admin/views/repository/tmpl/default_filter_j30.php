<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfrepo
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


$list_order  = $this->escape($this->state->get('list.ordering'));
$list_dir    = $this->escape($this->state->get('list.direction'));
$project     = (int) $this->state->get('filter.project');
$sort_fields = $this->getSortFields();
?>
<div id="filter-bar" class="btn-toolbar">

    <div class="filter-search btn-group pull-left">
        <label for="filter_search" class="element-invisible">
            <?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>
        </label>
        <input type="text" id="filter_search" name="filter_search"
            data-toggle="tooltip" data-placement="bottom" placeholder="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"
            value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
            title="<?php echo $this->escape(JText::_('COM_PROJECTFORK_SEARCH_FILTER_TOOLTIP')); ?>"
        />
    </div>

    <div class="btn-group pull-left">
        <button class="btn tip hasTooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>">
            <i class="icon-search"></i>
        </button>
        <button class="btn tip hasTooltip" type="button" onclick="document.id('filter_search').value='';this.form.submit();" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>">
            <i class="icon-remove"></i>
        </button>
    </div>

    <div class="btn-group pull-left">
        <?php echo JHtml::_('pfhtml.project.filter');?>
    </div>

    <?php if ($this->pagination) : ?>
        <div class="btn-group pull-right hidden-phone">
            <label for="limit" class="element-invisible">
                <?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?>
            </label>
            <?php echo $this->pagination->getLimitBox(); ?>
        </div>
    <?php endif; ?>

    <div class="btn-group pull-right hidden-phone">
        <label for="directionTable" class="element-invisible">
            <?php echo JText::_('JFIELD_ORDERING_DESC'); ?>
        </label>
        <select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
            <option value="">
                <?php echo JText::_('JFIELD_ORDERING_DESC'); ?>
            </option>
            <option value="asc" <?php if ($list_dir == 'asc') echo 'selected="selected"'; ?>>
                <?php echo JText::_('JGLOBAL_ORDER_ASCENDING'); ?>
            </option>
            <option value="desc" <?php if ($list_dir == 'desc') echo 'selected="selected"'; ?>>
                <?php echo JText::_('JGLOBAL_ORDER_DESCENDING');  ?>
            </option>
        </select>
    </div>

    <div class="btn-group pull-right">
        <label for="sortTable" class="element-invisible">
            <?php echo JText::_('JGLOBAL_SORT_BY'); ?>
        </label>
        <select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
            <option value=""><?php echo JText::_('JGLOBAL_SORT_BY');?></option>
            <?php echo JHtml::_('select.options', $sort_fields, 'value', 'text', $list_order); ?>
        </select>
    </div>

    <?php if ($project) : ?>
        <div class="btn-group pull-right">
            <select name="filter_parent_id" id="filter_parent_id" class="input-medium" onchange="this.form.submit();">
                <option value=""><?php echo JText::_('JOPTION_SELECT_DIRECTORY');?></option>
                <?php echo JHtml::_('select.options', JHtml::_('pfrepo.pathOptions', $project), 'value', 'text', $this->state->get('filter.parent_id'));?>
            </select>
        </div>
    <?php endif; ?>

</div>
<div class="clr clearfix"></div>
<script type="text/javascript">
jQuery('#filter_search').tooltip();
</script>
