<?php
/**
 * @package      Projectfork
 * @subpackage   Repository
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
?>
<script type="text/javascript">
jQuery(document).ready(function()
{
    PFform.radio2btngroup();
});

Joomla.submitbutton = function(task)
{
    if (task == 'noteform.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
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

<form action="<?php echo htmlspecialchars(JFactory::getURI()->toString()); ?>" method="post" name="adminForm" id="item-form" class="form-validate form-inline">
    <fieldset>
        <div class="formelm-buttons btn-toolbar">
            <?php echo $this->toolbar; ?>
        </div>
        <div class="formelm control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('dir_id'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('dir_id'); ?>
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
            <div class="control-label">
                <?php echo $this->form->getLabel('labels'); ?>
            </div>
            <div class="controls" id="jform_labels_reload">
                <?php echo $this->form->getInput('labels'); ?>
            </div>
        </div>
        <div class="formelm control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('description'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('description'); ?>
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

    <?php echo JHtml::_('tabs.start', 'noteform', array('useCookie' => 'true')) ;?>
    <?php
    $fieldsets = $this->form->getFieldsets('attribs');
    if (count($fieldsets)) :
        echo JHtml::_('tabs.panel', JText::_('COM_PROJECTFORK_DETAILS_FIELDSET'), 'note-options');
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

    <?php echo JHtml::_('tabs.panel', JText::_('COM_PROJECTFORK_FIELDSET_RULES'), 'note-permissions') ;?>
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

    <div id="jform_access_element">
        <div id="jform_access_reload"><?php echo $this->form->getInput('access'); ?></div>
    </div>

    <?php
        echo $this->form->getInput('project_id');
        echo $this->form->getInput('created');
        echo $this->form->getInput('id');
        echo $this->form->getInput('asset_id');
        echo $this->form->getInput('elements');
    ?>
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="return" value="<?php echo $this->return_page;?>" />
    <input type="hidden" name="view" value="<?php echo htmlspecialchars($this->get('Name'), ENT_COMPAT, 'UTF-8');?>" />
    <input type="hidden" name="filter_parent_id" value="<?php echo intval($this->form->getValue('dir_id'));?>" />
    <?php echo JHtml::_( 'form.token' ); ?>
</form>
</div>
