<?php
/**
 * @package      Projectfork
 * @subpackage   Timetracking
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


JHtml::_('pfhtml.script.listform');

$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$user       = JFactory::getUser();
$uid        = $user->get('id');

$list_total_time = 0;
$list_total_billable = 0.00;

// Calculate un/billable time percentage
$billable_percent   = ($this->total_time == 0) ? 0 : round($this->total_time_billable * (100 / $this->total_time));
$unbillable_percent = ($this->total_time == 0) ? 0 : round($this->total_time_unbillable * (100 / $this->total_time));

$filter_in = ($this->state->get('filter.isset') ? 'in ' : '');
?>
<script type="text/javascript">
Joomla.submitbutton = function(task)
{
    if (task == 'recorder') {
        var win_attr = 'width=500,height=600,resizable=yes,'
                     + 'scrollbars=yes,toolbar=no,location=no,'
                     + 'directories=no,status=no,menubar=no';

        window.open('<?php echo JRoute::_('index.php?option=com_pftime&view=recorder&tmpl=component'); ?>', 'winPFtimerec', win_attr);
    }
    else {
        Joomla.submitform(task, document.getElementById('adminForm'));
    }
}
</script>
<div id="projectfork" class="category-list<?php echo $this->pageclass_sfx;?> view-timesheet">

    <?php if ($this->params->get('show_page_heading', 1)) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>

    <div class="clearfix"></div>

    <div class="cat-items">

        <form name="adminForm" id="adminForm" action="<?php echo JRoute::_(PFtimeHelperRoute::getTimesheetRoute()); ?>" method="post">
            <div class="btn-toolbar btn-toolbar-top">
                <?php echo $this->toolbar; ?>
                <div class="filter-project btn-group">
                    <?php echo JHtml::_('pfhtml.project.filter');?>
                </div>
            </div>

            <div class="<?php echo $filter_in;?>collapse" id="filters">
                <div class="btn-toolbar clearfix">
                    <div class="filter-search btn-group pull-left">
                        <input type="text" name="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />
                    </div>
                    <div class="filter-search-buttons btn-group pull-left">
                        <button type="submit" class="btn" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
                        <button type="button" class="btn" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
                    </div>
                    <?php if ($this->access->get('time.edit.state') || $this->access->get('time.edit')) : ?>
                        <div class="filter-published btn-group pull-left">
                            <select name="filter_published" class="inputbox input-small" onchange="this.form.submit()">
                                <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
                                <?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <?php if (intval($this->state->get('filter.project')) > 0) : ?>
                        <div class="filter-author btn-group pull-left">
                            <select id="filter_author" name="filter_author" class="inputbox input-small" onchange="this.form.submit()">
                                <option value=""><?php echo JText::_('JOPTION_SELECT_AUTHOR');?></option>
                                <?php echo JHtml::_('select.options', $this->authors, 'value', 'text', $this->state->get('filter.author'), true);?>
                            </select>
                        </div>
                        <div class="filter-task btn-group pull-left">
                            <select id="filter_task" name="filter_task" class="inputbox input-small" onchange="this.form.submit()">
                                <option value=""><?php echo JText::_('COM_PROJECTFORK_OPTION_SELECT_TASK');?></option>
                                <?php echo JHtml::_('select.options', $this->tasks, 'value', 'text', $this->state->get('filter.task'), true);?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <div class="btn-group filter-order pull-left">
                        <select name="filter_order" class="inputbox input-small" onchange="this.form.submit()">
                            <?php echo JHtml::_('select.options', $this->sort_options, 'value', 'text', $list_order, true);?>
                        </select>
                    </div>
                    <div class="btn-group folder-order-dir pull-left">
                        <select name="filter_order_Dir" class="inputbox input-small" onchange="this.form.submit()">
                            <?php echo JHtml::_('select.options', $this->order_options, 'value', 'text', $list_dir, true);?>
                        </select>
                    </div>
                </div>
            </div>

            <?php if (intval($this->state->get('filter.project')) > 0) : ?>
                <div class="row-fluid row-hours">
                	<div class="span3">
                		<fieldset>
                			<legend><?php echo JText::_('COM_PROJECTFORK_TIME_TRACKING_TOTAL_HOURS');?></legend>
                			<p>
                				<span class="label label-info">
                					<?php echo JHtml::_('time.format', $this->total_time, 'decimal');?> <?php echo JText::_('COM_PROJECTFORK_TIME_TRACKING_TOTAL_HOURS');?>
                				</span>
                			</p>
                			<div>
                				<?php echo JText::_('COM_PROJECTFORK_TIME_TRACKING_ESTIMATED');?> (<?php echo JHtml::_('time.format', $this->total_estimated_time, 'decimal');?>)
                			</div>
                		</fieldset>
                	</div>
                	<div class="span6">
            			<fieldset>
            				<legend><?php echo JText::_('COM_PROJECTFORK_TIME_TRACKING_TOTAL_HOURS');?>: <?php echo PFApplicationHelper::getActiveProjectTitle();?></legend>
        					<div class="progress progress-success">
								<div class="bar" style="width: <?php echo $billable_percent;?>%;">
									<?php if ($billable_percent) : ?>
										<?php echo JHtml::_('time.format', $this->total_time_billable, 'decimal');?>
										<?php echo JText::_('COM_PROJECTFORK_TIME_TRACKING_BILLABLE');?>
									<?php endif; ?>
								</div>
							</div>
							<div class="progress progress-info">
								<div class="bar" style="width: <?php echo $unbillable_percent;?>%;">
									<?php if ($unbillable_percent) : ?>
										<?php echo JHtml::_('time.format', $this->total_time_unbillable, 'decimal');?>
										<?php echo JText::_('COM_PROJECTFORK_TIME_TRACKING_UNBILLABLE');?>
									<?php endif; ?>
								</div>
							</div>
            			</fieldset>
                	</div>
                	<div class="span3">
                		<fieldset>
                			<legend><?php echo JText::_('COM_PROJECTFORK_TIME_TRACKING_BILLABLE_TOTAL');?></legend>
                			<p>
                				<span class="label label-success">
                					<?php echo JHtml::_('pfhtml.format.money', $this->total_billable);?> <?php echo JText::_('COM_PROJECTFORK_TIME_TRACKING_BILLABLE_TOTAL');?>
                				</span>
                			</p>
                			<div>
                				<?php echo JText::_('COM_PROJECTFORK_TIME_TRACKING_ESTIMATED');?> (<?php echo JHtml::_('pfhtml.format.money', $this->total_estimated_cost);?>)
                			</div>
                		</fieldset>
                	</div>
                </div>
            <?php endif; ?>

            <hr />

            <table class="table table-striped table-condensed">
            	<thead>
            		<tr>
            			<th width="1%" class="hidden-phone"></th>
                        <th><?php echo JText::_('JGRID_HEADING_TASK');?></th>
                        <th width="1%"></th>
            			<th width="10%"><?php echo JText::_('COM_PROJECTFORK_TIME_TRACKING_TIME');?></th>
            			<th width="10%" class="hidden-phone"></th>
            			<th width="10%" class="hidden-phone"><?php echo JText::_('JGRID_HEADING_AUTHOR');?></th>
            			<th width="10%" class="hidden-phone"><?php echo JText::_('JGRID_HEADING_DATE');?></th>
            			<th width="10%" class="hidden-phone"><?php echo JText::_('COM_PROJECTFORK_TIME_TRACKING_RATE');?></th>
            			<th width="10%" class="hidden-phone"><?php echo JText::_('COM_PROJECTFORK_TIME_TRACKING_BILLABLE');?></th>
            		</tr>
            	</thead>
            	<tbody>
			        <?php
			        $k = 0;
			        foreach($this->items AS $i => $item) :
			            $access = PFtimeHelper::getActions($item->id);

			            $can_create   = $access->get('core.create');
			            $can_edit     = $access->get('core.edit');
			            $can_change   = $access->get('core.edit.state');
			            $can_edit_own = ($access->get('core.edit.own') && $item->created_by == $uid);

                        if ($item->log_time > 0) {
                            $list_total_time += (int) $item->log_time;
                        }

                        if ((float) $item->billable_total > 0.00) {
                            $list_total_billable += (float) $item->billable_total;
                        }

                        $percentage = ($item->estimate == 0) ? 0 : round($item->log_time * (100 / $item->estimate));
                        $percentage_class = 'progress';

                        if ($percentage > 100) {
                            $percentage = 100;
                            $percentage_class .= ' progress-info';
                        }
                        else {
                            $percentage_class .= ($item->billable == 1) ? ' progress-success' : '';
                        }

                        $exists = ((int) $item->task_exists > 0);
			        ?>
			        <tr>
                        <td class="hidden-phone">
                            <?php echo JHtml::_('pf.html.id', $i, $item->id); ?>
			        	</td>
			        	<td>
                            <?php if ($exists) : ?>
                                <a href="<?php echo JRoute::_(PFtasksHelperRoute::getTaskRoute($item->task_slug, $item->project_slug, $item->milestone_slug, $item->list_slug));?>"
                                    rel="popover"
                                    title="<?php echo $this->escape($item->task_title); ?>"
                                    data-content="<?php echo $this->escape($item->description); ?>"
                                >
                                    <?php echo $this->escape($item->task_title); ?>
                                </a>
                            <?php else : ?>
                                <?php echo $this->escape($item->task_title); ?>
                            <?php endif; ?>
			        	</td>
			        	<td>
			        		<?php
	                        $this->menu->start(array('class' => 'btn-mini'));
	                        $this->menu->itemEdit('form', $item->id, ($can_edit || $can_edit_own));
	                        $this->menu->itemTrash('timesheet', $i, $can_change);
	                        $this->menu->end();

	                        echo $this->menu->render(array('class' => 'btn-mini'));
	                        ?>
			        	</td>
			        	<td>
			        		<?php echo JHtml::_('time.format', $item->log_time); ?>
			        	</td>
			        	<td class="hidden-phone">
							<div class="<?php echo $percentage_class;?>" style="margin: 0;">
								<div class="bar" style="width: <?php echo $percentage;?>%;"></div>
							</div>
			        	</td>
			        	<td class="hidden-phone">
			        		<?php echo $item->author_name; ?>
			        	</td>
			        	<td class="hidden-phone">
			        		<?php echo JHtml::_('date', $item->log_date, JText::_('DATE_FORMAT_LC4')); ?>
			        	</td>
			        	<td class="hidden-phone">
                            <?php echo JHtml::_('pfhtml.format.money', $item->rate);?>
			        	</td>
			        	<td class="hidden-phone">
                            <?php echo JHtml::_('pfhtml.format.money', $item->billable_total);?>
			        	</td>
			        </tr>

			        <?php
			        $k = 1 - $k;
			        endforeach;
			        ?>
            	</tbody>
            	<tfoot>
            		<tr>
            			<th class="hidden-phone"><?php echo JText::_('COM_PROJECTFORK_TIME_TRACKING_TOTALS');?></th>
            			<th class="hidden-phone"></th>
            			<th class="hidden-phone"></th>
            			<th><?php echo JHtml::_('time.format', $list_total_time); ?></th>
            			<th class="hidden-phone"></th>
            			<th class="hidden-phone"></th>
            			<th class="hidden-phone"></th>
	            		<th ></th>
	            		<th><?php echo JHtml::_('pfhtml.format.money', $list_total_billable);?></th>
            		</tr>
            	</tfoot>
            </table>

            <?php if ($this->pagination->get('pages.total') > 1) : ?>
                <div class="pagination center">
                    <?php echo $this->pagination->getPagesLinks(); ?>
                </div>
                <p class="counter center"><?php echo $this->pagination->getPagesCounter(); ?></p>
            <?php endif; ?>

            <div class="filters center">
                <span class="display-limit">
                    <?php echo $this->pagination->getLimitBox(); ?>
                </span>
            </div>

            <input type="hidden" id="boxchecked" name="boxchecked" value="0" />
            <input type="hidden" name="task" value="" />
            <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
</div>
