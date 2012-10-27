<?php
/**
 * @version    SVN $Id: default.php 269 2012-03-22 10:07:58Z dhorsfall $
 * @package    hwdMediaShare
 * @copyright  Copyright (C) 2011 Highwood Design Limited. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 * @author     Dave Horsfall
 * @since      25-Nov-2011 17:33:20
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.calendar');
JHtml::_('behavior.formvalidation');

$lang = JFactory::getLanguage();
$upper_limit = $lang->getUpperLimitSearchWord();
$paramsSet = $this->form->getFieldsets('params');
$elementSet = array('media' => 1, 'albums' => 2, 'groups' => 3,'playlists' => 4, 'users' => 5);

?>

<div class="search">
	<form id="searchForm" class="form-horizontal" action="<?php echo JRoute::_('index.php?option=com_hwdmediashare');?>" method="post">
		<div id="hwd-container"> <a name="top" id="top"></a> 
			<!-- Media Navigation --> 
			<?php echo hwdMediaShareHelperNavigation::getInternalNavigation(); ?> 
			<!-- Media Header -->
			<div class="media-header">
				<div class="page-header">
					<h2><?php echo JText::_('COM_HWDMS_SEARCH'); ?></h2>
				</div>
				<div class="clear"></div>
			</div>
			
			<!-- Form -->
			<fieldset class="word">
				<div class="control-group">
					<label for="search-searchword"> <?php echo JText::_('COM_SEARCH_SEARCH_KEYWORD'); ?> </label>
					<div class="controls">
						<input type="text" name="searchword" id="search-searchword" size="30" maxlength="<?php echo $upper_limit; ?>" value="<?php echo $this->escape($this->origkeyword); ?>" class="inputbox" />
					</div>
				</div>
				<div class="form-actions">
					<button name="Search" onclick="this.form.submit()" class="btn"><?php echo JText::_('COM_SEARCH_SEARCH');?></button>
				</div>
				<input type="hidden" name="task" value="search" />
			</fieldset>
			<div class="alert alert-info">
				<?php if (!empty($this->searchword)):?>
				<?php echo JText::plural('COM_SEARCH_SEARCH_KEYWORD_N_RESULTS', $this->total);?>
				<?php endif;?>
			</div>
			<fieldset class="phrases" style="padding-top:20px;">
				<legend><?php echo JText::_('COM_SEARCH_FOR');?></legend>
				<div class="form-inline"> <?php echo $this->lists['searchphrase']; ?>
					<label for="ordering" class="ordering"> <?php echo JText::_('COM_SEARCH_ORDERING');?> </label>
					<?php echo $this->lists['ordering'];?> </div>
			</fieldset>
			<div class="form-inline">
				<fieldset class="only" style="padding-top:20px;">
					<legend><?php echo JText::_('COM_SEARCH_SEARCH_ONLY');?></legend>
					<?php foreach ($this->searchareas['search'] as $val => $txt) :
        $checked = is_array($this->searchareas['active']) && in_array($val, $this->searchareas['active']) ? 'checked="checked"' : '';
        ?>
					<input type="radio" name="areas[]" value="<?php echo $val;?>" id="area-<?php echo $val;?>" <?php echo $checked;?> />
					<label for="area-<?php echo $val;?>"> <?php echo JText::_($txt); ?> </label>
					<?php endforeach; ?>
				</fieldset>
				<fieldset class="only">
					<?php echo $this->form->getLabel('catid'); ?> <?php echo $this->form->getInput('catid'); ?> <?php echo $this->form->getLabel('tag'); ?> <?php echo $this->form->getInput('tag'); ?>
				</fieldset>
				<?php foreach ($this->searchareas['search'] as $val => $txt) : ?>
				<?php hwdMediaShareFactory::load('customfields');
        $customfields = hwdMediaShareCustomFields::get(null, $elementSet[$val]); ?>
				<?php foreach ($customfields['fields'] as $group => $groupFields) : ?>
				<fieldset id="<?php echo $val;?>-search-fieldset" class="only <?php echo $val;?>-search-fieldset">
					<legend><?php echo JText::_('COM_HWDMS_'.$txt); ?></legend>
					<?php foreach ($groupFields as $field) : ?>
					<?php $field = JArrayHelper::toObject ( $field );                 
            $field->value = JRequest::getVar('field28'); ?>
					<?php if ($field->searchable) : ?>
					<label title="" class="hasTip" for="jform_<?php echo $field->id;?>" id="jform_<?php echo $field->id;?>-lbl"><?php echo JText::_( $field->name );?></label>
					<?php echo hwdMediaShareCustomFields::getFieldHTML( $field , '' ); ?>
					<?php endif; ?>
					<?php endforeach; ?>
				</fieldset>
				<?php endforeach; ?>
				<?php endforeach; ?>
			</div>
			<?php if ($this->total > 0) : ?>
			<div class="form-limit">
				<label for="limit"> <?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?> </label>
				<?php echo $this->pagination->getLimitBox(); ?> </div>
			<p class="counter"> <?php echo $this->pagination->getPagesCounter(); ?> </p>
			<?php endif; ?>
			<?php 
    if ($this->error==null && count($this->results) > 0) :
            echo $this->loadTemplate('results');
    else :
            echo $this->loadTemplate('error');
    endif; 
    ?>
		</div>
		<input type="hidden" name="task" value="search.search" />
	</form>
</div>
