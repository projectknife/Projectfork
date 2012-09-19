<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


/**
 * Build the route for the com_projectfork component
 *
 * @param     array    $query    An array of URL arguments
 *
 * @return    array              The URL arguments to use to assemble the subsequent URL.
 */
function ProjectforkBuildRoute(&$query)
{
    // We need to have a view in the query or it is an invalid URL
    if (!isset($query['view'])) {
        return array();
    }

    // Setup vars
    $segments = array();
    $view     = $query['view'];

    // We need a menu item.  Either the one specified in the query, or the current active one if none specified
    if (empty($query['Itemid'])) {
        $menu_item_given = false;
    }
    else {
        $menu_item_given = true;
    }


    // Handle dashboard query
    if ($view == 'dashboard') {
        if (!$menu_item_given) $segments[] = $view;
        unset($query['view']);

        // Get project filter
        if (isset($query['id'])) {
            if (strpos($query['id'], ':') === false) {
                $query['id'] = ProjectforkMakeSlug($query['id'], '#__pf_projects');
            }
        }
        else {
            $query['id'] = ProjectforkMakeSlug('0', '#__pf_projects');
        }

        $segments[] = $query['id'];
        unset($query['id']);


        return $segments;
    }


    // Handle projects query
    if($view == 'projects') {
        if (!$menu_item_given) $segments[] = $view;
        unset($query['view']);
    }


    // Handle milestones and milestone query
    if($view == 'milestones' || $view == 'milestone') {
        if (!$menu_item_given) $segments[] = $view;
        unset($query['view']);

        // Get project filter
        if (isset($query['filter_project'])) {
            if (strpos($query['filter_project'], ':') === false) {
                $query['filter_project'] = ProjectforkMakeSlug($query['filter_project'], '#__pf_projects');
            }
        }
        else {
            $query['filter_project'] = ProjectforkMakeSlug('0', '#__pf_projects');
        }

        $segments[] = $query['filter_project'];
        unset($query['filter_project']);


        // Get milestone id
        if ($view == 'milestone' && isset($query['id'])) {
            if (strpos($query['id'], ':') === false) {
                $query['id'] = ProjectforkMakeSlug($query['id'], '#__pf_milestones');
            }

            $segments[] = $query['id'];
            unset($query['id']);
        }


        return $segments;
    }


    // Handle tasks and task query
    if($view == 'tasks' || $view == 'task') {
        if (!$menu_item_given) $segments[] = $view;
        unset($query['view']);

        // Get project filter
        if (isset($query['filter_project'])) {
            if (strpos($query['filter_project'], ':') === false) {
                $query['filter_project'] = ProjectforkMakeSlug($query['filter_project'], '#__pf_projects');
            }
        }
        else {
            $query['filter_project'] = ProjectforkMakeSlug('0', '#__pf_projects');
        }

        $segments[] = $query['filter_project'];
        unset($query['filter_project']);


        // Get milestone filter
        if (isset($query['filter_milestone'])) {
            if (strpos($query['filter_milestone'], ':') === false) {
                $query['filter_milestone'] = ProjectforkMakeSlug($query['filter_milestone'], '#__pf_milestones', 'all-milestones');
            }
        }
        else {
            $query['filter_milestone'] = '0:all-milestones';
        }

        $segments[] = $query['filter_milestone'];
        unset($query['filter_milestone']);


        // Get task list filter
        if (isset($query['filter_tasklist'])) {
            if (strpos($query['filter_tasklist'], ':') === false) {
                $query['filter_tasklist'] = ProjectforkMakeSlug($query['filter_tasklist'], '#__pf_task_lists', 'all-lists');
            }
        }
        else {
            $query['filter_tasklist'] = '0:all-lists';
        }

        $segments[] = $query['filter_tasklist'];
        unset($query['filter_tasklist']);


        // Get task id
        if($view == 'task' && isset($query['id'])) {
            if (strpos($query['id'], ':') === false) {
                $query['id'] = ProjectforkMakeSlug($query['id'], '#__pf_tasks');
            }

            $segments[] = $query['id'];
            unset($query['id']);
        }


        return $segments;
    }


    // Handle users query
    if($view == 'users') {
        if (!$menu_item_given) $segments[] = $view;
        unset($query['view']);

        // Get project filter
        if (isset($query['filter_project'])) {
            if (strpos($query['filter_project'], ':') === false) {
                $query['filter_project'] = ProjectforkMakeSlug($query['filter_project'], '#__pf_projects');
            }
        }
        else {
            $query['filter_project'] = ProjectforkMakeSlug('0', '#__pf_projects');
        }

        $segments[] = $query['filter_project'];
        unset($query['filter_project']);

        return $segments;
    }


    // Handle users query
    if($view == 'user') {
        if (!$menu_item_given) $segments[] = $view;
        unset($query['view']);

        // Get user id
        if (isset($query['id'])) {
            if (strpos($query['id'], ':') === false) {
                $query['id'] = ProjectforkMakeSlug($query['id'], '#__users', 'username', 'username');
            }
        }
        else {
            $query['id'] = ProjectforkMakeSlug('0', '#__users', 'username', 'username');
        }

        $segments[] = 'profile';
        $segments[] = $query['id'];
        unset($query['id']);


        return $segments;
    }


    // Handle topics query
    if($view == 'topics') {
        if (!$menu_item_given) $segments[] = $view;
        unset($query['view']);

        // Get project filter
        if (isset($query['filter_project'])) {
            if (strpos($query['filter_project'], ':') === false) {
                $query['filter_project'] = ProjectforkMakeSlug($query['filter_project'], '#__pf_projects');
            }
        }
        else {
            $query['filter_project'] = ProjectforkMakeSlug('0', '#__pf_projects');
        }

        $segments[] = $query['filter_project'];
        unset($query['filter_project']);

        return $segments;
    }


    // Handle replies query
    if($view == 'replies') {
        if (!$menu_item_given) $segments[] = $view;
        unset($query['view']);

        // Get project filter
        if (isset($query['filter_project'])) {
            if (strpos($query['filter_project'], ':') === false) {
                $query['filter_project'] = ProjectforkMakeSlug($query['filter_project'], '#__pf_projects');
            }
        }
        else {
            $query['filter_project'] = ProjectforkMakeSlug('0', '#__pf_projects');
        }

        $segments[] = $query['filter_project'];
        unset($query['filter_project']);

        // Get topic filter
        if (isset($query['filter_topic'])) {
            if (strpos($query['filter_topic'], ':') === false) {
                $query['filter_topic'] = ProjectforkMakeSlug($query['filter_topic'], '#__pf_topics');
            }
        }
        else {
            $query['filter_topic'] = ProjectforkMakeSlug('0', '#__pf_topics');
        }

        $segments[] = $query['filter_topic'];
        unset($query['filter_topic']);

        return $segments;
    }


    // Handle timesheet query
    if($view == 'timesheet') {
        if (!$menu_item_given) $segments[] = $view;
        unset($query['view']);

        // Get project filter
        if (isset($query['filter_project'])) {
            if (strpos($query['filter_project'], ':') === false) {
                $query['filter_project'] = ProjectforkMakeSlug($query['filter_project'], '#__pf_projects');
            }
        }
        else {
            $query['filter_project'] = ProjectforkMakeSlug('0', '#__pf_projects');
        }

        $segments[] = $query['filter_project'];
        unset($query['filter_project']);

        return $segments;
    }


    // Handle repository query
    if($view == 'repository') {
        if (!$menu_item_given) $segments[] = $view;
        unset($query['view']);

        // Get project filter
        if (isset($query['filter_project'])) {
            if (strpos($query['filter_project'], ':') === false) {
                $query['filter_project'] = ProjectforkMakeSlug($query['filter_project'], '#__pf_projects');
            }
        }
        else {
            $query['filter_project'] = ProjectforkMakeSlug('0', '#__pf_projects');
        }

        $segments[] = $query['filter_project'];
        unset($query['filter_project']);

        // Get path
        if (isset($query['path'])) {
            $parent_isset = isset($query['filter_parent_id']);
            $parent_id    = (($parent_isset) ? (int) $query['filter_parent_id'] : 0);

            $parts = explode('/', $query['path']);
            $count = count($parts);

            foreach($parts AS $i => $part)
            {
                if (empty($part)) continue;
                if ($count >= 1 && $i == 0) continue;
                if ($count >= 2 && $i == ($count - 1) && $parent_id > 1) continue;

                $segments[] = $part;
            }

            unset($query['path']);
        }

        // Get directory filter
        if (isset($query['filter_parent_id'])) {
            if (strpos($query['filter_parent_id'], ':') === false) {
                $query['filter_parent_id'] = ProjectforkMakeSlug($query['filter_parent_id'], '#__pf_repo_dirs');
            }
        }
        else {
            $query['filter_parent_id'] = ProjectforkMakeSlug('0', '#__pf_repo_dirs');
        }

        $segments[] = $query['filter_parent_id'];
        unset($query['filter_parent_id']);

        return $segments;
    }


    // Handle the layout
    if (isset($query['layout'])) {
        if ($menu_item_given && isset($menuItem->query['layout'])) {
            if ($query['layout'] == $menuItem->query['layout']) {
                unset($query['layout']);
            }
        }
        else {
            if ($query['layout'] == 'default') {
                unset($query['layout']);
            }
        }
    }

    return $segments;
}



/**
 * Parse the segments of a URL.
 *
 * @param     array    The segments of the URL to parse.
 *
 * @return    array    The URL attributes to be used by the application.
 */
function ProjectforkParseRoute($segments)
{
    // Setup vars
    $vars  = array();
    $count = count($segments);
    $menu  = JFactory::getApplication()->getMenu();
    $item  = $menu->getActive();


    // Standard routing.  If we don't pick up an Itemid then we get the view from the segments
    // the first segment is the view and the last segment is the id of the item.
    if (!isset($item)) {
        $vars['view'] = $segments[0];
        $vars['id']   = $segments[$count - 1];

        return $vars;
    }


    // Set the view var
    $vars['view'] = $item->query['view'];


    // Handle Dashboard
    if ($vars['view'] == 'dashboard') {
        if ($count == 1) {
            $vars['id'] = ProjectforkParseSlug($segments[0]);
        }

        return $vars;
    }


    // Handle Milestones
    if ($vars['view'] == 'milestones') {
        if ($count >= 1) {
            $vars['filter_project'] = ProjectforkParseSlug($segments[0]);
        }
        if ($count >= 2) {
            $vars['view'] = 'milestone';
            $vars['id']   = ProjectforkParseSlug($segments[1]);
        }

        return $vars;
    }


    // Handle Milestone details
    if ($vars['view'] == 'milestone') {
        if ($count >= 1) {
            $vars['filter_project'] = ProjectforkParseSlug($segments[0]);
        }
        if ($count >= 2) {
            $vars['id'] = ProjectforkParseSlug($segments[1]);
        }

        return $vars;
    }


    // Handle Tasks
    if ($vars['view'] == 'tasks') {
        if ($count >= 1) {
            $vars['filter_project'] = ProjectforkParseSlug($segments[0]);
        }
        if ($count >= 2) {
            $vars['filter_milestone'] = ProjectforkParseSlug($segments[1]);
        }
        if ($count >= 3) {
            $vars['filter_tasklist'] = ProjectforkParseSlug($segments[2]);
        }
        if ($count >= 4) {
            $vars['view'] = 'task';
            $vars['id']   = ProjectforkParseSlug($segments[3]);
        }

        return $vars;
    }


    // Handle Task details
    if ($vars['view'] == 'task') {
        if ($count >= 1) {
            $vars['filter_project'] = ProjectforkParseSlug($segments[0]);
        }
        if ($count >= 2) {
            $vars['filter_milestone'] = ProjectforkParseSlug($segments[1]);
        }
        if ($count >= 3) {
            $vars['filter_tasklist'] = ProjectforkParseSlug($segments[2]);
        }
        if ($count >= 4) {
            $vars['id'] = ProjectforkParseSlug($segments[3]);
        }

        return $vars;
    }


    // Handle Users
    if ($vars['view'] == 'users') {
        if ($count == 1) {
            $vars['filter_project'] = ProjectforkParseSlug($segments[0]);
        }
        if ($count > 1) {
            $vars['view'] = 'user';
            $vars['id']   = ProjectforkParseSlug($segments[1]);
        }

        return $vars;
    }


    // Handle User
    if ($vars['view'] == 'user') {
        if ($count == 1) {
            $vars['id'] = ProjectforkParseSlug($segments[0]);
        }

        return $vars;
    }


    // Handle Topics
    if ($vars['view'] == 'topics') {
        if ($count == 1) {
            $vars['filter_project'] = ProjectforkParseSlug($segments[0]);
        }
        if ($count > 1) {
            $vars['view'] = 'replies';
            $vars['filter_topic'] = ProjectforkParseSlug($segments[1]);
        }

        return $vars;
    }


    // Handle Replies
    if ($vars['view'] == 'replies') {
        if ($count >= 1) {
            $vars['filter_project'] = ProjectforkParseSlug($segments[0]);
        }
        if ($count >= 2) {
            $vars['filter_topic'] = ProjectforkParseSlug($segments[1]);
        }

        return $vars;
    }


    // Handle Timesheet
    if ($vars['view'] == 'timesheet') {
        if ($count == 1) {
            $vars['filter_project'] = ProjectforkParseSlug($segments[0]);
        }

        return $vars;
    }


    // Handle Repository
    if ($vars['view'] == 'repository') {
        if ($count >= 1) {
            $vars['filter_project'] = ProjectforkParseSlug($segments[0]);
        }
        if ($count >= 2) {
            $i    = 1;
            $path = array();

            while($i < $count)
            {
                if ($i == ($count - 1)) {
                    $vars['filter_parent_id'] = ProjectforkParseSlug($segments[$i]);
                }
                else {
                    $path[] = ProjectforkParseSlug($segments[$i]);
                }
                $i++;
            }
            $vars['path'] = implode('/', $path);
        }

        return $vars;
    }


    return $vars;
}


/**
 * Parses a slug segment and extracts the ID of the item
 *
 * @param     string    $segment    The slug segment
 *
 * @return    int                   The item id
 */
function ProjectforkParseSlug($segment)
{
    if (strpos($segment, ':') === false) {
        return (int) $segment;
    }
    else {
        list($id, $alias) = explode(':', $segment, 2);
        return (int) $id;
    }
}


/**
 * Creates a slug segment
 *
 * @param     int       $id       The item id
 * @param     string    $table    The item table
 * @param     string    $alt      Alternative alias if the id is 0
 * @param     string    $field    The field to query
 *
 * @return    string              The slug
 */
function ProjectforkMakeSlug($id, $table, $alt = 'all', $field = 'alias')
{
    if ($id == '' || $id == '0') {
        if ($table == '#__pf_projects') {
            $app   = JFactory::getApplication();
            $id    = (int) $app->getUserState('com_projectfork.project.active.id', 0);
            $alias = $app->getUserState('com_projectfork.project.active.title', 'all-projects');
            $alias = JApplication::stringURLSafe($alias);

            return $id . ':' . $alias;
        }
        else {
            return '0:' . $alt;
        }
    }

    $db    = JFactory::getDbo();
    $query = $db->getQuery(true);

    $query->select($db->quoteName($field))
          ->from($db->quoteName($table))
          ->where('id = ' . (int) $id);

    $db->setQuery($query->__toString());

    $alias = $db->loadResult();
    $slug  = $id . ':' . $alias;

    return $slug;
}