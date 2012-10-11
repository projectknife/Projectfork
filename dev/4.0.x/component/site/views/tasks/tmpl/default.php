<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


JHtml::_('projectfork.script.jquerysortable');
JHtml::_('projectfork.script.listform');
JHtml::_('projectfork.script.task');

$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$user       = JFactory::getUser();
$uid        = $user->get('id');

$action_count = count($this->actions);
$filter_in    = ($this->state->get('filter.isset') ? 'in ' : '');
?>
<script type="text/javascript">
jQuery(document).ready(function() {
    PFlist.sortable('.list-tasks', 'tasks');
});
</script>
<div id="projectfork" class="category-list<?php echo $this->pageclass_sfx;?> view-tasks">

    <?php if ($this->params->get('show_page_heading', 1)) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>

    <div class="clearfix"></div>

    <div class="cat-items">
        <form id="adminForm" name="adminForm" method="post" action="<?php echo htmlspecialchars(JFactory::getURI()->toString()); ?>">
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
                        <button type="submit" class="btn" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>">
                            <i class="icon-search"></i>
                        </button>
                        <button type="button" class="btn" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.id('filter_search').value='';this.form.submit();">
                            <i class="icon-remove"></i>
                        </button>
                    </div>

                    <div class="clearfix"> </div>
                    <hr />

                    <?php if ($this->state->get('filter.project')) : ?>
                        <div class="filter-milestone btn-group">
                            <select onchange="this.form.submit()" class="inputbox input-medium" name="filter_milestone" id="milestone">
                                <option value=""><?php echo JText::_('JOPTION_SELECT_MILESTONE');?></option>
                                <?php echo JHtml::_('select.options', $this->milestones, 'value', 'text', $this->state->get('filter.milestone'));?>
                            </select>
                        </div>
                        <div class="filter-tasklist btn-group">
                            <select id="filter_tasklist" name="filter_tasklist" class="inputbox input-medium" onchange="this.form.submit()">
                                <option value=""><?php echo JText::_('JOPTION_SELECT_TASKLIST');?></option>
                                <?php echo JHtml::_('select.options', $this->lists, 'value', 'text', $this->state->get('filter.tasklist'));?>
                            </select>
                        </div>
                        <div class="filter-author btn-group">
                            <select id="filter_author" name="filter_author" class="inputbox input-medium" onchange="this.form.submit()">
                                <option value=""><?php echo JText::_('JOPTION_SELECT_AUTHOR');?></option>
                                <?php echo JHtml::_('select.options', $this->authors, 'value', 'text', $this->state->get('filter.author'));?>
                            </select>
                        </div>
                        <div class="filter-user btn-group">
                                <select onchange="this.form.submit()" class="inputbox input-medium" name="filter_assigned" id="filter_assigned">
                                    <option value=""><?php echo JText::_('JOPTION_SELECT_ASSIGNED_USER');?></option>
                                    <?php echo JHtml::_('select.options', $this->assigned, 'value', 'text', $this->state->get('filter.assigned'));?>
                            </select>
                        </div>
                    <?php  else : ?>
                        <input type="hidden" name="filter_assigned" id="filter_assigned" value="<?php echo $this->escape($this->state->get('filter.assigned'));?>"/>
                    <?php endif; ?>
                    <?php if ($this->access->get('task.edit.state') || $this->access->get('task.edit')) : ?>
                        <div class="filter-status btn-group">
                                <select onchange="this.form.submit()" class="inputbox input-medium" name="filter_published" id="filter_published">
                                    <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
                                    <?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <div class="filter-priority btn-group">
                        <select onchange="this.form.submit()" class="inputbox input-medium" name="filter_priority" id="filter_priority">
                            <option selected="selected" value=""><?php echo JText::_('JOPTION_SELECT_PRIORITY');?></option>
                            <?php echo JHtml::_('select.options', JHtml::_('projectfork.priorityOptions'), 'value', 'text', $this->state->get('filter.priority'), true);?>
                        </select>
                    </div>
                    <div class="clearfix"> </div>

                    <?php if ($this->state->get('filter.project')) : ?>
                        <hr />
                        <div class="filter-labels">
                            <?php echo JHtml::_('projectfork.filterLabels', 'task', $this->state->get('filter.project'), $this->state->get('filter.labels'));?>
                        </div>
                        <div class="clearfix"> </div>
                    <?php endif; ?>

                </div>
            </div>
            <div id="list-reorder">
               <?php
                $k = 0;
                $x = 0;
                $current_list = '';
                $list_open    = false;
                $item_order   = array();

                foreach($this->items AS $i => $item) :
                    if ($current_list !== $item->list_title) :
                        // JHtml::_('projectfork.ajaxReorder', 'tasklist_' . $i, 'tasks', $k);
                        if ($item->list_title) :
                            $access = ProjectforkHelperAccess::getActions('tasklist', $item->list_id);

                            $can_create   = $access->get('tasklist.create');
                            $can_edit     = $access->get('tasklist.edit');
                            $can_checkin  = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out_list == $uid || $item->checked_out_list == 0);
                            $can_edit_own = ($access->get('tasklist.edit.own') && $item->list_created_by == $uid);
                            $can_change   = ($access->get('tasklist.edit.state') && $can_checkin);
                        endif;
                        ?>
                        <?php if ($list_open) : ?>
                                </ul>
                                <input type="hidden" name="item-order-<?php echo $k;?>" id="item_order_<?php echo $k;?>" value="<?php echo implode($item_order,'|'); ?>" />
                            </div>
                            <?php
                            $list_open  = false;
                            $item_order = array();
                        endif;
                        ?>
                        <div class="cat-list-row<?php echo $k;?>">
                        	<?php if ($item->list_title) : ?>
	                            <div class="list-title btn-toolbar">
	                            	<div class="btn-group">
		                                <h3>
		                                    <a href="<?php echo JRoute::_(ProjectforkHelperRoute::getTasksRoute($item->project_slug, $item->milestone_slug, $item->list_slug));?>">
		                                        <?php echo $this->escape($item->list_title);?>
		                                    </a>
		                                    <small><?php echo $this->escape($item->list_description);?></small>
		                                </h3>
	                            	</div>
                                    <?php
                                        $this->menu->start(array('class' => 'btn-mini'));
                                        $this->menu->itemEdit('tasklistform', $item->list_id, ($can_edit || $can_edit_own));
                                        $this->menu->itemTrash('tasklists', $x, ($can_edit || $can_edit_own));
                                        $this->menu->end();
                                        echo $this->menu->render();
                                    ?>
	                                <div class="clearfix"></div>
	                            </div>
                            <?php endif; ?>
                            <ul class="list-tasks list-striped list-condensed unstyled" id="tasklist_<?php echo $i;?>">
                        <?php
                        $k            = 1 - $k;
                        $list_open    = true;
                        $current_list = $item->list_title;
                        $x++;
                        // End of Task List
                    endif;
                    ?>
                    <?php
                        // Start task item
                        $access = ProjectforkHelperAccess::getActions('task', $item->id);
                        $item_order[] = $item->ordering;

                        $can_create   = $access->get('task.create');
                        $can_edit     = $access->get('task.edit');
                        $can_checkin  = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
                        $can_edit_own = ($access->get('task.edit.own') && $item->created_by == $uid);
                        $can_change   = ($access->get('task.edit.state') || $can_checkin);

                        // Task completed javascript
                        $cbjs     = '';
                        $disabled = ' disabled = disabled';
                        $checked  = ($item->complete ? ' checked="checked"' : '');

                        if ($can_change) {
                            $cbjs     = ' onclick="setTaskComplete('.intval($item->id).', this.checked);"';
                            $disabled = '';
                        }

                        // list item class
                        $class = ($item->complete ? 'task-complete' : 'task-incomplete');
                    ?>
                    <li id="list-item-<?php echo $x; ?>" class="<?php echo $class;?>">
                        <input type="hidden" name="order[]" value="<?php echo (int) $item->ordering;?>"/>

                        <div id="list-toolbar-<?php echo $x; ?>" class="btn-toolbar <?php if ($item->complete) : echo "complete"; endif;?>">
                            <?php if ($can_change) : ?>
                                <label for="cb<?php echo $x; ?>" class="checkbox pull-left">
                                    <?php echo JHtml::_('projectfork.id', $x, $item->id); ?>
                                </label>
                            <?php endif; ?>
                            <div class="btn-group">
                                <?php echo JHtml::_('projectfork.task.complete', $x, $item->complete, $can_change); ?>
                            </div>
                            <div class="btn-group">
                                <h5 class="task-title"><a href="<?php echo JRoute::_(ProjectforkHelperRoute::getTaskRoute($item->slug, $item->project_slug, $item->milestone_slug, $item->list_slug));?>">
                                    <?php if ($item->checked_out) : ?><i class="icon-lock"></i> <?php endif; ?>
                                    <?php echo $this->escape($item->title);?>
                                </a></h5>
                            </div>
                            <div class="btn-group">
                                <small><?php echo $this->escape(JHtml::_('projectfork.truncate', $item->description));?></small>
                            </div>
                            <?php
                                echo $this->menu->assignedUsers($x, $item->id, 'tasks', $item->users, ($can_edit || $can_edit_own), 'btn-mini');
                                echo $this->menu->priorityList($x, $item->id, 'tasks', $item->priority, ($can_edit || $can_edit_own || $can_change), 'btn-mini');

                                $this->menu->start(array('class' => 'btn-mini'));
                                $this->menu->itemEdit('taskform', $item->id, ($can_edit || $can_edit_own));
                                $this->menu->itemTrash('tasks', $x, ($can_edit || $can_edit_own));
                                $this->menu->end();

                                echo $this->menu->render();
                            ?>
                        </div>
                    </li>
                <?php
                    $x++;
                    endforeach;
                ?>
                <?php if ($list_open) : ?>
                        </ul>
                        <input type="hidden" name="item-order-<?php echo $k;?>" id="item_order_<?php echo $k;?>" value="<?php echo implode($item_order,'|'); ?>" />
                    </div>
                <?php $list_open = false; endif; ?>
            </div>

            <input type="hidden" id="boxchecked" name="boxchecked" value="0" />
            <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
            <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
            <input type="hidden" name="task" value="" />
            <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
</div>
