<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

$function = JRequest::getCmd('function', 'pfSelectAttachment');
$user     = JFactory::getUser();
$uid      = $user->get('id');
$this_dir = $this->items['directory'];

$link_append = '&layout=modal&tmpl=component&function=' . $function;

if ($this_dir->parent_id > 1) : ?>
    <tr class="row1">
        <td class="center"></td>
        <td colspan="6">
            <a href="<?php echo JRoute::_('index.php?option=com_projectfork&view=repository&filter_parent_id=' . $this_dir->parent_id . $link_append);?>">
                ..
            </a>
        </td>
    </tr>
<?php endif; ?>
<?php
foreach ($this->items['directories'] as $i => $item) :
    $link = 'index.php?option=com_projectfork&view=repository&filter_parent_id=' . $item->id . $link_append;

    $js = 'if (window.parent) window.parent.'
        . $this->escape($function)
        . '(\'' . $item->id . '\', \''
        . $this->escape(addslashes($item->title))
        . '\', \'directory\''
        . ');';
    ?>
    <tr class="row<?php echo $i % 2; ?>">
        <td>
            <button class="btn" onclick="<?php echo $js; ?>">
                &radic;
            </button>
        </td>
        <td>
            <a href="<?php echo $link;?>">
                <?php echo JText::_($this->escape($item->title)); ?>
            </a>
        </td>
        <td>
            <?php echo $this->escape($item->description); ?>
        </td>
    </tr>
<?php endforeach; ?>
