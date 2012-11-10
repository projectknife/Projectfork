<?php
/**
* @package      Projectfork Dashboard Buttons
*
* @author       Tobias Kuhn (eaxs)
* @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();


$option = JRequest::getVar('option');
$view   = JRequest::getVar('view');

// Disable this module on the "user" view if it is positioned on the dashboard
if (stripos($module->position, 'pf-dashboard') !== false && $option == 'com_projectfork' && $view == 'user') {
    return '';
}

// Stop if projectfork is not installed
if (!file_exists(JPATH_SITE . '/components/com_projectfork/projectfork.php')) {
    echo JText::_('MOD_PF_DASH_BUTTONS_PROJECTFORK_NOT_INSTALLED');
}
else {
    // Include dependencies
    jimport('projectfork.library');

    require_once dirname(__FILE__) . '/helper.php';

    // Get buttons
    $buttons = modPFdashButtonsHelper::getButtons();

    // Include layout
    $moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
    require JModuleHelper::getLayoutPath('mod_pf_dash_buttons', $params->get('layout', 'default'));
}
