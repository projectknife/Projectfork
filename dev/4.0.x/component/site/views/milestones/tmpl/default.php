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
<div id="projectfork" class="category-list<?php echo $this->pageclass_sfx;?> view-milestones">

    <?php if ($this->params->get('show_page_heading', 1)) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>


    <?php echo $this->toolbar;?>


    <div class="cat-items">

        <form name="adminForm" id="adminForm" action="<?php echo JRoute::_('index.php?option=com_projectfork&view=milestones'); ?>" method="post">

            <fieldset class="filters">
            	<?php if($action_count) : ?>
                    <div class="display-bulk-actions">
                        <select onchange="if(document.adminForm.boxchecked.value==0 & this.selectedIndex > 0){alert('<?php echo $message;?>');}
                                          else{Joomla.submitbutton(this.options[this.selectedIndex].value)}"
                                size="1" class="inputbox" name="bulk" id="bulk"
                        >
            		        <option value=""><?php echo JText::_('COM_PROJECTFORK_BULK_ACTIONS');?></option>
                            <?php echo JHtml::_('select.options', $this->actions);?>
            	        </select>
            	    </div>
                <?php endif;?>
				<?php if($this->params->get('filter_field')) : ?>
                    <div class="filter-search">
    			        <label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
    			        <input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />
    			        <button type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
    			        <button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
    		        </div>
                <?php endif; ?>
                <?php if($this->params->get('filter_state') && $this->user->get('id')) : ?>
    				<div class="display-published">
    				    <select name="filter_published" class="inputbox" onchange="this.form.submit()">
    				        <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
    				        <?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'),
                                                'value', 'text', $this->state->get('filter.published'),
                                                true
                                               );
                            ?>
    				    </select>
    				</div>
                <?php endif; ?>
				<?php if ($this->params->get('show_pagination_limit')) : ?>
		            <div class="display-limit">
			            <?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
			            <?php echo $this->pagination->getLimitBox(); ?>
		            </div>
		        <?php endif; ?>
			</fieldset>

            <table class="category table table-striped">
                <thead>
	                <tr>
	               		<?php if($action_count) : ?>
	               	    <th id="tableOrdering0" class="list-select">
	               			<input type="checkbox" onclick="checkAll(<?php echo count($this->items);?>);" value="" name="toggle" />
	               		</th>
                        <?php endif; ?>
	               		<th id="tableOrdering1" class="list-title">
	               		    <?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $list_dir, $list_order); ?>
                        </th>
	               		<th id="tableOrdering2" class="list-actions span1">
	               			&nbsp;
	               		</th>
                        <?php if($this->params->get('milestone_list_col_project')) : ?>
	               		<th id="tableOrdering3" class="list-project">
                            <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_PROJECT', 'project_title', $list_dir, $list_order); ?>
                        </th>
                        <?php endif; ?>
                        <?php if($this->params->get('milestone_list_col_tasklists')) : ?>
	               		<th id="tableOrdering4" class="list-tasklists">
                            <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_TASKLISTS', 'tasklists', $list_dir, $list_order); ?>
                        </th>
                        <?php endif; ?>
                        <?php if($this->params->get('milestone_list_col_tasks')) : ?>
	               		<th id="tableOrdering5" class="list-tasks">
                            <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_TASKS', 'tasks', $list_dir, $list_order); ?>
                        </th>
                        <?php endif; ?>
                        <?php if($this->params->get('milestone_list_col_author')) : ?>
                        <th id="tableOrdering6" class="list-author" nowrap="nowrap">
	               		    <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_CREATED_BY', 'author_name', $list_dir, $list_order); ?>
                        </th>
                        <?php endif; ?>
	               		<?php if($this->params->get('milestone_list_col_sdate')) : ?>
	               		<th id="tableOrdering7" class="list-sdate" nowrap="nowrap">
                            <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_START_DATE', 'a.start_date', $list_dir, $list_order); ?>
                        </th>
                        <?php endif; ?>
                        <?php if($this->params->get('milestone_list_col_created')) : ?>
                        <th id="tableOrdering8" class="list-created" nowrap="nowrap">
                            <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_CREATED_ON', 'a.created', $list_dir, $list_order); ?>
                        </th>
                        <?php endif;?>
                        <?php if($this->params->get('milestone_list_col_deadline')) : ?>
	               		<th id="tableOrdering9" class="list-deadline">
                            <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_DEADLINE', 'a.end_date', $list_dir, $list_order); ?>
                        </th>
                        <?php endif; ?>
                        <?php if($this->params->get('milestone_list_col_access')) : ?>
	               		<th id="tableOrdering10" class="list-access">
                            <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ACCESS', 'access_level', $list_dir, $list_order); ?>
                        </th>
                        <?php endif; ?>
	               	</tr>
               </thead>
               <tbody>
                   <?php
                    $k = 0;
                    foreach($this->items AS $i => $item) :
                        $asset_name = 'com_projectfork.milestone.'.$item->id;

			            $canCreate	= ($user->authorise('core.create', $asset_name) || $user->authorise('milestone.create', $asset_name));
			            $canEdit	= ($user->authorise('core.edit', $asset_name) || $user->authorise('milestone.edit', $asset_name));
			            $canCheckin	= ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
			            $canEditOwn	= (($user->authorise('core.edit.own', $asset_name) || $user->authorise('milestone.edit.own', $asset_name)) && $item->created_by == $uid);
			            $canChange	= (($user->authorise('core.edit.state',	$asset_name) || $user->authorise('milestone.edit.state', $asset_name)) && $canCheckin);
                    ?>
                        <tr class="cat-list-row<?php echo $k;?>">
    	               		<?php if($action_count) : ?>
                               <td class="list-select">
                                    <?php echo JHtml::_('grid.id', $i, $item->id); ?>
    	               		    </td>
                            <?php endif; ?>
    	               		<td class="list-title">
    	               		    <?php if ($item->checked_out) : ?>
            						<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'milestones.', $canCheckin); ?>
            					<?php endif; ?>
                                <a href="<?php echo JRoute::_('index.php?option=com_projectfork&view=milestone&id='.intval($item->id).':'.$item->alias);?>">
                                    <?php echo $this->escape($item->title);?>
                                </a>
                                <div class="clr"></div>
                                <span class="small"><?php echo $this->escape($item->description);?></span>
    	               		</td>
    	               		<td class="list-actions">
    	               			<div class="btn-group">
    	               			    <a class="btn dropdown-toggle" data-toggle="dropdown" href="#"><span class="caret"></span></a>
    	               			    <ul class="dropdown-menu">
    	               			        <?php if($canEdit || $canEditOwn) : ?>
                                        <li>
                                           <a href="<?php echo JRoute::_('index.php?option=com_projectfork&task=milestoneform.edit&id='.intval($item->id).':'.$item->alias);?>">
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
    	               		</td>
                            <?php if($this->params->get('milestone_list_col_project')) : ?>
        	               		<td class="list-project">
        		               		<a class="btn"><i class="icon-map-marker"></i> <?php echo $this->escape($item->project_title);?></a>
        	               		</td>
                            <?php endif; ?>
                            <?php if($this->params->get('milestone_list_col_tasklists')) : ?>
        	               		<td class="list-tasks">
        		               		<a class="btn"><i class="icon-ok"></i> <?php echo (int) $item->tasklists;?></a>
        	               		</td>
                            <?php endif; ?>
                            <?php if($this->params->get('milestone_list_col_tasks')) : ?>
        	               		<td class="list-tasks">
        		               		<a class="btn"><i class="icon-ok"></i> <?php echo (int) $item->tasks;?></a>
        	               		</td>
                            <?php endif; ?>
                            <?php if($this->params->get('milestone_list_col_author')) : ?>
        	               		<td class="list-author">
        	               			<small><?php echo $this->escape($item->author_name);?></small>
        	               		</td>
                            <?php endif; ?>
                            <?php if($this->params->get('milestone_list_col_created')) : ?>
    	               		    <td class="list-created">
        		               	    <?php echo JHtml::_('date', $item->created, $this->escape( $this->params->get('date_format', JText::_('DATE_FORMAT_LC4')))); ?>
        	               		</td>
                            <?php endif; ?>
                            <?php if($this->params->get('milestone_list_col_sdate')) : ?>
    	               		    <td class="list-sdate">
        		               	    <?php if($item->start_date == $this->nulldate) {
                                        echo JText::_('COM_PROJECTFORK_DATE_NOT_SET');
                                    }
                                    else {
                                        echo JHtml::_('date', $item->start_date, $this->escape( $this->params->get('sdate_format', JText::_('DATE_FORMAT_LC4'))));
                                    }
        		               		?>
        	               		</td>
                            <?php endif; ?>
                            <?php if($this->params->get('milestone_list_col_deadline')) : ?>
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
                            <?php if($this->params->get('milestone_list_col_access')) : ?>
    	               		    <td class="list-access">
        		               		<?php echo $this->escape($item->access_level);?>
        	               		</td>
                            <?php endif; ?>
    	               	</tr>
                    <?php
                    $k = 1 - $k;
                    endforeach;
                    ?>
                </tbody>
            </table>

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
	        <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
</div>