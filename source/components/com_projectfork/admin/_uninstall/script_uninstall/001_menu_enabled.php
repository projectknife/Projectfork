<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


$db    = JFactory::getDbo();
$query = $db->getQuery(true);

// Get the Menu model
JLoader::register('MenusModelMenu', JPATH_ADMINISTRATOR . '/components/com_menus/models/menu.php');

$options    = array('ignore_request' => true);
$menu_model = new MenusModelMenu($options);

// Find the menu id
$query->select('id')
      ->from('#__menu_types')
      ->where('menutype = ' . $db->quote('projectfork'));

$db->setQuery($query->__toString());
$menu_id = (int) $db->loadResult();

if(!$menu_id) return false;

$data = array($menu_id);

// Delete the menu
return $menu_model->delete($data);
