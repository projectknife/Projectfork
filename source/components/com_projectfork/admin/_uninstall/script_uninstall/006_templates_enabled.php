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
if (!isset($custom_data['uninstall']['templates'])) {
    $custom_data['uninstall']['templates'] = array();
}

// Get the templates
$templates = $custom_data['uninstall']['templates'];
$installer = new JInstaller();

// Uninstall templates
foreach($templates AS $tmpl_name)
{
    $query->clear();
    $query->select('extension_id')
          ->from('#__extensions')
          ->where('element = ' . $db->quote($tmpl_name))
          ->where('type = ' . $db->quote('template'));

    $db->setQuery((string) $query);
    $tmpl_id = (int) $db->loadResult();

    if ($tmpl_id) {
        // Check if the template is set to default
        $query->clear();
        $query->select('home')
              ->from('#__template_styles')
              ->where('template = ' . $db->quote($tmpl_name));

        $db->setQuery((string) $query);
        $is_home = (int) $db->loadResult();

        if (!$is_home) {
            // Uninstall if its not set to default
            $installer->uninstall('template', $tmpl_id);
        }
    }
}
