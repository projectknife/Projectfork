<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
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

jimport('joomla.application.component.helper');


/**
 * Projectfork Component Route Helper
 *
 * @static
 */
abstract class ProjectforkHelperRoute
{
	protected static $lookup;


    public static function getDashboardRoute($project = '')
    {
        if($project) {
            $link = 'index.php?option=com_projectfork&view=dashboard&id='.$project;
        }
        else {
            $link = 'index.php?option=com_projectfork&view=dashboard';
        }

        $needles = array('id'  => array((int) $project));

        if ($item = self::_findItem($needles, 'dashboard')) {
			$link .= '&Itemid='.$item;
		}
		elseif ($item = self::_findItem(null, 'dashboard')) {
			$link .= '&Itemid='.$item;
		}

		return $link;
    }


    public static function getMilestonesRoute($project = '')
    {
        $link = 'index.php?option=com_projectfork&view=milestones&filter_project='.$project;

        $needles = array('filter_project'  => array((int) $project));

        if ($item = self::_findItem($needles, 'milestones')) {
			$link .= '&Itemid='.$item;
		}
		elseif ($item = self::_findItem(null, 'milestones')) {
			$link .= '&Itemid='.$item;
		}

		return $link;
    }


    public static function getTaskListsRoute($project = '', $milestone = '')
    {
        $link  = 'index.php?option=com_projectfork&view=tasklists';
        $link .= '&filter_project='.$project;
        $link .= '&filter_milestone='.$milestone;

        $needles = array('filter_project'   => array((int) $project),
                         'filter_milestone' => array((int) $milestone)
                        );

        if ($item = self::_findItem($needles, 'tasklists')) {
			$link .= '&Itemid='.$item;
		}
		elseif ($item = self::_findItem(null, 'tasklists')) {
			$link .= '&Itemid='.$item;
		}

		return $link;
    }


    public static function getTasksRoute($project = '', $milestone = '', $list = '')
    {
        $link  = 'index.php?option=com_projectfork&view=tasks';
        $link .= '&filter_project='.$project;
        $link .= '&filter_milestone='.$milestone;
        $link .= '&filter_tasklist='.$list;

        $needles = array('filter_project'   => array((int) $project),
                         'filter_milestone' => array((int) $milestone),
                         'filter_tasklist'  => array((int) $list)
                        );

        if ($item = self::_findItem($needles, 'tasks')) {
			$link .= '&Itemid='.$item;
		}
		elseif ($item = self::_findItem(null, 'tasks')) {
			$link .= '&Itemid='.$item;
		}

		return $link;
    }


	protected static function _findItem($needles = null, $com_view = null)
	{
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu('site');

		// Prepare the reverse lookup array.
		if (self::$lookup === null)
		{
			self::$lookup = array();

			$component	= JComponentHelper::getComponent('com_projectfork');
			$items		= $menus->getItems('component_id', $component->id);
			foreach ($items as $item)
			{
				if (isset($item->query) && isset($item->query['view']))
				{
					$view = $item->query['view'];
					if (!isset(self::$lookup[$view])) {
						self::$lookup[$view] = array();

					}

					if (isset($item->query['id'])) {
						self::$lookup[$view][$item->query['id']] = $item->id;
					}
                    else {
                        self::$lookup[$view][0] = $item->id;
                    }
				}
			}
		}

		if ($needles)
		{
			foreach ($needles as $view => $ids)
			{
				if (isset(self::$lookup[$view]))
				{
					foreach($ids as $id)
					{
						if (isset(self::$lookup[$view][(int)$id])) {
							return self::$lookup[$view][(int)$id];
						}
					}
				}
			}
		}
		else
		{
			$active = $menus->getActive();
			if ($active && $active->component == 'com_projectfork') {
				if($com_view) {
                    if (isset(self::$lookup[$com_view][0])) return self::$lookup[$com_view][0];
                }

                return $active->id;
			}
            else {
                if($com_view) {
                    if (isset(self::$lookup[$com_view][0])) return self::$lookup[$com_view][0];
                }
            }
		}

		return null;
	}
}
