<?php
/**
 * @version    SVN $Id: default.php 140 2012-01-18 17:17:13Z dhorsfall $
 * @package    hwdMediaShare
 * @copyright  Copyright (C) 2011 Highwood Design Limited. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 * @author     Dave Horsfall
 * @since      14-Nov-2011 21:02:50
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

?>

<div id="hwd-container"> <a name="top" id="top"></a> 
	<!-- Media Navigation --> 
	<?php echo hwdMediaShareHelperNavigation::getInternalNavigation(); ?> <?php echo hwdMediaShareHelperNavigation::getAccountNavigation(); ?> 
	<!-- Media Header -->
	<div class="media-account"> <?php echo $this->loadTemplate('overview'); ?> </div>
</div>
