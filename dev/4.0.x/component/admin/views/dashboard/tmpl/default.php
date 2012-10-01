<?php
/**
* @package      Projectfork
*
* @author       Tobias Kuhn (eaxs)
* @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();
?>
<div class="adminform row-fluid">
    <div class="cpanel-left span9 hidden-phone">
        <div class="cpanel row-fluid">
            <div class="icon-wrapper span2">
                <div class="icon">
                    <a href="index.php?option=com_projectfork&view=projects" class="thumbnail btn">
                        <?php echo JHtml::image('com_projectfork/projectfork/header/icon-48-projects.png', JText::_('COM_PROJECTFORK_SUBMENU_PROJECTS'), null, true); ?>
                        <span><?php echo JText::_('COM_PROJECTFORK_SUBMENU_PROJECTS');?></span>
                    </a>
                </div>
            </div>
            <div class="icon-wrapper span2">
                <div class="icon">
                    <a href="index.php?option=com_projectfork&view=milestones" class="thumbnail btn">
                        <?php echo JHtml::image('com_projectfork/projectfork/header/icon-48-milestones.png', JText::_('COM_PROJECTFORK_SUBMENU_MILESTONES'), null, true); ?>
                        <span><?php echo JText::_('COM_PROJECTFORK_SUBMENU_MILESTONES');?></span>
                    </a>
                </div>
            </div>
            <div class="icon-wrapper span2">
                <div class="icon">
                    <a href="index.php?option=com_projectfork&view=tasklists" class="thumbnail btn">
                        <?php echo JHtml::image('com_projectfork/projectfork/header/icon-48-tasklists.png', JText::_('COM_PROJECTFORK_SUBMENU_TASKLISTS'), null, true); ?>
                        <span><?php echo JText::_('COM_PROJECTFORK_SUBMENU_TASKLISTS');?></span>
                    </a>
                </div>
            </div>
            <div class="icon-wrapper span2">
                <div class="icon">
                    <a href="index.well?option=com_projectfork&view=tasks" class="thumbnail btn">
                        <?php echo JHtml::image('com_projectfork/projectfork/header/icon-48-tasks.png', JText::_('COM_PROJECTFORK_SUBMENU_TASKS'), null, true); ?>
                        <span><?php echo JText::_('COM_PROJECTFORK_SUBMENU_TASKS');?></span>
                    </a>
                </div>
            </div>
            <div class="icon-wrapper span2">
                <div class="icon">
                    <a class="modal thumbnail btn" rel="{handler: 'iframe', size: {x: 875, y: 550}, onClose: function() {}}" href="<?php echo JRoute::_('index.php?option=com_config&view=component&component=com_projectfork&path=&tmpl=component');?>">
                        <?php echo JHtml::image('com_projectfork/projectfork/header/icon-48-config.png', JText::_('COM_PROJECTFORK_DASHBOARD_CONFIG'), null, true); ?>
                        <span><?php echo JText::_('COM_PROJECTFORK_DASHBOARD_CONFIG');?></span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="cpanel-right span3 width-40">
        <div class="well well-small">
            <div class="module-title nav-header"><?php echo JText::_('COM_PROJECTFORK');?></div>
            <div class="well well-small">
            	<h4>Projectfork 4 Alpha</h4>
            <p>Consider this a preview-only version of Projectfork. We highly recommend against using in a production environment as there may be many bugs. Features and functions will rapidly change in the alpha stage and we don't offer support or migration for these features.</p>
            <p><a href="https://github.com/projectfork/Projectfork/issues" class="btn btn-small" target="_blank"><span aria-hidden="true" class="icon-warning"></span> Report an issue on Github</a></p>
            </div>
        </div>
    </div>
</div>
