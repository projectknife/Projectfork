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
$j3000    = version_compare(JVERSION, '3.0.0', 'ge');

foreach ($this->items['files'] as $i => $item) :
    $js = 'if (window.parent) window.parent.'
        . $this->escape($function)
        . '(\'' . $item->id . '\', \''
        . $this->escape(addslashes($item->title))
        . '\', \'file\''
        . ');';
    ?>
    <tr class="row<?php echo $i % 2; ?>">
        <td>
            <?php if (!$j3000) : ?>
                [<a href="javascript:void(0);" onclick="<?php echo $js; ?>"><?php echo JText::_('JACTION_SELECT');?></a>]
            <?php else : ?>
                <a class="btn" href="javascript:void(0);" onclick="<?php echo $js; ?>">
                    <i class="icon-ok"></i>
                </a>
            <?php endif; ?>
        </td>
        <td>
            <?php echo $this->escape($item->title); ?>
        </td>
        <td>
            <?php echo JHtml::_('pf.html.truncate', $item->description); ?>
        </td>
    </tr>
<?php endforeach; ?>
