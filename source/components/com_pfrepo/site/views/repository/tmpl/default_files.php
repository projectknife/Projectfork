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


$user     = JFactory::getUser();
$uid      = $user->get('id');
$x        = count($this->items['directories']) + count($this->items['notes']);

$this_dir  = $this->items['directory'];
$this_path = (empty($this_dir) ? '' : $this_dir->path);

$filter_search  = $this->state->get('filter.search');
$filter_project = (int) $this->state->get('filter.project');
$is_search      = empty($filter_search) ? false : true;

$txt_revs = JText::_('COM_PROJECTFORK_VIEW_REVISIONS');


foreach ($this->items['files'] as $i => $item) :
    $link   = PFrepoHelperRoute::getFileRoute($item->slug, $item->project_slug, $item->dir_slug, $item->path);
    $access = PFrepoHelper::getActions('file', $item->id);
    $icon   = $this->escape(strtolower($item->file_extension));

    $can_create   = $access->get('core.create');
    $can_edit     = $access->get('core.edit');
    $can_checkin  = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
    $can_edit_own = ($access->get('core.edit.own') && $item->created_by == $uid);
    $can_change   = ($access->get('core.edit.state') && $can_checkin);
    $date_opts    = array('past-class' => '', 'past-icon' => 'calendar');
    ?>
    <tr class="row<?php echo $i % 2; ?>">
        <?php if ($this_dir->parent_id >= 1) : ?>
        <td class="hidden-phone">
            <label for="cb<?php echo $x; ?>" class="checkbox">
                <?php echo JHtml::_('pf.html.id', $x, $item->id, false, 'fid'); ?>
            </label>
        </td>
        <?php endif; ?>
        <td>
        	<span class="item-title pull-left">
	            <?php if ($item->checked_out) : ?><span aria-hidden="true" class="icon-lock"></span> <?php endif; ?>
	            <a href="<?php echo JRoute::_($link);?>" class="hasPopover" rel="popover" title="<?php echo JText::_($this->escape($item->title)); ?>" data-content="<?php echo $this->escape($item->description); ?>" data-placement="right">
	            	<span aria-hidden="true" class="icon-file icon-<?php echo $icon;?>"></span>
	                <?php echo JText::_($this->escape($item->title)); ?>
	            </a>

                <?php if ($item->revision_count) : ?>
                    <span class="item-count badge badge-info"><?php echo $item->revision_count; ?></span>
                <?php endif; ?>
        	</span>

        	<span class="dropdown pull-left">
	            <?php
	                $this->menu->start(array('class' => 'btn-mini btn-link'));
	                $this->menu->itemEdit('fileform', $item->id, ($can_edit || $can_edit_own));

                    if ($item->revision_count) {
                        $link_revs = PFrepoHelperRoute::getFileRevisionsRoute($item->slug, $item->project_slug, $item->dir_slug, $item->path);

                        $this->menu->itemLink('icon-flag', 'COM_PROJECTFORK_VIEW_REVISIONS', JRoute::_($link_revs));
                    }

	                $this->menu->itemDelete('repository', $x, ($can_edit || $can_edit_own));
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
        <td class="hidden-phone">
        	<?php echo JText::_('JGRID_HEADING_FILE'); ?> <span class="muted small"><?php echo $icon; ?></span>
        </td>
        <td class="hidden-phone">
            <?php echo $item->author_name; ?>
        </td>
        <td>
            <?php echo JHtml::_('date', $item->created, JText::_('M d')); ?>
        </td>
    </tr>
<?php $x++; endforeach; ?>
