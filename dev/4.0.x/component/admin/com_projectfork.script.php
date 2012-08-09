<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


class Com_ProjectforkInstallerScript
{
    /**
     * Constructor
     *
     * @param    jadapterinstance    $adapter    The object responsible for running this script
     */
    public function __constructor(JAdapterInstance $adapter)
    {

    }


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
            $script = JPATH_ADMINISTRATOR . '/components/com_projectfork/_install/script.postprocess.php';

            if (file_exists($script)) {
                require_once($script);
                return true;
            }

            return false;
        }

        return true;
    }


    /**
     * Called on installation
     *
     * @param     jadapterinstance    $adapter    The object responsible for running this script
     *
     * @return    boolean                         True on success
     */
    public function install(JAdapterInstance $adapter)
    {
        $script = JPATH_ADMINISTRATOR . '/components/com_projectfork/_install/script.install.php';

        if (file_exists($script)) {
            require_once($script);
            return true;
        }

        return false;
    }


    /**
     * Called on update
     *
     * @param     jadapterinstance    $adapter    The object responsible for running this script
     *
     * @return    boolean                         True on success
     */
    public function update(JAdapterInstance $adapter)
    {
        return true;
    }


    /**
     * Called on uninstallation
     *
     * @param    jadapterinstance    $adapter    The object responsible for running this script
     */
    public function uninstall(JAdapterInstance $adapter)
    {
        $script = JPATH_ADMINISTRATOR . '/components/com_projectfork/_uninstall/script.uninstall.php';

        if (file_exists($script)) {
            require_once($script);
            return true;
        }

        return false;
    }
}
