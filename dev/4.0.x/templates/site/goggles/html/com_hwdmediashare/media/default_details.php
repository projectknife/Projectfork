<?php
/**
 * @version    SVN $Id: default_details.php 224 2012-03-01 22:09:22Z dhorsfall $
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
?>
<!-- View Container -->

<div class="media-details-view">
	<?php foreach ($this->items as $id => &$item) :
$id= ($id-$leadingcount)+1;
$rowcount=( ((int)$id-1) %	(int) $this->columns) +1;
$row = $counter / $this->columns ;
$item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
$canEdit = ($user->authorise('core.edit', 'com_hwdmediashare.media.'.$item->id) || ($user->authorise('core.edit.own', 'com_hwdmediashare.media.'.$item->id) && ($item->created_user_id == $user->id)));
$canEditState = ($user->authorise('core.edit.state', 'com_hwdmediashare.album.'.$item->id) || ($user->authorise('core.edit.own', 'com_hwdmediashare') && ($item->created_user_id == $user->id)));
$canDelete = ($user->authorise('core.delete', 'com_hwdmediashare.media.'.$item->id) || ($user->authorise('core.edit.own', 'com_hwdmediashare') && ($item->created_user_id == $user->id)));
?>
	<!-- Row -->
	<?php if ($rowcount == 1) : ?>
	<div class="row-fluid items-row cols-<?php echo (int) $this->columns;?> <?php echo 'row-'.$row ; ?>">
		<?php endif; ?>
		<!-- Column -->
		<div class="item column-<?php echo $rowcount;?><?php echo ($item->published != '1' ? ' system-unpublished' : false); ?> span<?php echo round((12/$this->columns));?>"> 
			<!-- Cell -->
			<?php if ($item->published != '1') : ?>
			<div class="system-unpublished">
				<?php endif; ?>
				<?php if ($this->params->get('global_list_meta_title') != 'hide') :?>
				<h3> <a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getMediaItemRoute($item->slug)); ?>"> <?php echo $this->escape(JHtmlString::truncate($item->title, $this->params->get('global_list_title_truncate'))); ?> </a> </h3>
				<?php endif; ?>
				<!-- Thumbnail Image -->
				<div class="media-item thumbnail">
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
					<!-- Media Type -->
					<?php if ($this->params->get('global_list_meta_thumbnail') != 'hide') :?>
					<?php if ($this->params->get('global_list_meta_type_icon') != 'hide') :?>
					<div class="media-item-format-1-<?php echo $item->media_type; ?>"> <img src="<?php echo JHtml::_('hwdicon.overlay', '1-'.$item->media_type, $item); ?>" alt="<?php echo JText::_('COM_HWDMS_MEDIA_TYPE'); ?>" /> </div>
					<?php endif; ?>
					<?php if ($item->duration > 0) :?>
					<div class="media-duration"> <?php echo hwdMediaShareMedia::secondsToTime($item->duration); ?> </div>
					<?php endif; ?>
					<a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getMediaItemRoute($item->slug)); ?>"> <img src="<?php echo JRoute::_(hwdMediaShareDownloads::thumbnail($item)); ?>" border="0" alt="<?php echo $this->escape($item->title); ?>" style="max-width:100%;" /> </a>
					<?php endif; ?>
				</div>
				<!-- Clears Item and Information -->
				<div class="clear"></div>
				<!-- Item Meta -->
				<ul class="unstyled">
					<li class="article-info-term nav-header"><?php echo JText::_('COM_HWDMS_DETAILS'); ?> </li>
					<?php if ($this->params->get('global_list_meta_author') != 'hide') :?>
					<li class="media-info-createdby"> <?php echo JText::sprintf('COM_HWDMS_CREATED_BY', '<a href="'.JRoute::_(hwdMediaShareHelperRoute::getUserRoute($item->created_user_id)).'">'.htmlspecialchars($item->author, ENT_COMPAT, 'UTF-8').'</a>'); ?></li>
					<?php endif; ?>
					<?php if ($this->params->get('global_list_meta_created') != 'hide') :?>
					<li class="media-info-created"> <?php echo JText::sprintf('COM_HWDMS_CREATED_ON', JHtml::_('date', $item->created, $this->params->get('global_list_date_format'))); ?></li>
					<?php endif; ?>
					<?php if ($this->params->get('global_list_meta_likes') != 'hide') :?>
					<li class="media-info-like"> <a href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&task=album.like&id=' . $item->id . '&return=' . $this->return . '&tmpl=component'); ?>"><?php echo JText::_('COM_HWDMS_LIKE'); ?></a> (<?php echo $this->escape($item->likes); ?>) <a href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&task=album.dislike&id=' . $item->id . '&return=' . $this->return . '&tmpl=component'); ?>"><?php echo JText::_('COM_HWDMS_DISLIKE'); ?></a> (<?php echo $this->escape($item->dislikes); ?>) </li>
					<?php endif; ?>
					<?php if ($this->params->get('global_list_meta_hits') != 'hide') :?>
					<li class="media-info-hits"> <?php echo JText::_('COM_HWDMS_VIEWS'); ?> (<?php echo (int) $item->hits; ?>)</li>
					<?php endif; ?>
				</ul>
				<?php if ($item->published != '1') : ?>
			</div>
			<?php endif; ?>
			<div class="item-separator"></div>
		</div>
		<?php if (($rowcount == $this->columns) or (($counter + 1) == count($this->items))): ?>
		<span class="row-separator"></span> </div>
	<?php endif; ?>
	<?php $counter++; ?>
	<?php endforeach; ?>
</div>
