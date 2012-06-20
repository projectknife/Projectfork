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


if(!file_exists(JPATH_ADMINISTRATOR.'/components/com_projectfork/projectfork.php')) {
    echo JText::_('MOD_PF_DASH_BUTTONS_PROJECTFORK_NOT_INSTALLED');
}
else {
    require_once(dirname(__FILE__).'/helper.php');

    // Get buttons
    $buttons = modPFdashButtonsHelper::getButtons();

    // Include layout
    $moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
    require JModuleHelper::getLayoutPath('mod_pf_dash_buttons', $params->get('layout', 'default'));
}
