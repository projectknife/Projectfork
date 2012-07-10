<?php
/**
* @package   Projectfork Dashboard Buttons
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

// Disable this module on the "user" view if it is positioned on the dashboard
if(stripos($module->position, 'pf-dashboard') !== false &&
   JRequest::getVar('option') == 'com_projectfork' &&
   JRequest::getVar('view') == 'user')
{
    return '';
}


if(!file_exists(JPATH_ADMINISTRATOR.'/components/com_projectfork/projectfork.php')) {
    echo JText::_('MOD_PF_DASH_BUTTONS_PROJECTFORK_NOT_INSTALLED');
}
else {
    if(!file_exists(JPATH_ADMINISTRATOR.'/components/com_projectfork/helpers/projectfork.php')) {
        echo JText::_('MOD_PF_STATS_DIST_PROJECTFORK_FILE_NOT_FOUND');
    }
    else {
        // Include the helper classes
        require_once dirname(__FILE__).'/helper.php';
        require_once JPATH_ROOT.'/components/com_projectfork/helpers/route.php';

        // Get buttons
        $buttons = modPFdashButtonsHelper::getButtons();

        // Include layout
        $moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
        require JModuleHelper::getLayoutPath('mod_pf_dash_buttons', $params->get('layout', 'default'));
    }
}
