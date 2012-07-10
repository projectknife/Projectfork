<?php
/**
* @package   Projectfork Project Workload Statistics
* @copyright Copyright (C) 2012 Tobias Kuhn. All rights reserved.
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

// no direct access
defined('_JEXEC') or die;

// Initialize the chart
$doc = JFactory::getDocument();
$doc->addScriptDeclaration("jQuery(function()
{
    var data = ".json_encode($stats).";

    jQuery.plot(jQuery('#mod-pf-stats-load-".$module->id."'), data,
    {
        bars: { show: true },
        xaxis: { show: false }
    });
});");
?>
<div id="mod-pf-stats-load-<?php echo $module->id;?>" style="<?php echo $css_w.$css_h;?>"></div>
<span class="label"><?php echo JText::_('MOD_PF_STATS_LOAD_GRAPH_DESC');?></span>
