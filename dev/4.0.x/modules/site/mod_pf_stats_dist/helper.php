<?php
/**
* @package   Projectfork Task Distribution Statistics
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


abstract class modPFstatsDistHelper
{
	public static function getProject()
	{
	    $item = new stdClass();

        $item->id    = ProjectforkHelper::getActiveProjectId();
        $item->title = ProjectforkHelper::getActiveProjectTitle();

		return $item;
	}


    public static function getStats(&$params, $id = 0)
    {
        $user  = JFactory::getUser();
        $db    = JFactory::getDbo();
		$query = $db->getQuery(true);

        // Get Params
        $show_c   = (int) $params->get('show_completed', 1);
        $show_u   = (int) $params->get('show_unassigned', 1);
        $limit    = (int) $params->get('limit', 5);

        // Get the user task distribution
        $query->select('COUNT(a.user_id) AS data')
              ->from('#__pf_ref_users AS a');

        $query->join('RIGHT', '#__pf_tasks AS t ON t.id = a.item_id');
        $query->where('a.item_type = '.$db->quote('task'));

        $query->select('u.name AS label');
        $query->join('LEFT', '#__users AS u ON u.id = a.user_id');

        if($id) {
            $query->where('t.project_id = '.$id);
        }

        if(!$user->authorise('core.admin')) {
		    $groups	= implode(',', $user->getAuthorisedViewLevels());
			$query->where('t.access IN ('.$groups.')');
		}

        // Apply complete stage filter
        if($show_c == 0) {
            $query->where('t.complete = 0');
        }

        $query->group('a.user_id');
        $query->order('data', 'desc');

        $db->setQuery($query->__toString(), 0, $limit);
        $data = (array) $db->loadObjectList();


        // Find unassigned tasks if enabled
        $unassigned = 0;
        if($show_u) {
            // Count total amount of tasks
            $query = $db->getQuery(true);
            $query->select('COUNT(a.id)')
                  ->from('#__pf_tasks AS a');

            if($id) {
                $query->where('a.project_id = '.$id);
            }

            if(!$user->authorise('core.admin')) {
    		    $groups	= implode(',', $user->getAuthorisedViewLevels());
    			$query->where('a.access IN ('.$groups.')');
    		}

            $db->setQuery($query->__toString());
            $total = (int) $db->loadResult();


            // Count assigned tasks
            $query = $db->getQuery(true);

            $query->select('COUNT(DISTINCT a.item_id)')
                  ->from('#__pf_ref_users AS a');

            $query->join('RIGHT', '#__pf_tasks AS t ON t.id = a.item_id');
            $query->where('a.item_type = '.$db->quote('task'));

            if($id) {
                $query->where('t.project_id = '.$id);
            }

            if(!$user->authorise('core.admin')) {
    		    $groups	= implode(',', $user->getAuthorisedViewLevels());
    			$query->where('t.access IN ('.$groups.')');
    		}

            $db->setQuery($query->__toString());
            $assigned = (int) $db->loadResult();


            // Calculate the amount of unassigned tasks
            $unassigned = $total - $assigned;


            $obj_unassigned = new stdclass();
            $obj_unassigned->data  = $unassigned;
            $obj_unassigned->label = JText::_('MOD_PF_STATS_DIST_UNASSIGNED');

            $data[] = $obj_unassigned;
        }


        // Data values must be of type integer!
        foreach($data AS $i => $item)
        {
            $data[$i]->data = (int) $item->data;
        }

        return $data;
    }
}
