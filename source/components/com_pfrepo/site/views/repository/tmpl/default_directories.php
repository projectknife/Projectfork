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
            <i class="icon-arrow-up"></i>
            <a href="<?php echo JRoute::_(PFrepoHelperRoute::getRepositoryRoute($this_dir->project_id, $this_dir->parent_id, $this_dir->path));?>">
                ..
            </a>
        </td>
    </tr>
<?php endif; ?>
<?php
foreach ($this->items['directories'] as $i => $item) :
    $access = PFrepoHelper::getActions('directory', $item->id);
    $icon   = ($item->protected == '1' ? 'icon-warning' : 'icon-folder');

    if ($item->parent_id == '1') {
        $icon = 'icon-folder-2';
    }

    $can_create   = $access->get('core.create');
    $can_edit     = $access->get('core.edit');
    $can_checkin  = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
    $can_edit_own = ($access->get('core.edit.own') && $item->created_by == $uid);
    $can_change   = ($access->get('core.edit.state') && $can_checkin);
    ?>
    <tr class="row<?php echo $i % 2; ?>">
        <?php if ($this_dir->parent_id >= 1) : ?>
        <td>
            <label for="cb<?php echo $i; ?>" class="checkbox">
                <?php echo JHtml::_('pf.html.id', $i, $item->id); ?>
            </label>
        </td>
        <?php endif; ?>
        <td>
            <i class="<?php echo $icon;?>"></i>
            <?php if ($item->checked_out) : ?><i class="icon-lock"></i> <?php endif; ?>
            <a href="<?php echo JRoute::_(PFrepoHelperRoute::getRepositoryRoute($item->project_slug, $item->slug, $item->path));?>">
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
        <td>
            <?php echo $this->escape($item->description); ?> <i class="icon-user"></i> <?php echo $this->escape($item->author_name); ?>
        </td>
    </tr>
<?php endforeach; ?>
