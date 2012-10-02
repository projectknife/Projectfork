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
JHtml::_('projectfork.script.form');

$params = $this->state->get('params');
$access = ProjectforkHelperAccess::getActions();

$create_ms   = $access->get('milestone.create');
$create_list = $access->get('tasklist.create');
$create_task = $access->get('task.create');
?>
<script type="text/javascript">
Joomla.submitbutton = function(task)
{
	if (task == 'projectform.cancel' || document.getElementById('jform_title').value != '') {
		<?php echo $this->form->getField('description')->save(); ?>
		Joomla.submitform(task, document.getElementById('item-form'));
	} else {
		alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
	}
}
</script>
<div class="edit item-page<?php echo $this->pageclass_sfx; ?>">

<?php if ($params->get('show_page_heading', 0)) : ?>
<h1><?php echo $this->escape($params->get('page_heading')); ?></h1>
<?php endif; ?>

<form action="<?php echo htmlspecialchars(JFactory::getURI()->toString()); ?>" method="post" name="adminForm" id="item-form" class="form-inline">
	<fieldset>
		<div class="formelm-buttons btn-toolbar">
		    <div class="btn-group">
                <button class="btn btn-primary" type="button" onclick="Joomla.submitbutton('projectform.save')">
    			    <?php echo JText::_('JSAVE') ?>
    		    </button>
                <button class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a href="javascript:void();" onclick="javascript:Joomla.submitbutton('projectform.save2new')">
                            <?php echo JText::_('COM_PROJECTFORK_ACTION_2NEW') ?>
                        </a>
                    </li>
                    <?php if ($this->item->id > 0) : ?>
                        <li>
                            <a href="javascript:void();" onclick="javascript:Joomla.submitbutton('projectform.save2copy')">
                                <?php echo JText::_('COM_PROJECTFORK_ACTION_2COPY') ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if ($create_ms || $create_list || $create_task) : ?>
                        <li class="divider"></li>
                    <?php endif; ?>
                    <?php if ($create_ms) : ?>
                        <li>
                            <a href="javascript:void();" onclick="javascript:Joomla.submitbutton('projectform.save2milestone')">
                                <?php echo JText::_('COM_PROJECTFORK_ACTION_2MILESTONE') ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if ($create_list) : ?>
                        <li>
                            <a href="javascript:void();" onclick="javascript:Joomla.submitbutton('projectform.save2tasklist')">
                                <?php echo JText::_('COM_PROJECTFORK_ACTION_2TASKLIST') ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if ($create_task) : ?>
                        <li>
                            <a href="javascript:void();" onclick="javascript:Joomla.submitbutton('projectform.save2task')">
                                <?php echo JText::_('COM_PROJECTFORK_ACTION_2TASK') ?>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="btn-group">
                <button class="btn" type="button" onclick="Joomla.submitbutton('projectform.cancel')">
    			    <?php echo JText::_('JCANCEL') ?>
    		    </button>
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
		<div class="control-group">
			<div class="controls">
				<?php echo $this->form->getInput('description'); ?>
			</div>
		</div>
	</fieldset>

    <hr />

    <?php echo JHtml::_('tabs.start', 'projectform', array('useCookie' => 'true')) ;?>
    <?php echo JHtml::_('tabs.panel', JText::_('COM_PROJECTFORK_FIELDSET_PUBLISHING'), 'project-publishing') ;?>
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
				<?php echo $this->form->getLabel('catid'); ?>
		    </div>
		    <div class="controls">
				<?php echo $this->form->getInput('catid'); ?>
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
        <?php if ($this->item->modified_by) : ?>
            <div class="formelm control-group">
            	<div class="control-label">
                	<?php echo $this->form->getLabel('modified_by');?>
                </div>
                <div class="controls">
                	<?php echo $this->form->getInput('modified_by');?>
                </div>
            </div>
            <div class="formelm control-group">
            	<div class="control-label">
                	<?php echo $this->form->getLabel('modified');?>
                </div>
                <div class="controls">
                	<?php echo $this->form->getInput('modified');?>
                </div>
            </div>
		<?php endif; ?>
    </fieldset>

    <?php if ($this->item->id) : ?>
    <?php echo JHtml::_('tabs.panel', JText::_('COM_PROJECTFORK_FIELDSET_ATTACHMENTS'), 'project-attachments') ;?>
    <fieldset>
    	<div class="formelm control-group">
    		<?php echo $this->form->getInput('attachment'); ?>
    	</div>
    </fieldset>
    <?php endif; ?>

    <?php
    $fieldsets = $this->form->getFieldsets('attribs');
    if (count($fieldsets)) :
        echo JHtml::_('tabs.panel', JText::_('COM_PROJECTFORK_DETAILS_FIELDSET'), 'project-options');
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

    <?php echo JHtml::_('tabs.panel', JText::_('COM_PROJECTFORK_FIELDSET_RULES'), 'project-permissions') ;?>
    <fieldset>
        <p><?php echo JText::_('COM_PROJECTFORK_RULES_LABEL'); ?></p>
        <p><?php echo JText::_('COM_PROJECTFORK_RULES_NOTE'); ?></p>
        <div class="formlm" id="jform_rules_element">
            <div id="jform_rules_reload" class="controls">
                <?php echo $this->form->getInput('rules'); ?>
            </div>
        </div>
    </fieldset>

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
