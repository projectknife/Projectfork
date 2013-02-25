<?php
/**
 * @package      Projectfork
 * @subpackage   Tasks
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


$user       = JFactory::getUser();
$uid        = $user->get('id');
$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$save_order = ($list_order == 'a.ordering');
$archived   = $this->state->get('filter.published') == 2 ? true : false;
$trashed    = $this->state->get('filter.published') == -2 ? true : false;

$filter_project = (int) $this->state->get('filter.project');
$filter_ms      = (int) $this->state->get('filter.milestone');
$filter_tl      = (int) $this->state->get('filter.tasklist');

$txt_project = JText::_('JGRID_HEADING_PROJECT');
$txt_ms      = JText::_('JGRID_HEADING_MILESTONE');
$txt_tl      = JText::_('JGRID_HEADING_TASKLIST');
$txt_notset  = JText::_('DATE_NOT_SET');
$txt_norder  = JText::_('JORDERINGDISABLED');
$date_format = JText::_('DATE_FORMAT_LC4');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');

if (!$this->is_j25) :
    JHtml::_('dropdown.init');
    JHtml::_('formbehavior.chosen', 'select');

    if ($save_order) {
	   $order_url = 'index.php?option=com_pftasks&task=tasks.saveOrderAjax&tmpl=component';
	   JHtml::_('sortablelist.sortable', 'taskList', 'adminForm', strtolower($list_dir), $order_url, false);
    }
    ?>
    <script type="text/javascript">
    Joomla.orderTable = function()
    {
        table     = document.getElementById("sortTable");
        direction = document.getElementById("directionTable");
        order     = table.options[table.selectedIndex].value;

        if (order != '<?php echo $list_order; ?>') {
            dirn = 'asc';
        }
        else {
            dirn = direction.options[direction.selectedIndex].value;
        }

        Joomla.tableOrdering(order, dirn, '');
    }
    </script>
    <?php
endif;
?>
<form action="<?php echo JRoute::_('index.php?option=com_pftasks&view=tasks'); ?>" method="post" name="adminForm" id="adminForm">
    <!-- Task field must be at the top because of a bug in the Joomla 3.0 sortable script -->
    <input type="hidden" name="task" value="" />
    <?php
    if (!$this->is_j25) :
        if (!empty($this->sidebar)) :
            ?>
            <div id="j-sidebar-container" class="span2">
                <?php echo $this->sidebar; ?>
            </div>
            <div id="j-main-container" class="span10">
        <?php else : ?>
                <div id="j-main-container">
            <?php
        endif;
    endif;

    echo $this->loadTemplate('filter_' . ($this->is_j25 ? 'j25' : 'j30'));
    ?>
    <table class="adminlist table table-striped" id="taskList">
        <thead>
            <tr>
                <?php if (!$this->is_j25) : ?>
                    <th width="1%" class="nowrap center hidden-phone">
    					<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'a.ordering', $list_dir, $list_order, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
    				</th>
                <?php endif; ?>
                <th width="1%" class="hidden-phone">
                    <input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
                </th>
                <th width="5%" class="center">
                    <?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.state', $list_dir, $list_order); ?>
                </th>
                <th>
                    <?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $list_dir, $list_order); ?>
                </th>
                <?php if ($this->is_j25) : ?>
                    <th width="10%" class="nowrap">
                        <span class="pull-left">
                        	<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ORDERING', 'a.ordering', $list_dir, $list_order); ?>
                        </span>
                        <?php if ($save_order) :?>
                            <?php echo JHtml::_('grid.order',  $this->items, 'filesave.png', 'tasks.saveorder'); ?>
                        <?php endif; ?>
                    </th>
                <?php endif; ?>
                <th width="10%" class="nowrap">
                    <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_DEADLINE', 'a.end_date', $list_dir, $list_order); ?>
                </th>
                <th width="10%" class="hidden-phone nowrap">
                    <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ACCESS', 'access_level', $list_dir, $list_order); ?>
                </th>
                <th width="15%" class="nowrap hidden-phone">
                    <?php echo JHtml::_('grid.sort', 'JAUTHOR', 'author_name', $list_dir, $list_order); ?>
                </th>
                <th width="10%" class="nowrap hidden-phone">
                    <?php echo JHtml::_('grid.sort', 'JDATE', 'a.created', $list_dir, $list_order); ?>
                </th>
                <th width="1%" class="hidden-phone nowrap">
                    <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $list_dir, $list_order); ?>
                </th>
            </tr>
        </thead>
        <tbody>
        <?php
        foreach ($this->items as $i => $item) :
            $access = PFtasksHelper::getActions($item->id);

            $can_create   = $access->get('core.create');
            $can_edit     = $access->get('core.edit');
            $can_checkin  = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
            $can_edit_own = ($access->get('core.edit.own') && $item->created_by == $uid);
            $can_change   = ($access->get('core.edit.state') && $can_checkin);

            // Prepare re-order conditions for Joomla 2.5
            $p_order_u = '';
            $p_order_d = '';

            if ($this->is_j25 && $can_change && $save_order) {
                $order_up   = false;
                $order_down = false;
                $prev_item  = (isset($this->items[$i - 1]) ? $this->items[$i - 1] : null);
                $next_item  = (isset($this->items[$i + 1]) ? $this->items[$i + 1] : null);

                if ($prev_item) {
                    $order_up = ($item->project_id == $prev_item->project_id && $item->milestone_id == $prev_item->milestone_id && $item->list_id == $prev_item->list_id);
                }

                if ($next_item) {
                    $order_down = ($item->project_id == $next_item->project_id && $item->milestone_id == $next_item->milestone_id && $item->list_id == $next_item->list_id);
                }

                if ($list_dir == 'asc') {
                    $order_tu = 'tasks.orderup';
                    $order_td = 'tasks.orderdown';
                }
                else {
                    $order_tu = 'tasks.orderdown';
                    $order_td = 'tasks.orderup';
                }

                $p_order_u = $this->pagination->orderUpIcon($i, $order_up, $order_tu, 'JLIB_HTML_MOVE_UP', $save_order);
                $p_order_d = $this->pagination->orderDownIcon($i, $this->pagination->total, $order_down, $order_td, 'JLIB_HTML_MOVE_DOWN', $save_order);
            }

            // Sorting group for Joomla 3.0 or higher
            $sgroup = $item->project_id;

            if ($item->list_id) {
                $sgroup .= '-l-' . $item->list_id;
            }
            elseif ($item->milestone_id) {
                $sgroup .= '-m-' . $item->milestone_id;
            }

            // Prepare sub title
            $subtitles = array();

            if (!$filter_project) {
                $subtitles[] = $txt_project . ': ' . $this->escape($item->project_title);
            }
            if (!$filter_ms && $item->milestone_id) {
                $subtitles[] = $txt_ms . ': ' . $this->escape($item->milestone_title);
            }
            if (!$filter_tl && $item->list_id) {
                $subtitles[] = $txt_tl . ': ' . $this->escape($item->tasklist_title);
            }

            $subtitles = implode(', ', $subtitles);
            ?>
            <tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $sgroup; ?>">
                <?php if (!$this->is_j25) : ?>
                    <td class="order nowrap center hidden-phone">
                        <?php
                        if ($can_change) :
                            $r_disable = '';
                            $r_lbl     = '';

    						if (!$save_order) :
                                $r_disable = 'inactive tip-top';
    							$r_lbl     = $txt_norder;
    						endif;
                            ?>
    						<span class="sortable-handler hasTooltip <?php echo $r_disable; ?>" title="<?php echo $r_lbl; ?>">
    							<i class="icon-menu"></i>
    						</span>
    						<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order" />
    					<?php else : ?>
    						<span class="sortable-handler inactive" >
    							<i class="icon-menu"></i>
    						</span>
    					<?php endif; ?>
    				</td>
                <?php endif; ?>
                <td class="center hidden-phone">
                    <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                </td>
                <td class="center">
                    <?php echo JHtml::_('jgrid.published', $item->state, $i, 'tasks.', $can_change, 'cb'); ?>
                </td>
                <td class="has-context">
                    <div class="pull-left">
                        <?php if ($item->checked_out) : ?>
                            <?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'tasks.', $can_checkin); ?>
                        <?php endif; ?>

                        <?php if ($can_edit || $can_edit_own) : ?>
                            <a href="<?php echo JRoute::_('index.php?option=com_pftasks&task=task.edit&id=' . $item->id);?>">
                                <?php echo $this->escape($item->title); ?></a>
                        <?php else : ?>
                            <?php echo $this->escape($item->title); ?>
                        <?php endif; ?>

                        <div class="small">
                            <?php echo $subtitles; ?>
                        </div>
                    </div>

                    <?php if (!$this->is_j25) : ?>
                        <div class="pull-left">
                            <?php
                                // Create dropdown items
                                JHtml::_('dropdown.edit', $item->id, 'task.');
                                JHtml::_('dropdown.divider');

                                if ($item->state) :
                                    JHtml::_('dropdown.unpublish', 'cb' . $i, 'tasks.');
                                else :
                                    JHtml::_('dropdown.publish', 'cb' . $i, 'tasks.');
                                endif;

                                JHtml::_('dropdown.divider');

                                if ($archived) :
                                    JHtml::_('dropdown.unarchive', 'cb' . $i, 'tasks.');
                                else :
                                    JHtml::_('dropdown.archive', 'cb' . $i, 'tasks.');
                                endif;

                                if ($item->checked_out) :
                                    JHtml::_('dropdown.checkin', 'cb' . $i, 'tasks.');
                                endif;

                                if ($trashed) :
                                    JHtml::_('dropdown.untrash', 'cb' . $i, 'tasks.');
                                else :
                                    JHtml::_('dropdown.trash', 'cb' . $i, 'tasks.');
                                endif;

                                // Render dropdown list
                                echo JHtml::_('dropdown.render');
                            ?>
                        </div>
                    <?php endif; ?>
                </td>
                <?php if ($this->is_j25) : ?>
                    <td class="order">
                        <?php if ($can_change) : ?>
                            <?php if ($save_order) : ?>
                                <span><?php echo $p_order_u; ?></span>
                                <span><?php echo $p_order_d; ?></span>
                            <?php endif; ?>
                            <input type="text" name="order[]" size="5" value="<?php echo $item->ordering;?>"
                                <?php echo ($save_order ?  '' : 'disabled="disabled"') ?> class="text-area-order width-10"
                            />
                        <?php else : ?>
                            <?php echo $item->ordering; ?>
                        <?php endif; ?>
                    </td>
                <?php endif; ?>
                <td class="nowrap">
                    <?php echo (($item->end_date == $this->nulldate) ? $txt_notset : JHtml::_('date', $item->end_date, $date_format)); ?>
                </td>
                <td class="hidden-phone small">
                    <?php echo $this->escape($item->access_level); ?>
                </td>
                <td class="hidden-phone small">
                    <?php echo $this->escape($item->author_name); ?>
                </td>
                <td class="hidden-phone nowrap small">
                    <?php echo JHtml::_('date', $item->created, $date_format); ?>
                </td>
                <td class="hidden-phone small">
                    <?php echo (int) $item->id; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <?php if ($this->is_j25) : ?>
            <tfoot>
                <tr>
                    <td colspan="9">
                        <?php echo $this->pagination->getListFooter(); ?>
                    </td>
                </tr>
            </tfoot>
        <?php endif; ?>
    </table>

    <?php if (!$this->is_j25) : echo $this->pagination->getListFooter(); endif; ?>

    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
    <?php echo JHtml::_('form.token'); ?>

    <?php if (!$this->is_j25) : ?>
        </div>
    <?php endif; ?>
</form>
