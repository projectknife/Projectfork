<?php
/**
 * @package      Projectfork
 * @subpackage   Tasks
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.calendar');
JHtml::_('behavior.formvalidation');
JHtml::_('pfhtml.script.form');

// Create shortcut to parameters.
$params = $this->state->get('params');
$user   = JFactory::getUser();
?>
<script type="text/javascript">
jQuery(document).ready(function()
{
    PFform.radio2btngroup();
});

Joomla.submitbutton = function(task)
{
	if (task == 'taskform.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
		<?php echo $this->form->getField('description')->save(); ?>
        Joomla.submitform(task, document.getElementById('item-form'));
	} else {
		alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
	}
}
</script>
<div class="edit item-page<?php echo $this->pageclass_sfx; ?>">
<?php if ($params->get('show_page_heading', 0)) : ?>
<h1>
	<?php echo $this->escape($params->get('page_heading')); ?>
</h1>
<?php endif; ?>

<form action="<?php echo JRoute::_('index.php?option=com_pftasks&view=taskform&id=' . (int) $this->item->id . '&layout=edit'); ?>" method="post" name="adminForm" id="item-form" class="form-validate form-inline">
    <fieldset>
		<div class="formelm-buttons btn-toolbar">
            <?php echo $this->toolbar; ?>
		</div>
        <?php if ($this->item->id <= 0) : ?>
    		<div class="formelm control-group">
                <div class="control-label">
    		    	<?php echo $this->form->getLabel('project_id'); ?>
    		    </div>
    		    <div class="controls">
    		    	<?php echo $this->form->getInput('project_id'); ?>
    		    </div>
    		</div>
        <?php endif; ?>
        <?php if (PFApplicationHelper::enabled('com_pfmilestones')) : ?>
    		<div class="formelm control-group">
    			<div class="control-label">
    		    	<?php echo $this->form->getLabel('milestone_id'); ?>
    		    </div>
    		    <div class="controls" id="jform_milestone_id_reload">
    		    	<?php echo $this->form->getInput('milestone_id'); ?>
    		    </div>
    		</div>
        <?php endif; ?>
		<div class="formelm control-group">
			<div class="control-label">
		    	<?php echo $this->form->getLabel('list_id'); ?>
		    </div>
		    <div class="controls" id="jform_list_id_reload">
		    	<?php echo $this->form->getInput('list_id'); ?>
		    </div>
		</div>
        <div id="jform_access_element">
            <div id="jform_access_reload"><?php echo $this->form->getInput('access'); ?></div>
        </div>
		<div class="formelm control-group">
			<div class="control-label">
		    	<?php echo $this->form->getLabel('title'); ?>
		    </div>
		    <div class="controls">
		    	<?php echo $this->form->getInput('title'); ?>
		    </div>
		</div>
        <div class="formelm control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('labels'); ?>
            </div>
            <div class="controls" id="jform_labels_reload">
                <?php echo $this->form->getInput('labels'); ?>
            </div>
        </div>
		<div class="formelm control-group">
		    <div class="controls">
		    	<?php echo $this->form->getInput('description'); ?>
		    </div>
		</div>
	</fieldset>

    <?php echo JHtml::_('tabs.start', 'taskform', array('useCookie' => 'true')) ;?>
    <?php echo JHtml::_('tabs.panel', JText::_('COM_PROJECTFORK_FIELDSET_PUBLISHING'), 'task-publishing') ;?>
    <fieldset>
    	<div class="formelm control-group">
    		<div class="control-label">
    	    	<?php echo $this->form->getLabel('state'); ?>
    	    </div>
    	    <div class="controls">
    	    	<?php echo $this->form->getInput('state'); ?>
    	    </div>
    	</div>
    	<div class="formelm control-group">
    		<div class="control-label">
    	    	<?php echo $this->form->getLabel('priority'); ?>
    	    </div>
    	    <div class="controls">
    	    	<?php echo $this->form->getInput('priority'); ?>
    	    </div>
    	</div>
    	<div class="formelm control-group">
    		<div class="control-label">
    	    	<?php echo $this->form->getLabel('complete'); ?>
    	    </div>
    	    <div class="controls">
    	    	<?php echo $this->form->getInput('complete'); ?>
    	    </div>
    	</div>
    	<div class="formelm control-group">
    		<div class="control-label">
    	    	<?php echo $this->form->getLabel('start_date'); ?>
    	    </div>
    	    <div id="jform_start_date_reload" class="controls">
    	    	<?php echo $this->form->getInput('start_date'); ?>
    	    </div>
    	</div>
    	<div class="formelm control-group">
    		<div class="control-label">
    	    	<?php echo $this->form->getLabel('end_date'); ?>
    	    </div>
    	    <div id="jform_end_date_reload" class="controls">
    	    	<?php echo $this->form->getInput('end_date'); ?>
    	    </div>
    	</div>
        <div class="formelm control-group">
    		<div class="control-label">
    	    	<?php echo $this->form->getLabel('rate'); ?>
    	    </div>
    	    <div class="controls">
    	    	<?php echo $this->form->getInput('rate'); ?>
    	    </div>
    	</div>
        <div class="formelm control-group">
    		<div class="control-label">
    	    	<?php echo $this->form->getLabel('estimate'); ?>
    	    </div>
    	    <div class="controls">
    	    	<?php echo $this->form->getInput('estimate'); ?>
    	    </div>
    	</div>
    	<?php if ($this->item->modified_by) : ?>
	    	<div class="formelm control-group">
	    		<div class="control-label">
	    	    	<?php echo $this->form->getLabel('modified_by'); ?>
	    	    </div>
	    	    <div class="controls">
	    	    	<?php echo $this->form->getInput('modified_by'); ?>
	    	    </div>
	    	</div>
	    	<div class="formelm control-group">
	    		<div class="control-label">
	    	    	<?php echo $this->form->getLabel('modified'); ?>
	    	    </div>
	    	    <div class="controls">
	    	    	<?php echo $this->form->getInput('modified'); ?>
	    	    </div>
	    	</div>
    	<?php endif; ?>
    </fieldset>

    <?php echo JHtml::_('tabs.panel', JText::_('COM_PROJECTFORK_FIELDSET_ASSIGNED_USERS'), 'task-users') ;?>
    <fieldset>
    	<div id="jform_users_element" class="formelm control-group">
            <div id="jform_users_reload">
		        <?php echo $this->form->getInput('users'); ?>
            </div>
    	</div>
    </fieldset>

    <?php echo JHtml::_('tabs.panel', JText::_('COM_PROJECTFORK_FIELDSET_DEPENDENCIES'), 'task-dependencies') ;?>
    <fieldset>
    	<div id="jform_dependency_element" class="formelm control-group">
            <div id="jform_dependency_reload">
		        <?php echo $this->form->getInput('dependency'); ?>
            </div>
    	</div>
    </fieldset>

    <?php if (PFApplicationHelper::enabled('com_pfrepo')) : ?>
    <?php echo JHtml::_('tabs.panel', 'Attachments', 'task-attachments') ;?>
        <fieldset>
        	<div id="jform_attachment_element" class="formelm control-group">
                <div id="jform_attachment_reload">
        		  <?php echo $this->form->getInput('attachment'); ?>
                </div>
        	</div>
        </fieldset>
    <?php endif; ?>

    <?php
    $fieldsets = $this->form->getFieldsets('attribs');
    if (count($fieldsets)) :
        echo JHtml::_('tabs.panel', JText::_('COM_PROJECTFORK_DETAILS_FIELDSET'), 'task-options');
		foreach ($fieldsets as $name => $fieldset) :
            ?>
			<fieldset>
                <?php foreach ($this->form->getFieldset($name) as $field) : ?>
                    <div class="formelm control-group">
                    	<div class="control-label"><?php echo $field->label; ?></div>
            		    <div class="controls"><?php echo $field->input; ?></div>
            		</div>
                <?php endforeach; ?>
            </fieldset>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($user->authorise('core.admin', 'com_pftasks')) : ?>
        <?php echo JHtml::_('tabs.panel', JText::_('COM_PROJECTFORK_FIELDSET_RULES'), 'task-permissions') ;?>
        <fieldset>
            <p><?php echo JText::_('COM_PROJECTFORK_RULES_LABEL'); ?></p>
            <p><?php echo JText::_('COM_PROJECTFORK_RULES_NOTE'); ?></p>
            <div class="formlm" id="jform_rules_element">
                <div id="jform_rules_reload" class="controls">
                    <?php echo $this->form->getInput('rules'); ?>
                </div>
            </div>
        </fieldset>
    <?php endif; ?>

    <?php echo JHtml::_('tabs.end') ;?>

    <div style="display: none;">
    <?php
        if ($this->item->id > 0) {
            echo $this->form->getInput('project_id');
        }
        echo $this->form->getInput('alias');
        echo $this->form->getInput('created');
        echo $this->form->getInput('elements');
    ?>
    </div>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="return" value="<?php echo $this->return_page;?>" />
    <input type="hidden" name="view" value="<?php echo htmlspecialchars($this->get('Name'), ENT_COMPAT, 'UTF-8');?>" />
	<?php echo JHtml::_( 'form.token' ); ?>
</form>
</div>
