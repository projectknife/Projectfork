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

JHtml::_('behavior.tooltip');

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

        <!--
        <h3><?php echo JText::_('COM_PROJECTFORK_MAINTENANCE_TOOLS'); ?></h3>
        <form action="<?php echo JRoute::_('index.php?option=com_projectfork'); ?>" method="post" id="jform_maintenance" name="maintenanceForm" autocomplete="off">
            <table class="table">
                <tr>
                    <td width="8%">
                        <button type="button" class="btn" id="jform_chk_assets_btn" onclick="PFchkAssets.startStop();">
                            <?php echo JText::_('COM_PROJECTFORK_START'); ?>
                        </button>
                    </td>
                    <td width="25%">
                        <span class="hasTip" style="cursor: help;" title="<?php echo JText::_('COM_PROJECTFORK_CHK_ASSET_STRUCT_DESC'); ?>">
                            <?php echo JText::_('COM_PROJECTFORK_CHK_ASSET_STRUCT'); ?>
                        </span>
                    </td>
                    <td>
                        <div class="progress" id="jform_chk_assets_progcontainer" style="display: none;" >
                            <div class="bar bar-success" id="jform_chk_assets_prog_bar" style="width: 24px;">
                                <span class="label label-success pull-right" id="jform_chk_assets_prog_label">
                                    0%
                                </span>
                            </div>
                        </div>
                        <input type="hidden" name="chk_assets_limitstart" id="jform_chk_assets_limitstart" value="0"/>
                        <input type="hidden" name="chk_assets_limit" id="jform_chk_assets_limit" value="0"/>
                        <input type="hidden" name="chk_assets_stop" id="jform_chk_assets_stop" value="1"/>
                    </td>
                </tr>
            </table>
            <input type="hidden" name="task" id="jform_task" value=""/>
            <input type="hidden" name="tmpl" value="component" />
            <input type="hidden" name="format" value="json" />
            <?php echo JHtml::_('form.token'); ?>
        </form>
        <script type="text/javascript">
        var PFchkAssets =
        {
            startStop: function()
            {
                if (jQuery('#jform_chk_assets_stop').val() == 1) {
                    // Start
                    jQuery('#jform_chk_assets_stop').val(0);
                    jQuery('#jform_task').val('dashboard.countProjects');
                    jQuery('#jform_chk_assets_btn').text("<?php echo JText::_('COM_PROJECTFORK_STOP'); ?>");
                    jQuery('#jform_chk_assets_progcontainer').show();

                    var mt_form = jQuery('#jform_maintenance');
                    var mt_data = mt_form.serializeArray();

                    jQuery.ajax(
                    {
                        url: mt_form.attr('action'),
                        data: jQuery.param(mt_data),
                        type: 'POST',
                        processData: true,
                        cache: false,
                        dataType: 'json',

                        success: function(rsp)
                        {
                            jQuery('#jform_chk_assets_limit').val(parseInt(rsp.total));
                            setTimeout("PFchkAssets.process()", 1000);
                        }
                    });
                }
                else {
                    // Stop
                    jQuery('#jform_chk_assets_stop').val(1);
                    jQuery('#jform_chk_assets_limitstart').val(0);
                    jQuery('#jform_chk_assets_limit').val(0);
                    jQuery('#jform_chk_assets_btn').text("<?php echo JText::_('COM_PROJECTFORK_START'); ?>");
                    jQuery('#jform_chk_assets_progcontainer').hide();
                    jQuery('#jform_chk_assets_prog_bar').css('width', '24px');
                    jQuery('#jform_chk_assets_prog_label').text('0%');
                }
            },

            process: function()
            {
                var ls = parseInt(jQuery('#jform_chk_assets_limitstart').val());
                var l  = parseInt(jQuery('#jform_chk_assets_limit').val());

                if (jQuery('#jform_chk_assets_stop').val() == 1) {
                    return true;
                }
                else {
                    if (ls >= l) {
                        PFchkAssets.startStop();
                        return true;
                    }
                }

                jQuery('#jform_task').val('dashboard.checkAssets');

                var mt_form = jQuery('#jform_maintenance');
                var mt_data = mt_form.serializeArray();

                jQuery.ajax(
                {
                    url: mt_form.attr('action'),
                    data: jQuery.param(mt_data),
                    type: 'POST',
                    processData: true,
                    cache: false,
                    dataType: 'json',

                    success: function(rsp)
                    {
                        if (jQuery('#jform_chk_assets_stop').val() == 1) return true;

                        ls += 1;

                        var progress = ls * (100 / l);

                        jQuery('#jform_chk_assets_prog_bar').css('width', progress + '%');
                        jQuery('#jform_chk_assets_prog_label').text(parseInt(progress) + '%');

                        jQuery('#jform_chk_assets_limitstart').val(ls);

                        setTimeout("PFchkAssets.process()", 1000);
                    }
                });
            }
        }
        </script>-->
    </div>
    <div class="span4">
        <div class="well well-small">
        	<div class="module-title nav-header">Projectfork <?php echo PFVERSION; ?></div>
            <p>
                <a href="http://projectfork.net" class="btn btn-success btn-wide btn-small" target="_blank">
                    <span aria-hidden="true" class="icon-home"></span> Visit the website
                </a>
            </p>
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
<?php else :
JHtml::_('pfhtml.script.jquery');
?>
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

        <h3><?php echo JText::_('COM_PROJECTFORK_MAINTENANCE_TOOLS'); ?></h3>
        <form action="<?php echo JRoute::_('index.php?option=com_projectfork'); ?>" method="post" id="jform_maintenance" name="maintenanceForm" autocomplete="off">
            <table class="table">
                <tr>
                    <td width="8%">
                        <button type="button" class="btn" id="jform_chk_assets_btn" onclick="PFchkAssets.startStop();">
                            <?php echo JText::_('COM_PROJECTFORK_START'); ?>
                        </button>
                    </td>
                    <td width="25%">
                        <span class="hasTip" style="cursor: help;" title="<?php echo JText::_('COM_PROJECTFORK_CHK_ASSET_STRUCT_DESC'); ?>">
                            <?php echo JText::_('COM_PROJECTFORK_CHK_ASSET_STRUCT'); ?>
                        </span>
                    </td>
                    <td>
                        <div class="progress" id="jform_chk_assets_progcontainer" style="display: none;" >
                            <div class="bar bar-success" id="jform_chk_assets_prog_bar" style="width: 24px;">
                                <span class="label label-success pull-right" id="jform_chk_assets_prog_label">
                                    0%
                                </span>
                            </div>
                        </div>
                        <input type="hidden" name="chk_assets_limitstart" id="jform_chk_assets_limitstart" value="0"/>
                        <input type="hidden" name="chk_assets_limit" id="jform_chk_assets_limit" value="0"/>
                        <input type="hidden" name="chk_assets_stop" id="jform_chk_assets_stop" value="1"/>
                    </td>
                </tr>
            </table>
            <input type="hidden" name="task" id="jform_task" value=""/>
            <input type="hidden" name="tmpl" value="component" />
            <input type="hidden" name="format" value="json" />
            <?php echo JHtml::_('form.token'); ?>
        </form>
        <script type="text/javascript">
        var PFchkAssets =
        {
            startStop: function()
            {
                if (jQuery('#jform_chk_assets_stop').val() == 1) {
                    // Start
                    jQuery('#jform_chk_assets_stop').val(0);
                    jQuery('#jform_task').val('dashboard.countProjects');
                    jQuery('#jform_chk_assets_btn').text("<?php echo JText::_('COM_PROJECTFORK_STOP'); ?>");
                    jQuery('#jform_chk_assets_progcontainer').show();

                    var mt_form = jQuery('#jform_maintenance');
                    var mt_data = mt_form.serializeArray();

                    jQuery.ajax(
                    {
                        url: mt_form.attr('action'),
                        data: jQuery.param(mt_data),
                        type: 'POST',
                        processData: true,
                        cache: false,
                        dataType: 'json',

                        success: function(rsp)
                        {
                            jQuery('#jform_chk_assets_limit').val(parseInt(rsp.total));
                            setTimeout("PFchkAssets.process()", 1000);
                        }
                    });
                }
                else {
                    // Stop
                    jQuery('#jform_chk_assets_stop').val(1);
                    jQuery('#jform_chk_assets_limitstart').val(0);
                    jQuery('#jform_chk_assets_limit').val(0);
                    jQuery('#jform_chk_assets_btn').text("<?php echo JText::_('COM_PROJECTFORK_START'); ?>");
                    jQuery('#jform_chk_assets_progcontainer').hide();
                    jQuery('#jform_chk_assets_prog_bar').css('width', '24px');
                    jQuery('#jform_chk_assets_prog_label').text('0%');
                }
            },

            process: function()
            {
                var ls = parseInt(jQuery('#jform_chk_assets_limitstart').val());
                var l  = parseInt(jQuery('#jform_chk_assets_limit').val());

                if (jQuery('#jform_chk_assets_stop').val() == 1) {
                    return true;
                }
                else {
                    if (ls >= l) {
                        PFchkAssets.startStop();
                        return true;
                    }
                }

                jQuery('#jform_task').val('dashboard.checkAssets');

                var mt_form = jQuery('#jform_maintenance');
                var mt_data = mt_form.serializeArray();

                jQuery.ajax(
                {
                    url: mt_form.attr('action'),
                    data: jQuery.param(mt_data),
                    type: 'POST',
                    processData: true,
                    cache: false,
                    dataType: 'json',

                    success: function(rsp)
                    {
                        if (jQuery('#jform_chk_assets_stop').val() == 1) return true;

                        ls += 1;

                        var progress = ls * (100 / l);

                        jQuery('#jform_chk_assets_prog_bar').css('width', progress + '%');
                        jQuery('#jform_chk_assets_prog_label').text(parseInt(progress) + '%');

                        jQuery('#jform_chk_assets_limitstart').val(ls);

                        setTimeout("PFchkAssets.process()", 1000);
                    }
                });
            }
        }
        </script>
    </div>
    <div class="cpanel-right width-40">
        <div class="well well-small">
        	<h3>Projectfork <?php echo PFVERSION; ?></h3>
            <div class="button2-left">
              <div class="blank">
            		<a href="http://projectfork.net" class="button" target="_blank">
            		    Visit the Website
            		</a>
              </div>
            </div>
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
