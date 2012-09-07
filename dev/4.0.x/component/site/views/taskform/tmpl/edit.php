<?php
/**
 * @package      Projectfork
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
JHtml::_('projectfork.script.form');

// Create shortcut to parameters.
$params = $this->state->get('params');


// This checks if the editor config options have ever been saved. If they haven't they will fall back to the original settings.
$editoroptions = isset($params->show_publishing_options);
if(!$editoroptions) $params->show_urls_images_frontend = '0';
?>
<script type="text/javascript">
Joomla.submitbutton = function(task)
{
	if (task == 'taskform.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
		<?php echo $this->form->getField('description')->save(); ?>
        Joomla.submitform(task);
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

<form action="<?php echo htmlspecialchars(JFactory::getURI()->toString()); ?>" method="post" name="adminForm" id="item-form" class="form-validate form-inline">
	<fieldset>
		<div class="formelm-buttons btn-toolbar">
		    <button class="btn btn-primary" type="button" onclick="Joomla.submitbutton('taskform.save')">
			    <?php echo JText::_('JSAVE') ?>
		    </button>
		    <button class="btn" type="button" onclick="Joomla.submitbutton('taskform.cancel')">
			    <?php echo JText::_('JCANCEL') ?>
		    </button>
		</div>
		<div class="formelm control-group">
			<div class="control-label">
		    	<?php echo $this->form->getLabel('project_id'); ?>
		    </div>
		    <div class="controls">
		    	<?php echo $this->form->getInput('project_id'); ?>
		    </div>
		</div>
		<div class="formelm control-group">
			<div class="control-label">
		    	<?php echo $this->form->getLabel('milestone_id'); ?>
		    </div>
		    <div class="controls" id="jform_milestone_id_reload">
		    	<?php echo $this->form->getInput('milestone_id'); ?>
		    </div>
		</div>
		<div class="formelm control-group">
			<div class="control-label">
		    	<?php echo $this->form->getLabel('list_id'); ?>
		    </div>
		    <div class="controls" id="jform_list_id_reload">
		    	<?php echo $this->form->getInput('list_id'); ?>
		    </div>
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
		    <div class="controls">
		    	<?php echo $this->form->getInput('description'); ?>
		    </div>
		</div>
	</fieldset>

    <?php echo JHtml::_('tabs.start', 'taskform', array('useCookie' => 'true')) ;?>
    <?php echo JHtml::_('tabs.panel', 'Publishing', 'task-publishing') ;?>
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
    	    <div class="controls">
    	    	<?php echo $this->form->getInput('start_date'); ?>
    	    </div>
    	</div>
    	<div class="formelm control-group">
    		<div class="control-label">
    	    	<?php echo $this->form->getLabel('end_date'); ?>
    	    </div>
    	    <div class="controls">
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

    <?php echo JHtml::_('tabs.panel', 'Permissions', 'task-permissions') ;?>
    <fieldset>
    	<div class="formelm control-group">
    		<div class="control-label">
    	    	<?php echo $this->form->getLabel('access'); ?>
    	    </div>
    	    <div class="controls" id="jform_access_reload">
    	    	<?php echo $this->form->getInput('access'); ?>
    	    </div>
    	</div>
        <div class="formelm control-group">
            <label id="jform_access_exist-lbl" class="hasTip" title="<?php echo JText::_('COM_PROJECTFORK_FIELD_EXISTING_ACCESS_GROUPS_DESC');?>">
                <?php echo JText::_('COM_PROJECTFORK_FIELD_EXISTING_ACCESS_GROUPS_LABEL');?>
            </label>
        </div>
        <div class="formelm control-group">
            <div id="jform_rules_reload">
                <?php echo $this->form->getInput('rules'); ?>
            </div>
        </div>
    </fieldset>

    <?php $fieldSets = $this->form->getFieldsets('users'); ?>
	<?php foreach ($fieldSets as $name => $fieldSet) : ?>
		<?php echo JHtml::_('tabs.panel',JText::_($fieldSet->label), $name.'-options'); ?>
		<?php if (isset($fieldSet->description) && trim($fieldSet->description)) : ?>
			<p class="tip"><?php echo $this->escape(JText::_($fieldSet->description));?></p>
		<?php endif; ?>
		<fieldset>
			    <?php foreach ($this->form->getFieldset($name) as $field) : ?>
			    	<div class="formelm control-group">
			    		<div class="control-label">
			    	    	<?php echo $field->label; ?>
			    	    </div>
			    	    <div class="controls">
			    			<?php echo $field->input; ?>
			    		</div>
			    	</div>
			    <?php endforeach; ?>
		</fieldset>
	<?php endforeach; ?>

    <?php echo JHtml::_('tabs.panel', 'Options', 'task-options') ;?>
        <?php $fieldSets = $this->form->getFieldsets('attribs'); ?>
			<?php foreach ($fieldSets as $name => $fieldSet) : ?>
				<fieldset>
                    <?php foreach ($this->form->getFieldset($name) as $field) : ?>
                    	<div class="formelm control-group" id="jform_access-li">
                    		<div class="control-label">
                    	    	<?php echo $field->label; ?>
                    	    </div>
                    	    <div class="controls">
                    			<?php echo $field->input; ?>
                    		</div>
                    	</div>
                    <?php endforeach; ?>
                </fieldset>
			<?php endforeach; ?>

    <?php echo JHtml::_('tabs.end') ;?>

    <?php
        echo $this->form->getInput('alias');
        echo $this->form->getInput('created');
        echo $this->form->getInput('id');
        echo $this->form->getInput('asset_id');
        echo $this->form->getInput('elements');
    ?>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="return" value="<?php echo $this->return_page;?>" />
    <input type="hidden" name="view" value="<?php echo htmlspecialchars($this->get('Name'), ENT_COMPAT, 'UTF-8');?>" />
	<?php echo JHtml::_( 'form.token' ); ?>
</form>
</div>
