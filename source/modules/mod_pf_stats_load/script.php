<?php
/**
* @package      Projectfork Project Workload Statistics
*
* @author       Tobias Kuhn (eaxs)
* @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();


class Mod_Pf_Stats_LoadInstallerScript
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
            $db    = JFactory::getDBO();
            $query = $db->getQuery(true);

            // Get the XML manifest data
            $manifest = $adapter->get('manifest');

            // Get module name, position and published state
            $name  = $manifest->name;
            $pos   = (isset($manifest->position) ? $manifest->position : '');
            $pub   = (isset($manifest->published) ? (int) $manifest->published : 0);
            $title = (isset($manifest->show_title) ? (int) $manifest->show_title : 1);


            // Get the module id
            $query->select('id')
                  ->from('#__modules')
                  ->where('module = ' . $db->quote($name));

            $db->setQuery((string) $query);
            $id = (int) $db->loadResult();

            if (!$id) return true;


            // Update params
            $query = $db->getQuery(true);

            $query->update('#__modules');
            if ($pos) $query->set('position = ' . $db->quote($pos));
            if ($pub) $query->set('published = ' . $db->quote($pub));
            $query->set('showtitle = ' . $db->quote($title));
            $query->where('module = ' . $db->quote($name));

            $db->setQuery((string) $query);
            $db->query();


            // Show the module on all pages if a position is given
            if ($pos) {
                $query = $db->getQuery(true);

                $query->insert('#__modules_menu')
                      ->columns(array($db->quoteName('moduleid'), $db->quoteName('menuid')))
                      ->values((int)$id . ', 0');

                $db->setQuery((string) $query);
                $db->query();
            }
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
        return true;
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
        return true;
    }
}
