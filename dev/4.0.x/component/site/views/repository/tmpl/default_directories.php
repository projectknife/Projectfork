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
$this_dir = $this->items['directory'];

if ($this_dir->parent_id > 1) : ?>
    <tr class="row1">
        <td class="center"></td>
        <td colspan="6">
            <i class="icon-arrow-up"></i>
            <a href="<?php echo JRoute::_('index.php?option=com_projectfork&view=repository&filter_parent_id=' . $this_dir->parent_id);?>">
                ..
            </a>
        </td>
    </tr>
<?php endif; ?>
<?php
foreach ($this->items['directories'] as $i => $item) :
    $access = ProjectforkHelperAccess::getActions('directory', $item->id);
    $icon   = ($item->protected == '1' ? 'icon-star' : 'icon-folder-close');

    if ($item->parent_id == '1') {
        $icon = 'icon-home';
    }

    $can_create   = $access->get('directory.create');
    $can_edit     = $access->get('directory.edit');
    $can_checkin  = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
    $can_edit_own = ($access->get('directory.edit.own') && $item->created_by == $uid);
    $can_change   = ($access->get('directory.edit.state') && $can_checkin);
    ?>
    <tr class="row<?php echo $i % 2; ?>">
        <td class="center">
            <?php echo JHtml::_('grid.id', $i, $item->id, false, 'did'); ?>
        </td>
        <td>
            <i class="<?php echo $icon;?>"></i>
            <?php if ($item->checked_out) : ?><i class="icon-lock"></i> <?php endif; ?>
            <a href="<?php echo JRoute::_('index.php?option=com_projectfork&view=repository&filter_parent_id=' . $item->id);?>">
                <?php echo JText::_($this->escape($item->title)); ?>
            </a>
        </td>
        <td>
            <?php
                $this->menu->start(array('class' => 'btn-mini'));
                $this->menu->itemEdit('directoryform', $item->id, ($can_edit || $can_edit_own));
                $this->menu->itemDelete('repository', $i, ($can_edit || $can_edit_own));
                $this->menu->end();

                echo $this->menu->render();
            ?>
        </td>
        <td>
            <?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4')); ?>
        </td>
        <td class="center">
            <?php echo $this->escape($item->description); ?> <i class="icon-user"></i> <?php echo $this->escape($item->author_name); ?>
        </td>
    </tr>
<?php endforeach; ?>
