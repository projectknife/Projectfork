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


if(!file_exists(JPATH_ADMINISTRATOR.'/components/com_projectfork/projectfork.php')) {
    // Projectfork does not appear to be installed
    echo JText::_('MOD_PF_STATS_TASKS_PROJECTFORK_NOT_INSTALLED');
}
else {
    if(!file_exists(JPATH_ADMINISTRATOR.'/components/com_projectfork/helpers/projectfork.php')) {
        // Projectfork helper class not found
        echo JText::_('MOD_PF_STATS_TASKS_PROJECTFORK_FILE_NOT_FOUND');
    }
    else {
        // Include the helper classes
        require_once dirname(__FILE__).'/helper.php';
        require_once JPATH_ADMINISTRATOR.'/components/com_projectfork/helpers/projectfork.php';

        // Load jQuery and jQuery-Flot
        JHtml::_('projectfork.jQuery');
        JHtml::_('projectfork.jQueryFlot');

        // Get params
        $height = $params->get('height', 300);
        $width  = $params->get('width', '100%');

        // Check if width and height params are in percent or pixel
        $css_w = (substr($width, -1) == '%'  ? "width:".intval($width)."%;"   : "width:".intval($width)."px;");
        $css_h = (substr($height, -1) == '%' ? "height:".intval($height)."%;" : "height:".intval($height)."px;");


        // Get current project and statistics
        $stats = modPFstatsLoadHelper::getStats($params);

        // Include layout
        if(count($stats) > 0) {
            $moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
            require JModuleHelper::getLayoutPath('mod_pf_stats_load', $params->get('layout', 'default'));
        }
    }
}
