<?php
/**
* @package   Projectfork Task Distribution Statistics
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

if(!file_exists(JPATH_ADMINISTRATOR.'/components/com_projectfork/projectfork.php')) {
    echo JText::_('MOD_PF_STATS_DIST_PROJECTFORK_NOT_INSTALLED');
}
else {
    if(!file_exists(JPATH_ADMINISTRATOR.'/components/com_projectfork/helpers/projectfork.php')) {
        echo JText::_('MOD_PF_STATS_DIST_PROJECTFORK_FILE_NOT_FOUND');
    }
    else {
        // Include the helper classes
        require_once dirname(__FILE__).'/helper.php';
        require_once JPATH_ADMINISTRATOR.'/components/com_projectfork/helpers/projectfork.php';

        // Load jQuery and jQuery-Visualize
        JHtml::_('projectfork.jQuery');
        JHtml::_('projectfork.jQueryVisualize');

        // Get params
        $height   = (int) $params->get('height', 240);
        $width    = $params->get('width', 300);
        $show_c   = (int) $params->get('show_completed', 1);
        $show_u   = (int) $params->get('show_unassigned', 1);

        // Check if width param is in percent or pixel
        $in_percent = false;
        if(substr($width, -1) == '%') $in_percent = true;


        $width     = (int) $width;
        $width_js  = "width: '".$width."px',";
        $width_tbl = "";

        if($in_percent) {
            $width_js = '';
            $width_tbl = " width:".$width."%";
        }

        $refresh_js = '';
        if(!defined('JQUERY_VISUALIZE_REFRESH') && $in_percent) {
            define('JQUERY_VISUALIZE_REFRESH', 1);

            $refresh_js = "jQuery(window).resize(function(){"
                        . "jQuery('.visualize').trigger('visualizeRefresh');"
                        . "});";
        }

        // Initialize jQueryVisualize
        $doc = JFactory::getDocument();
        $doc->addScriptDeclaration("jQuery(function(){
                                        ".$refresh_js."
                                        jQuery('#mod-pf-stats-dist').visualize({
                                            type: 'pie',
                                            height: '".$height."px',
                                            ".$width_js."
                                            pieMargin: 10,
                                            appendTitle: false
                                        });
                                    });");

        // Get current project and statistics
        $project = modPFstatsDistHelper::getProject();
        $stats   = modPFstatsDistHelper::getStats($params, $project->id);

        // Include layout
        if(count($stats['users']) > 0 || $show_u == 1) {
            $moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
            require JModuleHelper::getLayoutPath('mod_pf_stats_dist', $params->get('layout', 'default'));
        }
    }
}
