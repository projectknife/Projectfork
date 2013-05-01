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


// Include the PF library
jimport('projectfork.library');

// Add table include paths
JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_pfrepo/tables');
JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_pfcomments/tables');

// Rebuild directories
$query->clear()
      ->select('id')
      ->from('#__pf_repo_dirs')
      ->where('id = 1');

$db->setQuery($query);
$exists = $db->loadResult();

if ($exists) {
    $table = JTable::getInstance('Directory', 'PFTable');
    $table->rebuild(1);
}

// Rebuild comments
$query->clear()
      ->select('id')
      ->from('#__pf_comments')
      ->where('id = 1');

$db->setQuery($query);
$exists = $db->loadResult();

if ($exists) {
    $table = JTable::getInstance('Comment', 'PFtable');
    $table->rebuild(1);
}

// Fix task list asset names
$query->clear()
      ->select('id, name')
      ->from('#__assets')
      ->where('name LIKE ' . $db->quote($db->escape('com_pftasklists.tasklist.', true) . '%'));

$db->setQuery($query);
$assets = (array) $db->loadObjectList();

foreach ($assets AS $asset)
{
    $name = str_replace('com_pftasklists.tasklist.', 'com_pftasks.tasklist.', $asset->name);

    $query->clear()
          ->update('#__assets')
          ->set('name = ' . $db->quote($name))
          ->where('id = ' . $asset->id);

    $db->setQuery($query);
    $db->execute();
}
