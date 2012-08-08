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

?>
<script type="text/javascript">
Joomla.submitbutton = function(task) {
    if (task == 'reply.cancel' ||
        task == 'reply.setAccess' ||
        document.formvalidator.isValid(document.id('item-form'))
       ) {
        Joomla.submitform(task, document.getElementById('item-form'));
    }
    else {
        alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
    }
}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_projectfork&view=reply&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

    <div class="width-60 fltlft">
        <fieldset class="adminform">
            <legend><?php echo empty($this->item->id) ? JText::_('COM_PROJECTFORK_NEW_REPLY') : JText::_('COM_PROJECTFORK_EDIT_REPLY'); ?></legend>
            <?php echo $this->form->getLabel('description'); ?>
            <div class="clr"></div>
            <?php echo $this->form->getInput('description'); ?>
            <div class="clr"></div>
        </fieldset>
    </div>

    <div class="width-40 fltrt">
        <?php echo JHtml::_('sliders.start','project-sliders-' . $this->item->id, array('useCookie'=>1)); ?>

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

            <?php echo JHtml::_('sliders.panel',JText::_('COM_PROJECTFORK_REPLY_FIELDSET_RULES'), 'access-rules'); ?>
            <fieldset class="panelform">
                <ul class="adminformlist">
                    <li id="jform_access-li"><?php echo $this->form->getLabel('access') . $this->form->getInput('access'); ?></li>
                    <li id="jform_access_exist-li">
                        <label id="jform_access_exist-lbl" class="hasTip" title="<?php echo JText::_('COM_PROJECTFORK_FIELD_EXISTING_ACCESS_GROUPS_DESC');?>">
                            <?php echo JText::_('COM_PROJECTFORK_FIELD_EXISTING_ACCESS_GROUPS_LABEL');?>
                        </label>
                    </li>
                    <li id="jform_access_groups-li">
                        <div id="jform_access_groups">
                            <div class="clr"></div>
                            <?php echo $this->form->getInput('rules'); ?>
                        </div>
                    </li>
                </ul>
            </fieldset>

            <?php $fieldSets = $this->form->getFieldsets('users'); ?>
            <?php foreach ($fieldSets as $name => $fieldSet) : ?>
                <?php echo JHtml::_('sliders.panel', JText::_($fieldSet->label), $name.'-options'); ?>
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
            echo $this->form->getInput('project_id');
            echo $this->form->getInput('topic_id');
            echo $this->form->getInput('created');
            echo $this->form->getInput('id');
        ?>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="return" value="<?php echo JRequest::getCmd('return');?>" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
