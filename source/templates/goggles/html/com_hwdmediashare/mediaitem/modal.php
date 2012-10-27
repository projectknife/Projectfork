<?php
/**
 * @version    SVN $Id: modal.php 269 2012-03-22 10:07:58Z dhorsfall $
 * @package    hwdMediaShare
 * @copyright  Copyright (C) 2012 Highwood Design Limited. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 * @author     Dave Horsfall
 * @since      17-Mar-2012 12:55:40
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.modal');
JHtml::_('behavior.framework', true);
JHtml::_('behavior.tooltip');

$user = JFactory::getUser();
$canEdit = $user->authorise('core.edit', 'com_hwdmediashare.album.'.$this->item->id);
$canEditState = ($user->authorise('core.edit.state', 'com_hwdmediashare.album.'.$this->item->id) || ($user->authorise('core.edit.own', 'com_hwdmediashare') && ($item->created_user_id == $user->id)));
$canDelete = $user->authorise('core.edit', 'com_hwdmediashare.album.'.$this->item->id);
$hasDownloads = $this->hasDownloads();
$hasQualities = $this->hasQualities();
$hasMeta = $this->hasMeta();
?>

<div id="hwd-container"> <a name="top" id="top" title="top"></a> <?php echo hwdMediaShareHelperNavigation::getInternalNavigation(); ?> 
	<!-- Media Header -->
	<div class="media-header">
		<div class="page-header">
			<h2> <a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getMediaItemRoute($this->item->id)); ?>" target="_top"> <?php echo $this->escape($this->item->title); ?> </a> </h2>
		</div>
		<div class="clear"></div>
	</div>
	<div id="media-item-container" class="media-item-container"> 
		<!-- Item Media -->
		<div class="media-item-full" id="media-item" style="width:100%;"> <?php echo hwdMediaShareMedia::get($this->item); ?> </div>
		<div class="clear"></div>
	</div>
	<div class="clear"></div>
</div>
