<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


class pkg_projectforkInstallerScript
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
        // Joomla version check
        if (!version_compare(JVERSION, '2.5.5', 'ge')) {
            $adapter->get('parent')->abort('Unsupported version! Projectfork requires Joomla 2.5.5 or newer.');
            return false;
        }

        // Memory Check
        if (file_exists(dirname(__FILE__) . '/memcheck.php')) {
            require_once dirname(__FILE__) . '/memcheck.php';

            $mem   = new pkg_projectforkMemory();
            $check = $mem->check();

            if ($check !== true) {
                $msg = 'Not enough memory available: Missing ' . $check . 'k. '
                     . 'You can delete the "memcheck.php" file from this install package to disable the memory check and try again.';

                $adapter->get('parent')->abort($msg);
                return false;
            }
        }

        if (JDEBUG) {
            JProfiler::getInstance('Application')->mark('before' . ucfirst($route) . 'Projectfork');
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
        if (JDEBUG) {
            JProfiler::getInstance('Application')->mark('after' . ucfirst($route) . 'Projectfork');

            $buffer = JProfiler::getInstance('Application')->getBuffer();
            $app    = JFactory::getApplication();

            foreach ($buffer as $mark)
    		{
    		    $app->enqueueMessage($mark, 'debug');
    		}
        }

        return true;
    }
}
