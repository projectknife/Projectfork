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


$user = JFactory::getUser();
$uid  = $user->get('id');

foreach ($this->items['files'] as $i => $item) :
    $edit_link = 'task=file.edit&filter_project=' . $item->project_id . 'filter_parent_id=' . $item->dir_id . '&id=' . $item->id;
    $access    = PFrepoHelper::getActions('file', $item->id);

    $can_create   = $access->get('core.create');
    $can_edit     = $access->get('core.edit');
    $can_checkin  = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
    $can_edit_own = ($access->get('core.edit.own') && $item->created_by == $uid);
    $can_change   = ($access->get('core.edit.state') && $can_checkin);
    ?>
    <tr class="row<?php echo $i % 2; ?>">
        <td class="center hidden-phone">
            <?php echo JHtml::_('grid.id', $i, $item->id, false, 'fid'); ?>
        </td>
        <td>
            <?php if ($item->checked_out) : ?>
                <?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'repository.', $can_change); ?>
            <?php endif; ?>
            <i class="icon-flag-2 hasTip" title="<?php echo JText::_('COM_PROJECTFORK_FIELD_FILE_LABEL');?>"></i>
            <?php if ($can_edit || $can_edit_own) : ?>
                <a href="<?php echo JRoute::_('index.php?option=com_pfrepo&' . $edit_link);?>">
                    <?php echo JText::_($this->escape($item->title)); ?>
                </a>
            <?php else : ?>
                <?php echo JText::_($this->escape($item->title)); ?>
            <?php endif; ?>
        </td>
        <td>
            <?php echo JHtml::_('pf.html.truncate', $item->description); ?>
        </td>
        <td class="hidden-phone small">
            <?php echo $this->escape($item->access_level); ?>
        </td>
        <td class="hidden-phone small">
            <?php echo $this->escape($item->author_name); ?>
        </td>
        <td class="nowrap hidden-phone small">
            <?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4')); ?>
        </td>
        <td class="hidden-phone small">
            <?php echo (int) $item->id; ?>
        </td>
    </tr>
<?php endforeach; ?>
