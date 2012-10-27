<?php
/**
 * @package      Projectfork
 * @subpackage   Milestones
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.helper');


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
     * @param     string    $project    The project slug. Optional
     * @return    string    $link       The link
     */
    public static function getMilestonesRoute($project = '')
    {
        $link = 'index.php?option=com_pfmilestones&view=milestones&filter_project=' . $project;

        $needles = array('filter_project'  => array((int) $project));

        if ($item = PFApplicationHelper::itemRoute($needles, 'com_pfmilestones.milestones')) {
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
     * @param     string    $id         The milestone slug
     * @param     string    $project    The project slug. Optional
     *
     * @return    string    $link       The link
     */
    public static function getMilestoneRoute($id, $project = '')
    {
        $link = 'index.php?option=com_pfmilestones&view=milestone&filter_project=' . $project . '&id=' . $id;

        $needles = array('id' => array((int) $id));

        if ($item = PFApplicationHelper::itemRoute($needles, 'com_pfmilestones.milestone')) {
            $link .= '&Itemid=' . $item;
        }
        elseif ($item = PFApplicationHelper::itemRoute(null, 'com_pfmilestones.milestones')) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }
}
