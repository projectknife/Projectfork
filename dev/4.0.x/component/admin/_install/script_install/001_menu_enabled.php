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

// Delete projectfork backend menu item if it exists
// This should help to avoid duplicate entries
$query->delete('#__menu')
      ->where('title = ' . $db->quote('projectfork'))
      ->where('client_id = 1');

$db->setQuery($query);
$db->query();


// Check if a projectfork menu already exists
$query->clear();
$query->select('COUNT(id)')
      ->from('#__menu_types')
      ->where('menutype = ' . $db->quote('projectfork'));

$db->setQuery((string) $query);
$menu_exists = (int) $db->loadResult();


// Do nothing if the menu exists
if ($menu_exists) return true;


// Get the Menu model
JModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_menus/models', 'MenusModel');
$menu_model = JModel::getInstance('Menu', 'MenusModel', array('ignore_request' => true));


// Create the menu
$data = array('title'       => 'Projectfork',
              'menutype'    => 'projectfork',
              'description' => 'Projectfork Menu');

$success = $menu_model->save($data);
$menu_id = $menu_model->getState('menu.id');


// Do not continue if menu creation failed
if (!$success || !$menu_id) return false;

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
$menu_items[] = array('title' => 'Discussions',
                      'alias' => 'discussions',
                      'link'  => $base_link.'&view=topics');
$menu_items[] = array('title' => 'Users',
                      'alias' => 'project-members',
                      'link'  => $base_link.'&view=users');


// Iterate through each item
foreach($menu_items AS $i => $menu_item)
{
    // Add default properties
    if (!isset($menu_item['menutype']))     $menu_item['menutype']     = 'projectfork';
    if (!isset($menu_item['parent_id']))    $menu_item['parent_id']    = '1';
    if (!isset($menu_item['level']))        $menu_item['level']        = '1';
    if (!isset($menu_item['published']))    $menu_item['published']    = '1';
    if (!isset($menu_item['type']))         $menu_item['type']         = 'component';
    if (!isset($menu_item['component_id'])) $menu_item['component_id'] = 0;
    if (!isset($menu_item['language']))     $menu_item['language']     = '*';
    if (!isset($menu_item['access']))       $menu_item['access']       = '1';
    if (!isset($menu_item['params']))       $menu_item['params']       = '{}';
    if (!isset($menu_item['ordering']))     $menu_item['ordering']     = ($i + 1);
    if (!isset($menu_item['id']))           $menu_item['id']           = null;

    // Save the menu item
    $row = JTable::getInstance('menu', 'JTable');

    foreach($menu_item AS $key => $value)
    {
        if (property_exists($row, $key)) {
            $row->$key = $value;
        }
    }

    $row->check();

    if (!$row->store()) {
        return false;
    }
}


$mm_pos = 'position-7';
$mm_st  = '1';

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
                $db->quote('0'),
                $db->quote('mod_menu'),
                $db->quote('1'),
                $db->quote($mm_st),
                $db->quote('{"menutype":"projectfork"}'),
                $db->quote('0'),
                $db->quote('*'));


$query->clear();
$query->insert('#__modules')
      ->columns($cols)
      ->values(implode(', ', $values));

$db->setQuery((string) $query);
$db->query();

$module_id = $db->insertid();

if (!$module_id) return false;


// Show the module on all pages
$query->clear();
$query->insert('#__modules_menu')
      ->columns(array($db->quoteName('moduleid'), $db->quoteName('menuid')))
      ->values((int)$module_id . ', 0');

$db->setQuery((string) $query);
$db->query();

// Notify the user about the module position
$format = 'A Projectfork navigation module has been created on position "%s". You may need to change it in the Module Manager to fit into your template.';
$app    = JFactory::getApplication();

$app->enqueueMessage(JText::sprintf($format, $mm_pos));
