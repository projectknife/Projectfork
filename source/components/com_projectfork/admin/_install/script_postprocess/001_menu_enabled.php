<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

// Get new component id.
$com    = JComponentHelper::getComponent('com_projectfork');
$com_id = (is_object($com) && isset($com->id)) ? $com->id : 0;

if ($com_id) {
    $item = array();
    $item['title'] = 'Dashboard';
    $item['alias'] = 'dashboard';
    $item['link']  = 'index.php?option=com_projectfork&view=dashboard';
    $item['component_id'] = $com_id;

    PFInstallerHelper::addMenuItem($item);
}
