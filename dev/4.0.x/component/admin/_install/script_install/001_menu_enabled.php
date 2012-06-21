<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
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

defined( '_JEXEC' ) or die( 'Restricted access' );


$db    = JFactory::getDbo();
$query = $db->getQuery(true);


// Check if a projectfork menu already exists
$query->select('COUNT(id)')
      ->from('#__menu_types')
      ->where('menutype = '.$db->quote('projectfork'));

$db->setQuery($query->__toString());
$menu_exists = (int) $db->loadResult();


// Do nothing if the menu exists
if($menu_exists) return true;


// Get the Menu model
JModel::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_menus/models', 'MenusModel');
$menu_model = JModel::getInstance('Menu', 'MenusModel', array('ignore_request' => true));


// Create the menu
$data = array('title'       => 'Projectfork',
              'menutype'    => 'projectfork',
              'description' => 'Projectfork Menu');

$success = $menu_model->save($data);
$menu_id = $menu_model->getState('menu.id');


// Do not continue if menu creation failed
if(!$success || !$menu_id) return false;

// Prepare the menu items we want to create
$base_link  = 'index.php?option=com_projectfork';
$menu_items = array();


$menu_items[] = array('title' => 'Dashboard',
                      'alias' => 'dashboard',
                      'link'  => $base_link.'&view=dashboard');
$menu_items[] = array('title' => 'Projects',
                      'alias' => 'projects',
                      'link'  => $base_link.'&view=projects');
$menu_items[] = array('title' => 'Milestones',
                      'alias' => 'milestones',
                      'link'  => $base_link.'&view=milestones');
$menu_items[] = array('title' => 'Tasks',
                      'alias' => 'tasks',
                      'link'  => $base_link.'&view=tasks');


// Iterate through each item
foreach($menu_items AS $i => $menu_item)
{
    // Add default properties
    if(!isset($menu_item['menutype']))     $menu_item['menutype']     = 'projectfork';
    if(!isset($menu_item['parent_id']))    $menu_item['parent_id']    = '1';
    if(!isset($menu_item['level']))        $menu_item['level']        = '1';
    if(!isset($menu_item['published']))    $menu_item['published']    = '1';
    if(!isset($menu_item['type']))         $menu_item['type']         = 'component';
    if(!isset($menu_item['component_id'])) $menu_item['component_id'] = 0;
    if(!isset($menu_item['language']))     $menu_item['language']     = '*';
    if(!isset($menu_item['access']))       $menu_item['access']       = '1';
    if(!isset($menu_item['params']))       $menu_item['params']       = '{}';
    if(!isset($menu_item['ordering']))     $menu_item['ordering']     = ($i + 1);
    if(!isset($menu_item['id']))           $menu_item['id']           = null;

    // Save the menu item
    $row = JTable::getInstance ( 'menu', 'JTable' );

    foreach($menu_item AS $key => $value)
    {
        if(property_exists($row, $key)) {
            $row->$key = $value;
        }
    }

    $row->check();

	if(!$row->store()) {
		return false;
	}
}

// Try to find the position and showtitle of the (presumably) main menu
$query = $db->getQuery(true);

$query->select('position, showtitle')
      ->from('#__modules')
      ->where('id = 1');

$db->setQuery($query->__toString());
$main_menu = $db->loadObject();

if(is_object($main_menu)) {
    $mm_pos = $main_menu->position;
    $mm_st  = $main_menu->showtitle;
}
else {
    $mm_pos = '';
    $mm_st  = '1';
}


// Create a module for the menu
$cols = array($db->quoteName('id'),
              $db->quoteName('title'),
              $db->quoteName('position'),
              $db->quoteName('published'),
              $db->quoteName('module'),
              $db->quoteName('access'),
              $db->quoteName('showtitle'),
              $db->quoteName('params'),
              $db->quoteName('client_id'),
              $db->quoteName('language'));

$values = array('NULL',
                $db->quote('Projectfork'),
                $db->quote($mm_pos),
                $db->quote('1'),
                $db->quote('mod_menu'),
                $db->quote('1'),
                $db->quote($mm_st),
                $db->quote('{"menutype":"projectfork"}'),
                $db->quote('0'),
                $db->quote('*'));

$query = $db->getQuery(true);

$query->insert('#__modules')
      ->columns($cols)
      ->values(implode(', ', $values));

$db->setQuery($query->__toString());
$db->query();

$module_id = $db->insertid();

if(!$module_id) return false;


// Show the module on all pages
$query = $db->getQuery(true);

$query->insert('#__modules_menu')
      ->columns(array($db->quoteName('moduleid'), $db->quoteName('menuid')))
      ->values((int)$module_id . ', 0');

$db->setQuery($query->__toString());
$db->query();
