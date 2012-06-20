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


// Get the tmp source path
$installer   = JInstaller::getInstance();
$source_path = $installer->getPath('source');

$source_tmpl_path_site  = $source_path.'/templates/site';
$source_tmpl_path_admin = $source_path.'/templates/admin';

$site_templates_exist  = JFolder::exists($source_tmpl_path_site);
$admin_templates_exist = JFolder::exists($source_tmpl_path_admin);

// Do nothing if no template folders exist
if(!$site_templates_exist && !$admin_templates_exist) {
    return true;
}


// Collect template information
$tmpl_paths       = array();
$site_tmpl_names  = array();
$admin_tmpl_names = array();

$db = JFactory::getDbo();

// Find site templates
if($site_templates_exist) {
    $frontend_folders = (array) JFolder::folders($source_tmpl_path_site);

    foreach($frontend_folders AS $tmpl_name)
    {
        $manifest_path = $source_tmpl_path_site.'/'.$tmpl_name.'/templateDetails.xml';

        if(JFile::exists($manifest_path)) {
            $query = $db->getQuery(true);

            $query->select('COUNT(extension_id)')
                  ->from('#__extensions')
                  ->where('element = '.$db->quote($tmpl_name))
                  ->where('type = '.$db->quote('template'));

            $db->setQuery($query->__toString());
            $tmpl_exists = (int) $db->loadResult();

            if(!$tmpl_exists) {
                $tmpl_paths[] = $source_tmpl_path_site.'/'.$tmpl_name;
                $site_tmpl_names[] = $tmpl_name;
            }
        }
    }
}

// Find admin templates
if($admin_templates_exist) {
    $backend_folders = (array) JFolder::folders($source_tmpl_path_admin);

    foreach($backend_folders AS $tmpl_name)
    {
        $manifest_path = $source_tmpl_path_admin.'/'.$tmpl_name.'/templateDetails.xml';

        if(JFile::exists($manifest_path)) {
            $query = $db->getQuery(true);

            $query->select('COUNT(extension_id)')
                  ->from('#__extensions')
                  ->where('element = '.$db->quote($tmpl_name))
                  ->where('type = '.$db->quote('template'));

            $db->setQuery($query->__toString());
            $tmpl_exists = (int) $db->loadResult();

            if(!$tmpl_exists) {
                $tmpl_paths[] = $source_tmpl_path_admin.'/'.$tmpl_name;
                $admin_tmpl_names[] = $tmpl_name;
            }
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
$query = $db->getQuery(true);

$query->select('custom_data')
      ->from('#__extensions')
      ->where('element = '.$db->quote('com_projectfork'))
      ->where('type = '.$db->quote('component'));

$db->setQuery($query->__toString());
$custom_data = $db->loadResult();
$custom_data = ($custom_data == '') ? array() : json_decode($custom_data, true);


// Check the data keys
if(!isset($custom_data['uninstall'])) {
    $custom_data['uninstall'] = array();
}

if(!isset($custom_data['uninstall']['site_templates'])) {
    $custom_data['uninstall']['site_templates'] = array();
}

if(!isset($custom_data['uninstall']['admin_templates'])) {
    $custom_data['uninstall']['admin_templates'] = array();
}


// Add the data
foreach($site_tmpl_names AS $tmpl_name)
{
    $custom_data['uninstall']['site_templates'][] = $tmpl_name;
}

foreach($admin_tmpl_names AS $tmpl_name)
{
    $custom_data['uninstall']['admin_templates'][] = $tmpl_name;
}


// Update the field
$query = $db->getQuery(true);

$query->update('#__extensions')
      ->set('custom_data = '.$db->quote(json_encode($custom_data)))
      ->where('element = '.$db->quote('com_projectfork'))
      ->where('type = '.$db->quote('component'));

$db->setQuery($query->__toString());
$db->query();
