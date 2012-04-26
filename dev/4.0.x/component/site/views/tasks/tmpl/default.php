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
        <h1 class="pull-left"><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>
    <?php echo $this->toolbar;?>
	<div class="clearfix"></div>

	<div class="cat-items">

		<form id="adminForm" name="adminForm" method="post" action="<?php echo JRoute::_('index.php?option=com_projectfork&view=tasks'); ?>">

			<fieldset class="filters btn-toolbar">
				<?php if($this->params->get('filter_fields')) : ?>
                    <?php if($this->state->get('filter.project')) : ?>
                        <div class="filter-milestone btn-group">
        						<select onchange="this.form.submit()" class="inputbox" name="filter_milestone" id="milestone">
        						    <option value=""><?php echo JText::_('JOPTION_SELECT_MILESTONE');?></option>
        				            <?php echo JHtml::_('select.options', $this->milestones, 'value', 'text', $this->state->get('filter.milestone'));?>
        					</select>
        				</div>
        				<div class="filter-tasklist btn-group">
        				    <select name="filter_tasklist" class="inputbox" onchange="this.form.submit()">
        						<option value=""><?php echo JText::_('JOPTION_SELECT_TASKLIST');?></option>
        						<?php echo JHtml::_('select.options', $this->tasklists, 'value', 'text', $this->state->get('filter.tasklist'));?>
        					</select>
        				</div>
        				<div class="filter-user btn-group">
        						<select onchange="this.form.submit()" class="inputbox" name="filter_assigned_id" id="filter_assigned_id">
        						    <option value=""><?php echo JText::_('JOPTION_SELECT_ASSIGNED_USER');?></option>
        				            <?php echo JHtml::_('select.options', $this->assigned, 'value', 'text', $this->state->get('filter.assigned_id'));?>
        					</select>
        				</div>
                        <div class="filter-author btn-group">
                            <select name="filter_author_id" class="inputbox" onchange="this.form.submit()">
                				<option value=""><?php echo JText::_('JOPTION_SELECT_AUTHOR');?></option>
                				<?php echo JHtml::_('select.options', $this->authors, 'value', 'text', $this->state->get('filter.author_id'));?>
                			</select>
                        </div>
                    <?php endif; ?>
    				<div class="filter-status btn-group">
    						<select onchange="this.form.submit()" class="inputbox" name="filter_published" id="filter_published">
    						    <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
    				            <?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
    					</select>
    				</div>
    				<div class="filter-priority btn-group">
    						<select onchange="this.form.submit()" class="inputbox" name="filter_priority" id="filter_priority">
    						<option selected="selected" value=""><?php echo JText::_('JOPTION_SELECT_PRIORITY');?></option>
    						<?php echo JHtml::_('select.options', JHtml::_('projectfork.priorityOptions'), 'value', 'text', $this->state->get('filter.priority'));?>
    					</select>
    				</div>
                    <div class="filter-project btn-group">
                        <?php echo JHtml::_('projectfork.filterProject');?>
                    </div>
                <?php endif; ?>
                <?php if ($this->params->get('show_pagination_limit')) : ?>
		            <span class="display-limit btn-group">
			            <?php echo $this->pagination->getLimitBox(); ?>
		            </span>
		        <?php endif; ?>
			</fieldset>

			<div id="list-reorder">
               <?php
                $k = 0;
                foreach($this->items AS $i => $item) :
                    $asset_name = 'com_projectfork.tasklist.'.$item->id;

		            $canCreate	= ($user->authorise('core.create', $asset_name) || $user->authorise('tasklist.create', $asset_name));
		            $canEdit	= ($user->authorise('core.edit', $asset_name) || $user->authorise('tasklist.edit', $asset_name));
		            $canCheckin	= ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
		            $canEditOwn	= (($user->authorise('core.edit.own', $asset_name) || $user->authorise('tasklist.edit.own', $asset_name)) && $item->created_by == $uid);
		            $canChange	= (($user->authorise('core.edit.state',	$asset_name) || $user->authorise('tasklist.edit.state', $asset_name)) && $canCheckin);
                ?>
                    <div class="cat-list-row<?php echo $k;?>">
	               		<div class="list-title">
	               			<div class="btn-toolbar">
		               			<?php if($action_count) : ?>
		               			<div class="btn-group">
		               			   <span class="list-select">
		               			        <?php echo JHtml::_('grid.id', $i, $item->id); ?>
		               				</span>
		               			</div>
		               			<?php endif; ?>
		               			<div class="btn-group">
		               				<h3>
			                            <a href="<?php echo JRoute::_('index.php?option=com_projectfork&view=tasks&filter_tasklist='.intval($item->id).':'.$item->alias);?>">
			                                <?php echo $this->escape($item->title);?>
			                            </a> 
			                            <small><?php echo $this->escape($item->description);?></small>
		                            </h3>
	                            </div>
	                            <div class="btn-group">
	                                <a class="btn btn-mini dropdown-toggle" data-toggle="dropdown" href="#"><span class="caret"></span></a>
	                                <ul class="dropdown-menu">
	                                    <?php if($canEdit || $canEditOwn) : ?>
	                                 <li>
	                                    <a href="<?php echo JRoute::_('index.php?option=com_projectfork&task=tasklistform.edit&id='.intval($item->id).':'.$item->alias);?>">
	                                        <?php echo JText::_('COM_PROJECTFORK_ACTION_EDIT');?>
	                                    </a>
	                                 </li>
	                                 <?php endif; ?>
	                                    <li>
	                                    <a href="#">
	                                        <?php echo JText::_('COM_PROJECTFORK_ACTION_TRASH');?>
	                                    </a>
	                                 </li>
	                                </ul>
	                            </div>
                            </div>
	               		</div>
	               		<ul class="list-tasks list-striped unstyled">
	               			<li>
	               				<div class="btn-toolbar">
	               					<div class="btn-group">
			               				<i class="icon-move"></i> 
		               				</div>
		               				<div class="btn-group">
			               				<input type="checkbox" name="" value="" /> 
		               				</div>
		               				<div class="btn-group">
			               				<a href="#">Task Title</a> 
		               				</div>
		               				<div class="btn-group">
			               				<small>task description will be truncated at 40 char...</small> 
		               				</div>
		               				<div class="btn-group">
		               					<a href="#" class="btn btn-mini dropdown-toggle" data-toggle="dropdown" >Tobias Kuhn <span class="caret"></span></a>
		               					<ul class="dropdown-menu">
		               						<li><a href="#">Kyle Ledbetter</a></li>
		               						<li><a href="#">Melinda Ledbetter</a></li>
		               						<li><a href="#">Tobias Kuhn</a></li>
		               						<li class="divider"></li>
		               						<li><a href="#">Unassigned</a></li>
		               					</ul>
		               				</div>
		               				<div class="btn-group">
		               					<a href="#" class="btn btn-danger btn-mini dropdown-toggle" data-toggle="dropdown" >HIGH <span class="caret"></span></a>
		               					<ul class="dropdown-menu">
		               						<li><a href="#">High</a></li>
		               						<li><a href="#">Medium</a></li>
		               						<li><a href="#">Low</a></li>
		               						<li class="divider"></li>
		               						<li><a href="#">Postponed</a></li>
		               					</ul>
		               				</div>
		               				<div class="btn-group">
		               					<a href="#" class="btn btn-mini dropdown-toggle" data-toggle="dropdown" ><span class="caret"></span></a>
		               					<ul class="dropdown-menu">
		               						<li><a href="#">Comments <span class="badge badge-error">5</span></a></li>
		               						<li><a href="#">Edit</a></li>
		               						<li class="divider"></li>
		               						<li><a href="#">Delete</a></li>
		               					</ul>
		               				</div>
	               				</div>
	               			</li>
	               			<li>
	               				<div class="btn-toolbar">
	               					<div class="btn-group">
	               						<i class="icon-move"></i> 
	               					</div>
	               					<div class="btn-group">
	               						<input type="checkbox" name="" value="" /> 
	               					</div>
	               					<div class="btn-group">
	               						<a href="#">Task Title</a> 
	               					</div>
	               					<div class="btn-group">
	               						<small>task description will be truncated at 40 char...</small> 
	               					</div>
	               					<div class="btn-group">
	               						<a href="#" class="btn btn-mini dropdown-toggle" data-toggle="dropdown" >Melinda Ledbetter <span class="caret"></span></a>
	               						<ul class="dropdown-menu">
	               							<li><a href="#">Kyle Ledbetter</a></li>
	               							<li><a href="#">Melinda Ledbetter</a></li>
	               							<li><a href="#">Tobias Kuhn</a></li>
	               							<li class="divider"></li>
	               							<li><a href="#">Unassigned</a></li>
	               						</ul>
	               					</div>
	               					<div class="btn-group">
	               						<a href="#" class="btn btn-warning btn-mini dropdown-toggle" data-toggle="dropdown" >MEDIUM <span class="caret"></span></a>
	               						<ul class="dropdown-menu">
	               							<li><a href="#">High</a></li>
	               							<li><a href="#">Medium</a></li>
	               							<li><a href="#">Low</a></li>
	               							<li class="divider"></li>
	               							<li><a href="#">Postponed</a></li>
	               						</ul>
	               					</div>
	               					<div class="btn-group">
	               						<a href="#" class="btn btn-mini dropdown-toggle" data-toggle="dropdown" ><span class="caret"></span></a>
	               						<ul class="dropdown-menu">
	               							<li><a href="#">Comments <span class="badge badge-error">5</span></a></li>
	               							<li><a href="#">Edit</a></li>
	               							<li class="divider"></li>
	               							<li><a href="#">Delete</a></li>
	               						</ul>
	               					</div>
	               				</div>
	               			</li>
	               			<li>
	               				<div class="btn-toolbar">
	               					<div class="btn-group">
	               						<i class="icon-move"></i> 
	               					</div>
	               					<div class="btn-group">
	               						<input type="checkbox" name="" value="" /> 
	               					</div>
	               					<div class="btn-group">
	               						<a href="#">Task Title</a> 
	               					</div>
	               					<div class="btn-group">
	               						<small>task description will be truncated at 40 char...</small> 
	               					</div>
	               					<div class="btn-group">
	               						<a href="#" class="btn btn-mini dropdown-toggle" data-toggle="dropdown" >Kyle Ledbetter <span class="caret"></span></a>
	               						<ul class="dropdown-menu">
	               							<li><a href="#">Kyle Ledbetter</a></li>
	               							<li><a href="#">Melinda Ledbetter</a></li>
	               							<li><a href="#">Tobias Kuhn</a></li>
	               							<li class="divider"></li>
	               							<li><a href="#">Unassigned</a></li>
	               						</ul>
	               					</div>
	               					<div class="btn-group">
	               						<a href="#" class="btn btn-info btn-mini dropdown-toggle" data-toggle="dropdown" >LOW <span class="caret"></span></a>
	               						<ul class="dropdown-menu">
	               							<li><a href="#">High</a></li>
	               							<li><a href="#">Medium</a></li>
	               							<li><a href="#">Low</a></li>
	               							<li class="divider"></li>
	               							<li><a href="#">Postponed</a></li>
	               						</ul>
	               					</div>
	               					<div class="btn-group">
	               						<a href="#" class="btn btn-mini dropdown-toggle" data-toggle="dropdown" ><span class="caret"></span></a>
	               						<ul class="dropdown-menu">
	               							<li><a href="#">Comments <span class="badge badge-error">5</span></a></li>
	               							<li><a href="#">Edit</a></li>
	               							<li class="divider"></li>
	               							<li><a href="#">Delete</a></li>
	               						</ul>
	               					</div>
	               				</div>
	               			</li>
	               			<li>
	               				<div class="btn-toolbar">
	               					<div class="btn-group">
	               						<i class="icon-move"></i> 
	               					</div>
	               					<div class="btn-group">
	               						<input type="checkbox" name="" value="" /> 
	               					</div>
	               					<div class="btn-group">
	               						<a href="#">Task Title</a> 
	               					</div>
	               					<div class="btn-group">
	               						<small>task description will be truncated at 40 char...</small> 
	               					</div>
	               					<div class="btn-group">
	               						<a href="#" class="btn btn-mini dropdown-toggle" data-toggle="dropdown" >Tobias Kuhn <span class="caret"></span></a>
	               						<ul class="dropdown-menu">
	               							<li><a href="#">Kyle Ledbetter</a></li>
	               							<li><a href="#">Melinda Ledbetter</a></li>
	               							<li><a href="#">Tobias Kuhn</a></li>
	               							<li class="divider"></li>
	               							<li><a href="#">Unassigned</a></li>
	               						</ul>
	               					</div>
	               					<div class="btn-group">
	               						<a href="#" class="btn btn-inverse btn-mini dropdown-toggle" data-toggle="dropdown" >POSTPONED <span class="caret"></span></a>
	               						<ul class="dropdown-menu">
	               							<li><a href="#">High</a></li>
	               							<li><a href="#">Medium</a></li>
	               							<li><a href="#">Low</a></li>
	               							<li class="divider"></li>
	               							<li><a href="#">Postponed</a></li>
	               						</ul>
	               					</div>
	               					<div class="btn-group">
	               						<a href="#" class="btn btn-mini dropdown-toggle" data-toggle="dropdown" ><span class="caret"></span></a>
	               						<ul class="dropdown-menu">
	               							<li><a href="#">Comments <span class="badge badge-error">5</span></a></li>
	               							<li><a href="#">Edit</a></li>
	               							<li class="divider"></li>
	               							<li><a href="#">Delete</a></li>
	               						</ul>
	               					</div>
	               				</div>
	               			</li>
	               			<li>
	               				<div class="btn-toolbar">
	               					<div class="btn-group">
	               						<i class="icon-move"></i> 
	               					</div>
	               					<div class="btn-group">
	               						<input type="checkbox" name="" value="" /> 
	               					</div>
	               					<div class="btn-group">
	               						<a href="#">Task Title</a> 
	               					</div>
	               					<div class="btn-group">
	               						<small>task description will be truncated at 40 char...</small> 
	               					</div>
	               					<div class="btn-group">
	               						<a href="#" class="btn btn-mini dropdown-toggle" data-toggle="dropdown" >Tobias Kuhn <span class="caret"></span></a>
	               						<ul class="dropdown-menu">
	               							<li><a href="#">Kyle Ledbetter</a></li>
	               							<li><a href="#">Melinda Ledbetter</a></li>
	               							<li><a href="#">Tobias Kuhn</a></li>
	               							<li class="divider"></li>
	               							<li><a href="#">Unassigned</a></li>
	               						</ul>
	               					</div>
	               					<div class="btn-group">
	               						<a href="#" class="btn btn-danger btn-mini dropdown-toggle" data-toggle="dropdown" >HIGH <span class="caret"></span></a>
	               						<ul class="dropdown-menu">
	               							<li><a href="#">High</a></li>
	               							<li><a href="#">Medium</a></li>
	               							<li><a href="#">Low</a></li>
	               							<li class="divider"></li>
	               							<li><a href="#">Postponed</a></li>
	               						</ul>
	               					</div>
	               					<div class="btn-group">
	               						<a href="#" class="btn btn-mini dropdown-toggle" data-toggle="dropdown" ><span class="caret"></span></a>
	               						<ul class="dropdown-menu">
	               							<li><a href="#">Comments <span class="badge badge-error">5</span></a></li>
	               							<li><a href="#">Edit</a></li>
	               							<li class="divider"></li>
	               							<li><a href="#">Delete</a></li>
	               						</ul>
	               					</div>
	               				</div>
	               			</li>
	               			<li>
	               				<a href="#collapseTask" data-toggle="collapse" class="btn btn-mini">New Task</a>
	               			</li>
	               		</ul>
	               	</div>
	               	 <hr />
                <?php
                $k = 1 - $k;
                endforeach;
                ?>
            </div>

            <input type="hidden" name="boxchecked" value="0" />
            <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
	        <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
            <input type="hidden" name="task" value="" />
	        <?php echo JHtml::_('form.token'); ?>
	    </form>
    </div>
</div>