<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.helper');


/**
 * Projectfork Component Route Helper
 *
 * @static
 */
abstract class ProjectforkHelperRoute
{
    protected static $lookup;


    /**
     * Creates a link to the dashboard
     *
     * @param     string    $project    The project slug. Optional
     * @return    string    $link       The link
     */
    public static function getDashboardRoute($project = '')
    {
        if ($project) {
            $link = 'index.php?option=com_projectfork&view=dashboard&id=' . $project;
        }
        else {
            $link = 'index.php?option=com_projectfork&view=dashboard';
        }

        $needles = array('id'  => array((int) $project));

        if ($item = self::_findItem($needles, 'dashboard')) {
            $link .= '&Itemid=' . $item;
        }
        elseif ($item = self::_findItem(null, 'dashboard')) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }


    /**
     * Creates a link to the projects overview
     *
     * @return    string    $link    The link
     */
    public static function getProjectsRoute()
    {
        $link = 'index.php?option=com_projectfork&view=projects';

        if ($item = self::_findItem(null, 'projects')) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }


    /**
     * Creates a link to the milestones overview
     *
     * @param     string    $project    The project slug. Optional
     * @return    string    $link       The link
     */
    public static function getMilestonesRoute($project = '')
    {
        $link = 'index.php?option=com_projectfork&view=milestones&filter_project=' . $project;

        $needles = array('filter_project'  => array((int) $project));

        if ($item = self::_findItem($needles, 'milestones')) {
            $link .= '&Itemid=' . $item;
        }
        elseif ($item = self::_findItem(null, 'milestones')) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }


    /**
     * Creates a link to a milestone item view
     *
     * @param     string    $id         The milestone slug
     * @param     string    $project    The project slug. Optional
     *
     * @return    string    $link       The link
     */
    public static function getMilestoneRoute($id, $project = '')
    {
        $link = 'index.php?option=com_projectfork&view=milestone&filter_project=' . $project.'&id=' . $id;

        $needles = array('id' => array((int) $id));

        if ($item = self::_findItem($needles, 'milestone')) {
            $link .= '&Itemid=' . $item;
        }
        elseif ($item = self::_findItem(null, 'milestones')) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }


    /**
     * Creates a link to the task overview
     *
     * @param     string    $project      The project slug. Optional
     * @param     string    $milestone    The milestone slug. Optional
     * @param     string    $list         The list slug. Optional
     *
     * @return    string    $link         The link
     */
    public static function getTasksRoute($project = '', $milestone = '', $list = '')
    {
        $link  = 'index.php?option=com_projectfork&view=tasks';
        $link .= '&filter_project=' . $project;
        $link .= '&filter_milestone=' . $milestone;
        $link .= '&filter_tasklist=' . $list;

        $needles = array('filter_project'   => array((int) $project),
                         'filter_milestone' => array((int) $milestone),
                         'filter_tasklist'  => array((int) $list)
                        );

        if ($item = self::_findItem($needles, 'tasks')) {
            $link .= '&Itemid=' . $item;
        }
        elseif ($item = self::_findItem(null, 'tasks')) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }


    /**
     * Creates a link to a task item view
     *
     * @param     string    $id           The task slug
     * @param     string    $project      The project slug. Optional
     * @param     string    $milestone    The milestone slug. Optional
     * @param     string    $list         The list slug. Optional
     *
     * @return    string    $link         The link
     */
    public static function getTaskRoute($id, $project = '', $milestone = '', $list = '')
    {
        $link  = 'index.php?option=com_projectfork&view=task';
        $link .= '&filter_project=' . $project;
        $link .= '&filter_milestone=' . $milestone;
        $link .= '&filter_tasklist=' . $list;
        $link .= '&id=' . $id;

        $needles = array('id' => array((int) $id));

        if ($item = self::_findItem($needles, 'task')) {
            $link .= '&Itemid=' . $item;
        }
        elseif ($item = self::_findItem(null, 'tasks')) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }


    /**
     * Creates a link to the users overview
     *
     * @param     string    $project    The project slug. Optional
     *
     * @return    string    $link       The link
     */
    public static function getUsersRoute($project = '')
    {
        $link  = 'index.php?option=com_projectfork&view=users';
        $link .= '&filter_project=' . $project;

        $needles = array('filter_project' => array((int) $project)
                        );

        if ($item = self::_findItem($needles, 'users')) {
            $link .= '&Itemid=' . $item;
        }
        elseif ($item = self::_findItem(null, 'users')) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }


    /**
     * Creates a link to a user item view
     *
     * @param     string    $id      The user slug
     *
     * @return    string    $link    The link
     */
    public static function getUserRoute($id)
    {
        $link  = 'index.php?option=com_projectfork&view=user';
        $link .= '&id=' . $id;

        $needles = array('id' => array((int) $id));

        if ($item = self::_findItem($needles, 'user')) {
            $link .= '&Itemid=' . $item;
        }
        elseif ($item = self::_findItem(null, 'users')) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }


    /**
     * Creates a link to the topics overview
     *
     * @param     string    $project      The project slug. Optional
     *
     * @return    string    $link         The link
     */
    public static function getTopicsRoute($project = '')
    {
        $link  = 'index.php?option=com_projectfork&view=topics';
        $link .= '&filter_project=' . $project;

        $needles = array('filter_project'   => array((int) $project)
                        );

        if ($item = self::_findItem($needles, 'topics')) {
            $link .= '&Itemid=' . $item;
        }
        elseif ($item = self::_findItem(null, 'topics')) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }


    /**
     * Creates a link to a topic item view
     *
     * @param     string    $id           The topic slug
     * @param     string    $project      The project slug. Optional
     *
     * @return    string    $link         The link
     */
    public static function getTopicRoute($id, $project = '')
    {
        return ProjectforkHelperRoute::getRepliesRoute($id, $project);
    }


    /**
     * Creates a link to a topic item view
     *
     * @param     string    $id           The topic slug
     * @param     string    $project      The project slug. Optional
     *
     * @return    string    $link         The link
     */
    public static function getRepliesRoute($id, $project = '')
    {
        $link  = 'index.php?option=com_projectfork&view=replies';
        $link .= '&filter_project=' . $project;
        $link .= '&filter_topic=' . $id;

        $needles = array('id' => array((int) $id));

        if ($item = self::_findItem($needles, 'topic')) {
            $link .= '&Itemid=' . $item;
        }
        elseif ($item = self::_findItem(null, 'topics')) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }


    /**
     * Creates a link to the timesheet overview
     *
     * @param     string    $project      The project slug. Optional
     *
     * @return    string    $link         The link
     */
    public static function getTimesheetRoute($project = '')
    {
        $link  = 'index.php?option=com_projectfork&view=timesheet';
        $link .= '&filter_project=' . $project;

        $needles = array('filter_project'   => array((int) $project)
                        );

        if ($item = self::_findItem($needles, 'timesheet')) {
            $link .= '&Itemid=' . $item;
        }
        elseif ($item = self::_findItem(null, 'timesheet')) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }

    public static function getRepositoryRoute($project = '', $dir = '', $path = '')
    {
        static $paths = array();

        // Get all paths of the project
        if (!isset($paths[$project])) {
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('id, path')
                  ->from('#__pf_repo_dirs')
                  ->where('project_id = ' . $db->quote((int) $project));

            $db->setQuery($query);
            $list = (array) $db->loadObjectList();

            $project_paths = array();

            foreach($list AS $list_item)
            {
                $id = $list_item->id;
                $p  = $list_item->path;

                $project_paths[$p] = $id;
            }

            $paths[$project] = $project_paths;
        }

        if ($path) {
            $parts    = array_reverse(explode('/', $path));
            $new_path = array();
            $looped   = array();

            while(count($parts))
            {
                $part     = array_pop($parts);
                $looped[] = $part;

                $find = implode('/', $looped);

                if (isset($paths[$project][$find])) {
                    $new_path[] = $paths[$project][$find] . ':' . $part;
                }
            }

            $path = implode('/', $new_path);
        }

        $link  = 'index.php?option=com_projectfork&view=repository';
        $link .= '&filter_project=' . $project;
        $link .= '&filter_parent_id=' . $dir;
        $link .= '&path=' . $path;

        $needles = array('filter_project'   => array((int) $project),
                         'filter_parent_id' => array((int) $dir),
                         'path' => array((int) $path),
                        );

        if ($item = self::_findItem($needles, 'repository')) {
            $link .= '&Itemid=' . $item;
        }
        elseif ($item = self::_findItem(null, 'repository')) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }


    /**
     * This method will try to find a menu item for the given view and
     * URL params ($needles)
     *
     * @param     array     $needles     Query segments to search for
     * @param     string    $com_view    The component view name to look for
     * @return    mixed                  The item id if found, or NULL
     */
    protected static function _findItem($needles = null, $com_view = null)
    {
        $app        = JFactory::getApplication();
        $menus        = $app->getMenu('site');

        // Prepare the reverse lookup array.
        if (self::$lookup === null)
        {
            self::$lookup = array();

            $component = JComponentHelper::getComponent('com_projectfork');
            $items       = $menus->getItems('component_id', $component->id);

            foreach ($items as $item)
            {
                if (isset($item->query) && isset($item->query['view']))
                {
                    $view = $item->query['view'];
                    if (!isset(self::$lookup[$view])) {
                        self::$lookup[$view] = array();

                    }

                    if (isset($item->query['id'])) {
                        self::$lookup[$view][$item->query['id']] = $item->id;
                    }
                    else {
                        self::$lookup[$view][0] = $item->id;
                    }
                }
            }
        }

        if ($needles)
        {
            foreach ($needles as $view => $ids)
            {
                if (isset(self::$lookup[$view]))
                {
                    foreach($ids as $id)
                    {
                        if (isset(self::$lookup[$view][(int)$id])) {
                            return self::$lookup[$view][(int)$id];
                        }
                    }
                }
            }
        }
        else
        {
            $active = $menus->getActive();
            if ($active && $active->component == 'com_projectfork') {
                if ($com_view) {
                    if (isset(self::$lookup[$com_view][0])) return self::$lookup[$com_view][0];
                }

                return $active->id;
            }
            else {
                if ($com_view) {
                    if (isset(self::$lookup[$com_view][0])) return self::$lookup[$com_view][0];
                }
            }
        }

        return null;
    }
}
