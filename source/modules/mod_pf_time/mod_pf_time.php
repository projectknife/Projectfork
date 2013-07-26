<?php
/**
* @package      Projectfork Timesheet Module
*
* @author       ANGEK DESIGN (Kon Angelopoulos)
* @copyright    Copyright (C) 2013 ANGEK DESIGN. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();


if (!jimport('projectfork.framework')) {
    echo JText::_('MOD_PF_TIME_PROJECTFORK_LIB_NOT_INSTALLED');
    return;
}

if (!PFApplicationHelper::exists('com_projectfork')) {
    echo JText::_('MOD_PF_TIME_PROJECTFORK_NOT_INSTALLED');
    return;
}


require_once dirname(__FILE__) . '/helper.php';

$items = modPFtimeHelper::getItems($params);



// Include layout
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
require JModuleHelper::getLayoutPath('mod_pf_time', $params->get('layout', 'default'));
