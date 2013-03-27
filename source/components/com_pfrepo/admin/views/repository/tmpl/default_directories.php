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
$is_search      = empty($filter_search) ? false : true;

if ($this_dir->parent_id > 1) : ?>
    <tr class="row1">
        <td class="center"></td>
        <td colspan="6">
            <a href="<?php echo JRoute::_('index.php?option=com_pfrepo&view=repository&filter_parent_id=' . $this_dir->parent_id);?>">
                ..
            </a>
        </td>
    </tr>
<?php
endif;

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
        <td class="has-context">
            <div class="pull-left fltlft">
                <?php if ($item->checked_out) : ?>
                    <?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'repository.', $can_change); ?>
                <?php endif; ?>

                <i class="icon-folder hasTip" title="<?php echo JText::_('COM_PROJECTFORK_FIELD_DIRECTORY_TITLE');?>"></i>
                <a href="<?php echo JRoute::_('index.php?option=com_pfrepo&view=repository&filter_parent_id=' . $item->id);?>">
                    <?php echo JText::_($this->escape($item->title)); ?>
                </a>

                <?php if ($filter_project && $is_search): ?>
                    <div class="small">
                        <?php echo str_replace($this_path, '.', $item->path) . '/'; ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!$this->is_j25) : ?>
                <div class="pull-left">
                    <?php
                        // Create dropdown items
                        JHtml::_('dropdown.edit', $item->id, 'directory.');

                        // Render dropdown list
                        echo JHtml::_('dropdown.render');
                    ?>
                </div>
            <?php else : ?>
                <?php if ($can_edit || $can_edit_own) : ?>
                    <div class="fltrt">
                        <a href="<?php echo JRoute::_('index.php?option=com_pfrepo&' . $edit_link);?>">
                            <?php echo JText::_('JACTION_EDIT'); ?>
                        </a>
                    </div>
                <?php endif; ?>
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
