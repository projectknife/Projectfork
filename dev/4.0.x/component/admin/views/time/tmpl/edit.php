<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('projectfork.script.form');
?>
<script type="text/javascript">
Joomla.submitbutton = function(task)
{
    if (task == 'time.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
        Joomla.submitform(task, document.getElementById('item-form'));
    }
    else {
        alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
    }
}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_projectfork&view=time&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

    <div class="width-60 fltlft">
        <fieldset class="adminform">
            <legend><?php echo empty($this->item->id) ? JText::_('COM_PROJECTFORK_NEW_TIME') : JText::_('COM_PROJECTFORK_EDIT_TIME'); ?></legend>
            <ul class="adminformlist">
                <li><?php echo $this->form->getLabel('project_id') . $this->form->getInput('project_id'); ?></li>
                <li>
                    <?php echo $this->form->getLabel('task_id'); ?>
                    <div id="jform_task_id_reload">
                        <?php echo $this->form->getInput('task_id'); ?>
                    </div>
                </li>
                <li><?php echo $this->form->getLabel('description') . $this->form->getInput('description'); ?></li>
                <li><?php echo $this->form->getLabel('log_date') . $this->form->getInput('log_date'); ?></li>
                <li><?php echo $this->form->getLabel('log_time') . $this->form->getInput('log_time'); ?></li>
                <li><?php echo $this->form->getLabel('billable') . $this->form->getInput('billable'); ?></li>
                <li><?php echo $this->form->getLabel('rate') . $this->form->getInput('rate'); ?></li>
            </ul>
            <div class="clr"></div>
        </fieldset>
    </div>

    <div class="width-40 fltrt">
        <?php echo JHtml::_('sliders.start','time-sliders-' . $this->item->id, array('useCookie'=>1)); ?>

            <?php echo JHtml::_('sliders.panel',JText::_('COM_PROJECTFORK_FIELDSET_PUBLISHING'), 'publishing-details'); ?>
            <fieldset class="panelform">
                <ul class="adminformlist">
                    <li><?php echo $this->form->getLabel('created_by') . $this->form->getInput('created_by'); ?></li>
                    <li><?php echo $this->form->getLabel('state') . $this->form->getInput('state'); ?></li>
                    <?php if ($this->item->modified_by) : ?>
                        <li><?php echo $this->form->getLabel('modified_by') . $this->form->getInput('modified_by'); ?></li>
                        <li><?php echo $this->form->getLabel('modified') . $this->form->getInput('modified'); ?></li>
                    <?php endif; ?>
                </ul>
            </fieldset>

            <?php echo JHtml::_('sliders.panel',JText::_('COM_PROJECTFORK_TIME_FIELDSET_RULES'), 'access-rules'); ?>
            <fieldset class="panelform">
                <ul class="adminformlist">
                    <li>
                        <?php echo $this->form->getLabel('access');?>
                        <div id="jform_access_reload"><?php echo $this->form->getInput('access'); ?></div>
                    </li>
				    <li>
                        <label id="jform_access_exist-lbl" class="hasTip" title="<?php echo JText::_('COM_PROJECTFORK_FIELD_EXISTING_ACCESS_GROUPS_DESC');?>">
                            <?php echo JText::_('COM_PROJECTFORK_FIELD_EXISTING_ACCESS_GROUPS_LABEL');?>
                        </label>
                    </li>
                    <li>
                        <div id="jform_access_groups">
    			            <div class="clr"></div>
                            <div id="jform_rules_reload"><?php echo $this->form->getInput('rules'); ?></div>
                        </div>
                    </li>
                </ul>
            </fieldset>

            <?php $fieldSets = $this->form->getFieldsets('attribs'); ?>
            <?php foreach ($fieldSets as $name => $fieldSet) : ?>
                <?php echo JHtml::_('sliders.panel', JText::_($fieldSet->label), $name.'-options'); ?>
                <?php if (isset($fieldSet->description) && trim($fieldSet->description)) : ?>
                    <p class="tip"><?php echo $this->escape(JText::_($fieldSet->description));?></p>
                <?php endif; ?>
                <fieldset class="panelform">
                    <ul class="adminformlist">
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

    <div>
        <?php
            echo $this->form->getInput('created');
            echo $this->form->getInput('id');
            echo $this->form->getInput('asset_id');
            echo $this->form->getInput('elements');
        ?>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="return" value="<?php echo JRequest::getCmd('return');?>" />
        <input type="hidden" name="view" value="<?php echo htmlspecialchars($this->get('Name'), ENT_COMPAT, 'UTF-8');?>" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
