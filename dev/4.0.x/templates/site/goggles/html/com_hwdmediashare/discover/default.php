<?php
/**
 * @version    SVN $Id: default.php 190 2012-02-20 13:06:16Z dhorsfall $
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
	<?php echo hwdMediaShareHelperNavigation::getInternalNavigation(); ?>
	<div class="media-featured-view">
		<div class="items-leading">
			<div class="leading-0">
				<?php echo hwdMediaShareHelperModule::_loadpos('media-discover-leading'); ?>
				<div class="item-separator"></div>
			</div>
		</div>
		<div class="items-row cols-2 row-0">
			<div class="item column-1">
				<?php echo hwdMediaShareHelperModule::_loadpos('media-discover-1'); ?>
				<div class="item-separator"></div>
			</div>
			<div class="item column-2">
				<?php echo hwdMediaShareHelperModule::_loadpos('media-discover-2'); ?>
				<div class="item-separator"></div>
			</div>
		<span class="row-separator"></span>
		</div>
		<div class="items-row cols-2 row-1">
			<div class="item column-1">
				<?php echo hwdMediaShareHelperModule::_loadpos('media-discover-3'); ?>
				<div class="item-separator"></div>
			</div>
			<div class="item column-2">
				<?php echo hwdMediaShareHelperModule::_loadpos('media-discover-4'); ?>
				<div class="item-separator"></div>
			</div>
		<span class="row-separator"></span>
		</div>
	</div>
</div>