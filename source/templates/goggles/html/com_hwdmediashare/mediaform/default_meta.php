<?php
/**
 * @version    SVN $Id: default_meta.php 265 2012-03-16 13:18:05Z dhorsfall $
 * @package    hwdMediaShare
 * @copyright  Copyright (C) 2012 Highwood Design Limited. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 * @author     Dave Horsfall
 * @since      16-Mar-2012 10:45:37
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.mootools');

$counter=0;
?>
<form action="<?php echo htmlspecialchars(JFactory::getURI()->toString()); ?>" method="post" name="adminForm" id="adminForm">
  <div id="hwd-container"> <a name="top" id="top"></a>
    <div class="media-list-view">
    <table class="category table table-bordered table-striped">
        <tbody>
        <?php if (!is_array($this->items) || count($this->items) == 0) : ?>
        <tr class="cat-list-row<?php echo ($counter % 2);?>">
            <td><?php echo JText::_('COM_HWDMS_NO_DATA'); ?></td>
        </tr>   
        <?php else : ?>
        <?php foreach ($this->items as $id => &$item) : ?>
        <tr class="cat-list-row<?php echo ($counter % 2);?>">
            <td><?php echo $this->escape(ucwords($id)); ?></a></td>
            <td><?php echo $this->escape(ucwords($item)); ?></td>
        </tr>
        <?php $counter++; ?>
        <?php endforeach; ?>
        <?php foreach ($this->items as $id => &$item) : ?>
        <tr class="cat-list-row<?php echo ($counter % 2);?>">
            <td><?php echo $this->escape(ucwords($id)); ?></a></td>
            <td><?php echo $this->escape(ucwords($item)); ?></td>
        </tr>
        <?php $counter++; ?>
        <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
  </div>   
</form>