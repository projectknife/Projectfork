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

$function    = JRequest::getCmd('function', 'pfSelectAttachment');
$user        = JFactory::getUser();
$uid         = $user->get('id');
$this_dir    = $this->items['directory'];
$link_append = '&layout=modal&tmpl=component&function=' . $function;

if ($this_dir->parent_id > 1) : ?>
    <tr class="row1">
        <td></td>
        <td colspan="2">
            <i class="icon-arrow-up"></i>&nbsp;
            <a href="<?php echo JRoute::_(PFrepoHelperRoute::getRepositoryRoute($this_dir->project_id, $this_dir->parent_id, $this_dir->path) . $link_append);?>">
                ..
            </a>
        </td>
    </tr>
<?php endif; ?>
<?php
foreach ($this->items['directories'] as $i => $item) :
    $link   = PFrepoHelperRoute::getRepositoryRoute($item->project_slug, $item->slug, $item->path);
    $icon   = ($item->protected == '1' ? 'icon-warning' : 'icon-folder');

    if ($item->parent_id == '1') {
        $icon = 'icon-folder-2';
    }

    $js = 'if (window.parent) window.parent.'
        . $this->escape($function)
        . '(\'' . $item->id . '\', \''
        . $this->escape(addslashes($item->title))
        . '\', \'directory\''
        . ');';
    ?>
    <tr class="row<?php echo $i % 2; ?>">
        <td>
            <a class="btn btn-mini" onclick="<?php echo $js;?>">
                <i class="icon-ok"></i>
            </a>
        </td>
        <td>
            <i class="<?php echo $icon;?>"></i>&nbsp;
            <a href="<?php echo JRoute::_($link . $link_append);?>">
                <?php echo $this->escape($item->title); ?>
            </a>
        </td>
        <td>
            <?php echo JHtml::_('pf.html.truncate', $item->description); ?>&nbsp;<i class="icon-user"></i> <?php echo $this->escape($item->author_name); ?>
        </td>
    </tr>
<?php endforeach; ?>
