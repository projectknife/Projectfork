<?php
/**
* @package   Projectfork Task Distribution Statistics
* @copyright Copyright (C) 2012 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
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

    jQuery.plot(jQuery('#mod-pf-stats-dist-".$module->id."'), data,
    {
        series: {
 			pie: {
        		show: true,
                radius: 1,
                label: {
                    show: true,
                    radius: 3/4,
                    formatter: function(label, series) {
                        return '<div style=\'font-size:8pt;text-align:center;padding:2px;color:white;\'>'+label+'<br/>'+Math.round(series.percent)+'%</div>';
                    },
                    background: {
                        opacity: 0.5
                    }
                }
        	}
        },
        legend: {
            show: false
        }
    });


});");
?>
<div id="mod-pf-stats-dist-<?php echo $module->id;?>" style="<?php echo $css_w.$css_h;?>"></div>

