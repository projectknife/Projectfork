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

$source_plg_path = JPath::clean($source_path . '/plugins');

// Do nothing if no plugins folders exist
if (!JFolder::exists($source_plg_path)) {
    return true;
}


// Collect plugin information
$plg_paths = array();
$plg_names = array();
$plg_types = array();

$db = JFactory::getDbo();

// Find all files in the folder
$plg_files = (array) JFolder::files($source_plg_path);

// Check if any of the files are archives and try to unpack them
foreach($plg_files AS $plg_file)
{
    $ext = JFile::getExt($plg_file);

    if (!in_array($ext, array('zip', 'gzip', 'tar'))) {
        continue;
    }

    // Extract the archive
    $archive_name   = JFile::stripExt($plg_file);
    $archive_source = JPath::clean($source_plg_path . '/' . $plg_file);
    $unpack_dir     = JPath::clean($source_plg_path . '/' . $archive_name);

    JArchive::extract($archive_source, $unpack_dir);
}

// Get all folders
$folders = (array) JFolder::folders($source_plg_path);

foreach($folders AS $plg_folder_name)
{
    $elements = explode('_', $plg_folder_name);

    // The plugin name must have at least 3 elements:
    // plg, <type>, <name>
    if (count($elements) < 3) {
        continue;
    }

    $plg_type = $elements[1];

    unset($elements[0]);
    unset($elements[1]);

    $plg_name = implode('_', $plg_folder_name);

    $manifest_path = $source_plg_path .'/' . $plg_folder_name . '/' . $plg_name . '.xml';

    if (JFile::exists($manifest_path)) {
        $query = $db->getQuery(true);

        $query->select('COUNT(extension_id)')
              ->from('#__extensions')
              ->where('element = ' . $db->quote($plg_name))
              ->where('type = ' . $db->quote('plugin'))
              ->where('folder = ' . $db->quote($plg_type));

        $db->setQuery((string) $query);
        $plg_exists = (int) $db->loadResult();

        if (!$plg_exists) {
            $plg_paths[] = $source_plg_path . '/' . $plg_folder_name;
            $plg_names[] = $plg_name;
            $plg_types[] = $plg_type;
        }
    }
}


// Install all plugins
$installer = new JInstaller();
foreach($plg_paths AS $plg)
{
    $installer->install($plg);
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

if (!isset($custom_data['uninstall']['plugins'])) {
    $custom_data['uninstall']['plugins'] = array();
}


// Add the data
foreach($plg_names AS $plg_name)
{
    $custom_data['uninstall']['plugins'][] = $plg_type . '/' . $plg_name;
}


// Update the field
$query = $db->getQuery(true);

$query->update('#__extensions')
      ->set('custom_data = ' . $db->quote(json_encode($custom_data)))
      ->where('element = ' . $db->quote('com_projectfork'))
      ->where('type = ' . $db->quote('component'));

$db->setQuery((string) $query);
$db->query();
