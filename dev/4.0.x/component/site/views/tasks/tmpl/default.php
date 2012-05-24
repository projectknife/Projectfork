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
	<div class="btn-toolbar">
	    <?php if ($this->params->get('show_page_heading', 1)) : ?>
	    	<div class="btn-group">
	      	  <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
	        </div>
	    <?php endif; ?>
	    <div class="btn-group">
	   	 <?php echo $this->toolbar;?>
	    </div>
    </div>
	<div class="clearfix"></div>

	<div class="cat-items">

		<form id="adminForm" name="adminForm" method="post" action="<?php echo htmlspecialchars(JFactory::getURI()->toString()); ?>">

			<fieldset class="filters btn-toolbar">
				<?php if($this->params->get('filter_fields')) : ?>
					<div class="btn-group pull-right">
						<a data-toggle="collapse" data-target="#filters" class="btn"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?> <span class="caret"></span></a>
					</div>
				<?php endif; ?>
                <div class="filter-project btn-group">
                    <?php echo JHtml::_('projectfork.filterProject');?>
                </div>
			</fieldset>
			<div class="clearfix"> </div>
			<div class="collapse" id="filters">
				<?php if($this->params->get('filter_fields')) : ?>
					<div class="well btn-toolbar">
                    <?php if($this->state->get('filter.project')) : ?>
                        <div class="filter-milestone btn-group">
        				    <select onchange="this.form.submit()" class="inputbox" name="filter_milestone" id="milestone">
    						    <option value=""><?php echo JText::_('JOPTION_SELECT_MILESTONE');?></option>
    				            <?php echo JHtml::_('select.options', $this->milestones, 'value', 'text', $this->state->get('filter.milestone'));?>
        					</select>
        				</div>
        				<div class="filter-tasklist btn-group">
        				    <select id="filter_tasklist" name="filter_tasklist" class="inputbox" onchange="this.form.submit()">
        						<option value=""><?php echo JText::_('JOPTION_SELECT_TASKLIST');?></option>
        						<?php echo JHtml::_('select.options', $this->tasklists, 'value', 'text', $this->state->get('filter.tasklist'));?>
        					</select>
        				</div>
                        <div class="filter-author btn-group">
                            <select id="filter_author" name="filter_author" class="inputbox" onchange="this.form.submit()">
                				<option value=""><?php echo JText::_('JOPTION_SELECT_AUTHOR');?></option>
                				<?php echo JHtml::_('select.options', $this->authors, 'value', 'text', $this->state->get('filter.author'));?>
                			</select>
                        </div>
        				<div class="filter-user btn-group">
        						<select onchange="this.form.submit()" class="inputbox" name="filter_assigned" id="filter_assigned">
        						    <option value=""><?php echo JText::_('JOPTION_SELECT_ASSIGNED_USER');?></option>
        				            <?php echo JHtml::_('select.options', $this->assigned, 'value', 'text', $this->state->get('filter.assigned'));?>
        					</select>
        				</div>
                        <div class="filter-priority btn-group">
    						<select onchange="this.form.submit()" class="inputbox" name="filter_priority" id="filter_priority">
        						<option selected="selected" value=""><?php echo JText::_('JOPTION_SELECT_PRIORITY');?></option>
        						<?php echo JHtml::_('select.options', $this->priorities, 'value', 'text', $this->state->get('filter.priority'));?>
        					</select>
        				</div>
                    <?php  else : ?>
                        <input type="hidden" name="filter_assigned" id="filter_assigned"/>
                    <?php endif; ?>

                    <?php if ($user->authorise('core.edit.state', 'com_projectfork') || $user->authorize('task.edit.state', 'com_projectfork')
                          ||  $user->authorise('core.edit', 'com_projectfork') || $user->authorize('task.edit', 'com_projectfork')) : ?>
        				<div class="filter-status btn-group">
        						<select onchange="this.form.submit()" class="inputbox" name="filter_published" id="filter_published">
        						    <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
        				            <?php echo JHtml::_('select.options', $this->states, 'value', 'text', $this->state->get('filter.published'), true);?>
        					</select>
        				</div>
                    <?php endif; ?>
					</div>
                <?php else : ?>
                    <input type="hidden" name="filter_assigned" id="filter_assigned"/>
                <?php endif; ?>

			</div>
			<div id="list-reorder">
               <?php
                $k = 0;
                $x = 0;
                $current_list = '';
                $list_open    = false;
                $item_order   = array();

                foreach($this->items AS $i => $item) :
                ?>
                    <?php if($current_list !== $item->tasklist_title) :
                        JHtml::_('projectfork.ajaxReorder', 'tasklist_'.$i, 'tasks', $k);
                        if($item->tasklist_title) :
                            $asset_name = 'com_projectfork.tasklist.'.$item->list_id;

        		            $canCreate	= ($user->authorise('core.create', $asset_name) || $user->authorise('tasklist.create', $asset_name));
        		            $canEdit	= ($user->authorise('core.edit', $asset_name) || $user->authorise('tasklist.edit', $asset_name));
        		            $canCheckin	= ($user->authorise('core.manage', 'com_checkin') || $item->checked_out_list == $uid || $item->checked_out_list == 0);
        		            $canEditOwn	= (($user->authorise('core.edit.own', $asset_name) || $user->authorise('tasklist.edit.own', $asset_name)) && $item->list_created_by == $uid);
        		            $canChange	= (($user->authorise('core.edit.state',	$asset_name) || $user->authorise('tasklist.edit.state', $asset_name)) && $canCheckin);
                        endif;
                        ?>
                        <?php if($list_open) : ?>
                                 </ul>
                                 <input type="hidden" name="item-order-<?php echo $k;?>" id="item_order_<?php echo $k;?>" value="<?php echo implode($item_order,'|'); ?>" />
                             </div>
                             <hr />
                        <?php
                            $list_open  = false;
                            $item_order = array();
                            endif;
                        ?>

                        <div class="cat-list-row<?php echo $k;?>">
    	               		<div class="list-title">
    	               			<div class="btn-toolbar">
    		               			<?php if($action_count) : ?>
        		               			<div class="btn-group">
        		               			   <span class="list-select">
        		               			        <?php echo JHtml::_('grid.id', $x, $item->list_id, false, 'lid'); ?>
        		               				</span>
        		               			</div>
    		               			<?php endif; ?>
                                    <?php if($item->tasklist_title) : ?>
        		               			<div class="btn-group">
        		               				<h3>
        			                            <a href="<?php echo JRoute::_(ProjectforkHelperRoute::getTasksRoute($item->project_slug, $item->milestone_slug, $item->list_slug));?>">
        			                                <?php echo $this->escape($item->tasklist_title);?>
        			                            </a>
        			                            <small><?php echo $this->escape($item->tasklist_description);?></small>
        		                            </h3>
        	                            </div>
                                        <?php
                                            $this->menu->start(array('class' => 'btn-mini'));
                                            $this->menu->itemEdit('tasklistform', $item->list_id, ($canEdit || $canEditOwn));
                                            $this->menu->itemTrash('tasklists', $x, ($canEdit || $canEditOwn));
                                            $this->menu->end();
                                            echo $this->menu->render();
                                        ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <ul class="list-tasks list-striped unstyled" id="tasklist_<?php echo $i;?>">
                    <?php
                        $k            = 1 - $k;
                        $list_open    = true;
                        $current_list = $item->tasklist_title;
                        $x++;
                        endif;
                    ?>
                    <?php
                    $asset_name   = 'com_projectfork.task.'.$item->id;
                    $item_order[] = $item->ordering;

		            $canCreate	= ($user->authorise('core.create', $asset_name) || $user->authorise('task.create', $asset_name));
		            $canEdit	= ($user->authorise('core.edit', $asset_name) || $user->authorise('task.edit', $asset_name));
		            $canCheckin	= ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
		            $canEditOwn	= (($user->authorise('core.edit.own', $asset_name) || $user->authorise('task.edit.own', $asset_name)) && $item->created_by == $uid);
		            $canChange	= (($user->authorise('core.edit.state',	$asset_name) || $user->authorise('task.edit.state', $asset_name)) && $canCheckin);
                    ?>

                    <li alt="<?php echo (int) $item->id;?>">
           				<div class="btn-toolbar">
           					<?php if($action_count) : ?>
                                <div class="btn-group">
	               			        <i class="icon-move"></i>
                                    <input type="hidden" name="order[]" value="<?php echo (int) $item->ordering;?>"/>
               				    </div>
                   				<div class="btn-group">
    	               				<?php echo JHtml::_('grid.id', $x, $item->id); ?>
                   				</div>
                            <?php endif; ?>
               				<div class="btn-group">
	               				<a href="<?php echo JRoute::_(ProjectforkHelperRoute::getTaskRoute($item->slug, $item->project_slug, $item->milestone_slug, $item->list_slug));?>">
                                   <?php if ($item->checked_out) : ?><i class="icon-lock"></i> <?php endif; ?>
                                   <?php echo $this->escape($item->title);?>
                                </a>
               				</div>
               				<div class="btn-group">
	               				<small><?php echo $this->escape(JHtml::_('projectfork.truncate', $item->description));?></small>
               				</div>
                            <?php
                                echo $this->menu->assignedUsers($x, $item->id, 'tasks', $item->users, ($canEdit || $canEditOwn));
                                echo $this->menu->priorityList($x, $item->id, 'tasks', $item->priority, ($canEdit || $canEditOwn || $canChange));

                                $this->menu->start(array('class' => 'btn-mini'));
                                $this->menu->itemEdit('taskform', $item->id, ($canEdit || $canEditOwn));
                                $this->menu->itemTrash('tasks', $i, ($canEdit || $canEditOwn));
                                $this->menu->end();
                                echo $this->menu->render();
                            ?>
           				</div>
           			</li>
                <?php
                    $x++;
                    endforeach;
                ?>
                <?php if($list_open) : ?>
                    </ul>
                    <input type="hidden" name="item-order-<?php echo $k;?>" id="item_order_<?php echo $k;?>" value="<?php echo implode($item_order,'|'); ?>" />
                </div>
                <?php $list_open = false; endif; ?>
            </div>

            <input type="hidden" name="boxchecked" value="0" />
            <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
	        <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
            <input type="hidden" name="task" value="" />
	        <?php echo JHtml::_('form.token'); ?>
	    </form>
    </div>
</div>