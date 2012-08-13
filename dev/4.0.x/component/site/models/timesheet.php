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
 * This models supports retrieving lists of time tracking records.
 *
 */
class ProjectforkModelTimesheet extends JModelList
{

    /**
     * Constructor.
     *
     * @param    array          $config    An optional associative array of configuration settings.
     * @see      jcontroller
     */
    public function __construct($config = array())
    {
        // Include query helper class
        require_once JPATH_BASE . '/components/com_projectfork/helpers/query.php';

        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'project_id', 'a.project_id', 'project_title',
                'task_id', 'a.task_id', 'task_title',
                'description', 'a.description',
                'created', 'a.created',
                'created_by', 'a.created_by',
                'modified', 'a.modified',
                'modified_by', 'a.modified_by',
                'checked_out', 'a.checked_out',
                'checked_out_time', 'a.checked_out_time',
                'attribs', 'a.attribs',
                'access', 'a.access', 'access_level',
                'state', 'a.state',
                'log_date', 'a.log_date',
                'log_time', 'a.log_time'
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
                . 'a.checked_out, a.checked_out_time, a.state, a.access, '
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
        $query->select('t.title AS task_title, t.alias AS task_alias');
        $query->join('LEFT', '#__pf_tasks AS t ON t.id = a.task_id');

        // Implement View Level Access
        if (!$user->authorise('core.admin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups . ')');
        }

        // Filter fields
        $filters = array();
        $filters['a.state']      = array('STATE',       $this->getState('filter.published'));
        $filters['a.project_id'] = array('INT-NOTZERO', $this->getState('filter.project'));
        $filters['a.task_id']    = array('INT-NOTZERO', $this->getState('filter.task'));
        $filters['a.access']     = array('INT-NOTZERO', $this->getState('filter.access'));
        $filters['a.created_by'] = array('INT-NOTZERO', $this->getState('filter.author'));
        $filters['a']            = array('SEARCH',      $this->getState('filter.search'));

        // Apply Filter
        ProjectforkHelperQuery::buildFilter($query, $filters);

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
        $global_params = JComponentHelper::getParams('com_projectfork', true);

        foreach ($items as $i => &$item)
        {
            // Convert the parameter fields into objects.
            $params = new JRegistry;
            $params->loadString($item->attribs);

            $items[$i]->params = clone $this->getState('params');

            // Create slugs
            $items[$i]->slug         = $items[$i]->id . ':record';
            $items[$i]->project_slug = $items[$i]->project_alias ? ($items[$i]->project_id.':' . $items[$i]->project_alias) : $items[$i]->project_id;
            $items[$i]->task_slug    = $items[$i]->task_alias ? ($items[$i]->task_id.':' . $items[$i]->task_alias) : $items[$i]->task_id;
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
        $db    = $this->getDbo();
        $query = $db->getQuery(true);
        $user  = $user = JFactory::getUser();

        // Construct the query
        $query->select('u.id AS value, u.name AS text, COUNT(DISTINCT a.id) AS count');
        $query->from('#__users AS u');
        $query->join('INNER', '#__pf_timesheet AS a ON a.created_by = u.id');

        // Implement View Level Access
        if (!$user->authorise('core.admin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups.')');
        }

        // Filter fields
        $filters = array();
        $filters['a.state']      = array('STATE',       $this->getState('filter.published'));
        $filters['a.project_id'] = array('INT-NOTZERO', $this->getState('filter.project'));
        $filters['a.task_id']    = array('INT-NOTZERO', $this->getState('filter.task'));

        // Apply Filter
        ProjectforkHelperQuery::buildFilter($query, $filters);

        // Filter by search in title.
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = '.(int) substr($search, 4));
            }
            elseif (stripos($search, 'author:') === 0) {
                $search = $db->Quote('%' . $db->getEscaped(trim(substr($search, 8)), true).'%');
                $query->where('(u.name LIKE ' . $search.' OR u.username LIKE ' . $search.')');
            }
            else {
                $search = $db->Quote('%' . $db->getEscaped($search, true).'%');
                $query->where('(a.title LIKE ' . $search.' OR a.alias LIKE ' . $search.')');
            }
        }

        // Group and order
        $query->group('u.id');
        $query->order('u.name, count');

        $db->setQuery((string) $query);

        $items = (array) $db->loadObjectList();
        $count = count($items);

        for($i = 0; $i < $count; $i++)
        {
            $items[$i]->text .= ' (' . $items[$i]->count.')';
            unset($items[$i]->count);
        }


        // Return the items
        return $items;
    }


    /**
     * Build a list of publishing states
     *
     * @return    jdatabasequery
     */
    public function getPublishedStates()
    {
        $db     = $this->getDbo();
        $states = JHtml::_('jgrid.publishedOptions');
        $count  = count($states);

        $query_select = $this->getState('list.select');
        $query_state  = $this->getState('filter.published');

        for($i = 0; $i < $count; $i++)
        {
            if ($states[$i]->disable == true) {
                $states[$i]->text = JText::_($states[$i]->text).' (0)';
                continue;
            }
            if ($states[$i]->value == '*') {
                unset($states[$i]);
                continue;
            }

            $this->setState('list.select', 'COUNT(DISTINCT a.id)');
            $this->setState('filter.published', $states[$i]->value);

            $query = $this->getListQuery();
            $db->setQuery((string) $query);

            $found = (int) $db->loadResult();

            $states[$i]->text = JText::_($states[$i]->text).' (' . $found.')';
        }

        $this->setState('list.select', $query_select);
        $this->setState('filter.published', $query_state);

        return $states;
    }


    /**
     * Method to retrieve the query list limit start value
     *
     * @return    integer
     **/
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
    protected function populateState($ordering = 'a.log_date', $direction = 'DESC')
    {
        // Query limit
        $value = JRequest::getUInt('limit', JFactory::getApplication()->getCfg('list_limit', 0));
        $this->setState('list.limit', $value);

        // Query limit start
        $value = JRequest::getUInt('limitstart', 0);
        $this->setState('list.start', $value);

        // Query order field
        $value = JRequest::getCmd('filter_order', 'a.log_date');
        if (!in_array($value, $this->filter_fields)) $value = 'a.created';
        $this->setState('list.ordering', $value);

        // Query order direction
        $value = JRequest::getCmd('filter_order_Dir', 'DESC');
        if (!in_array(strtoupper($value), array('ASC', 'DESC', ''))) $value = 'DESC';
        $this->setState('list.direction', $value);

        // Params
        $value = JFactory::getApplication()->getParams();
        $this->setState('params', $value);

        // State
        $value = JRequest::getCmd('filter_published', '');
        $this->setState('filter.published', $value);

        // Filter on published for those who do not have edit or edit.state rights.
        $access = ProjectforkHelper::getActions();
        if (!$access->get('time.edit.state') && !$access->get('time.edit')){
            $this->setState('filter.published', 1);
        }

        // Filter - Search
        $value = JRequest::getString('filter_search', '');
        $this->setState('filter.search', $value);

        // Filter - Project
        $value = $this->getUserStateFromRequest('com_projectfork.project.active.id', 'filter_project', '');
        $this->setState('filter.project', $value);
        ProjectforkHelper::setActiveProject($value);

        // Filter - Task
        $value = JRequest::getCmd('filter_task', '');
        $this->setState('filter.task', $value);

        // Filter - Author
        $value = JRequest::getCmd('filter_author', '');
        $this->setState('filter.author', $value);

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
        $id .= ':' . $this->getState('filter.task');
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.author');

        return parent::getStoreId($id);
    }
}
