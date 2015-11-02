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
 * This models supports retrieving lists of notes.
 *
 */
class PFrepoModelNotes extends JModelList
{

    /**
     * Constructor.
     *
     * @param    array          $config    An optional associative array of configuration settings.
     * @see      jcontroller
     */
    public function __construct($config = array())
    {
        // Set field filter
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
     * Get the master query for retrieving a list of items subject to the model state.
     *
     * @return    jdatabasequery
     */
    public function getListQuery()
    {
        $query = $this->_db->getQuery(true);
        $user  = JFactory::getUser();

        // Get possible filters
        $filter_project = $this->getState('filter.project');
        $filter_access  = $this->getState('filter.access');
        $filter_author  = $this->getState('filter.author_id');
        $filter_search  = $this->getState('filter.search');
        $filter_dir     = $this->getState('filter.dir_id');
        $filter_labels  = $this->getState('filter.labels');

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.project_id, a.title, a.alias, a.description, a.checked_out, '
                . 'a.checked_out_time, a.created, a.access, a.created_by, a.dir_id, '
                . 'a.attribs'
            )
        );

        $query->from('#__pf_repo_notes AS a');

        // Join over the directory for path
        $query->select('d.title AS dir_title, d.alias AS dir_alias, d.lft, d.rgt, d.path')
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
        $query->select('p.title AS project_title, p.alias AS project_alias')
              ->join('LEFT', '#__pf_projects AS p ON p.id = a.project_id');

        // Join over the label refs for label count
        $query->select('COUNT(DISTINCT lbl.id) AS label_count')
              ->join('LEFT', '#__pf_ref_labels AS lbl ON (lbl.item_id = a.id '
                           . 'AND lbl.item_type = ' . $this->_db->quote('com_pfrepo.note') . ')');

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

        // Filter by labels
        if (count($filter_labels)) {
            JArrayHelper::toInteger($labels);

            if (count($labels) > 1) {
                $query->where('lbl.label_id IN (' . implode(', ', $labels) . ')');
            }
            else {
                $query->where('lbl.label_id = ' . (int) implode(', ', $labels));
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
        $order_dir = $this->state->get('list.direction', 'desc');

        $query->order($this->_db->escape($order_col . ' ' . $order_dir))
              ->group('a.id');

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
        $items  = parent::getItems();
        $labels = $this->getInstance('Labels', 'PFModel');

        // Get revision count
        $pks      = JArrayHelper::getColumn($items, 'id');
        $elements = $this->getRevisionCount($pks);

        foreach ($items as $i => &$item)
        {
            // Convert the parameter fields into objects.
            $params = new JRegistry;
            $params->loadString($item->attribs);

            $item->params = clone $this->getState('params');

            // Create slugs
            $item->slug         = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
            $item->project_slug = $item->project_alias ? ($item->project_id . ':' . $item->project_alias) : $item->project_id;
            $item->dir_slug     = $item->dir_alias ? ($item->dir_id . ':' . $item->dir_alias) : $item->dir_id;

            // Get the labels
            if ($item->label_count > 0) {
                $item->labels = $labels->getConnections('com_pfrepo.note', $item->id);
            }

            // Revision count
            $item->revision_count = $elements[$item->id];
        }

        return $items;
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
     * Note. Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState($ordering = 'a.title', $direction = 'ASC')
    {
        // Initialise variables.
        $app    = JFactory::getApplication();
        $params = $app->getParams();

        // Adjust the context to support modal layouts.
        if ($layout = JRequest::getVar('layout')) $this->context .= '.' . $layout;

        // Set Params
        $this->setState('params', $params);

        // Filter - Search
        $search = JRequest::getString('filter_search', '');
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

        // Filter - Labels
        $labels = (array) JRequest::getVar('filter_label', array(), 'post', 'array');
        $this->setState('filter.labels', $labels);

        // Do not allow to filter by author if no project is selected
        if ($project <= 0) {
            $this->setState('filter.author', '');
            $this->setState('filter.labels', array());

            $author_id = '';
            $labels    = array();
        }

        // Filter - Is set
        $this->setState('filter.isset', (!empty($search) || is_numeric($author_id) || count($labels)));

        // Call parent method
        parent::populateState($ordering, $direction);
    }

    /**
     * Method to get the starting number of items for the data set.
     *
     * @return  integer  The starting number of items available in the data set.
     */
    public function getStart()
    {
        $store = $this->getStoreId('getstart');

        // Try to load the data from internal storage.
        if (isset($this->cache[$store]))
        {
            return $this->cache[$store];
        }

        $start = $this->getState('list.start');
        $limit = $this->getState('list.limit');
        $total = $this->getTotal();

        if ($start > $total)
        {
            $start = max(0, (int) (ceil($total / $limit) - 1) * $limit);
        }

        // Add the total to the internal cache.
        $this->cache[$store] = $start;

        return $this->cache[$store];
    }


    /**
     * Method to get a store id based on model configuration state.
     *
     * @param     string    $id    A prefix for the store id.
     *
     * @return    string           A store id.
     */
    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.access');
        $id .= ':' . $this->getState('filter.author_id');
        $id .= ':' . $this->getState('filter.project');
        $id .= ':' . $this->getState('filter.dir_id');
        $id .= ':' . serialize($this->getState('filter.labels'));

        return parent::getStoreId($id);
    }
}
