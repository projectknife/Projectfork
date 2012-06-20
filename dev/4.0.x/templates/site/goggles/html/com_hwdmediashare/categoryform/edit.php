<?php
/**
 * @version    SVN $Id: edit.php 225 2012-03-02 10:10:01Z dhorsfall $
 * @package    hwdMediaShare
 * @copyright  Copyright (C) 2011 Highwood Design Limited. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 * @author     Dave Horsfall
 * @since      26-Nov-2011 12:05:23
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
					<h2><?php echo JText::sprintf( 'COM_HWDMS_EDIT_CATEGORYX', $this->escape($this->item->title)); ?></h2>
				</div>
				<div class="clear"></div>
			</div>
			<!-- Form -->
			<fieldset>
				<legend><?php echo JText::_('JEDITOR'); ?></legend>
				<div class="control-group"> <?php echo $this->form->getLabel('title'); ?>
					<div class="controls"> <?php echo $this->form->getInput('title'); ?> </div>
				</div>
				<?php if (is_null($this->item->id)):?>
				<div class="formelm"> <?php echo $this->form->getLabel('alias'); ?> <?php echo $this->form->getInput('alias'); ?> </div>
				<?php endif; ?>
				<div class="form-actions">
					<button type="button" class="btn" onclick="Joomla.submitbutton('categoryform.save')"> <?php echo JText::_('JSAVE') ?> </button>
					<button type="button" class="btn" onclick="Joomla.submitbutton('categoryform.cancel')"> <?php echo JText::_('JCANCEL') ?> </button>
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
				<?php endif; ?>
				<div class="control-group"> <?php echo $this->form->getLabel('access'); ?>
					<div class="controls"><?php echo $this->form->getInput('access'); ?> </div>
				</div>
				<div class="control-group"> <?php echo $this->form->getLabel('language'); ?>
					<div class="controls"><?php echo $this->form->getInput('language'); ?> </div>
				</div>
			</fieldset>
			<!-- Meta -->
			<fieldset>
				<legend><?php echo JText::_('COM_HWDMS_METADATA'); ?></legend>
				<div class="control-group"> <?php echo $this->form->getLabel('metadesc'); ?>
					<div class="controls"><?php echo $this->form->getInput('metadesc'); ?> </div>
				</div>
				<div class="control-group"> <?php echo $this->form->getLabel('metakey'); ?>
					<div class="controls"><?php echo $this->form->getInput('metakey'); ?> </div>
				</div>
				<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
				<input type="hidden" name="task" value="" />
				<input type="hidden" name="return" value="<?php echo $this->return_page;?>" />
				<?php echo JHtml::_( 'form.token' ); ?>
			</fieldset>
			<div class="form-actions">
				<button type="button" class="btn" onclick="Joomla.submitbutton('categoryform.save')"> <?php echo JText::_('JSAVE') ?> </button>
				<button type="button" class="btn" onclick="Joomla.submitbutton('categoryform.cancel')"> <?php echo JText::_('JCANCEL') ?> </button>
			</div>
		</div>
	</form>
</div>
