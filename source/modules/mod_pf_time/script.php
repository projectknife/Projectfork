<?php
/**
* @package      Projectfork Timesheet Module
*
* @author       ANGEK DESIGN (Kon Angelopoulos)
* @copyright    Copyright (C) 2013 ANGEK DESIGN. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();


class mod_pf_timeInstallerScript
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
        if (strtolower($route) == 'install' || strtolower($route) == 'update') {
            if (!defined('PF_LIBRARY')) {
                jimport('projectfork.library');
            }

            $name = htmlspecialchars($adapter->get('manifest')->name, ENT_QUOTES, 'UTF-8');

            // Check if the library is installed
            if (!defined('PF_LIBRARY')) {
                JError::raiseWarning(1, JText::_('This extension (' . $name . ') requires the Projectfork Library to be installed!'));
                return false;
            }

            // Check if the projectfork component is installed
            if (!PFApplicationHelper::exists('com_projectfork')) {
                JError::raiseWarning(1, JText::_('This extension (' . $name . ') requires the Projectfork Component to be installed!'));
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
            // Get the XML manifest data
            $manifest = $adapter->get('manifest');

            // Set the module params
            PFInstallerHelper::setModuleParams($manifest);
        }

        return true;
    }
}
