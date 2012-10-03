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
$x        = count($this->items['directories']);

foreach ($this->items['notes'] as $i => $item) :
    $link   = ProjectforkHelperRoute::getNoteRoute($item->slug, $item->project_slug, $item->dir_slug, $item->path);
    $access = ProjectforkHelperAccess::getActions('note', $item->id);

    $can_create   = $access->get('note.create');
    $can_edit     = $access->get('note.edit');
    $can_checkin  = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
    $can_edit_own = ($access->get('note.edit.own') && $item->created_by == $uid);
    $can_change   = ($access->get('note.edit.state') && $can_checkin);
    ?>
    <tr class="row<?php echo $i % 2; ?>">
        <td class="center">
            <?php echo JHtml::_('grid.id', $x, $item->id, false, 'nid'); ?>
        </td>
        <td>
            <i class="icon-pencil"></i> <?php if ($item->checked_out) : ?><i class="icon-lock"></i> <?php endif; ?>
            <a href="<?php echo JRoute::_($link);?>">
                <?php echo JText::_($this->escape($item->title)); ?>
            </a>
        </td>
        <td>
            <?php
                $this->menu->start(array('class' => 'btn-mini'));
                $this->menu->itemEdit('noteform', $item->id, ($can_edit || $can_edit_own));
                $this->menu->itemDelete('repository', $x, ($can_edit || $can_edit_own));
                $this->menu->end();

                echo $this->menu->render();
            ?>
        </td>
        <td>
            <?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4')); ?>
        </td>
        <td class="center">
            <?php echo JHtml::_('projectfork.truncate', $item->description, 128); ?> <i class="icon-user"></i> <?php echo $this->escape($item->author_name); ?>
        </td>
    </tr>
<?php $x++; endforeach; ?>
