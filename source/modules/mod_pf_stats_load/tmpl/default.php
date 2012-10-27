<?php
/**
* @package      Projectfork Project Workload Statistics
*
* @author       Tobias Kuhn (eaxs)
* @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();


// Initialize the chart
$doc = JFactory::getDocument();
$doc->addScriptDeclaration("jQuery(function()
{
    var data = " . json_encode($stats) . ";
    jQuery.plot(jQuery('#mod-pf-stats-load-" . $module->id . "'), data,
    {
        bars: { show: true },
        xaxis: { show: false }
    });
});");
?>
<div id="mod-pf-stats-load-<?php echo $module->id;?>" style="<?php echo $css_w . $css_h;?>"></div>
<span class="label"><?php echo JText::_('MOD_PF_STATS_LOAD_GRAPH_DESC');?></span>
