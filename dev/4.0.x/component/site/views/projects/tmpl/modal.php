<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


$function   = JRequest::getCmd('function', 'pfSelectActiveProject');
$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$user       = JFactory::getUser();
$uid        = $user->get('id');
?>
<div id="projectfork" class="category-list<?php echo $this->pageclass_sfx;?> view-projects">

    <div class="cat-items">

        <form name="adminForm" id="adminForm" action="<?php echo JRoute::_(ProjectforkHelperRoute::getProjectsRoute() . '&layout=modal&tmpl=component&function=' . $function);?>" method="post">

            <fieldset class="filters">
                <span class="filter-search">
                    <input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />
                    <button type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
                    <button type="button" class="btn" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
                </span>
                <?php if ($this->access->get('project.edit.state') || $this->access->get('project.edit')) : ?>
                    <span class="filter-published">
                        <select id="filter_published" name="filter_published" class="inputbox" onchange="this.form.submit()">
                            <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
                            <?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
                        </select>
                    </span>
                <?php endif; ?>
                <span class="filter-category">
                    <select name="filter_category" class="inputbox" onchange="this.form.submit()">
                        <option value=""><?php echo JText::_('JOPTION_SELECT_CATEGORY');?></option>
                        <?php echo JHtml::_('select.options', JHtml::_('category.options', 'com_projectfork'), 'value', 'text', $this->state->get('filter.category'));?>
                    </select>
                </span>
                <span class="filter-limit">
                    <?php echo $this->pagination->getLimitBox(); ?>
                </span>
            </fieldset>

            <table class="category table table-striped">
                <thead>
                    <tr>
                        <th id="tableOrdering0" class="list-title">
                            <?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'category_title, a.title', $list_dir, $list_order); ?>
                        </th>
                        <th id="tableOrdering1" class="list-category">
                            <?php echo JHtml::_('grid.sort', 'JCATEGORY', 'category_title', $list_dir, $list_order); ?>
                        </th>
                        <th id="tableOrdering2" class="list-milestones">
                            <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_MILESTONES', 'milestones', $list_dir, $list_order); ?>
                        </th>
                        <th id="tableOrdering3" class="list-tasks">
                            <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_TASKLISTS_AND_TASKS', 'tasks', $list_dir, $list_order); ?>
                        </th>
                    </tr>
               </thead>
               <tbody>
                    <?php
                    $k = 0;
                    foreach($this->items AS $i => $item) :
                    ?>
                        <tr class="cat-list-row<?php echo $k;?>">
                            <td class="list-title">
                                <a class="pointer" style="cursor: pointer;" onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>('<?php echo $item->id; ?>', '<?php echo $this->escape(addslashes($item->title)); ?>');">
                                    <?php echo $this->escape($item->title);?>
                                </a>
                            </td>
                            <td class="list-categories">
                                <?php echo $this->escape($item->category_title);?>
                            </td>
                            <td class="list-milestones">
                                <i class="icon-map-marker"></i> <?php echo (int) $item->milestones;?>
                            </td>
                            <td class="list-tasks">
                                <i class="icon-ok"></i> <?php echo intval($item->tasklists) . ' / ' . intval($item->tasks);?>
                            </td>
                        </tr>
                    <?php
                    $k = 1 - $k;
                    endforeach;
                    ?>
                </tbody>
            </table>

            <?php if ($this->pagination->get('pages.total') > 1) : ?>
                <div class="pagination">
                    <p class="counter"><?php echo $this->pagination->getPagesCounter(); ?></p>
                    <?php echo $this->pagination->getPagesLinks(); ?>
                </div>
            <?php endif; ?>

            <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
            <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
            <input type="hidden" name="task" value="" />
            <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
</div>
