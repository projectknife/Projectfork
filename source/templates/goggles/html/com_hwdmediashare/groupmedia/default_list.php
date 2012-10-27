<?php
/**
 * @version    SVN $Id: default_list.php 223 2012-03-01 21:20:19Z dhorsfall $
 * @package    hwdMediaShare
 * @copyright  Copyright (C) 2012 Highwood Design Limited. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 * @author     Dave Horsfall
 * @since      18-Jan-2012 09:31:13
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$user = JFactory::getUser();

$row=0;
$counter=0;
$leadingcount=0;
$introcount=0;
?>
<div class="media-list-view">
  <table class="category table table-bordered table-striped table-condensed">
    <thead>
      <tr>
          <th width="1%"> <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
          </th>
          <th width="20"> <?php echo JText::_( 'COM_HWDMS_THUMBNAIL' ); ?>
          </th>
          <th> <?php echo JHtml::_('grid.sort',  JText::_('COM_HWDMS_TITLE'), 'a.title', $listDirn, $listOrder); ?>
          </th>
          <th width="20"> <?php echo JHtml::_('grid.sort',  JText::_('COM_HWDMS_LINKED'), 'connection', $listDirn, $listOrder); ?>
          </th>          
          <th width="20"> <?php echo JText::_('COM_HWDMS_ID'); ?>
          </th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($this->items as $id => &$item) :
    $item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
    $canEdit = $user->authorise('core.edit', 'com_hwdmediashare.media.'.$item->id);
    $canEditState = $user->authorise('core.edit.state', 'com_hwdmediashare.album.'.$item->id);
    $canDelete = $user->authorise('core.delete', 'com_hwdmediashare.media.'.$item->id);
    ?>
      <tr class="<?php echo ($item->published != '1' ? 'system-unpublished ' : false); ?>cat-list-row<?php echo ($counter % 2);?>">
         <td><?php echo JHtml::_('grid.id', $id, $item->id); ?>
<td><div class="media-item">
            <div class="media-item-format-1-<?php echo $item->media_type; ?>"><img src="<?php echo JHtml::_('hwdicon.overlay', '1-'.$item->media_type, $item); ?>" alt="<?php echo JText::_('COM_HWDMS_MEDIA_TYPE'); ?>" /></div>
            <?php if ($item->duration > 0) :?>
            <div class="media-duration">
               <?php echo hwdMediaShareMedia::secondsToTime($item->duration); ?>
            </div>
            <?php endif; ?>
            <a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getMediaItemRoute($item->slug)); ?>"> <img src="<?php echo JRoute::_(hwdMediaShareDownloads::thumbnail($item)); ?>" border="0" alt="<?php echo $this->escape($item->title); ?>" style="max-width:100px;" /> </a> </div></td>

            <td>
<a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getMediaItemRoute($item->slug)); ?>"> <?php echo $this->escape($item->title); ?> </a>            
         </td>
         <td><?php echo $this->getConnection($item, $id); ?></td>
         <td><?php echo $item->id; ?></td>
      </tr>
      <?php $counter++; ?>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
