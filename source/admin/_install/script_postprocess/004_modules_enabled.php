<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


// Get the tmp source path
$installer   = JInstaller::getInstance();
$source_path = $installer->getPath('source');

$source_mod_path = JPath::clean($source_path . '/modules');
$modules_exist   = JFolder::exists($source_mod_path);

// Do nothing if no modules folders exist
if (!$modules_exist) {
    return true;
}


// Collect module information
$module_paths = array();
$module_names = array();

$db    = JFactory::getDbo();
$query = $db->getQuery(true);

// Find all files in the folder
$module_files = (array) JFolder::files($source_mod_path);

// Check if any of the files are archives and try to unpack them
foreach($module_files AS $module_file)
{
    $ext = JFile::getExt($module_file);

    if (!in_array($ext, array('zip', 'gzip', 'tar'))) {
        continue;
    }

    // Extract the archive
    $archive_name   = JFile::stripExt($module_file);
    $archive_source = JPath::clean($source_mod_path .'/' . $module_file);
    $unpack_dir     = JPath::clean($source_mod_path .'/' . $archive_name);

    JArchive::extract($archive_source, $unpack_dir);
}

// Get all folders
$folders = (array) JFolder::folders($source_mod_path);

foreach($folders AS $module_name)
{
    $manifest_path = $source_mod_path . '/' . $module_name . '/' . $module_name . '.xml';

    if (JFile::exists($manifest_path)) {
        $query->clear();
        $query->select('COUNT(extension_id)')
              ->from('#__extensions')
              ->where('element = ' . $db->quote($module_name))
              ->where('type = ' . $db->quote('module'));

        $db->setQuery((string) $query);
        $module_exists = (int) $db->loadResult();

        if (!$module_exists) {
            $module_paths[] = $source_mod_path . '/' . $module_name;
            $module_names[] = $module_name;
        }
    }
}


// Install all modules
$installer = new JInstaller();
foreach($module_paths AS $module)
{
    $installer->install($module);
}
unset($installer);


// Get extension custom data
$query->clear();
$query->select('custom_data')
      ->from('#__extensions')
      ->where('element = ' . $db->quote('com_projectfork'))
      ->where('type = ' . $db->quote('component'));

$db->setQuery((string) $query);
$custom_data = $db->loadResult();
$custom_data = ($custom_data == '') ? array() : json_decode($custom_data, true);

// Check the data keys
if (!isset($custom_data['uninstall'])) {
    $custom_data['uninstall'] = array();
}

if (!isset($custom_data['uninstall']['modules'])) {
    $custom_data['uninstall']['modules'] = array();
}

// Add the data
foreach($module_names AS $mod_name)
{
    $custom_data['uninstall']['modules'][] = $mod_name;
}

// Update the field
$query->clear();
$query->update('#__extensions')
      ->set('custom_data = ' . $db->quote(json_encode($custom_data)))
      ->where('element = ' . $db->quote('com_projectfork'))
      ->where('type = ' . $db->quote('component'));

$db->setQuery((string) $query);
$db->query();
