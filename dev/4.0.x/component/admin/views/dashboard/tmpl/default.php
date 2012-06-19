<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2011 Tobias Kuhn. All rights reserved.
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

defined('_JEXEC') or die;
?>

<div class="adminform">
	<div class="cpanel-left">
		<div class="cpanel">
			<div class="icon-wrapper">
				<div class="icon">
					<a href="index.php?option=com_projectfork&view=projects">
						<img src="../components/com_projectfork/assets/projectfork/images/header/icon-48-projects.png" alt=""/>
						<span><?php echo JText::_('COM_PROJECTFORK_SUBMENU_PROJECTS');?></span>
					</a>
				</div>
			</div>
			<div class="icon-wrapper">
				<div class="icon">
					<a href="index.php?option=com_projectfork&view=milestones">
						<img src="../components/com_projectfork/assets/projectfork/images/header/icon-48-milestones.png" alt=""/>
						<span><?php echo JText::_('COM_PROJECTFORK_SUBMENU_MILESTONES');?></span>
					</a>
				</div>
			</div>
			<div class="icon-wrapper">
				<div class="icon">
					<a href="index.php?option=com_projectfork&view=tasklists">
						<img src="../components/com_projectfork/assets/projectfork/images/header/icon-48-tasklists.png" alt=""/>
						<span><?php echo JText::_('COM_PROJECTFORK_SUBMENU_TASKLISTS');?></span>
					</a>
				</div>
			</div>
			<div class="icon-wrapper">
				<div class="icon">
					<a href="index.php?option=com_projectfork&view=tasks">
						<img src="../components/com_projectfork/assets/projectfork/images/header/icon-48-tasks.png" alt=""/>
						<span><?php echo JText::_('COM_PROJECTFORK_SUBMENU_TASKS');?></span>
					</a>
				</div>
			</div>
            <div class="icon-wrapper">
				<div class="icon">
					<a class="modal" rel="{handler: 'iframe', size: {x: 875, y: 550}, onClose: function() {}}" href="<?php echo JRoute::_('index.php?option=com_config&view=component&component=com_projectfork&path=&tmpl=component');?>">
						<img src="../components/com_projectfork/assets/projectfork/images/header/icon-48-config.png" alt=""/>
						<span><?php echo JText::_('COM_PROJECTFORK_DASHBOARD_CONFIG');?></span>
					</a>
				</div>
			</div>
		</div>
	</div>
	<div class="cpanel-right width-40">
		<fieldset>
			<legend><?php echo JText::_('COM_PROJECTFORK');?></legend>
			<h3>Projectfork 4 Alpha</h3>
			<p>Consider this a preview-only version of Projectfork. We highly recommend against using in a production environment as there may be many bugs. Features and functions will rapidly change in the alpha stage and we don't offer support or migration for these features.</p>
			<p>Onebit icon set from <a href="http://www.icojoy.com/">icojoy.com</a>.</p>
		</fieldset>
	</div>
</div>