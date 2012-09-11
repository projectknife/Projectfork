<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('projectfork.script.form');
?>
<script type="text/javascript">
PFform.accessAction();
Joomla.submitbutton = function(task)
{
    if (task == 'project.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
	    <?php echo $this->form->getField('description')->save(); ?>
        Joomla.submitform(task, document.getElementById('item-form'));
	}
    else {
	    alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
	}
}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_projectfork&view=project&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

    <div class="width-60 fltlft">
        <fieldset class="adminform">
			<legend><?php echo empty($this->item->id) ? JText::_('COM_PROJECTFORK_NEW_PROJECT') : JText::_('COM_PROJECTFORK_EDIT_PROJECT'); ?></legend>
			<ul class="adminformlist">
				<li><?php echo $this->form->getLabel('title').$this->form->getInput('title'); ?></li>
			</ul>
			<div class="clr"></div>
			<?php echo $this->form->getLabel('description'); ?>
			<div class="clr"></div>
			<?php echo $this->form->getInput('description'); ?>
		</fieldset>
    </div>

    <div class="width-40 fltrt">
        <?php echo JHtml::_('sliders.start','project-sliders-'.$this->item->id, array('useCookie'=>1)); ?>

            <?php echo JHtml::_('sliders.panel',JText::_('COM_PROJECTFORK_FIELDSET_PUBLISHING'), 'publishing-details'); ?>
            <fieldset class="panelform">
				<ul class="adminformlist">
					<li><?php echo $this->form->getLabel('catid').$this->form->getInput('catid'); ?></li>
                    <li><?php echo $this->form->getLabel('created_by').$this->form->getInput('created_by'); ?></li>
                    <li><?php echo $this->form->getLabel('state').$this->form->getInput('state'); ?></li>
                    <li><?php echo $this->form->getLabel('start_date').$this->form->getInput('start_date'); ?></li>
                    <li><?php echo $this->form->getLabel('end_date').$this->form->getInput('end_date'); ?></li>
                    <?php if ($this->item->modified_by) : ?>
						<li><?php echo $this->form->getLabel('modified_by').$this->form->getInput('modified_by'); ?></li>
						<li><?php echo $this->form->getLabel('modified').$this->form->getInput('modified'); ?></li>
					<?php endif; ?>
                </ul>
            </fieldset>

            <?php echo JHtml::_('sliders.panel',JText::_('COM_PROJECTFORK_PROJECT_FIELDSET_RULES'), 'access-details'); ?>
            <fieldset class="panelform">
                <ul class="adminformlist">
                    <li><?php echo $this->form->getLabel('access_action') . $this->form->getInput('access_action'); ?></li>
                    <li id="jform_access_element"><?php echo $this->form->getLabel('access') . $this->form->getInput('access'); ?></li>
                    <li id="jform_access_title_element" style="display: none;"><?php echo $this->form->getLabel('access_title') . $this->form->getInput('access_title'); ?></li>
                    <li id="jform_rules_element">
                        <?php echo $this->form->getLabel('rules'); ?>
                        <div id="jform_rules_reload" style="clear: both;"><?php echo $this->form->getInput('rules'); ?></div>
                    </li>
                </ul>
            </fieldset>


            <?php $fieldSets = $this->form->getFieldsets('attribs'); ?>
			<?php foreach ($fieldSets as $name => $fieldSet) : ?>
				<?php echo JHtml::_('sliders.panel',JText::_($fieldSet->label), $name.'-options'); ?>
				<?php if (isset($fieldSet->description) && trim($fieldSet->description)) : ?>
					<p class="tip"><?php echo $this->escape(JText::_($fieldSet->description));?></p>
				<?php endif; ?>
				<fieldset class="panelform">
					<ul class="adminformlist">
					    <?php foreach ($this->form->getFieldset($name) as $field) : ?>
						    <li><?php echo $field->label. $field->input; ?></li>
					    <?php endforeach; ?>
					</ul>
				</fieldset>
			<?php endforeach; ?>

       <?php echo JHtml::_('sliders.end'); ?>
       <div class="clr"></div>
    </div>

    <div class="clr"></div>

    <div>
		<?php
            echo $this->form->getInput('alias');
            echo $this->form->getInput('created');
            echo $this->form->getInput('id');
            echo $this->form->getInput('asset_id');
            echo $this->form->getInput('elements');
        ?>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="view" value="<?php echo htmlspecialchars($this->get('Name'), ENT_COMPAT, 'UTF-8');?>" />
		<input type="hidden" name="return" value="<?php echo JRequest::getCmd('return');?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>