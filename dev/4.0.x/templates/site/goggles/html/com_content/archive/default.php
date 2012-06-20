<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
?>

<div class="archive<?php echo $this->pageclass_sfx;?> row-fluid">
<?php if ($this->params->get('show_page_heading', 1)) : ?>
<div class="page-header">
	<h1> <?php echo $this->escape($this->params->get('page_heading')); ?> </h1>
</div>
<?php endif; ?>
<form id="adminForm" action="<?php echo JRoute::_('index.php')?>" method="post" class="form-horizontal">
	<fieldset class="filters">
		<legend class="hidelabeltxt"><?php echo JText::_('JGLOBAL_FILTER_LABEL'); ?></legend>
		<?php if ($this->params->get('filter_field') != 'hide') : ?>
		<div class="filter-search control-group">
			<label class="filter-search-lbl control-label" for="filter-search"> <?php echo JText::_('COM_CONTENT_'.$this->params->get('filter_field').'_FILTER_LABEL').'&#160;'; ?> </label>
			<div class="controls">
				<input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->filter); ?>" class="inputbox" onchange="document.getElementById('adminForm').submit();" />
			</div>
		</div>
		<?php endif; ?>
		<div class="filter-search control-group">
			<div class="controls"> <?php echo $this->form->monthField; ?> </div>
		</div>
		<div class="filter-search control-group">
			<div class="controls"> <?php echo $this->form->yearField; ?> </div>
		</div>
		<div class="filter-search control-group">
			<div class="controls"> <?php echo $this->form->limitField; ?> </div>
		</div>
		<div class="form-actions">
			<button type="submit" class="button btn btn-primary"><?php echo JText::_('JGLOBAL_FILTER_BUTTON'); ?></button>
		</div>
		</div>
		<input type="hidden" name="view" value="archive" />
		<input type="hidden" name="option" value="com_content" />
		<input type="hidden" name="limitstart" value="0" />
	</fieldset>
	<?php echo $this->loadTemplate('items'); ?>
</form>
</div>
