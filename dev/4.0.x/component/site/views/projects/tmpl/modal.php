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


$function	= JRequest::getCmd('function', 'pfSelectActiveProject');
$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$user	    = JFactory::getUser();
$uid	    = $user->get('id');
?>
<div id="projectfork" class="category-list<?php echo $this->pageclass_sfx;?> view-projects">

    <div class="cat-items">

        <form name="adminForm" id="adminForm" action="<?php echo JRoute::_('index.php?option=com_projectfork&view=projects&layout=modal&tmpl=component&function='.$function);?>" method="post">

            <fieldset class="filters">
                <?php if($this->params->get('filter_fields')) : ?>
                    <span class="filter-search">
    			        <input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />
    			        <button type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
    			        <button type="button" class="btn" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
    		        </span>
                    <?php if ($this->user->authorise('core.edit.state', 'com_projectfork') || $this->user->authorize('project.edit.state', 'com_projectfork')
                          ||  $this->user->authorise('core.edit', 'com_projectfork') || $this->user->authorize('project.edit', 'com_projectfork')) : ?>
        				<span class="filter-published">
        				    <select id="filter_published" name="filter_published" class="inputbox" onchange="this.form.submit()">
        				        <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
        				        <?php echo JHtml::_('select.options', $this->states,
                                                    'value', 'text', $this->state->get('filter.published'),
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

            <table class="category table table-striped">
                <thead>
	                <tr>
	               		<th id="tableOrdering0" class="list-title">
                            <?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $list_dir, $list_order); ?>
                        </th>
                        <?php if($this->params->get('project_list_col_milestones')) : ?>
	               		<th id="tableOrdering1" class="list-milestones">
                            <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_MILESTONES', 'milestones', $list_dir, $list_order); ?>
                        </th>
                        <?php endif; ?>
                        <?php if($this->params->get('project_list_col_tasks')) : ?>
	               		<th id="tableOrdering2" class="list-tasks">
                            <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_TASKLISTS_AND_TASKS', 'tasks', $list_dir, $list_order); ?>
                        </th>
                        <?php endif; ?>
                        <?php if($this->params->get('project_list_col_deadline')) : ?>
	               		<th id="tableOrdering3" class="list-deadline">
                            <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_DEADLINE', 'a.end_date', $list_dir, $list_order); ?>
                        </th>
                        <?php endif; ?>
	               	</tr>
               </thead>
               <tbody>
                    <?php
                    $k = 0;
                    foreach($this->items AS $i => $item) :
                    ?>
                        <tr class="cat-list-row<?php echo $k;?>">
    	               		<td class="list-title">
                                <a class="pointer" style="cursor: pointer;" onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>('<?php echo $item->id; ?>', '<?php echo $this->escape(addslashes($item->title)); ?>');">
                                    <?php echo $this->escape($item->title);?>
                                </a>
    	               		</td>
                            <?php if($this->params->get('project_list_col_milestones')) : ?>
        	               		<td class="list-milestones">
                                    <i class="icon-map-marker"></i> <?php echo (int) $item->milestones;?>
        	               		</td>
                            <?php endif; ?>
                            <?php if($this->params->get('project_list_col_tasks')) : ?>
        	               		<td class="list-tasks">
                                    <i class="icon-ok"></i> <?php echo intval($item->tasklists).' / '.intval($item->tasks);?>
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

            <?php if($this->pagination->get('pages.total') > 1 && $this->params->get('show_pagination')) : ?>
                <div class="pagination">
                    <?php if ($this->params->get('show_pagination_results')) : ?>
    				    <p class="counter"><?php echo $this->pagination->getPagesCounter(); ?></p>
    				<?php endif; ?>
    		        <?php echo $this->pagination->getPagesLinks(); ?>
                </div>
            <?php endif; ?>

            <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
	        <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
            <input type="hidden" name="task" value="" />
	        <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
</div>