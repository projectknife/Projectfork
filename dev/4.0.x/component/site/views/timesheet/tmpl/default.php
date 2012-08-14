<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$user       = JFactory::getUser();
$uid        = $user->get('id');

$action_count = count($this->actions);
?>
<div id="projectfork" class="category-list<?php echo $this->pageclass_sfx;?> view-timesheet">

    <?php if ($this->params->get('show_page_heading', 1)) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>

    <div class="clearfix"></div>

    <div class="cat-items">

        <form name="adminForm" id="adminForm" action="<?php echo JRoute::_(ProjectforkHelperRoute::getTimesheetRoute()); ?>" method="post">
            <div class="btn-toolbar btn-toolbar-top">
                <div class="btn-group">
                        <?php echo $this->toolbar;?>
                </div>
                <div class="filter-project btn-group">
                    <?php echo JHtml::_('projectfork.filterProject');?>
                </div>
                <?php if ($uid) : ?>
                    <div class="btn-group">
                        <a data-toggle="collapse" data-target="#filters" class="btn"><i class="icon-list"></i> <?php echo JText::_('JSEARCH_FILTER_LABEL'); ?> <span class="caret"></span></a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="clearfix"> </div>
            <?php if ($uid) : ?>
                <div class="collapse" id="filters">
                    <div class="well btn-toolbar">
                        <div class="filter-search btn-group pull-left">
                            <input type="text" name="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />
                        </div>
                        <div class="filter-search-buttons btn-group pull-left">
                            <button type="submit" class="btn" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
                            <button type="button" class="btn" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
                        </div>
                        <?php if ($this->access->get('milestone.edit.state') || $this->access->get('milestone.edit')) : ?>
                            <div class="filter-published btn-group pull-left">
                                <select name="filter_published" class="inputbox input-medium" onchange="this.form.submit()">
                                    <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
                                    <?php echo JHtml::_('select.options', $this->states,
                                                        'value', 'text', $this->state->get('filter.published'),
                                                        true
                                                       );
                                    ?>
                                </select>
                            </div>
                        <?php endif; ?>
                        <?php if (intval($this->state->get('filter.project')) != 0 && count($this->authors)) : ?>
                            <div class="filter-author btn-group pull-left">
                                <select id="filter_author" name="filter_author" class="inputbox" onchange="this.form.submit()">
                                    <option value=""><?php echo JText::_('JOPTION_SELECT_AUTHOR');?></option>
                                    <?php echo JHtml::_('select.options', $this->authors,
                                                        'value', 'text', $this->state->get('filter.author'),
                                                        true
                                                       );
                                    ?>
                                </select>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <h3>Project Title <?php echo $this->escape($this->params->get('page_heading')); ?></h3>
            
            <div class="row-fluid">
            	<div class="span3">
            		<div class="thumbnail thumbnail-timesheet">
            			<h6>Total Hours</h6>
            			<h1>100.0</h1>
            			<h5>Estimated (124.0)</h5>
            		</div>
            	</div>
            	<div class="span6">
            		<div class="thumbnail thumbnail-timesheet">
            			<h6>Total Hours</h6>
            			<div class="row-fluid">
            				<div class="span6">
            					<div class="progress progress-success">
									<div class="bar" style="width: 60%;"></div>
								</div>
								<div class="progress">
									<div class="bar" style="width: 40%;"></div>
								</div>
            				</div>
            				<div class="span6">
            					<h2>60.0 <span class="label label-success">Billable</span></h2>
            					<h2>40.0 <span class="label label-info">Unbillable</span></h2>
            				</div>
            			</div>
            		</div>
            	</div>
            	<div class="span3">
            		<div class="thumbnail thumbnail-timesheet">
            			<h6>Billable Total</h6>
            			<h2>$2,000.00</h2>
            			<h5>Estimated (2,500.00)</h5>
            		</div>
            	</div>
            </div>
            <hr />
            <table class="table table-striped">
            	<thead>
            		<tr>
            			<th>Task</th>
            			<th width="5%"></th>
            			<th width="10%">Time</th>
            			<th width="20%"></th>
            			<th width="10%">Author</th>
            			<th width="10%">Date</th>
            			<th width="10%">Rate</th>
            			<th width="10%">Billable</th>
            		</tr>
            	</thead>
            	<tbody>
			        <?php
			        $k = 0;
			        foreach($this->items AS $i => $item) :
			            $access = ProjectforkHelperAccess::getActions('time', $item->id);
			
			            $can_create   = $access->get('time.create');
			            $can_edit     = $access->get('time.edit');
			            $can_change   = $access->get('time.edit.state');
			            $can_edit_own = ($access->get('time.edit.own') && $item->created_by == $uid);
			
			            /**
			             * Available data:
			             *
			             * $item->id;
			             * $item->project_title
			             * $item->task_title
			             * $item->description
			             * $item->author_name
			             * $item->access_level
			             * $item->log_date
			             * JHtml::_('date', $item->log_date, JText::_('DATE_FORMAT_LC4'))
			             * JHtml::_('timesheet.format', $item->log_time)
			             */
			        ?>
			        <tr>
			        	<td>
			        		<a href="#" rel="popover" title="<?php echo $item->task_title; ?>" data-content="<?php echo $item->description; ?>"><?php echo $item->task_title; ?></a>
			        	</td>
			        	<td>
			        		<?php
                            $this->menu->start(array('class' => 'btn-mini'));
                            $this->menu->itemEdit('timeform', $item->id, ($can_edit || $can_edit_own));
                            $this->menu->itemTrash('time', $i, $can_change);
                            $this->menu->end();

                            echo $this->menu->render(array('class' => 'btn-mini'));
	                        ?>
			        	</td>
			        	<td>
			        		<?php echo JHtml::_('timesheet.format', $item->log_time); ?>
			        	</td>
			        	<td>
							<div class="progress">
								<div class="bar" style="width: 60%;"></div>
							</div>
			        	</td>
			        	<td>
			        		<?php echo $item->author_name; ?>
			        	</td>
			        	<td>
			        		<?php echo JHtml::_('date', $item->log_date, JText::_('DATE_FORMAT_LC4')); ?>
			        	</td>
			        	<td>
			        		$100.00
			        	</td>
			        	<td>
			        		$200.00
			        	</td>
			        </tr>
			
			        <?php
			        $k = 1 - $k;
			        endforeach;
			        ?>
            	</tbody>
            	<tfoot>
            		<tr>
            			<th>Totals</th>
            			<th></th>
            			<th>100.00</th>
            			<th></th>
            			<th></th>
            			<th>
	            		</th>
	            		<th></th>
	            		<th>$2,000.00</th>
            		</tr>
            	</tfoot>
            </table>

            <div class="filters btn-toolbar">
                <?php if ($this->pagination->get('pages.total') > 1) : ?>
                    <div class="pagination">
                        <p class="counter"><?php echo $this->pagination->getPagesCounter(); ?></p>
                        <?php echo $this->pagination->getPagesLinks(); ?>
                    </div>
                <?php endif; ?>
                <div class="btn-group display-limit">
                    <?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
                    <?php echo $this->pagination->getLimitBox(); ?>
                </div>
            </div>

            <input type="hidden" name="boxchecked" value="0" />
            <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
            <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
            <input type="hidden" name="task" value="" />
            <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
</div>
