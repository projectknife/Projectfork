<?php
/**
* @package      Projectfork Task Statistics
*
* @author       Tobias Kuhn (eaxs)
* @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();


if (!file_exists(JPATH_ADMINISTRATOR.'/components/com_projectfork/projectfork.php')) {
    // Projectfork does not appear to be installed
    echo JText::_('MOD_PF_STATS_TASKS_PROJECTFORK_NOT_INSTALLED');
}
else {
    // Include dependencies
    JHtml::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_projectfork/helpers/html');
    JLoader::register('ProjectforkHelper', JPATH_ADMINISTRATOR . '/components/com_projectfork/helpers/projectfork.php');
    require_once dirname(__FILE__) . '/helper.php';

    // Load jQuery and Flot
    JHtml::_('projectfork.script.jQuery');
    JHtml::_('projectfork.script.flot');

    // Get params
    $height   = $params->get('height', 300);
    $width    = $params->get('width', '100%');
    $show_a   = (int) $params->get('show_archived', 1);
    $show_t   = (int) $params->get('show_trashed', 1);

    $colors = array();
    $colors[] = $params->get('color_completed', '66CC66');
    $colors[] = $params->get('color_pending', 'FFCC66');
    $colors[] = $params->get('color_archived', 'FF99FF');
    $colors[] = $params->get('color_trashed', '6699CC');

    // Check if width and height params are in percent or pixel
    $css_w = (substr($width, -1) == '%'  ? "width:" . intval($width) . "%;"   : "width:" . intval($width) . "px;");
    $css_h = (substr($height, -1) == '%' ? "height:" . intval($height) . "%;" : "height:" . intval($height) . "px;");

    // Make sure the colors have a hash symbol
    foreach($colors AS $i => $color)
    {
        $colors[$i] = ($color[0] == '#') ? '"' . $color.'"' : '"#' . $color . '"';
    }
    $colors = '[' . implode(', ', $colors) . ']';

    // Get the current option, view and user id
    $option = JRequest::getCmd('option');
    $view   = JRequest::getCmd('view');
    $uid    = JRequest::getUint('id');

    if ($option == 'com_projectfork' && $view == 'user' && $uid > 0) {
        // Get stats for the current user
        $stats = modPFstatsTasksHelper::getStatsUser($uid, $show_a, $show_t);
    }
    else {
        // Get current project and statistics
        $project = modPFstatsTasksHelper::getProject();
        $stats   = modPFstatsTasksHelper::getStatsProject($project->id, $show_a, $show_t);
    }

    // Include layout
    if (count($stats)) {
        $moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
        require JModuleHelper::getLayoutPath('mod_pf_stats_tasks', $params->get('layout', 'default'));
    }
}
