<?php
/**
 * @package      Projectfork
 * @subpackage   Repository
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
$this_dir   = $this->items['directory'];

$link_append = '&layout=modal&tmpl=component&function=' . $function;
$access      = PFrepoHelper::getActions('directory', $this_dir->id);
?>
<form action="<?php echo JRoute::_('index.php?option=com_pfrepo&view=repository' . $link_append); ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">

    <fieldset id="filter-bar">
        <div class="filter-search fltlft btn-toolbar pull-left">
            <div class="fltlft btn-group pull-left">
                <input type="text" name="filter_search" id="filter_search" class="inputbox input-medium" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />
            </div>
            <div class="fltlft btn-group pull-left">
                <button type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
                <button type="button" class="btn" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
            </div>
        </div>
        <div class="filter-select fltrt btn-toolbar pull-right">
            <div class="fltrt btn-group">
                <?php if ($project) : ?>
                    <select name="filter_parent_id" class="inputbox" onchange="this.form.submit()">
                        <option value=""><?php echo JText::_('JOPTION_SELECT_DIRECTORY');?></option>
                        <?php echo JHtml::_('select.options', JHtml::_('pfrepo.pathOptions', $project), 'value', 'text', $this->state->get('filter.parent_id'));?>
                </select>
                <?php endif; ?>
            </div>
        </div>
    </fieldset>

    <div class="clr clearfix"></div>

    <?php if ($access->get('core.create')) : ?>
        <hr />
        <fieldset id="upload-form">
            <div class="filter-select fltrt btn-toolbar pull-right">
                <div class="fltlft btn-group pull-left">
                    <input type="file" name="jform[file]" id="jform_file"/>
                </div>
                <div class="fltlft btn-group pull-left">
                    <button type="button" class="btn" onclick="Joomla.submitbutton('file.save');"><?php echo JText::_('JACTION_UPLOAD'); ?></button>
                </div>
            </div>
            <input type="hidden" name="jform[dir_id]" id="jform_dir_id" value="<?php echo $this->escape((int) $this_dir->id);?>"/>
            <input type="hidden" name="jform[project_id]" id="jform_dir_id" value="<?php echo $this->escape((int) $this->state->get('filter.project'));?>"/>
            <input type="hidden" name="jform[access]" id="jform_access" value="<?php echo $this->escape((int) $this_dir->access);?>"/>
        </fieldset>
    <?php endif; ?>

    <div class="clr clearfix"></div>

    <table class="adminlist table table-striped">
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
    <input type="hidden" name="layout" value="modal" />
    <input type="hidden" name="tmpl" value="component" />
    <input type="hidden" name="function" value="<?php echo $this->escape($function);?>" />
    <?php echo JHtml::_('form.token'); ?>
</form>
