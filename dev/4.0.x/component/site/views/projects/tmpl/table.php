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

$state      = $this->state;
$list_order = $this->escape($state->get('list.ordering'));
$list_dir   = $this->escape($state->get('list.direction'));
$user	    = JFactory::getUser();
$uid	    = $user->get('id');

$action_count = count($this->actions);
?>
<div id="projectfork" class="category-list<?php echo $this->pageclass_sfx;?> view-projects">

    <?php if ($this->params->get('show_page_heading', 1)) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>

    <?php echo $this->toolbar;?>


    <div class="cat-items">

        <form name="adminForm" id="adminForm" action="<?php echo htmlspecialchars(JFactory::getURI()->toString()); ?>" method="post">

            <fieldset class="filters">
                <?php if($this->params->get('filter_fields')) : ?>
                    <span class="filter-search">
    			        <input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($state->get('filter.search')); ?>" />
    			        <button type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
    			        <button type="button" class="btn" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
    		        </span>
                    <?php if ($user->authorise('core.edit.state', 'com_projectfork') || $user->authorise('project.edit.state', 'com_projectfork')
                          ||  $user->authorise('core.edit', 'com_projectfork') || $user->authorise('project.edit', 'com_projectfork')) : ?>
        				<span class="filter-published">
        				    <select id="filter_published" name="filter_published" class="inputbox" onchange="this.form.submit()">
        				        <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
        				        <?php echo JHtml::_('select.options', $this->states,
                                                    'value', 'text', $state->get('filter.published'),
                                                    true
                                                   );
                                ?>
        				    </select>
        				</span>
                    <?php endif; ?>
                    <?php if($user->authorise('core.admin') && count($this->authors)) : ?>
                        <span class="filter-author">
                            <select id="filter_author" name="filter_author" class="inputbox" onchange="this.form.submit()">
        				        <option value=""><?php echo JText::_('JOPTION_SELECT_AUTHOR');?></option>
        				        <?php echo JHtml::_('select.options', $this->authors,
                                                    'value', 'text', $this->state->get('filter.author'),
                                                    true
                                                   );
                                ?>
        				    </select>
                        </span>
                    <?php endif; ?>
                <?php endif; ?>
				<?php if ($this->params->get('show_pagination_limit')) : ?>
		            <span class="filter-limit">
			            <?php echo $this->pagination->getLimitBox(); ?>
		            </span>
		        <?php endif; ?>
			</fieldset>

            <?php if(count($this->items)) : ?>
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
                            <?php if($this->params->get('project_list_col_milestones')) : ?>
    	               		<th id="tableOrdering3" class="list-milestones">
                                <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_MILESTONES', 'milestones', $list_dir, $list_order); ?>
                            </th>
                            <?php endif; ?>
                            <?php if($this->params->get('project_list_col_tasks')) : ?>
    	               		<th id="tableOrdering4" class="list-tasks">
                                <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_TASKLISTS_AND_TASKS', 'tasks', $list_dir, $list_order); ?>
                            </th>
                            <?php endif; ?>
                            <?php if($this->params->get('project_list_col_deadline')) : ?>
    	               		<th id="tableOrdering5" class="list-deadline">
                                <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_DEADLINE', 'a.end_date', $list_dir, $list_order); ?>
                            </th>
                            <?php endif; ?>
    	               	</tr>
                   </thead>
                   <tbody>
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
                            <tr class="cat-list-row<?php echo $k;?>">
        	               		<?php if($action_count) : ?>
                                   <td class="list-select">
                                        <?php echo JHtml::_('grid.id', $i, $item->id); ?>
        	               		    </td>
                                <?php endif; ?>
                                <td class="list-actions">
                                    <?php
                                        $this->menu->start();
                                        $this->menu->itemEdit('projectform', $item->id, ($canEdit || $canEditOwn));
                                        $this->menu->itemTrash('projects', $i, ($canEdit || $canEditOwn));
                                        $this->menu->end();

                                        echo $this->menu->render();
                                    ?>
        	               		</td>
        	               		<td class="list-title">
                                    <a href="<?php echo JRoute::_(ProjectforkHelperRoute::getDashboardRoute($item->slug)); ?>">
                                        <?php if ($item->checked_out) : ?><i class="icon-lock"></i> <?php endif; ?>
                                        <?php echo $this->escape($item->title);?>
                                    </a>
        	               		</td>
                                <?php if($this->params->get('project_list_col_milestones')) : ?>
            	               		<td class="list-milestones">
            		               		<a class="btn" href="<?php echo JRoute::_(ProjectforkHelperRoute::getMilestonesRoute($item->slug));?>">
                                           <i class="icon-map-marker"></i> <?php echo (int) $item->milestones;?>
                                        </a>
            	               		</td>
                                <?php endif; ?>
                                <?php if($this->params->get('project_list_col_tasks')) : ?>
            	               		<td class="list-tasks">
            		               		<a class="btn" href="<?php echo JRoute::_(ProjectforkHelperRoute::getTasksRoute($item->slug));?>">
                                           <i class="icon-ok"></i> <?php echo intval($item->tasklists).' / '.intval($item->tasks);?>
                                        </a>
            	               		</td>
                                <?php endif; ?>
                                <?php if($this->params->get('project_list_col_deadline')) : ?>
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