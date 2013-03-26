<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfrepo
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
$project    = (int) $this->state->get('filter.project');

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
<form action="<?php echo JRoute::_('index.php?option=com_pfrepo&view=repository'); ?>" method="post" name="adminForm" id="adminForm">
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
                <th>
                    <?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $list_dir, $list_order); ?>
                </th>
                <th>
                    <?php echo JText::_('JGRID_HEADING_DESCRIPTION'); ?>
                </th>
                <th width="10%" class="hidden-phone">
                    <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ACCESS', 'access_level', $list_dir, $list_order); ?>
                </th>
                <th width="15%" class="hidden-phone">
                    <?php echo JHtml::_('grid.sort', 'JAUTHOR', 'author_name', $list_dir, $list_order); ?>
                </th>
                <th width="10%" class="hidden-phone">
                    <?php echo JHtml::_('grid.sort', 'JDATE', 'a.created', $list_dir, $list_order); ?>
                </th>
                <th width="1%" class="nowrap hidden-phone">
                    <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $list_dir, $list_order); ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php echo $this->loadTemplate('directories'); ?>
            <?php echo $this->loadTemplate('notes'); ?>
            <?php echo $this->loadTemplate('files'); ?>
        </tbody>
        <?php if ($this->is_j25 && $this->pagination) : ?>
            <tfoot>
                <tr>
                    <td colspan="7">
                        <?php echo $this->pagination->getListFooter(); ?>
                    </td>
                </tr>
            </tfoot>
        <?php endif; ?>
    </table>

    <?php
    if (!$this->is_j25 && $this->pagination) :
        echo $this->pagination->getListFooter();
    endif;
    ?>

    <?php echo $this->loadTemplate('batch'); ?>

    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>

    <?php if (!$this->is_j25) : ?>
        </div>
    <?php endif; ?>
</form>
