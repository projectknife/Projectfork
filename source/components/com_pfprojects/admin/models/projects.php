<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfprojects
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.modellist');


/**
 * Methods supporting a list of project records.
 *
 */
class PFprojectsModelProjects extends JModelList
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
                'a.title',
                'a.alias',
                'c.title', 'category_title',
                'a.created',
                'a.created_by', 'author_name',
                'a.modified',
                'a.modified_by',
                'a.access', 'access_level',
                'a.state',
                'a.start_date',
                'a.end_date'
            );
        }

        parent::__construct($config);
    }


    /**
     * Build a list of authors
     *
     * @return    array    The author list
     */
    public function getAuthors()
    {
        $query = $this->_db->getQuery(true);

        // Construct the query
        $query->select('u.id AS value, u.name AS text')
              ->from('#__users AS u')
              ->join('INNER', '#__pf_projects AS a ON a.created_by = u.id')
              ->group('u.id')
              ->order('u.name ASC');

        // Return the result
        $this->_db->setQuery($query, 0, 50);
        return (array) $this->_db->loadObjectList();
    }


    /**
     * Method to auto-populate the model state.
     * Note: Calling getState in this method will result in recursion.
     *
     * @param     string    $ordering     Default field to sort the items by
     * @param     string    $direction    Default list sorting direction
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

        // Filter - State
        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        // Filter - Category
        $category = $this->getUserStateFromRequest($this->context . '.filter.category', 'filter_category', '');
        $this->setState('filter.category', $category);

        // Filter - Access
        $access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', '');
        $this->setState('filter.access', $access);

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
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.access');
        $id .= ':' . $this->getState('filter.author_id');
        $id .= ':' . $this->getState('filter.category');

        return parent::getStoreId($id);
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
        $filter_state  = $this->getState('filter.published');
        $filter_cat    = $this->getState('filter.category');
        $filter_access = $this->getState('filter.access');
        $filter_author = $this->getState('filter.author_id');
        $filter_search = $this->getState('filter.search');

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.title, a.alias, a.checked_out, a.checked_out_time,'
                . 'a.state, a.access, a.created, a.created_by,'
                . 'a.start_date, a.end_date'
            )
        );

        $query->from('#__pf_projects AS a');

        // Join over the users for the checked out user.
        $query->select('uc.name AS editor')
              ->join('LEFT', '#__users AS uc ON uc.id = a.checked_out');

        // Join over the asset groups.
        $query->select('ag.title AS access_level')
              ->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

        // Join over the users for the author name.
        $query->select('ua.name AS manager_name, ua.name AS author_name')
              ->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

        // Join over the categories.
        $query->select('c.title AS category_title')
              ->join('LEFT', '#__categories AS c ON c.id = a.catid');

        // Filter by access level.
        if ($filter_access) {
            $query->where('a.access = ' . (int) $filter_access);
        }

        // Implement View Level Access
        if (!$user->authorise('core.admin')) {
            $levels = implode(',', $user->getAuthorisedViewLevels());

            $query->where('a.access IN (' . $levels . ')');
        }

        // Filter by published state
        if (is_numeric($filter_state)) {
            $query->where('a.state = ' . (int) $filter_state);
        }
        elseif ($filter_state === '') {
            $query->where('(a.state = 0 OR a.state = 1)');
        }

        // Filter by a single or group of categories.
        if (is_numeric($filter_cat)) {
            $filter_cat = (int) $filter_cat;

            $cat_tbl = JTable::getInstance('Category', 'JTable');
            $cat_tbl->load($filter_cat);

            $query->where('c.lft >= ' . (int) $cat_tbl->lft);
            $query->where('c.rgt <= ' . (int) $cat_tbl->rgt);
        }
        elseif (is_array($filter_cat)) {
            JArrayHelper::toInteger($filter_cat);

            $query->where('a.catid IN (' . implode(',', $filter_cat) . ')');
        }

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
                $search = $this->_db->quote('%' . $this->_db->escape(substr($filter_search, 7), true) . '%');
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

        $query->order($this->_db->escape($order_col . ' ' . $order_dir));

        return $query;
    }
}
