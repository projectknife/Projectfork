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
 * This models supports retrieving lists of task lists.
 *
 */
class ProjectforkModelTasklists extends JModelList
{

    /**
     * Constructor.
     *
     * @param    array          An optional associative array of configuration settings.
     * @see      jcontroller
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'title', 'a.title',
                'created', 'a.created',
                'modified', 'a.modified',
                'checked_out', 'a.checked_out',
                'checked_out_time', 'a.checked_out_time',
                'state', 'a.state',
                'ordering', 'a.ordering',
                'project_title', 'p.title',
                'milestone_title', 'm.title'.
                'tasks', 'ta.tasks',
                'access_level',
                'author_name',
                'editor'
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
                'a.id, a.project_id, a.milestone_id, a.title, a.alias, a.description, '
                . 'a.checked_out, a.checked_out_time, a.state, a.access, a.created, '
                . 'a.created_by, a.ordering, a.attribs, p.alias as project_alias, m.alias AS milestone_alias'
            )
        );
        $query->from('#__pf_task_lists AS a');

        // Join over the users for the checked out user.
        $query->select('uc.name AS editor');
        $query->join('LEFT', '#__users AS uc ON uc.id = a.checked_out');

        // Join over the asset groups.
        $query->select('ag.title AS access_level');
        $query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

        // Join over the users for the author.
        $query->select('ua.name AS author_name, ua.email AS author_email');
        $query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

        // Join over the projects for the project title.
        $query->select('p.title AS project_title');
        $query->join('LEFT', '#__pf_projects AS p ON p.id = a.project_id');

        // Join over the milestones for the milestone title.
        $query->select('m.title AS milestone_title');
        $query->join('LEFT', '#__pf_milestones AS m ON m.id = a.milestone_id');

        // Join over the tasks for the task count.
        $query->select('COUNT(DISTINCT ta.id) AS tasks');
        $query->join('LEFT', '#__pf_tasks AS ta ON ta.list_id = a.id');

        // Implement View Level Access
        if (!$user->authorise('core.admin')) {
            $groups    = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups . ')');
        }

        // Filter by project
        $project = $this->getState('filter.project');
        if (is_numeric($project) && $project != 0) {
            $query->where('a.project_id = ' . (int) $project);
        }

        // Filter by milestone
        $milestone = $this->getState('filter.milestone');
        if (is_numeric($milestone) && $milestone != 0) {
            $query->where('a.milestone_id = ' . (int) $milestone);
        }

        // Filter by published state
        $published = $this->getState('filter.published');
        if (is_numeric($published)) {
            $query->where('a.state = ' . (int) $published);
        }
        elseif ($published === '') {
            $query->where('(a.state = 0 OR a.state = 1)');
        }

        // Filter by search in title.
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = '. (int) substr($search, 4));
            }
            elseif (stripos($search, 'author:') === 0) {
                $search = $db->Quote('%' . $db->getEscaped(trim(substr($search, 8)), true) . '%');
                $query->where('(ua.name LIKE ' . $search . ' OR ua.username LIKE ' . $search . ')');
            }
            else {
                $search = $db->Quote('%' . $db->getEscaped($search, true).'%');
                $query->where('(a.title LIKE ' . $search.' OR a.alias LIKE ' . $search . ')');
            }
        }

        // Add the list ordering clause.
        $query->group('a.id');
        $query->order($this->getState('list.ordering', 'a.title') . ' ' . $this->getState('list.direction', 'ASC'));

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
        $items = parent::getItems();

        // Get the global params
        $global_params = JComponentHelper::getParams('com_projectfork', true);

        // Convert the parameter fields into objects.
        foreach ($items as $i => &$item)
        {
            $params = new JRegistry;
            $params->loadString($item->attribs);

            $items[$i]->params = clone $this->getState('params');
        }

        return $items;
    }


    public function getStart()
    {
        return $this->getState('list.start');
    }


    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState($ordering = 'title', $direction = 'ASC')
    {
        // Query limit
        $value = JRequest::getUInt('limit', JFactory::getApplication()->getCfg('list_limit', 0));
        $this->setState('list.limit', $value);

        // Query limit start
        $value = JRequest::getUInt('limitstart', 0);
        $this->setState('list.start', $value);

        // Query order field
        $value = JRequest::getCmd('filter_order', 'a.title');
        if (!in_array($value, $this->filter_fields)) $value = 'a.title';
        $this->setState('list.ordering', $value);

        // Query order direction
        $value = JRequest::getCmd('filter_order_Dir', 'ASC');
        if (!in_array(strtoupper($value), array('ASC', 'DESC', ''))) $value = 'ASC';
        $this->setState('list.direction', $value);

        // Params
        $value = JFactory::getApplication()->getParams();
        $this->setState('params', $value);

        // State
        $value = JRequest::getCmd('filter_published', '');
        $this->setState('filter.published', $value);

        // Filter on published for those who do not have edit or edit.state rights.
        $access = ProjectforkHelperAccess::getActions();
        if (!$access->get('milestone.edit.state') && !$access->get('milestone.edit')){
            $this->setState('filter.published', 1);
        }

        // Filter - Search
        $value = JRequest::getString('filter_search', '');
        $this->setState('filter.search', $value);

        // Filter - Project
        $value = $this->getUserStateFromRequest('com_projectfork.project.active.id', 'filter_project', '');
        $this->setState('filter.project', $value);
        ProjectforkHelper::setActiveProject($value);

        // Filter - Milestone
        $value = JRequest::getCmd('filter_milestone', '');
        $this->setState('filter.milestone', $value);

        // View Layout
        $this->setState('layout', JRequest::getCmd('layout'));
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
        $id .= ':' . $this->getState('filter.milestone');

        return parent::getStoreId($id);
    }
}
