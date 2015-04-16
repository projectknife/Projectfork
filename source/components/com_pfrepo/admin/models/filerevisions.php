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
 * Methods supporting a list of file revisions.
 *
 */
class PFrepoModelFileRevisions extends JModelList
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
                'a.id',
                'a.ordering'
            );
        }

        parent::__construct($config);
    }


    /**
     * Method to load the file head revision
     *
     * @param     integer    $pk    The file id
     *
     * @return    mixed             Record object on success, False on error
     */
    public function getItem($pk = 0)
    {
        $pk    = (int) (empty($pk) ? $this->getState('filter.id') : $pk);
        $cfg   = array('ignore_request' => true);
        $model = $this->getInstance('File', 'PFrepoModel', $cfg);

        if (empty($pk)) return false;

        $item = $model->getItem($pk);

        if ($item) {
            $query = $this->_db->getQuery(true);
            $uid   = ($item->modified_by > 0 ? $item->modified_by : $item->created_by);

            $query->select('name')
                  ->from('#__users')
                  ->where('id = ' . (int) $uid);

            $this->_db->setQuery($query);
            $item->author_name = $this->_db->loadResult();

            if ($item->modified != $this->_db->getNullDate()) {
                $item->created = $item->modified;
            }
        }

        return $item;
    }


    /**
     * Method to get an array of data items.
     *
     * @return    mixed    An array of data items on success, false on failure.
     */
    public function getItems()
    {
        if ((int) $this->getState('filter.id') <= 0) {
            return array();
        }

        // Get a storage key.
        $store = $this->getStoreId();

        // Try to load the data from internal storage.
        if (isset($this->cache[$store])) {
            return $this->cache[$store];
        }

        // Load the list items.
        $query = $this->_getListQuery();
        $items = $this->_getList($query, 0, 0);

        // Check for a database error.
        if ($this->_db->getErrorNum()) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        // Add the items to the internal cache.
        $this->cache[$store] = $items;

        return $this->cache[$store];
    }


    /**
     * Build a list of project authors
     *
     * @return    array
     */
    public function getAuthors()
    {
        // Load only if we have a file id
        $id = (int) $this->getState('filter.id');

        if ($id <= 0) return array();

        $query = $this->_db->getQuery(true);

        // Construct the query
        $query->select('u.id AS value, u.name AS text')
              ->from('#__users AS u')
              ->join('INNER', '#__pf_repo_file_revs AS a ON a.created_by = u.id')
              ->where('a.parent_id = ' . $id)
              ->group('u.id')
              ->order('u.name');

        // Return the result
        $this->_db->setQuery($query, 0, 50);
        return $this->_db->loadObjectList();
    }


    /**
     * Build an SQL query to load the list data.
     *
     * @return    object
     */
    protected function getListQuery()
    {
        $query = $this->_db->getQuery(true);
        $user  = JFactory::getUser();

        // Get possible filters
        $filter_author = $this->getState('filter.author_id');
        $filter_search = $this->getState('filter.search');
        $filter_id     = $this->getState('filter.id');

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.project_id, a.parent_id, a.title, a.alias, a.description, '
                . 'a.file_name, a.file_extension, a.file_size, a.created, a.created_by, '
                . 'a.attribs, a.ordering'
            )
        );

        $query->from('#__pf_repo_file_revs AS a');

        // Join over the users for the author.
        $query->select('ua.name AS author_name')
              ->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

        // Filter by file id
        $query->where('parent_id = ' . (int) $filter_id);

        // Filter by author
        if (is_numeric($filter_author)) {
            $type = $this->getState('filter.author_id.include', true) ? '= ' : '<>';
            $query->where('a.created_by ' . $type . (int) $filter_author);
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
        $order_col = $this->state->get('list.ordering', 'a.ordering');
        $order_dir = $this->state->get('list.direction', 'desc');

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
    protected function populateState($ordering = 'a.ordering', $direction = 'desc')
    {
        // Initialise variables.
        $params = JComponentHelper::getParams('com_pfrepo');
        $app    = JFactory::getApplication();

        // Adjust the context to support modal layouts.
        if ($layout = JRequest::getVar('layout')) $this->context .= '.' . $layout;

        // Filter - Search
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        // Filter - Author
        $author_id = $app->getUserStateFromRequest($this->context . '.filter.author_id', 'filter_author_id');
        $this->setState('filter.author_id', $author_id);

        // Filter - File id
        $id = JRequest::getUint('id');
        $this->setState('filter.id', $id);

        // List state information.
        parent::populateState($ordering, $direction);
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
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.author_id');
        $id .= ':' . $this->getState('filter.id');

        return parent::getStoreId($id);
    }
}
