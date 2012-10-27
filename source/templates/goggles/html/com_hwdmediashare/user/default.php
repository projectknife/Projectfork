<?php
/**
 * @version    SVN $Id: default.php 224 2012-03-01 22:09:22Z dhorsfall $
 * @package    hwdMediaShare
 * @copyright  Copyright (C) 2011 Highwood Design Limited. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 * @author     Dave Horsfall
 * @since      16-Nov-2011 19:45:01
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$user = JFactory::getUser();
$canEdit = $user->authorise('core.edit', 'com_hwdmediashare.user.'.$this->channel->id);
$canEditState = $user->authorise('core.edit.state', 'com_hwdmediashare.user.'.$this->channel->id);
$canDelete = $user->authorise('core.delete', 'com_hwdmediashare.user.'.$this->channel->id);
JHtml::_('behavior.modal');
JHtml::_('behavior.framework', true);

?>

<form action="<?php echo htmlspecialchars(JFactory::getURI()->toString()); ?>" method="post" name="adminForm" id="adminForm" class="form-inline">
	<div id="hwd-container"> <a name="top" id="top"></a> 
		<!-- Media Navigation --> 
		<?php echo hwdMediaShareHelperNavigation::getInternalNavigation(); ?> 
		<!-- Media Header -->
		<div class="media-header"> 
			
			<!-- View Type -->
			<div class="pull-right btn-group"> <a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getSelfRoute('details')); ?>" class="btn" title="<?php echo JText::_('COM_HWDMS_DETAILS'); ?>"> <i class="icon-file"></i> <?php echo JText::_('COM_HWDMS_DETAILS'); ?></a> <a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getSelfRoute('list')); ?>" class="btn" title="<?php echo JText::_('COM_HWDMS_LIST'); ?>"> <i class="icon-list"></i> <?php echo JText::_('COM_HWDMS_LIST'); ?></a> </div>
			<div class="page-header">
				<h2><?php echo $this->escape($this->channel->title); ?></h2>
			</div>
			<div class="clear"></div>
			<!-- Description -->
			<div class="media-user-description row-fluid"> 
				<!-- Thumbnail Image -->
				<div class="media-item span3">
					<?php if ($canEdit || $canDelete): ?>
					<!-- Actions -->
					<ul class="media-nav">
						<li><a href="#" class="pagenav-manage"><?php echo JText::_('COM_HWDMS_MANAGE'); ?> </a>
							<ul class="media-subnav">
								<?php if ($canEdit) : ?>
								<li><?php echo JHtml::_('hwdicon.edit', 'user', $this->channel, $this->params); ?></li>
								<?php endif; ?>
								<?php if ($canEditState) : ?>
								<?php if ($this->channel->published != '1') : ?>
								<li><?php echo JHtml::_('hwdicon.publish', 'user', $this->channel, $this->params); ?></li>
								<?php else : ?>
								<li><?php echo JHtml::_('hwdicon.unpublish', 'user', $this->channel, $this->params); ?></li>
								<?php endif; ?>
								<?php endif; ?>
								<?php if ($canDelete) : ?>
								<li><?php echo JHtml::_('hwdicon.delete', 'user', $this->channel, $this->params); ?></li>
								<?php endif; ?>
							</ul>
						</li>
					</ul>
					<?php endif; ?>
					<!-- Media Type -->
					<div class="media-item-format-4"> <img src="<?php echo JHtml::_('hwdicon.overlay', 5); ?>" alt="Playlist" /> </div>
					<img src="<?php echo JRoute::_(hwdMediaShareDownloads::thumbnail($this->channel, 5)); ?>" border="0" alt="<?php echo $this->escape($this->channel->title); ?>" style="width:120px;" /> </div>
				<div class="span3">
					<ul class="unstyled">
						<li class="article-info-term"><?php echo JText::_('COM_HWDMS_DETAILS'); ?> </li>
						<li class="media-info-count"><?php echo JText::_('COM_HWDMS_MEDIA'); ?> (<?php echo (int) $this->channel->nummedia; ?>)</li>
						<li class="media-info-created"> <?php echo JText::sprintf('COM_HWDMS_CREATED_ON', JHtml::_('date', $this->channel->created, $this->params->get('global_list_date_format'))); ?></li>
						<li class="media-info-hits"><?php echo JText::_('COM_HWDMS_VIEWS'); ?> (<?php echo (int) $this->channel->hits; ?>)</li>
						<li class="media-info-like"> <a href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&task=user.like&id=' . $this->channel->id . '&return=' . $this->return . '&tmpl=component'); ?>"><?php echo JText::_('COM_HWDMS_LIKE'); ?></a> (<?php echo $this->escape($this->channel->likes); ?>) <a href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&task=user.dislike&id=' . $this->channel->id . '&return=' . $this->return . '&tmpl=component'); ?>"><?php echo JText::_('COM_HWDMS_DISLIKE'); ?></a> (<?php echo $this->escape($this->channel->dislikes); ?>) </li>
						<li class="media-info-report"> <a title="<?php echo JText::_('COM_HWDMS_REPORT'); ?>" href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&task=userform.report&id=' . $this->channel->id . '&return=' . $this->return . '&tmpl=component'); ?>" class="modal" rel="{handler: 'iframe', size: {x: 500, y: 300}}"><?php echo JText::_('COM_HWDMS_REPORT'); ?> </a> </li>
					</ul>
					<!-- Custom fields -->
					<ul class="unstyled">
						<?php foreach ($this->channel->customfields['fields'] as $group => $groupFields) : ?>
						<li class="media-article-info-term"><?php echo JText::_( $group ); ?></li>
						<?php foreach ($groupFields as $field) :
          $field	= JArrayHelper::toObject ( $field );
          $field->value = $this->escape( $field->value );
          ?>
						<li class="media-createdby hasTip" title="" for="jform_<?php echo $field->id;?>" id="jform_<?php echo $field->id;?>-lbl"> <?php echo JText::_( $field->name );?> <?php echo $this->escape($field->value); ?> </li>
						<?php endforeach; ?>
						<?php endforeach; ?>
					</ul>
				</div>
				<div class="span6"> <?php echo JHtml::_('content.prepare', $this->channel->description); ?> </div>
				<div class="clear"></div>
			</div>
			<div class="clear"></div>
		</div>
		<?php echo JHtml::_('sliders.start', 'media-user-slider'); ?> <?php echo JHtml::_('sliders.panel',JText::_('COM_HWDMS_MEDIA'), 'media'); ?> <?php echo $this->loadTemplate('media_'.$this->display); ?>
		<p class="readmore"> <a href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&view=user&id=' . $this->channel->id . '&layout=media&display='.$this->display.'&tmpl=component'); ?>" class="modal" rel="{handler: 'iframe', size: {x: 840, y: 500}}"> <?php echo JText::_('COM_HWDMS_VIEW_ALL'); ?> </a> </p>
		<?php echo JHtml::_('sliders.panel',JText::_('COM_HWDMS_FAVOURITES'), 'favourites'); ?> <?php echo $this->loadTemplate('favourites_'.$this->display); ?>
		<p class="readmore"> <a href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&view=user&id=' . $this->channel->id . '&layout=favourites&display='.$this->display.'&tmpl=component'); ?>" class="modal" rel="{handler: 'iframe', size: {x: 840, y: 500}}"> <?php echo JText::_('COM_HWDMS_VIEW_ALL'); ?> </a> </p>
		<?php echo JHtml::_('sliders.panel',JText::_('COM_HWDMS_GROUPS'), 'groups'); ?> <?php echo $this->loadTemplate('groups_'.$this->display); ?>
		<p class="readmore"> <a href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&view=user&id=' . $this->channel->id . '&layout=groups&display='.$this->display.'&tmpl=component'); ?>" class="modal" rel="{handler: 'iframe', size: {x: 840, y: 500}}"> <?php echo JText::_('COM_HWDMS_VIEW_ALL'); ?> </a> </p>
		<?php echo JHtml::_('sliders.panel',JText::_('COM_HWDMS_PLAYLISTS'), 'playlists'); ?> <?php echo $this->loadTemplate('playlists_'.$this->display); ?>
		<p class="readmore"> <a href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&view=user&id=' . $this->channel->id . '&layout=playlists&display='.$this->display.'&tmpl=component'); ?>" class="modal" rel="{handler: 'iframe', size: {x: 840, y: 500}}"> <?php echo JText::_('COM_HWDMS_VIEW_ALL'); ?> </a> </p>
		<?php echo JHtml::_('sliders.panel',JText::_('COM_HWDMS_ALBUMS'), 'albums'); ?> <?php echo $this->loadTemplate('albums_'.$this->display); ?>
		<p class="readmore"> <a href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&view=user&id=' . $this->channel->id . '&layout=albums&display='.$this->display.'&tmpl=component'); ?>" class="modal" rel="{handler: 'iframe', size: {x: 840, y: 500}}"> <?php echo JText::_('COM_HWDMS_VIEW_ALL'); ?> </a> </p>
		<?php echo JHtml::_('sliders.panel',JText::_('COM_HWDMS_ACTIVITY'), 'activities'); ?> <?php echo $this->loadTemplate('activities'); ?>
		<p class="readmore"> <a class="modal" href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&view=activities&user_id='.$this->channel->id.'&tmpl=component'); ?>" rel="{handler: 'iframe', size: {x: 800, y: 500}}"> <?php echo JText::_('COM_HWDMS_VIEW_ALL'); ?> </a> </p>
		<?php echo JHtml::_('sliders.panel',JText::_('COM_HWDMS_SUBSCRIBERS'), 'subscribers'); ?> <?php echo $this->loadTemplate('subscribers_'.$this->display); ?>
		<p class="readmore"> <a href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&view=user&id=' . $this->channel->id . '&layout=subscribers&display='.$this->display.'&tmpl=component'); ?>" class="modal" rel="{handler: 'iframe', size: {x: 840, y: 500}}"> <?php echo JText::_('COM_HWDMS_VIEW_ALL'); ?> </a> </p>
		<?php echo JHtml::_('sliders.end'); ?> </div>
</form>
