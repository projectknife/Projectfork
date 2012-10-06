<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$user       = JFactory::getUser();
$uid        = $user->get('id');
$pid        = (int) $this->state->get('filter.project');

$action_count = count($this->actions);
$filter_in    = ($this->state->get('filter.isset') ? 'in ' : '');
?>
<div id="projectfork" class="category-list<?php echo $this->pageclass_sfx;?> view-milestones">

    <?php if ($this->params->get('show_page_heading', 1)) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>

    <div class="clearfix"></div>

    <div class="cat-items">
        <form name="adminForm" id="adminForm" action="<?php echo JRoute::_(ProjectforkHelperRoute::getMilestonesRoute()); ?>" method="post">
            <div class="btn-toolbar btn-toolbar-top">
                <?php echo $this->toolbar;?>
                <div class="filter-project btn-group">
                    <?php echo JHtml::_('projectfork.filterProject');?>
                </div>
            </div>

            <div class="clearfix"> </div>

            <div class="<?php echo $filter_in;?>collapse" id="filters">
                <div class="well btn-toolbar">
                    <div class="filter-search btn-group pull-left">
                        <input type="text" name="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />
                    </div>
                    <div class="filter-search-buttons btn-group pull-left">
                        <button type="submit" class="btn" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
                        <button type="button" class="btn" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
                    </div>
                    <?php if ($pid) : ?>
                        <div class="filter-author btn-group">
                            <select id="filter_author" name="filter_author" class="inputbox input-medium" onchange="this.form.submit()">
                                <option value=""><?php echo JText::_('JOPTION_SELECT_AUTHOR');?></option>
                                <?php echo JHtml::_('select.options', $this->authors, 'value', 'text', $this->state->get('filter.author'), true);?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <?php if ($this->access->get('milestone.edit.state') || $this->access->get('milestone.edit')) : ?>
                        <div class="filter-published btn-group">
                            <select name="filter_published" class="inputbox input-medium" onchange="this.form.submit()">
                                <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
                                <?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <div class="clearfix"> </div>
                </div>
            </div>

            <?php
            $k = 0;
            $current_project = '';
            foreach($this->items AS $i => $item) :
                $access = ProjectforkHelperAccess::getActions('milestone', $item->id);

                $can_create   = $access->get('milestone.create');
                $can_edit     = $access->get('milestone.edit');
                $can_change   = $access->get('milestone.edit.state');
                $can_edit_own = ($access->get('milestone.edit.own') && $item->created_by == $uid);

                // Calculate milestone progress
                $task_count = (int) $item->tasks;
                $completed  = (int) $item->completed_tasks;
                $progress   = ($task_count == 0) ? 0 : round($completed * (100 / $task_count));

                if ($progress >= 67)  $progress_class = 'info';
                if ($progress == 100) $progress_class = 'success';
                if ($progress < 67)   $progress_class = 'warning';
                if ($progress < 34)   $progress_class = 'danger label-important';
            ?>
                <?php if ($item->project_title != $current_project && $pid <= 0) : ?>
                    <h3><?php echo $this->escape($item->project_title);?></h3>
                    <hr />
                <?php $current_project = $item->project_title; endif; ?>
                <div class="well well-<?php echo $k;?>">
                    <?php
                        $this->menu->start(array('class' => 'btn-mini', 'pull' => 'right'));
                        $this->menu->itemEdit('milestoneform', $item->id, ($can_edit || $can_edit_own));
                        $this->menu->itemTrash('milestones', $i, $can_change);
                        $this->menu->end();

                        echo $this->menu->render(array('class' => 'btn-mini'));
                    ?>
                    <div style="display: none !important;">
                        <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                    </div>
                    <?php if ($item->end_date != $this->nulldate) : ?>
                        <span class="label label-info pull-right"><i class="icon-calendar icon-white"></i>
                            <?php echo JHtml::_('date', $item->end_date, $this->escape( $this->params->get('date_format', JText::_('DATE_FORMAT_LC1'))));?>
                        </span>
                    <?php endif; ?>
                    <h4 class="milestone-title">
                        <?php if ($item->checked_out) : ?><i class="icon-lock"></i> <?php endif; ?>
                        <a href="<?php echo JRoute::_(ProjectforkHelperRoute::getMilestoneRoute($item->slug, $item->project_slug));?>">
                            <?php echo $this->escape($item->title);?>
                        </a>
                        <a href="#milestone-<?php echo $item->id;?>" class="btn btn-mini" data-toggle="collapse">
                            <?php echo JText::_('COM_PROJECTFORK_DETAILS_LABEL');?> <span class="caret"></span>
                        </a>
                    </h4>
                    <div class="collapse" id="milestone-<?php echo $item->id;?>">
                        <hr />
                        <div class="small">
                            <span class="label access pull-right">
                                <i class="icon-user icon-white"></i> <?php echo $this->escape($item->author_name);?>
                            </span>
                            <span class="label access pull-right">
                                <i class="icon-lock icon-white"></i> <?php echo $this->escape($item->access_level);?>
                            </span>

                            <p><?php echo $this->escape($item->description);?></p>

                            <span class="list-created">
                                <?php echo JHtml::_('date', $item->created, $this->escape( $this->params->get('date_format', JText::_('DATE_FORMAT_LC1')))); ?>
                            </span>
                            <span class="list-sdate">
                                <?php if ($item->start_date == $this->nulldate) {
                                    echo JText::_('COM_PROJECTFORK_DATE_NOT_SET');
                                }
                                else {
                                    echo JHtml::_('date', $item->start_date, $this->escape( $this->params->get('date_format', JText::_('DATE_FORMAT_LC1'))));
                                }
                                ?>
                            </span>
                        </div>
                        <div class="btn-toolbar">
                            <div class="btn-group">
                                <a class="btn" href="<?php echo JRoute::_(ProjectforkHelperRoute::getTasksRoute($item->project_slug, $item->slug));?>">
                                    <i class="icon-list"></i> <?php echo intval($item->tasklists).' '. JText::_('COM_PROJECTFORK_TASK_LISTS');?>
                                </a>
                                <a class="btn" href="<?php echo JRoute::_(ProjectforkHelperRoute::getTasksRoute($item->project_slug, $item->slug));?>">
                                    <i class="icon-ok"></i> <?php echo intval($item->tasks).' '. JText::_('COM_PROJECTFORK_TASKS');?>
                                </a>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <hr />
                    <div class="progress progress-<?php echo $progress_class;?> progress-striped progress-milestone">
                        <div class="bar" style="width: <?php echo ($progress > 0) ? $progress."%": "24px";?>">
                            <span class="label label-<?php echo $progress_class;?> pull-right"><?php echo $progress;?>%</span>
                        </div>
                    </div>
                </div>
            <?php
            $k = 1 - $k;
            endforeach;
            ?>

            <div class="filters btn-toolbar">
                <?php if ($this->pagination->get('pages.total') > 1) : ?>
                    <div class="pagination">
                        <p class="counter"><?php echo $this->pagination->getPagesCounter(); ?></p>
                        <?php echo $this->pagination->getPagesLinks(); ?>
                    </div>
                <?php endif; ?>

                <div class="btn-group display-limit">
                    <?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
                    <?php echo $this->pagination->getLimitBox(); ?>
                </div>
            </div>

            <input type="hidden" name="boxchecked" value="0" />
            <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
            <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
            <input type="hidden" name="task" value="" />
            <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
</div>
