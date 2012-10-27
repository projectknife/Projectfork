<?php
/**
 * @package      Projectfork
 * @subpackage   Timetracking
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.modellist');


/**
 * This models supports retrieving lists of time tracking records.
 *
 */
class PFtimeModelTimesheet extends JModelList
{

    /**
     * Constructor.
     *
     * @param    array          $config    An optional associative array of configuration settings.
     * @see      jcontroller
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'a.id', 'a.project_id', 'project_title',
                'a.task_id', 'task_title', 'a.description',
                'a.created', 'a.created_by', 'a.modified',
                'a.modified_by', 'a.checked_out',
                'a.checked_out_time', 'a.attribs',
                'a.access', 'access_level', 'a.state',
                'a.log_date', 'a.log_time', 'author_name'
            );
        }

        parent::__construct($config);
    }


    /**
     * Get the master query for retrieving a list of items subject to the model state.
     *
     * @return    jdatabasequery
     */
    public function getListQuery()
    {
        // Create a new query object.
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $user  = JFactory::getUser();

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.project_id, a.task_id, a.description, '
                . 'a.checked_out, a.checked_out_time, a.state, a.access, a.rate, a.billable,'
                . 'a.created, a.created_by, a.log_date, a.log_time, a.attribs'
            )
        );

        $query->from('#__pf_timesheet AS a');

        // Join over the users for the checked out user.
        $query->select('uc.name AS editor');
        $query->join('LEFT', '#__users AS uc ON uc.id = a.checked_out');

        // Join over the asset groups.
        $query->select('ag.title AS access_level');
        $query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

        // Join over the users for the author.
        $query->select('ua.name AS author_name');
        $query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

        // Join over the projects for the project title.
        $query->select('p.title AS project_title, p.alias AS project_alias');
        $query->join('LEFT', '#__pf_projects AS p ON p.id = a.project_id');

        // Join over the tasks for the task title.
        $query->select('t.title AS task_title, t.alias AS task_alias, t.estimate');
        $query->join('LEFT', '#__pf_tasks AS t ON t.id = a.task_id');

        // Join over the milestones for the milestone alias.
        $query->select('m.id AS milestone_id, m.alias AS milestone_alias');
        $query->join('LEFT', '#__pf_milestones AS m ON m.id = t.milestone_id');

        // Join over the task lists for the list alias.
        $query->select('l.id AS list_id, l.alias AS list_alias');
        $query->join('LEFT', '#__pf_task_lists AS l ON l.id = t.list_id');

        // Implement View Level Access
        if (!$user->authorise('core.admin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups . ')');
        }

        // Calculate billable amount
        $query->select('CASE WHEN (a.billable = 1 AND a.rate > 0 AND a.log_time > 0) '
                       . 'THEN ((a.log_time / 60) * (a.rate / 60)) '
                       . 'ELSE "0.00"'
                       . 'END AS billable_total');

        // Filter fields
        $filters = array();
        $filters['a.state']      = array('STATE',       $this->getState('filter.published'));
        $filters['a.project_id'] = array('INT-NOTZERO', $this->getState('filter.project'));
        $filters['a.task_id']    = array('INT-NOTZERO', $this->getState('filter.task'));
        $filters['a.access']     = array('INT-NOTZERO', $this->getState('filter.access'));
        $filters['a.created_by'] = array('INT-NOTZERO', $this->getState('filter.author'));
        $filters['t']            = array('SEARCH',      $this->getState('filter.search'));

        // Apply Filter
        PFQueryHelper::buildFilter($query, $filters);

        // Add the list ordering clause.
        $query->group('a.id');
        $query->order($this->getState('list.ordering', 'a.log_date').' ' . $this->getState('list.direction', 'DESC'));

        return $query;
    }


    /**
     * Method to get a list of items.
     * Overriden to inject convert the attribs field into a JParameter object.
     *
     * @return    mixed    $items    An array of objects on success, false on failure.
     */
    public function getItems()
    {
        $items = parent::getItems();

        // Get the global params
        $global_params = JComponentHelper::getParams('com_pftime', true);

        foreach ($items as $i => &$item)
        {
            // Convert the parameter fields into objects.
            $params = new JRegistry;
            $params->loadString($item->attribs);

            $items[$i]->params = clone $this->getState('params');

            // Create slugs
            $items[$i]->slug           = $items[$i]->id;
            $items[$i]->project_slug   = $items[$i]->project_alias ? ($items[$i]->project_id . ':' . $items[$i]->project_alias) : $items[$i]->project_id;
            $items[$i]->task_slug      = $items[$i]->task_alias ? ($items[$i]->task_id . ':' . $items[$i]->task_alias) : $items[$i]->task_id;
            $items[$i]->milestone_slug = $items[$i]->milestone_alias ? ($items[$i]->milestone_id . ':' . $items[$i]->milestone_alias) : $items[$i]->milestone_id;
            $items[$i]->list_slug      = $items[$i]->list_alias ? ($items[$i]->list_id . ':' . $items[$i]->list_alias) : $items[$i]->list_id;
        }

        return $items;
    }


    /**
     * Build a list of authors
     *
     * @return    jdatabasequery
     */
    public function getAuthors()
    {
        $db     = $this->getDbo();
        $query  = $db->getQuery(true);
        $user   = JFactory::getUser();
        $access = PFtimeHelper::getActions();

        // Return empty array if no project is select
        $project = (int) $this->getState('filter.project');
        if ($project < 0) {
            return array();
        }

        // Construct the query
        $query->select('u.id AS value, u.name AS text');
        $query->from('#__users AS u');
        $query->join('INNER', '#__pf_timesheet AS a ON a.created_by = u.id');

        // Implement View Level Access
        if (!$user->authorise('core.admin', 'com_pftime')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups . ')');
        }

        // Filter fields
        $filters = array();
        $filters['a.project_id'] = array('INT-NOTZERO', $this->getState('filter.project'));

        if (!$access->get('core.edit.state') && !$access->get('core.edit')) {
            $filters['a.state'] = array('STATE', '1');
        }

        // Apply Filter
        PFQueryHelper::buildFilter($query, $filters);

        // Group and order
        $query->group('u.id');
        $query->order('u.name ASC');

        // Get the results
        $db->setQuery((string) $query);
        $items = (array) $db->loadObjectList();

        // Return the items
        return $items;
    }


    /**
     * Build a list of tasks
     *
     * @return    jdatabasequery
     */
    public function getTasks()
    {
        $db     = $this->getDbo();
        $query  = $db->getQuery(true);
        $user   = JFactory::getUser();
        $access = PFtimeHelper::getActions();

        // Return empty array if no project is select
        $project = (int) $this->getState('filter.project');
        if ($project < 0) {
            return array();
        }

        // Construct the query
        $query->select('t.id AS value, t.title AS text');
        $query->from('#__pf_tasks AS t');
        $query->join('INNER', '#__pf_timesheet AS a ON a.task_id = t.id');

        // Implement View Level Access
        if (!$user->authorise('core.admin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('t.access IN (' . $groups . ')');
        }

        // Filter fields
        $filters = array();
        $filters['a.project_id'] = array('INT-NOTZERO', $this->getState('filter.project'));

        if (!$access->get('core.edit.state') && !$access->get('core.edit')) {
            $filters['a.state'] = array('STATE', '1');
        }

        // Apply Filter
        PFQueryHelper::buildFilter($query, $filters);

        // Group and order
        $query->group('t.id');
        $query->order('t.title ASC');

        // Get the results
        $db->setQuery((string) $query);
        $items = (array) $db->loadObjectList();

        // Return the items
        return $items;
    }


    /**
     * Gets the total time spent on a project
     *
     * @param     integer    $billable    0 = unbillable time, 1 = billable time
     * @return    integer    $sum
     */
    public function getProjectTime($billable = 0)
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        // Return 0 if no project is selected
        if ((int) $this->getState('filter.project') == 0) {
            return 0;
        }

        // Construct the query
        $query->select('SUM(a.log_time)')
              ->from('#__pf_timesheet AS a')
              ->where('a.billable = ' . (int) $billable)
              ->where('a.state = 1');

        $filters = array();
        $filters['a.project_id'] = array('INT-NOTZERO', $this->getState('filter.project'));

        // Apply Filter
        PFQueryHelper::buildFilter($query, $filters);

        // Get the result
        $db->setQuery((string) $query);
        $sum = (int) $db->loadResult();

        // Return the items
        return $sum;
    }


    /**
     * Gets the total billable time spent on a project
     *
     * @see getProjectTime
     */
    public function getBillableProjectTime()
    {
        return $this->getProjectTime(1);
    }


    /**
     * Gets the total unbillable time spent on a project
     *
     * @see getProjectTime
     */
    public function getUnbillableProjectTime()
    {
        return $this->getProjectTime(0);
    }


    /**
     * Gets the current cost of a project
     *
     * @return    integer    $sum
     */
    public function getProjectCost()
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        // Return 0 if no project is selected
        if ((int) $this->getState('filter.project') == 0) {
            return 0.00;
        }

        // Construct the query
        $query->select('SUM(a.log_time / 60) * (a.rate / 60)')
              ->from('#__pf_timesheet AS a')
              ->where('a.billable = 1')
              ->where('a.state = 1')
              ->where('a.rate > 0')
              ->where('a.log_time > 0');

        $filters = array();
        $filters['a.project_id'] = array('INT-NOTZERO', $this->getState('filter.project'));

        // Apply Filter
        PFQueryHelper::buildFilter($query, $filters);

        // Get the result
        $db->setQuery((string) $query);
        $sum = (int) $db->loadResult();

        // Return the items
        return $sum;
    }


    /**
     * Gets the estimated project completition time
     *
     * @return    integer    $sum
     */
    public function getProjectEstimatedTime()
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        // Return 0 if no project is selected
        if ((int) $this->getState('filter.project') == 0) {
            return 0.00;
        }

        // Construct the query
        $query->select('SUM(a.estimate)')
              ->from('#__pf_tasks AS a')
              ->where('a.state = 1');

        $filters = array();
        $filters['a.project_id'] = array('INT-NOTZERO', $this->getState('filter.project'));

        // Apply Filter
        PFQueryHelper::buildFilter($query, $filters);

        // Get the result
        $db->setQuery((string) $query);
        $sum = (int) $db->loadResult();

        // Return the items
        return $sum;
    }


    /**
     * Gets the estimated project cost
     *
     * @return    integer    $sum
     */
    public function getProjectEstimatedCost()
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        // Return 0 if no project is selected
        if ((int) $this->getState('filter.project') == 0) {
            return 0.00;
        }

        // Construct the query
        $query->select('SUM(a.estimate / 60) * (a.rate / 60)')
              ->from('#__pf_tasks AS a')
              ->where('a.state = 1')
              ->where('a.rate > 0')
              ->where('a.estimate > 0');

        $filters = array();
        $filters['a.project_id'] = array('INT-NOTZERO', $this->getState('filter.project'));

        // Apply Filter
        PFQueryHelper::buildFilter($query, $filters);

        // Get the result
        $db->setQuery((string) $query);
        $sum = (int) $db->loadResult();

        // Return the items
        return $sum;
    }


    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState($ordering = 'a.log_date', $direction = 'DESC')
    {
        $app    = JFactory::getApplication();
        $access = PFtimeHelper::getActions();

        // Adjust the context to support modal layouts.
        $layout = JRequest::getCmd('layout');

        // View Layout
        $this->setState('layout', $layout);
        if ($layout) $this->context .= '.' . $layout;

        // Params
        $value = $app->getParams();
        $this->setState('params', $value);

        // State
        $state = $app->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $state);

        // Filter on published for those who do not have edit or edit.state rights.
        if (!$access->get('time.edit.state') && !$access->get('time.edit')){
            $this->setState('filter.published', 1);
            $state = '';
        }

        // Filter - Search
        $search = JRequest::getString('filter_search', '');
        $this->setState('filter.search', $search);

        // Filter - Project
        // Filter - Project
        $project = PFApplicationHelper::getActiveProjectId('filter_project');
        $this->setState('filter.project', $project);

        // Filter - Task
        $task = $app->getUserStateFromRequest($this->context . '.filter.task', 'filter_task', '');
        $this->setState('filter.task', $task);

        // Filter - Author
        $author = $app->getUserStateFromRequest($this->context . '.filter.author', 'filter_author', '');
        $this->setState('filter.author', $author);

        // Do not allow some filters if no project is selected
        if (intval($project) == 0) {
            $this->setState('filter.author', '');
            $this->setState('filter.task', '');

            $author = '';
            $task   = '';
        }

        // Filter - Is set
        $this->setState('filter.isset',
            (is_numeric($state) || !empty($search) || is_numeric($author) ||
            is_numeric($task))
        );

        // Call parent method
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
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.project');
        $id .= ':' . $this->getState('filter.task');
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.author');

        return parent::getStoreId($id);
    }
}
