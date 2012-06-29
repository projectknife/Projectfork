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


require_once(JPATH_ADMINISTRATOR.'/components/com_users/models/users.php');


/**
 * This models supports retrieving lists of users.
 * Extends on the backend version of com_users
 *
 */
class ProjectforkModelUsers extends UsersModelUsers
{
    /**
	 * Method to auto-populate the model state.
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
	    parent::populateState($ordering, $direction);

        JModel::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_projectfork/models');

        $app    = JFactory::getApplication();
        $user   = JFactory::getUser();
        $model  = JModel::getInstance('Project', 'ProjectforkModel');
        $groups = array();


        // Filter - Project
        $pid = (int) $this->getUserStateFromRequest('com_projectfork.project.active.id', 'filter_project', '');
        $this->setState('filter.project', $pid);
        ProjectforkHelper::setActiveProject($pid);


        // Override group filter by active project
        if($pid) {
            $tmp_groups = $model->getUserGroups($pid);

            // Get group ids
            if(is_array($tmp_groups)) {
                foreach($tmp_groups AS $group)
                {
                    $groups[] = (int) $group->value;
                }
            }
        }
        else {
            // No active project. Filter by all accessible projects
            if(!$user->authorise('core.admin')) {
                $umodel   = JModel::getInstance('User', 'ProjectforkModel');
                $projects = $umodel->getProjects();

                foreach($projects AS $project)
                {
                    $tmp_groups = $model->getUserGroups($project);

                    // Get group ids
                    if(is_array($tmp_groups)) {
                        foreach($tmp_groups AS $group)
                        {
                            $groups[] = (int) $group->value;
                        }
                    }
                }
            }
        }

        if(count($groups)) {
            $this->setState('filter.groups', $groups);
        }
        else {
            if(!$user->authorise('core.admin')) {
                $this->setState('filter.groups', array('1'));
            }
        }
    }
}
