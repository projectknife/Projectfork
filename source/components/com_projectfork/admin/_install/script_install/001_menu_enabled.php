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
$db->execute();

$query->clear();
$query->delete('#__menu')
      ->where('title = ' . $db->quote('com_projectfork'))
      ->where('menutype = ' . $db->quote('main'));

$db->setQuery($query);
$db->execute();


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
JLoader::register('MenusModelMenu', JPATH_ADMINISTRATOR . '/components/com_menus/models/menu.php');
$menu_model = new MenusModelMenu(array('ignore_request' => true));

// Create the menu
$data = array('title'       => 'Projectfork',
              'menutype'    => 'projectfork',
              'description' => 'Projectfork Menu');

$success = $menu_model->save($data);
$menu_id = $menu_model->getState('menu.id');


// Do not continue if menu creation failed
if (!$success || !$menu_id) return false;

$mm_pos = 'position-7';
$mm_st  = '1';

$module = JTable::getInstance('module');
$module->set('title', 'Projectfork');
$module->set('module', 'mod_menu');
$module->set('access', '1');
$module->set('showtitle', $mm_st);
$module->set('client_id', 0);
$module->set('language', '*');
$module->set('position', $mm_pos);
$module->set('params', '{"menutype":"projectfork"}');

$module->store();

// Create a module for the menu
/*$cols = array($db->quoteName('id'),
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
$db->execute();

$module_id = $db->insertid();

if (!$module_id) return false;*/

// Notify the user about the module position
$format = 'A Projectfork navigation module has been created on position "%s". You may need to change it in the Module Manager to fit into your template.';
$app    = JFactory::getApplication();

$app->enqueueMessage(JText::sprintf($format, $mm_pos));
