<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_weblinks
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

// Create shortcut to parameters.
$params = $this->state->get('params');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'weblink.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
			<?php echo $this->form->getField('description')->save(); ?>
			Joomla.submitform(task);
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<div class="edit<?php echo $this->pageclass_sfx; ?>">
	<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<div class="page-header">
		<h1> <?php echo $this->escape($this->params->get('page_heading')); ?> </h1>
	</div>
	<?php endif; ?>
	<form action="<?php echo JRoute::_('index.php?option=com_weblinks&view=form&w_id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal">
		<fieldset>
			<legend><?php echo JText::_('COM_WEBLINKS_LINK'); ?></legend>
			<div class="formelm control-group"> <?php echo $this->form->getLabel('title'); ?>
				<div class="controls"><?php echo $this->form->getInput('title'); ?> </div>
			</div>
			<div class="formelm control-group"> <?php echo $this->form->getLabel('alias'); ?>
				<div class="controls"><?php echo $this->form->getInput('alias'); ?> </div>
			</div>
			<div class="formelm control-group"> <?php echo $this->form->getLabel('catid'); ?>
				<div class="controls"><?php echo $this->form->getInput('catid'); ?> </div>
			</div>
			<div class="formelm control-group"> <?php echo $this->form->getLabel('url'); ?>
				<div class="controls"><?php echo $this->form->getInput('url'); ?> </div>
			</div>
			<?php if ($this->user->authorise('core.edit.state', 'com_weblinks.weblink')): ?>
			<div class="formelm control-group"> <?php echo $this->form->getLabel('state'); ?>
				<div class="controls"> <?php echo $this->form->getInput('state'); ?> </div>
			</div>
			<?php endif; ?>
			<div class="formelm control-group"> <?php echo $this->form->getLabel('language'); ?>
				<div class="controls"><?php echo $this->form->getInput('language'); ?> </div>
			</div>
			<div class="formelm-buttons form-actions">
				<button type="button" onclick="Joomla.submitbutton('weblink.save')" class="btn btn-primary"> <?php echo JText::_('JSAVE') ?> </button>
				<button type="button" onclick="Joomla.submitbutton('weblink.cancel')" class="btn"> <?php echo JText::_('JCANCEL') ?> </button>
			</div>
			<div class="row-fluid"> <?php echo $this->form->getLabel('description'); ?> <?php echo $this->form->getInput('description'); ?> </div>
		</fieldset>
		<input type="hidden" name="return" value="<?php echo $this->return_page;?>" />
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_( 'form.token' ); ?>
	</form>
</div>
