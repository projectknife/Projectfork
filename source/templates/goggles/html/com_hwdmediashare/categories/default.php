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

?>

<form action="<?php echo htmlspecialchars(JFactory::getURI()->toString()); ?>" method="post" name="adminForm" id="adminForm" class="form-inline">
	<div id="hwd-container"> <a name="top" id="top"></a> 
		<!-- Media Navigation --> 
		<?php echo hwdMediaShareHelperNavigation::getInternalNavigation(); ?> 
		<!-- Media Header -->
		<div class="media-header">
			<div class="pull-right btn-group"> <a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getSelfRoute('details')); ?>" class="btn" title="<?php echo JText::_('COM_HWDMS_DETAILS'); ?>"><i class="icon-file"></i> <?php echo JText::_('COM_HWDMS_DETAILS'); ?></a> <a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getSelfRoute('tree')); ?>" class="btn" title="<?php echo JText::_('COM_HWDMS_TREE'); ?>"><i class="icon-th-list"></i> <?php echo JText::_('COM_HWDMS_TREE'); ?></a> </div>
			<div class="page-header">
				<h2><?php echo JText::_('COM_HWDMS_CATEGORIES'); ?></h2>
			</div>
			<div class="clear"></div>
			<?php if ($this->params->get('category_list_quick_view') != 'hide' && count($this->items[$this->parent->id]) != 0) :?>
			<?php  echo JHtml::_('sliders.start', 'media-category-slider', array('startOffset' => 1)); ?>
			<?php  echo JHtml::_('sliders.panel',JText::_('COM_HWDMS_CATEGORY_QUICK_VIEW'), 'quick-view'); ?>
			<div class="media-categories-lists"> <?php echo $this->loadTemplate('list'); ?>
				<div class="clear"></div>
			</div>
			<?php echo JHtml::_('sliders.end'); ?>
			<div class="clear"></div>
			<?php endif; ?>
			<!-- Search Filters -->
			<fieldset class="filters">
				<?php if ($this->display != 'list') :?>
				<?php if ($this->params->get('global_list_filter_ordering') != 'hide') :?>
				<label class="filter-order-lbl" for="filter_order"><?php echo JText::_('COM_HWDMS_ORDER'); ?></label>
				<select onchange="this.form.submit()" size="1" class="inputbox" name="filter_order" id="filter_order">
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
				<!-- @TODO add hidden inputs -->
				<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
				<input type="hidden" name="limitstart" value="" />
			</fieldset>
		</div>
		<hr />
		<?php echo $this->loadTemplate($this->display); ?> </div>
</form>
