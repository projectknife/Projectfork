<?php
/**
 * @version    SVN $Id: default.php 252 2012-03-11 11:21:36Z dhorsfall $
 * @package    hwdMediaShare
 * @copyright  Copyright (C) 2011 Highwood Design Limited. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 * @author     Dave Horsfall
 * @since      01-Dec-2011 09:58:16
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// load tooltip behavior
JHtml::_('behavior.tooltip');
JHtml::_('behavior.mootools');

$user = & JFactory::getUser();
$maxUpload = (int)$this->params->get('max_upload_filesize');
$maxPhpUpload = min(ini_get('post_max_size'),ini_get('upload_max_filesize'),$maxUpload);
?>

<form action="<?php echo JRoute::_('index.php?option=com_hwdmediashare&task=addmedia.upload'); ?>" method="post" name="adminForm" id="adminForm" class="formelm form-validate form-horizontal" enctype="multipart/form-data">
	<div id="hwd-container"> <a name="top" id="top"></a> 
		<!-- Media Navigation --> 
		<?php echo hwdMediaShareHelperNavigation::getInternalNavigation(); ?> 
		<!-- Media Header --> 
		<?php echo JHtml::_('sliders.start', 'media-upload-slider'); ?> <?php echo JHtml::_('sliders.panel', JText::sprintf( 'COM_HWDMS_UPLOAD_FILES_LESS_THAN_N_MB', $maxPhpUpload ), 'publishing');?>
		<fieldset class="adminform" id="hwd-upload-fallback">
			<div class="control-group">
				<label for="hwd-upload-photoupload"> <?php echo JText::_('COM_HWDMS_UPLOAD_A_FILE') ?> </label>
				<div class="controls">
					<input type="file" name="Filedata" />
					<input type="hidden" name="fallback" value="true11" />
				</div>
			</div>
			<div class="form-actions">
				<button type="button" class="btn" onclick="Joomla.submitbutton('addmedia.upload')"> <?php echo JText::_('COM_HWDMS_UPLOAD') ?> </button>
			</div>
		</fieldset>
		<?php if ($this->params->get('upload_tool_fancy') == 1) : ?>
		<div id="hwd-upload-status" class="hide">
			<p> <a href="#" id="hwd-upload-browse" class="btn"><?php echo JText::_('COM_HWDMS_BROWSE_FILES'); ?></a> <a href="#" id="hwd-upload-clear" class="btn"><?php echo JText::_('COM_HWDMS_CLEAR_LIST'); ?></a> <a href="#" id="hwd-upload-upload" class="btn"><?php echo JText::_('COM_HWDMS_START_UPLOAD'); ?></a> </p>
			<div> <span class="overall-title"></span> <img src="<?php echo JURI::root(true); ?>/media/com_hwdmediashare/assets/images/ajaxupload/progress-bar/bar.gif" class="progress overall-progress" /> </div>
			<div class="clr"></div>
			<div> <span class="current-title"></span> <img src="<?php echo JURI::root(true); ?>/media/com_hwdmediashare/assets/images/ajaxupload/progress-bar/bar.gif" class="progress current-progress" /> </div>
			<div class="current-text"></div>
		</div>
		<ul id="hwd-upload-list">
		</ul>
		<?php endif; ?>
		<?php if ($this->params->get('upload_tool_perl') == 1) : ?>
		<?php echo JHtml::_('sliders.panel', JText::sprintf( 'COM_HWDMS_UPLOAD_LARGE_FILES_UP_TO_N_MB', $maxUpload ), 'large');?> <?php echo $this->uberUploadHtml; ?>
		<?php endif; ?>
		<?php echo JHtml::_('sliders.panel', JText::_( 'COM_HWDMS_ADD_REMOTE_MEDIA' ), 'large');?>
		<fieldset class="adminform" style="padding-top:20px;">
			<?php foreach($this->form->getFieldset('remote') as $field): ?>
			<div class="control-group"> <?php echo $field->label; ?>
				<div class="controls"> <?php echo $field->input;?> </div>
			</div>
			<?php endforeach; ?>
			<div class="form-actions">
				<button type="button" class="btn" onclick="Joomla.submitbutton('addmedia.remote')"> <?php echo JText::_('COM_HWDMS_ADD') ?> </button>
			</div>
		</fieldset>
		<?php echo JHtml::_('sliders.end'); ?> <?php echo JHtml::_('sliders.start', 'media-params-slider'); ?> <?php echo JHtml::_('sliders.panel', JText::_( 'COM_HWDMS_ASSOCIATIONS' ), 'publishing');?>
		<fieldset class="adminform" style="padding-top:20px;">
			<legend><?php echo JText::_('COM_HWDMS_ADD_YOUR_UPLOADS_TO_ITEMS'); ?></legend>
			<div class="control-group"> <?php echo $this->form->getLabel('catid'); ?>
				<div class="controls"><?php echo $this->form->getInput('catid'); ?> </div>
			</div>
			<div class="control-group"> <?php echo $this->form->getLabel('album_id'); ?>
				<div class="controls"><?php echo $this->form->getInput('album_id'); ?> </div>
			</div>
			<div class="control-group"> <?php echo $this->form->getLabel('playlist_id'); ?>
				<div class="controls"><?php echo $this->form->getInput('playlist_id'); ?> </div>
			</div>
			<div class="control-group"> <?php echo $this->form->getLabel('group_id'); ?>
				<div class="controls"><?php echo $this->form->getInput('group_id'); ?> </div>
			</div>
		</fieldset>
		<?php echo JHtml::_('sliders.end'); ?>
		<div class="clear"> </div>
		<div>
			<input type="hidden" name="tmpl" value="<?php echo $this->template; ?>" />
			<input type="hidden" name="task" value="addmedia.upload" />
			<?php //echo JHtml::_('form.token'); ?>
		</div>
	</div>
</form>
