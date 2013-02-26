<?php
/**
 * @package      Projectfork
 * @subpackage   Comments
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
$archived   = $this->state->get('filter.published') == 2 ? true : false;
$trashed    = $this->state->get('filter.published') == -2 ? true : false;

$filter_project = (int) $this->state->get('filter.project');
$filter_context = (int) $this->state->get('filter.context');

$txt_context = JText::_('JGRID_HEADING_CONTEXT');
$txt_title   = JText::_('JGLOBAL_TITLE');
$date_format = JText::_('DATE_FORMAT_LC4');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');

if (!$this->is_j25) :
    JHtml::_('dropdown.init');
    JHtml::_('formbehavior.chosen', 'select');
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
<form action="<?php echo JRoute::_('index.php?option=com_pfcomments&view=comments'); ?>" method="post" name="adminForm" id="adminForm">
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
    <table class="adminlist table table-striped">
        <thead>
            <tr>
                <th width="1%" class="hidden-phone">
                    <input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
                </th>
                <th width="5%" class="center">
                    <?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.state', $list_dir, $list_order); ?>
                </th>
                <th>
                    <?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $list_dir, $list_order); ?>
                </th>
                <th width="15%" class="nowrap hidden-phone">
                    <?php echo JHtml::_('grid.sort', 'JAUTHOR', 'author_name', $list_dir, $list_order); ?>
                </th>
                <th width="10%" class="nowrap hidden-phone">
                    <?php echo JHtml::_('grid.sort', 'JDATE', 'a.created', $list_dir, $list_order); ?>
                </th>
                <th width="1%" class="nowrap hidden-phone">
                    <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $list_dir, $list_order); ?>
                </th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($this->items as $i => $item) :
            $access  = PFcommentsHelper::getActions($item->id);
            $context = str_replace('.', '_', strtoupper($item->context)) . '_TITLE';

            if ($item->level == 0) $item->level = 1;

            $can_create   = $access->get('core.create');
            $can_edit     = $access->get('core.edit');
            $can_checkin  = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
            $can_edit_own = ($access->get('core.edit.own') && $item->created_by == $uid);
            $can_change   = ($access->get('core.edit.state') && $can_checkin);

            // Prepare subtitles
            $subtitles = array();

            if (!$filter_context) {
                $subtitles[] = $txt_context . ': ' . JText::_($context);
            }

            $subtitles[] = $txt_title . ': ' . $this->escape($item->title);
            $subtitles   = implode(', ', $subtitles);
            ?>
            <tr class="row<?php echo $i % 2; ?>">
                <td class="hidden-phone">
                    <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                </td>
                <td class="center">
                    <?php echo JHtml::_('jgrid.published', $item->state, $i, 'comments.', $can_change, 'cb'); ?>
                </td>
                <td class="has-context">
                    <div class="pull-left">
                        <?php echo str_repeat('<span class="gi">|&mdash;</span>', $item->level - 1) ?>

                        <?php if ($item->checked_out) : ?>
                            <?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'comments.', $can_checkin); ?>
                        <?php endif; ?>

                        <?php if ($can_edit || $can_edit_own) : ?>
                            <a href="<?php echo JRoute::_('index.php?option=com_pfcomments&task=comment.edit&id=' . $item->id);?>">
                                <?php echo JHtml::_('string.truncate', $item->description, 80, true, false); ?>
                            </a>
                        <?php else : ?>
                            <?php echo JHtml::_('string.truncate', $item->description, 80, true, false); ?>
                        <?php endif; ?>

                        <div class="small">
                            <?php echo $subtitles; ?>

                        </div>
                    </div>

                    <?php if (!$this->is_j25) : ?>
                        <div class="pull-left">
                            <?php
                                // Create dropdown items
                                JHtml::_('dropdown.edit', $item->id, 'comment.');
                                JHtml::_('dropdown.divider');

                                if ($item->state) :
                                    JHtml::_('dropdown.unpublish', 'cb' . $i, 'comments.');
                                else :
                                    JHtml::_('dropdown.publish', 'cb' . $i, 'comments.');
                                endif;

                                JHtml::_('dropdown.divider');

                                if ($archived) :
                                    JHtml::_('dropdown.unarchive', 'cb' . $i, 'comments.');
                                else :
                                    JHtml::_('dropdown.archive', 'cb' . $i, 'comments.');
                                endif;

                                if ($item->checked_out) :
                                    JHtml::_('dropdown.checkin', 'cb' . $i, 'comments.');
                                endif;

                                if ($trashed) :
                                    JHtml::_('dropdown.untrash', 'cb' . $i, 'comments.');
                                else :
                                    JHtml::_('dropdown.trash', 'cb' . $i, 'comments.');
                                endif;

                                // Render dropdown list
                                echo JHtml::_('dropdown.render');
                            ?>
                        </div>
                    <?php endif; ?>
                </td>
                <td class="hidden-phone nowrap">
                    <?php echo $this->escape($item->author_name); ?>
                </td>
                <td class="nowrap hidden-phone">
                    <?php echo JHtml::_('date', $item->created, $date_format); ?>
                </td>
                <td class="hidden-phone">
                    <?php echo (int) $item->id; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <?php if ($this->is_j25) : ?>
            <tfoot>
                <tr>
                    <td colspan="8">
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
    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>

    <?php if (!$this->is_j25) : ?>
        </div>
    <?php endif; ?>
</form>
