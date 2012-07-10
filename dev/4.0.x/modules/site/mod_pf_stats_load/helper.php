<?php
/**
* @package   Projectfork Project Workload Statistics
* @copyright Copyright (C) 2012 Tobias Kuhn. All rights reserved.
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

// no direct access
defined('_JEXEC') or die;


abstract class modPFstatsLoadHelper
{
    public static function getStatsProjects(&$params)
    {
        $user  = JFactory::getUser();
        $db    = JFactory::getDbo();
        $date  = JDate::getInstance();
		$query = $db->getQuery(true);
        $data  = array();

        // Load the projects
        $query->select('a.title, a.end_date, COUNT(t.id) AS tasks')
              ->from('#__pf_projects AS a');

        $query->join('LEFT', '#__pf_tasks AS t ON t.project_id = a.id');

        $query->where('a.end_date > '.$db->quote($date->toSql()));
        $query->where('(t.state = 0 OR t.state = 1)');
        $query->where('t.complete = 0');

        $query->group('a.id');
        $query->order('tasks DESC');

        $db->setQuery($query->__toString(), 0, $params->get('limit', 5));
        $projects = (array) $db->loadObjectList();

        // Calculate workload
        $date_unix = $date->toUnix();

        foreach($projects AS $i => $project)
        {
            $load = 0.00;

            if($project->tasks > 0) {
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


    public static function getStatsUser(&$params, $id)
    {
        $user  = JFactory::getUser();
        $db    = JFactory::getDbo();
        $date  = JDate::getInstance();
		$query = $db->getQuery(true);
        $data  = array();

        // Load the projects
        $query->select('a.title, a.end_date, COUNT(t.id) AS tasks')
              ->from('#__pf_projects AS a');

        $query->join('RIGHT', '#__pf_tasks AS t ON t.project_id = a.id');
        $query->join('RIGHT', '#__pf_ref_users AS u ON u.item_id = t.id');
        $query->where('u.item_type = '.$db->quote('task'));
        $query->where('u.user_id = '.$db->quote($id));

        $query->where('a.end_date > '.$db->quote($date->toSql()));
        $query->where('(t.state = 0 OR t.state = 1)');
        $query->where('t.complete = 0');

        $query->group('a.id, u.user_id');
        $query->order('tasks DESC');


        $db->setQuery($query->__toString(), 0, $params->get('limit', 5));
        $projects = (array) $db->loadObjectList();

        // Calculate workload
        $date_unix = $date->toUnix();

        foreach($projects AS $i => $project)
        {
            $load = 0.00;

            if($project->tasks > 0) {
                $project_date = JDate::getInstance($project->end_date);
                $deadline     = $project_date->toUnix();

                $secs_left = $deadline - $date_unix;
                $days_left = round($secs_left / 86400, 2);

                $load = round($project->tasks / $days_left, 2);
            }

            if($load == 0.00) continue;

            $set = new stdClass();
            $set->data  = array(array($i, $load));
            $set->label = $project->title;

            $data[] = $set;
        }

        return $data;
    }
}
