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

		<form id="adminForm" name="adminForm" method="post" action="<?php echo JRoute::_('index.php?option=com_projectfork&view=tasks'); ?>">

			<fieldset class="filters">
				<?php if($this->params->get('filter_fields')) : ?>
                    <?php if($this->state->get('filter.project')) : ?>
                        <span class="filter-milestone">
        						<select onchange="this.form.submit()" size="1" class="inputbox" name="filter_milestone" id="milestone">
        						    <option value=""><?php echo JText::_('JOPTION_SELECT_MILESTONE');?></option>
        				            <?php echo JHtml::_('select.options', $this->milestones, 'value', 'text', $this->state->get('filter.milestone'));?>
        					</select>
                            <span class="filter-tasklist">
                                <select name="filter_tasklist" class="inputbox" onchange="this.form.submit()">
                    				<option value=""><?php echo JText::_('JOPTION_SELECT_TASKLIST');?></option>
                    				<?php echo JHtml::_('select.options', $this->tasklists, 'value', 'text', $this->state->get('filter.tasklist'));?>
                    			</select>
                            </span>
        				</span>
        				<span class="filter-user">
        						<select onchange="this.form.submit()" size="1" class="inputbox" name="filter_assigned_id" id="filter_assigned_id">
        						    <option value=""><?php echo JText::_('JOPTION_SELECT_ASSIGNED_USER');?></option>
        				            <?php echo JHtml::_('select.options', $this->assigned, 'value', 'text', $this->state->get('filter.assigned_id'));?>
        					</select>
        				</span>
                        <span class="filter-author">
                            <select name="filter_author_id" class="inputbox" onchange="this.form.submit()">
                				<option value=""><?php echo JText::_('JOPTION_SELECT_AUTHOR');?></option>
                				<?php echo JHtml::_('select.options', $this->authors, 'value', 'text', $this->state->get('filter.author_id'));?>
                			</select>
                        </span>
                    <?php endif; ?>
    				<span class="filter-status">
    						<select onchange="this.form.submit()" size="1" class="inputbox" name="filter_published" id="filter_published">
    						    <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
    				            <?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
    					</select>
    				</span>
    				<span class="filter-priority">
    						<select onchange="this.form.submit()" size="1" class="inputbox" name="filter_priority" id="filter_priority">
    						<option selected="selected" value=""><?php echo JText::_('JOPTION_SELECT_PRIORITY');?></option>
    						<?php echo JHtml::_('select.options', JHtml::_('projectfork.priorityOptions'), 'value', 'text', $this->state->get('filter.priority'));?>
    					</select>
    				</span>
                    <span class="filter-project">
                        <?php echo JHtml::_('projectfork.filterProject');?>
                    </span>
                <?php endif; ?>
                <?php if ($this->params->get('show_pagination_limit')) : ?>
		            <span class="display-limit">
			            <?php echo $this->pagination->getLimitBox(); ?>
		            </span>
		        <?php endif; ?>
			</fieldset>



            <input type="hidden" name="boxchecked" value="0" />
            <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
	        <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
            <input type="hidden" name="task" value="" />
	        <?php echo JHtml::_('form.token'); ?>
	    </form>
    </div>
</div>