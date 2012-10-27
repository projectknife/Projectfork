<?php
/**
 * @package      Projectfork
 * @subpackage   Forum
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


class com_pfforumInstallerScript
{
    /**
     * Called before any type of action
     *
     * @param     string              $route      Which action is happening (install|uninstall|discover_install)
     * @param     jadapterinstance    $adapter    The object responsible for running this script
     *
     * @return    boolean                         True on success
     */
    public function preflight($route, JAdapterInstance $adapter)
    {
        if (strtolower($route) == 'install') {
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);

            // Check if Projectfork is installed
            $query->select('extension_id')
                  ->from('#__extensions')
                  ->where('element = ' . $db->quote('com_projectfork'))
                  ->where('type = ' . $db->quote('component'));

            $db->setQuery($query);
            $installed = (int) $db->loadResult();

            if (!$installed) {
                JLog::add('This extension requires Projectfork to be installed!', JLog::WARNING, 'jerror');
                return false;
            }

            // Check if the library is installed
            $query->clear();
            $query->select('extension_id')
                  ->from('#__extensions')
                  ->where('element = ' . $db->quote('projectfork'))
                  ->where('type = ' . $db->quote('library'));

            $db->setQuery($query);
            $installed = (int) $db->loadResult();

            if (!$installed) {
                JLog::add('This extension requires the Projectfork library to be installed!', JLog::WARNING, 'jerror');
                return false;
            }
        }

        return true;
    }


    /**
     * Called after any type of action
     *
     * @param     string              $route      Which action is happening (install|uninstall|discover_install)
     * @param     jadapterinstance    $adapter    The object responsible for running this script
     *
     * @return    boolean                         True on success
     */
    public function postflight($route, JAdapterInstance $adapter)
    {
        if (strtolower($route) == 'install') {
            $element   = $adapter->get('element');
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
                        $asset_bak->store();
                    }
                }
            }

            // Next, make the admin menu item a child item of com_projectfork
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);

            // Find the menu item id of com_projectfork
            $query->select('id')
                  ->from('#__menu')
                  ->where('menutype = ' . $db->quote('main'))
                  ->where('title = ' . $db->quote('com_projectfork'))
                  ->where('client_id = 1');

            $db->setQuery($query);
            $parent = (int) $db->loadResult();

            if ($parent > 1) {
                // Find the menu item id of this component
                $query->clear();
                $query->select('id')
                      ->from('#__menu')
                      ->where('menutype = ' . $db->quote('main'))
                      ->where('title = ' . $db->quote($element))
                      ->where('client_id = 1');

                $db->setQuery($query);
                $mid = (int) $db->loadResult();

                if ($mid) {
                    $menu = JTable::getInstance('menu');

                    // Set the new parent item
                    if ($menu->load($mid)) {
                        $menu->setLocation($parent, 'last-child');
                        $menu->store();
                    }
                }
            }
        }

        return true;
    }


    /**
     * Called on uninstallation
     *
     * @param    jadapterinstance    $adapter    The object responsible for running this script
     */
    public function uninstall(JAdapterInstance $adapter)
    {
        if (JFactory::getApplication()->getUserState('com_projectfork.uninstall') === true) {
            // Skip this step if the user is removing projectfork itself
            return true;
        }

        $element = $adapter->get('element');
        $asset   = JTable::getInstance('Asset');

        // We want to preserve the asset container and its children for any replacement component
        if ($asset->loadByName($element)) {
            $asset->name = $asset->name . '_bak';
            $asset->store();
        }

        return true;
    }
}
