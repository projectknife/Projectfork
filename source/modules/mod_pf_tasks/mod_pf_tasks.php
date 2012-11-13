<?php
/**
* @package      Projectfork Tasks
*
* @author       Tobias Kuhn (eaxs)
* @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();


if (!jimport('projectfork.framework')) {
    echo JText::_('MOD_PF_TASKS_PROJECTFORK_LIB_NOT_INSTALLED');
    return;
}

if (!PFApplicationHelper::exists('com_projectfork')) {
    echo JText::_('MOD_PF_TASKS_PROJECTFORK_NOT_INSTALLED');
    return;
}


require_once dirname(__FILE__) . '/helper.php';

$items = modPFtasksHelper::getItems($params);

// Include layout
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
require JModuleHelper::getLayoutPath('mod_pf_tasks', $params->get('layout', 'default'));
