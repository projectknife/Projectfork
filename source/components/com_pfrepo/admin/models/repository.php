<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfrepo
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.modellist');
jimport('joomla.application.component.helper');


/**
 * Methods supporting the listings of a repository directory.
 *
 */
class PFrepoModelRepository extends JModelList
{
    /**
     * Constructor
     *
     * @param    array          An optional associative array of configuration settings.
     * @see      jcontroller
     */
    public function __construct($config = array())
    {
        // Set field filter
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'a.id',
                'a.project_id', 'project_title',
                'a.title',
                'a.alias',
                'a.created',
                'a.created_by', 'author_name',
                'a.modified',
                'a.modified_by', 'editor_name',
                'a.checked_out',
                'a.checked_out_time',
                'a.access', 'access_level'
            );
        }

        parent::__construct($config);
    }


    /**
     * Method to get an array of data items.
     *
     * @return    mixed    An array of data items on success, false on failure.
     */
    public function getItems()
    {
        // Get a storage key.
        $store = $this->getStoreId();

        // Try to load the data from internal storage.
        if (isset($this->cache[$store])) {
            return $this->cache[$store];
        }

        // Load the items
        $items  = array();
        $parent = (int) $this->getState('filter.parent_id', 1);
        $dir    = $this->getInstance('Directory', 'PFrepoModel', $config = array('ignore_request' => true));

        if ($parent == 1) {
            // Load the list items.
            $query = $this->_getListQuery();

            try {
                $items['directory']   = $dir->getItem($parent);
                $items['directories'] = $this->_getList($query, $this->getStart(), $this->getState('list.limit'));
                $items['notes']       = array();
                $items['files']       = array();
            }
            catch (RuntimeException $e) {
                $this->setError($e->getMessage());
                return false;
            }
        }
        else {
            // Get the models
            $dirs  = $this->getInstance('Directories', 'PFrepoModel', $config = array());
            $notes = $this->getInstance('Notes', 'PFrepoModel', $config = array());
            $files = $this->getInstance('Files', 'PFrepoModel', $config = array());

            // Get the data
            try {
                $items['directory']   = $dir->getItem($parent);
                $items['directories'] = $dirs->getItems();
                $items['notes']       = $notes->getItems();
                $items['files']       = $files->getItems();
            }
            catch (RuntimeException $e) {
                $this->setError($e->getMessage());
                return false;
            }
        }

        // Add the items to the internal cache.
        $this->cache[$store] = $items;

        return $this->cache[$store];
    }


    /**
     * Build a list of authors
     *
     * @return    array
     */
    public function getAuthors()
    {
        // Load only if project filter is set
        $project = (int) $this->getState('filter.project');
        $dir     = (int) $this->getState('filter.parent_id');

        if ($project <= 0 || $dir <= 0) return array();

        $query = $this->_db->getQuery(true);

        // Construct the query
        $query->select('u.id AS value, u.name AS text')
              ->from('#__users AS u')
              ->join('LEFT', '#__pf_repo_dirs AS d ON d.created_by = u.id')
              ->join('LEFT', '#__pf_repo_notes AS n ON n.dir_id = d.id')
              ->join('LEFT', '#__pf_repo_files AS f ON f.dir_id = d.id')
              ->where('d.project_id = ' . $project)
              ->group('u.id')
              ->order('u.name');

        // Return the result
        $this->_db->setQuery($query, 0, 50);
        return $this->_db->loadObjectList();
    }


    /**
     * Build an SQL query to load the list data.
     * This query loads the project repo list only!
     *
     * @return    jdatabasequery
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $query = $this->_db->getQuery(true);
        $user  = JFactory::getUser();

        // Get possible filters
        $filter_access = $this->getState('filter.access');
        $filter_search = $this->getState('filter.search');

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.project_id, a.title, a.alias, a.description, a.checked_out, '
                . 'a.checked_out_time, a.created, a.access, a.created_by, a.parent_id, '
                . 'a.lft, a.rgt, a.level, a.path'
            )
        );

        $query->from('#__pf_repo_dirs AS a');

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

        if ($this->getState('list.count_elements')) {
            // Join over the directories for folder count
            $query->select('COUNT(DISTINCT d.id) AS dir_count')
                  ->join('LEFT', '#__pf_repo_dirs AS d ON d.parent_id = a.id');

            // Join over the files for file count
            $query->select('COUNT(DISTINCT f.id) AS file_count')
                  ->join('LEFT', '#__pf_repo_files AS f ON f.dir_id = a.id');

            // Join over the notes for note count
            $query->select('COUNT(DISTINCT n.id) AS note_count')
                  ->join('LEFT', '#__pf_repo_notes AS n ON n.dir_id = a.id');
        }

        // Filter by access level.
        if ($filter_access) {
            $query->where('a.access = ' . (int) $filter_access);
        }

        // Implement View Level Access
        if (!$user->authorise('core.admin')) {
            $levels = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $levels . ')');
        }

        // Filter by parent directory
        $query->where('a.parent_id = 1');

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
                $query->where('(a.title LIKE ' . $search . ' OR a.alias LIKE ' . $search . ')');
            }
        }

        // Add the list ordering clause.
        $order_col = $this->state->get('list.ordering', 'a.title');
        $order_dir = $this->state->get('list.direction', 'asc');

        $query->order($this->_db->escape($order_col . ' ' . $order_dir))
              ->group('a.id');

        return $query;
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
        $params = JComponentHelper::getParams('com_pfrepo');
        $app    = JFactory::getApplication();

        // Adjust the context to support modal layouts.
        if ($layout = JRequest::getVar('layout')) $this->context .= '.' . $layout;

        // Config - Count elements
        $this->setState('list.count_elements', (int) $params->get('show_element_count'));

        // Filter - Search
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        // Filter - Access
        $access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', '');
        $this->setState('filter.access', $access);

        // Filter - Project
        $project = PFApplicationHelper::getActiveProjectId('filter_project');

        // Filter - Author
        $author_id = $app->getUserStateFromRequest($this->context . '.filter.author_id', 'filter_author_id');
        $this->setState('filter.author_id', $author_id);

        // Filter - Directory
        $parent_id = JRequest::getVar('filter_parent_id');

        // If no parent folder is given (or in root), find the repo dir of the project
        if ($parent_id <= 1 && $project) {
            $params = PFApplicationHelper::getProjectParams();
            $repo   = (int) $params->get('repo_dir');

            if ($repo) $parent_id = (int) $repo;
        }
        elseif ($parent_id > 1 && !$project) {
            // If a folder is selected, but no project active, find the project id of the folder
            $query = $this->_db->getQuery(true);

            $query->select('project_id')
                  ->from('#__pf_repo_dirs')
                  ->where('id = ' . (int) $parent_id);

            $this->_db->setQuery($query);
            $project = (int) $this->_db->loadResult();

            // If no project was found, return to the repo root
            if (!$project) $parent_id = 1;
        }
        elseif (empty($parent_id) && !$project) {
            $parent_id = 1;
            $project   = 0;
        }

        if (JRequest::getVar('filter_project', null, 'post') === '0') {
            $parent_id = 1;
            $project   = 0;
        }

        PFApplicationHelper::setActiveProject($project);
        $this->setState('filter.project', $project);
        $this->setState('filter.parent_id',  $parent_id);

        // Override the user input to control the other models
        JRequest::setVar('filter_parent_id', $parent_id);
        JRequest::setVar('filter_project',   $project);

        // Handle list limit
        if ($project) {
            JRequest::setVar('limit', 0);
        }
        else {
            if (JRequest::getVar('limit') === null) {
                JRequest::setVar('limit', $app->getCfg('list_limit'));
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
     *
     * @return    string           A store id.
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.access');
        $id .= ':' . $this->getState('filter.author_id');
        $id .= ':' . $this->getState('filter.parent_id');
        $id .= ':' . $this->getState('filter.project');

        return parent::getStoreId($id);
    }
}
