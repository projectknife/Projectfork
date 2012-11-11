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


if (!defined('PF_LIBRARY')) {
    jimport('projectfork.library');
}


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
            // Check if the library is installed
            if (!defined('PF_LIBRARY')) {
                JLog::add('This extension requires the Projectfork Library to be installed!', JLog::WARNING, 'jerror');
                return false;
            }

            // Check if the projectfork component is installed
            if (!PFApplicationHelper::exists('com_projectfork')) {
                JLog::add('This extension requires the Projectfork Component to be installed!', JLog::WARNING, 'jerror');
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
            $element = $adapter->get('element');

            // Restore assets from backup
            PFInstallerHelper::restoreAssets($element);

            // Make the admin component menu item a child of com_projectfork
            PFInstallerHelper::setComponentMenuItem($element);

            // Create a menu item in the projectfork site menu
            $com = JComponentHelper::getComponent($element);
            $eid = (is_object($com) && isset($com->id)) ? $com->id : 0;

            if ($eid) {
                $item = array();
                $item['title'] = 'Forum';
                $item['alias'] = 'forum';
                $item['link']  = 'index.php?option=' . $element . '&view=topics';
                $item['component_id'] = $eid;

                PFInstallerHelper::addMenuItem();
            }


            // Register the extension to uninstall with com_projectfork
            if (JFactory::getApplication()->get('pkg_projectfork_install') !== true) {
                PFInstallerHelper::registerCustomUninstall($element);
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
        if (JFactory::getApplication()->get('pkg_projectfork_uninstall') === true) {
            // Skip this step if the user is removing the entire projectfork package
            return true;
        }

        $element = $adapter->get('element');
        $asset   = JTable::getInstance('Asset');

        // Backup any assets for another component that might take over
        if ($asset->loadByName($element)) {
            $asset->name = $asset->name . '_bak';
            $asset->store();
        }

        return true;
    }
}
