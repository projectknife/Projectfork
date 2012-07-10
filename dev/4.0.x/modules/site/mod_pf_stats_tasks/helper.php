<?php
/**
* @package   Projectfork Task Statistics
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


abstract class modPFstatsTasksHelper
{
	public static function getProject()
	{
	    $item = new stdClass();

        $item->id    = ProjectforkHelper::getActiveProjectId();
        $item->title = ProjectforkHelper::getActiveProjectTitle();

		return $item;
	}


    public static function getStatsProject($id = 0, $archived = 0, $trashed = 0)
    {
        $complete = new stdClass();
        $complete->label = JText::_('MOD_PF_STATS_TASKS_COMPLETE');
        $complete->data  = self::getData($id, array('t.complete = 1'));

        $pending = new stdClass();
        $pending->label = JText::_('MOD_PF_STATS_TASKS_PENDING');
        $pending->data  = self::getData($id, array('t.complete = 0'));

        $archived = new stdClass();
        $archived->label = JText::_('MOD_PF_STATS_TASKS_ARCHIVED');
        $archived->data  = ($archived ? self::getData($id, array('t.state = 2')) : 0);

        $trashed = new stdClass();
        $trashed->label = JText::_('MOD_PF_STATS_TASKS_TRASHED');
        $trashed->data  = ($trashed  ? self::getData($id, array('t.state = -2')) : 0);

        if($complete->data > 0 || $pending->data > 0 || $archived->data > 0 || $trashed->data > 0) {
            $data = array($complete, $pending, $archived, $trashed);
        }
        else {
            $data = array();
        }

        return $data;
    }


    public static function getStatsUser($id = 0, $archived = 0, $trashed = 0)
    {
        $complete = new stdClass();
        $complete->label = JText::_('MOD_PF_STATS_TASKS_COMPLETE');
        $complete->data  = self::getData(0, array('t.complete = 1', 'a.user_id = '.$id));

        $pending = new stdClass();
        $pending->label = JText::_('MOD_PF_STATS_TASKS_PENDING');
        $pending->data  = self::getData(0, array('t.complete = 0', 'a.user_id = '.$id));

        $archived = new stdClass();
        $archived->label = JText::_('MOD_PF_STATS_TASKS_ARCHIVED');
        $archived->data  = ($archived ? self::getData(0, array('t.state = 2', 'a.user_id = '.$id)) : 0);

        $trashed = new stdClass();
        $trashed->label = JText::_('MOD_PF_STATS_TASKS_TRASHED');
        $trashed->data  = ($trashed  ? self::getData(0, array('t.state = -2', 'a.user_id = '.$id)) : 0);

        if($complete->data > 0 || $pending->data > 0 || $archived->data > 0 || $trashed->data > 0) {
            $data = array($complete, $pending, $archived, $trashed);
        }
        else {
            $data = array();
        }

        return $data;
    }


    protected function getData($id = 0, $filters = array())
    {
        $user  = JFactory::getUser();
        $db    = JFactory::getDbo();
		$query = $db->getQuery(true);

        $query->select('COUNT(t.id)')
              ->from('#__pf_tasks AS t');

        foreach($filters AS $filter)
        {
            if(strpos($filter, 'a.user_id') !== false) {
                $query->join('RIGHT', '#__pf_ref_users AS a ON a.item_id = t.id');
                $query->where('a.item_type = '.$db->quote('task'));
                $query->where($filter);
                $query->group('a.user_id');
            }
            else {
                $query->where($filter);
            }
        }

        if(!$user->authorise('core.admin')) {
		    $groups	= implode(',', $user->getAuthorisedViewLevels());
			$query->where('t.access IN ('.$groups.')');
		}

        $db->setQuery($query->__toString());
        $result = (int) $db->loadResult();

        return $result;
    }
}
