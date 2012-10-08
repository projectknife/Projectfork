<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.modellist');


/**
 * Methods supporting a list of task records.
 *
 */
class ProjectforkModelTasks extends JModelList
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
                'a.id', 'a.project_id', 'project_title',
                'a.list_id', 'tasklist_title', 'tasklist',
                'a.milestone_id', 'milestone_title',
                'a.title', 'a.description', 'a.alias',
                'a.created', 'a.created_by', 'a.modified',
                'a.modified_by', 'a.checked_out',
                'a.checked_out_time', 'a.attribs',
                'a.access', 'access_level', 'a.state',
                'a.priority', 'a.complete', 'a.start_date',
                'a.end_date', 'a.ordering', 'a.parentid',
                'assigned_id'
            );
        }

        parent::__construct($config);
    }


    /**
     * Method to auto-populate the model state.
     * Note: Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState($ordering = null, $direction = null)
    {
        // Initialise variables.
        $app = JFactory::getApplication();

        // Adjust the context to support modal layouts.
        if ($layout = JRequest::getVar('layout')) $this->context .= '.' . $layout;

        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $author_id = $app->getUserStateFromRequest($this->context . '.filter.author_id', 'filter_author_id');
        $this->setState('filter.author_id', $author_id);

        $assigned = $this->getUserStateFromRequest($this->context . '.filter.assigned_id', 'filter_assigned_id', '');
        $this->setState('filter.assigned_id', $assigned);

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        $access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', '');
        $this->setState('filter.access', $access);

        $task_list = $this->getUserStateFromRequest($this->context . '.filter.tasklist', 'filter_tasklist', '');
        $this->setState('filter.tasklist', $task_list);

        $milestone = $this->getUserStateFromRequest($this->context . '.filter.milestone', 'filter_milestone', '');
        $this->setState('filter.milestone', $milestone);

        $project = ProjectforkHelper::getActiveProjectId('filter_project');
        $this->setState('filter.project', $project);

        if (!$project) {
            $this->setState('filter.author_id', '');
            $this->setState('filter.assigned_id', '');
            $this->setState('filter.tasklist', '');
            $this->setState('filter.milestone', '');
        }

        // List state information.
        parent::populateState('a.ordering', 'asc');
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
        $db	   = $this->getDbo();
        $query = $db->getQuery(true);
        $user  = JFactory::getUser();

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.project_id, a.list_id, a.milestone_id, a.catid, a.title, '
                . 'a.description, a.alias, a.checked_out, '
                . 'a.checked_out_time, a.state, a.access, a.created, a.created_by, '
                . 'a.start_date, a.end_date, a.ordering'
            )
        );
        $query->from('#__pf_tasks AS a');

        // Join over the users for the checked out user.
        $query->select('uc.name AS editor')
              ->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

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

        // Implement View Level Access
        if (!$user->authorise('core.admin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups . ')');
        }

        // Filter by project
        $project = $this->getState('filter.project');
        if (is_numeric($project) && $project != 0) {
            $query->where('a.project_id = ' . (int) $project);
        }

        // Filter by published state
        $published = $this->getState('filter.published');
        if (is_numeric($published)) {
            $query->where('a.state = ' . (int) $published);
        }
        elseif ($published === '') {
            $query->where('(a.state = 0 OR a.state = 1)');
        }

        // Filter by access level.
        if ($access = $this->getState('filter.access')) {
            $query->where('a.access = ' . (int) $access);
        }

        // Filter by task list
        $task_list = $this->getState('filter.tasklist');
        if (is_numeric($task_list)) {
            $query->where('a.list_id = ' . (int) $task_list);
        }

        // Filter by milestone
        $milestone = $this->getState('filter.milestone');
        if (is_numeric($milestone)) {
            $query->where('a.milestone_id = ' . (int) $milestone);
        }

        // Filter by author
        $author_id = $this->getState('filter.author_id');
        if (is_numeric($author_id)) {
            $type = $this->getState('filter.author_id.include', true) ? '= ' : '<>';
            $query->where('a.created_by ' . $type . (int) $author_id);
        }

        // Filter by assigned user
        $assigned = $this->getState('filter.assigned_id');
        if (is_numeric($assigned)) {
            $query->join('INNER', '#__pf_ref_users AS ru ON (ru.item_type = ' .
                                   $db->quote('task') . ' AND ru.item_id = a.id)');
            $query->where('ru.user_id = '.(int)$assigned);
        }

        // Filter by search in title.
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            }
            elseif (stripos($search, 'manager:') === 0) {
                $search = $db->Quote('%' . $db->escape(substr($search, 7), true) . '%');
                $query->where('(ua.name LIKE ' . $search . ' OR ua.username LIKE ' . $search . ')');
            }
            else {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');
                $query->where('(a.title LIKE ' . $search.' OR a.alias LIKE ' . $search . ')');
            }
        }

        // Add the list ordering clause.
        $order_col = $this->state->get('list.ordering', 'a.ordering');
        $order_dir = $this->state->get('list.direction', 'asc');

        if ($order_col == 'a.ordering') {
            $order_col = 'p.title, m.title, tl.title ' . $order_dir . ', ' . $order_col;
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

        $query->order($db->escape($order_col . ' ' . $order_dir));

        return $query;
    }


    /**
     * Build a list of project authors
     *
     * @return    jdatabasequery
     */
    public function getAuthors()
    {
        // Load only if project filter is set
        $project = (int) $this->getState('filter.project');

        if ($project <= 0) {
            return array();
        }

        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        // Construct the query
        $query->select('u.id AS value, u.name AS text')
              ->from('#__users AS u')
              ->join('INNER', '#__pf_tasks AS a ON a.created_by = u.id')
              ->where('a.project_id = ' . $db->quote($project))
              ->group('u.id')
              ->order('u.name');

        $db->setQuery((string) $query);

        // Return the result
        return $db->loadObjectList();
    }


    /**
     * Build a list of milestones
     *
     * @return    jdatabasequery
     */
    public function getMilestones()
    {
        // Load only if project filter is set
        $project = (int) $this->getState('filter.project');

        if ($project <= 0) {
            return array();
        }

        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        // Construct the query
        $query->select('m.id AS value, m.title AS text')
              ->from('#__pf_milestones AS m')
              ->join('INNER', '#__pf_tasks AS a ON a.milestone_id = m.id')
              ->where('a.project_id = ' . $db->quote($project))
              ->group('m.id')
              ->order('m.title');

        $db->setQuery((string) $query);

        // Return the result
        return $db->loadObjectList();
    }


    /**
     * Build a list of task lists
     *
     * @return    jdatabasequery
     */
    public function getTaskLists()
    {
        // Load only if project filter is set
        $project = (int) $this->getState('filter.project');

        if ($project <= 0) {
            return array();
        }

        // Create a new query object.
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        // Construct the query
        $query->select('t.id AS value, t.title AS text')
              ->from('#__pf_task_lists AS t')
              ->join('INNER', '#__pf_tasks AS a ON a.list_id = t.id')
              ->where('a.project_id = ' . $db->quote($project))
              ->group('t.id')
              ->order('t.title');

        $db->setQuery((string) $query);

        // Return the result
        return $db->loadObjectList();
    }


    /**
     * Build a list of assigned users
     *
     * @return    jdatabasequery
     */
    public function getAssignedUsers()
    {
        // Load only if project filter is set
        $project = (int) $this->getState('filter.project');

        if ($project <= 0) {
            return array();
        }

        // Create a new query object.
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        // Construct the query
        $query->select('u.id AS value, u.name AS text')
              ->from('#__users AS u')
              ->join('INNER', '#__pf_ref_users AS a ON a.user_id = u.id')
              ->join('INNER', '#__pf_tasks AS t ON a.id = a.item_id')
              ->where('a.item_type = ' . $db->quote('task'))
              ->where('t.project_id = ' . $db->quote($project))
              ->group('u.id')
              ->order('u.name');

        $db->setQuery((string) $query);

        // Return the result
        return $db->loadObjectList();
    }


    /**
     * Method to get a list of tasks.
     * Overridden to add a check for access levels.
     *
     * @return    mixed    An array of data items on success, false on failure.
     */
    public function getItems()
    {
        $items = parent::getItems();
        $app   = JFactory::getApplication();

        // Get the assigned users for each item
        $ref = JModelLegacy::getInstance('UserRefs', 'ProjectforkModel');

        for ($x = 0, $count = count($items); $x < $count; $x++)
        {
            $items[$x]->users = $ref->getItems('task', $items[$x]->id);
        }

        return $items;
    }
}
