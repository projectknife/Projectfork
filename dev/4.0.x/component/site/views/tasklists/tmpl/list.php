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
$user	    = JFactory::getUser();
$uid	    = $user->get('id');
$message    = addslashes(JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'));

$action_count = count($this->actions);
?>
<div id="projectfork" class="category-list<?php echo $this->pageclass_sfx;?> view-tasklists">

    <?php if ($this->params->get('show_page_heading', 1)) : ?>
    <div class="page-header">
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?> <a href="#collapseNew" data-toggle="collapse" class="btn btn-info">New</a></h1> 
	</div>
    <?php endif; ?>
    <?php //echo $this->toolbar;
    ?>
    <!-- Begin New Form -->
    <div id="collapseNew" class="collapse out">
	    <div class="well">
	    	<form method="post" action="" class="form-horizontal">
	    		<fieldset>
	    			<div class="control-group">
	    				<label for="" class="control-label">List Title</label>
	    				<div class="controls">
	    					<input type="text" name="" value="" /> 
	    					<select class="input-small">
	    						<option>Milestone</option>
	    					</select> 
	    					<select class="input-small">
	    						<option>Access</option>
	    					</select>
	    				</div>
	    			</div>
	    			<div class="control-group">
	    				<label for="" class="control-label">List Description</label>
	    				<div class="controls">
	    					<textarea cols="5" rows="5" class="span5"></textarea>
	    				</div>
	    			</div>
	    			<div class="control-group">
	    				<div class="controls">
		    				<button class="btn btn-primary">Save</button> 
		    				<a class="btn" href="#collapseNew" data-toggle="collapse">Cancel</a>
	    				</div>
	    			</div>
	    		</fieldset>
	    	</form>
	    </div>
    </div>
    <!-- End New Form -->
    <div class="task-lists">
        <form name="adminForm" id="adminForm" action="<?php echo JRoute::_('index.php?option=com_projectfork&view=tasklists'); ?>" method="post">

            <div class="filters btn-toolbar">
				<?php if($this->params->get('filter_field')) : ?>
                    <div class="filter-search btn-group">
    			        <label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
    			        <input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />
    			        <button type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
    			        <button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
    		        </div>
                <?php endif; ?>
			</div>

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
            
            <?php if($this->pagination->get('pages.total') > 1 && $this->params->get('show_pagination')) : ?>
            <div class="btn-toolbar">
            	<div class="pagination btn-group">
            	    <?php if ($this->params->get('show_pagination_results')) : ?>
            		    <p class="counter"><?php echo $this->pagination->getPagesCounter(); ?></p>
            		<?php endif; ?>
            	    <?php echo $this->pagination->getPagesLinks(); ?>
            	</div>
            	<?php if ($this->params->get('show_pagination_limit')) : ?>
            	    <div class="display-limit btn-group">
            	        <?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
            	        <?php echo $this->pagination->getLimitBox(); ?>
            	    </div>
            	<?php endif; ?>
			</div>
			<?php endif; ?>

            <input type="hidden" name="boxchecked" value="0" />
            <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
	        <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
            <input type="hidden" name="task" value="" />
	        <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
</div>