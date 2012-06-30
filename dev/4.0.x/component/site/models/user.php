<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
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


require_once(JPATH_ADMINISTRATOR.'/components/com_users/models/user.php');


/**
 * Projectfork User Model
 * Extends on the backend version of com_users
 *
 */
class ProjectforkModelUser extends UsersModelUser
{
    /**
     * Method to find all projects a user has access to
     *
     * @param     $pk      The user id
     * @return    array    The project IDs
     */
    public function getProjects($pk = NULL)
    {
        $user  = JFactory::getUser($pk);
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $groups	= implode(',', $user->getAuthorisedViewLevels());

        $query->select('id')
              ->from('#__pf_projects')
              ->where('access IN('.$groups.')');

        if ((!$user->authorise('core.edit.state', 'com_projectfork') && !$user->authorise('project.edit.state', 'com_projectfork')) &&
            (!$user->authorise('core.edit', 'com_projectfork') && !$user->authorise('project.edit', 'com_projectfork')))
        {
            $query->where('state = 1');
        }

        $db->setQuery($query->__toString());
        $projects = (array) $db->loadResultArray();

        return $projects;
    }
}
