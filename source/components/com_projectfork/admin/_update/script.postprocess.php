<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


$db     = JFactory::getDbo();
$app    = JFactory::getApplication();
$folder = dirname(__FILE__) . '/script_postprocess';
$query  = $db->getQuery(true);

// Check if the scripts folder exists
if (!JFolder::exists($folder)) {
    $app->enqueueMessage('Post-process script update folder not found!');
    return false;
}

// Get all script files
$files = (array) str_replace('.php', '', JFolder::files($folder, '\.php$'));
usort($files, 'version_compare');

// Iterate through all scripts
foreach($files AS $file)
{
    if (version_compare($file, $prev_version, '>')) {
        // Run the script
        if (file_exists($folder . '/' . $file . '.php')) {
            require_once $folder . '/' . $file . '.php';
        }
    }
}


// Restore com_pfdesigns admin menu item
$query->clear();
$query->select('extension_id')
      ->from('#__extensions')
      ->where('name = ' . $db->quote('com_pfdesigns'))
      ->where('type = ' . $db->quote('component'));

$db->setQuery($query);
$designs_id = $db->loadResult();

if ($designs_id) {
    if (!defined('PF_LIBRARY')) {
        jimport('projectfork.library');
    }

    PFInstallerHelper::setComponentMenuItem('com_pfdesigns');
}