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
$canEdit = $user->authorise('core.edit', 'com_hwdmediashare.'.$this->view_item.'.'.$item->id);
$canEditState = $user->authorise('core.edit.state', 'com_hwdmediashare.'.$this->view_item.'.'.$item->id);
$canDelete = $user->authorise('core.delete', 'com_hwdmediashare.'.$this->view_item.'.'.$item->id);
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
				<h3> <a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getPlaylistRoute($item->slug)); ?>"> <?php echo $this->escape(JHtmlString::truncate($item->title, $this->params->get('global_list_title_truncate'))); ?> </a> </h3>
				<?php endif; ?>
				<!-- Thumbnail Image -->
				<div class="thumbnail media-item">
					<?php if ($canEdit || $canDelete): ?>
					<!-- Actions -->
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
					<!-- Media Type -->
					<?php if ($this->params->get('global_list_meta_thumbnail') != 'hide') :?>
					<div class="media-item-format-<?php echo $this->elementType; ?>"> <img src="<?php echo JHtml::_('hwdicon.overlay', $this->elementType, $item); ?>" alt="<?php echo $this->elementName; ?>" /> </div>
					<a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getPlaylistRoute($item->slug)); ?>"> <img src="<?php echo JRoute::_(hwdMediaShareDownloads::thumbnail($item, $this->elementType)); ?>" border="0" alt="<?php echo $this->escape($item->title); ?>" style="max-width:100%;" /> </a>
					<?php endif; ?>
				</div>
				<!-- Clears Item and Information -->
				<div class="clear"></div>
				<!-- Item Meta -->
				<ul class="unstyled">
					<li class="article-info-term"><?php echo JText::_('COM_HWDMS_DETAILS'); ?> </li>
					<?php if ($this->params->get('global_list_meta_author') != 'hide') :?>
					<li class="media-info-createdby"> <?php echo JText::sprintf('COM_HWDMS_CREATED_BY', '<a href="#">'.htmlspecialchars($item->author, ENT_COMPAT, 'UTF-8').'</a>'); ?></li>
					<?php endif; ?>
					<?php if ($this->params->get('global_list_meta_created') != 'hide') :?>
					<li class="media-info-created"> <?php echo JText::sprintf('COM_HWDMS_CREATED_ON', JHtml::_('date', $item->created, $this->params->get('global_list_date_format'))); ?></li>
					<?php endif; ?>
					<?php if ($this->params->get('global_list_meta_likes') != 'hide') :?>
					<li class="media-info-like"> <a href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&task='.$this->view_item.'.like&id=' . $item->id . '&return=' . $this->return . '&tmpl=component'); ?>"><?php echo JText::_('COM_HWDMS_LIKE'); ?></a> (<?php echo $this->escape($item->likes); ?>) <a href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&task='.$this->view_item.'.dislike&id=' . $item->id . '&return=' . $this->return . '&tmpl=component'); ?>"><?php echo JText::_('COM_HWDMS_DISLIKE'); ?></a> (<?php echo $this->escape($item->dislikes); ?>) </li>
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
