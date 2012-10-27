<?php
/**
 * @version    SVN $Id: default_download.php 269 2012-03-22 10:07:58Z dhorsfall $
 * @package    hwdMediaShare
 * @copyright  Copyright (C) 2012 Highwood Design Limited. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 * @author     Dave Horsfall
 * @since      18-Jan-2012 09:52:56
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
JHtml::_('behavior.mootools');

$user = JFactory::getUser();
$counter=0;
?>
<form action="<?php echo htmlspecialchars(JFactory::getURI()->toString()); ?>" method="post" name="adminForm" id="adminForm">
  <div id="hwd-container"> <a name="top" id="top"></a>
    <div class="media-list-view">
      <table class="category table table-bordered table-striped">
        <thead>
        <tr>
            <th> <?php echo JHtml::_('grid.sort',  JText::_('COM_HWDMS_TYPE'), 'a.file_type', $listDirn, $listOrder); ?>
            </th>     
            <th> <?php echo JHtml::_('grid.sort',  JText::_('COM_HWDMS_EXTENSION'), 'a.ext', $listDirn, $listOrder); ?>
            </th> 
            <th> <?php echo JHtml::_('grid.sort',  JText::_('COM_HWDMS_SIZE'), 'a.size', $listDirn, $listOrder); ?>
            </th> 
        </tr>
        </thead>
        <tbody>
        <?php foreach ($this->items as $id => &$item) : ?>
          <tr class="cat-list-row<?php echo ($counter % 2);?>">
            <td><a href=""> <?php echo $this->getFileType($item); ?></a></td>
            <td><?php echo $this->escape($item->ext); ?></td>
            <td><?php echo JHtml::_('number.bytes', $item->size); ?></td>
          </tr>
        <?php $counter++; ?>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <input type="hidden" name="task" value="mediaform.download" />
  <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
  <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />    
</form>