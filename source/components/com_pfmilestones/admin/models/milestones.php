<?php
/**
 * @package      Projectfork
 * @subpackage   Milestones
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.modellist');


/**
 * Methods supporting a list of milestone records.
 *
 */
class PFmilestonesModelMilestones extends JModelList
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
                'a.id', 'a.project_id', 'a.title',
                'a.description', 'a.alias',
                'a.created', 'a.created_by', 'a.modified',
                'a.modified_by', 'a.checked_out',
                'a.checked_out_time', 'a.attribs',
                'a.access', 'access_level',
                'a.state', 'a.start_date', 'a.end_date',
                'project_title', 'p.title'
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
    protected function populateState($ordering = 'a.title', $direction = 'asc')
    {
        // Initialise variables.
        $app = JFactory::getApplication();

        // Adjust the context to support modal layouts.
        if ($layout = JRequest::getVar('layout')) $this->context .= '.' . $layout;

        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $author_id = $app->getUserStateFromRequest($this->context . '.filter.author_id', 'filter_author_id');
        $this->setState('filter.author_id', $author_id);

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        $access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', '');
        $this->setState('filter.access', $access);

        $project = PFApplicationHelper::getActiveProjectId('filter_project');
        $this->setState('filter.project', $project);

        // Disable author filter if no project is selected
        if (!$project) {
            $this->setState('filter.author_id', '');
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
        $id .= ':' . $this->getState('filter.project');

        return parent::getStoreId($id);
    }


    /**
     * Build an SQL query to load the list data.
     *
     * @return    jdatabasequery
     */
    protected function getListQuery()
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);
        $user  = JFactory::getUser();

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.project_id, a.title, a.description, a.alias, a.checked_out, '
                . 'a.checked_out_time, a.state, a.access, a.created, a.created_by,'
                . 'a.start_date, a.end_date'
            )
        );
        $query->from('#__pf_milestones AS a');

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

        // Implement View Level Access
        if (!$user->authorise('core.admin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups . ')');
            $query->where('p.access IN (' . $groups . ')');
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

        // Filter by author
        $author_id = $this->getState('filter.author_id');
        if (is_numeric($author_id)) {
            $type = $this->getState('filter.author_id.include', true) ? '= ' : '<>';
            $query->where('a.created_by ' . $type. (int) $author_id);
        }

        // Filter by search in title.
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            }
            elseif (stripos($search, 'author:') === 0) {
                $search = $db->quote('%' . $db->escape(substr($search, 7), true) . '%');
                $query->where('(ua.name LIKE ' . $search.' OR ua.username LIKE ' . $search . ')');
            }
            else {
                $search = $db->quote('%' . $db->escape($search, true) . '%');
                $query->where('(a.title LIKE ' . $search.' OR a.alias LIKE ' . $search . ')');
            }
        }

        // Add the list ordering clause.
        $order_col  = $this->state->get('list.ordering', 'a.title');
        $order_dir = $this->state->get('list.direction', 'asc');

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
              ->join('INNER', '#__pf_milestones AS a ON a.created_by = u.id')
              ->where('a.project_id = ' . $db->quote($project))
              ->group('u.id')
              ->order('u.name');

        $db->setQuery((string) $query);

        // Return the result
        return $db->loadObjectList();
    }
}
