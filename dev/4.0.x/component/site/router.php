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

defined('_JEXEC') or die;


/**
 * Build the route for the com_projectfork component
 *
 * @param	array	An array of URL arguments
 * @return	array	The URL arguments to use to assemble the subsequent URL.
 */
function ProjectforkBuildRoute(&$query)
{
	$segments = array();

	// Get a menu item based on Itemid or currently active
	$app		= JFactory::getApplication();
	$menu		= $app->getMenu();

	// We need a menu item.  Either the one specified in the query, or the current active one if none specified
	if (empty($query['Itemid'])) {
		$menuItem      = $menu->getActive();
		$menuItemGiven = false;
	}
	else {
		$menuItem      = $menu->getItem($query['Itemid']);
		$menuItemGiven = true;
	}

	if (isset($query['view'])) {
		$view = $query['view'];
	}
	else {
		// We need to have a view in the query or it is an invalid URL
		return $segments;
	}


    // Dashboard
    if($view == 'dashboard') {
        if (!$menuItemGiven) $segments[] = $view;
        unset($query['view']);

        if (isset($query['id'])) {
            if (strpos($query['id'], ':') === false) {
				$db = JFactory::getDbo();

				$aquery = $db->setQuery($db->getQuery(true)
					         ->select('alias')
					         ->from('#__pf_projects')
					         ->where('id='.(int)$query['id'])
				          );

				$alias = $db->loadResult();
				$query['id'] = $query['id'].':'.$alias;
			}

            $segments[] = $query['id'];
            unset($query['id']);
        }
        else {
			return $segments;
        }
    }


    // Projects
    if($view == 'projects') {
        if (!$menuItemGiven) $segments[] = $view;
        unset($query['view']);
    }


    // Milestones
    if($view == 'milestones') {
        if (!$menuItemGiven) $segments[] = $view;
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


    // Task Lists
    if($view == 'tasklists') {
        if (!$menuItemGiven) $segments[] = $view;
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
            $query['filter_milestone'] = '0:all';
        }

        $segments[] = $query['filter_milestone'];
        unset($query['filter_milestone']);


        return $segments;
    }


    // Tasks
    if($view == 'tasks') {
        if (!$menuItemGiven) $segments[] = $view;
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


        return $segments;
    }


	// Handle the layout
	if (isset($query['layout'])) {
		if ($menuItemGiven && isset($menuItem->query['layout'])) {
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
 * @param	array	The segments of the URL to parse.
 * @return	array	The URL attributes to be used by the application.
 */
function ProjectforkParseRoute($segments)
{
	$vars = array();

	// Get the active menu item.
	$app   = JFactory::getApplication();
	$menu  = $app->getMenu();
	$item  = $menu->getActive();
	$db    = JFactory::getDBO();


	// Count route segments
	$count = count($segments);


	// Standard routing.  If we don't pick up an Itemid then we get the view from the segments
	// the first segment is the view and the last segment is the id of the item.
	if (!isset($item)) {
		$vars['view'] = $segments[0];
		$vars['id']	  = $segments[$count - 1];

		return $vars;
	}


    // Set the view var
    $vars['view'] = $item->query['view'];


    // Dashboard
    if($vars['view'] == 'dashboard') {
        if($count == 1) {
            $vars['id'] = ProjectforkParseSlug($segments[0]);
        }

        return $vars;
    }


    // Milestones
    if($vars['view'] == 'milestones') {
        if($count >= 1) {
            $vars['filter_project'] = ProjectforkParseSlug($segments[0]);
        }

        return $vars;
    }


    // Task Lists
    if($vars['view'] == 'tasklists') {
        if($count >= 1) {
            $vars['filter_project'] = ProjectforkParseSlug($segments[0]);
        }
        if($count >= 2) {
            $vars['filter_milestone'] = ProjectforkParseSlug($segments[1]);
        }

        return $vars;
    }


    // Tasks
    if($vars['view'] == 'tasks') {
        if($count >= 1) {
            $vars['filter_project'] = ProjectforkParseSlug($segments[0]);
        }
        if($count >= 2) {
            $vars['filter_milestone'] = ProjectforkParseSlug($segments[1]);
        }
        if($count >= 3) {
            $vars['filter_tasklist'] = ProjectforkParseSlug($segments[2]);
        }

        return $vars;
    }


	return $vars;
}


/**
 * Parses a slug segment and extracts the ID of the item
 *
 * @param	string	$segment    The slug segment
 * @return	int  	            The item id
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
 * @param	int  	$id       The item id
 * @param   string  $table    The item table
 * @param	string  $alt      Alternative alias if the id is 0
 * @return  string            The slug
 */
function ProjectforkMakeSlug($id, $table, $alt = 'all')
{
    if($id == '' || $id == '0') {
        if($table == '#__pf_projects') {
            $app = JFactory::getApplication();

            $id    = (int) $app->getUserState('com_projectfork.project.active.id', 0);
            $alias = $app->getUserState('com_projectfork.project.active.title', 'all-projects');

            return $id.':'.$alias;
        }
        else {
            return '0:'.$alt;
        }
    }

    $db = JFactory::getDbo();

	$aquery = $db->setQuery($db->getQuery(true)
				 ->select('alias')
				 ->from($table)
				 ->where('id='.(int) $id));

	$alias = $db->loadResult();
	$slug  = $id.':'.$alias;

    return $slug;
}