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


JHtml::_('behavior.multiselect');


$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$user	    = JFactory::getUser();
$uid	    = $user->get('id');
$message    = addslashes(JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'));

$action_count = count($this->actions);
?>
<div id="projectfork" class="category-list<?php echo $this->pageclass_sfx;?> view-projects">

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

    <div class="grid">
        <form name="adminForm" id="adminForm" action="<?php echo JRoute::_('index.php?option=com_projectfork&view=projects'); ?>" method="post">
            <div class="filters btn-toolbar">
            	<?php if($this->params->get('filter_field')) : ?>
            	    <div class="btn-group filter-search">
            	        <label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
            	        <input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />
            	        <button type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
            	        <button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
            	    </div>
            	<?php endif; ?>
			</div>
			
			<ul class="thumbnails">
                    <?php
                    $k = 0;
                    foreach($this->items AS $i => $item) :
                        $asset_name = 'com_projectfork.project.'.$item->id;

			            $canCreate	= ($user->authorise('core.create', $asset_name) || $user->authorise('project.create', $asset_name));
			            $canEdit	= ($user->authorise('core.edit', $asset_name) || $user->authorise('project.edit', $asset_name));
			            $canCheckin	= ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
			            $canEditOwn	= (($user->authorise('core.edit.own', $asset_name) || $user->authorise('project.edit.own', $asset_name)) && $item->created_by == $uid);
			            $canChange	= (($user->authorise('core.edit.state',	$asset_name) || $user->authorise('project.edit.state', $asset_name)) && $canCheckin);
                    ?>
                    
                        <li class="span3">
                          <div class="thumbnail">
                            <a href="<?php echo JRoute::_('index.php?option=com_projectfork&view=dashboard&id='.intval($item->id).':'.$item->alias);?>">
                            	<img src="http://placehold.it/260x180" alt="">
                            </a>
                            <div class="caption">
                              <h5>
                              	<?php if ($item->checked_out) : ?>
                              	<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'projects.', $canCheckin); ?>
	                              <?php endif; ?>
	                              <a href="<?php echo JRoute::_('index.php?option=com_projectfork&view=dashboard&id='.intval($item->id).':'.$item->alias);?>">
	                                  <?php echo $this->escape($item->title);?>
	                              </a>
	                              <?php if($canEdit || $canEditOwn) : ?>
	                                 <a class="btn btn-mini" href="<?php echo JRoute::_('index.php?option=com_projectfork&task=projectform.edit&id='.intval($item->id).':'.$item->alias);?>">
	                                     <?php echo JText::_('COM_PROJECTFORK_ACTION_EDIT');?>
	                                 </a>
	                              <?php endif; ?>
                              </h5>
                              <p>
                              	<?php echo $this->escape($item->description);?>
                              </p>
                              <div class="btn-group">
                              	<?php if($this->params->get('project_list_col_milestones')) : ?>
                              	   		<a class="btn" href="<?php echo JRoute::_(ProjectforkHelperRoute::getMilestonesRoute($item->id.':'.$item->alias));?>" rel="tooltip" data-placement="bottom" title="<?php echo JText::_('JGRID_HEADING_MILESTONES');?>"><i class="icon-map-marker"></i> <?php echo (int) $item->milestones;?></a>
                              	<?php endif; ?>
                              	<?php if($this->params->get('project_list_col_tasklists')) : ?>
                              	   		<a class="btn" href="<?php echo JRoute::_(ProjectforkHelperRoute::getTasksRoute($item->id.':'.$item->alias));?>" rel="tooltip" data-placement="bottom" title="<?php echo JText::_('JGRID_HEADING_TASKLISTS');?>"><i class="icon-th-list"></i> <?php echo (int) $item->tasklists;?></a>
                              	<?php endif; ?>
                              	<?php if($this->params->get('project_list_col_tasks')) : ?>
                              	   		<a class="btn" href="<?php echo JRoute::_(ProjectforkHelperRoute::getTasksRoute($item->id.':'.$item->alias));?>" rel="tooltip" data-placement="bottom" title="<?php echo JText::_('JGRID_HEADING_TASKS');?>"><i class="icon-ok"></i> <?php echo (int) $item->tasks;?></a>
                              	<?php endif; ?>
                              </div>
                            </div>
                          </div>
                        </li>
                    <?php
                    $k = 1 - $k;
                    endforeach;
                    ?>
			</ul>
			
			<div class="filters btn-toolbar">
				<?php if($this->pagination->get('pages.total') > 1 && $this->params->get('show_pagination')) : ?>
				    <div class="btn-group pagination">
				        <?php if ($this->params->get('show_pagination_results')) : ?>
						    <p class="counter"><?php echo $this->pagination->getPagesCounter(); ?></p>
						<?php endif; ?>
				        <?php echo $this->pagination->getPagesLinks(); ?>
				    </div>
				<?php endif; ?>
				<?php if ($this->params->get('show_pagination_limit')) : ?>
			        <div class="btn-group display-limit">
			            <?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
			            <?php echo $this->pagination->getLimitBox(); ?>
			        </div>
			    <?php endif; ?>
			</div>

            <input type="hidden" name="boxchecked" value="0" />
            <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
	        <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
            <input type="hidden" name="task" value="" />
	        <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
</div>