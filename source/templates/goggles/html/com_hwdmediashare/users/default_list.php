<?php
/**
 * @version    SVN $Id: default_list.php 221 2012-03-01 11:35:20Z dhorsfall $
 * @package    hwdMediaShare
 * @copyright  Copyright (C) 2011 Highwood Design Limited. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 * @author     Dave Horsfall
 * @since      09-Nov-2011 16:22:58
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
  <table class="category table table-bordered table-striped">
    <thead>
      <tr>
        <?php if ($this->params->get('global_list_meta_thumbnail') != 'hide') :?>
          <th width="20"> <?php echo JText::_( 'COM_HWDMS_THUMBNAIL' ); ?>
          </th>
        <?php endif; ?>
        <?php if ($this->params->get('global_list_meta_title') != 'hide') :?>
          <th class="list-title" id="tableOrdering1"> <?php  echo JHtml::_('grid.sort', 'COM_HWDMS_TITLE', 'a.title', $listDirn, $listOrder) ; ?>
          </th>
        <?php endif; ?>
        <?php if ($this->params->get('global_list_meta_created') != 'hide') :?>
          <th class="list-date" id="tableOrdering2"> <?php  echo JHtml::_('grid.sort', 'COM_HWDMS_CREATED', 'a.created', $listDirn, $listOrder) ; ?>
          </th>
        <?php endif; ?>
        <?php if ($this->params->get('global_list_meta_author') != 'hide') :?>
          <th class="list-date" id="tableOrdering3"> <?php  echo JHtml::_('grid.sort', 'JAUTHOR', 'u.username', $listDirn, $listOrder) ; ?>
          </th>
        <?php endif; ?>
        <?php if ($this->params->get('global_list_meta_hits') != 'hide') :?>
          <th class="list-date" id="tableOrdering4"> <?php  echo JHtml::_('grid.sort', 'JGLOBAL_HITS', 'a.hits', $listDirn, $listOrder) ; ?>
          </th>
        <?php endif; ?>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($this->items as $id => &$item) :
    $item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
    $canEdit = $user->authorise('core.edit', 'com_hwdmediashare.'.$this->view_item.'.'.$item->id);
    $canEditState = $user->authorise('core.edit.state', 'com_hwdmediashare.'.$this->view_item.'.'.$item->id);
    $canDelete = $user->authorise('core.delete', 'com_hwdmediashare.'.$this->view_item.'.'.$item->id);
    ?>
      <tr class="<?php echo ($item->published != '1' ? 'system-unpublished ' : false); ?>cat-list-row<?php echo ($counter % 2);?>">
        <?php if ($this->params->get('global_list_meta_thumbnail') != 'hide') :?>
        <td><div class="media-item">
            <div class="media-item-format-<?php echo $this->elementType; ?>"><img src="<?php echo JHtml::_('hwdicon.overlay', $this->elementType, $item); ?>" alt="$this->elementName" /></div>
            <a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getUserRoute($item->slug)); ?>"> <img src="<?php echo JRoute::_(hwdMediaShareDownloads::thumbnail($item, $this->elementType)); ?>" border="0" alt="<?php echo $this->escape($item->title); ?>" style="max-width:100px;" /> </a> </div></td>
        <?php endif; ?>
        <?php if ($this->params->get('global_list_meta_title') != 'hide') :?>
          <td class="list-title"><?php if ($canEdit || $canDelete): ?>
          <ul class="media-nav">
            <li><a href="#" class="pagenav-manage"><?php echo JText::_('COM_HWDMS_MANAGE'); ?> </a>
              <ul class="media-subnav">
                <?php if ($canEdit) : ?>
                <li><?php echo JHtml::_('hwdicon.edit', $this->view_item, $item, $this->params); ?></li>
                <?php endif; ?>
                <?php if ($canEditState) : ?>
                <?php if ($item->published != '1') : ?>
                <li><?php echo JHtml::_('hwdicon.publish', $this->view_item, $item, $this->params); ?></li>
                <?php else : ?>
                <li><?php echo JHtml::_('hwdicon.unpublish', $this->view_item, $item, $this->params); ?></li>
                <?php endif; ?>
                <?php endif; ?>
                <?php if ($canDelete) : ?>
                <li><?php echo JHtml::_('hwdicon.delete', $this->view_item, $item, $this->params); ?></li>
                <?php endif; ?>
              </ul>
            </li>
          </ul>
          <?php endif; ?>
          <a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getUserRoute($item->slug)); ?>">
            <?php echo $this->escape($item->title); ?>
          </a>          
          </td>
        <?php endif; ?>
        <?php if ($this->params->get('global_list_meta_created') != 'hide') :?>
          <td class="list-date"><?php echo JHtml::_('date',$item->created, JText::_('DATE_FORMAT_LC2')); ?></td>
        <?php endif; ?>
        <?php if ($this->params->get('global_list_meta_author') != 'hide') :?>
          <td class="list-author"><a href="#"><?php echo $this->escape($item->author); ?></a></td>
        <?php endif; ?>
        <?php if ($this->params->get('global_list_meta_hits') != 'hide') :?>
          <td class="list-hits"><?php echo (int) $item->hits; ?></td>
        <?php endif; ?>
      </tr>
      <?php $counter++; ?>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>