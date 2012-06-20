<?php
/**
 * @version    SVN $Id: default_report.php 234 2012-03-06 10:41:48Z dhorsfall $
 * @package    hwdMediaShare
 * @copyright  Copyright (C) 2011 Highwood Design Limited. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 * @author     Dave Horsfall
 * @since      18-Nov-2011 09:57:55
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.modal');
JHtml::_('behavior.framework', true);

?>

<div class="edit">
	<form action="<?php echo JRoute::_('index.php?option=com_hwdmediashare'); ?>" method="post" id="adminForm" class="formelm form-horizontal">
		<fieldset>
			<legend><?php echo JText::_( 'COM_HWDMS_REPORT_MEDIA' ); ?></legend>
			<?php foreach($this->form->getFieldset('details') as $field): ?>
			<div class="control-group"> <?php echo $field->label;?>
				<div class="controls"> <?php echo $field->input;?> </div>
			</div>
			<?php endforeach; ?>
		</fieldset>
		<div class="form-actions">
			<button onclick="Joomla.submitbutton('mediaitem.report')" type="button" class="btn"><?php echo JText::_('COM_HWDMS_REPORT'); ?></button>
			<button onclick="window.parent.SqueezeBox.close();" type="button" class="btn"><?php echo JText::_('COM_HWDMS_CANCEL'); ?></button>
		</div>
		<div>
			<input type="hidden" name="tmpl" value="component" />
			<input type="hidden" name="id" value="<?php echo $this->id; ?>" />
			<input type="hidden" name="task" value="mediaitem.report" />
			<?php echo JHtml::_('form.token'); ?> </div>
	</form>
</div>
