<?php
/**
 * @package      Projectfork
 * @subpackage   Users
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


class com_pfusersInstallerScript
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
                $item['title'] = 'Users';
                $item['alias'] = 'users';
                $item['link']  = 'index.php?option=' . $element . '&view=users';
                $item['component_id'] = $eid;

                PFInstallerHelper::addMenuItem($item);
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
        // Skip this step if the user is removing the entire projectfork package
        if ($this->isRemovingAll()) {
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


    /**
     * Method to find out if the user is removing com_projectfork
     * or pkg_projectfork
     *
     * @return    boolean
     */
    protected function isRemovingAll()
    {
        $cid = JFactory::getApplication()->input->get('cid', array(), 'array');

		JArrayHelper::toInteger($cid, array());

        if (count($cid) == 0) {
            $extensions = array();
        }
        else {
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('element')
                  ->from('#__extensions')
                  ->where('extension_id IN(' . implode(', ', $cid) . ')');

            $db->setQuery($query);
            $extensions = (array) $db->loadColumn();
        }

        if (in_array('pkg_projectfork', $extensions) || in_array('com_projectfork', $extensions)) {
            return true;
        }

        return false;
    }
}
