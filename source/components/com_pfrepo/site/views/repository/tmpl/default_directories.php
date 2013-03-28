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


$user      = JFactory::getUser();
$uid       = $user->get('id');
$this_dir  = $this->items['directory'];
$this_path = (empty($this_dir) ? '' : $this_dir->path);

$filter_search  = $this->state->get('filter.search');
$filter_project = (int) $this->state->get('filter.project');
$count_elements = (int) $this->state->get('list.count_elements');
$is_search      = empty($filter_search) ? false : true;

if ($this_dir->parent_id > 1) : ?>
    <tr class="row1">
        <td class="center"></td>
        <td colspan="5">
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

    $exists = true;

    if ($item->parent_id == 1) {
        $icon = 'icon-folder-2';

        if (!$item->project_exists) {
            $exists = false;
            $icon   = 'icon-warning';
        }
    }

    $elements = ($count_elements ? ($item->dir_count + $item->note_count + $item->file_count) : 0 );

    $can_create   = $access->get('core.create');
    $can_edit     = $access->get('core.edit');
    $can_checkin  = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
    $can_edit_own = ($access->get('core.edit.own') && $item->created_by == $uid);
    $can_change   = ($access->get('core.edit.state') && $can_checkin);
    $date_opts    = array('past-class' => '', 'past-icon' => 'calendar');
    ?>
    <tr class="row<?php echo $i % 2; ?>">
        <?php if ($this_dir->parent_id >= 1) : ?>
        <td>
            <label for="cb<?php echo $i; ?>" class="checkbox">
                <?php echo JHtml::_('pf.html.id', $i, $item->id, false, 'did'); ?>
            </label>
        </td>
        <?php endif; ?>
        <td>
            <i class="<?php echo $icon;?>"></i>
            <?php if ($item->checked_out) : ?><i class="icon-lock"></i> <?php endif; ?>

            <?php if ($exists) : ?>
                <a href="<?php echo JRoute::_(PFrepoHelperRoute::getRepositoryRoute($item->project_slug, $item->slug, $item->path));?>">
                    <?php echo JText::_($this->escape($item->title)); ?>
                </a>
            <?php else : ?>
                <span class="hasTip" title="<?php echo JText::_('COM_PROJECTFORK_ORPHANED_REPO'); ?>" style="cursor: help;">
                    <?php echo JText::_($this->escape($item->title)); ?>
                </span>
            <?php endif; ?>

            <?php if ($count_elements && $elements) : ?>
                <span class="small">[<?php echo $elements; ?>]</span>
            <?php endif; ?>

            <?php if ($filter_project && $is_search): ?>
                <div class="small">
                    <?php echo str_replace($this_path, '.', $item->path) . '/'; ?>
                </div>
            <?php endif; ?>
        </td>
        <td>
            <?php
                $this->menu->start(array('class' => 'btn-mini'));
                $this->menu->itemEdit('directoryform', $item->id, ($can_edit || $can_edit_own));

                if (($item->parent_id == 1 && !$item->project_exists) || $this_dir->id > 1) {
                    $this->menu->itemDelete('repository', $i, ($can_edit || $can_edit_own));
                }

                $this->menu->end();

                echo $this->menu->render(array('class' => 'btn-mini'));
            ?>
        </td>
        <td>
            <?php echo JHtml::_('pfhtml.label.datetime', $item->created, false, $date_opts); ?>
        </td>
        <td>
            <?php echo $this->escape($item->description); ?>
            <?php echo JHtml::_('pfhtml.label.author', $item->author_name, $item->created); ?>
            <?php echo JHtml::_('pfhtml.label.access', $item->access); ?>
            <?php if ($item->label_count) : echo JHtml::_('pfhtml.label.labels', $item->labels); endif; ?>
        </td>
    </tr>
<?php endforeach; ?>
