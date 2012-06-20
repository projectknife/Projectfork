<?php
/**
 * @version    $Id: default.php 224 2012-03-01 22:09:22Z dhorsfall $
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
$canEdit = $user->authorise('core.edit', 'com_hwdmediashare.category.'.$this->category->id);
$canEditState = $user->authorise('core.edit.state', 'com_hwdmediashare.category.'.$this->category->id);
$canDelete = $user->authorise('core.delete', 'com_hwdmediashare.category.'.$this->category->id);

?>

<form action="<?php echo htmlspecialchars(JFactory::getURI()->toString()); ?>" method="post" name="adminForm" id="adminForm" class="form-inline">
	<div id="hwd-container"> <a name="top" id="top"></a> 
		<!-- Media Navigation --> 
		<?php echo hwdMediaShareHelperNavigation::getInternalNavigation(); ?> 
		<!-- Media Header -->
		<div class="media-header">
			<div class="pull-right btn-group"> <a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getSelfRoute('details')); ?>" class="btn" title="<?php echo JText::_('COM_HWDMS_DETAILS'); ?>"><i class="icon-file"></i> <?php echo JText::_('COM_HWDMS_DETAILS'); ?></a> <a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getSelfRoute('gallery')); ?>" class="btn" title="<?php echo JText::_('COM_HWDMS_GALLERY'); ?>"><i class="icon-th"></i> <?php echo JText::_('COM_HWDMS_GALLERY'); ?></a> <a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getSelfRoute('list')); ?>" class="btn" title="<?php echo JText::_('COM_HWDMS_LIST'); ?>"><i class="icon-list"></i> <?php echo JText::_('COM_HWDMS_LIST'); ?></a> </div>
			<div class="page-header">
				<h2><?php echo $this->escape($this->category->title); ?></h2>
			</div>
			<div class="clear"></div>
			<?php echo $this->loadTemplate('subcategories'); ?>
			<div class="clear"></div>
			<!-- Description -->
			<div class="media-category-description row-fluid"> 
				<!-- Thumbnail Image -->
				<div class="media-item span3">
					<?php if ($canEdit || $canDelete): ?>
					<!-- Actions -->
					<ul class="media-nav">
						<li><a href="#" class="pagenav-manage"><?php echo JText::_('COM_HWDMS_MANAGE'); ?> </a>
							<ul class="media-subnav">
								<?php if ($canEdit) : ?>
								<li><?php echo JHtml::_('hwdicon.edit', 'category', $this->category, $this->params); ?></li>
								<?php endif; ?>
								<?php if ($canEditState) : ?>
								<?php if ($this->category->published != '1') : ?>
								<li><?php echo JHtml::_('hwdicon.publish', 'category', $this->category, $this->params); ?></li>
								<?php else : ?>
								<li><?php echo JHtml::_('hwdicon.unpublish', 'category', $this->category, $this->params); ?></li>
								<?php endif; ?>
								<?php endif; ?>
								<?php if ($canDelete) : ?>
								<li><?php echo JHtml::_('hwdicon.delete', 'category', $this->category, $this->params); ?></li>
								<?php endif; ?>
							</ul>
						</li>
					</ul>
					<?php endif; ?>
					<!-- Media Type -->
					<div class="media-item-format-6"> <img src="<?php echo JHtml::_('hwdicon.overlay', 6); ?>" alt="<?php echo JText::_('COM_HWDMS_CATEGORY'); ?>" /> </div>
					<div class="thumbnail"> <img src="<?php echo JRoute::_(hwdMediaShareDownloads::thumbnail($this->category, 6)); ?>" border="0" alt="<?php echo $this->escape($this->category->title); ?>" style="width:120px;" /> </div>
				</div>
				<div class="span3">
					<ul class="unstyled">
						<li class="article-info-term"><?php echo JText::_('COM_HWDMS_DETAILS'); ?> </li>
						<li class="media-info-count"><?php echo JText::_('COM_HWDMS_MEDIA'); ?> (<?php echo count($this->items); ?>)</li>
						<li class="media-info-count"><?php echo JText::_('COM_HWDMS_SUBCATEGORIES'); ?> (<?php echo count($this->subcategories); ?>)</li>
						<li class="media-info-hits"> <?php echo JText::_('COM_HWDMS_VIEWS'); ?> (<?php echo (int) $this->category->hits; ?>)</li>
						<li class="media-info-report"> <a title="<?php echo JText::_('COM_HWDMS_REPORT'); ?>" href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&task=categoryform.report&id=' . $this->category->id . '&return=' . $this->return . '&tmpl=component'); ?>" class="modal" rel="{handler: 'iframe', size: {x: 500, y: 250}}"><?php echo JText::_('COM_HWDMS_REPORT'); ?> </a> </li>
					</ul>
				</div>
				<div class="span6"> <?php echo JHtml::_('content.prepare', $this->category->description); ?> </div>
			</div>
			<div class="clear"></div>
			<!-- Search Filters -->
			<hr />
			<fieldset class="filters">
				<?php if ($this->params->get('global_list_filter_search') != 'hide') :?>
				<input type="text" name="filter_search" class="span2" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_HWDMS_SEARCH_IN_TITLE'); ?>" />
				<button type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
				<button type="button" class="btn" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
				<?php endif; ?>
				<?php if ($this->params->get('global_list_filter_pagination') != 'hide') : ?>
				<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160; <?php echo $this->pagination->getLimitBox(); ?>
				<?php endif; ?>
				<?php if ($this->display != 'list') :?>
				<?php if ($this->params->get('global_list_filter_ordering') != 'hide') :?>
				<label class="filter-order-lbl" for="filter_order"><?php echo JText::_('COM_HWDMS_ORDER'); ?></label>
				<select onchange="this.form.submit()" size="1" class="inputbox span1" name="filter_order" id="filter_order">
					<?php if ($this->params->get('global_list_meta_title') != 'hide') :?>
					<option value="a.title"<?php echo ($listOrder == 'a.title' ? ' selected="selected"' : false); ?>><?php echo JText::_( 'COM_HWDMS_TITLE' ); ?></option>
					<?php endif; ?>
					<?php if ($this->params->get('global_list_meta_likes') != 'hide') :?>
					<option value="a.likes"<?php echo ($listOrder == 'a.likes' ? ' selected="selected"' : false); ?>><?php echo JText::_( 'COM_HWDMS_LIKES' ); ?></option>
					<?php endif; ?>
					<?php if ($this->params->get('global_list_meta_likes') != 'hide') :?>
					<option value="a.dislikes"<?php echo ($listOrder == 'a.dislikes' ? ' selected="selected"' : false); ?>><?php echo JText::_( 'COM_HWDMS_DISLIKES' ); ?></option>
					<?php endif; ?>
					<?php if ($this->params->get('global_list_meta_created') != 'hide') :?>
					<option value="a.created"<?php echo ($listOrder == 'a.created' ? ' selected="selected"' : false); ?>><?php echo JText::_( 'COM_HWDMS_CREATED' ); ?></option>
					<?php endif; ?>
					<?php if ($this->params->get('global_list_meta_hits') != 'hide') :?>
					<option value="a.hits"<?php echo ($listOrder == 'a.hits' ? ' selected="selected"' : false); ?>><?php echo JText::_( 'JGLOBAL_HITS' ); ?></option>
					<?php endif; ?>
				</select>
				<?php endif; ?>
				<?php endif; ?>
				<?php if ($this->params->get('global_list_filter_media') != 'hide') :?>
				<select name="filter_mediaType" class="inputbox span1" onchange="this.form.submit()">
					<option value=""><?php echo JText::_('COM_HWDMS_LIST_SELECT_MEDIA_TYPE');?></option>
					<?php echo JHtml::_('select.options', $mediaSelectOptions, 'value', 'text', $this->state->get('filter.mediaType'), true);?>
				</select>
				<?php endif; ?>
				<!-- @TODO add hidden inputs -->
				<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
				<input type="hidden" name="limitstart" value="" />
			</fieldset>
			<div class="clear"></div>
		</div>
		<hr />
		<?php echo $this->loadTemplate($this->display); ?> 
		<!-- Pagination -->
		<div class="pagination"> <?php echo $this->pagination->getPagesLinks(); ?> </div>
	</div>
</form>
