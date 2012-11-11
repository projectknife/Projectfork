<?php
/**
* @package      Projectfork Dashboard Buttons
*
* @author       Tobias Kuhn (eaxs)
* @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();


if (!defined('PF_LIBRARY')) {
    jimport('projectfork.library');
}


class mod_pf_dash_buttonsInstallerScript
{
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

            // Get the XML manifest data
            $manifest = $adapter->get('manifest');

            // Set the module params
            PFInstallerHelper::setModuleParams($manifest);

            // Register the extension to uninstall with com_projectfork
            if (JFactory::getApplication()->get('pkg_projectfork_install') !== true) {
                PFInstallerHelper::registerCustomUninstall($manifest->name, 'module');
            }
        }

        return true;
    }
}
