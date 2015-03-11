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

// Restore admin component menu item if not exists (Joomla 3.4)
if (version_compare(JVERSION, '3.4', 'ge')) {
    $db    = JFactory::getDbo();
    $query = $db->getQuery(true);

    // Check if the menu item exists
    $query->select('id')
          ->from('#__menu')
          ->where('title = ' . $db->quote('com_projectfork'))
          ->where('menutype = ' . $db->quote('main'))
          ->where('client_id = 1');

    $db->setQuery($query);
    $menu_id = (int) $db->loadResult();

    if ($menu_id) {
        return true;
    }


    $data = array();
    $data['menutype']     = 'main';
    $data['title']        = 'com_projectfork';
    $data['alias']        = 'com-projectfork';
    $data['link']         = 'index.php?option=com_projectfork';
    $data['type']         = 'component';
    $data['published']    = 0;
    $data['parent_id']    = 1;
    $data['component_id'] = $com_id;
    $data['img']          = 'class:component';
    $data['home']         = 0;
    $data['language']     = '*';
    $data['client_id']    = 1;

    $menu = JTable::getInstance('menu');
    $menu->setLocation(1, 'last-child');

    $menu->bind($data);
    $menu->check();
    $menu->store();
}

