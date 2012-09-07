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
<div class="adminform">
    <div class="cpanel-left">
        <div class="cpanel">
            <div class="icon-wrapper">
                <div class="icon">
                    <a href="index.php?option=com_projectfork&view=projects">
                        <?php echo JHtml::image('com_projectfork/projectfork/header/icon-48-projects.png', JText::_('COM_PROJECTFORK_SUBMENU_PROJECTS'), null, true); ?>
                        <span><?php echo JText::_('COM_PROJECTFORK_SUBMENU_PROJECTS');?></span>
                    </a>
                </div>
            </div>
            <div class="icon-wrapper">
                <div class="icon">
                    <a href="index.php?option=com_projectfork&view=milestones">
                        <?php echo JHtml::image('com_projectfork/projectfork/header/icon-48-milestones.png', JText::_('COM_PROJECTFORK_SUBMENU_MILESTONES'), null, true); ?>
                        <span><?php echo JText::_('COM_PROJECTFORK_SUBMENU_MILESTONES');?></span>
                    </a>
                </div>
            </div>
            <div class="icon-wrapper">
                <div class="icon">
                    <a href="index.php?option=com_projectfork&view=tasklists">
                        <?php echo JHtml::image('com_projectfork/projectfork/header/icon-48-tasklists.png', JText::_('COM_PROJECTFORK_SUBMENU_TASKLISTS'), null, true); ?>
                        <span><?php echo JText::_('COM_PROJECTFORK_SUBMENU_TASKLISTS');?></span>
                    </a>
                </div>
            </div>
            <div class="icon-wrapper">
                <div class="icon">
                    <a href="index.php?option=com_projectfork&view=tasks">
                        <?php echo JHtml::image('com_projectfork/projectfork/header/icon-48-tasks.png', JText::_('COM_PROJECTFORK_SUBMENU_TASKS'), null, true); ?>
                        <span><?php echo JText::_('COM_PROJECTFORK_SUBMENU_TASKS');?></span>
                    </a>
                </div>
            </div>
            <div class="icon-wrapper">
                <div class="icon">
                    <a class="modal" rel="{handler: 'iframe', size: {x: 875, y: 550}, onClose: function() {}}" href="<?php echo JRoute::_('index.php?option=com_config&view=component&component=com_projectfork&path=&tmpl=component');?>">
                        <?php echo JHtml::image('com_projectfork/projectfork/header/icon-48-config.png', JText::_('COM_PROJECTFORK_DASHBOARD_CONFIG'), null, true); ?>
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
            <p><a href="https://github.com/projectfork/Projectfork/issues" target="_blank">Report an issue on Github</a></p>
        </fieldset>
    </div>
</div>
