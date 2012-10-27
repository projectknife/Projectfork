<?php
/**
 * @version    SVN $Id: edit.php 221 2012-03-01 11:35:20Z dhorsfall $
 * @package    hwdMediaShare
 * @copyright  Copyright (C) 2011 Highwood Design Limited. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 * @author     Dave Horsfall
 * @since      26-Nov-2011 12:06:48
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
					<h2><?php echo JText::sprintf( 'COM_HWDMS_EDIT_MEDIAX', $this->escape($this->item->title)); ?></h2>
				</div>
			</div>
			<!-- Form -->
			<fieldset>
				<legend><?php echo JText::_('JEDITOR'); ?></legend>
				<div class="control-group"> <?php echo $this->form->getLabel('title'); ?>
					<div class="controls"><?php echo $this->form->getInput('title'); ?> </div>
				</div>
				<?php if (is_null($this->item->id)):?>
				<div class="control-group"> <?php echo $this->form->getLabel('alias'); ?>
					<div class="controls"><?php echo $this->form->getInput('alias'); ?> </div>
				</div>
				<?php endif; ?>
				<div class="form-actions">
					<button type="button" class="btn" onclick="Joomla.submitbutton('mediaform.save')"> <?php echo JText::_('JSAVE') ?> </button>
					<button type="button" class="btn" onclick="Joomla.submitbutton('mediaform.cancel')"> <?php echo JText::_('JCANCEL') ?> </button>
				</div>
				<?php echo $this->form->getInput('description'); ?>
			</fieldset>
			<!-- Publishing -->
			<fieldset>
				<legend><?php echo JText::_('COM_HWDMS_PUBLISHING'); ?></legend>
				<div class="control-group"> <?php echo $this->form->getLabel('catid'); ?>
					<?php if($this->params->get('enable_category', 0) == 1) : ?>
					<label><?php echo $this->category_title; ?></label>
					<?php else : ?>
					<div class="controls">
						<?php   echo $this->form->getInput('catid'); ?>
					</div>
					<?php endif;?>
				</div>
				<div class="control-group"> <?php echo $this->form->getLabel('tags'); ?>
					<?php if($this->params->get('enable_category', 0) == 1) : ?>
					<label><?php echo $this->tags; ?></label>
					<?php else : ?>
					<div class="controls">
						<?php   echo $this->form->getInput('tags'); ?>
					</div>
					<?php endif;?>
				</div>
				<?php if ($this->item->params->get('access-change')): ?>
				<div class="control-group"> <?php echo $this->form->getLabel('published'); ?>
					<div class="controls"> <?php echo $this->form->getInput('published'); ?> </div>
				</div>
				<div class="control-group"> <?php echo $this->form->getLabel('featured'); ?>
					<div class="controls"><?php echo $this->form->getInput('featured'); ?> </div>
				</div>
				<div class="control-group"> <?php echo $this->form->getLabel('publish_up'); ?>
					<div class="controls"><?php echo $this->form->getInput('publish_up'); ?> </div>
				</div>
				<div class="control-group"> <?php echo $this->form->getLabel('publish_down'); ?>
					<div class="controls"> <?php echo $this->form->getInput('publish_down'); ?> </div>
				</div>
				<?php endif; ?>
				<div class="control-group"> <?php echo $this->form->getLabel('access'); ?>
					<div class="controls"><?php echo $this->form->getInput('access'); ?> </div>
				</div>
				<div class="control-group"> <?php echo $this->form->getLabel('language'); ?>
					<div class="controls"> <?php echo $this->form->getInput('language'); ?> </div>
				</div>
				<div class="control-group"> <?php echo $this->form->getLabel('created_by_alias'); ?>
					<div class="controls"><?php echo $this->form->getInput('created_by_alias'); ?> </div>
				</div>
				<?php if (is_null($this->item->id)):?>
				<div class="form-note">
					<p><?php echo JText::_('COM_CONTENT_ORDERING'); ?></p>
				</div>
				<?php endif; ?>
			</fieldset>
			<!-- Custom -->
			<?php foreach ($this->item->customfields['fields'] as $group => $groupFields) : ?>
			<fieldset class="adminform">
				<legend><?php echo JText::_( $group ); ?></legend>
				<div class="control-group">
					<?php foreach ($groupFields as $field) :
        $field	= JArrayHelper::toObject ( $field );
        $field->value	= $this->escape( $field->value );
        ?>
					<label title="" class="hasTip" for="jform_<?php echo $field->id;?>" id="jform_<?php echo $field->id;?>-lbl"><?php echo JText::_( $field->name );?>
						<?php if($field->required == 1) echo '<span class="star">&nbsp;*</span>'; ?>
					</label>
					<div class="controls"> <?php echo hwdMediaShareCustomFields::getFieldHTML( $field , '' ); ?> </div>
					<?php endforeach; ?>
				</div>
			</fieldset>
			<?php endforeach; ?>
			<!-- Meta -->
			<fieldset>
				<legend><?php echo JText::_('COM_HWDMS_METADATA'); ?></legend>
				<div class="control-group"> <?php echo $this->form->getLabel('metadesc'); ?>
					<div class="controls"><?php echo $this->form->getInput('metadesc'); ?> </div>
				</div>
				<div class="control-group"> <?php echo $this->form->getLabel('metakey'); ?>
					<div class="controls"> <?php echo $this->form->getInput('metakey'); ?> </div>
				</div>
				<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
				<input type="hidden" name="task" value="" />
				<input type="hidden" name="return" value="<?php echo $this->return_page;?>" />
				<?php if($this->params->get('enable_category', 0) == 1) :?>
				<input type="hidden" name="jform[catid]" value="<?php echo $this->params->get('catid', 1);?>"/>
				<?php endif;?>
				<?php echo JHtml::_( 'form.token' ); ?>
				<div class="form-actions">
					<button type="button" class="btn" onclick="Joomla.submitbutton('mediaform.save')"> <?php echo JText::_('JSAVE') ?> </button>
					<button type="button" class="btn" onclick="Joomla.submitbutton('mediaform.cancel')"> <?php echo JText::_('JCANCEL') ?> </button>
				</div>
			</fieldset>
		</div>
	</form>
</div>
