<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pftasks
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.modellist');


/**
 * Methods supporting a list of task records.
 *
 */
class PFtasksModelTasks extends JModelList
{
    /**
     * Constructor
     *
     * @param    array    An optional associative array of configuration settings.
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'a.id',
                'a.project_id', 'project_title',
                'a.list_id', 'tasklist_title',
                'a.milestone_id', 'milestone_title',
                'a.title',
                'a.alias',
                'a.created',
                'a.created_by', 'author_name',
                'a.modified',
                'a.modified_by', 'editor',
                'a.checked_out',
                'a.checked_out_time',
                'a.access', 'access_level',
                'a.state',
                'a.priority',
                'a.complete',
                'a.start_date',
                'a.end_date',
                'a.ordering',
                'a.assigned_id'
            );
        }

        parent::__construct($config);
    }


    /**
     * Build a list of project authors
     *
     * @return    array
     */
    public function getAuthors()
    {
        // Load only if project filter is set
        $project = (int) $this->getState('filter.project');

        if ($project <= 0) {
            // Make an exception if we are logged in...
            $user = JFactory::getUser();

            if ($user->id) {
                $item = new stdClass();
                $item->value = $user->id;
                $item->text  = $user->name;

                $items = array($item);

                return $items;
            }

            return array();
        }

        $query = $this->_db->getQuery(true);

        // Construct the query
        $query->select('u.id AS value, u.name AS text')
              ->from('#__users AS u')
              ->join('INNER', '#__pf_tasks AS a ON a.created_by = u.id')
              ->where('a.project_id = ' . $this->_db->quote($project))
              ->group('u.id')
              ->order('u.name');

        // Return the result
        $this->_db->setQuery($query, 0, 50);
        return $this->_db->loadObjectList();
    }


    /**
     * Build a list of milestones
     *
     * @return    array
     */
    public function getMilestones()
    {
        $project = (int) $this->getState('filter.project');
        $query   = $this->_db->getQuery(true);

        // Load only if project filter is set
        if ($project <= 0) return array();

        // Construct the query
        $query->select('m.id AS value, m.title AS text')
              ->from('#__pf_milestones AS m')
              ->join('INNER', '#__pf_tasks AS a ON a.milestone_id = m.id')
              ->where('a.project_id = ' . $this->_db->quote($project))
              ->group('m.id')
              ->order('m.title');

        // Return the result
        $this->_db->setQuery($query);
        return $this->_db->loadObjectList();
    }


    /**
     * Build a list of task lists
     *
     * @return    array
     */
    public function getTaskLists()
    {
        // Load only if project filter is set
        $project = (int) $this->getState('filter.project');

        if ($project <= 0) return array();

        // Create a new query object.
        $query = $this->_db->getQuery(true);

        // Construct the query
        $query->select('t.id AS value, t.title AS text')
              ->from('#__pf_task_lists AS t')
              ->join('INNER', '#__pf_tasks AS a ON a.list_id = t.id')
              ->where('a.project_id = ' . $this->_db->quote($project))
              ->group('t.id')
              ->order('t.title');

        // Return the result
        $this->_db->setQuery($query);
        return (array) $this->_db->loadObjectList();
    }


    /**
     * Build a list of assigned users
     *
     * @return    array
     */
    public function getAssignedUsers()
    {
        // Load only if project filter is set
        $project = (int) $this->getState('filter.project');

        if ($project <= 0) {
            // Make an exception if we are logged in...
            $user = JFactory::getUser();

            if ($user->id) {
                $item = new stdClass();
                $item->value = $user->id;
                $item->text  = $user->name;

                $items = array($item);

                return $items;
            }

            return array();
        }

        // Create a new query object.
        $query = $this->_db->getQuery(true);

        // Construct the query
        $query->select('u.id AS value, u.name AS text')
              ->from('#__users AS u')
              ->join('INNER', '#__pf_ref_users AS a ON a.user_id = u.id')
              ->join('INNER', '#__pf_tasks AS t ON a.id = a.item_id')
              ->where('a.item_type = ' . $this->_db->quote('com_pftasks.task'))
              ->where('t.project_id = ' . $this->_db->quote($project))
              ->group('u.id')
              ->order('u.name');

        // Return the result
        $this->_db->setQuery($query);
        return (array) $this->_db->loadObjectList();
    }


    /**
     * Method to get a list of tasks.
     *
     * @return    mixed    An array of data items on success, false on failure.
     */
    public function getItems()
    {
        $items = parent::getItems();
        $count = count($items);
        $ref  = JModelLegacy::getInstance('UserRefs', 'PFusersModel');

        // Get the assigned users for each task
        for ($i = 0; $i < $count; $i++)
        {
            $items[$i]->users = $ref->getItems('task', $items[$i]->id);
        }

        return $items;
    }


    /**
     * Method to auto-populate the model state.
     * Note: Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState($ordering = 'a.ordering', $direction = 'asc')
    {
        // Initialise variables.
        $app  = JFactory::getApplication();
        $user = JFactory::getUser();

        // Adjust the context to support modal layouts.
        if ($layout = JRequest::getVar('layout')) $this->context .= '.' . $layout;

        // Filter - Search
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        // Filter - State
        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        // Filter - Access
        $access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', '');
        $this->setState('filter.access', $access);

        // Filter - Project
        $project = PFApplicationHelper::getActiveProjectId('filter_project');
        $this->setState('filter.project', $project);

        // Filter - Author
        $author_id = $app->getUserStateFromRequest($this->context . '.filter.author_id', 'filter_author_id');
        $this->setState('filter.author_id', $author_id);

        // Filter - Assigned User
        $assigned = $app->getUserStateFromRequest($this->context . '.filter.assigned_id', 'filter_assigned_id', '');
        $this->setState('filter.assigned_id', $assigned);

        // Filter - List
        $task_list = $app->getUserStateFromRequest($this->context . '.filter.tasklist', 'filter_tasklist', '');
        $this->setState('filter.tasklist', $task_list);

        // Filter - Milestone
        $milestone = $app->getUserStateFromRequest($this->context . '.filter.milestone', 'filter_milestone', '');
        $this->setState('filter.milestone', $milestone);

        // Filter - Priority
        $priority = $app->getUserStateFromRequest($this->context . '.filter.priority', 'filter_priority', '');
        $this->setState('filter.priority', $priority);

        // Filter - Complete
        $complete = $app->getUserStateFromRequest($this->context . '.filter.complete', 'filter_complete', '');
        $this->setState('filter.complete', $complete);

        // Do not allow these filters if no project is selected
        if (!$project) {
            $this->setState('filter.tasklist', '');
            $this->setState('filter.milestone', '');

            if ($author_id != $user->id) {
                $this->setState('filter.author_id', '');
            }

            if ($assigned != $user->id) {
                $this->setState('filter.assigned_id', '');
            }
        }

        // List state information.
        parent::populateState($ordering, $direction);
    }


    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param     string    $id    A prefix for the store id.
     * @return    string           A store id.
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.access');
        $id .= ':' . $this->getState('filter.author_id');
        $id .= ':' . $this->getState('filter.assigned_id');
        $id .= ':' . $this->getState('filter.project');
        $id .= ':' . $this->getState('filter.tasklist');
        $id .= ':' . $this->getState('filter.milestone');
        $id .= ':' . $this->getState('filter.priority');
        $id .= ':' . $this->getState('filter.complete');

        return parent::getStoreId($id);
    }


    /**
     * Build an SQL query to load the list data.
     *
     * @return    jdatabasequery
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $query = $this->_db->getQuery(true);
        $user  = JFactory::getUser();

        // Get possible filters
        $filter_state    = $this->getState('filter.published');
        $filter_project  = $this->getState('filter.project');
        $filter_ms       = $this->getState('filter.milestone');
        $filter_list     = $this->getState('filter.tasklist');
        $filter_access   = $this->getState('filter.access');
        $filter_author   = $this->getState('filter.author_id');
        $filter_assign   = $this->getState('filter.assigned_id');
        $filter_search   = $this->getState('filter.search');
        $filter_priority = $this->getState('filter.priority');
        $filter_complete = $this->getState('filter.complete');

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.project_id, a.list_id, a.milestone_id, a.title, '
                . 'a.alias, a.checked_out, a.checked_out_time, a.state, '
                . 'a.access, a.created, a.created_by, a.start_date, a.end_date, '
                . 'a.ordering'
            )
        );

        $query->from('#__pf_tasks AS a');

        // Join over the users for the checked out user.
        $query->select('uc.name AS editor')
              ->join('LEFT', '#__users AS uc ON uc.id = a.checked_out');

        // Join over the asset groups.
        $query->select('ag.title AS access_level')
              ->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

        // Join over the users for the author.
        $query->select('ua.name AS author_name')
              ->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

        // Join over the projects for the project title.
        $query->select('p.title AS project_title')
              ->join('LEFT', '#__pf_projects AS p ON p.id = a.project_id');

        // Join over the task lists for the task list title.
        $query->select('tl.title AS tasklist_title')
              ->join('LEFT', '#__pf_task_lists AS tl ON tl.id = a.list_id');

        // Join over the milestones for the milestone title.
        $query->select('m.title AS milestone_title')
              ->join('LEFT', '#__pf_milestones AS m ON m.id = a.milestone_id');

        // Filter by access level.
        if ($filter_access) {
            $query->where('a.access = ' . (int) $filter_access);
        }

        // Implement View Level Access
        if (!$user->authorise('core.admin')) {
            $levels = implode(',', $user->getAuthorisedViewLevels());

            $query->where('a.access IN (' . $levels . ')');
        }

        // Filter by published state
        if (is_numeric($filter_state)) {
            $query->where('a.state = ' . (int) $filter_state);
        }
        elseif ($filter_state === '') {
            $query->where('(a.state = 0 OR a.state = 1)');
        }

        // Filter by project
        if (is_numeric($filter_project) && $filter_project > 0) {
            $query->where('a.project_id = ' . (int) $filter_project);
        }

        // Filter by milestone
        if (is_numeric($filter_ms)) {
            $query->where('a.milestone_id = ' . (int) $filter_ms);
        }

        // Filter by task list
        if (is_numeric($filter_list)) {
            $query->where('a.list_id = ' . (int) $filter_list);
        }

        // Filter by priority
        if (is_numeric($filter_priority)) {
            $query->where('a.priority = ' . (int) $filter_priority);
        }

        // Filter by completition
        if (is_numeric($filter_complete)) {
            $query->where('a.complete = ' . (int) $filter_complete);
        }

        // Filter by author
        if (is_numeric($filter_author)) {
            $type = $this->getState('filter.author_id.include', true) ? '= ' : '<>';
            $query->where('a.created_by ' . $type . (int) $filter_author);
        }

        // Filter by assigned user
        if (is_numeric($filter_assign)) {
            $query->join('INNER',
                '#__pf_ref_users AS ru ON (ru.item_type = ' . $this->_db->quote('com_pftasks.task') . ' AND ru.item_id = a.id)'
            );

            $query->where('ru.user_id = '. (int) $filter_assign);
        }

        // Filter by search in title.
        if (!empty($filter_search)) {
            if (stripos($filter_search, 'id:') === 0) {
                $query->where('a.id = '. (int) substr($filter_search, 3));
            }
            elseif (stripos($filter_search, 'author:') === 0) {
                $search = $this->_db->quote('%' . $this->_db->escape(substr($filter_search, 7), true) . '%');
                $query->where('(ua.name LIKE ' . $search . ' OR ua.username LIKE ' . $search . ')');
            }
            else {
                $search = $this->_db->quote('%' . $this->_db->escape($filter_search, true) . '%');
                $query->where('(a.title LIKE ' . $search . ' OR a.alias LIKE ' . $search . ')');
            }
        }

        // Add the list ordering clause.
        $order_col = $this->state->get('list.ordering', 'a.ordering');
        $order_dir = $this->state->get('list.direction', 'asc');

        if ($order_col == 'a.ordering') {
            $order_col = 'p.title, tl.title ' . $order_dir . ', ' . $order_col;
        }
        if ($order_col == 'project_title') {
            $order_col = 'm.title, tl.title, a.title ' . $order_dir . ', p.title';
        }
        if ($order_col == 'milestone_title') {
            $order_col = 'p.title ' . $order_dir . ', m.title';
        }
        if ($order_col == 'tasklist_title') {
            $order_col = 'p.title, m.title ' . $order_dir . ', tl.title';
        }

        $query->order($this->_db->escape($order_col . ' ' . $order_dir));

        return $query;
    }
}
