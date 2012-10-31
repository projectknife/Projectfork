<?php
/**
 * @package      Projectfork
 * @subpackage   Milestones
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


JHtml::_('pfhtml.script.listform');

$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$user       = JFactory::getUser();
$uid        = $user->get('id');
$pid        = (int) $this->state->get('filter.project');

$filter_in     = ($this->state->get('filter.isset') ? 'in ' : '');
$tasks_enabled = PFApplicationHelper::enabled('com_pftasks');
?>
<div id="projectfork" class="category-list<?php echo $this->pageclass_sfx;?> view-milestones">

    <?php if ($this->params->get('show_page_heading', 1)) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>

    <div class="clearfix"></div>

    <div class="cat-items">
        <form name="adminForm" id="adminForm" action="<?php echo JRoute::_(PFmilestonesHelperRoute::getMilestonesRoute()); ?>" method="post">
            <div class="btn-toolbar btn-toolbar-top">
                <?php echo $this->toolbar;?>
                <div class="filter-project btn-group">
                    <?php echo JHtml::_('pfhtml.project.filter');?>
                </div>
            </div>

            <div class="clearfix"> </div>

            <div class="<?php echo $filter_in;?>collapse" id="filters">
                <div class="well btn-toolbar">
                    <div class="filter-search btn-group pull-left">
                        <input type="text" name="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER_SEARCH'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />
                    </div>
                    <div class="filter-search-buttons btn-group pull-left">
                        <button type="submit" class="btn" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
                        <button type="button" class="btn" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
                    </div>

                    <div class="clearfix"> </div>

                    <hr />

                    <?php if ($pid) : ?>
                        <div class="filter-author btn-group">
                            <select id="filter_author" name="filter_author" class="inputbox input-medium" onchange="this.form.submit()">
                                <option value=""><?php echo JText::_('JOPTION_SELECT_AUTHOR');?></option>
                                <?php echo JHtml::_('select.options', $this->authors, 'value', 'text', $this->state->get('filter.author'), true);?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <?php if ($this->access->get('core.edit.state') || $this->access->get('core.edit')) : ?>
                        <div class="filter-published btn-group">
                            <select name="filter_published" class="inputbox input-medium" onchange="this.form.submit()">
                                <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
                                <?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <div class="clearfix"> </div>

                    <?php if ($pid) : ?>
                        <hr />
                        <div class="filter-labels">
                            <?php echo JHtml::_('pfhtml.label.filter', 'com_pfmilestones.milestone', $pid, $this->state->get('filter.labels'));?>
                        </div>
                        <div class="clearfix"> </div>
                    <?php endif; ?>

                </div>
            </div>

            <?php
            $k = 0;
            $current_project = '';
            foreach($this->items AS $i => $item) :
                $access = PFmilestonesHelper::getActions($item->id);

                $can_create   = $access->get('core.create');
                $can_edit     = $access->get('core.edit');
                $can_edit_own = ($access->get('core.edit.own') && $item->created_by == $uid);
                $can_checkin  = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
                $can_change   = ($access->get('core.edit.state') || $can_checkin);

                // Calculate milestone progress
                $task_count = (int) $item->tasks;
                $completed  = (int) $item->completed_tasks;
                $progress   = ($task_count == 0) ? 0 : round($completed * (100 / $task_count));

                if ($progress >= 67)  $progress_class = 'info';
                if ($progress == 100) $progress_class = 'success';
                if ($progress < 67)   $progress_class = 'warning';
                if ($progress < 34)   $progress_class = 'danger label-important';

                // Prepare the watch button
                $options = array('div-class' => 'pull-right', 'a-class' => 'btn-mini');
                $watch = JHtml::_('pfhtml.button.watch', 'milestones', $i, $item->watching, $options);
            ?>
                <?php if ($item->project_title != $current_project && $pid <= 0) : ?>
                    <h3><?php echo $this->escape($item->project_title);?></h3>
                    <hr />
                <?php $current_project = $item->project_title; endif; ?>
                <div class="well well-small well-<?php echo $k;?>">
                	<div class="btn-toolbar">
                    	<?php if ($can_change) : ?>
                            <label for="cb<?php echo $i; ?>" class="checkbox pull-left">
                                <?php echo JHtml::_('pf.html.id', $i, $item->id); ?>
                            </label>
                        <?php endif; ?>
                        <?php echo $watch; ?>
	                   	 <h3>
	                   	 	<span class="toolbar-inline pull-left">
	                   	 		<div class="btn-group pull-left">
				                   	<?php
			                        $this->menu->start(array('class' => 'btn-mini'));
			                        $this->menu->itemEdit('form', $item->id, ($can_edit || $can_edit_own));
			                        $this->menu->itemTrash('milestones', $i, $can_change);
			                        $this->menu->end();

			                        echo $this->menu->render(array('class' => 'btn-mini'));
				                    ?>
			                   </div>
	                   	 	</span>
	                        <?php if ($item->checked_out) : ?>
	                            <i class="icon-lock"></i>
	                        <?php endif; ?>
	                        <a href="<?php echo JRoute::_(PFmilestonesHelperRoute::getMilestoneRoute($item->slug, $item->project_slug));?>">
	                            <?php echo $this->escape($item->title);?>
	                        </a>
	                    </h3>
	                   <div class="clearfix"></div>
                	</div>
                    <div>
                        <p>
                            <?php echo $this->escape($item->description);?>
                        </p>
                    </div>
                    <?php if ($tasks_enabled) : ?>
                        <div class="progress progress-<?php echo $progress_class;?> progress-striped progress-milestone">
                            <div class="bar" style="width: <?php echo ($progress > 0) ? $progress . "%": "24px";?>">
                                <span class="label label-<?php echo $progress_class;?> pull-right">
                                    <?php echo $progress;?>%
                                </span>
                            </div>
                        </div>
                    <?php endif; ?>
                    <hr />
                    <?php echo JHtml::_('pfhtml.label.author', $item->author_name, $item->created, $this->params->get('date_format')); ?>
                    <span class="label"><i class="icon-lock icon-white"></i> <?php echo $this->escape($item->access_level); ?></span>
                    <?php echo JHtml::_('pfhtml.label.datetime', $item->end_date, $this->params->get('date_format')); ?>
                    <?php
                    if ($item->label_count > 0 && isset($item->labels))
                    {
                        foreach ($item->labels AS $label)
                        {
                            $style = ($label->style ? ' ' . $label->style : '');
                            ?>
                            <span class="label<?php echo $style; ?>"><i class="icon-bookmark"></i> <?php echo $this->escape($label->title); ?></span>
                            <?php
                        }
                    }
                    ?>
                    <?php if ($tasks_enabled) : ?>
                        <div class="btn-group pull-right">
                            <a class="btn btn-mini" href="<?php echo JRoute::_(PFtasksHelperRoute::getTasksRoute($item->project_slug, $item->slug));?>">
                                <i class="icon-list"></i> <?php echo JText::sprintf('JGRID_HEADING_TASKLISTS_AND_TASKS', intval($item->tasklists), intval($item->tasks)); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    <div class="clearfix"></div>
                </div>
            <?php
            $k = 1 - $k;
            endforeach;
            ?>

            <div class="filters btn-toolbar">
                <div class="btn-group filter-order">
                    <select name="filter_order" class="inputbox input-medium" onchange="this.form.submit()">
                        <?php echo JHtml::_('select.options', $this->sort_options, 'value', 'text', $list_order, true);?>
                    </select>
                </div>
                <div class="btn-group folder-order-dir">
                    <select name="filter_order_Dir" class="inputbox input-medium" onchange="this.form.submit()">
                        <?php echo JHtml::_('select.options', $this->order_options, 'value', 'text', $list_dir, true);?>
                    </select>
                </div>
                <div class="btn-group display-limit">
                    <?php echo $this->pagination->getLimitBox(); ?>
                </div>
                <?php if ($this->pagination->get('pages.total') > 1) : ?>
                    <div class="btn-group pagination">
                        <p class="counter"><?php echo $this->pagination->getPagesCounter(); ?></p>
                        <?php echo $this->pagination->getPagesLinks(); ?>
                    </div>
                <?php endif; ?>
            </div>

            <input type="hidden" id="boxchecked" name="boxchecked" value="0"/>
            <input type="hidden" name="task" value="" />
            <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
</div>
