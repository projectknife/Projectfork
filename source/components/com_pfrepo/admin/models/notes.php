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


/**
 * Methods supporting a list of notes.
 *
 */
class PFrepoModelNotes extends JModelList
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
                'a.dir_id',
                'a.project_id', 'project_title',
                'a.title',
                'a.created',
                'a.created_by', 'author_name',
                'a.modified',
                'a.modified_by', 'editor',
                'a.checked_out',
                'a.checked_out_time',
                'a.access', 'access_level'
            );
        }

        parent::__construct($config);
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
        $filter_dir     = $this->getState('filter.dir_id');

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.project_id, a.title, a.alias, a.description, a.checked_out, '
                . 'a.checked_out_time, a.created, a.access, a.created_by, a.dir_id'
            )
        );

        $query->from('#__pf_repo_notes AS a');

        // Join over the directory for path
        $query->select('d.lft, d.rgt, d.path')
              ->join('INNER', '#__pf_repo_dirs AS d ON d.id = a.dir_id');

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
        if (is_numeric($filter_dir)) {
            if (!empty($filter_search)) {
                $query2 = $this->_db->getQuery(true);

                $query2->select('lft, rgt')
                       ->from('#__pf_repo_dirs')
                       ->where('id = ' . (int) $filter_dir);

                $this->_db->setQuery($query2);
                $dir = $this->_db->loadObject();

                if (!empty($dir)) {
                    $query->where('d.lft >= ' . (int) $dir->lft)
                          ->where('d.rgt <= ' . (int) $dir->rgt);
                }
            }
            else {
                $query->where('a.dir_id = ' . (int) $filter_dir);
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

        $query->order($this->_db->escape($order_col . ' ' . $order_dir))
              ->group('a.id');

        return $query;
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
            $items = $this->_getList($query, 0, 0);

            $pks      = JArrayHelper::getColumn($items, 'id');
            $elements = $this->getRevisionCount($pks);

            foreach ($items AS $item)
            {
                $item->revision_count = $elements[$item->id];
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
     * Counts the revisions of the given files
     *
     * @param    array    $pks      The files to count the revisions of
     *
     * @retun    array    $count    The revision count
     */
    public function getRevisionCount($pks)
    {
        $query = $this->_db->getQuery(true);

        if (!is_array($pks) || !count($pks)) return array();

        // Count sub-folders
        $query->select('parent_id, COUNT(id) AS revision_count')
              ->from('#__pf_repo_note_revs')
              ->where('parent_id IN(' . implode(',', $pks) . ')');


        $query->group('parent_id');
        $this->_db->setQuery($query);

        try {
            $rev_count = $this->_db->loadAssocList('parent_id', 'revision_count');
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

            if (isset($rev_count[$pk])) $count[$pk] += $rev_count[$pk];
        }

        return $count;
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
        $dir_id = JRequest::getCmd('filter_parent_id', JRequest::getCmd('filter_dir_id', ''));
        $this->setState('filter.dir_id', $dir_id);

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
        $id .= ':' . $this->getState('filter.dir_id');

        return parent::getStoreId($id);
    }
}
