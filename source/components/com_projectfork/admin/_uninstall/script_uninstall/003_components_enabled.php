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
if (!isset($custom_data['uninstall']['components'])) {
    $custom_data['uninstall']['components'] = array();
}

// Get the components
$components = $custom_data['uninstall']['components'];
$installer  = new JInstaller();

// Uninstall components
foreach($components AS $com_name)
{
    $query->clear();
    $query->select('extension_id')
          ->from('#__extensions')
          ->where('element = ' . $db->quote($com_name))
          ->where('type = ' . $db->quote('component'));

    $db->setQuery((string) $query);
    $com_id = (int) $db->loadResult();

    if ($com_id) {
        $installer->uninstall('component', $com_id);
    }
}
