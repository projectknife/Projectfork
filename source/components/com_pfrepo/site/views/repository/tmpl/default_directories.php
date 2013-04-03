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
            
            <a href="<?php echo JRoute::_(PFrepoHelperRoute::getRepositoryRoute($this_dir->project_id, $this_dir->parent_id, $this_dir->path));?>" class="btn btn-mini">
                <span aria-hidden="true" class="icon-arrow-left"></span> <?php echo JText::_('JPREVIOUS'); ?>
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
            <?php if ($item->checked_out) : ?><span aria-hidden="true" class="icon-lock"></span> <?php endif; ?>

            <span class="item-title pull-left">
            	<?php if ($exists) : ?>
	                <a href="<?php echo JRoute::_(PFrepoHelperRoute::getRepositoryRoute($item->project_slug, $item->slug, $item->path));?>" class="hasPopover" rel="popover" title="<?php echo JText::_($this->escape($item->title)); ?>" data-content="<?php echo $this->escape($item->description); ?>" data-placement="right">
	                	<span aria-hidden="true" class="<?php echo $icon;?>"></span> 
	                    <?php echo JText::_($this->escape($item->title)); ?>
	                </a>
	            <?php else : ?>
	                <span class="hasTooltip" rel="tooltip" title="<?php echo JText::_('COM_PROJECTFORK_ORPHANED_REPO'); ?>" style="cursor: help;">
	                    <?php echo JText::_($this->escape($item->title)); ?>
	                </span>
	            <?php endif; ?>
	            
	            <?php if ($count_elements && $elements) : ?>
                <span class="item-count badge badge-info"><?php echo $elements; ?></span>
            <?php endif; ?>
            </span>
            
            <span class="dropdown pull-left">
            	<?php
	                $this->menu->start(array('class' => 'btn-mini btn-link'));
	                $this->menu->itemEdit('directoryform', $item->id, ($can_edit || $can_edit_own));
	
	                if (($item->parent_id == 1 && !$item->project_exists) || $this_dir->id > 1) {
	                    $this->menu->itemDelete('repository', $i, ($can_edit || $can_edit_own));
	                }
	
	                $this->menu->end();
	
	                echo $this->menu->render(array('class' => 'btn-mini'));
	            ?>
            </span>
            
            <?php if ($item->access != 1) : ?>
            	<?php echo JHtml::_('pfhtml.label.access', $item->access); ?>
            <?php endif; ?>

            <?php if ($filter_project && $is_search): ?>
                <div class="small">
                    <?php echo str_replace($this_path, '.', $item->path) . '/'; ?>
                </div>
            <?php endif; ?>
        </td>
        <td>
        	<?php echo JText::_('JGRID_HEADING_DIRECTORY'); ?>
        </td>
        <td>
            <?php echo $item->author_name; ?>
        </td>
        <td>
            <?php echo JHtml::_('date', $item->created, JText::_('M d')); ?>
        </td>
    </tr>
<?php endforeach; ?>
