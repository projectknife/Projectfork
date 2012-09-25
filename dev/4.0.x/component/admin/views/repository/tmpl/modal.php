<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


JHtml::_('behavior.tooltip');

$function   = JRequest::getCmd('function', 'pfSelectAttachment');
$user       = JFactory::getUser();
$uid        = $user->get('id');
$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$project    = (int) $this->state->get('filter.project');

$link_append  = '&layout=modal&tmpl=component&function=' . $function;
?>
<form action="<?php echo JRoute::_('index.php?option=com_projectfork&view=repository' . $link_append); ?>" method="post" name="adminForm" id="adminForm">

    <fieldset id="filter-bar">
        <div class="filter-search fltlft">
            <label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
            <input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />

            <button type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
            <button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
        </div>
        <div class="filter-select fltrt">
            <?php if ($project) : ?>
                <select name="filter_parent_id" class="inputbox" onchange="this.form.submit()">
                    <option value=""><?php echo JText::_('JOPTION_SELECT_DIRECTORY');?></option>
                    <?php echo JHtml::_('select.options', JHtml::_('projectfork.repository.pathOptions', $project), 'value', 'text', $this->state->get('filter.parent_id'));?>
            </select>
            <?php endif; ?>
        </div>
    </fieldset>
    <div class="clr"></div>

    <table class="adminlist">
        <thead>
            <tr>
                <th width="1%">

                </th>
                <th width="45%">
                    <?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $list_dir, $list_order); ?>
                </th>
                <th>
                    <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_DESCRIPTION', 'a.description', $list_dir, $list_order); ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php echo $this->loadTemplate('directories'); ?>
            <?php echo $this->loadTemplate('notes'); ?>
            <?php echo $this->loadTemplate('files'); ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">

                </td>
            </tr>
        </tfoot>
    </table>

    <input type="hidden" name="filter_project" value="<?php echo $project; ?>" />
    <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
</form>
