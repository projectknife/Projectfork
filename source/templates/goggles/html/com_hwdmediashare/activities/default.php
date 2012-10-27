<?php
/**
 * @version    SVN $Id: default.php 146 2012-01-20 17:48:50Z dhorsfall $
 * @package    hwdMediaShare
 * @copyright  Copyright (C) 2012 Highwood Design Limited. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 * @author     Dave Horsfall
 * @since      20-Jan-2012 09:23:40
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$user = JFactory::getUser();
JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');

?>

<form action="<?php echo JRoute::_('index.php?option=com_hwdmediashare'); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal">
	<div id="hwd-container"> <a name="top" id="top"></a> 
		<!-- Media Navigation -->
		<div class="categories-list"> <?php echo $this->getActivities($this->items); ?> </div>
		<!-- Pagination -->
		<div class="pagination"> <?php echo $this->pagination->getPagesLinks(); ?> </div>
	</div>
</form>
