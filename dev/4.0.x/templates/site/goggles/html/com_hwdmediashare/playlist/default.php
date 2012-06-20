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

$mediaSelectOptions = array("1" => "COM_HWDMS_AUDIO", "2" => "COM_HWDMS_DOCUMENT", "3" => "COM_HWDMS_IMAGE", "4" => "COM_HWDMS_VIDEO");
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$user = JFactory::getUser();
$canEdit = $user->authorise('core.edit', 'com_hwdmediashare.playlist.'.$this->playlist->id);
$canEditState = $user->authorise('core.edit.state', 'com_hwdmediashare.playlist.'.$this->playlist->id);
$canDelete = $user->authorise('core.delete', 'com_hwdmediashare.playlist.'.$this->playlist->id);
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
			<div class="btn-group pull-right">
				<li class="btn"> <?php echo JText::_('COM_HWDMS_MEDIA'); ?> (<?php echo (int) $this->playlist->nummedia; ?>) </li>
				<a title="<?php echo JText::_('COM_HWDMS_PLAY_NOW'); ?>"  href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&view=slideshow&id=' . $this->playlist . '&format=raw'); ?>" class="modal btn" rel="{handler: 'iframe', size: {x: 800, y: 500}}"> <i class="icon-play"></i> <?php echo JText::_('COM_HWDMS_PLAY_NOW'); ?></a> </div>
			<div class="page-header">
				<h2><?php echo $this->escape($this->playlist->title); ?></h2>
			</div>
			<div class="clear"></div>
			<!-- Description -->
			<div class="row-fluid media-playlist-description"> 
				<!-- Thumbnail Image -->
				<div class="media-item span3">
					<?php if ($canEdit || $canDelete): ?>
					<!-- Actions -->
					<ul class="media-nav">
						<li><a href="#" class="pagenav-manage"><?php echo JText::_('COM_HWDMS_MANAGE'); ?> </a>
							<ul class="media-subnav">
								<?php if ($canEdit) : ?>
								<li><?php echo JHtml::_('hwdicon.edit', 'playlist', $this->playlist, $this->params); ?></li>
								<?php endif; ?>
								<?php if ($canEditState) : ?>
								<?php if ($this->playlist->published != '1') : ?>
								<li><?php echo JHtml::_('hwdicon.publish', 'playlist', $this->playlist, $this->params); ?></li>
								<?php else : ?>
								<li><?php echo JHtml::_('hwdicon.unpublish', 'playlist', $this->playlist, $this->params); ?></li>
								<?php endif; ?>
								<?php endif; ?>
								<?php if ($canDelete) : ?>
								<li><?php echo JHtml::_('hwdicon.delete', 'playlist', $this->playlist, $this->params); ?></li>
								<?php endif; ?>
							</ul>
						</li>
					</ul>
					<?php endif; ?>
					<!-- Media Type -->
					<div class="media-item-format-4"> <img src="<?php echo JHtml::_('hwdicon.overlay', 4); ?>"  alt="Playlist" /> </div>
					<div class="thumbnail">
					<img src="<?php echo JRoute::_(hwdMediaShareDownloads::thumbnail($this->playlist, 4)); ?>" border="0" alt="<?php echo $this->escape($this->playlist->title); ?>" style="width:120px;" /> </div>
					</div>
				<div class="span3"> 
					<!--<input name="" type="button" value="Play Now" class="button modal" /><br />-->
					<ul class="unstyled">
						<li class="article-info-term"><?php echo JText::_('COM_HWDMS_DETAILS'); ?> </li>
						<li class="media-info-createdby"> <?php echo JText::sprintf('COM_HWDMS_CREATED_BY', '<a href="'.JRoute::_(hwdMediaShareHelperRoute::getUserRoute($this->playlist->created_user_id)).'">'.htmlspecialchars($this->playlist->author, ENT_COMPAT, 'UTF-8').'</a>'); ?></li>
						<li class="media-info-created"> <?php echo JText::sprintf('COM_HWDMS_CREATED_ON', JHtml::_('date', $this->playlist->created, $this->params->get('global_list_date_format'))); ?></li>
						<li class="media-info-hits"> <?php echo JText::_('COM_HWDMS_VIEWS'); ?> (<?php echo (int) $this->playlist->hits; ?>)</li>
						<li class="media-info-like"> <a href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&task=playlist.like&id=' . $this->playlist->id . '&return=' . $this->return . '&tmpl=component'); ?>"><?php echo JText::_('COM_HWDMS_LIKE'); ?></a> (<?php echo $this->escape($this->playlist->likes); ?>) <a href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&task=playlist.dislike&id=' . $this->playlist->id . '&return=' . $this->return . '&tmpl=component'); ?>"><?php echo JText::_('COM_HWDMS_DISLIKE'); ?></a> (<?php echo $this->escape($this->playlist->dislikes); ?>) </li>
						<li class="media-info-report"> <a title="<?php echo JText::_('COM_HWDMS_REPORT'); ?>" href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&task=playlistform.report&id=' . $this->playlist->id . '&return=' . $this->return . '&tmpl=component'); ?>" class="modal" rel="{handler: 'iframe', size: {x: 500, y: 250}}"><?php echo JText::_('COM_HWDMS_REPORT'); ?> </a> </li>
					</ul>
					<!-- Custom fields -->
					<ul class="unstyled">
						<?php foreach ($this->playlist->customfields['fields'] as $group => $groupFields) : ?>
						<li class="media-article-info-term"><?php echo JText::_( $group ); ?></li>
						<?php foreach ($groupFields as $field) :
          $field	= JArrayHelper::toObject ( $field );
          $field->value = $this->escape( $field->value );
          ?>
						<li class="media-createdby hasTip" title=""for="jform_<?php echo $field->id;?>" id="jform_<?php echo $field->id;?>-lbl"> <?php echo JText::_( $field->name );?> <?php echo $this->escape($field->value); ?> </li>
						<?php endforeach; ?>
						<?php endforeach; ?>
					</ul>
				</div>
				<div class="span6"> <?php echo JHtml::_('content.prepare',$this->playlist->description); ?> </div>
			</div>
			<div class="clear"></div>
			<hr />
			<!-- Search Filters -->
			<fieldset class="filters">
				<?php if ($this->params->get('global_list_filter_search') != 'hide') :?>
				<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
				<input type="text" name="filter_search" class="span2" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_HWDMS_SEARCH_IN_TITLE'); ?>" />
				<button type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
				<button type="button" class="btn" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
				<?php endif; ?>
				<?php if ($this->params->get('global_list_filter_pagination') != 'hide') : ?>
				<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160; <?php echo $this->pagination->getLimitBox(); ?>
				<?php endif; ?>
				<!-- @TODO add hidden inputs -->
				<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
				<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
				<input type="hidden" name="limitstart" value="" />
			</fieldset>
			<div class="clear"></div>
		</div>
		<hr />
		<div class="media-playlist"> <?php echo $this->loadTemplate('list'); ?> </div>
		<!-- Pagination -->
		<div class="pagination"> <?php echo $this->pagination->getPagesLinks(); ?> </div>
	</div>
</form>
