<?php
/**
 * @version    SVN $Id: edit.php 221 2012-03-01 11:35:20Z dhorsfall $
 * @package    hwdMediaShare
 * @copyright  Copyright (C) 2012 Highwood Design Limited. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 * @author     Dave Horsfall
 * @since      20-Jan-2012 09:00:25
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.calendar');
JHtml::_('behavior.formvalidation');

?>

<div class="edit">
	<form action="<?php echo htmlspecialchars(JFactory::getURI()->toString()); ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal">
		<div id="hwd-container"> <a name="top" id="top"></a> 
			<!-- Media Navigation --> 
			<?php echo hwdMediaShareHelperNavigation::getInternalNavigation(); ?> 
			<!-- Media Header -->
			<div class="media-header">
				<div class="page-header">
					<h2><?php echo JText::_('COM_HWDMS_EDIT_ACTIVITY'); ?></h2>
				</div>
				<div class="clear"></div>
			</div>
			<!-- Form -->
			<fieldset>
				<legend><?php echo JText::_('JEDITOR'); ?></legend>
				<div class="form-actions">
					<button type="button" onclick="Joomla.submitbutton('activityform.save')"> <?php echo JText::_('JSAVE') ?> </button>
					<button type="button" onclick="Joomla.submitbutton('activityform.cancel')"> <?php echo JText::_('JCANCEL') ?> </button>
				</div>
				<?php echo $this->form->getInput('description'); ?>
			</fieldset>
			<!-- Publishing -->
			<fieldset>
				<legend><?php echo JText::_('COM_HWDMS_PUBLISHING'); ?></legend>
				<?php if ($this->item->params->get('access-change')): ?>
				<div class="control-group"> <?php echo $this->form->getLabel('published'); ?>
					<div class="controls"><?php echo $this->form->getInput('published'); ?> </div>
				</div>
				<div class="control-group"> <?php echo $this->form->getLabel('featured'); ?>
					<div class="controls"><?php echo $this->form->getInput('featured'); ?> </div>
				</div>
				<div class="control-group"> <?php echo $this->form->getLabel('publish_up'); ?>
					<div class="controls"><?php echo $this->form->getInput('publish_up'); ?> </div>
				</div>
				<div class="control-group"> <?php echo $this->form->getLabel('publish_down'); ?>
					<div class="controls"><?php echo $this->form->getInput('publish_down'); ?> </div>
				</div>
				<?php endif; ?>
				<div class="control-group"> <?php echo $this->form->getLabel('access'); ?>
					<div class="controls"><?php echo $this->form->getInput('access'); ?> </div>
				</div>
				<div class="control-group"> <?php echo $this->form->getLabel('language'); ?>
					<div class="controls"><?php echo $this->form->getInput('language'); ?> </div>
				</div>
				<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
				<input type="hidden" name="task" value="" />
				<input type="hidden" name="return" value="<?php echo $this->return_page;?>" />
				<?php echo JHtml::_( 'form.token' ); ?>
				<div class="form-actions">
					<button type="button" onclick="Joomla.submitbutton('activityform.save')"> <?php echo JText::_('JSAVE') ?> </button>
					<button type="button" onclick="Joomla.submitbutton('activityform.cancel')"> <?php echo JText::_('JCANCEL') ?> </button>
				</div>
			</fieldset>
		</div>
	</form>
</div>
