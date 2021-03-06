<?php
/**
 * @package      Projectfork
 * @subpackage   Forum
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('pfhtml.script.form');

$user = JFactory::getUser();
?>
<script type="text/javascript">
Joomla.submitbutton = function(task)
{
    if (task == 'reply.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
        <?php echo $this->form->getField('description')->save(); ?>
        Joomla.submitform(task, document.getElementById('item-form'));
    }
    else {
        alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
    }
}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_pfforum&view=reply&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

    <div class="width-60 fltlft span7">
        <fieldset class="adminform">
            <legend><?php echo empty($this->item->id) ? JText::_('COM_PROJECTFORK_NEW_REPLY') : JText::_('COM_PROJECTFORK_EDIT_REPLY'); ?></legend>
            <?php echo $this->form->getLabel('description'); ?>
            <div class="clr"></div>
            <?php echo $this->form->getInput('description'); ?>
            <div class="clr"></div>
        </fieldset>
    </div>

    <div class="width-40 fltrt span4">
        <?php echo JHtml::_('sliders.start','reply-sliders-' . $this->item->id, array('useCookie'=>1)); ?>

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

            <?php if (PFApplicationHelper::enabled('com_pfrepo')) : ?>
                <?php echo JHtml::_('sliders.panel',JText::_('COM_PROJECTFORK_FIELDSET_ATTACHMENTS'), 'attachments'); ?>
                <fieldset class="panelform">
    				<ul class="adminformlist">
                        <li>
                            <?php echo $this->form->getInput('attachment'); ?>
                        </li>
                    </ul>
                </fieldset>
            <?php endif; ?>

       <?php echo JHtml::_('sliders.end'); ?>
       <div class="clr"></div>
    </div>

    <div class="clr"></div>

    <?php if ($user->authorise('core.admin', 'com_pfforum')) : ?>
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

    <div id="jform_access_element">
        <div id="jform_access_reload">
            <?php echo $this->form->getInput('access'); ?>
        </div>
    </div>

    <div>
        <?php
            echo $this->form->getInput('project_id');
            echo $this->form->getInput('topic_id');
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
