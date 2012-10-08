<?php
/**
* @package      Projectfork Task Distribution Statistics
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
abstract class modPFstatsDistHelper
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
     * @param     object     $params    The module params
     * @param     integer    $id        The project id
     *
     * @return    array      $data      The stats
     */
    public static function getStatsProject(&$params, $id = 0)
    {
        $user  = JFactory::getUser();
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Get Params
        $show_c = (int) $params->get('show_completed', 1);
        $show_u = (int) $params->get('show_unassigned', 1);
        $limit  = (int) $params->get('limit', 5);

        // Get the user task distribution
        $query->select('COUNT(a.user_id) AS data')
              ->from('#__pf_ref_users AS a')
              ->join('RIGHT', '#__pf_tasks AS t ON t.id = a.item_id')
              ->where('a.item_type = ' . $db->quote('task'));

        $query->select('u.name AS label')
              ->join('LEFT', '#__users AS u ON u.id = a.user_id');

        if ($id) {
            $query->where('t.project_id = ' . $id);
        }

        if (!$user->authorise('core.admin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('t.access IN (' . $groups . ')');
        }

        // Apply complete stage filter
        if ($show_c == 0) {
            $query->where('t.complete = 0');
        }

        $query->group('a.user_id');
        $query->order('data', 'desc');

        $db->setQuery((string) $query, 0, $limit);
        $data = (array) $db->loadObjectList();


        // Find unassigned tasks if enabled
        $unassigned = 0;
        if ($show_u) {
            // Count total amount of tasks
            $query->clear();
            $query->select('COUNT(a.id)')
                  ->from('#__pf_tasks AS a');

            if ($id) {
                $query->where('a.project_id = ' . $id);
            }

            if (!$user->authorise('core.admin')) {
                $groups = implode(',', $user->getAuthorisedViewLevels());
                $query->where('a.access IN (' . $groups . ')');
            }

            // Apply complete stage filter
            if ($show_c == 0) {
                $query->where('a.complete = 0');
            }

            $db->setQuery((string) $query);
            $total = (int) $db->loadResult();


            // Count assigned tasks
            $query->clear();
            $query->select('COUNT(DISTINCT a.item_id)')
                  ->from('#__pf_ref_users AS a')
                  ->join('RIGHT', '#__pf_tasks AS t ON t.id = a.item_id')
                  ->where('a.item_type = ' . $db->quote('task'));

            if ($id) {
                $query->where('t.project_id = ' . $id);
            }

            // Apply complete stage filter
            if ($show_c == 0) {
                $query->where('t.complete = 0');
            }

            if (!$user->authorise('core.admin')) {
                $groups    = implode(',', $user->getAuthorisedViewLevels());
                $query->where('t.access IN (' . $groups . ')');
            }

            $db->setQuery((string) $query);
            $assigned = (int) $db->loadResult();


            // Calculate the amount of unassigned tasks
            $unassigned = $total - $assigned;

            if ($unassigned) {
                $obj_unassigned = new stdclass();
                $obj_unassigned->data  = $unassigned;
                $obj_unassigned->label = JText::_('MOD_PF_STATS_DIST_UNASSIGNED');

                $data[] = $obj_unassigned;
            }
        }


        // Data values must be of type integer!
        foreach($data AS $i => $item)
        {
            $data[$i]->data = (int) $item->data;
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
        $user  = JFactory::getUser((int) $id);
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);
        $data  = array();

        // Get Params
        $show_c = (int) $params->get('show_completed', 1);
        $show_u = (int) $params->get('show_unassigned', 1);
        $limit  = (int) $params->get('limit', 5);

        // Get the other users task distribution
        $query->select('COUNT(a.user_id) AS data')
              ->from('#__pf_ref_users AS a')
              ->join('RIGHT', '#__pf_tasks AS t ON t.id = a.item_id')
              ->where('a.item_type = ' . $db->quote('task'));

        if (!$user->authorise('core.admin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('t.access IN (' . $groups . ')');
        }

        // Apply complete stage filter
        if ($show_c == 0) {
            $query->where('t.complete = 0');
        }

        $query->group('a.user_id');

        $db->setQuery((string) $query);

        $item = new stdClass();
        $item->data  = (int) $db->loadResult();
        $item->label = JText::_('MOD_PF_STATS_DIST_OTHER_USERS');

        $data[] = $item;


        // Get the current user task distribution
        $query->clear();
        $query->select('COUNT(a.user_id) AS data, u.name AS label')
              ->from('#__pf_ref_users AS a')
              ->join('RIGHT', '#__pf_tasks AS t ON t.id = a.item_id')
              ->where('a.item_type = ' . $db->quote('task'))
              ->join('RIGHT', '#__users AS u ON u.id = '.(int) $id);

        if (!$user->authorise('core.admin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('t.access IN (' . $groups.')');
        }

        // Apply complete stage filter
        if ($show_c == 0) {
            $query->where('t.complete = 0');
        }

        $query->where('a.user_id = '. (int) $id)
              ->group('a.user_id');

        $db->setQuery((string) $query);
        $item = $db->loadObject();

        if (is_null($item)) {
            $item = new stdClass();
            $item->data  = 0;
            $item->label = $user->get('name');
        }

        $data[] = $item;


        // Find unassigned tasks if enabled
        $unassigned = 0;
        if ($show_u) {
            // Count total amount of tasks
            $query->clear();
            $query->select('COUNT(a.id)')
                  ->from('#__pf_tasks AS a');

            if (!$user->authorise('core.admin')) {
                $groups = implode(',', $user->getAuthorisedViewLevels());
                $query->where('a.access IN (' . $groups . ')');
            }

            // Apply complete stage filter
            if ($show_c == 0) {
                $query->where('a.complete = 0');
            }

            $db->setQuery((string) $query);
            $total = (int) $db->loadResult();


            // Count assigned tasks
            $query = $db->getQuery(true);

            $query->select('COUNT(DISTINCT a.item_id)')
                  ->from('#__pf_ref_users AS a')
                  ->join('RIGHT', '#__pf_tasks AS t ON t.id = a.item_id')
                  ->where('a.item_type = ' . $db->quote('task'));

            if (!$user->authorise('core.admin')) {
                $groups = implode(',', $user->getAuthorisedViewLevels());
                $query->where('t.access IN (' . $groups . ')');
            }

            // Apply complete stage filter
            if ($show_c == 0) {
                $query->where('t.complete = 0');
            }

            $db->setQuery((string) $query);
            $assigned = (int) $db->loadResult();

            // Calculate the amount of unassigned tasks
            $unassigned = $total - $assigned;

            if ($unassigned) {
                $obj_unassigned = new stdclass();
                $obj_unassigned->data  = $unassigned;
                $obj_unassigned->label = JText::_('MOD_PF_STATS_DIST_UNASSIGNED');

                $data[] = $obj_unassigned;
            }
        }


        // Data values must be of type integer!
        foreach($data AS $i => $item)
        {
            $data[$i]->data = (int) $item->data;
        }

        return $data;
    }
}
