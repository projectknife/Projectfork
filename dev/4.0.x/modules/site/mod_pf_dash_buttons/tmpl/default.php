<?php
/**
* @package   Projectfork Dashboard Buttons
* @copyright Copyright (C) 2012 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
*
* This file is part of Projectfork.
*
* Projectfork is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*
* Projectfork is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Projectfork. If not, see <http://www.gnu.org/licenses/gpl.html>.
**/

// no direct access
defined('_JEXEC') or die;


if(count($buttons) == 0) return '';
?>
<div class="row-fluid">
    <?php foreach($buttons AS $task => $data) : ?>
    <div class="span3">
        <a href="<?php echo JRoute::_($data['link'].'&task='.$task);?>" class="thumbnail btn">
        	<p><img src="<?php echo JURI::base();?>components/com_projectfork/assets/projectfork/images/header/icon-48-<?php echo $task;?>.png" /></p>
            <?php echo JText::_($data['label']);?>
        </a>
    </div>
    <?php endforeach; ?>
</div>