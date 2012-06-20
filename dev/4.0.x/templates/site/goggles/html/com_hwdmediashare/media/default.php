<?php
/**
 * @version    SVN $Id: default.php 224 2012-03-01 22:09:22Z dhorsfall $
 * @package    hwdMediaShare
 * @copyright  Copyright (C) 2011 Highwood Design Limited. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 * @author     Dave Horsfall
 * @since      09-Nov-2011 14:02:20
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$mediaSelectOptions = array("1" => "COM_HWDMS_AUDIO", "2" => "COM_HWDMS_DOCUMENT", "3" => "COM_HWDMS_IMAGE", "4" => "COM_HWDMS_VIDEO");
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
?>

<form action="<?php echo htmlspecialchars(JFactory::getURI()->toString()); ?>" method="post" name="adminForm" id="adminForm">
	<div id="hwd-container"> <a name="top" id="top"></a> 
		<!-- Media Navigation --> 
		<?php echo hwdMediaShareHelperNavigation::getInternalNavigation(); ?> 
		<!-- Media Header -->
		<div class="media-header"> 
			<!-- View Type -->
			<div class="btn-group pull-right"> <a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getSelfRoute('details')); ?>" class="btn" title="<?php echo JText::_('COM_HWDMS_DETAILS'); ?>"> <i class="icon-file"></i> <?php echo JText::_('COM_HWDMS_DETAILS'); ?></a> <a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getMediaRoute(array('display'=>'gallery'))); ?>" class="btn" title="<?php echo JText::_('COM_HWDMS_GALLERY'); ?>"> <i class="icon-th"></i> <?php echo JText::_('COM_HWDMS_GALLERY'); ?></a> <a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getMediaRoute(array('display'=>'list'))); ?>" class="btn" title="<?php echo JText::_('COM_HWDMS_LIST'); ?>"> <i class="icon-list"></i> <?php echo JText::_('COM_HWDMS_LIST'); ?></a> </div>
			<div class="page-header">
				<h2><?php echo JText::_('COM_HWDMS_MEDIA'); ?></h2>
			</div>
			<div class="clear"></div>
			<!-- Search Filters -->
			<fieldset class="form-inline">
				<?php if ($this->params->get('global_list_filter_search') != 'hide') :?>
				<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
				<input type="text" name="filter_search" id="filter_search" class="input-small" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_HWDMS_SEARCH_IN_TITLE'); ?>" />
				<button type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
				<button type="button" class="btn" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
				<?php endif; ?>
				<?php if ($this->params->get('global_list_filter_pagination') != 'hide') : ?>
				<label><?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;</label>
				<?php echo $this->pagination->getLimitBox(); ?>
				<?php endif; ?>
				<?php if ($this->display != 'list' || $this->params->get('global_list_filter_ordering') != 'hide') :?>
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
				<?php else: ?>
				<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
				<?php endif; ?>
				<?php if ($this->params->get('global_list_filter_media') != 'hide') :?>
				<select name="filter_mediaType" class="inputbox span2" onchange="this.form.submit()">
					<option value=""><?php echo JText::_('COM_HWDMS_LIST_SELECT_MEDIA_TYPE');?></option>
					<?php echo JHtml::_('select.options', $mediaSelectOptions, 'value', 'text', $this->state->get('filter.mediaType'), true);?>
				</select>
				<?php endif; ?>
				<!-- @TODO add hidden inputs -->
				<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
				<input type="hidden" name="limitstart" value="" />
			</fieldset>
		</div>
		<hr />
		<?php echo $this->loadTemplate($this->display); ?> 
		<!-- Pagination -->
		<div class="pagination"> <?php echo $this->pagination->getPagesLinks(); ?> </div>
	</div>
</form>
