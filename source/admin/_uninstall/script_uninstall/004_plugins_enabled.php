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

// Get the custom data from the component
$query->select('custom_data')
      ->from('#__extensions')
      ->where('element = ' . $db->quote('com_projectfork'))
      ->where('type = ' . $db->quote('component'));

$db->setQuery((string) $query);
$custom_data = $db->loadResult();
$custom_data = ($custom_data == '') ? array() : json_decode($custom_data, true);

// Check data keys
if (!isset($custom_data['uninstall']['plugins'])) {
    $custom_data['uninstall']['plugins'] = array();
}

// Get the plugins
$plugins   = $custom_data['uninstall']['plugins'];
$installer = new JInstaller();

// Uninstall
foreach($plugins AS $plg)
{
    $elements = explode('/', $plg);
    $type = $elements[0];
    $name = $elements[1];

    $query->clear();
    $query->select('extension_id')
          ->from('#__extensions')
          ->where('element = ' . $db->quote($name))
          ->where('folder = ' . $db->quote($type))
          ->where('type = ' . $db->quote('plugin'));

    $db->setQuery((string) $query);
    $plg_id = (int) $db->loadResult();

    if ($plg_id) {
        $installer->uninstall('plugin', $plg_id);
    }
}
