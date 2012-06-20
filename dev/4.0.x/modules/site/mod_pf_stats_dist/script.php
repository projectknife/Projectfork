<?php
/**
* @package   Projectfork Task Distribution Statistics
* @copyright Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.php
*
* This file is part of Projectfork.
*
* Projectfork is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*
* Projectfork is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Projectfork. If not, see <http://www.gnu.org/licenses/gpl.html>.
**/

defined( '_JEXEC' ) or die( 'Restricted access' );


class Mod_Pf_Stats_DistInstallerScript
{
	/**
	 * Constructor
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 */
	public function __constructor(JAdapterInstance $adapter)
    {

    }


	/**
	 * Called before any type of action
	 *
	 * @param   string  $route  Which action is happening (install|uninstall|discover_install)
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function preflight($route, JAdapterInstance $adapter)
    {
        return true;
    }


	/**
	 * Called after any type of action
	 *
	 * @param   string  $route  Which action is happening (install|uninstall|discover_install)
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function postflight($route, JAdapterInstance $adapter)
    {
        if(strtolower($route) == 'install') {
            $db    = JFactory::getDBO();
            $query = $db->getQuery(true);

            // Get the XML manifest data
            $manifest = $adapter->get('manifest');

            // Get module name, position and published state
            $name  = $manifest->name;
            $pos   = (isset($manifest->position) ? $manifest->position : '');
            $pub   = (isset($manifest->published) ? (int) $manifest->published : 0);


            // Get the module id
            $query->select('id')
                  ->from('#__modules')
                  ->where('module = '.$db->quote($name));

            $db->setQuery($query->__toString());
            $id = (int) $db->loadResult();

            if(!$id) return true;


            // Update module position and published state
            if($pub || $state) {
                $query = $db->getQuery(true);

                $query->update('#__modules');
                if($pos) $query->set('position = '.$db->quote($pos));
                if($pub) $query->set('published = '.$db->quote($pub));
                $query->where('module = '.$db->quote($name));

                $db->setQuery($query->__toString());
                $db->query();
            }


            // Show the module on all pages if a position is given
            if($pos) {
                $query = $db->getQuery(true);

                $query->insert('#__modules_menu')
				      ->columns(array($db->quoteName('moduleid'), $db->quoteName('menuid')))
				      ->values((int)$id . ', 0');

                $db->setQuery($query->__toString());
                $db->query();
            }
        }

        return true;
    }


	/**
	 * Called on installation
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function install(JAdapterInstance $adapter)
    {
        return true;
    }


	/**
	 * Called on update
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function update(JAdapterInstance $adapter)
    {
        return true;
    }


	/**
	 * Called on uninstallation
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 */
	public function uninstall(JAdapterInstance $adapter)
    {
        return true;
    }
}
