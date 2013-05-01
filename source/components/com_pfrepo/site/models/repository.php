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
        $count  = (int) $this->getState('list.count_elements');
        $parent = (int) $this->getState('filter.parent_id', 1);

        if ($parent == 1) {
            // Load the list items.
            $query  = $this->_getListQuery();
            $dir    = $this->getInstance('DirectoryForm', 'PFrepoModel', $config = array('ignore_request' => true));
            $labels = $this->getInstance('Labels', 'PFModel');

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

            if ($count) {
                $pks      = JArrayHelper::getColumn($items['directories'], 'id');
                $elements = $this->getElementCount($pks);
            }

            foreach ($items['directories'] as $i => &$item)
            {
                // Convert the parameter fields into objects.
                $params = new JRegistry;
                $params->loadString($item->attribs);

                $item->params = clone $this->getState('params');

                // Create slugs
                $item->slug         = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
                $item->project_slug = $item->project_alias ? ($item->project_id . ':' . $item->project_alias) : $item->project_id;

                // Set orphaned status
                $item->orphaned = empty($item->project_exists);

                // Get element count
                $item->element_count = ($count ? $elements[$item->id] : 0);

                // Get the labels
                if ($item->label_count > 0) {
                    $item->labels = $labels->getConnections('com_pfrepo.directory', $item->id);
                }
            }
        }
        else {
            // Get the models
            $dir    = $this->getInstance('DirectoryForm', 'PFrepoModel', $config = array('ignore_request' => true));
            $dirs   = $this->getInstance('Directories', 'PFrepoModel', $config = array());
            $notes  = $this->getInstance('Notes', 'PFrepoModel', $config = array());
            $files  = $this->getInstance('Files', 'PFrepoModel', $config = array());

            // Get the data
            $items['directory']   = $dir->getItem($parent);
            $items['directories'] = $dirs->getItems();
            $items['notes']       = $notes->getItems();
            $items['files']       = $files->getItems();

            // Check for errors
            if ($dir->getError()) {
                $this->setError($dir->getError());
            }

            if ($dirs->getError()) {
                $this->setError($dirs->getError());
            }

            if ($notes->getError()) {
                $this->setError($notes->getError());
            }

            if ($files->getError()) {
                $this->setError($files->getError());
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
                . 'a.lft, a.rgt, a.level, a.path, a.protected, a.attribs'
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
        $query->select('p.id AS project_exists, p.title AS project_title, p.alias AS project_alias')
              ->join('LEFT', '#__pf_projects AS p ON p.id = a.project_id');

        // Join over the label refs for label count
        $query->select('COUNT(DISTINCT lbl.id) AS label_count')
              ->join('LEFT', '#__pf_ref_labels AS lbl ON (lbl.item_id = a.id '
                           . 'AND lbl.item_type = ' . $this->_db->quote('com_pfrepo.directory') . ')');

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
                $search = $this->_db->quote('%' . $this->_db->escape($filter_search, true) . '%');
                $query->where('(a.title LIKE ' . $search . ' OR a.alias LIKE ' . $search . ')');
            }
        }

        // Add the list ordering clause.
        $order_col = $this->state->get('list.ordering', 'a.ordering');
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
        $app    = JFactory::getApplication();
        $params = $app->getParams();

        // Adjust the context to support modal layouts.
        if ($layout = JRequest::getVar('layout')) $this->context .= '.' . $layout;

        // Set Params
        $this->setState('params', $params);

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

        // Filter - Labels
        $labels = (array) JRequest::getVar('filter_label', array(), 'post', 'array');
        $this->setState('filter.labels', $labels);

        // Filter - Directory
        $parent_id = JRequest::getVar('filter_parent_id');

        // Get the path
        $path = str_replace(':', '-', JRequest::getVar('path'));

        if (!$parent_id && !empty($path)  && $project > 0) {
            // No parent folder given. Try to find it from the path
            $dir  = $this->getInstance('DirectoryForm', 'PFrepoModel', $config = array('ignore_request' => true));
            $item = $dir->getItemFromProjectPath($project, $path);

            if ($item) {
                $parent_id = $item->id;
                JRequest::setVar('filter_parent_id', $parent_id);
            }
        }

        // If no parent folder is given, find the repo dir of the project
        if (empty($parent_id) && $project > 0) {
            $params = PFApplicationHelper::getProjectParams();
            $repo   = (int) $params->get('repo_dir');

            if ($repo) $parent_id = (int) $repo;
        }
        elseif (is_numeric($parent_id) && $project <= 0) {
            // If a folder is selected, but no project, find the project id of the folder
            $dir  = $this->getInstance('DirectoryForm', 'PFrepoModel', $config = array('ignore_request' => true));
            $item = $dir->getItem((int) $parent_id);

            if ($item->id > 0) {
                if ($item->parent_id == '1') {
                    $project = $item->project_id;
                }
                else {
                    $parent_id = 1;
                    $project   = 0;
                }
            }
            else {
                $parent_id = 1;
                $project   = 0;
            }
        }
        elseif ($project <= 0 && empty($parent_id)) {
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

        // Do not allow to filter by author if no project is selected
        if ($project <= 0) {
            $this->setState('filter.labels', array());
            $this->setState('filter.author_id', '');

            $labels    = array();
            $author_id = '';
        }

        // Filter - Is set
        $this->setState('filter.isset', (!empty($search) || count($labels) || is_numeric($author_id)));

        // List state information.
        parent::populateState($ordering, $direction);
    }


    /**
     * Counts the contents of the given folders
     *
     * @param    array    $pks      The folders to count the contents of
     *
     * @retun    array    $count    The element count
     */
    protected function getElementCount($pks)
    {
        $config = array('ignore_request' => true);
        $model  = $this->getInstance('Directories', 'PFrepoModel', $config);

        return $model->getElementCount($pks);
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
        $id .= ':' . serialize($this->getState('filter.labels'));

        return parent::getStoreId($id);
    }
}
