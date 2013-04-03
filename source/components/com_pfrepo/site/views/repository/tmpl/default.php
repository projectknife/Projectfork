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


JHtml::_('pfhtml.script.listform');

$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$project    = (int) $this->state->get('filter.project');
$user       = JFactory::getUser();
$uid        = $user->get('id');
$dir        = $this->items['directory'];

$filter_in  = ($this->state->get('filter.isset') ? 'in ' : '');

$doc =& JFactory::getDocument();
$style = '.text-large {'
        . 'font-size: 20px;'
        . 'line-height: 24px;'
        . '}' 
        . '.text-medium {'
        . 'font-size: 16px;'
        . 'line-height: 22px;'
        . '}'
        . '.margin-none {'
        . 'margin: 0;'
        . '}'
        . '.icon-jpg:before,.icon-png:before,.icon-bmp:before,.icon-psd:before,.icon-tiff:before,.icon-jpeg:before {'
        . 'content: "\2f";'
        . 'color: #468847;'
        . '}'
        . '.icon-mov:before,.icon-swf:before,.icon-flv:before,.icon-mp4:before,.icon-wmv:before {'
        . 'content: "\56";'
        . 'color: #b94a48;'
        . '}'
        . '.icon-pdf:before {'
        . 'margin: 0;'
        . '}'
        . '.item-title {'
        . 'margin-right: 10px;'
        . '}'
        . '.item-count {'
        . 'margin-left: 5px;'
        . '}';
$doc->addStyleDeclaration( $style );
?>
<div id="projectfork" class="category-list<?php echo $this->pageclass_sfx;?> view-repository">

    <?php if ($this->params->get('show_page_heading', 1)) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>

    <div class="clearfix"></div>

    <div class="cat-items">
        <form name="adminForm" id="adminForm" action="<?php echo JRoute::_(PFrepoHelperRoute::getRepositoryRoute($dir->project_id, $dir->id)); ?>" method="post">
            <div class="btn-toolbar btn-toolbar-top">
                <?php echo $this->toolbar;?>
                <div class="filter-project btn-group">
                    <?php echo JHtml::_('pfhtml.project.filter');?>
                </div>
            </div>

            <div class="<?php echo $filter_in;?>collapse" id="filters">
                <div class="btn-toolbar clearfix">
                    <div class="filter-search btn-group pull-left">
                        <input type="text" name="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />
                    </div>
                    <div class="filter-search-buttons btn-group pull-left">
                        <button type="submit" class="btn" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
                        <button type="button" class="btn" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
                    </div>
                    <?php if ($project) : ?>
                        <div class="filter-labels btn-group pull-left">
                            <?php echo JHtml::_('pfhtml.label.filter', 'com_pfrepo', $this->state->get('filter.project'), $this->state->get('filter.labels'));?>
                        </div>
                    <?php endif; ?>
                    <div class="btn-group filter-order pull-left">
                        <select name="filter_order" class="inputbox input-small" onchange="this.form.submit()">
                            <?php echo JHtml::_('select.options', $this->sort_options, 'value', 'text', $list_order, true);?>
                        </select>
                    </div>
                    <div class="btn-group folder-order-dir pull-left">
                        <select name="filter_order_Dir" class="inputbox input-small" onchange="this.form.submit()">
                            <?php echo JHtml::_('select.options', $this->order_options, 'value', 'text', $list_dir, true);?>
                        </select>
                    </div>
                </div>
            </div>

            <table class="adminlist table table-striped">
                <thead>
                    <tr>
                        <?php if ($dir->parent_id >= 1) : ?>
                        <th width="1%">
                            <input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
                        </th>
                        <?php endif; ?>
                        <th width="25%">
                            <?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $list_dir, $list_order); ?>
                        </th>
                        <th width="6%">
                            <?php echo JText::_('JGRID_HEADING_TYPE'); ?>
                        </th>
                        <th width="8%">
                            <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_CREATED_BY', 'a.created_by', $list_dir, $list_order); ?>
                        </th>
                        <th width="8%">
                            <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_CREATED_ON', 'a.created', $list_dir, $list_order); ?>
                        </th>
                    </tr>
                </thead>
                <tfoot>
                	<tr>
                		<td colspan="5"></td>
                	</tr>
                </tfoot>
                <tbody>
                    <?php echo $this->loadTemplate('directories'); ?>
                    <?php echo $this->loadTemplate('notes'); ?>
                    <?php echo $this->loadTemplate('files'); ?>
                </tbody>
            </table>

            <?php if ($this->pagination) : ?>
                <?php if ($this->pagination->get('pages.total') > 1) : ?>
                    <div class="pagination center">
                        <?php echo $this->pagination->getPagesLinks(); ?>
                    </div>
                    <p class="counter center"><?php echo $this->pagination->getPagesCounter(); ?></p>
                <?php endif; ?>

                <div class="filters center">
                    <span class="display-limit">
                        <?php echo $this->pagination->getLimitBox(); ?>
                    </span>
                </div>
            <?php endif; ?>

            <input type="hidden" id="boxchecked" name="boxchecked" value="0" />
            <input type="hidden" name="task" value="" />
            <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
</div>
