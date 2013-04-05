<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


class com_projectforkInstallerScript
{
    /**
     * Previous version number before updating
     *
     * @var    string    
     */
    protected $prev_version;


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
            if (!defined('PF_LIBRARY')) {
                jimport('projectfork.library');
            }

            // Check if the library is installed
            if (!defined('PF_LIBRARY')) {
                JLog::add('This extension requires the Projectfork Library to be installed!', JLog::WARNING, 'jerror');
                return false;
            }
        }

        if (strtolower($route) == 'update') {
            $this->setSchemaVersion();

            $this->prev_version = $this->getExtensionVersion();
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
            // Call custom install script
            $script = JPATH_ADMINISTRATOR . '/components/com_projectfork/_install/script.postprocess.php';

            if (file_exists($script)) {
                require_once $script;
                return true;
            }

            return false;
        }

        if (strtolower($route) == 'update') {
            // Call custom update script
            $script = JPATH_ADMINISTRATOR . '/components/com_projectfork/_update/script.postprocess.php';
            $prev_version = $this->prev_version;

            if (file_exists($script)) {
                require_once $script;
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


    /**
     * Method to get the current extension version
     *
     * @return    string    The version number
     */
    protected function getExtensionVersion()
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('manifest_cache')
              ->from('#__extensions')
              ->where('element = ' . $db->quote('com_projectfork'));

        $db->setQuery($query);
        $manifest = $db->loadResult();

        if (empty($manifest)) return '4.0.0';

        $object = json_decode($manifest);

        if (!$object) return '4.0.0';

        return (isset($object->version) ? $object->version : '4.0.0');
    }


    /**
     * Method to insert version id into the schemas table if not found
     *
     * @param     string     $current    The current version
     *
     * @return    boolean                True on success
     */
    protected function setSchemaVersion($current = '4.0.0')
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('extension_id')
              ->from('#__extensions')
              ->where('element = ' . $db->quote('com_projectfork'));

        $db->setQuery($query);
        $eid = (int) $db->loadResult();

        if (!$eid) return false;

        $query->clear()
              ->select('version_id')
              ->from('#__schemas')
              ->where('extension_id = ' . $eid);

        $db->setQuery($query);
        $version = $db->loadResult();

        if (empty($version)) {
            $query->clear()
                  ->insert('#__schemas')
                  ->columns(array('extension_id', 'version_id'))
                  ->values($eid . ', ' . $db->quote($current));

            $db->setQuery($query);
            $db->execute();
        }

        return true;
    }
}
