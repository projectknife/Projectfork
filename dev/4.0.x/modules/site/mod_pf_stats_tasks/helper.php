<?php
/**
* @package      Projectfork Task Statistics
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
abstract class modPFstatsTasksHelper
{
    /**
     * Method to get the current project id and title
     *
     * @return    object    $item    The project data
     */
    public static function getProject()
    {
        $item = new stdClass();

        $item->id    = ProjectforkHelper::getActiveProjectId();
        $item->title = ProjectforkHelper::getActiveProjectTitle();

        return $item;
    }


    /**
     * Method to get the project statistics
     *
     * @param     integer    $id          The project id
     * @param     integer    $archived    Include archived tasks
     * @param     integer    $trashed     Include trashed tasks
     *
     * @return    array      $data        The stats
     */
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

        if ($complete->data > 0 || $pending->data > 0 || $archived->data > 0 || $trashed->data > 0) {
            $data = array($complete, $pending, $archived, $trashed);
        }
        else {
            $data = array();
        }

        return $data;
    }


    /**
     * Method to get the user statistics
     *
     * @param     integer    $id          The user id
     * @param     integer    $archived    Include archived tasks
     * @param     integer    $trashed     Include trashed tasks
     *
     * @return    array      $data        The stats
     */
    public static function getStatsUser($id = 0, $archived = 0, $trashed = 0)
    {
        $complete = new stdClass();
        $complete->label = JText::_('MOD_PF_STATS_TASKS_COMPLETE');
        $complete->data  = self::getData(0, array('t.complete = 1', 'a.user_id = ' . $id));

        $pending = new stdClass();
        $pending->label = JText::_('MOD_PF_STATS_TASKS_PENDING');
        $pending->data  = self::getData(0, array('t.complete = 0', 'a.user_id = ' . $id));

        $archived = new stdClass();
        $archived->label = JText::_('MOD_PF_STATS_TASKS_ARCHIVED');
        $archived->data  = ($archived ? self::getData(0, array('t.state = 2', 'a.user_id = ' . $id)) : 0);

        $trashed = new stdClass();
        $trashed->label = JText::_('MOD_PF_STATS_TASKS_TRASHED');
        $trashed->data  = ($trashed  ? self::getData(0, array('t.state = -2', 'a.user_id = ' . $id)) : 0);

        if ($complete->data > 0 || $pending->data > 0 || $archived->data > 0 || $trashed->data > 0) {
            $data = array($complete, $pending, $archived, $trashed);
        }
        else {
            $data = array();
        }

        return $data;
    }


    /**
     * Method to get the stats data
     *
     * @param     integer    $id         The project or user id
     * @param     array      $filters    Optional query filters
     *
     * @return    integer    $result     The stats count
     */
    protected function getData($id = 0, $filters = array())
    {
        $user  = JFactory::getUser();
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('COUNT(t.id)')
              ->from('#__pf_tasks AS t');

        foreach($filters AS $filter)
        {
            if (strpos($filter, 'a.user_id') !== false) {
                $query->join('RIGHT', '#__pf_ref_users AS a ON a.item_id = t.id')
                      ->where('a.item_type = ' . $db->quote('task'))
                      ->where($filter)
                      ->group('a.user_id');
            }
            else {
                $query->where($filter);
            }
        }

        if (!$user->authorise('core.admin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('t.access IN (' . $groups . ')');
        }

        $db->setQuery((string) $query);
        $result = (int) $db->loadResult();

        return $result;
    }
}
