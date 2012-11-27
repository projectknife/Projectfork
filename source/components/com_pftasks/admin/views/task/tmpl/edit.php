<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */


defined('_JEXEC') or die;


JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('pfhtml.script.form');

$user = JFactory::getUser();
?>
<script type="text/javascript">
Joomla.submitbutton = function(task)
{
    if (task == 'task.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
        <?php echo $this->form->getField('description')->save(); ?>
        Joomla.submitform(task, document.getElementById('item-form'));
	}
    else {
	    alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
	}
}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_pftasks&view=task&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

    <div class="width-60 fltlft span7">
        <fieldset class="adminform">
			<legend><?php echo empty($this->item->id) ? JText::_('COM_PROJECTFORK_NEW_TASK') : JText::_('COM_PROJECTFORK_EDIT_TASK'); ?></legend>
			<ul class="adminformlist unstyled">
				<?php if ($this->item->id <= 0) : ?>
                    <li><?php echo $this->form->getLabel('project_id').$this->form->getInput('project_id'); ?></li>
                <?php endif; ?>
                <?php if (PFApplicationHelper::enabled('com_pfmilestones')) : ?>
    				<li>
                        <?php echo $this->form->getLabel('milestone_id'); ?>
                        <div id="jform_milestone_id_reload">
                            <?php echo $this->form->getInput('milestone_id'); ?>
                        </div>
                    </li>
                <?php endif; ?>
				<li>
                    <?php echo $this->form->getLabel('list_id'); ?>
                    <div id="jform_list_id_reload">
                        <?php echo $this->form->getInput('list_id'); ?>
                    </div>
                </li>
				<li><?php echo $this->form->getLabel('title').$this->form->getInput('title'); ?></li>
			</ul>
            <div class="clr"></div>
			<?php echo $this->form->getLabel('description'); ?>
			<div class="clr"></div>
			<?php echo $this->form->getInput('description'); ?>
			<div class="clr"></div>
		</fieldset>
    </div>

    <div class="width-40 fltrt span4">
        <?php echo JHtml::_('sliders.start','task-sliders-'.$this->item->id, array('useCookie'=>1)); ?>

            <?php echo JHtml::_('sliders.panel',JText::_('COM_PROJECTFORK_FIELDSET_PUBLISHING'), 'publishing-details'); ?>
            <fieldset class="panelform">
				<ul class="adminformlist unstyled">
                    <li><?php echo $this->form->getLabel('created_by').$this->form->getInput('created_by'); ?></li>
                    <li><?php echo $this->form->getLabel('state').$this->form->getInput('state'); ?></li>
                    <li><?php echo $this->form->getLabel('priority').$this->form->getInput('priority'); ?></li>
                    <li><?php echo $this->form->getLabel('complete').$this->form->getInput('complete'); ?></li>
                    <li><?php echo $this->form->getLabel('start_date') . '<span id="jform_start_date_reload">' . $this->form->getInput('start_date') . '</span>'; ?></li>
                    <li><?php echo $this->form->getLabel('end_date') . '<span id="jform_end_date_reload">' . $this->form->getInput('end_date') . '</span>'; ?></li>
                    <li><?php echo $this->form->getLabel('rate').$this->form->getInput('rate'); ?></li>
                    <li><?php echo $this->form->getLabel('estimate').$this->form->getInput('estimate'); ?></li>
                    <?php if ($this->item->modified_by) : ?>
						<li><?php echo $this->form->getLabel('modified_by').$this->form->getInput('modified_by'); ?></li>
						<li><?php echo $this->form->getLabel('modified').$this->form->getInput('modified'); ?></li>
					<?php endif; ?>
                </ul>
            </fieldset>

            <?php echo JHtml::_('sliders.panel', JText::_('COM_PROJECTFORK_FIELDSET_ASSIGNED_USERS'), 'users'); ?>
            <fieldset class="panelform">
                <div id="jform_users_element">
                    <div id="jform_users_reload">
				        <?php echo $this->form->getInput('users'); ?>
                    </div>
                </div>
            </fieldset>

            <?php echo JHtml::_('sliders.panel', JText::_('COM_PROJECTFORK_FIELDSET_DEPENDENCIES'), 'dependencies'); ?>
            <fieldset class="panelform">
                <div id="jform_dependency_element">
                    <div id="jform_dependency_reload">
				        <?php echo $this->form->getInput('dependency'); ?>
                    </div>
                </div>
            </fieldset>

            <?php echo JHtml::_('sliders.panel', JText::_('COM_PROJECTFORK_FIELDSET_LABELS'), 'labels'); ?>
            <fieldset class="panelform">
                <div id="jform_labels_element">
                    <div id="jform_labels_reload">
				        <?php echo $this->form->getInput('labels'); ?>
                    </div>
                </div>
            </fieldset>

            <?php if (PFApplicationHelper::enabled('com_pfrepo')) : ?>
                <?php echo JHtml::_('sliders.panel', JText::_('COM_PROJECTFORK_FIELDSET_ATTACHMENTS'), 'attachments'); ?>
                <fieldset class="panelform">
    				<ul class="adminformlist unstyled" id="jform_attachment_element">
                        <li id="jform_attachment_reload">
                            <?php echo $this->form->getInput('attachment'); ?>
                        </li>
                    </ul>
                </fieldset>
            <?php endif; ?>

            <?php $fieldsets = $this->form->getFieldsets('attribs'); ?>
			<?php foreach ($fieldsets as $name => $fieldset) : ?>
				<?php echo JHtml::_('sliders.panel', JText::_($fieldset->label), $name . '-options'); ?>
				<?php if (isset($fieldset->description) && trim($fieldset->description)) : ?>
					<p><?php echo $this->escape(JText::_($fieldset->description));?></p>
				<?php endif; ?>
				<fieldset class="panelform">
					<ul class="adminformlist unstyled">
					    <?php foreach ($this->form->getFieldset($name) as $field) : ?>
						    <li><?php echo $field->label . $field->input; ?></li>
					    <?php endforeach; ?>
					</ul>
				</fieldset>
			<?php endforeach; ?>

       <?php echo JHtml::_('sliders.end'); ?>
       <div class="clr"></div>
    </div>

    <div class="clr"></div>

    <?php if ($user->authorise('core.admin', 'com_pftasks')) : ?>
        <div class="width-100 fltlft span12">
    		<?php echo JHtml::_('sliders.start', 'permissions-sliders-' . $this->item->id, array('useCookie'=>1)); ?>

    			<?php echo JHtml::_('sliders.panel', JText::_('COM_PROJECTFORK_FIELDSET_RULES'), 'access-rules'); ?>
    			<fieldset class="panelform">
                    <p><?php echo JText::_('COM_PROJECTFORK_RULES_LABEL'); ?></p>
                    <p><?php echo JText::_('COM_PROJECTFORK_RULES_NOTE'); ?></p>
    				<div id="jform_rules_element">
                        <div id="jform_rules_reload" style="clear: both;">
                            <?php echo $this->form->getInput('rules'); ?>
                        </div>
                    </div>
    			</fieldset>
    		<?php echo JHtml::_('sliders.end'); ?>
    	</div>
    <?php endif; ?>

    <div>
        <div id="jform_access_element">
            <div id="jform_access_reload">
                <?php echo $this->form->getInput('access'); ?>
            </div>
        </div>
		<?php
            echo $this->form->getInput('alias');
            echo $this->form->getInput('created');
            echo $this->form->getInput('elements');
        ?>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="view" value="<?php echo htmlspecialchars($this->get('Name'), ENT_COMPAT, 'UTF-8');?>" />
		<input type="hidden" name="return" value="<?php echo JRequest::getCmd('return');?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>