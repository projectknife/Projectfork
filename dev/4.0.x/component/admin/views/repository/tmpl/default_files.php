<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


$user     = JFactory::getUser();
$uid      = $user->get('id');

return;

foreach ($this->items['files'] as $i => $item) :
    $edit_link = 'task=note.edit&filter_project=' . $item->project_id . 'filter_parent_id=' . $item->parent_id . '&id=' . $item->id;
    $access    = ProjectforkHelperAccess::getActions('note', $item->id);

    $can_create   = $access->get('note.create');
    $can_edit     = $access->get('note.edit');
    $can_checkin  = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
    $can_edit_own = ($access->get('note.edit.own') && $item->created_by == $uid);
    $can_change   = ($access->get('note.edit.state') && $can_checkin);
    ?>
    <tr class="row<?php echo $i % 2; ?>">
        <td class="center">
            <?php echo JHtml::_('grid.id', $i, $item->id, false, 'nid'); ?>
        </td>
        <td>
            <?php if ($item->checked_out) : ?>
                <?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'repository.', $can_change); ?>
            <?php endif; ?>
            <?php if ($can_edit || $can_edit_own) : ?>
                <a href="<?php echo JRoute::_('index.php?option=com_projectfork&' . $edit_link);?>">
                    <?php echo JText::_($this->escape($item->title)); ?>
                </a>
            <?php else : ?>
                <?php echo JText::_($this->escape($item->title)); ?>
            <?php endif; ?>
        </td>
        <td>
            <?php echo JHtml::_('projectfork.truncate', $item->description); ?>
        </td>
        <td class="center">
            <?php echo $this->escape($item->author_name); ?>
        </td>
        <td class="center nowrap">
            <?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4')); ?>
        </td>
        <td class="center">
            <?php echo $this->escape($item->access_level); ?>
        </td>
        <td class="center">
            <?php echo (int) $item->id; ?>
        </td>
    </tr>
<?php endforeach; ?>
