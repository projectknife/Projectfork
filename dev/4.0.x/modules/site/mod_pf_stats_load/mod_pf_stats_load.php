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
    echo JText::_('MOD_PF_STATS_LOAD_PROJECTFORK_NOT_INSTALLED');
}
else {
    // Include the helper classes
    require_once dirname(__FILE__).'/helper.php';

    // Load jQuery and jQuery-Visualize
    JHtml::_('projectfork.jQuery');
    JHtml::_('projectfork.jQueryVisualize');

    // Get params
    $height = (int) $params->get('height', 240);
    $width  = (int) $params->get('width', 300);

    // Initialize jQueryVisualize
    $doc = JFactory::getDocument();
    $doc->addScriptDeclaration("jQuery(function(){jQuery('#mod-pf-stats-load').visualize({
                                        type: 'bar',
                                        height: '".$height."px',
                                        width: '".$width."px',
                                        appendTitle: false
                                    });
                                });");

    // Get current project and statistics
    $stats = modPFstatsLoadHelper::getStats($params);

    // Include layout
    if(count($stats) > 0) {
        $moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
        require JModuleHelper::getLayoutPath('mod_pf_stats_load', $params->get('layout', 'default'));
    }
}
