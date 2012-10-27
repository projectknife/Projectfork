<?php
/**
 * @package      Projectfork
 * @subpackage   Comments
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
?>
<script type="text/javascript">
Joomla.submitbutton = function(task)
{
    if (task == 'comment.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
        Joomla.submitform(task, document.getElementById('item-form'));
    }
    else {
        alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
    }
}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_pfcomments&view=comment&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

    <div class="width-60 fltlft span7">
        <fieldset class="adminform">
			<legend><?php echo empty($this->item->id) ? JText::_('COM_PROJECTFORK_NEW_COMMENT') : JText::_('COM_PROJECTFORK_EDIT_COMMENT'); ?></legend>
			<ul class="adminformlist unstyled">
				<li><?php echo $this->form->getLabel('description').$this->form->getInput('description'); ?></li>
			</ul>
			<div class="clr"></div>
		</fieldset>
    </div>

    <div class="width-40 fltrt span4">
        <?php echo JHtml::_('sliders.start','project-sliders-'.$this->item->id, array('useCookie'=>1)); ?>

            <?php echo JHtml::_('sliders.panel',JText::_('COM_PROJECTFORK_FIELDSET_PUBLISHING'), 'publishing-details'); ?>
            <fieldset class="panelform">
				<ul class="adminformlist unstyled">
                    <li><?php echo $this->form->getLabel('created_by').$this->form->getInput('created_by'); ?></li>
                    <li><?php echo $this->form->getLabel('state').$this->form->getInput('state'); ?></li>
                    <?php if ($this->item->modified_by) : ?>
						<li><?php echo $this->form->getLabel('modified_by').$this->form->getInput('modified_by'); ?></li>
						<li><?php echo $this->form->getLabel('modified').$this->form->getInput('modified'); ?></li>
					<?php endif; ?>
                </ul>
            </fieldset>

            <?php
            $fieldsets = (array) $this->form->getFieldsets('attribs');

            if (count($fieldsets)) :
                foreach ($fieldsets as $name => $fieldset) :
                ?>
    				<?php echo JHtml::_('sliders.panel',JText::_($fieldset->label), $name.'-options'); ?>
    				<?php if (isset($fieldset->description) && trim($fieldset->description)) : ?>
    					<p class="tip"><?php echo $this->escape(JText::_($fieldSet->description));?></p>
    				<?php endif; ?>
    				<fieldset class="panelform">
    					<ul class="adminformlist unstyled">
    					    <?php foreach ($this->form->getFieldset($name) as $field) : ?>
    						    <li><?php echo $field->label . $field->input; ?></li>
    					    <?php endforeach; ?>
    					</ul>
    				</fieldset>
    			<?php
                endforeach;
            endif;
            ?>

       <?php echo JHtml::_('sliders.end'); ?>
       <div class="clr"></div>
    </div>

    <div class="clr"></div>

    <div>
		<?php
            echo $this->form->getInput('title');
            echo $this->form->getInput('created');
            echo $this->form->getInput('project_id');
            echo $this->form->getInput('parent_id');
            echo $this->form->getInput('item_id');
            echo $this->form->getInput('context');
            echo $this->form->getInput('id');
        ?>
        <input type="hidden" name="task" value="" />
		<input type="hidden" name="return" value="<?php echo JRequest::getCmd('return');?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>