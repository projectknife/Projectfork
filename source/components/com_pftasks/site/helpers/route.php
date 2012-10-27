<?php
/**
 * @package      Projectfork
 * @subpackage   Tasks
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
abstract class PFtasksHelperRoute
{
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
        $link  = 'index.php?option=com_pftasks&view=tasks';
        $link .= '&filter_project=' . $project;
        $link .= '&filter_milestone=' . $milestone;
        $link .= '&filter_tasklist=' . $list;

        $needles = array('filter_project'   => array((int) $project),
                         'filter_milestone' => array((int) $milestone),
                         'filter_tasklist'  => array((int) $list)
                        );

        if ($item = PFApplicationHelper::itemRoute($needles, 'com_pftasks.tasks')) {
            $link .= '&Itemid=' . $item;
        }
        elseif ($item = PFApplicationHelper::itemRoute(null, 'com_pftasks.tasks')) {
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
        $link  = 'index.php?option=com_pftasks&view=task';
        $link .= '&filter_project=' . $project;
        $link .= '&filter_milestone=' . $milestone;
        $link .= '&filter_tasklist=' . $list;
        $link .= '&id=' . $id;

        $needles = array('id' => array((int) $id));

        if ($item = PFApplicationHelper::itemRoute($needles, 'com_pftasks.task')) {
            $link .= '&Itemid=' . $item;
        }
        elseif ($item = PFApplicationHelper::itemRoute(null, 'com_pftasks.tasks')) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }
}
