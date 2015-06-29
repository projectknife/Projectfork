<?php
/**
 * @package      Projectfork
 * @subpackage   Projects
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.helper');


/**
 * Projects Component Route Helper
 *
 * @static
 */
abstract class PFprojectsHelperRoute
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
     * @return    string    $link    The link
     */
    public static function getProjectsRoute()
    {
        $link = 'index.php?option=com_pfprojects&view=projects';

        if ($item = PFApplicationHelper::itemRoute(null, 'com_pfprojects.projects')) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }


    public static function getProjectEditRoute($project)
    {
        $link = 'index.php?option=com_pfprojects&task=form.edit&id=' . $project;

        if ($item = PFApplicationHelper::itemRoute(null, 'com_pfprojects.form')) {
            $link .= '&Itemid=' . $item;
        }
        elseif ($item = PFApplicationHelper::itemRoute(null, 'com_pfprojects.projects')) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }
}
