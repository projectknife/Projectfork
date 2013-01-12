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


$modules = &$this->modules;
$pfv     = new PFVersion();
$jv      = new JVersion();
?>
<?php if (version_compare(JVERSION, '3.0.0', 'ge')) : ?>
<div class="row-fluid">
    <div class="span8 hidden-phone">
            <?php foreach ($this->buttons AS $component => $buttons) : ?>
                <?php if (PFApplicationHelper::enabled($component)) : ?>
                    <?php foreach ($buttons AS $button) : ?>
        		        <a href="<?php echo $button['link']; ?>" class="thumbnail btn pull-left">
        		            <?php echo $button['icon']; ?>
        		            <span class="small"><?php echo JText::_($button['title']);?></span>
        		        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php if ($this->user->authorise('core.admin')) : ?>
                <a class="thumbnail btn pull-left" href="<?php echo JRoute::_('index.php?option=com_config&view=component&component=com_projectfork');?>">
                    <?php echo JHtml::image('com_projectfork/projectfork/header/icon-48-config.png', JText::_('COM_PROJECTFORK_DASHBOARD_CONFIG'), null, true); ?>
                    <span class="small"><?php echo JText::_('COM_PROJECTFORK_DASHBOARD_CONFIG');?></span>
                </a>
            <?php endif; ?>
            <div class="clearfix"></div>
            <?php echo $modules->render('pf-dashboard-top', array('style' => 'xhtml'), null); ?>
        		<?php echo $modules->render('pf-dashboard-left', array('style' => 'xhtml'), null); ?>
    </div>
    <div class="span4" style="margin-left: 0;">
        <div class="well well-small">
        	<div class="module-title nav-header">Projectfork <?php echo PFVERSION; ?></div>
            <div class="well-small">
            	Consider this a preview-only version of Projectfork. We highly recommend against using in a production environment as there may be many bugs.
            </div>
            <p>
                <a href="https://github.com/projectfork/Projectfork/issues" class="btn btn-primary btn-wide btn-small" target="_blank">
                    <span aria-hidden="true" class="icon-warning"></span> Report an Issue
                </a>
            </p>
            <div class="alert alert-info small">
            	<strong>Please include:</strong>
            	<ul class="unstyled">
            	    <li><small>Joomla Version: <?php echo JVERSION; ?> <?php echo $jv->DEV_STATUS;?></small></li>
            	    <li><small>Projectfork Version: <?php echo PFVERSION; ?> <?php echo $pfv->DEV_STATUS;?></small></li>
            	    <li><small>PHP Version: <?php echo phpversion(); ?></small></li>
            	</ul>
            </div>
        </div>
        <?php echo $modules->render('pf-dashboard-right', array('style' => 'xhtml'), null); ?>
    </div>
</div>
<?php else : ?>
<div class="adminform">
    <div class="cpanel-left">
        <div class="cpanel">
            <?php foreach ($this->buttons AS $component => $buttons) : ?>
                <?php if (PFApplicationHelper::enabled($component)) : ?>
                    <?php foreach ($buttons AS $button) : ?>
                	    <div class="icon-wrapper">
                	        <div class="icon">
                	            <a href="<?php echo $button['link']; ?>" class="thumbnail btn">
                	                <?php echo $button['icon']; ?>
                	                <span class="small"><?php echo JText::_($button['title']);?></span>
                	            </a>
                	        </div>
                	    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php if ($this->user->authorise('core.admin')) : ?>
                <div class="icon-wrapper">
                    <div class="icon">
                        <a class="modal thumbnail btn" rel="{handler: 'iframe', size: {x: 875, y: 550}, onClose: function() {}}" href="<?php echo JRoute::_('index.php?option=com_config&view=component&component=com_projectfork&tmpl=component');?>">
                            <?php echo JHtml::image('com_projectfork/projectfork/header/icon-48-config.png', JText::_('COM_PROJECTFORK_DASHBOARD_CONFIG'), null, true); ?>
                            <span><?php echo JText::_('COM_PROJECTFORK_DASHBOARD_CONFIG');?></span>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
            <div class="clr clearfix"></div>
            <?php echo $modules->render('pf-dashboard-top', array('style' => 'xhtml'), null); ?>
        </div>
        <div class="row-fluid">
        	<div class="span12">
        		<?php echo $modules->render('pf-dashboard-left', array('style' => 'xhtml'), null); ?>
        	</div>
        </div>
    </div>
    <div class="cpanel-right width-40">
        <div class="well well-small">
        	<h3>Projectfork <?php echo PFVERSION; ?></h3>
            <p>
            	Consider this a preview-only version of Projectfork. We highly recommend against using in a production environment as there may be many bugs.
            </p>
            <div class="button2-left">
              <div class="blank">
            		<a href="https://github.com/projectfork/Projectfork/issues" class="button" target="_blank">
            		    Report an Issue
            		</a>
              </div>
            </div>
            <div class="clr"></div>
            <h4>Please include:</h4>
        	<ul class="unstyled">
        	    <li><small>Joomla Version: <?php echo JVERSION; ?> <?php echo $jv->DEV_STATUS;?></small></li>
        	    <li><small>Projectfork Version: <?php echo PFVERSION; ?> <?php echo $pfv->DEV_STATUS;?></small></li>
        	    <li><small>PHP Version: <?php echo phpversion(); ?></small></li>
        	</ul>
        </div>
        <?php echo $modules->render('pf-dashboard-right', array('style' => 'xhtml'), null); ?>
    </div>
</div>
<?php endif; ?>
