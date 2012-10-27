<?php
/**
 * @version    SVN $Id: default_reply.php 127 2012-01-10 14:53:12Z dhorsfall $
 * @package    hwdMediaShare
 * @copyright  Copyright (C) 2012 Highwood Design Limited. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 * @author     Dave Horsfall
 * @since      10-Jan-2012 11:17:43
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.modal');
JHtml::_('behavior.framework', true);

?>

<div class="edit">
	<form action="<?php echo JRoute::_('index.php?option=com_hwdmediashare'); ?>" method="post" id="adminForm" class="form-horizontal">
		<fieldset>
			<legend><?php echo JText::_( 'COM_HWDMS_WRITE_A_REPLY' ); ?></legend>
			<div class="control-group">
				<?php //echo $this->form->getLabel('comment'); ?>
				<div class="controls"><?php echo $this->form->getInput('comment'); ?> </div>
			</div>
		</fieldset>
		<div class="form-actions">
			<button onclick="Joomla.submitbutton('activity.reply')" type="button">Reply</button>
			<button onclick="window.parent.SqueezeBox.close();" type="button">Cancel</button>
		</div>
		<div>
			<input type="hidden" name="tmpl" value="component" />
			<input type="hidden" name="element_type" value="<?php echo $this->element_type; ?>" />
			<input type="hidden" name="element_id" value="<?php echo $this->element_id; ?>" />
			<input type="hidden" name="reply_id" value="<?php echo $this->reply_id; ?>" />
			<input type="hidden" name="task" value="activity.reply" />
			<?php echo JHtml::_('form.token'); ?> </div>
	</form>
</div>
