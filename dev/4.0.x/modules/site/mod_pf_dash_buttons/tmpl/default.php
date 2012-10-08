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
    <?php foreach($buttons AS $task => $data) : ?>
    <div class="span3">
        <a href="<?php echo JRoute::_($data['link'].'&task=' . $task);?>" class="thumbnail btn">
            <p><?php echo JHtml::image('com_projectfork/projectfork/header/icon-48-' . $task . '.png', JText::_($data['label']), null, true); ?></p>
            <?php echo JText::_($data['label']);?>
        </a>
    </div>
    <?php endforeach; ?>
</div>
