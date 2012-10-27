<?php
/**
 * @version    $Id: default.php 269 2012-03-22 10:07:58Z dhorsfall $
 * @package    hwdMediaShare
 * @copyright  Copyright (C) 2007 - 2011 Highwood Design Limited. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 * @author     Dave Horsfall
 * @since      15-Apr-2011 10:13:15
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

if ($this->mobile->_isMobile)
{
	$modalSizeLarge = "x: 220, y: 220";
	$modalSizeSmall = "x: 220, y: 220";
}
else
{
	$modalSizeLarge = "x: 800, y: 500";
	$modalSizeSmall = "x: 400, y: 350";
}
JHtml::_('behavior.modal');
JHtml::_('behavior.framework', true);
JHtml::_('behavior.tooltip');

$user = JFactory::getUser();
$canEdit = $user->authorise('core.edit', 'com_hwdmediashare.album.'.$this->item->id);
$canEditState = ($user->authorise('core.edit.state', 'com_hwdmediashare.album.'.$this->item->id) || ($user->authorise('core.edit.own', 'com_hwdmediashare') && ($item->created_user_id == $user->id)));
$canDelete = $user->authorise('core.edit', 'com_hwdmediashare.album.'.$this->item->id);
$hasDownloads = $this->hasDownloads();
$hasQualities = $this->hasQualities();
$hasMeta = $this->hasMeta();
?>

<div id="hwd-container"> <a name="top" id="top" title="top"></a> <?php echo hwdMediaShareHelperNavigation::getInternalNavigation(); ?> 
	<!-- Media Header -->
	<div class="media-header">
		<div class="page-header">
			<h2><?php echo $this->escape($this->item->title); ?></h2>
		</div>
		<div class="media-details pull-left">
			<?php if ($this->item->subscribed) : ?>
			<form action="<?php echo JRoute::_('index.php?option=com_hwdmediashare&task=user.unsubscribe&id=' . $this->item->created_user_id . '&return=' . $this->return . '&tmpl=component'); ?>" method="post" id="media-subscribe-form" class="form-inline">
				<a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getUserRoute($this->item->created_user_id)); ?>"><?php echo $this->escape($this->item->username); ?></a>
				<input class="btn" type="submit" value="<?php echo JText::_('COM_HWDMS_UNSUBSCRIBE'); ?>" id="media-unsubscribe" />
				<input class="btn" type="submit" value="<?php echo JText::_('COM_HWDMS_SUBSCRIBE'); ?>" id="media-subscribe" style="display:none;"/>
				<span id="media-subscribe-loading"></span>
			</form>
			<?php else : ?>
			<form action="<?php echo JRoute::_('index.php?option=com_hwdmediashare&task=user.subscribe&id=' . $this->item->created_user_id . '&return=' . $this->return . '&tmpl=component'); ?>" method="post" id="media-subscribe-form">
				<a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getUserRoute($this->item->created_user_id)); ?>"><?php echo $this->escape($this->item->username); ?></a>
				<input class="btn" type="submit" value="<?php echo JText::_('COM_HWDMS_SUBSCRIBE'); ?>" id="media-subscribe" />
				<input class="btn" type="submit" value="<?php echo JText::_('COM_HWDMS_UNSUBSCRIBE'); ?>" id="media-unsubscribe" style="display:none;" />
				<span id="media-subscribe-loading"></span>
			</form>
			<?php endif; ?>
		</div>
		<div class="btn-group pull-right">
			<?php if (isset($this->item->navigation->prev->id)) :
      $tip = '<img src="'.JRoute::_(hwdMediaShareDownloads::thumbnail($this->item->navigation->prev)).'" border="0" alt="'.$this->escape($this->item->navigation->prev->title).'" style="max-width:100%;" />'; ?>
			<a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getMediaItemRoute($this->item->navigation->prev->id)); ?>" class="btn"> <i class="icon-arrow-left"></i> <span class="editlinktip hasTip" title="<?php echo $this->escape($this->item->navigation->prev->title); ?>::<?php echo $this->escape(strip_tags($tip, '<img>,<br>')); ?>"><?php echo JText::_('JPREV'); ?> </span> </a>
			<?php else : ?>
			<a href="#" class="btn btn-disabled"> <i class="icon-arrow-left"></i> <span class="editlinktip hasTip" title="::<?php echo JText::_('COM_HWDMS_NO_PREVIOUS_MEDIA'); ?>"><?php echo JText::_('JPREV'); ?></span> </a>
			<?php endif; ?>
			<a href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&view=slideshow&id=' . $this->item->id . '&format=raw'); ?>" class="btn modal" rel="{handler: 'iframe', size: {<?php echo $modalSizeLarge; ?>}}"> <i class="icon-search"></i> Zoom</a>
			<?php if (isset($this->item->navigation->next->id)) :
      $tip = '<img src="'.JRoute::_(hwdMediaShareDownloads::thumbnail($this->item->navigation->next)).'" border="0" alt="'.$this->escape($this->item->navigation->next->title).'" style="max-width:100%;" />'; ?>
			<a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getMediaItemRoute($this->item->navigation->next->id)); ?>" class="btn"> <span class="editlinktip hasTip" title="<?php echo $this->escape($this->item->navigation->next->title); ?>::<?php echo $this->escape(strip_tags($tip, '<img>,<br>')); ?>" > <i class="icon-arrow-right"></i> <?php echo JText::_('JNEXT'); ?> </span> </a>
			<?php else : ?>
			<a href="#" class="btn btn-disabled"> <span class="editlinktip hasTip pagenav-disabled pagenav-next" title="::<?php echo JText::_('COM_HWDMS_NO_NEXT_MEDIA'); ?>"><?php echo JText::_('JNEXT'); ?></span> </a>
			<?php endif; ?>
			<?php if ($canEdit || $canEditState || $canDelete || $hasMeta || $hasDownloads) : ?>
			<a href="#" class="btn dropdown-toggle" data-toggle="dropdown"> <i class="icon-arrow-down"></i> &nbsp; <span class="caret"></span> </a>
			<ul class="dropdown-menu">
				<?php if ($canEdit) : ?>
				<li><?php echo JHtml::_('hwdicon.edit', 'media', $this->item, $this->params); ?></li>
				<?php endif; ?>
				<?php if ($canEditState) : ?>
				<?php if ($this->item->published != '1') : ?>
				<li><?php echo JHtml::_('hwdicon.publish', 'media', $this->item, $this->params); ?></li>
				<?php else : ?>
				<li><?php echo JHtml::_('hwdicon.unpublish', 'media', $this->item, $this->params); ?></li>
				<?php endif; ?>
				<?php endif; ?>
				<?php if ($canDelete) : ?>
				<li><?php echo JHtml::_('hwdicon.delete', 'media', $this->item, $this->params); ?></li>
				<?php endif; ?>
				<?php if ($hasMeta): ?>
				<li><a href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&task=mediaform.meta&id=' . $this->item->id . '&return=' . $this->return . '&tmpl=component'); ?>" class="pagenav-meta modal" rel="{handler: 'iframe', size: {<?php echo $modalSizeSmall; ?>}}"><?php echo JText::_('COM_HWDMS_VIEW_META_DATA'); ?></a>
					<?php endif; ?>
					<?php if ($hasDownloads): ?>
				<li><a href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&task=mediaform.download&id=' . $this->item->id . '&return=' . $this->return . '&tmpl=component'); ?>" class="pagenav-sizes modal" rel="{handler: 'iframe', size: {<?php echo $modalSizeSmall; ?>}}"><?php echo JText::_('COM_HWDMS_VIEW_ALL_SIZES'); ?></a></li>
				<?php endif; ?>
			</ul>
			<?php endif; ?>
		</div>
		<div class="clear"></div>
	</div>
	<div id="media-item-container" class="media-item-container"> 
		<!-- Item Media -->
		<div class="media-item-full" id="media-item" style="width:100%;"> <?php echo hwdMediaShareMedia::get($this->item); ?> </div>
		<!-- Item Meta -->
		<div class="media-info-container">
			<div class="media-count"><?php echo (int) $this->item->hits; ?></div>
			<div class="media-maker"> <?php echo JText::sprintf('COM_HWDMS_CREATED_BY', '<a href="'.JRoute::_(hwdMediaShareHelperRoute::getUserRoute($this->item->created_user_id)).'">'.htmlspecialchars($this->item->author, ENT_COMPAT, 'UTF-8').'</a>'); ?> </div>
			<div class="media-date"> <?php echo JText::sprintf('COM_HWDMS_CREATED_ON', JHtml::_('date', $this->item->created, $this->params->get('global_list_date_format'))); ?> </div>
			<div class="media-rating-stats"><?php echo JText::sprintf('COM_HWDMS_XLIKES', '<span id="media-likes">'.$this->item->likes.'</span>'); ?>, <?php echo JText::sprintf('COM_HWDMS_XDISLIKES', '<span id="media-dislikes">'.$this->item->dislikes.'</span>'); ?></div>
			<div class="clear"></div>
		</div>
		<!-- Media Actions -->
		<div class="btn-group pull-left"> <a title="<?php echo JText::_('COM_HWDMS_LIKE'); ?>" href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&task=mediaitem.like&id=' . $this->item->id . '&return=' . $this->return . '&tmpl=component'); ?>" class="btn" id="media-like-link"> <i class="icon-ok"></i> <?php echo JText::_('COM_HWDMS_LIKE'); ?> </a> <a title="<?php echo JText::_('COM_HWDMS_DISLIKE'); ?>" href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&task=mediaitem.dislike&id=' . $this->item->id . '&return=' . $this->return . '&tmpl=component'); ?>" class="btn" id="media-dislike-link"> <i class="icon-remove"></i> <?php echo JText::_('COM_HWDMS_DISLIKE'); ?> </a>
			<?php if ($this->item->favoured) : ?>
			<a title="<?php echo JText::_('COM_HWDMS_FAVOURITE'); ?>" href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&task=mediaitem.unfavour&id=' . $this->item->id . '&return=' . $this->return . '&tmpl=component'); ?>" class="btn active" id="media-favadd-link"> <i class="icon-heart"></i> <?php echo JText::_('COM_HWDMS_FAVOURITES'); ?> </a>
			<?php else : ?>
			<a title="<?php echo JText::_('COM_HWDMS_FAVOURITE'); ?>" href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&task=mediaitem.favour&id=' . $this->item->id . '&return=' . $this->return . '&tmpl=component'); ?>" class="btn" id="media-fav-link"> <i class="icon-heart"></i> <?php echo JText::_('COM_HWDMS_FAVOURITES'); ?> </a>
			<?php endif; ?>
			<a title="<?php echo JText::_('COM_HWDMS_ADD_TO'); ?>" href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&task=mediaform.link&id=' . $this->item->id . '&return=' . $this->return . '&tmpl=component'); ?>" class="btn modal" rel="{handler: 'iframe', size: {<?php echo $modalSizeSmall; ?>}}"> <i class="icon-plus"></i> <?php echo JText::_('COM_HWDMS_ADD_TO'); ?> </a> <a title="<?php echo JText::_('COM_HWDMS_SHARE'); ?>" href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&task=mediaform.share&id=' . $this->item->id . '&return=' . $this->return . '&tmpl=component'); ?>" class="btn modal" rel="{handler: 'iframe', size: {<?php echo $modalSizeSmall; ?>}}"> <i class="icon-share"></i> <?php echo JText::_('COM_HWDMS_SHARE'); ?></a> <a title="<?php echo JText::_('COM_HWDMS_REPORT'); ?>" href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&task=mediaform.report&id=' . $this->item->id . '&return=' . $this->return . '&tmpl=component'); ?>" class="btn modal" rel="{handler: 'iframe', size: {<?php echo $modalSizeSmall; ?>}}"> <i class="icon-exclamation-sign"></i> <?php echo JText::_('COM_HWDMS_REPORT'); ?></a>
			<?php if ($hasDownloads): ?>
			<a title="<?php echo JText::_('COM_HWDMS_DOWNLOAD'); ?>" href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&task=mediaform.download&id=' . $this->item->id . '&return=' . $this->return . '&tmpl=component'); ?>" class="btn modal" rel="{handler: 'iframe', size: {<?php echo $modalSizeSmall; ?>}}"> <i class="icon-download"></i> <?php echo JText::_('COM_HWDMS_DOWNLOAD'); ?></a>
			<?php endif; ?>
			<?php if ($hasQualities): ?>
			<a title="<?php echo JText::_('COM_HWDMS_QUALITY'); ?>" href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&task=mediaitem.quality&id=' . $this->item->id . '&return=' . $this->return . '&tmpl=component'); ?>" data-toggle="dropdown" class="btn modal dropdown-toggle" rel="{handler: 'iframe', size: {<?php echo $modalSizeSmall; ?>}}"> <i class="icon-cog"></i> <?php echo JText::_('COM_HWDMS_QUALITY'); ?> <span class="caret"></span> </a>
			<ul class="dropdown-menu">
				<li><a href="#"><?php echo JText::_('COM_HWDMS_240P'); ?></a></li>
				<li><a href="#"><?php echo JText::_('COM_HWDMS_360P'); ?></a></li>
				<li><a href="#"><?php echo JText::_('COM_HWDMS_480P'); ?></a></li>
				<li><a href="#"><?php echo JText::_('COM_HWDMS_720P'); ?></a></li>
				<li><a href="#"><?php echo JText::_('COM_HWDMS_1080P'); ?></a></li>
			</ul>
			<?php endif; ?>
		</div>
		<div class="clear"></div>
		<hr />
		<!-- Tabs --> 
		<?php echo $this->pane->startPane( 'pane' ); ?>
		<?php if (!empty($this->item->description)) : ?>
		<?php echo $this->pane->startPanel( JText::_('COM_HWDMS_DESCRIPTION'), 'description' ); ?> <?php echo JHtml::_('content.prepare',$this->item->description); ?> <?php echo $this->pane->endPanel(); ?>
		<?php endif; ?>
		<?php echo $this->pane->startPanel( JText::_('COM_HWDMS_RELATED'), 'related' ); ?> <?php echo $this->loadTemplate('related'); ?>
		<form action="<?php echo JRoute::_('index.php?option=com_hwdmediashare'); ?>" method="post">
			<a class="btn modal" rel="{handler: 'iframe', size: {<?php echo $modalSizeLarge; ?>}}" href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&task=search.search&searchword='.urlencode($this->item->title).'&tmpl=component&layout=related'); ?>"> <?php echo JText::_('COM_HWDMS_VIEW_ALL'); ?> </a>
			<input type="hidden" name="searchword" value="<?php echo urlencode($this->item->title); ?>" />
		</form>
		<?php echo $this->pane->endPanel(); ?>
		<?php if (!empty($this->item->location)) : ?>
		<?php echo $this->pane->startPanel( '<div id="paneMap">'.JText::_('COM_HWDMS_LOCATION').'</div>', 'map' ); ?> <?php echo ($this->item->map); ?>
		<div class="clear"></div>
		<?php echo $this->pane->endPanel(); ?>
		<?php endif; ?>
		<?php if (!empty($this->item->tags)) : ?>
		<?php echo $this->pane->startPanel( JText::_('COM_HWDMS_TAGS'), 'tags' ); ?>
		<ul class="media-tags">
			<?php foreach ($this->item->tags as $id => &$tag) : ?>
			<li><a href="#"><?php echo $this->escape($tag->tag); ?></a></li>
			<?php endforeach; ?>
		</ul>
		<?php echo $this->pane->endPanel(); ?>
		<?php endif; ?>
		<?php echo $this->pane->startPanel( JText::_('COM_HWDMS_ASSOCIATIONS'), 'associations' ); ?>
		<ul class="unstyled">
			<li class="article-info-term"><?php echo JText::_('COM_HWDMS_DETAILS'); ?> </li>
			<li class="media-createdby"> <?php echo JText::_('COM_HWDMS_USER_CHANNEL'); ?>: <?php echo $this->getChannel($this->item); ?></li>
			<li class="media-category-name"> <?php echo JText::_('COM_HWDMS_CATEGORIES'); ?>: <?php echo $this->getCategories($this->item); ?></li>
			<li class="media-album"> <?php echo JText::_('COM_HWDMS_ALBUMS'); ?>: <?php echo $this->getLinkedAlbums($this->item); ?></li>
			<li class="media-group"> <?php echo JText::_('COM_HWDMS_GROUPS'); ?>: <?php echo $this->getLinkedGroups($this->item); ?></li>
			<li class="media-group"> <?php echo JText::_('COM_HWDMS_PLAYLISTS'); ?>: <?php echo $this->getLinkedPlaylists($this->item); ?></li>
			<li class="media-group"> <?php echo JText::_('COM_HWDMS_OTHER_MEDIA'); ?>: <?php echo $this->getLinkedMedia($this->item); ?></li>
			<li class="media-group"> <?php echo JText::_('COM_HWDMS_OTHER_PAGES'); ?>: <?php echo $this->getLinkedPages($this->item); ?></li>
		</ul>
		<?php echo $this->pane->endPanel(); ?> <?php echo $this->pane->endPane(); ?>
		<div class="clear"></div>
		<ul class="unstyled">
			<?php foreach ($this->item->customfields['fields'] as $group => $groupFields) : ?>
			<li class="media-article-info-term"><?php echo JText::_( $group ); ?></li>
			<?php foreach ($groupFields as $field) :
          $field	= JArrayHelper::toObject ( $field );
          $field->value = $this->escape( $field->value );
          ?>
			<li class="media-createdby hasTip" title="" for="jform_<?php echo $field->id;?>" id="jform_<?php echo $field->id;?>-lbl"> <?php echo JText::_( $field->name );?> <?php echo $this->escape($field->value); ?> </li>
			<?php endforeach; ?>
			<?php endforeach; ?>
		</ul>
		<div class="clear"></div>
		<form action="<?php echo JRoute::_('index.php?option=com_hwdmediashare'); ?>" method="post" class="form-horizontal">
			<!-- Comments -->
			<div class="media-comments">
				<h3><?php echo JText::_('COM_HWDMS_ACTIVITY'); ?></h3>
				<?php if ($this->params->get('commenting') == 1) : ?>
				<div id="member-profile">
					<fieldset>
						<legend><strong><?php echo JText::_('COM_HWDMS_WRITE_A_COMMENT'); ?></strong></legend>
						<div class="control-group">
							<label> <a class="image-left" href="#"><img width="50" height="50" border="0" src="<?php echo JRoute::_(hwdMediaShareDownloads::thumbnail($user, 5)); ?>" /></a> </label>
							<div class="controls">
								<textarea class="required" rows="10" cols="50" id="jform_comment" name="jform[comment]" required="required" style="width:200px; margin-bottom:10px; height: 50px;"></textarea>
							</div>
						</div>
						<div class="control-group">
							<div class="controls"> <?php echo $this->getRecaptcha(); ?> </div>
						</div>
						<div class="form-actions">
							<input class="btn" type="submit" value="<?php echo JText::_('COM_HWDMS_COMMENT'); ?>" />
						</div>
						<input type="hidden" name="task" value="activity.comment" />
						<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
						<input type="hidden" name="element_type" value="1" />
						<input type="hidden" name="return" value="<?php echo $this->return; ?>" />
					</fieldset>
				</div>
				<?php endif; ?>
				<div class="categories-list"> <?php echo $this->getActivities($this->item->activities); ?> </div>
			</div>
		</form>
		<?php if ($this->params->get('commenting') != 1) : ?>
		<?php echo $this->getComments($this->item); ?>
		<?php endif; ?>
	</div>
	<div class="clear"></div>
	
	<!-- Clears Top Link -->
	<div class="clear"></div>
	<a class="media-tos" href="<?php echo htmlspecialchars(JFactory::getURI()->toString()); ?>#top">Back to Top</a> </div>
<div class="clear"></div>
