<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfmilestones
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
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
$repo_enabled  = PFApplicationHelper::enabled('com_pfrepo');
$cmnts_enabled = PFApplicationHelper::enabled('com_pfcomments');

$doc = JFactory::getDocument();
$style = '.large {'
        . 'font-size: 20px;'
        . 'line-height: 24px;'
        . '}'
        . '.medium {'
        . 'font-size: 16px;'
        . 'line-height: 22px;'
        . '}'
        . '.margin-none {'
        . 'margin: 0;'
        . '}';
$doc->addStyleDeclaration( $style );

$print_url = PFmilestonesHelperRoute::getMilestonesRoute($this->state->get('filter.project'))
           . '&tmpl=component&layout=print';
$print_opt = 'width=1024,height=600,resizable=yes,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no';

$itemid     = PFApplicationHelper::getActiveMenuItemId();
$list_url   = PFmilestonesHelperRoute::getMilestonesRoute($this->params->get('filter_category'), $itemid);
$return_url = base64_encode($list_url);
?>
<div id="projectfork" class="category-list<?php echo $this->pageclass_sfx;?> view-milestones PrintArea all">

    <?php if ($this->params->get('show_page_heading', 1)) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>

    <div class="clearfix"></div>

    <div class="cat-items">
        <form name="adminForm" id="adminForm" action="<?php echo JRoute::_($list_url); ?>" method="post">
            <div class="btn-toolbar btn-toolbar-top">
                <?php echo $this->toolbar;?>
                <div class="filter-project btn-group">
                    <?php echo JHtml::_('pfhtml.project.filter');?>
                </div>
				<a class="btn button" id="print_btn" href="javascript:void(0);" onclick="window.open('<?php echo JRoute::_($print_url);?>', 'print', '<?php echo $print_opt; ?>')">
                    <?php echo JText::_('COM_PROJECTFORK_PRINT'); ?>
                </a>
            </div>

            <?php if (!$this->params->get('show_filter', '1')) : ?>
                <div style="display: none !important">
            <?php else : ?>
                <div class="<?php echo $filter_in;?>collapse" id="filters">
            <?php endif; ?>
                <div class="btn-toolbar clearfix">
                    <div class="filter-search btn-group pull-left">
                        <input type="text" name="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER_SEARCH'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />
                    </div>
                    <div class="filter-search-buttons btn-group pull-left">
                        <button type="submit" class="btn" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><span aria-hidden="true" class="icon-search"></span></button>
                        <button type="button" class="btn" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.id('filter_search').value='';this.form.submit();"><span aria-hidden="true" class="icon-remove"></span></button>
                    </div>
                    <?php if ($pid) : ?>
                        <div class="filter-author btn-group pull-left">
                            <select id="filter_author" name="filter_author" class="inputbox input-small" onchange="this.form.submit()">
                                <option value=""><?php echo JText::_('JOPTION_SELECT_AUTHOR');?></option>
                                <?php echo JHtml::_('select.options', $this->authors, 'value', 'text', $this->state->get('filter.author'), true);?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <?php if ($this->access->get('core.edit.state') || $this->access->get('core.edit')) : ?>
                        <div class="filter-published btn-group pull-left">
                            <select name="filter_published" class="inputbox input-small" onchange="this.form.submit()">
                                <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
                                <?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
                            </select>
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
                    <?php if ($pid) : ?>
                        <div class="clearfix clr"></div>
                        <hr />
                        <div class="filter-labels">
                            <?php echo JHtml::_('pfhtml.label.filter', 'com_pfmilestones.milestone', $pid, $this->state->get('filter.labels'));?>
                        </div>
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
                $can_change   = ($access->get('core.edit.state') && $can_checkin);

                // Calculate milestone progress
                $task_count = (int) $item->tasks;
                $completed  = (int) $item->completed_tasks;
                $progress   = $item->progress;

                // Repo directory
                $repo_dir = (int) $this->params->get('repo_dir');

                if ($item->progress >= 67)  $progress_class = 'info';
                if ($item->progress == 100) $progress_class = 'success';
                if ($item->progress < 67)   $progress_class = 'warning';
                if ($item->progress < 34)   $progress_class = 'danger label-important';

                // Prepare the watch button
                $watch = '';

                if ($uid) {
                    $options = array('div-class' => '', 'a-class' => 'btn-mini');
                    $watch = JHtml::_('pfhtml.button.watch', 'milestones', $i, $item->watching, $options);
                }
            ?>
                <?php if ($item->project_title != $current_project && $pid <= 0) : ?>
                    <h3><?php echo $this->escape($item->project_title);?></h3>
                    <hr />
                <?php $current_project = $item->project_title; endif; ?>

                <div class="row-fluid">
                	<div class="span1 hidden-phone">
                		<div class="thumbnail center">
                			<div class="label <?php if ($progress == 100) : echo "label-success hasTooltip"; endif;?>" rel="tooltip" <?php if ($progress == 100) : echo "title=\"" . JText::_('COM_PROJECTFORK_FIELD_COMPLETE_LABEL') . "\""; endif;?>>
                				<div class="large"><?php echo JHtml::_('date', $item->end_date, JText::_('d')); ?></div>
                				<div class="medium"><?php echo JHtml::_('date', $item->end_date, JText::_('M')); ?></div>
                			</div>
                		</div>
                        <hr />
                        <?php if ($tasks_enabled) : ?>
                            <div class="progress progress-<?php echo $progress_class;?> progress-striped progress-milestone">
                                <div class="bar" style="min-width:20px; width: <?php echo ($progress > 0) ? $progress . "%": "20px";?>">
                                    <span class="">
                                        <?php echo $progress;?>%
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>
                	</div>
                	<div class="span11">
                		<div class="well well-small margin-none">
                			<span class="pull-right"><?php echo JHtml::_('pfhtml.label.datetime', $item->end_date); ?></span>
                			<?php if ($can_change || $uid) : ?>
                			    <label for="cb<?php echo $i; ?>" class="checkbox pull-left">
                			        <?php echo JHtml::_('pf.html.id', $i, $item->id); ?>
                			    </label>
                			<?php endif; ?>
                			<h4>
                				<?php if ($item->checked_out) : ?>
                				    <span aria-hidden="true" class="icon-lock"></span>
                				<?php endif; ?>
                				<a href="<?php echo JRoute::_(PFmilestonesHelperRoute::getMilestoneRoute($item->slug, $item->project_slug));?>">
                				    <?php echo $this->escape($item->title);?>
                				</a>
                				<?php if ($item->label_count) : echo JHtml::_('pfhtml.label.labels', $item->labels); endif; ?>
                			</h4>
                			<div class="well-description">
                				<?php echo JHtml::_('pf.html.truncate', $item->description, 180); ?>
                			</div>
                			<hr />
                			<div class="btn-toolbar margin-none">
                			<?php if ($can_edit || $can_edit_own) : ?>
    	    	    			<div class="btn-group">
    	    	    			    <a class="btn btn-mini" href="<?php echo JRoute::_('index.php?option=com_pfmilestones&task=form.edit&id=' . $item->slug . '&return=' . $return_url);?>">
    	    	    			        <span aria-hidden="true" class="icon-pencil"></span><?php echo JText::_('COM_PROJECTFORK_ACTION_EDIT'); ?>
    	    	    			    </a>
    	    	    			</div>
	    	    			<?php endif; ?>
	    	    			<?php if ($cmnts_enabled) : ?>
	    	    				<div class="btn-group">
	    	    					<a class="btn btn-mini" href="<?php echo JRoute::_(PFmilestonesHelperRoute::getMilestoneRoute($item->slug, $item->project_slug));?>#comments">
	    	    			       	 <span aria-hidden="true" class="icon-comment"></span> <?php echo $item->comments; ?> <?php echo JText::_('COM_PROJECTFORK_COMMENTS'); ?>
	    	    			        </a>
	    	    			    </div>
    	    				<?php endif; ?>
                			<?php if ($tasks_enabled) : ?>
	    	    				<div class="btn-group">
	    	    			        <a href="<?php echo JRoute::_(PFtasksHelperRoute::getTasksRoute($item->project_slug, $item->slug));?>" class="btn btn-mini">
	    	    			            <span aria-hidden="true" class="icon-list-view"></span>
	    	    			            <?php echo (int) $item->tasklists;?> <?php echo JText::_('JGRID_HEADING_TASKLISTS'); ?>
	    	    			        </a>
	    	    				</div>
	    	    			<?php endif; ?>
	    	    			<?php if ($tasks_enabled) : ?>
    	    	    			<div class="btn-group">
                                    <a href="<?php echo JRoute::_(PFtasksHelperRoute::getTasksRoute($item->project_slug, $item->slug));?>" class="btn btn-mini">
                                        <span aria-hidden="true" class="icon-checkmark"></span>
                                        <?php echo (int) $item->tasks;?> <?php echo JText::_('JGRID_HEADING_TASKS'); ?>
                                    </a>
    	    	    			</div>
	    	    			<?php endif; ?>
	    	    			<?php if ($repo_enabled) : ?>
	    	    				<div class="btn-group">
	    	    			        <a href="<?php echo JRoute::_(PFrepoHelperRoute::getRepositoryRoute($item->project_slug, $repo_dir));?>" class="btn btn-mini">
	    	    			            <span aria-hidden="true" class="icon-flag-2"></span>
	    	    			            <?php echo (int) $item->attachments;?> <?php echo JText::_('COM_PROJECTFORK_FIELDSET_ATTACHMENTS'); ?>
	    	    			        </a>
	    	    				</div>
	    	    			<?php endif; ?>

                			<?php echo $watch; ?>
                		</div>
                		</div>

                	</div>
                </div>
                <hr />
            <?php
            $k = 1 - $k;
            endforeach;
            ?>

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

            <input type="hidden" id="boxchecked" name="boxchecked" value="0"/>
            <input type="hidden" name="task" value="" />
            <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
</div>
