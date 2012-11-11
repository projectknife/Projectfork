<?php
/**
 * @package      Projectfork
 * @subpackage   Time Tracking
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


if (!defined('PF_LIBRARY')) {
    jimport('projectfork.library');
}


class com_pftimeInstallerScript
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

            // Register the extension to uninstall with com_projectfork
            if (JFactory::getApplication()->getUserState('pkg_projectfork.install') !== true) {
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
        if (JFactory::getApplication()->getUserState('pkg_projectfork.uninstall') === true) {
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
