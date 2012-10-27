<?php
/**
 * @version    SVN $Id: default_link.php 269 2012-03-22 10:07:58Z dhorsfall $
 * @package    hwdMediaShare
 * @copyright  Copyright (C) 2011 Highwood Design Limited. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 * @author     Dave Horsfall
 * @since      18-Nov-2011 10:01:56
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.modal');
JHtml::_('behavior.framework', true);
$user = JFactory::getUser();

?>

<div class="edit">
	<form action="<?php echo JRoute::_('index.php?option=com_hwdmediashare&id='); ?>" method="post" id="adminForm" class="formelm form-horizontal">
		<fieldset>
			<legend><?php echo JText::_( 'COM_HWDMS_ADD_MEDIA_TO' ); ?></legend>
			<?php if ($this->item->created_user_id == $user->id): ?>
			<div class="control-group"> <?php echo $this->form->getLabel('category_id'); ?>
				<div class="controls"> <?php echo $this->form->getInput('category_id'); ?> </div>
			</div>
			<?php endif; ?>
			<div class="control-group"> <?php echo $this->form->getLabel('playlist_id'); ?>
				<div class="controls"> <?php echo $this->form->getInput('playlist_id'); ?> </div>
			</div>
			<?php if ($this->item->created_user_id == $user->id): ?>
			<div class="control-group"> <?php echo $this->form->getLabel('album_id'); ?>
				<div class="controls"> <?php echo $this->form->getInput('album_id'); ?> </div>
			</div>
			<?php endif; ?>
			<div class="control-group"> <?php echo $this->form->getLabel('group_id'); ?>
				<div class="controls"> <?php echo $this->form->getInput('group_id'); ?> </div>
			</div>
		</fieldset>
		<div class="form-actions">
			<button onclick="Joomla.submitbutton('mediaitem.link')" type="button" class="btn"><?php echo JText::_( 'COM_HWDMS_ADD' ); ?></button>
			<button onclick="window.parent.SqueezeBox.close();" type="button" class="btn"><?php echo JText::_( 'COM_HWDMS_CANCEL' ); ?></button>
		</div>
		<div>
			<input type="hidden" name="task" value="mediaitem.link" />
			<?php echo JHtml::_('form.token'); ?> </div>
	</form>
</div>
