<?php
/**
* @package      Projectfork
* @subpackage   Dashboard
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
            <?php if (array_key_exists('com_pfprojects', $this->components)) : ?>
            <div class="icon-wrapper span2">
                <div class="icon">
                    <a href="index.php?option=com_pfprojects" class="thumbnail btn">
                        <?php echo JHtml::image('com_projectfork/projectfork/header/icon-48-projects.png', JText::_('COM_PROJECTFORK_SUBMENU_PROJECTS'), null, true); ?>
                        <span><?php echo JText::_('COM_PROJECTFORK_SUBMENU_PROJECTS');?></span>
                    </a>
                </div>
            </div>
            <?php endif; ?>
            <?php if (array_key_exists('com_pfmilestones', $this->components)) : ?>
            <div class="icon-wrapper span2">
                <div class="icon">
                    <a href="index.php?option=com_pfmilestones" class="thumbnail btn">
                        <?php echo JHtml::image('com_projectfork/projectfork/header/icon-48-milestones.png', JText::_('COM_PROJECTFORK_SUBMENU_MILESTONES'), null, true); ?>
                        <span><?php echo JText::_('COM_PROJECTFORK_SUBMENU_MILESTONES');?></span>
                    </a>
                </div>
            </div>
            <?php endif; ?>
            <?php if (array_key_exists('com_pftasks', $this->components)) : ?>
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
                    <a href="index.php?option=com_pftasks" class="thumbnail btn">
                        <?php echo JHtml::image('com_projectfork/projectfork/header/icon-48-tasks.png', JText::_('COM_PROJECTFORK_SUBMENU_TASKS'), null, true); ?>
                        <span><?php echo JText::_('COM_PROJECTFORK_SUBMENU_TASKS');?></span>
                    </a>
                </div>
            </div>
            <?php endif; ?>
            <?php if ($this->user->authorise('core.admin')) : ?>
            <div class="icon-wrapper span2">
                <div class="icon">
                    <?php if (version_compare(JVERSION, '3.0.0', 'ge')) : ?>
                        <a class="thumbnail btn" href="<?php echo JRoute::_('index.php?option=com_config&view=component&component=com_projectfork');?>">
                            <?php echo JHtml::image('com_projectfork/projectfork/header/icon-48-config.png', JText::_('COM_PROJECTFORK_DASHBOARD_CONFIG'), null, true); ?>
                            <span><?php echo JText::_('COM_PROJECTFORK_DASHBOARD_CONFIG');?></span>
                        </a>
                    <?php else : ?>
                        <a class="modal thumbnail btn" rel="{handler: 'iframe', size: {x: 875, y: 550}, onClose: function() {}}" href="<?php echo JRoute::_('index.php?option=com_config&view=component&component=com_projectfork&tmpl=component');?>">
                            <?php echo JHtml::image('com_projectfork/projectfork/header/icon-48-config.png', JText::_('COM_PROJECTFORK_DASHBOARD_CONFIG'), null, true); ?>
                            <span><?php echo JText::_('COM_PROJECTFORK_DASHBOARD_CONFIG');?></span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="cpanel-right span3 width-40">
        <div class="well well-small">
            <div class="module-title nav-header"><?php echo JText::_('COM_PROJECTFORK');?></div>
            <div class="well well-small">
           	    <h4>Projectfork 4 Beta</h4>
                <p>Consider this a preview-only version of Projectfork. We highly recommend against using in a production environment as there may be many bugs. </p>
            </div>
            <p>
                <a href="https://github.com/projectfork/Projectfork/issues" class="btn btn-small" target="_blank">
                    <i aria-hidden="true" class="icon-warning"></i> Report an issue on Github
                </a>
            </p>
        </div>
    </div>
</div>
