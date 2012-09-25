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

foreach ($this->items['notes'] as $i => $item) :
    $js = 'if (window.parent) window.parent.'
        . $this->escape($function)
        . '(\'' . $item->id . '\', \''
        . $this->escape(addslashes($item->title))
        . '\', \'note\''
        . ');';
    ?>
    <tr class="row<?php echo $i % 2; ?>">
        <td>
            <button class="btn" onclick="<?php echo $js; ?>">
                &radic;
            </button>
        </td>
        <td>
            <?php echo JText::_($this->escape($item->title)); ?>
        </td>
        <td>
            <?php echo JHtml::_('projectfork.truncate', $item->description); ?>
        </td>
    </tr>
<?php endforeach; ?>
