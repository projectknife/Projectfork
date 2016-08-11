<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfmilestones
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2016 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die;


/**
 * Build the route for the com_pfmilestones component
 *
 * @param     array    $query    An array of URL arguments
 *
 * @return    array              The URL arguments to use to assemble the subsequent URL.
 */
function PFmilestonesBuildRoute(&$query)
{
    // We need to have a view in the query or it is an invalid URL
    if (!isset($query['view'])) {
        return array();
    }

    // Setup vars
    $segments = array();

    // Get the view
    $view = $query['view'];
    unset($query['view']);

    // If there is no menu item, add view to segments
    if (empty($query['Itemid'])) {
        $menu_item_given = false;
    }
    else {
        $menu = JFactory::getApplication()->getMenu();
        $item = $menu->getActive();

        if ($item->query['view'] != $view) {
            $menu_item_given = false;
        }
        else {
            $menu_item_given = true;
        }
    }

    if (!$menu_item_given) {
        $segments[] = $view;
    }


    // Handle milestones query
    if($view == 'milestones') {
        if (isset($query['filter_project'])) {
            if (strrpos($query['filter_project'], ':') === false) {
                $query['filter_project'] = PFmilestonesMakeSlug($query['filter_project'], '#__pf_projects');
            }
        }
        else {
            $query['filter_project'] = PFmilestonesMakeSlug('0', '#__pf_projects');
        }

        $segments[] = $query['filter_project'];
        unset($query['filter_project']);
    }



    // Handle milestone query
    if ($view == 'milestone' && isset($query['id'])) {
        if (isset($query['filter_project'])) {
            if (strrpos($query['filter_project'], ':') === false) {
                $query['filter_project'] = PFmilestonesMakeSlug($query['filter_project'], '#__pf_projects');
            }
        }
        else {
            $query['filter_project'] = PFmilestonesMakeSlug('0', '#__pf_projects');
        }

        $segments[] = $query['filter_project'];
        unset($query['filter_project']);

        if (strrpos($query['id'], ':') === false) {
            $query['id'] = PFmilestonesMakeSlug($query['id'], '#__pf_milestones');
        }

        $segments[] = $query['id'];
        unset($query['id']);
    }


    // Handle form query
    if($view == 'form') {
        if (isset($query['id'])) {
            if (strrpos($query['id'], ':') === false) {
                $query['id'] = PFmilestonesMakeSlug($query['id'], '#__pf_milestones');
            }

            $segments[] = $query['id'];
            unset($query['id']);
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
function PFmilestonesParseRoute($segments)
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
    $alt_view     = false;

    if ($count && ($segments[0] == 'form' || $segments[0] == 'milestone')) {
        $vars['view'] = $segments[0];

        if ($count == 2) {
            $vars['id'] = $segments[1];
        }

        $alt_view = true;
    }

    // Handle Milestones
    if ($vars['view'] == 'milestones') {
        if ($count >= 1) {
            $vars['filter_project'] = PFmilestonesParseSlug($segments[0]);
        }
        if ($count >= 2) {
            $vars['view'] = 'milestone';
            $vars['id']   = PFmilestonesParseSlug($segments[1]);
        }

        return $vars;
    }

    // Handle Milestone details
    if ($vars['view'] == 'milestone') {
        if ($alt_view) {
            if ($count >= 2) {
                $vars['filter_project'] = PFmilestonesParseSlug($segments[1]);
            }
            if ($count >= 3) {
                $vars['id'] = PFmilestonesParseSlug($segments[2]);
            }
        }
        else {
            if ($count >= 1) {
                $vars['filter_project'] = PFmilestonesParseSlug($segments[0]);
            }
            if ($count >= 2) {
                $vars['id'] = PFmilestonesParseSlug($segments[1]);
            }
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
function PFmilestonesParseSlug($segment)
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
function PFmilestonesMakeSlug($id, $table, $alt = 'all', $field = 'alias')
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

    $db->setQuery((string) $query);

    $alias = $db->loadResult();
    $slug  = $id . ':' . $alias;

    return $slug;
}
