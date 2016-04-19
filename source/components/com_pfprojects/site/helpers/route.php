<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfprojects
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2016 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die;


/**
 * Projects Component Route Helper
 *
 * @static
 */
abstract class PFprojectsHelperRoute
{
    /**
     * Creates a link to the dashboard
     *
     * @param     string    $project_slug    The project slug. Optional
     *
     * @return    string    $link            The link
     */
    public static function getDashboardRoute($project_slug = '')
    {
        $link = 'index.php?option=com_projectfork&view=dashboard';

        // Get the id from the slug
        if (strrpos($project_slug, ':') !== false) {
            $slug_parts = explode(':', $project_slug);
            $project_id = (int) $slug_parts[0];
        }
        else {
            $project_id = (int) $project_slug;
        }

        if ($project_id) {
            $link .= '&id=' . $project_slug;
        }

        $needles = array('id' => array($project_id));

        if ($item = PFApplicationHelper::itemRoute($needles, 'com_projectfork.dashboard')) {
            $link .= '&Itemid=' . $item;
        }
        elseif ($item = PFApplicationHelper::itemRoute(null, 'com_projectfork.dashboard')) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }


    /**
     * Creates a link to the projects overview
     *
     * @param     string     $cat_slug    The category slug. Optional
     * @param     integer    $item        The desired menu item id to append. Optional
     *
     * @return    string     $link        The link
     */
    public static function getProjectsRoute($cat_slug = '', $item = null)
    {
        $link = 'index.php?option=com_pfprojects&view=projects';

        // Get the id from the slug
        if (strrpos($cat_slug, ':') !== false) {
            $slug_parts = explode(':', $cat_slug);
            $cat_id = (int) $slug_parts[0];
        }
        else {
            $cat_id = (int) $cat_slug;
        }

        if ($cat_id) {
            $link .= '&filter_category=' . $cat_slug;
        }

        $needles = array('filter_category'  => array($cat_id));

        if (!is_null($item)) {
            $link .= '&Itemid=' . $item;
        }
        elseif ($item = PFApplicationHelper::itemRoute($needles, 'com_pfprojects.projects')) {
            $link .= '&Itemid=' . $item;
        }
        elseif ($item = PFApplicationHelper::itemRoute(null, 'com_pfprojects.projects')) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }


    /**
     * Creates a link to the project form
     *
     * @param     string    $project_slug    The project slug. Optional
     *
     * @return    string    $link            The link
     */
    public static function getProjectEditRoute($project_slug = '')
    {
        $link = 'index.php?option=com_pfprojects&task=form.edit&id=' . $project_slug;

        // Get the form menu item
        $item = PFApplicationHelper::itemRoute(null, 'com_pfprojects.form');

        if (!$item) {
            $app = JFactory::getApplication();

            // Stay on current menu item if we are viewing a project list
            if ($app->input->get('option') == 'com_pfprojects' && $app->input->get('view') == 'projects') {
                $item = PFApplicationHelper::getActiveMenuItemId();
            }
            else {
                // Find overview menu item
                $item = PFApplicationHelper::itemRoute(null, 'com_pfprojects.projects');
            }
        }

        if ($item) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }
}
