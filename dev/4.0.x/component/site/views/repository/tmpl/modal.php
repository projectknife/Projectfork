<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

$function   = JRequest::getCmd('function', 'pfSelectAttachment');
$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$user       = JFactory::getUser();
$uid        = $user->get('id');
$dir        = $this->items['directory'];

$link_append  = '&layout=modal&tmpl=component&function=' . $function;
?>
<div id="projectfork" class="category-list<?php echo $this->pageclass_sfx;?> view-repository">

    <?php if ($this->params->get('show_page_heading', 1)) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>

    <div class="clearfix"></div>

    <div class="cat-items">
        <form name="adminForm" id="adminForm" action="<?php echo JRoute::_(ProjectforkHelperRoute::getRepositoryRoute($dir->project_id, $dir->id) . $link_append); ?>" method="post">

            <fieldset class="filters">
                <div class="well btn-toolbar">
                    <div class="filter-search btn-group pull-left">
                        <input type="text" name="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />
                    </div>
                    <div class="filter-search-buttons btn-group pull-left">
                        <button type="submit" class="btn" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
                        <button type="button" class="btn" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
                    </div>
                    <div class="clearfix"> </div>
                </div>
            </fieldset>

            <div class="clearfix"> </div>

            <hr />

            <table class="adminlist table table-striped">
                <thead>
                    <tr>
                        <th width="1%">

                        </th>
                        <th width="40%">
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
                        <td colspan="4">

                        </td>
                    </tr>
                </tfoot>
            </table>

            <input type="hidden" name="boxchecked" value="0" />
            <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
            <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
            <input type="hidden" name="task" value="" />
            <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
</div>
