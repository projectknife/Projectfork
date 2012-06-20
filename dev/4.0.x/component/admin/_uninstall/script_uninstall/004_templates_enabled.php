<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
*
* This file is part of Projectfork.
*
* Projectfork is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*
* Projectfork is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Projectfork. If not, see <http://www.gnu.org/licenses/gpl.html>.
**/

defined( '_JEXEC' ) or die( 'Restricted access' );


$db    = JFactory::getDbo();
$query = $db->getQuery(true);

// Get the custom data from the component
$query->select('custom_data')
      ->from('#__extensions')
      ->where('element = '.$db->quote('com_projectfork'))
      ->where('type = '.$db->quote('component'));

$db->setQuery($query->__toString());
$custom_data = $db->loadResult();
$custom_data = ($custom_data == '') ? array() : json_decode($custom_data, true);


// Check data keys
if(!isset($custom_data['uninstall']['site_templates'])) {
    $custom_data['uninstall']['site_templates'] = array();
}

if(!isset($custom_data['uninstall']['admin_templates'])) {
    $custom_data['uninstall']['admin_templates'] = array();
}


// Get the templates
$site_templates  = $custom_data['uninstall']['site_templates'];
$admin_templates = $custom_data['uninstall']['admin_templates'];
$installer       = new JInstaller();


// Uninstall site templates
foreach($site_templates AS $tmpl_name)
{
    $query = $db->getQuery(true);

    $query->select('extension_id')
          ->from('#__extensions')
          ->where('element = '.$db->quote($tmpl_name))
          ->where('type = '.$db->quote('template'));

    $db->setQuery($query->__toString());
    $tmpl_id = (int) $db->loadResult();

    if($tmpl_id) {
        // Check if the template is set to default
        $query = $db->getQuery(true);

        $query->select('home')
              ->from('#__template_styles')
              ->where('template = '.$db->quote($tmpl_name));

        $db->setQuery($query->__toString());
        $is_home = (int) $db->loadResult();

        if(!$is_home) {
            // Uninstall if its not set to default
            $installer->uninstall('template', $tmpl_id);
        }
    }
}

// Uninstall admin templates
foreach($admin_templates AS $tmpl_name)
{
    $query = $db->getQuery(true);

    $query->select('extension_id')
          ->from('#__extensions')
          ->where('element = '.$db->quote($tmpl_name))
          ->where('type = '.$db->quote('template'));

    $db->setQuery($query->__toString());
    $tmpl_id = (int) $db->loadResult();

    if($tmpl_id) {
        // Check if the template is set to default
        $query = $db->getQuery(true);

        $query->select('home')
              ->from('#__template_styles')
              ->where('template = '.$db->quote($tmpl_name));

        $db->setQuery($query->__toString());
        $is_home = (int) $db->loadResult();

        if(!$is_home) {
            // Uninstall if its not set to default
            $installer->uninstall('template', $tmpl_id);
        }
    }
}
