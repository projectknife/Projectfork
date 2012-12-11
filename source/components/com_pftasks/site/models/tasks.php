<?php
/**
 * @package      Projectfork
 * @subpackage   Tasks
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.modellist');
jimport('joomla.application.component.helper');


/**
 * This models supports retrieving lists of tasks.
 *
 */
class PFtasksModelTasks extends JModelList
{

    /**
     * Constructor.
     *
     * @param    array          An optional associative array of configuration settings.
     * @see      jcontroller
     */
    public function __construct($config = array())
    {
        // Set field filter
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'a.id', 'a.title', 'a.created',
                'a.modified', 'a.checked_out',
                'a.checked_out_time', 'a.state',
                'a.priority', 'a.complete', 'a.start_date',
                'a.end_date', 'a.ordering', 'a.parentid', 'author_name', 'editor',
                'access_level', 'project_title', 'milestone_title',
                'list_title', 'ordering', 'assigned'
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

        $user   = JFactory::getUser();
        $access = PFtasksHelper::getActions();

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.project_id, a.list_id, a.milestone_id, a.title, '
                . 'a.description, a.alias, a.checked_out, a.attribs, a.priority, '
                . 'a.checked_out_time, a.state, a.access, a.created, a.created_by, '
                . 'a.start_date, a.end_date, a.ordering, a.complete'
            )
        );

        $query->from('#__pf_tasks AS a');

        // Join over the users for the checked out user.
        $query->select('uc.name AS editor');
        $query->join('LEFT', '#__users AS uc ON uc.id = a.checked_out');

        // Join over the asset groups.
        $query->select('ag.title AS access_level');
        $query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

        // Join over the users for the author.
        $query->select('ua.name AS author_name, ua.email AS author_email');
        $query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

        // Join over the projects for the project title and alias.
        $query->select('p.title AS project_title, p.alias AS project_alias');
        $query->join('LEFT', '#__pf_projects AS p ON p.id = a.project_id');

        // Join over the task lists for the task list title, description, checked out, author
        $query->select('tl.title AS list_title, tl.alias AS list_alias, tl.description AS list_description, '
                       . 'tl.checked_out AS checked_out_list, tl.created_by AS list_created_by');
        $query->join('LEFT', '#__pf_task_lists AS tl ON tl.id = a.list_id');

        // Join over the milestones for the milestone title and alias.
        $query->select('m.title AS milestone_title, m.alias AS milestone_alias');
        $query->join('LEFT', '#__pf_milestones AS m ON m.id = a.milestone_id');

        // Join over the label refs for label count
        $query->select('COUNT(DISTINCT lbl.id) AS label_count');
        $query->join('LEFT', '#__pf_ref_labels AS lbl ON (lbl.item_id = a.id AND lbl.item_type = ' . $db->quote('com_pftasks.task') . ')');

        // Join over the observer table for email notification status
        if ($user->get('id') > 0) {
            $query->select('COUNT(DISTINCT obs.user_id) AS watching');
            $query->join('LEFT', '#__pf_ref_observer AS obs ON (obs.item_type = '
                               . $db->quote('com_pftasks.task') . ' AND obs.item_id = a.id AND obs.user_id = '
                               . $db->quote($user->get('id')) . ')'
                        );
        }

        // Join over the attachments for attachment count
        $query->select('COUNT(DISTINCT at.id) AS attachments');
        $query->join('LEFT', '#__pf_ref_attachments AS at ON (at.item_type = '
              . $db->quote('com_pftasks.task') . ' AND at.item_id = a.id)');

        // Join over the comments for comment count
        $query->select('COUNT(DISTINCT co.id) AS comments');
        $query->join('LEFT', '#__pf_comments AS co ON (co.context = '
              . $db->quote('com_pftasks.task') . ' AND co.item_id = a.id)');

        // Join over the task refs for dependencies
        $query->select('COUNT(d.id) AS dependency_count');
        $query->join('LEFT', '#__pf_ref_tasks AS d ON (d.task_id = a.id)');

        // Implement View Level Access
        if (!$user->authorise('core.admin', 'com_pftasks')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups . ')');
        }

        // Filter by assigned user
        $assigned = $this->getState('filter.assigned');
        if (is_numeric($assigned) && intval($assigned) != 0) {
            $query->join('INNER', '#__pf_ref_users AS ru ON (ru.item_type = ' . $db->quote('com_pftasks.task') . ' AND ru.item_id = a.id)');
            $query->where('ru.user_id = ' . (int) $assigned);
        }

        // Filter labels
        if (count($this->getState('filter.labels'))) {
            $labels = $this->getState('filter.labels');

            JArrayHelper::toInteger($labels);

            if (count($labels) > 1) {
                $labels = implode(', ', $labels);
                $query->where('lbl.label_id IN (' . $labels . ')');
            }
            else {
                $labels = implode(', ', $labels);
                $query->where('lbl.label_id = ' . $db->quote((int) $labels));
            }
        }

        // Filter fields
        $filters = array();
        $filters['a.state']        = array('STATE',       $this->getState('filter.published'));
        $filters['a.project_id']   = array('INT-NOTZERO', $this->getState('filter.project'));
        $filters['a.milestone_id'] = array('INT-NOTZERO', $this->getState('filter.milestone'));
        $filters['a.list_id']      = array('INT-NOTZERO', $this->getState('filter.tasklist'));
        $filters['a.created_by']   = array('INT-NOTZERO', $this->getState('filter.author'));
        $filters['a.priority']     = array('INT',         $this->getState('filter.priority'));
        $filters['a.complete']     = array('INT',         $this->getState('filter.complete'));
        $filters['a']              = array('SEARCH',      $this->getState('filter.search'));

        // Apply Filter
        PFQueryHelper::buildFilter($query, $filters);

        // Add the list ordering clause.
        $order_col = $this->state->get('list.ordering', 'a.title');
        $order_dir = $this->state->get('list.direction', 'asc');

        if ($order_col == 'a.title') {
            $order_col = 'tl.title, p.title, a.ordering ' . $order_dir . ', ' . $order_col;
        }
        if ($order_col == 'a.ordering' || $order_col == 'a.title') {
            $order_col = 'tl.title, p.title, m.title ' . $order_dir . ', ' . $order_col;
        }
        if ($order_col == 'project_title') {
            $order_col = 'tl.title, m.title, a.title ' . $order_dir . ', p.title';
        }
        if ($order_col == 'milestone_title') {
            $order_col = 'p.title ' . $order_dir . ', m.title';
        }
        if ($order_col == 'tasklist_title') {
            $order_col = 'p.title, m.title ' . $order_dir . ', tl.title';
        }

        $query->order($db->escape($order_col . ' ' . $order_dir));
        $query->group('a.id');

        return $query;
    }


    /**
     * Method to get a list of items.
     * Overriden to inject convert the attribs field into a JParameter object.
     *
     * @return    mixed    An array of objects on success, false on failure.
     */
    public function getItems()
    {
        // Get a storage key.
		$store = $this->getStoreId();

		// Try to load the data from internal storage.
		if (!isset($this->cache[$store])) {
		    // Load the list items.
            $limit = ($this->getState('filter.project') ? 0 : $this->getState('list.limit'));
    		$query = $this->_getListQuery();
    		$items = $this->_getList($query, $this->getStart(), $limit);

    		// Check for a database error.
    		if ($this->_db->getErrorNum()) {
    			$this->setError($this->_db->getErrorMsg());
                $this->cache[$store] = false;
    		}
            else {
                // Add the items to the internal cache.
                $this->cache[$store] = $items;
            }
		}

		$items  = $this->cache[$store];
        $ref    = JModelLegacy::getInstance('UserRefs', 'PFusersModel');
        $tref   = JModelLegacy::getInstance('TaskRefs', 'PFtasksModel');
        $labels = $this->getInstance('Labels', 'PFModel');


        // Get the global params
        $global_params = JComponentHelper::getParams('com_pftasks', true);

        foreach ($items as $i => &$item)
        {
            $params = new JRegistry;
            $params->loadString($item->attribs);

            // Convert the parameter fields into objects.
            $items[$i]->params = clone $this->getState('params');

            // Get assigned users
            $items[$i]->users = $ref->getItems('com_pftasks.task', $items[$i]->id);

            // Create item slugs
            $items[$i]->slug           = $items[$i]->alias           ? ($items[$i]->id . ':' . $items[$i]->alias)                     : $items[$i]->id;
            $items[$i]->project_slug   = $items[$i]->project_alias   ? ($items[$i]->project_id . ':' . $items[$i]->project_alias)     : $items[$i]->project_id;
            $items[$i]->milestone_slug = $items[$i]->milestone_alias ? ($items[$i]->milestone_id . ':' . $items[$i]->milestone_alias) : $items[$i]->milestone_id;
            $items[$i]->list_slug      = $items[$i]->list_alias      ? ($items[$i]->list_id . ':' . $items[$i]->list_alias)           : $items[$i]->list_id;

            // Get the labels
            if ($items[$i]->label_count > 0) {
                $items[$i]->labels = $labels->getConnections('com_pftasks.task', $items[$i]->id);
            }
            else {
                $items[$i]->labels = array();
            }

            // Get the dependencies
            if ($items[$i]->dependency_count > 0) {
                $items[$i]->dependencies = $tref->getItems($items[$i]->id);
            }
            else {
                $items[$i]->dependencies = array();
            }
        }

        return $items;
    }


    /**
	 * Method to get a JPagination object for the data set.
	 *
	 * @return  JPagination  A JPagination object for the data set.
	 */
	public function getPagination()
	{
		// Get a storage key.
		$store = $this->getStoreId('getPagination');

		// Try to load the data from internal storage.
		if (isset($this->cache[$store])) {
			return $this->cache[$store];
		}

		// Create the pagination object.
		jimport('joomla.html.pagination');
		$limit = (int) ($this->getState('filter.project') ? 0 : $this->getState('list.limit')) - (int) $this->getState('list.links');
		$page  = new JPagination($this->getTotal(), $this->getStart(), $limit);

		// Add the object to the internal cache.
		$this->cache[$store] = $page;

		return $this->cache[$store];
	}


    /**
     * Build a list of project authors
     *
     * @return    jdatabasequery
     */
    public function getAuthors()
    {
        $db     = $this->getDbo();
        $query  = $db->getQuery(true);
        $user   = JFactory::getUser();
        $access = PFtasksHelper::getActions();

        // Return empty array if no project is select
        $project = (int) $this->getState('filter.project');
        if ($project < 0) {
            return array();
        }

        // Construct the query
        $query->select('u.id AS value, u.name AS text');
        $query->from('#__users AS u');
        $query->join('INNER', '#__pf_tasks AS a ON a.created_by = u.id');

        // Implement View Level Access
        if (!$user->authorise('core.admin', 'com_pftasks')) {
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
     * Build a list of milestones
     *
     * @return    jdatabasequery
     */
    public function getMilestones()
    {
        $db     = $this->getDbo();
        $query  = $db->getQuery(true);
        $user   = JFactory::getUser();
        $access = PFtasksHelper::getActions();

        // Return empty array if no project is select
        $project = (int) $this->getState('filter.project');

        if ($project < 0) {
            return array();
        }

        // Construct the query
        $query->select('a.id AS value, a.title AS text');
        $query->from('#__pf_milestones AS a');
        // $query->join('LEFT', '#__pf_tasks AS a ON a.milestone_id = m.id');

        // Implement View Level Access
        if (!$user->authorise('core.admin', 'com_pftasks')) {
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
        // $query->group('a.id');
        $query->order('a.title ASC');

        // Get results
        $db->setQuery((string) $query);
        $items = (array) $db->loadObjectList();

        // Return the items
        return $items;
    }


    /**
     * Build a list of task lists
     *
     * @return    jdatabasequery
     */
    public function getTaskLists()
    {
        $db     = $this->getDbo();
        $query  = $db->getQuery(true);
        $user   = JFactory::getUser();
        $access = PFtasksHelper::getActions();

        // Return empty array if no project is select
        $project = (int) $this->getState('filter.project');
        if ($project < 0) {
            return array();
        }

        // Construct the query
        $query->select('a.id AS value, a.title AS text');
        $query->from('#__pf_task_lists AS a');
        // $query->join('LEFT', '#__pf_tasks AS a ON a.list_id = t.id');

        // Implement View Level Access
        if (!$user->authorise('core.admin', 'com_pftasks')) {
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
        // $query->group('a.id');
        $query->order('a.title ASC');

        // Get results
        $db->setQuery((string) $query);
        $items = (array) $db->loadObjectList();

        // Return the items
        return $items;
    }


    /**
     * Build a list of assigned users
     *
     * @return    jdatabasequery
     */
    public function getAssignedUsers()
    {
        $db     = $this->getDbo();
        $user   = JFactory::getUser();
        $query  = $db->getQuery(true);
        $access = PFtasksHelper::getActions();

        // Return empty array if no project is select
        $project = (int) $this->getState('filter.project');
        if ($project < 0) {
            return array();
        }

        // Construct the query
        $query->select('u.id AS value, u.name AS text');
        $query->from('#__users AS u');
        $query->join('INNER', '#__pf_ref_users AS a ON a.user_id = u.id');
        $query->join('RIGHT', '#__pf_tasks AS t ON t.id = a.item_id');
        $query->where('a.item_type = ' . $db->quote('task'));

        // Implement View Level Access
        if (!$user->authorise('core.admin', 'com_pftasks')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('t.access IN (' . $groups . ')');
        }

        // Filter fields
        $filters = array();
        $filters['t.project_id'] = array('INT-NOTZERO', $this->getState('filter.project'));

        if (!$access->get('core.edit.state') && !$access->get('core.edit')) {
            $filters['t.state'] = array('STATE', '1');
        }

        // Apply Filter
        PFQueryHelper::buildFilter($query, $filters);

        $query->group('u.id');
        $query->order('u.name ASC');

        // Get results
        $db->setQuery((string) $query);
        $items = (array) $db->loadObjectList();

        // Return the items
        return $items;
    }


    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState($ordering = 'a.title', $direction = 'ASC')
    {
        $app    = JFactory::getApplication();
        $access = PFtasksHelper::getActions();

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
        if (!$access->get('core.edit.state') && !$access->get('core.edit')){
            $this->setState('filter.published', 1);
            $state = '';
        }

        // Filter - Search
        $search = JRequest::getString('filter_search', '');
        $this->setState('filter.search', $search);

        // Filter - Project
        $project = PFApplicationHelper::getActiveProjectId('filter_project');
        $this->setState('filter.project', $project);

        // Filter - Milestone
        $milestone = $app->getUserStateFromRequest($this->context . '.filter.milestone', 'filter_milestone', '');
        $this->setState('filter.milestone', $milestone);

        // Filter - Task list
        $list = $app->getUserStateFromRequest($this->context . '.filter.tasklist', 'filter_tasklist', '');
        $this->setState('filter.tasklist', $list);

        // Filter - Author
        $author = $app->getUserStateFromRequest($this->context . '.filter.author', 'filter_author', '');
        $this->setState('filter.author', $author);

        // Filter - Assigned User
        $assigned = $app->getUserStateFromRequest($this->context . '.filter.assigned', 'filter_assigned', '');
        $this->setState('filter.assigned', $assigned);

        // Filter - Priority
        $priority = $app->getUserStateFromRequest($this->context . '.filter.priority', 'filter_priority', '');
        $this->setState('filter.priority', $priority);

        // Filter - Complete
        $complete = $app->getUserStateFromRequest($this->context . '.filter.complete', 'filter_complete', '');
        $this->setState('filter.complete', $complete);

        // Filter - Labels
        $labels = JRequest::getVar('filter_label', array());
        $this->setState('filter.labels', $labels);

        // Do not allow some filters if no project is selected
        if (!is_numeric($project) || intval($project) == 0) {
            $this->setState('filter.author', '');
            $this->setState('filter.assigned', '');
            $this->setState('filter.tasklist', '');
            $this->setState('filter.milestone', '');
            $this->setState('filter.labels', array());

            $author    = '';
            $assigned  = '';
            $milestone = '';
            $list      = '';
            $labels    = array();
        }

        if (!is_array($labels)) {
            $labels = array();
        }

        // Filter - Is set
        $this->setState('filter.isset',
            (is_numeric($state) || !empty($search) || is_numeric($author) ||
            is_numeric($assigned) || (is_numeric($list) && $list > 0) || (is_numeric($milestone) && $milestone > 0) ||
            count($labels) || is_numeric($complete))
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
        $id .= ':' . $this->getState('filter.project');
        $id .= ':' . $this->getState('filter.milestone');
        $id .= ':' . $this->getState('filter.tasklist');
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.assigned');
        $id .= ':' . $this->getState('filter.priority');
        $id .= ':' . $this->getState('filter.complete');
        $id .= ':' . $this->getState('filter.author');
        $id .= ':' . $this->getState('filter.search');

        return parent::getStoreId($id);
    }
}
