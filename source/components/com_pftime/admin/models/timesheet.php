<?php
/**
 * @package      Projectfork
 * @subpackage   Timetracking
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.modellist');


/**
 * Methods supporting a list of time tracking records.
 *
 */
class PFtimeModelTimesheet extends JModelList
{
    /**
     * Constructor
     *
     * @param     array    An optional associative array of configuration settings.
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'a.id',
                'a.project_id', 'project_title',
                'task_id',
                'a.task_id',
                'a.task_title',
                'a.created',
                'a.created_by', 'author_name',
                'a.modified',
                'a.modified_by', 'editor',
                'a.checked_out',
                'a.checked_out_time',
                'a.access', 'access_level',
                'a.state',
                'a.log_date',
                'a.log_time'
            );
        }

        parent::__construct($config);
    }


    /**
     * Build a list of authors
     *
     * @return    array
     */
    public function getAuthors()
    {
        $query   = $this->_db->getQuery(true);
        $project = (int) $this->getState('filter.project');

        // Load only if project filter is set
        if ($project <= 0) return array();

        // Construct the query
        $query->select('u.id AS value, u.name AS text')
              ->from('#__users AS u')
              ->join('INNER', '#__pf_timesheet AS a ON a.created_by = u.id')
              ->where('a.project_id = ' . $this->_db->quote($project))
              ->group('u.id')
              ->order('u.name');

        // Return the result
        $this->_db->setQuery($query, 0, 50);
        return (array) $this->_db->loadObjectList();
    }


    /**
     * Build a list of tasks
     *
     * @return    array
     */
    public function getTasks()
    {
        $query   = $this->_db->getQuery(true);
        $project = (int) $this->getState('filter.project');

        // Load only if project filter is set
        if ($project <= 0) return array();

        // Construct the query
        $query->select('t.id AS value, t.title AS text')
              ->from('#__pf_tasks AS t')
              ->join('INNER', '#__pf_timesheet AS a ON a.task_id = t.id')
              ->where('a.project_id = ' . $this->_db->quote($project))
              ->group('t.id')
              ->order('t.title');

        // Return the result
        $this->_db->setQuery($query, 0, 50);
        return (array) $this->_db->loadObjectList();
    }


    /**
     * Method to auto-populate the model state.
     * Note: Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState($ordering = 'a.log_date', $direction = 'desc')
    {
        // Initialise variables.
        $app = JFactory::getApplication();

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

        // Filter - Task
        $task = $app->getUserStateFromRequest($this->context . '.filter.task', 'filter_task', '');
        $this->setState('filter.task', $task);

        if (!$project) {
            $this->setState('filter.author_id', '');
            $this->setState('filter.task', '');
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
     *
     * @return    string           A store id.
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.access');
        $id .= ':' . $this->getState('filter.author_id');
        $id .= ':' . $this->getState('filter.project');
        $id .= ':' . $this->getState('filter.task');

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
        $filter_state   = $this->getState('filter.published');
        $filter_project = $this->getState('filter.project');
        $filter_task    = $this->getState('filter.task');
        $filter_access  = $this->getState('filter.access');
        $filter_author  = $this->getState('filter.author_id');
        $filter_search  = $this->getState('filter.search');

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.project_id, a.task_id, a.task_title, a.description, '
                . 'a.checked_out, a.checked_out_time, a.state, a.access, '
                . 'a.created, a.created_by, a.log_date, a.log_time'
            )
        );

        $query->from('#__pf_timesheet AS a');

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

        // Join over the tasks.
        $query->select('t.id AS task_exists')
              ->join('LEFT', '#__pf_tasks AS t ON t.id = a.task_id');

        // Filter by access level.
        if ($filter_access) {
            $query->where('a.access = ' . (int) $filter_access);
        }

        // Implement View Level Access
        if (!$user->authorise('core.admin', 'com_pftime')) {
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

        // Filter by task list
        if (is_numeric($filter_task)) {
            $query->where('a.task_id = ' . (int) $filter_task);
        }

        // Filter by author
        if (is_numeric($filter_author)) {
            $type = $this->getState('filter.author_id.include', true) ? '= ' : '<>';
            $query->where('a.created_by ' . $type . (int) $filter_author);
        }

        // Filter by search in title.
        if (!empty($filter_search)) {
            if (stripos($filter_search, 'id:') === 0) {
                $query->where('a.id = '. (int) substr($filter_search, 3));
            }
            elseif (stripos($filter_search, 'author:') === 0) {
                $search = $this->_db->quote($this->_db->escape(substr($filter_search, 7), true) . '%');
                $query->where('(ua.name LIKE ' . $search . ' OR ua.username LIKE ' . $search . ')');
            }
            else {
                $search = $this->_db->quote($this->_db->escape($filter_search, true) . '%');
                $query->where('(a.task_title LIKE ' . $search . ' OR a.description LIKE ' . $search . ')');
            }
        }

        // Add the list ordering clause.
        $order_col = $this->state->get('list.ordering', 'a.log_date');
        $order_dir = $this->state->get('list.direction', 'desc');

        $query->order($this->_db->escape($order_col . ' ' . $order_dir));

        // Group by
        $query->group('a.id');

        return $query;
    }
}
