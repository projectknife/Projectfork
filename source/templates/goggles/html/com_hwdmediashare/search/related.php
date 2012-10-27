<?php
/**
 * @version    SVN $Id: related.php 236 2012-03-06 21:56:39Z dhorsfall $
 * @package    hwdMediaShare
 * @copyright  Copyright (C) 2012 Highwood Design Limited. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 * @author     Dave Horsfall
 * @since      06-Mar-2012 20:12:26
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.calendar');
JHtml::_('behavior.formvalidation');

$lang = JFactory::getLanguage();
$upper_limit = $lang->getUpperLimitSearchWord();
$paramsSet = $this->form->getFieldsets('params');
$elementSet = array('media' => 1, 'albums' => 2, 'groups' => 3,'playlists' => 4, 'users' => 5);

?>

<div class="search">
	<form id="searchForm" action="<?php echo JRoute::_('index.php?option=com_hwdmediashare');?>" method="post" class="form-horizontal">
		<div id="hwd-container"> <a name="top" id="top"></a> 
			<!-- Media Navigation --> 
			<?php echo hwdMediaShareHelperNavigation::getInternalNavigation(); ?> 
			<!-- Media Header -->
			<div class="media-header">
				<div class="page-header">
					<h2><?php echo JText::_('COM_HWDMS_RELATED'); ?> to <?php echo $this->escape($this->origkeyword); ?></h2>
				</div>
				<div class="clear"></div>
			</div>
			<?php if ($this->total > 0) : ?>
			<div class="control-group">
				<label for="limit"> <?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?> </label>
				<div class="controls"><?php echo $this->pagination->getLimitBox(); ?> </div>
			</div>
			<p class="counter"> <?php echo $this->pagination->getPagesCounter(); ?> </p>
			<?php endif; ?>
			<?php 
    if ($this->error==null && count($this->results) > 0) :
            echo $this->loadTemplate('results');
    else :
            echo $this->loadTemplate('error');
    endif; 
    ?>
		</div>
		<input type="hidden" name="task" value="search.search" />
	</form>
</div>
