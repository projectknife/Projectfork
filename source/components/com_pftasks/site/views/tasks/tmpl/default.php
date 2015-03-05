<?php
/**
 * @package      Projectfork
 * @subpackage   Tasks
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


JHtml::_('pfhtml.script.jquerysortable');
JHtml::_('pfhtml.script.listform');
JHtml::_('pfhtml.script.task');
JHtml::script('com_projectfork/projectfork/jquery.PrintArea.js', false, true);
$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$user       = JFactory::getUser();
$uid        = $user->get('id');

$action_count = count($this->actions);
$filter_in    = ($this->state->get('filter.isset') ? 'in ' : '');
$can_order    = $user->authorise('core.edit.state', 'com_pftasks');

$repo_enabled  = PFApplicationHelper::enabled('com_pfrepo');
$cmnts_enabled = PFApplicationHelper::enabled('com_pfcomments');
$time_enabled  = PFApplicationHelper::enabled('com_pftime');
$can_track     = ($user->authorise('core.create', 'com_pftime') && $time_enabled);

$doc   = JFactory::getDocument();
$style = '.complete .task-title > a, .complete .task-description, .complete .caret {'
        . 'opacity:0.35;'
        . '}'
        . '.task-title > a {'
        . 'margin-left:10px;'
        . 'margin-right:10px;'
        . '}'
        . '.margin-none {'
        . 'margin: 0;'
        . '}'
        . '.priority-1 {'
        . 'border-left:2px solid #CCC;'
        . '}'
        . '.priority-2 {'
        . 'border-left:2px solid #468847;'
        . '}'
        . '.priority-3 {'
        . 'border-left:2px solid #3a87ad;'
        . '}'
        . '.priority-4 {'
        . 'border-left:2px solid #c09853;'
        . '}'
        . '.priority-5 {'
        . 'border-left:2px solid #b94a48;'
        . '}'
        . '.list-striped .dropdown-menu li {'
        . 'background-color:transparent;'
        . 'padding: 0;'
        . 'border-bottom-width: 0;'
        . '}'
        . '.list-striped .dropdown-menu li.divider {'
        . 'background-color: rgba(0, 0, 0, 0.1);'
        . 'margin: 2px 0;'
        . '}';
$doc->addStyleDeclaration( $style );
$doc->addScriptDeclaration('
jQuery(document).ready(function()
{
	jQuery("div#print_btn").click(function(){		
		var options = {mode:"popup"};
		jQuery(".PrintArea.all").printArea(options);
	});
});
');
?>
<script type="text/javascript">
jQuery(document).ready(function() {
    <?php if ($can_track) : ?>
        var turl  = '<?php echo JRoute::_('index.php?option=com_pftime&task=recorder.add&tmpl=component', false); ?>';
        var topts = 'width=500,height=600,resizable=yes,'
                  + 'scrollbars=yes,toolbar=no,location=no,'
                  + 'directories=no,status=no,menubar=no';

        PFtask.setTimeTracker(turl, topts);
    <?php endif; ?>

    <?php if ($uid && $this->state->get('filter.project') && $can_order) : ?>
        PFlist.sortable('.list-tasks', 'tasks');
    <?php endif; ?>
});
</script>
<div id="projectfork" class="category-list<?php echo $this->pageclass_sfx;?> view-tasks PrintArea all">

    <?php if ($this->params->get('show_page_heading', 1)) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>

    <div class="clearfix"></div>

    <div class="cat-items">
        <form id="adminForm" name="adminForm" method="post" action="<?php echo htmlspecialchars(JFactory::getURI()->toString()); ?>">
            <div class="btn-toolbar btn-toolbar-top">
                <?php echo $this->toolbar;?>
                <div class="filter-project btn-group">
                    <?php echo JHtml::_('pfhtml.project.filter');?>
                </div>
				<div class="btn button b1" id="print_btn"><?php echo JText::_('COM_PROJECTFORK_PRINT'); ?></div>
            </div>

            <div class="clearfix"> </div>

            <div class="<?php echo $filter_in;?>collapse" id="filters">
                <div class="btn-toolbar">
                    <div class="filter-search btn-group pull-left">
                        <input type="text" name="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />
                    </div>
                    <div class="filter-search-buttons btn-group pull-left">
                        <button type="submit" class="btn" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>">
                            <span aria-hidden="true" class="icon-search"></span>
                        </button>
                        <button type="button" class="btn" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.id('filter_search').value='';this.form.submit();">
                           <span aria-hidden="true" class="icon-remove"></span>
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
                    <?php endif; ?>

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

                    <?php if ($this->access->get('core.edit.state') || $this->access->get('core.edit')) : ?>
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
                            <?php echo JHtml::_('select.options', JHtml::_('pftasks.priorityOptions'), 'value', 'text', $this->state->get('filter.priority'), true);?>
                        </select>
                    </div>

                    <div class="filter-complete btn-group">
                        <select onchange="this.form.submit()" class="inputbox input-medium" name="filter_complete" id="filter_complete">
                            <option selected="selected" value=""><?php echo JText::_('JOPTION_SELECT_COMPLETITION');?></option>
                            <?php echo JHtml::_('select.options', JHtml::_('pftasks.completeOptions'), 'value', 'text', $this->state->get('filter.complete'), true);?>
                        </select>
                    </div>

                    <?php if ($this->state->get('filter.project')) : ?>
                        <hr />
                        <div class="filter-labels">
                            <?php echo JHtml::_('pfhtml.label.filter', 'com_pftasks.task', $this->state->get('filter.project'), $this->state->get('filter.labels'));?>
                        </div>
                        <div class="clearfix"> </div>
                    <?php endif; ?>

                    <div class="clearfix"> </div>

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
                        if ($item->list_title) :
                            $access = PFtasksHelper::getListActions($item->list_id);

                            $can_create   = $access->get('core.create');
                            $can_edit     = $access->get('core.edit');
                            $can_checkin  = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out_list == $uid || $item->checked_out_list == 0);
                            $can_edit_own = ($access->get('core.edit.own') && $item->list_created_by == $uid);
                            $can_change   = ($access->get('core.edit.state') && $can_checkin);
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

                               <h3>
		                          <a class="pull-left" href="<?php echo JRoute::_(PFtasksHelperRoute::getTasksRoute($item->project_slug, $item->milestone_slug, $item->list_slug));?>">
		                              <?php echo $this->escape($item->list_title);?>
		                          </a>
		                          <?php
                                    $this->menu->start(array('class' => 'btn-mini btn-link', 'pull' => 'left'));
                                    $this->menu->itemEdit('tasklistform', $item->list_id, ($can_edit || $can_edit_own));
                                    $this->menu->itemTrash('tasklists', $x, ($can_edit || $can_edit_own));
                                    $this->menu->end();
                                    echo $this->menu->render(array('class' => 'btn-mini btn-link', 'pull' => 'left'));
	                              ?>
		                           <small><?php echo htmlspecialchars_decode($this->escape($item->list_description));?></small>
	                           </h3>

                               <div class="clearfix clr"></div>
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
                        $access = PFtasksHelper::getActions($item->id);
                        $item_order[] = $item->ordering;

                        $can_create   = $access->get('core.create');
                        $can_edit     = $access->get('core.edit');
                        $can_checkin  = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
                        $can_edit_own = ($access->get('core.edit.own') && $item->created_by == $uid);
                        $can_change   = ($access->get('core.edit.state') && $can_checkin);

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

                        // Prepare the watch button
                        $watch = '';

                        if ($uid) {
                            $options = array('a-class' => 'btn-mini', 'div-class' => 'pull-right');
                            $watch = JHtml::_('pfhtml.button.watch', 'tasks', $x, $item->watching, $options);
                        }

                        // Deadline and completition date
                        $task_date = JHtml::_(
                            'pfhtml.label.datetime',
                            ($item->complete ? $item->completed : $item->end_date),
                            false,
                            ($item->complete ? array('past-class' => 'label-success', 'past-icon' => 'calendar') : array())
                        );
                    ?>
                    <li id="list-item-<?php echo $x; ?>" class="<?php echo $class;?> clearfix <?php if ($item->complete) : echo "complete"; endif;?> priority-<?php echo $item->priority;?>">
                        <div class="task-row clearfix">
	                        <input type="hidden" name="order[]" value="<?php echo (int) $item->ordering;?>"/>
	                        <?php if ($can_change || $uid) : ?>
	                                <label for="cb<?php echo $x; ?>" class="checkbox pull-right hidden-phone">
	                                    <?php echo JHtml::_('pf.html.id', $x, $item->id); ?>
	                                </label>
	                            <?php endif; ?>
	                        <div id="list-toolbar-<?php echo $x; ?>" class="pull-left">
	                            <div class="btn-group">
	                                <?php echo JHtml::_('pftasks.complete', $x, $item->complete, $can_change, $item->dependencies, $item->users, $item->start_date); ?>
	                            </div>
	                        </div>
	                        <span class="task-title">
	                            <a href="<?php echo JRoute::_(PFtasksHelperRoute::getTaskRoute($item->slug, $item->project_slug, $item->milestone_slug, $item->list_slug));?>" class="pull-left">
	                                <?php if ($item->checked_out) : ?><span aria-hidden="true" class="icon-lock"></span> <?php endif; ?>
	                                <?php echo $this->escape($item->title);?>
	                            </a>
	                            <span class="dropdown pull-left">
	                            	<?php
		                                $this->menu->start(array('class' => 'btn-mini btn-link'));
		                                $itm_icon = 'icon-menu-2';
	                                    $itm_txt  = 'COM_PROJECTFORK_DETAILS_LABEL';
	                                    $itm_link = '#collapse-' . $x;
	                                    $this->menu->itemCollapse($itm_icon, $itm_txt, $itm_link);

	                                    $this->menu->itemDivider();

		                                $this->menu->itemEdit('taskform', $item->id, ($can_edit || $can_edit_own));
		                                $this->menu->itemTrash('tasks', $x, ($can_edit || $can_edit_own));

	                                    if ($can_track) {
	                                        $this->menu->itemDivider();
	                                        $this->menu->itemJavaScript('icon-clock ', 'COM_PROJECTFORK_TASKS_TRACK_TIME', 'PFtask.trackItem(' . $item->id . ');');
	                                    }

		                                if (($can_edit || $can_edit_own)) {
		                                    $itm_icon = 'icon-plus';
		                                    $itm_txt  = 'COM_PROJECTFORK_ASSIGN_TO_USER';
		                                    $itm_link = PFusersHelperRoute::getUsersRoute() . '&amp;layout=modal&amp;tmpl=component&amp;field=PFtaskAssignUser';

		                                    $this->menu->itemDivider();
		                                    $this->menu->itemModal($itm_icon, $itm_txt, $itm_link, "PFlist.setTarget(" . $x . ");");
		                                }

		                                if ($can_change) {
		                                    $itm_icon = 'icon-warning';
		                                    $itm_pfx  = 'COM_PROJECTFORK_PRIORITY';
		                                    $itm_ac   = 'PFtask.priority(' . $x . ',';

		                                    $this->menu->itemDivider();
		                                    $this->menu->itemJavaScript($itm_icon, $itm_pfx. '_VERY_LOW', $itm_ac . ' 1, \'' . addslashes(JText::_($itm_pfx. '_VERY_LOW')) . '\')');
		                                    $this->menu->itemJavaScript($itm_icon, $itm_pfx. '_LOW', $itm_ac . ' 2, \'' . addslashes(JText::_($itm_pfx. '_LOW')) . '\')');
		                                    $this->menu->itemJavaScript($itm_icon, $itm_pfx. '_MEDIUM', $itm_ac . ' 3, \'' . addslashes(JText::_($itm_pfx. '_MEDIUM')) . '\')');
		                                    $this->menu->itemJavaScript($itm_icon, $itm_pfx. '_HIGH', $itm_ac . ' 4, \'' . addslashes(JText::_($itm_pfx. '_HIGH')) . '\')');
		                                    $this->menu->itemJavaScript($itm_icon, $itm_pfx. '_VERY_HIGH', $itm_ac . ' 5, \'' . addslashes(JText::_($itm_pfx. '_VERY_HIGH')) . '\')');
		                                }

		                                $this->menu->end();

		                                echo $this->menu->render(array('class' => 'btn-mini'));
		                            ?>
	                            </span>
	                        </span>
	                        <small class="task-description">
                                <?php echo htmlspecialchars_decode($this->escape(JHtml::_('pf.html.truncate', $item->description, '100')));?>
                         </small>
                        </div>
                        <div id="collapse-<?php echo $x; ?>" class="collapse">
                        	<hr />
                        	<?php echo $watch; ?>
                        	<?php echo JHtml::_('pftasks.assignedLabel', $item->id, $x, $item->users); ?>
	                        <?php echo JHtml::_('pftasks.priorityLabel', $item->id, $x, $item->priority); ?>
	                        <?php echo $task_date; ?>
	                        <?php if ($item->access != 1) {
	                        	echo JHtml::_('pfhtml.label.access', $item->access);
	                        	}
	                        ?>
	                        <?php if ($cmnts_enabled) : echo JHtml::_('pfcomments.label', $item->comments); endif; ?>
	                        <?php if ($repo_enabled) : echo JHtml::_('pfrepo.attachmentsLabel', $item->attachments); endif; ?>
	                        <?php if ($item->label_count) : echo JHtml::_('pfhtml.label.labels', $item->labels); endif; ?>
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

            <?php if ($can_order) : ?>
                <?php if (!$this->state->get('filter.project')) : ?>
                    <div class="alert"><?php echo JText::_('COM_PROJECTFORK_REORDER_DISABLED'); ?></div>
                <?php else: ?>
                    <div class="alert alert-success"><?php echo JText::_('COM_PROJECTFORK_REORDER_ENABLED'); ?></div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($this->pagination->get('pages.total') > 1) : ?>
                <div class="pagination center">
                    <?php echo $this->pagination->getPagesLinks(); ?>
                </div>
                <p class="counter center">
                	<?php echo $this->pagination->getPagesCounter(); ?>
                </p>
            <?php endif; ?>

            <?php if (!$this->state->get('filter.project')) : ?>
                <div class="filters center">
                    <span class="display-limit">
                        <?php echo $this->pagination->getLimitBox(); ?>
                    </span>
                </div>
            <?php endif; ?>

            <input type="hidden" id="boxchecked" name="boxchecked" value="0" />
            <input type="hidden" id="target-item" name="target_item" value="0" />
            <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
            <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
            <input type="hidden" name="task" value="" />
            <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
</div>
