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

$txt_root    = JText::_('COM_PROJECTFORK_REV_ROOT');
$txt_head    = JText::_('COM_PROJECTFORK_REV_HEAD');
$date_format = JText::_('DATE_FORMAT_LC4');

JHtml::_('behavior.multiselect');

if (!$this->is_j25) :
    JHtml::_('bootstrap.tooltip');
    JHtml::_('dropdown.init');
    JHtml::_('formbehavior.chosen', 'select');
else :
    JHtml::_('behavior.tooltip');
endif;
?>
<script type="text/javascript">
Joomla.submitbutton = function(task)
{
    Joomla.submitform(task, document.getElementById('adminForm'));
}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_pfrepo&view=noterevisions'); ?>" method="post" name="adminForm" id="adminForm">
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
                <th width="1%" class="nowrap">
                    #
                </th>
                <th width="25%">
                    <?php echo JText::_('JGLOBAL_TITLE'); ?>
                </th>
                <th>
                    <?php echo JText::_('JGRID_HEADING_DESCRIPTION'); ?>
                </th>
                <th width="15%" class="hidden-phone">
                    <?php echo JText::_('JAUTHOR'); ?>
                </th>
                <th width="10%" class="hidden-phone">
                    <?php echo JText::_('JDATE'); ?>
                </th>
                <th width="1%" class="nowrap hidden-phone">
                    <?php echo JText::_('JGRID_HEADING_ID'); ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($this->items as $i => $item) :
                $rev_id    = '';
                $rev_class = '';
                $icon      = 'icon-checkbox-unchecked';
                $rev_tt    = JText::_('COM_PROJECTFORK_REV_DESC');

                if (!isset($item->ordering)) {
                    $rev_id    = $txt_head;
                    $rev_class = ' badge-success';
                    $icon      = 'icon-checkbox';
                    $rev_tt    = JText::_('COM_PROJECTFORK_REV_HEAD_DESC');
                }
                elseif ($item->ordering == 1) {
                    $rev_id    = $txt_root;
                    $rev_class = ' badge-inverse';
                    $icon      = 'icon-checkbox-partial';
                    $rev_tt    = JText::_('COM_PROJECTFORK_REV_ROOT_DESC');
                }
                else {
                    $rev_id = (int) $item->ordering;
                }

                $dl_link = 'index.php?option=com_pfrepo&task=note.edit'
                         . '&filter_project=' . $item->project_id
                         . '&filter_parent_id=' . $this->item->dir_id
                         . '&id=' . (isset($item->parent_id) ? $item->parent_id . '&rev=' . $item->id : $item->id)
                ?>
                <tr class="row<?php echo $i % 2; ?>">
                    <td class="nowrap">
                        <span class="badge hasTip hasTooltip<?php echo $rev_class; ?>" title="<?php echo $rev_tt; ?>">
                            <i class="<?php echo $icon; ?>"></i>
                            <?php echo $rev_id; ?>
                        </span>
                    </td>
                    <td>
                        <a href="<?php echo JRoute::_($dl_link); ?>">
                            <?php echo $this->escape($item->title); ?>
                        </a>
                    </td>
                    <td>
                        <?php echo JHtml::_('pf.html.truncate', $item->description); ?>
                    </td>
                    <td class="hidden-phone small">
                        <?php echo $this->escape($item->author_name); ?>
                    </td>
                    <td class="hidden-phone small">
                        <?php echo JHtml::_('date', $item->created, $date_format); ?>
                    </td>
                    <td class="hidden-phone small">
                        <?php echo (int) $item->id; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="filter_project" value="<?php echo (int) $this->item->project_id; ?>" />
    <input type="hidden" name="filter_parent_id" value="<?php echo (int) $this->item->dir_id; ?>" />
    <input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>" />
    <?php echo JHtml::_('form.token'); ?>

    <?php if (!$this->is_j25) : ?>
        </div>
    <?php endif; ?>
</form>
