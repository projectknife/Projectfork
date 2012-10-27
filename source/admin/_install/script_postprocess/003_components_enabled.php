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

$source_com_path = JPath::clean($source_path . '/components');

// Do nothing if no components folders exist
if (!JFolder::exists($source_com_path)) {
    return true;
}


// Collect component information
$com_paths = array();
$com_names = array();

$db    = JFactory::getDbo();
$query = $db->getQuery(true);

// Find all files in the folder
$com_files = (array) JFolder::files($source_com_path);

// Check if any of the files are archives and try to unpack them
foreach($com_files AS $com_file)
{
    $ext = JFile::getExt($com_file);

    if (!in_array($ext, array('zip', 'gzip', 'tar'))) {
        continue;
    }

    // Extract the archive
    $archive_name   = JFile::stripExt($com_file);
    $archive_source = JPath::clean($source_com_path . '/' . $com_file);
    $unpack_dir     = JPath::clean($source_com_path . '/' . $archive_name);

    JArchive::extract($archive_source, $unpack_dir);
}

// Get all folders
$folders = (array) JFolder::folders($source_com_path);

foreach($folders AS $com_name)
{
    $manifest_path = $source_com_path .'/' . $com_name . '/' . str_replace('com_', '', $com_name) . '.xml';

    if (JFile::exists($manifest_path)) {
        $query->clear();
        $query->select('COUNT(extension_id)')
              ->from('#__extensions')
              ->where('element = ' . $db->quote($com_name))
              ->where('type = ' . $db->quote('component'));

        $db->setQuery((string) $query);
        $com_exists = (int) $db->loadResult();

        if (!$com_exists) {
            $com_paths[] = $source_com_path . '/' . $com_name;
            $com_names[] = $com_name;
        }
    }
}


// Install all components
$installer = new JInstaller();
foreach($com_paths AS $com)
{
    $installer->install($com);
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

if (!isset($custom_data['uninstall']['components'])) {
    $custom_data['uninstall']['components'] = array();
}


// Add the data
foreach($com_names AS $com_name)
{
    $custom_data['uninstall']['components'][] = $com_name;
}


// Update the field
$query->clear();
$query->update('#__extensions')
      ->set('custom_data = ' . $db->quote(json_encode($custom_data)))
      ->where('element = ' . $db->quote('com_projectfork'))
      ->where('type = ' . $db->quote('component'));

$db->setQuery((string) $query);
$db->query();
