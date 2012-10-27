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

$source_tmpl_path = JPath::clean($source_path . '/templates');
$templates_exist  = JFolder::exists($source_tmpl_path);

// Do nothing if no template folders exist
if (!$templates_exist) {
    return true;
}


// Collect template information
$tmpl_paths = array();
$tmpl_names = array();

$db    = JFactory::getDbo();
$query = $db->getQuery(true);

// Find all files in the folder
$template_files = (array) JFolder::files($source_tmpl_path);

// Check if any of the files are archives and try to unpack them
foreach($template_files AS $template_file)
{
    $ext = JFile::getExt($template_file);

    if (!in_array($ext, array('zip', 'gzip', 'tar'))) {
        continue;
    }

    // Extract the archive
    $archive_name   = JFile::stripExt($template_file);
    $archive_source = JPath::clean($source_tmpl_path . '/' . $template_file);
    $unpack_dir     = JPath::clean($source_tmpl_path . '/' . $archive_name);

    JArchive::extract($archive_source, $unpack_dir);
}

// Get all folders
$folders = (array) JFolder::folders($source_tmpl_path);

foreach($folders AS $tmpl_name)
{
    $manifest_path = $source_tmpl_path . '/' . $tmpl_name . '/templateDetails.xml';

    if (JFile::exists($manifest_path)) {
        $query->clear();
        $query->select('COUNT(extension_id)')
              ->from('#__extensions')
              ->where('element = ' . $db->quote($tmpl_name))
              ->where('type = ' . $db->quote('template'));

        $db->setQuery((string) $query);
        $tmpl_exists = (int) $db->loadResult();

        if (!$tmpl_exists) {
            $tmpl_paths[] = $source_tmpl_path . '/' . $tmpl_name;
            $tmpl_names[] = $tmpl_name;
        }
    }
}

// Install all templates
$installer = new JInstaller();
foreach($tmpl_paths AS $tmpl)
{
    $installer->install($tmpl);
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

if (!isset($custom_data['uninstall']['templates'])) {
    $custom_data['uninstall']['templates'] = array();
}

// Add the data
foreach($tmpl_names AS $tmpl_name)
{
    $custom_data['uninstall']['templates'][] = $tmpl_name;
}

// Update the field
$query->clear();
$query->update('#__extensions')
      ->set('custom_data = ' . $db->quote(json_encode($custom_data)))
      ->where('element = ' . $db->quote('com_projectfork'))
      ->where('type = ' . $db->quote('component'));

$db->setQuery((string) $query);
$db->query();
