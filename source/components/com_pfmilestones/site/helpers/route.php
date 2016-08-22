<?php
/**
 * @package      pkg_projectknife
 * @subpackage   com_pfmilestones
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2016 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die;


/**
 * Component Route Helper
 *
 * @static    
 */
abstract class PFmilestonesHelperRoute
{
    /**
     * Creates a link to the milestones overview
     *
     * @param     string     $project_slug    The project slug. Optional
     * @param     integer    $item            The desired menu item id to append. Optional
     *
     * @return    string     $link            The link
     */
    public static function getMilestonesRoute($project_slug = '', $item = null)
    {
        if (!$project_slug) {
            $project_slug = PFApplicationHelper::getActiveProjectId();
        }

        $link = 'index.php?option=com_pfmilestones&view=milestones&filter_project=' . $project_slug;

        // Get the id from the slug
        if (strrpos($project_slug, ':') !== false) {
            $slug_parts = explode(':', $project_slug);
            $project_id = (int) $slug_parts[0];
        }
        else {
            $project_id = (int) $project_slug;
        }

        $needles = array('filter_project'  => array($project_id));

        if (!is_null($item)) {
            $link .= '&Itemid=' . $item;
        }
        elseif ($item = PFApplicationHelper::itemRoute($needles, 'com_pfmilestones.milestones')) {
            $link .= '&Itemid=' . $item;
        }
        elseif ($item = PFApplicationHelper::itemRoute(null, 'com_pfmilestones.milestones')) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }


    /**
     * Creates a link to a milestone item view
     *
     * @param     string    $milestone_slug    The milestone slug
     * @param     string    $project_slug      The project slug. Optional
     *
     * @return    string    $link              The link
     */
    public static function getMilestoneRoute($milestone_slug, $project_slug = '')
    {
        if (!$project_slug) {
            $project_slug = PFApplicationHelper::getActiveProjectId();
        }

        $link = 'index.php?option=com_pfmilestones&view=milestone&filter_project=' . $project_slug . '&id=' . $milestone_slug;

        // Get the id from the slug
        if (strrpos($milestone_slug, ':') !== false) {
            $slug_parts   = explode(':', $milestone_slug);
            $milestone_id = (int) $slug_parts[0];
        }
        else {
            $milestone_id = (int) $milestone_slug;
        }

        $needles = array('id' => array($milestone_slug));
        $item    = PFApplicationHelper::itemRoute($needles, 'com_pfmilestones.milestone');

        if (!$item) {
            $app = JFactory::getApplication();

            // Stay on current menu item if we are viewing a milestone list
            if ($app->input->get('option') == 'com_pfmilestones' && $app->input->get('view') == 'milestones') {
                $item = PFApplicationHelper::getActiveMenuItemId();
            }
            else {
                // Find overview menu item
                $item = PFApplicationHelper::itemRoute(null, 'com_pfmilestones.milestones');
            }
        }

        if ($item) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }
}
