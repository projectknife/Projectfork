<?php
/**
 * @version    SVN $Id: groups.php 222 2012-03-01 11:56:11Z dhorsfall $
 * @package    hwdMediaShare
 * @copyright  Copyright (C) 2012 Highwood Design Limited. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 * @author     Dave Horsfall
 * @since      18-Jan-2012 17:45:17
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
?>

<form action="<?php echo htmlspecialchars(JFactory::getURI()->toString()); ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-inline">
	<div id="hwd-container"> <a name="top" id="top"></a> 
		<!-- Media Navigation --> 
		<?php echo hwdMediaShareHelperNavigation::getInternalNavigation(); ?> <?php echo hwdMediaShareHelperNavigation::getAccountNavigation(); ?> 
		<!-- Media Header -->
		<div class="media-header">
			<div class="page-header">
				<h2><?php echo JText::_('COM_HWDMS_MY_GROUPS'); ?></h2>
			</div>
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
				<input type="hidden" name="boxchecked" value="0" />
				<input type="hidden" name="task" value="" />
				<input type="hidden" name="view" value="" />
				<input type="hidden" name="return" value="<?php echo $this->return; ?>" />
			</fieldset>
		</div>
		<hr />
		<?php echo $this->loadTemplate('list'); ?>
		<div class="form-actions">
			<button type="button" class="btn" onclick="Joomla.submitbutton('groups.delete')"><?php echo JText::_('COM_HWDMS_REMOVE'); ?></button>
			<button type="button" class="btn" onclick="Joomla.submitbutton('groups.publish')"><?php echo JText::_('COM_HWDMS_PUBLISH'); ?></button>
			<button type="button" class="btn" onclick="Joomla.submitbutton('groups.unpublish')"><?php echo JText::_('COM_HWDMS_UNPUBLISH'); ?></button>
		</div>
		<!-- Pagination -->
		<div class="pagination"> <?php echo $this->pagination->getPagesLinks(); ?> </div>
	</div>
</form>
