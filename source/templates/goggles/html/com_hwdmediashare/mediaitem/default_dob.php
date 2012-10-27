<?php
/**
 * @version    SVN $Id: default_dob.php 269 2012-03-22 10:07:58Z dhorsfall $
 * @package    hwdMediaShare
 * @copyright  Copyright (C) 2012 Highwood Design Limited. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 * @author     Dave Horsfall
 * @since      27-Feb-2012 22:02:13
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.framework', true);

?>

<div class="edit">
	<form action="<?php echo htmlspecialchars(JFactory::getURI()->toString()); ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal" autocomplete="off">
		<div id="hwd-container">
		<a name="top" id="top" title="top"></a> <?php echo hwdMediaShareHelperNavigation::getInternalNavigation(); ?> 
		<!-- Media Header -->
		<div class="media-header">
			<div class="page-header">
				<h2><?php echo JText::_('COM_HWDMS_AGE_RESTRICTED_MEDIA'); ?></h2>
			</div>
			<div class="clear"></div>
		</div>
		<div id="media-item-container" class="media-item-container">
			<p><?php echo JText::sprintf( 'COM_HWDMS_YOU_MUST_BE_OVER_X_YEARS', $this->item->params->get('age')); ?></p>
			<fieldset>
				<div class="control-group">
					<label id="jform_dob-lbl" for="jform_dob" class="hasTip required" title="<?php echo JText::_('COM_HWDMS_DOB_LABEL'); ?>::<?php echo JText::_('COM_HWDMS_DOB_DESC'); ?>"><?php echo JText::_('COM_HWDMS_DOB_LABEL'); ?><span class="star">&#160;*</span></label>
					<div class="controls">
					<?php echo JHtml::_('calendar', $this->dob, "jform[dob]", "jform_dob", '%Y-%m-%d'); ?> </div>
					</div>
				<div class="form-actions">
					<button type="button" class="btn" onclick="Joomla.submitbutton('mediaitem.dob')"><?php echo JText::_('COM_HWDMS_SUBMIT'); ?></button>
				</div>
			</fieldset>
			<div class="clear"></div>
		</div>
		<div class="clear"></div>
		<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="return" value="<?php echo $this->return;?>" />
		<?php echo JHtml::_( 'form.token' ); ?>
	</form>
</div>
