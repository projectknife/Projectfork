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

$source_lib_path = JPath::clean($source_path . '/libraries');

// Do nothing if no libraries folders exist
if (!JFolder::exists($source_lib_path)) {
    return true;
}


// Collect library information
$lib_paths = array();
$lib_names = array();

$db    = JFactory::getDbo();
$query = $db->getQuery(true);

// Find all files in the folder
$lib_files = (array) JFolder::files($source_lib_path);

// Check if any of the files are archives and try to unpack them
foreach($lib_files AS $lib_file)
{
    $ext = JFile::getExt($lib_file);

    if (!in_array($ext, array('zip', 'gzip', 'tar'))) {
        continue;
    }

    // Extract the archive
    $archive_name   = JFile::stripExt($lib_file);
    $archive_source = JPath::clean($source_lib_path . '/' . $lib_file);
    $unpack_dir     = JPath::clean($source_lib_path . '/' . $archive_name);

    JArchive::extract($archive_source, $unpack_dir);
}

// Get all folders
$folders = (array) JFolder::folders($source_lib_path);

foreach($folders AS $lib_name)
{
    $manifest_path = $source_lib_path .'/' . $lib_name . '/' . $lib_name . '.xml';

    if (JFile::exists($manifest_path)) {
        $query->clear();
        $query->select('COUNT(extension_id)')
              ->from('#__extensions')
              ->where('element = ' . $db->quote($lib_name))
              ->where('type = ' . $db->quote('library'));

        $db->setQuery((string) $query);
        $lib_exists = (int) $db->loadResult();

        if (!$lib_exists) {
            $lib_paths[] = $source_lib_path . '/' . $lib_name;
            $lib_names[] = $lib_name;
        }
    }
}


// Install all libraries
$installer = new JInstaller();
foreach($lib_paths AS $lib)
{
    $installer->install($lib);
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

if (!isset($custom_data['uninstall']['libraries'])) {
    $custom_data['uninstall']['libraries'] = array();
}


// Add the data
foreach($lib_names AS $lib_name)
{
    $custom_data['uninstall']['libraries'][] = $lib_name;
}


// Update the field
$query->clear();
$query->update('#__extensions')
      ->set('custom_data = ' . $db->quote(json_encode($custom_data)))
      ->where('element = ' . $db->quote('com_projectfork'))
      ->where('type = ' . $db->quote('component'));

$db->setQuery((string) $query);
$db->query();
