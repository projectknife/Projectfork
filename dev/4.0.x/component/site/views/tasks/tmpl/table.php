<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.php
*
* This file is part of Projectfork.
*
* Projectfork is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*
* Projectfork is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Projectfork. If not, see <http://www.gnu.org/licenses/gpl.html>.
**/

defined('_JEXEC') or die;


$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$save_order = ($list_order == 'a.ordering');
$user	    = JFactory::getUser();
$uid	    = $user->get('id');

$action_count = count($this->actions);
?>
<div id="projectfork" class="category-list<?php echo $this->pageclass_sfx;?> view-tasks">

    <?php if ($this->params->get('show_page_heading', 1)) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>

    <?php echo $this->toolbar;?>


	<div class="cat-items">

		<form id="adminForm" name="adminForm" method="post" action="<?php echo htmlspecialchars(JFactory::getURI()->toString()); ?>">

			<fieldset class="filters">
				<span class="filter-project">
                    <?php echo JHtml::_('projectfork.filterProject');?>
                </span>
                <?php if($this->params->get('filter_fields')) : ?>

                    <?php if($this->state->get('filter.project')) : ?>
                        <span class="filter-milestone">
        						<select onchange="this.form.submit()" size="1" class="inputbox" name="filter_milestone" id="filter_milestone">
        						    <option value=""><?php echo JText::_('JOPTION_SELECT_MILESTONE');?></option>
        				            <?php echo JHtml::_('select.options', $this->milestones, 'value', 'text', $this->state->get('filter.milestone'));?>
        					</select>
                            <span class="filter-tasklist">
                                <select id="filter_tasklist" name="filter_tasklist" class="inputbox" onchange="this.form.submit()">
                    				<option value=""><?php echo JText::_('JOPTION_SELECT_TASKLIST');?></option>
                    				<?php echo JHtml::_('select.options', $this->tasklists, 'value', 'text', $this->state->get('filter.tasklist'));?>
                    			</select>
                            </span>
        				</span>
                        <span class="filter-author">
                            <select id="filter_author" name="filter_author" class="inputbox" onchange="this.form.submit()">
                				<option value=""><?php echo JText::_('JOPTION_SELECT_AUTHOR');?></option>
                				<?php echo JHtml::_('select.options', $this->authors, 'value', 'text', $this->state->get('filter.author'));?>
                			</select>
                        </span>
        				<span class="filter-user">
        						<select onchange="this.form.submit()" size="1" class="inputbox" name="filter_assigned" id="filter_assigned">
        						    <option value=""><?php echo JText::_('JOPTION_SELECT_ASSIGNED_USER');?></option>
        				            <?php echo JHtml::_('select.options', $this->assigned, 'value', 'text', $this->state->get('filter.assigned'));?>
        					</select>
        				</span>
                        <span class="filter-priority">
    						<select onchange="this.form.submit()" size="1" class="inputbox" name="filter_priority" id="filter_priority">
        						<option selected="selected" value=""><?php echo JText::_('JOPTION_SELECT_PRIORITY');?></option>
        						<?php echo JHtml::_('select.options', $this->priorities, 'value', 'text', $this->state->get('filter.priority'));?>
        					</select>
        				</span>
                    <?php endif; ?>

                    <?php if ($user->authorise('core.edit.state', 'com_projectfork') || $user->authorise('task.edit.state', 'com_projectfork')
                          ||  $user->authorise('core.edit', 'com_projectfork') || $user->authorise('task.edit', 'com_projectfork')) : ?>
        				<span class="filter-status">
        						<select onchange="this.form.submit()" size="1" class="inputbox" name="filter_published" id="filter_published">
        						    <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
        				            <?php echo JHtml::_('select.options', $this->states, 'value', 'text', $this->state->get('filter.published'), true);?>
        					</select>
        				</span>
                    <?php endif; ?>

                <?php endif; ?>

                <?php if ($this->params->get('show_pagination_limit')) : ?>
		            <span class="display-limit">
			            <?php echo $this->pagination->getLimitBox(); ?>
		            </span>
		        <?php endif; ?>

			</fieldset>

            <?php if(count($this->items) != 0) : ?>
    			<table class="category table table-striped">
        			<thead>
        				<tr>
        					<?php if($action_count) : ?>
                           	    <th id="tableOrdering0" class="list-select" width="1%">
                           			<input type="checkbox" onclick="checkAll(<?php echo count($this->items);?>);" value="" name="toggle" />
                           		</th>
                            <?php endif; ?>
                            <th id="tableOrdering1" class="list-actions" width="1%">
        	               	    <?php echo $this->menu->bulkItems($this->actions); ?>
        	               	</th>
        					<th id="tableOrdering2" class="list-title">
        	               		<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $list_dir, $list_order); ?>
                            </th>
                            <?php if($this->params->get('task_list_col_project')) : ?>
        	               		<th id="tableOrdering3" class="list-project">
                                    <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_PROJECT', 'project_title', $list_dir, $list_order); ?>
                                </th>
                            <?php endif; ?>
                            <?php if($this->params->get('task_list_col_milestone')) : ?>
        	               		<th id="tableOrdering4" class="list-project">
                                    <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_MILESTONE', 'milestone_title', $list_dir, $list_order); ?>
                                </th>
                            <?php endif; ?>
                            <?php if($this->params->get('task_list_col_tasklist')) : ?>
        	               		<th id="tableOrdering5" class="list-project">
                                    <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_TASKLIST', 'tasklist_title', $list_dir, $list_order); ?>
                                </th>
                            <?php endif; ?>
                            <?php if($this->params->get('task_list_col_priority')) : ?>
            					<th id="tableOrdering6" class="list-priority">
            						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_PRIORITY', 'a.priority', $list_dir, $list_order); ?>
            					</th>
                            <?php endif; ?>

                            <?php if($this->params->get('task_list_col_deadline')) : ?>
        	               		<th id="tableOrdering7" class="list-deadline">
                                    <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_DEADLINE', 'a.end_date', $list_dir, $list_order); ?>
                                </th>
                            <?php endif; ?>
                            <th id="tableOrdering8" class="list-ordering">
            					<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ORDERING', 'a.ordering', $list_dir, $list_order); ?>
            					<?php if ($save_order) :?>
            						<?php echo JHtml::_('grid.order',  $this->items, 'filesave.png', 'tasks.saveorder'); ?>
            					<?php endif; ?>
            				</th>
        				</tr>
        			</thead>

        			<tbody>
                        <?php
                        $k = 0;
                        foreach($this->items AS $i => $item) :
                            $asset_name = 'com_projectfork.task.'.$item->id;
                            $ordering	= ($list_order == 'a.ordering');

    			            $canCreate	= ($user->authorise('core.create', $asset_name) || $user->authorise('task.create', $asset_name));
    			            $canEdit	= ($user->authorise('core.edit', $asset_name) || $user->authorise('task.edit', $asset_name));
    			            $canCheckin	= ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
    			            $canEditOwn	= (($user->authorise('core.edit.own', $asset_name) || $user->authorise('task.edit.own', $asset_name)) && $item->created_by == $uid);
    			            $canChange	= (($user->authorise('core.edit.state',	$asset_name) || $user->authorise('task.edit.state', $asset_name)) && $canCheckin);
                        ?>
                            <tr class="cat-list-row<?php echo $k;?>">
        	               		<?php if($action_count) : ?>
                                   <td class="list-select">
                                        <?php echo JHtml::_('grid.id', $i, $item->id); ?>
        	               		    </td>
                                <?php endif; ?>
                                <td class="list-actions">
                                    <?php
                                        $this->menu->start();
                                        $this->menu->itemEdit('taskform', $item->id, ($canEdit || $canEditOwn));
                                        $this->menu->itemTrash('tasks', $i, ($canEdit || $canEditOwn));
                                        $this->menu->end();

                                        echo $this->menu->render();
                                    ?>
        	               		</td>
        	               		<td class="list-title">
        	               		    <?php if ($item->checked_out) : ?><i class="icon-lock"></i> <?php endif; ?>
                                    <a href="<?php echo JRoute::_(ProjectforkHelperRoute::getTaskRoute($item->slug, $item->project_slug, $item->milestone_slug, $item->list_slug));?>">
                                        <?php echo $this->escape($item->title);?>
                                    </a>
        	               		</td>
                                <?php if($this->params->get('task_list_col_project')) : ?>
            	               		<td class="list-project">
            		               		<a class="btn" href="<?php echo JRoute::_(ProjectforkHelperRoute::getDashboardRoute($item->project_slug));?>">
                                           <i class="icon-map-marker"></i> <?php echo $this->escape($item->project_title);?>
                                        </a>
            	               		</td>
                                <?php endif; ?>
                                <?php if($this->params->get('task_list_col_milestone')) : ?>
            	               		<td class="list-milestone">
                                        <?php if($item->milestone_id) : ?>
                                            <a class="btn" href="<?php echo JRoute::_(ProjectforkHelperRoute::getMilestonesRoute($item->project_slug));?>">
                                               <i class="icon-map-marker"></i> <?php echo $this->escape($item->milestone_title);?>
                                            </a>
                                        <?php endif; ?>
            	               		</td>
                                <?php endif; ?>
                                <?php if($this->params->get('task_list_col_tasklist')) : ?>
            	               		<td class="list-tasklist">
                                        <?php if($item->list_id) : ?>
                		               		<a class="btn" href="<?php echo JRoute::_(ProjectforkHelperRoute::getTasksRoute($item->project_slug, $item->milestone_slug, $item->list_slug));?>">
                                               <i class="icon-ok"></i> <?php echo $this->escape($item->tasklist_title);?>
                                            </a>
                                        <?php endif; ?>
            	               		</td>
                                <?php endif; ?>
                                <?php if($this->params->get('task_list_col_priority')) : ?>
            	               		<td class="list-priority">
            		               		<?php echo JHtml::_('projectfork.priorityToString', $item->priority);?>
            	               		</td>
                                <?php endif; ?>
                                <?php if($this->params->get('task_list_col_deadline')) : ?>
        	               		    <td class="list-deadline">
                                        <?php if($item->end_date == $this->nulldate) {
                                            echo JText::_('COM_PROJECTFORK_DATE_NOT_SET');
                                        }
                                        else {
                                            echo JHtml::_('date', $item->end_date, $this->escape( $this->params->get('deadline_format', JText::_('DATE_FORMAT_LC4'))));
                                        }
            		               		?>
            	               		</td>
                                <?php endif; ?>
                                <td class="list-ordering">
                					<?php if ($canChange) : ?>
                						<?php if ($save_order) :?>
                							<?php if ($list_dir == 'asc') : ?>
                								<span><?php echo $this->pagination->orderUpIcon($i, ($item->catid == @$this->items[$i-1]->catid), 'tasks.orderup', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
                								<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, ($item->catid == @$this->items[$i+1]->catid), 'tasks.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
                							<?php elseif ($list_dir == 'desc') : ?>
                								<span><?php echo $this->pagination->orderUpIcon($i, ($item->catid == @$this->items[$i-1]->catid), 'tasks.orderdown', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
                								<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, ($item->catid == @$this->items[$i+1]->catid), 'tasks.orderup', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
                							<?php endif; ?>
                						<?php endif; ?>
                						<?php $disabled = ($save_order ?  '' : 'disabled="disabled"'); ?>
                						<input type="text" name="order[]" size="5" value="<?php echo $item->ordering;?>" <?php echo $disabled ?> class="text-area-order" />
                					<?php else : ?>
                						<?php echo $item->ordering; ?>
                					<?php endif; ?>
                				</td>
        	               	</tr>
                        <?php
                        $k = 1 - $k;
                        endforeach;
                        ?>
        			</tbody>
        		</table>
            <?php endif; ?>

    		<?php if($this->pagination->get('pages.total') > 1 && $this->params->get('show_pagination')) : ?>
                <div class="pagination">
                    <?php if ($this->params->get('show_pagination_results')) : ?>
    				    <p class="counter"><?php echo $this->pagination->getPagesCounter(); ?></p>
    				<?php endif; ?>
    		        <?php echo $this->pagination->getPagesLinks(); ?>
                </div>
            <?php endif; ?>

            <input type="hidden" name="boxchecked" value="0" />
            <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
	        <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
            <input type="hidden" name="task" value="" />
            <input type="hidden" name="layout" value="<?php echo $this->getLayout(); ?>" />
	        <?php echo JHtml::_('form.token'); ?>
	    </form>
    </div>
</div>