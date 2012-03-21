<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.php
*
* This file is part of Projectfork.
*
* Projectfork is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*
* Projectfork is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Projectfork. If not, see <http://www.gnu.org/licenses/gpl.html>.
**/

// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.calendar');
JHtml::_('behavior.formvalidation');


// Create shortcut to parameters.
$params = $this->state->get('params');


// This checks if the editor config options have ever been saved. If they haven't they will fall back to the original settings.
$editoroptions = isset($params->show_publishing_options);
if(!$editoroptions) $params->show_urls_images_frontend = '0';
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'projectform.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
			<?php echo $this->form->getField('description')->save(); ?>
			Joomla.submitform(task);
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>
<div class="edit item-page<?php echo $this->pageclass_sfx; ?>">
<?php if ($params->get('show_page_heading', 1)) : ?>
<h1>
	<?php echo $this->escape($params->get('page_heading')); ?>
</h1>
<?php endif; ?>

<form action="<?php echo JRoute::_('index.php?option=com_projectfork&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
	<fieldset>
		<legend><?php echo JText::_('JEDITOR'); ?></legend>
			<div class="formelm">
			    <?php echo $this->form->getLabel('title'); ?>
			    <?php echo $this->form->getInput('title'); ?>
			</div>
			<div class="formelm-buttons">
			    <button type="button" onclick="Joomla.submitbutton('projectform.save')">
				    <?php echo JText::_('JSAVE') ?>
			    </button>
			    <button type="button" onclick="Joomla.submitbutton('projectform.cancel')">
				    <?php echo JText::_('JCANCEL') ?>
			    </button>
			</div>

			<?php echo $this->form->getInput('description'); ?>

	</fieldset>

    <?php echo JHtml::_('tabs.start', 'projectform', array('useCookie' => 'true')) ;?>
    <?php echo JHtml::_('tabs.panel', 'Publishing', 'project-publishing') ;?>
    <fieldset>
        <div class="formelm">
		    <?php echo $this->form->getLabel('created_by'); ?>
			<?php echo $this->form->getInput('created_by'); ?>
		</div>
        <div class="formelm">
		    <?php echo $this->form->getLabel('state'); ?>
			<?php echo $this->form->getInput('state'); ?>
		</div>
        <div class="formelm">
		    <?php echo $this->form->getLabel('start_date'); ?>
			<?php echo $this->form->getInput('start_date'); ?>
		</div>
        <div class="formelm">
		    <?php echo $this->form->getLabel('end_date'); ?>
			<?php echo $this->form->getInput('end_date'); ?>
		</div>
        <?php if ($this->item->modified_by) : ?>
            <div class="formelm">
                <?php echo $this->form->getLabel('modified_by');?>
                <?php echo $this->form->getInput('modified_by');?>
            </div>
            <div class="formelm">
                <?php echo $this->form->getLabel('modified');?>
                <?php echo $this->form->getInput('modified');?>
            </div>
		<?php endif; ?>
    </fieldset>

    <?php echo JHtml::_('tabs.panel', 'Permissions', 'project-permissions') ;?>
    <fieldset>
        <div class="formelm" id="jform_access-li">
		    <?php echo $this->form->getLabel('access'); ?>
			<?php echo $this->form->getInput('access'); ?>
		</div>
        <div class="formelm" id="jform_access_new-li" style="display: none;">
		    <?php echo $this->form->getLabel('access_new'); ?>
			<?php echo $this->form->getInput('access_new'); ?>
		</div>
        <div class="formelm" id="jform_access_exist-li" style="display: none;">
            <label id="jform_access_exist-lbl" class="hasTip" title="<?php echo JText::_('COM_PROJECTFORK_FIELD_EXISTING_ACCESS_GROUPS_DESC');?>">
                <?php echo JText::_('COM_PROJECTFORK_FIELD_EXISTING_ACCESS_GROUPS_LABEL');?>
            </label>
        </div>
        <div class="formlm" id="jform_access_groups-li">
            <label id="jform_access_groups-lbl" class="hasTip" title="<?php echo JText::_('COM_PROJECTFORK_FIELD_NEW_ACCESS_GROUPS_DESC');?>">
                <?php echo JText::_('COM_PROJECTFORK_FIELD_NEW_ACCESS_GROUPS_LABEL');?>
            </label>
            <div id="jform_access_groups">
    		    <div class="clr"></div>
                <?php echo $this->form->getInput('rules'); ?>
            </div>
        </div>
    </fieldset>

    <?php echo JHtml::_('tabs.panel', 'Options', 'project-options') ;?>
    <fieldset>
        <?php $fieldSets = $this->form->getFieldsets('attribs'); ?>
			<?php foreach ($fieldSets as $name => $fieldSet) : ?>
				<fieldset>
                    <?php foreach ($this->form->getFieldset($name) as $field) : ?>
                        <div class="formelm" id="jform_access-li">
                		    <?php echo $field->label; ?>
                			<?php echo $field->input; ?>
                		</div>
                    <?php endforeach; ?>
                </fieldset>
			<?php endforeach; ?>
    </fieldset>

    <?php echo JHtml::_('tabs.end') ;?>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="return" value="<?php echo $this->return_page;?>" />
	<?php echo JHtml::_( 'form.token' ); ?>
</form>
</div>
