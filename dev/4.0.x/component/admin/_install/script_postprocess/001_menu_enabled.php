<?php
/**
* @package   Projectfork
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


// Get the db object
$db = JFactory::getDbo();

// Get new component id.
$com = JComponentHelper::getComponent('com_projectfork');
$com_id	= (is_object($com) && isset($com->id)) ? $com->id : 0;


if($com_id) {
    // Update menu items with the correct component id
    $query = $db->getQuery(true);
    $query->update('#__menu')
          ->set('component_id = '.$db->quote($com_id))
          ->set('parent_id = '.$db->quote('1'))
          ->set('level = '.$db->quote('1'))
          ->where('menutype = '.$db->quote('projectfork'))
          ->where('component_id = '.$db->quote('0'));

    $db->setQuery($query->__toString());
    $db->query();
}
else {
    // Something went wrong. Delete the menu items and the menu

    // Get the Menu model
    JLoader::register('MenusModelMenu', JPATH_ADMINISTRATOR . '/components/com_menus/models/menu.php');

    if (!class_exists('JModel')) {
        $menu_model = new MenusModelMenu();
    }
    else {
        $menu_model = JModel::getInstance('Menu', 'MenusModel', array('ignore_request' => true));
    }

    // Find the menu id
    $query = $db->getQuery(true);
    $query->select('id')
          ->from('#__menu_types')
          ->where('menutype = '.$db->quote('projectfork'));

    $db->setQuery($query->__toString());
    $menu_id = (int) $db->loadResult();


    if(!$menu_id) return false;

    $data = array($menu_id);

    return $menu_model->delete($data);
}
