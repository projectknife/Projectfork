<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


$app = JFactory::getApplication();
$app->setUserState('com_projectfork.uninstall', true);

// Check if the scripts folder exists
$script_folder = dirname(__FILE__) . '/script_uninstall';

if (!JFolder::exists($script_folder)) {
    $app->enqueueMessage('Uninstall script folder not found!');
    return false;
}

// Get all script files
$script_files = (array) JFolder::files($script_folder, '.php');
sort($script_files);

// Iterate through all scripts
foreach($script_files AS $file)
{
    // Skip disabled scripts
    if (stripos($file, 'enabled') === false || stripos($file, 'disabled')) {
        continue;
    }

    // Run the script
    if (file_exists($script_folder . '/' . $file)) {
        require_once($script_folder . '/' . $file);
    }
}

$app->setUserState('com_projectfork.uninstall', false);