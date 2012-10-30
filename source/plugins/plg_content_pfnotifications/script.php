<?php
/**
 * @package      Projectfork Notifications
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


class plgContentPfnotificationsInstallerScript
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
            $db    = JFactory::getDBO();
            $query = $db->getQuery(true);

            // Get the XML manifest data
            $manifest = $adapter->get('manifest');

            // Get plugin published state
            $name = $manifest->name;
            $pub  = (isset($manifest->published) ? (int) $manifest->published : 0);

            if (!$pub) return true;

            // Get the plugin id
            $query->select('extension_id')
                  ->from('#__extensions')
                  ->where('name = ' . $db->quote($name))
                  ->where('type = ' . $db->quote('plugin'));

            $db->setQuery((string) $query);
            $id = (int) $db->loadResult();

            if (!$id) return true;

            // Update params
            $query->clear();
            $query->update('#__extensions')
                  ->set('enabled = ' . $db->quote($pub))
                  ->where('extension_id = ' . $db->quote($id));

            $db->setQuery((string) $query);
            $db->query();
        }

        return true;
    }
}
