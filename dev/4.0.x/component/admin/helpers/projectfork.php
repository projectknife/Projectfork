<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see license.txt
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

// No direct access
defined('_JEXEC') or die;


class ProjectforkHelper
{
	public static $extension = 'com_projectfork';


	/**
	 * Configure the Linkbar.
	 *
	 * @param	string	$vName	The name of the active view.
	 * @return	void
	 */
	public static function addSubmenu($vName)
	{
        JSubMenuHelper::addEntry(
			JText::_('COM_PROJECTFORK_SUBMENU_DASHBOARD'),
			'index.php?option=com_projectfork&view=dashboard',
			($vName == 'dashboard')
		);
        JSubMenuHelper::addEntry(
        JText::_('COM_PROJECTFORK_SUBMENU_CATEGORIES'),
        'index.php?option=com_projectfork&view=categories',
        ($vName == 'categories')
        );
		JSubMenuHelper::addEntry(
			JText::_('COM_PROJECTFORK_SUBMENU_PROJECTS'),
			'index.php?option=com_projectfork&view=projects',
			($vName == 'projects')
        );
        JSubMenuHelper::addEntry(
			JText::_('COM_PROJECTFORK_SUBMENU_MILESTONES'),
			'index.php?option=com_projectfork&view=milestones',
			($vName == 'milestones')
        );
        JSubMenuHelper::addEntry(
			JText::_('COM_PROJECTFORK_SUBMENU_TASKLISTS'),
			'index.php?option=com_projectfork&view=tasklists',
			($vName == 'tasklists')
        );
        JSubMenuHelper::addEntry(
			JText::_('COM_PROJECTFORK_SUBMENU_TASKS'),
			'index.php?option=com_projectfork&view=tasks',
			($vName == 'tasks')
        );
	}


    /**
	 * Returns all available actions
	 *
	 * @return	object
	 */
    public static function getActions($asset_name = NULL, $asset_id = 0)
	{
		$user	= JFactory::getUser();
		$result	= new JObject;
		$asset  = 'com_projectfork';

        if($asset_name) $asset .= '.'.$asset_name;
        if($asset_id)   $asset .= '.'.$asset_id;

		$actions = array('create', 'edit', 'edit.own', 'edit.state', 'delete');
        $assets  = array('core', 'project', 'milestone', 'tasklist', 'task');

        $result->set('core.admin',  $user->authorise('core.admin',  $asset));
        $result->set('core.manage', $user->authorise('core.manage', $asset));

        foreach($assets AS $name)
        {
            foreach($actions AS $action)
            {
                $result->set($name.'.'.$action, $user->authorise($name.'.'.$action, $asset));
            }
        }

		return $result;
	}


    /**
	 * Returns all groups with the give access level
	 *
     * @param    int     $access      The access level id
     * @param    bool    $children    Include child groups in the result?
	 * @return	 array                The groups
	 **/
    public function getGroupsByAccess($access, $children = true)
    {
        // Setup vars
        $db     = JFactory::getDbo();
        $query  = $db->getQuery(true);
        $groups = array();

        // Get the rule of the access level
        if($access != 1) {
            $query->select('a.rules');
            $query->from('#__viewlevels AS a');
            $query->where('a.id = '.(int) $access);

            $db->setQuery((string) $query);
    		$rules = json_decode($db->loadResult());
        }
        else {
            $query->select('id')
                  ->from('#__usergroups');

            $db->setQuery((string) $query);
            $rules = $db->loadResultArray();
            $children = false;
        }


        if(!count($rules)) return $groups;


        // Get the associated groups data
        //$rules = implode(',', $rules);

        if(!$children) {
            $query = $db->getQuery(true);
            $rules = implode(', ', $rules);

            $query->select('a.id AS value, a.title AS text, COUNT(DISTINCT b.id) AS level, a.parent_id, a.lft, a.rgt');
		    $query->from('#__usergroups AS a');
            $query->where('a.id IN('.$rules.')');
            $query->leftJoin($query->qn('#__usergroups').' AS b ON a.lft > b.lft AND a.rgt < b.rgt');
            $query->group('a.id');
		    $query->order('a.lft ASC');

            $db->setQuery((string) $query);
            $groups = $db->loadObjectList();
        }
        else {
            foreach($rules AS $gid)
            {
                $gid = (int) $gid;


                // Load the group data
                $query = $db->getQuery(true);

                $query->select('a.id AS value, a.title AS text, COUNT(DISTINCT b.id) AS level, a.parent_id, a.lft, a.rgt');
    		    $query->from('#__usergroups AS a');
                $query->where('a.id = '.$gid);
                $query->leftJoin($query->qn('#__usergroups').' AS b ON a.lft > b.lft AND a.rgt < b.rgt');
                $query->group('a.id');
    		    $query->order('a.lft ASC');

                $db->setQuery((string) $query);
                $group = $db->loadObject();


                // Load child groups
                if(is_object($group)) {
                    $groups[] = $group;

                    $query = $db->getQuery(true);

                    $query->select('a.id AS value, a.title AS text, COUNT(DISTINCT b.id) AS level, a.parent_id, a.lft, a.rgt');
        		    $query->from('#__usergroups AS a');
                    $query->leftJoin($query->qn('#__usergroups').' AS b ON a.lft > b.lft AND a.rgt < b.rgt');
                    $query->where('a.lft > '.$group->lft.' AND a.rgt < '.$group->rgt);
                    $query->group('a.id');
        		    $query->order('a.lft ASC');

                    $db->setQuery((string) $query);
                    $subgroups = (array) $db->loadObjectList();



                    foreach($subgroups AS $subgroup)
                    {
                        $groups[] = $subgroup;
                    }
                }
            }
        }

        return $groups;
    }


    /**
	 * Returns all child access levels of a given access levels
     * The children are defined by the group hierarchy
	 *
     * @param    int     $access      The access level id
	 * @return	 array                The access levels
	 **/
    public function getChildrenOfAccess($access)
    {
        // Setup vars
        static $accesslist = NULL;

        $groups   = ProjectforkHelper::getGroupsByAccess($access);
        $children = array();

        if(!count($groups)) return $children;


        // Load all access levels if not yet set
        if(is_null($accesslist)) {
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('a.id AS value, a.title AS text, a.ordering, a.rules');
            $query->from('#__viewlevels AS a');
            $query->order('a.title ASC');

            $db->setQuery((string) $query);
            $accesslist = (array) $db->loadObjectList();
        }


        // Go through each access level
        foreach($groups AS $group)
        {
            // And each access level
            foreach($accesslist AS $item)
            {
                $rules = json_decode($item->rules);
                $key   = $item->value;

                if($key == $access) continue;

                // Check if the group is listed in the access rules and add to children if so
                if(in_array($group->value, $rules) && !array_key_exists($key, $children)) {
                    $children[$key] = $item;
                }
            }
        }

        return $children;
    }


    public function getGroupPath($id)
    {
        static $groups;
        static $path;

        // Preload all groups
		if (empty($groups)) {
			$db = JFactory::getDbo();

			$query = $db->getQuery(true)
				   ->select('parent.id, parent.lft, parent.rgt')
				   ->from('#__usergroups AS parent')
				   ->order('parent.lft');

			$db->setQuery($query);
			$groups = (array) $db->loadObjectList('id');
		}

        if(empty($path)) $path = array();


		// Make sure groupId is valid
		if(!array_key_exists($id, $groups)) return array();


		// Get parent groups and leaf group
		if (!isset($path[$id]))
		{
			$path[$id] = array();

			foreach ($groups as $group)
			{
				if ($group->lft <= $groups[$id]->lft && $group->rgt >= $groups[$id]->rgt)
				{
					$path[$id][] = $group->id;
				}
			}
		}

		return $path[$id];
    }


    /**
	 * Sets the currently active project for the user.
     * The active project serves as a global data filter.
	 *
     * @param    int        $id      The project id
	 * @return	 boolean             True on success, False on error
	 **/
    public function setActiveProject($id = 0)
    {
        $app = JFactory::getApplication();

        if($app->isSite()) {
            JModel::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_projectfork'.DS.'models');
        }

        $model = JModel::getInstance('Project', 'ProjectforkModel');
        $data  = array('id' => (int) $id);

        return $model->setActive($data);
    }


    /**
	 * Returns the currently active project ID of the user.
	 *
     * @param    int     $alt      Alternative value of no project is set
	 * @return	 int               The project id
	 **/
    public function getActiveProjectId($alt = 0)
    {
        $app = JFactory::getApplication();

        return (int) $app->getUserState('com_projectfork.project.active.id', $alt);
    }


    /**
	 * Returns the currently active project title of the user.
	 *
     * @param    string     $alt      Alternative value of no project is set
	 * @return	 string               The project title
	 **/
    public function getActiveProjectTitle($alt = '')
    {
        $app = JFactory::getApplication();

        if($alt) $alt = JText::_($alt);

        return $app->getUserState('com_projectfork.project.active.title', $alt);
    }
}
