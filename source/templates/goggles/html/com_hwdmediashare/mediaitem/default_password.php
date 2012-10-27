<?php
/**
 * @version    SVN $Id: default_password.php 234 2012-03-06 10:41:48Z dhorsfall $
 * @package    hwdMediaShare
 * @copyright  Copyright (C) 2012 Highwood Design Limited. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 * @author     Dave Horsfall
 * @since      27-Feb-2012 19:57:45
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

?>

<div class="edit">
	<form action="<?php echo htmlspecialchars(JFactory::getURI()->toString()); ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal" autocomplete="off">
		<div id="hwd-container">
		<a name="top" id="top" title="top"></a> <?php echo hwdMediaShareHelperNavigation::getInternalNavigation(); ?> 
		<!-- Media Header -->
		<div class="media-header">
			<div class="page-header">
			<h2><?php echo JText::_('COM_HWDMS_THIS_IS_A_PASSWORD_PROTECTED_MEDIA'); ?></h2>
			</div>
			<div class="clear"></div>
		</div>
		<div id="media-item-container" class="media-item-container">
			<p><?php echo JText::_('COM_HWDMS_DO_YOU_HAVE_THE_PASSWORD'); ?></p>
			<fieldset>
				<div class="control-group">
					<label id="jform_password-lbl" for="jform_password" class="hasTip required" title="<?php echo JText::_('COM_HWDMS_PASSWORD_LABEL'); ?>::<?php echo JText::_('COM_HWDMS_PASSWORD_DESC'); ?>"><?php echo JText::_('COM_HWDMS_PASSWORD_LABEL'); ?><span class="star">&#160;*</span></label>
					<div class="controls">
					<input type="password" name="jform[password]" id="jform_password" value="" class="inputbox required" size="40"/>
					</div>
				</div>
				<div class="form-actions">
					<button type="button" class="btn" onclick="Joomla.submitbutton('mediaitem.password')"><?php echo JText::_('COM_HWDMS_SUBMIT'); ?></button>
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
