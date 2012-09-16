<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


class ProjectforkHelper
{
    /**
     * The component name
     *
     * @var    string
     */
    public static $extension = 'com_projectfork';


    /**
     * Configure the Linkbar.
     *
     * @param     string    $view    The name of the active view.
     *
     * @return    void
     */
    public static function addSubmenu($view)
    {
        JSubMenuHelper::addEntry(
            JText::_('COM_PROJECTFORK_SUBMENU_DASHBOARD'),
            'index.php?option=com_projectfork&view=dashboard',
            ($view == 'dashboard')
        );

        JSubMenuHelper::addEntry(
            JText::_('COM_PROJECTFORK_SUBMENU_PROJECTS'),
            'index.php?option=com_projectfork&view=projects',
            ($view == 'projects')
        );

        if ($view == 'projects' || $view == 'categories') {
                JSubMenuHelper::addEntry(
                JText::_('COM_PROJECTFORK_SUBMENU_CATEGORIES'),
                'index.php?option=com_categories&extension=com_projectfork',
                ($view == 'categories')
            );
        }

        JSubMenuHelper::addEntry(
            JText::_('COM_PROJECTFORK_SUBMENU_MILESTONES'),
            'index.php?option=com_projectfork&view=milestones',
            ($view == 'milestones')
        );

        JSubMenuHelper::addEntry(
            JText::_('COM_PROJECTFORK_SUBMENU_TASKLISTS'),
            'index.php?option=com_projectfork&view=tasklists',
            ($view == 'tasklists')
        );

        JSubMenuHelper::addEntry(
            JText::_('COM_PROJECTFORK_SUBMENU_TASKS'),
            'index.php?option=com_projectfork&view=tasks',
            ($view == 'tasks')
        );

        JSubMenuHelper::addEntry(
            JText::_('COM_PROJECTFORK_SUBMENU_TIME_TRACKING'),
            'index.php?option=com_projectfork&view=timesheet',
            ($view == 'timesheet')
        );

        JSubMenuHelper::addEntry(
            JText::_('COM_PROJECTFORK_SUBMENU_REPO'),
            'index.php?option=com_projectfork&view=repository',
            ($view == 'repository')
        );

        JSubMenuHelper::addEntry(
            JText::_('COM_PROJECTFORK_SUBMENU_DISCUSSIONS'),
            'index.php?option=com_projectfork&view=topics',
            ($view == 'topics')
        );

        if ($view == 'replies') {
            $topic  = JRequest::getUint('filter_topic', 0);
            $append = '';

            if ($append) $append .= '&filter_topic=' . $topic;

            JSubMenuHelper::addEntry(
                JText::_('COM_PROJECTFORK_SUBMENU_REPLIES'),
                'index.php?option=com_projectfork&view=replies' . $append,
                ($view == 'replies')
            );
        }

        JSubMenuHelper::addEntry(
            JText::_('COM_PROJECTFORK_SUBMENU_COMMENTS'),
            'index.php?option=com_projectfork&view=comments',
            ($view == 'comments')
        );
    }


    public function getProjectParams($id = 0)
    {
        static $cache = array();

        $project = ($id > 0) ? (int) $id : ProjectforkHelper::getActiveProjectId();

        if (array_key_exists($project, $cache)) {
            return $cache[$project];
        }

        $params = JComponentHelper::GetParams('com_projectfork');

        // Get the project parameters if they exist
        if ($project) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('attribs')
                  ->from('#__pf_projects')
                  ->where('id = ' . $db->quote($project));

            $db->setQuery((string) $query);
            $attribs = $db->loadResult();

            if (!empty($attribs)) {
                $registry = new JRegistry();
                $registry->loadString($attribs);

                $params->merge($registry);
            }
        }

        $cache[$project] = $params;

        return $cache[$project];
    }


    /**
     * Calculates and returns all available actions for the given asset
     *
     * @deprecated                          Use ProjectforkHelperAccess::getActions() instead!
     *
     * @param     string     $asset_name    Optional asset item name
     * @param     integer    $asset_id      Optional asset id
     *
     * @return    object
     */
    public static function getActions($asset_name = NULL, $asset_id = 0)
    {
        JLoader::register('ProjectforkHelperAccess', JPATH_ADMINISTRATOR . '/components/com_projectfork/helpers/access.php');

        $actions = ProjectforkHelperAccess::getActions($asset_name, $asset_id);

        return $actions;
    }


    /**
     * Returns all groups with the given access level
     *
     * @param     integer    $access      The access level id
     * @param     boolean    $children    Include child groups in the result?
     *
     * @return    array                   The groups
     **/
    public function getGroupsByAccess($access, $children = true)
    {
        // Setup vars
        $db     = JFactory::getDbo();
        $query  = $db->getQuery(true);
        $groups = array();

        // Get the rule of the access level
        if ($access != 1) {
            $query->select('a.rules')
                  ->from('#__viewlevels AS a')
                  ->where('a.id = '.(int) $access);

            $db->setQuery((string) $query);

            $rules = (array) json_decode($db->loadResult());
        }
        else {
            $query->select('id')
                  ->from('#__usergroups');

            $db->setQuery((string) $query);

            $rules    = (array) $db->loadResultArray();
            $children = false;
        }

        if (!count($rules)) return $groups;


        // Get the associated groups data
        if (!$children) {
            $rules = implode(', ', $rules);

            $query->clear();
            $query->select('a.id AS value, a.title AS text, COUNT(DISTINCT b.id) AS level, a.parent_id, a.lft, a.rgt')
                  ->from('#__usergroups AS a')
                  ->where('a.id IN(' . $rules . ')')
                  ->leftJoin($query->qn('#__usergroups') . ' AS b ON a.lft > b.lft AND a.rgt < b.rgt')
                  ->group('a.id')
                  ->order('a.lft ASC');

            $db->setQuery((string) $query);

            $groups = (array) $db->loadObjectList();
        }
        else {
            foreach($rules AS $gid)
            {
                $gid = (int) $gid;

                // Load the group data
                $query->clear();
                $query->select('a.id AS value, a.title AS text, COUNT(DISTINCT b.id) AS level, a.parent_id, a.lft, a.rgt')
                      ->from('#__usergroups AS a')
                      ->where('a.id = ' . $gid)
                      ->leftJoin($query->qn('#__usergroups') . ' AS b ON a.lft > b.lft AND a.rgt < b.rgt')
                      ->group('a.id')
                      ->order('a.lft ASC');

                $db->setQuery((string) $query);

                $group = $db->loadObject();


                // Load child groups
                if (is_object($group)) {
                    $groups[] = $group;

                    $query->clear();
                    $query->select('a.id AS value, a.title AS text, COUNT(DISTINCT b.id) AS level, a.parent_id, a.lft, a.rgt')
                          ->from('#__usergroups AS a')
                          ->leftJoin($query->qn('#__usergroups'). ' AS b ON a.lft > b.lft AND a.rgt < b.rgt')
                          ->where('a.lft > ' . $group->lft. ' AND a.rgt < ' . $group->rgt)
                          ->group('a.id')
                          ->order('a.lft ASC');

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
     * @param     integer    $access    The access level id
     *
     * @return    array                 The access levels
     **/
    public function getChildrenOfAccess($access)
    {
        // Setup vars
        static $accesslist = NULL;

        $groups   = ProjectforkHelper::getGroupsByAccess($access);
        $children = array();

        if (!count($groups)) return $children;


        // Load all access levels if not yet set
        if (is_null($accesslist)) {
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('a.id AS value, a.title AS text, a.ordering, a.rules')
                  ->from('#__viewlevels AS a')
                  ->order('a.title ASC');

            $db->setQuery((string) $query);

            $accesslist = (array) $db->loadObjectList();
        }


        // Go through each group
        foreach($groups AS $group)
        {
            // And each access level
            foreach($accesslist AS $item)
            {
                $rules = json_decode($item->rules);
                $key   = $item->value;

                if ($key == $access) continue;

                // Check if the group is listed in the access rules and add to children if so
                if (in_array($group->value, $rules) && !array_key_exists($key, $children)) {
                    $children[$key] = $item;
                }
            }
        }

        return $children;
    }


    /**
     * Returns all parents of the given group id
     *
     * @param     integer    $id    The group id to start with
     *
     * @return    array             The parent groups
     **/
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

            $db->setQuery((string) $query);
            $groups = (array) $db->loadObjectList('id');
        }

        if (empty($path)) $path = array();

        // Make sure groupId is valid
        if (!array_key_exists($id, $groups)) return array();


        // Get parent groups and leaf group
        if (!isset($path[$id])) {
            $path[$id] = array();

            foreach ($groups as $group)
            {
                if ($group->lft <= $groups[$id]->lft && $group->rgt >= $groups[$id]->rgt) {
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
     * @param     int        $id      The project id
     *
     * @return    boolean             True on success, False on error
     **/
    public function setActiveProject($id = 0)
    {
        if (JFactory::getApplication()->isSite()) {
            JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_projectfork/models');
        }

        $model = JModelLegacy::getInstance('Project', 'ProjectforkModel');
        $data  = array('id' => (int) $id);

        return $model->setActive($data);
    }


    /**
     * Returns the currently active project ID of the user.
     *
     * @param     int    $alt    Alternative value of no project is set
     *
     * @return    int            The project id
     **/
    public function getActiveProjectId($alt = 0)
    {
        $id = JFactory::getApplication()->getUserState('com_projectfork.project.active.id', $alt);

        return (int) $id;
    }


    /**
     * Returns the currently active project title of the user.
     *
     * @param     string    $alt      Alternative value of no project is set
     *
     * @return    string              The project title
     **/
    public function getActiveProjectTitle($alt = '')
    {
        if ($alt) $alt = JText::_($alt);

        $title = JFactory::getApplication()->getUserState('com_projectfork.project.active.title', $alt);

        return $title;
    }
}
