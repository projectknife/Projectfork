<?php
/**
 * @package      Projectfork
 * @subpackage   Repository
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


$user     = JFactory::getUser();
$uid      = $user->get('id');
$this_dir = $this->items['directory'];

if ($this_dir->parent_id > 1) : ?>
    <tr class="row1">
        <td class="center"></td>
        <td colspan="6">
            <a href="<?php echo JRoute::_('index.php?option=com_pfrepo&view=repository&filter_parent_id=' . $this_dir->parent_id);?>">
                ..
            </a>
        </td>
    </tr>
<?php endif; ?>
<?php
foreach ($this->items['directories'] as $i => $item) :
    $edit_link = 'task=directory.edit&filter_project=' . $item->project_id . 'filter_parent_id=' . $item->parent_id . '&id=' . $item->id;
    $access    = PFrepoHelper::getActions('directory', $item->id);

    $can_create   = $access->get('core.create');
    $can_edit     = $access->get('core.edit');
    $can_checkin  = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
    $can_edit_own = ($access->get('core.edit.own') && $item->created_by == $uid);
    $can_change   = ($access->get('core.edit.state') && $can_checkin);
    ?>
    <tr class="row<?php echo $i % 2; ?>">
        <td class="center hidden-phone">
            <?php echo JHtml::_('grid.id', $i, $item->id, false, 'did'); ?>
        </td>
        <td>
            <?php if ($item->checked_out) : ?>
                <?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'repository.', $can_change); ?>
            <?php endif; ?>
            <i class="icon-folder hasTip" title="<?php echo JText::_('COM_PROJECTFORK_FIELD_DIRECTORY_TITLE');?>"></i>
            <a href="<?php echo JRoute::_('index.php?option=com_pfrepo&view=repository&filter_parent_id=' . $item->id);?>">
                <?php echo JText::_($this->escape($item->title)); ?>
            </a>
            <?php if ($can_edit || $can_edit_own) : ?>
                <a href="<?php echo JRoute::_('index.php?option=com_pfrepo&' . $edit_link);?>" class="fltrt btn btn-mini pull-right">
                <?php echo JText::_('JACTION_EDIT'); ?>
            <?php endif; ?>
        </td>
        <td>
            <?php echo $this->escape($item->description); ?>
        </td>
        <td class="center hidden-phone">
            <?php echo $this->escape($item->author_name); ?>
        </td>
        <td class="center nowrap hidden-phone">
            <?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4')); ?>
        </td>
        <td class="center hidden-phone">
            <?php echo $this->escape($item->access_level); ?>
        </td>
        <td class="center hidden-phone">
            <?php echo (int) $item->id; ?>
        </td>
    </tr>
<?php endforeach; ?>
