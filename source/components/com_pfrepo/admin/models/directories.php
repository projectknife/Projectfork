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
 * Methods supporting a list of directories.
 *
 */
class PFrepoModelDirectories extends JModelList
{
    /**
     * Constructor
     *
     * @param     array    An optional associative array of configuration settings.
     *
     * @return    void
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'a.id',
                'a.project_id', 'project_title',
                'a.title',
                'a.created',
                'a.created_by', 'author_name',
                'a.modified',
                'a.parent_id',
                'a.modified_by', 'editor',
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

        // Load the list items.
        $query = $this->_getListQuery();

        try {
            $count    = (int) $this->getState('list.count_elements');
            $items    = $this->_getList($query, $this->getStart(), $this->getState('list.limit'));

            if ($count) {
                $pks      = JArrayHelper::getColumn($items, 'id');
                $elements = $this->getElementCount($pks);
            }

            foreach ($items AS $item)
            {
                $item->element_count = ($count ? $elements[$item->id] : 0);
                $item->orphaned      = empty($item->project_exists);
            }
        }
        catch (RuntimeException $e) {
            $this->setError($e->getMessage());
            return false;
        }

        // Add the items to the internal cache.
        $this->cache[$store] = $items;

        return $this->cache[$store];
    }


    /**
     * Counts the contents of the given folders
     *
     * @param    array    $pks      The folders to count the contents of
     *
     * @retun    array    $count    The element count
     */
    public function getElementCount($pks)
    {
        $query = $this->_db->getQuery(true);
        $user  = JFactory::getUser();

        if (!is_array($pks) || !count($pks)) return array();

        // Count sub-folders
        $query->select('parent_id, COUNT(id) AS folder_count')
              ->from('#__pf_repo_dirs')
              ->where('parent_id IN(' . implode(',', $pks) . ')');

        if (!$user->authorise('core.admin')) {
            $levels = implode(',', $user->getAuthorisedViewLevels());
            $query->where('access IN (' . $levels . ')');
        }

        $query->group('parent_id');
        $this->_db->setQuery($query);

        try {
            $folder_count = $this->_db->loadAssocList('parent_id', 'folder_count');
        }
        catch (RuntimeException $e) {
            $this->setError($e->getMessage());
            return false;
        }

        // Count notes
        $query->clear()
              ->select('dir_id, COUNT(id) AS note_count')
              ->from('#__pf_repo_notes')
              ->where('dir_id IN(' . implode(',', $pks) . ')');

        if (!$user->authorise('core.admin')) {
            $levels = implode(',', $user->getAuthorisedViewLevels());
            $query->where('access IN (' . $levels . ')');
        }

        $query->group('dir_id');
        $this->_db->setQuery($query);

        try {
            $note_count = $this->_db->loadAssocList('dir_id', 'note_count');
        }
        catch (RuntimeException $e) {
            $this->setError($e->getMessage());
            return false;
        }

        // Count files
        $query->clear()
              ->select('dir_id, COUNT(id) AS file_count')
              ->from('#__pf_repo_files')
              ->where('dir_id IN(' . implode(',', $pks) . ')');

        if (!$user->authorise('core.admin')) {
            $levels = implode(',', $user->getAuthorisedViewLevels());
            $query->where('access IN (' . $levels . ')');
        }

        $query->group('dir_id');
        $this->_db->setQuery($query);

        try {
            $file_count = $this->_db->loadAssocList('dir_id', 'file_count');
        }
        catch (RuntimeException $e) {
            $this->setError($e->getMessage());
            return false;
        }

        // Put everything together
        $count = array();

        foreach ($pks as $pk)
        {
            $count[$pk] = 0;

            if (isset($folder_count[$pk])) $count[$pk] += $folder_count[$pk];
            if (isset($note_count[$pk]))   $count[$pk] += $note_count[$pk];
            if (isset($file_count[$pk]))   $count[$pk] += $file_count[$pk];
        }

        return $count;
    }


    /**
     * Build an SQL query to load the list data.
     *
     * @return    jdatabasequery
     */
    protected function getListQuery()
    {
        $query = $this->_db->getQuery(true);
        $user  = JFactory::getUser();

        // Get possible filters
        $filter_project = $this->getState('filter.project');
        $filter_access  = $this->getState('filter.access');
        $filter_author  = $this->getState('filter.author_id');
        $filter_search  = $this->getState('filter.search');
        $filter_parent  = $this->getState('filter.parent_id');

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.project_id, a.title, a.alias, a.description, a.checked_out, '
                . 'a.checked_out_time, a.created, a.access, a.created_by, a.parent_id, '
                . 'a.protected, a.lft, a.rgt, a.level, a.path'
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

        // Filter by access level.
        if ($filter_access) {
            $query->where('a.access = ' . (int) $filter_access);
        }

        // Implement View Level Access
        if (!$user->authorise('core.admin')) {
            $levels = implode(',', $user->getAuthorisedViewLevels());

            $query->where('a.access IN (' . $levels . ')');
        }

        // Filter by project
        if (is_numeric($filter_project) && $filter_project > 0) {
            $query->where('a.project_id = ' . (int) $filter_project);
        }

        // Filter by author
        if (is_numeric($filter_author)) {
            $type = $this->getState('filter.author_id.include', true) ? '= ' : '<>';
            $query->where('a.created_by ' . $type . (int) $filter_author);
        }

        // Filter by parent directory
        if (is_numeric($filter_parent)) {
            if (!empty($filter_search)) {
                $query2 = $this->_db->getQuery(true);

                $query2->select('lft, rgt')
                       ->from('#__pf_repo_dirs')
                       ->where('id = ' . (int) $filter_parent);

                $this->_db->setQuery($query2);
                $dir = $this->_db->loadObject();

                if (!empty($dir)) {
                    $query->where('a.lft > ' . (int) $dir->lft)
                          ->where('a.rgt < ' . (int) $dir->rgt);
                }
            }
            else {
                $query->where('a.parent_id = ' . (int) $filter_parent);
            }
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
                $search = $this->_db->quote('%' . $this->_db->escape($filter_search, true) . '%');
                $query->where('(a.title LIKE ' . $search . ' OR a.alias LIKE ' . $search . ')');
            }
        }

        // Add the list ordering clause.
        $order_col = $this->state->get('list.ordering', 'a.title');
        $order_dir = $this->state->get('list.direction', 'asc');

        if ($order_col != 'a.lft') {
            $order_col = $order_col .  ' ' . $order_dir . ', a.lft';
        }

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

        // Filter - Author
        $author_id = $app->getUserStateFromRequest($this->context . '.filter.author_id', 'filter_author_id');
        $this->setState('filter.author_id', $author_id);

        // Filter - Access
        $access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', '');
        $this->setState('filter.access', $access);

        // Filter - Project
        $project = PFApplicationHelper::getActiveProjectId('filter_project');
        $this->setState('filter.project', $project);

        // Filter - Directory
        $parent_id = JRequest::getUint('filter_parent_id', 1);
        $this->setState('filter.parent_id', $parent_id);

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
        $id .= ':' . $this->getState('filter.project');
        $id .= ':' . $this->getState('filter.parent_id');

        return parent::getStoreId($id);
    }
}
