<?php
/**
* @package      Projectfork Project Workload Statistics
*
* @author       Tobias Kuhn (eaxs)
* @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();


/**
 * Module helper class
 *
 */
abstract class modPFstatsLoadHelper
{
    /**
     * Method to get the project statistics
     *
     * @param     object     $params    The module params
     *
     * @return    array      $data      The stats
     */
    public static function getStatsProjects(&$params)
    {
        $user  = JFactory::getUser();
        $db    = JFactory::getDbo();
        $date  = JDate::getInstance();
        $query = $db->getQuery(true);
        $data  = array();

        // Load the projects
        $query->select('a.title, a.end_date, COUNT(t.id) AS tasks')
              ->from('#__pf_projects AS a')
              ->join('LEFT', '#__pf_tasks AS t ON t.project_id = a.id')
              ->where('a.end_date > ' . $db->quote($date->toSql()))
              ->where('(t.state = 0 OR t.state = 1)')
              ->where('t.complete = 0')
              ->group('a.id')
              ->order('tasks DESC');

        $db->setQuery((string) $query, 0, $params->get('limit', 5));
        $projects = (array) $db->loadObjectList();

        // Calculate workload
        $date_unix = $date->toUnix();

        foreach($projects AS $i => $project)
        {
            $load = 0.00;

            if ($project->tasks > 0) {
                $project_date = JDate::getInstance($project->end_date);
                $deadline     = $project_date->toUnix();

                $secs_left = $deadline - $date_unix;
                $days_left = round($secs_left / 86400, 2);

                $load = round($project->tasks / $days_left, 2);
            }

            $set = new stdClass();
            $set->data  = array(array($i, $load));
            $set->label = $project->title;

            $data[] = $set;
        }

        return $data;
    }


    /**
     * Method to get the user statistics
     *
     * @param     object     $params    The module params
     * @param     integer    $id        The user id
     *
     * @return    array      $data      The stats
     */
    public static function getStatsUser(&$params, $id)
    {
        $user  = JFactory::getUser();
        $db    = JFactory::getDbo();
        $date  = JDate::getInstance();
        $query = $db->getQuery(true);
        $data  = array();

        // Load the projects
        $query->select('a.title, a.end_date, COUNT(t.id) AS tasks')
              ->from('#__pf_projects AS a')
              ->join('RIGHT', '#__pf_tasks AS t ON t.project_id = a.id')
              ->join('RIGHT', '#__pf_ref_users AS u ON u.item_id = t.id')
              ->where('u.item_type = ' . $db->quote('task'))
              ->where('u.user_id = ' . $db->quote($id))
              ->where('a.end_date > ' . $db->quote($date->toSql()))
              ->where('(t.state = 0 OR t.state = 1)')
              ->where('t.complete = 0')
              ->group('a.id, u.user_id')
              ->order('tasks DESC');

        $db->setQuery((string) $query, 0, $params->get('limit', 5));
        $projects = (array) $db->loadObjectList();

        // Calculate workload
        $date_unix = $date->toUnix();

        foreach($projects AS $i => $project)
        {
            $load = 0.00;

            if ($project->tasks > 0) {
                $project_date = JDate::getInstance($project->end_date);
                $deadline     = $project_date->toUnix();

                $secs_left = $deadline - $date_unix;
                $days_left = round($secs_left / 86400, 2);

                $load = round($project->tasks / $days_left, 2);
            }

            if ($load == 0.00) continue;

            $set = new stdClass();
            $set->data  = array(array($i, $load));
            $set->label = $project->title;

            $data[] = $set;
        }

        return $data;
    }
}
