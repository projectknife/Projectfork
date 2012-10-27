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

$function = JRequest::getCmd('function', 'pfSelectAttachment');

foreach ($this->items['files'] as $i => $item) :
    $icon = $this->escape(strtolower($item->file_extension));

    $js = 'if (window.parent) window.parent.'
        . $this->escape($function)
        . '(\'' . $item->id . '\', \''
        . $this->escape(addslashes($item->title))
        . '\', \'file\''
        . ');';
    ?>
    <tr class="row<?php echo $i % 2; ?>">
        <td>
            <a class="btn btn-mini" onclick="<?php echo $js;?>">
                <i class="icon-ok"></i>
            </a>
        </td>
        <td>
            <i class="icon-file icon-<?php echo $icon;?>"></i>&nbsp;
            <?php echo $this->escape($item->title); ?>
        </td>
        <td>
            <?php echo JHtml::_('pf.html.truncate', $item->description); ?>&nbsp;<i class="icon-user"></i> <?php echo $this->escape($item->author_name); ?>
        </td>
    </tr>
<?php endforeach; ?>
