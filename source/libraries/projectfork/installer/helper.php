<?php
/**
 * @package      Projectfork.Library
 * @subpackage   Menu
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


/**
 * Projectfork Installer Helper Class
 * Supports the install procedure of Projectfork related extensions
 *
 */
abstract class PFInstallerHelper
{
    /**
     * Method to restore the assets from a previous component install
     *
     * @param     string     $element    The name of the component to restore
     *
     * @return    boolean                True on success, False on error
     */
    static function restoreAssets($element)
    {
        $asset_bak = JTable::getInstance('Asset');
        $asset_new = JTable::getInstance('Asset');

        // Check if we have a backup asset container from a previous install
        if ($asset_bak->loadByName($element . '_bak')) {
            // Yes, then try to load the current (new) one
            if ($asset_new->loadByName($element)) {
                // Delete the current asset
                if ($asset_new->delete()) {
                    // And make the old one the current again
                    $asset_bak->name = $element;

                    if (!$asset_bak->store()) {
                        return false;
                    }
                }
            }
        }

        return true;
    }


    /**
     * Method to set the admin component menu item as child of com_projectfork
     *
     * @param     string     $element    The component name
     *
     * @return    boolean                True on success, False on error
     */
    static function setComponentMenuItem($element)
    {
        static $pf_menu_item = null;

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Find the projectfork admin component menu item
        if (is_null($pf_menu_item)) {
            $query->select('id')
                  ->from('#__menu')
                  ->where('menutype = ' . $db->quote('main'))
                  ->where('title = ' . $db->quote('com_projectfork'))
                  ->where('client_id = 1');

            $db->setQuery($query);
            $pf_menu_item = (int) $db->loadResult();
        }

        if (!$pf_menu_item) {
            return false;
        }

        // Find the menu item id of this component
        $query->clear();
        $query->select('id')
              ->from('#__menu')
              ->where('menutype = ' . $db->quote('main'))
              ->where('title = ' . $db->quote($element))
              ->where('client_id = 1');

        $db->setQuery($query);
        $menu_item = (int) $db->loadResult();

        if (!$menu_item) {
            return false;
        }

        $menu = JTable::getInstance('menu');

        // Set the new parent item
        if ($menu->load($menu_item)) {
            $menu->setLocation($pf_menu_item, 'last-child');

            if (!$menu->store()) {
                return false;
            }
        }
        else {
            return false;
        }

        return true;
    }


    /**
     * Method to add a menu item in the Projectfork site navigation menu
     *
     * @param     array      $data    Menu item properties
     *
     * @return    boolean             True on success, False on error
     */
    public static function addMenuItem($data)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Add any missing default properties
        if (!isset($data['menutype']))     $data['menutype']     = 'projectfork';
        if (!isset($data['parent_id']))    $data['parent_id']    = '1';
        if (!isset($data['level']))        $data['level']        = '1';
        if (!isset($data['published']))    $data['published']    = '1';
        if (!isset($data['type']))         $data['type']         = 'component';
        if (!isset($data['component_id'])) $data['component_id'] = 0;
        if (!isset($data['language']))     $data['language']     = '*';
        if (!isset($data['access']))       $data['access']       = '1';
        if (!isset($data['params']))       $data['params']       = '{}';
        if (!isset($data['ordering']))     $data['ordering']     = 0;

        $data['id'] = null;

        // Save the menu item
        $row = JTable::getInstance('menu');

        $row->setLocation(1, 'last-child');

        if (!$row->bind($data)) {
            return false;
        }

        if (!$row->check()) {
            return false;
        }

        if (!$row->store()) {
            return false;
        }

        $query->clear();
        $query->update('#__menu')
              ->set('parent_id = 1')
              ->set('level = 1')
              ->where('id = ' . (int) $row->id);

        $db->setQuery($query);
        $db->execute();

        $row->parent_id = 1;
        $row->level = 1;

        $row->setLocation(1, 'last-child');

        if (!$row->rebuildPath($row->id)) {
            return false;
        }

        return true;
    }


    /**
     * Method to register an extension to be uninstalled with com_projectfork
     *
     * @param     string     $element    The name of the extension
     * @param     string     $type       The extension type
     *
     * @return    boolean                True on success, False on error
     */
    public static function registerCustomUninstall($element, $type = 'component')
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        switch (strtolower($type))
        {
            case 'plugin':
                $key = 'plugins';
                break;

            case 'module':
                $key = 'modules';
                break;

            case 'template':
                $key = 'templates';
                break;

            case 'library':
                $key = 'libraries';
                break;

            case 'component':
            default:
                $key = 'components';
                break;
        }

        // Get projectfork custom data
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

        if (!isset($custom_data['uninstall'][$key])) {
            $custom_data['uninstall'][$key] = array();
        }

        if (in_array($element, $custom_data['uninstall'][$key])) {
            return true;
        }

        // Register
        $custom_data['uninstall'][$key][] = $element;

        // Update the field
        $query->clear();
        $query->update('#__extensions')
              ->set('custom_data = ' . $db->quote(json_encode($custom_data)))
              ->where('element = ' . $db->quote('com_projectfork'))
              ->where('type = ' . $db->quote('component'));

        $db->setQuery((string) $query);

        if (!$db->execute()) {
            return false;
        }

        return true;
    }
}
