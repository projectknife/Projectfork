<?php
/**
* @package      Projectfork Dashboard Buttons
*
* @author       Tobias Kuhn (eaxs)
* @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();


if (count($buttons) == 0) return '';
?>
<div class="row-fluid">
    <?php foreach($buttons AS $component => $btns) : ?>
        <?php if (PFApplicationHelper::enabled($component)) : ?>
            <?php foreach ($btns AS $btn) : ?>
                <div class="span3">
                    <a href="<?php echo JRoute::_($btn['link']);?>" class="thumbnail btn">
                        <p><?php echo $btn['icon']; ?></p>
                        <?php echo JText::_($btn['title']);?>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
