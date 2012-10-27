<?php
/**
 * @version    SVN $Id: default_overview.php 223 2012-03-01 21:20:19Z dhorsfall $
 * @package    hwdMediaShare
 * @copyright  Copyright (C) 2011 Highwood Design Limited. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 * @author     Dave Horsfall
 * @since      30-Nov-2011 16:40:50
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
$user = JFactory::getUser();
$uri	= JFactory::getURI();
$canEdit = $user->authorise('core.edit', 'com_hwdmediashare.user.'.$this->item->id);
$canEditState = $user->authorise('core.edit.state', 'com_hwdmediashare.user.'.$this->item->id);
$canDelete = $user->authorise('core.delete', 'com_hwdmediashare.user.'.$this->item->id);

?>

<div class="media-account-header">
	<div class="page-header">
		<h2><?php echo JText::_('COM_HWDMS_OVERVIEW'); ?></h2>
	</div>
	
	<!-- Description -->
	<div class="media-account-description row-fluid"> 
		
		<!-- Thumbnail Image -->
		<div class="media-item span3">
			<?php if ($canEdit || $canDelete): ?>
			<!-- Actions -->
			<ul class="media-nav">
				<li><a href="#" class="pagenav-manage"><?php echo JText::_('COM_HWDMS_MANAGE'); ?> </a>
					<ul class="media-subnav">
						<?php if ($canEdit) : ?>
						<li><?php echo JHtml::_('hwdicon.edit', 'user', $this->item, $this->params); ?></li>
						<?php endif; ?>
						<?php if ($canEditState) : ?>
						<?php if ($this->item->published != '1') : ?>
						<li><?php echo JHtml::_('hwdicon.publish', 'user', $this->item, $this->params); ?></li>
						<?php else : ?>
						<li><?php echo JHtml::_('hwdicon.unpublish', 'user', $this->item, $this->params); ?></li>
						<?php endif; ?>
						<?php endif; ?>
						<?php if ($canDelete) : ?>
						<li><?php echo JHtml::_('hwdicon.delete', 'user', $this->item, $this->params); ?></li>
						<?php endif; ?>
					</ul>
				</li>
			</ul>
			<?php endif; ?>
			<!-- Media Type -->
			<div class="media-item-format-2"> <img src="<?php echo JHtml::_('hwdicon.overlay', 5); ?>" alt="User" /> </div>
			<img src="<?php echo JRoute::_(hwdMediaShareDownloads::thumbnail($this->item,5)); ?>" border="0" alt="<?php echo $this->escape($this->item->title); ?>" style="width:120px;" /> </div>
		<div class="span3">
			<ul class="unstyled">
				<li class="article-info-term"><?php echo JText::_('COM_HWDMS_DETAILS'); ?> </li>
				<li class="media-info-profile"><a href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&task=userform.edit&id='.$user->id.'&return='.base64_encode($uri)); ?>"><?php echo JText::_('COM_HWDMS_PROFILE'); ?></a></li>
				<li class="media-info-media"><a href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&view=account&layout=media'); ?>"><?php echo JText::_('COM_HWDMS_MEDIA'); ?></a> (<?php echo count($this->item->nummedia); ?>)</li>
				<li class="media-info-favourites"><a href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&view=account&layout=favourites'); ?>"><?php echo JText::_('COM_HWDMS_FAVOURITES'); ?></a> (<?php echo count($this->item->numfavourites); ?>)</li>
				<li class="media-info-albums"><a href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&view=account&layout=albums'); ?>"><?php echo JText::_('COM_HWDMS_ALBUMS'); ?></a> (<?php echo count($this->item->numalbums); ?>)</li>
				<li class="media-info-groups"><a href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&view=account&layout=groups'); ?>"><?php echo JText::_('COM_HWDMS_GROUPS'); ?></a> (<?php echo count($this->item->numgroups); ?>)</li>
				<li class="media-info-playlist"><a href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&view=account&layout=playlists'); ?>"><?php echo JText::_('COM_HWDMS_PLAYLISTS'); ?></a> (<?php echo count($this->item->numplaylists); ?>)</li>
				<li class="media-info-subscriptions"><a href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&view=account&layout=playlists'); ?>"><?php echo JText::_('COM_HWDMS_SUBSCRIPTIONS'); ?></a> (<?php echo count($this->item->numsubscriptions); ?>)</li>
				<li class="media-info-hits"><?php echo JText::_('COM_HWDMS_VIEWS'); ?> (<?php echo $this->item->hits; ?>)</li>
			</ul>
		</div>
		<div class="span6"> <?php echo $this->item->description; ?> </div>
	</div>
</div>
