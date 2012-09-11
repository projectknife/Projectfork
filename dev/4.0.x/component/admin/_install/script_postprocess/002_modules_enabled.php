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
//$base_installer = $adapter->getParent();
$installer   = JInstaller::getInstance();
$source_path = $installer->getPath('source');

$source_mod_path_site  = JPath::clean($source_path . '/modules/site');
$source_mod_path_admin = JPath::clean($source_path . '/modules/admin');

$site_modules_exist  = JFolder::exists($source_mod_path_site);
$admin_modules_exist = JFolder::exists($source_mod_path_admin);

// Do nothing if no module folders exist
if (!$site_modules_exist && !$admin_modules_exist) {
    return true;
}


// Collect module information
$module_paths       = array();
$site_module_names  = array();
$admin_module_names = array();

$db = JFactory::getDbo();

// Find site modules
if ($site_modules_exist) {
    // Find all files in the folder
    $site_module_files = (array) JFolder::files($source_mod_path_site);

    // Check if any of the files are archives and try to unpack them
    foreach($site_module_files AS $site_module_file)
    {
        $ext = JFile::getExt($site_module_file);

        if (!in_array($ext, array('zip', 'gzip', 'tar'))) {
            continue;
        }

        // Extract the archive
        $archive_name   = JFile::stripExt($site_module_file);
        $archive_source = JPath::clean($source_mod_path_site.'/' . $site_module_file);
        $unpack_dir     = JPath::clean($source_mod_path_site.'/' . $archive_name);

        JArchive::extract($archive_source, $unpack_dir);
    }

    // Get all folders
    $frontend_folders = (array) JFolder::folders($source_mod_path_site);

    foreach($frontend_folders AS $module_name)
    {
        $manifest_path = $source_mod_path_site . '/' . $module_name . '/' . $module_name . '.xml';

        if (JFile::exists($manifest_path)) {
            $query = $db->getQuery(true);

            $query->select('COUNT(extension_id)')
                  ->from('#__extensions')
                  ->where('element = ' . $db->quote($module_name))
                  ->where('type = ' . $db->quote('module'));

            $db->setQuery((string) $query);
            $module_exists = (int) $db->loadResult();

            if (!$module_exists) {
                $module_paths[] = $source_mod_path_site . '/' . $module_name;
                $site_module_names[] = $module_name;
            }
        }
    }
}

// Find admin modules
if ($admin_modules_exist) {
    // Find all files in the folder
    $admin_module_files = (array) JFolder::files($source_mod_path_admin);

    // Check if any of the files are archives and try to unpack them
    foreach($admin_module_files AS $admin_module_file)
    {
        $ext = JFile::getExt($admin_module_file);

        if (!in_array($ext, array('zip', 'gzip', 'tar'))) {
            continue;
        }

        // Extract the archive
        $archive_name   = JFile::stripExt($admin_module_file);
        $archive_source = JPath::clean($source_mod_path_admin . '/' . $admin_module_file);
        $unpack_dir     = JPath::clean($source_mod_path_admin . '/' . $archive_name);

        JArchive::extract($archive_source, $unpack_dir);
    }

    // Get all folders
    $admin_folders = (array) JFolder::folders($source_mod_path_admin);

    foreach($admin_folders AS $module_name)
    {
        $manifest_path = $source_mod_path_admin . '/' . $module_name . '/' . $module_name . '.xml';

        if (JFile::exists($manifest_path)) {
            $query = $db->getQuery(true);

            $query->select('COUNT(extension_id)')
                  ->from('#__extensions')
                  ->where('element = ' . $db->quote($module_name))
                  ->where('type = ' . $db->quote('module'));

            $db->setQuery((string) $query);
            $module_exists = (int) $db->loadResult();

            if (!$module_exists) {
                $module_paths[] = $source_mod_path_admin . '/' . $module_name;
                $admin_module_names[] = $module_name;
            }
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
$query = $db->getQuery(true);

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

if (!isset($custom_data['uninstall']['site_modules'])) {
    $custom_data['uninstall']['site_modules'] = array();
}

if (!isset($custom_data['uninstall']['admin_modules'])) {
    $custom_data['uninstall']['admin_modules'] = array();
}


// Add the data
foreach($site_module_names AS $mod_name)
{
    $custom_data['uninstall']['site_modules'][] = $mod_name;
}

foreach($admin_module_names AS $mod_name)
{
    $custom_data['uninstall']['admin_modules'][] = $mod_name;
}


// Update the field
$query = $db->getQuery(true);

$query->update('#__extensions')
      ->set('custom_data = ' . $db->quote(json_encode($custom_data)))
      ->where('element = ' . $db->quote('com_projectfork'))
      ->where('type = ' . $db->quote('component'));

$db->setQuery((string) $query);
$db->query();
