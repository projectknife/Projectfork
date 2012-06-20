<?php
/**
 * @version    SVN $Id: default_comparison.php 224 2012-03-01 22:09:22Z dhorsfall $
 * @package    hwdMediaShare
 * @copyright  Copyright (C) 2011 Highwood Design Limited. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 * @author     Dave Horsfall
 * @since      09-Nov-2011 16:21:17
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
$this->columns = 1;

?>

<div class="media-comparison-view">
  <div class="items-row cols-4 row-0">
    <div class="item column-1">
      <h2>Audio</h2>
      <?php foreach ($this->items as $id => &$item) :
      $id= ($id-$leadingcount)+1;
      $item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
      $canEdit = $user->authorise('core.edit', 'com_hwdmediashare.media.'.$item->id);
      $canEditState = $user->authorise('core.edit.state', 'com_hwdmediashare.album.'.$item->id);
      $canDelete = $user->authorise('core.delete', 'com_hwdmediashare.media.'.$item->id);
      ?>
      <?php if ($item->published != '1') : ?>
      <div class="system-unpublished">
        <?php endif; ?>
        <h4> <a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getMediaItemRoute($item->slug)); ?>"> <?php echo $this->escape($item->title); ?> </a> </h4>
        <div class="media-item">
          <?php if ($canEdit || $canDelete): ?>
          <!-- Actions -->
          <ul class="media-nav">
            <li><a href="#" class="pagenav-manage"><?php echo JText::_('COM_HWDMS_MANAGE'); ?> </a>
              <ul class="media-subnav">
                <?php if ($canEdit) : ?>
                <li><?php echo JHtml::_('hwdicon.edit', 'media', $item, $this->params); ?></li>
                <?php endif; ?>
                <?php if ($canEditState) : ?>
                <?php if ($item->published != '1') : ?>
                <li><?php echo JHtml::_('hwdicon.publish', 'media', $item, $this->params); ?></li>
                <?php else : ?>
                <li><?php echo JHtml::_('hwdicon.unpublish', 'media', $item, $this->params); ?></li>
                <?php endif; ?>
                <?php endif; ?>
                <?php if ($canDelete) : ?>
                <li><?php echo JHtml::_('hwdicon.delete', 'media', $item, $this->params); ?></li>
                <?php endif; ?>
              </ul>
            </li>
          </ul>
          <?php endif; ?>
          <div class="media-item-format-1-<?php echo $item->media_type; ?>"> <img src="<?php echo JHtml::_('hwdicon.overlay', '1-'.$item->media_type, $item); ?>" alt="<?php echo JText::_('COM_HWDMS_MEDIA_TYPE'); ?>" /> </div>
          <a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getMediaItemRoute($item->slug)); ?>"> <img src="<?php echo JRoute::_(hwdMediaShareDownloads::thumbnail($item)); ?>" border="0" alt="<?php echo $this->escape($item->title); ?>" style="max-width:100%;" /> </a> </div>

        <!-- Clears Item and Information -->
        <div class="clear"></div>
        <dl class="article-info">
          <dt class="article-info-term"><?php echo JText::_('COM_HWDMS_DETAILS'); ?> </dt>
          <dd class="media-info-createdby"> Created by <a href="#"><?php echo $this->escape($item->author); ?></a></dd>
          <dd class="media-info-hits"> <?php echo JText::_('COM_HWDMS_VIEWS'); ?> (<?php echo (int) $item->hits; ?>)</dd>
        </dl>
        <?php if ($item->published != '1') : ?>
      </div>
      <?php endif; ?>
      <div class="item-separator"></div>
      <?php endforeach; ?>
      <div class="item-separator"></div>
    </div>
    <div class="item column-2">
      <h2>Documents</h2>
      <?php foreach ($this->items as $id => &$item) :
      $id= ($id-$leadingcount)+1;
      $item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
      $canEdit = $user->authorise('core.edit', 'com_hwdmediashare.media.'.$item->id);
      $canEditState = $user->authorise('core.edit.state', 'com_hwdmediashare.album.'.$item->id);
      $canDelete = $user->authorise('core.delete', 'com_hwdmediashare.media.'.$item->id);
      ?>
      <?php if ($item->published != '1') : ?>
      <div class="system-unpublished">
        <?php endif; ?>
        <h4> <a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getMediaItemRoute($item->slug)); ?>"> <?php echo $this->escape($item->title); ?> </a> </h4>
        <div class="media-item">
          <?php if ($canEdit || $canDelete): ?>
          <!-- Actions -->
          <ul class="media-nav">
            <li><a href="#" class="pagenav-manage"><?php echo JText::_('COM_HWDMS_MANAGE'); ?> </a>
              <ul class="media-subnav">
                <?php if ($canEdit) : ?>
                <li><?php echo JHtml::_('hwdicon.edit', 'media', $item, $this->params); ?></li>
                <?php endif; ?>
                <?php if ($canEditState) : ?>
                <?php if ($item->published != '1') : ?>
                <li><?php echo JHtml::_('hwdicon.publish', 'media', $item, $this->params); ?></li>
                <?php else : ?>
                <li><?php echo JHtml::_('hwdicon.unpublish', 'media', $item, $this->params); ?></li>
                <?php endif; ?>
                <?php endif; ?>
                <?php if ($canDelete) : ?>
                <li><?php echo JHtml::_('hwdicon.delete', 'media', $item, $this->params); ?></li>
                <?php endif; ?>
              </ul>
            </li>
          </ul>
          <?php endif; ?>
          <div class="media-item-format-1-<?php echo $item->media_type; ?>"> <img src="<?php echo JHtml::_('hwdicon.overlay', '1-'.$item->media_type); ?>" width="24" alt="<?php echo JText::_('COM_HWDMS_MEDIA_TYPE'); ?>" /> </div>
          <a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getMediaItemRoute($item->slug)); ?>"> <img src="<?php echo JRoute::_(hwdMediaShareDownloads::thumbnail($item)); ?>" border="0" alt="<?php echo $this->escape($item->title); ?>" style="max-width:100%;" /> </a> </div>

        <!-- Clears Item and Information -->
        <div class="clear"></div>
        <dl class="article-info">
          <dt class="article-info-term"><?php echo JText::_('COM_HWDMS_DETAILS'); ?> </dt>
          <dd class="media-info-createdby"> Created by <a href="#"><?php echo $this->escape($item->author); ?></a></dd>
          <dd class="media-info-hits"> <?php echo JText::_('COM_HWDMS_VIEWS'); ?> (<?php echo (int) $item->hits; ?>)</dd>
        </dl>
        <?php if ($item->published != '1') : ?>
      </div>
      <?php endif; ?>
      <div class="item-separator"></div>
      <?php endforeach; ?>
      <div class="item-separator"></div>
    </div>
    <div class="item column-3">
      <h2>Images</h2>
      <?php foreach ($this->items as $id => &$item) :
      $id= ($id-$leadingcount)+1;
      $item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
      $canEdit = $user->authorise('core.edit', 'com_hwdmediashare.media.'.$item->id);
      $canEditState = $user->authorise('core.edit.state', 'com_hwdmediashare.album.'.$item->id);
      $canDelete = $user->authorise('core.delete', 'com_hwdmediashare.media.'.$item->id);
      ?>
      <?php if ($item->published != '1') : ?>
      <div class="system-unpublished">
        <?php endif; ?>
        <h4> <a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getMediaItemRoute($item->slug)); ?>"> <?php echo $this->escape($item->title); ?> </a> </h4>
        <div class="media-item">
          <?php if ($canEdit || $canDelete): ?>
          <!-- Actions -->
          <ul class="media-nav">
            <li><a href="#" class="pagenav-manage"><?php echo JText::_('COM_HWDMS_MANAGE'); ?> </a>
              <ul class="media-subnav">
                <?php if ($canEdit) : ?>
                <li><?php echo JHtml::_('hwdicon.edit', 'media', $item, $this->params); ?></li>
                <?php endif; ?>
                <?php if ($canEditState) : ?>
                <?php if ($item->published != '1') : ?>
                <li><?php echo JHtml::_('hwdicon.publish', 'media', $item, $this->params); ?></li>
                <?php else : ?>
                <li><?php echo JHtml::_('hwdicon.unpublish', 'media', $item, $this->params); ?></li>
                <?php endif; ?>
                <?php endif; ?>
                <?php if ($canDelete) : ?>
                <li><?php echo JHtml::_('hwdicon.delete', 'media', $item, $this->params); ?></li>
                <?php endif; ?>
              </ul>
            </li>
          </ul>
          <?php endif; ?>
          <div class="media-item-format-1-<?php echo $item->media_type; ?>"> <img src="<?php echo JHtml::_('hwdicon.overlay', '1-'.$item->media_type); ?>" width="24" alt="<?php echo JText::_('COM_HWDMS_MEDIA_TYPE'); ?>" /> </div>
          <a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getMediaItemRoute($item->slug)); ?>"> <img src="<?php echo JRoute::_(hwdMediaShareDownloads::thumbnail($item)); ?>" border="0" alt="<?php echo $this->escape($item->title); ?>" style="max-width:100%;" /> </a> </div>

        <!-- Clears Item and Information -->
        <div class="clear"></div>
        <dl class="article-info">
          <dt class="article-info-term"><?php echo JText::_('COM_HWDMS_DETAILS'); ?> </dt>
          <dd class="media-info-createdby"> Created by <a href="#"><?php echo $this->escape($item->author); ?></a></dd>
          <dd class="media-info-hits"> <?php echo JText::_('COM_HWDMS_VIEWS'); ?> (<?php echo (int) $item->hits; ?>)</dd>
        </dl>
        <?php if ($item->published != '1') : ?>
      </div>
      <?php endif; ?>
      <div class="item-separator"></div>
      <?php endforeach; ?>
      <div class="item-separator"></div>
    </div>
    <div class="item column-4">
      <h2>Videos</h2>
      <?php foreach ($this->items as $id => &$item) :
      $id= ($id-$leadingcount)+1;
      $item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
      $canEdit = $user->authorise('core.edit', 'com_hwdmediashare.media.'.$item->id);
      $canEditState = $user->authorise('core.edit.state', 'com_hwdmediashare.album.'.$item->id);
      $canDelete = $user->authorise('core.delete', 'com_hwdmediashare.media.'.$item->id);
      ?>
      <?php if ($item->published != '1') : ?>
      <div class="system-unpublished">
        <?php endif; ?>
        <h4> <a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getMediaItemRoute($item->slug)); ?>"> <?php echo $this->escape($item->title); ?> </a> </h4>
        <div class="media-item">
          <?php if ($canEdit || $canDelete): ?>
          <!-- Actions -->
          <ul class="media-nav">
            <li><a href="#" class="pagenav-manage"><?php echo JText::_('COM_HWDMS_MANAGE'); ?> </a>
              <ul class="media-subnav">
                <?php if ($canEdit) : ?>
                <li><?php echo JHtml::_('hwdicon.edit', 'media', $item, $this->params); ?></li>
                <?php endif; ?>
                <?php if ($canEditState) : ?>
                <?php if ($item->published != '1') : ?>
                <li><?php echo JHtml::_('hwdicon.publish', 'media', $item, $this->params); ?></li>
                <?php else : ?>
                <li><?php echo JHtml::_('hwdicon.unpublish', 'media', $item, $this->params); ?></li>
                <?php endif; ?>
                <?php endif; ?>
                <?php if ($canDelete) : ?>
                <li><?php echo JHtml::_('hwdicon.delete', 'media', $item, $this->params); ?></li>
                <?php endif; ?>
              </ul>
            </li>
          </ul>
          <?php endif; ?>
          <div class="media-item-format-1-<?php echo $item->media_type; ?>"> <img src="<?php echo JHtml::_('hwdicon.overlay', '1-'.$item->media_type); ?>" width="24" alt="<?php echo JText::_('COM_HWDMS_MEDIA_TYPE'); ?>" /> </div>
          <a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getMediaItemRoute($item->slug)); ?>"> <img src="<?php echo JRoute::_(hwdMediaShareDownloads::thumbnail($item)); ?>" border="0" alt="<?php echo $this->escape($item->title); ?>" style="max-width:100%;" /> </a> </div>

        <!-- Clears Item and Information -->
        <div class="clear"></div>
        <dl class="article-info">
          <dt class="article-info-term"><?php echo JText::_('COM_HWDMS_DETAILS'); ?> </dt>
          <dd class="media-info-createdby"> Created by <a href="#"><?php echo $this->escape($item->author); ?></a></dd>
          <dd class="media-info-hits"> <?php echo JText::_('COM_HWDMS_VIEWS'); ?> (<?php echo (int) $item->hits; ?>)</dd>
        </dl>
        <?php if ($item->published != '1') : ?>
      </div>
      <?php endif; ?>
      <div class="item-separator"></div>
      <?php endforeach; ?>
      <div class="item-separator"></div>
    </div>
    <span class="row-separator"></span> </div>
</div>
