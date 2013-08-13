<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfcomments
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.modellist');


/**
 * Class supporting a list of comments.
 *
 */
class PFcommentsModelComments extends JModelList
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
                'a.project_id', 'project_title',
                'a.title',
                'a.description',
                'a.context',
                'a.created',
                'a.created_by', 'author_name',
                'a.modified',
                'a.modified_by',
                'a.checked_out',
                'a.checked_out_time',
                'a.access', 'access_level',
                'a.state'
            );
        }

        parent::__construct($config);
    }


    /**
     * Build a list of authors
     *
     * @return    array
     */
    public function getAuthors()
    {
        // Create a new query object.
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        if ((int) $this->getState('filter.project') == 0) {
            return array();
        }

        // Construct the query
        $query->select('u.id AS value, u.name AS text')
              ->from('#__users AS u')
              ->join('INNER', '#__pf_comments AS a ON a.created_by = u.id')
              ->group('u.id')
              ->order('u.name');

        // Setup the query
        $db->setQuery((string) $query);

        // Return the result
        return $db->loadObjectList();
    }


    /**
     * Build a list of contexts
     *
     * @return    array
     */
    public function getContexts()
    {
        $query = $this->_db->getQuery(true);

        // Construct the query
        $query->select('DISTINCT a.context')
              ->from('#__pf_comments AS a')
              ->where('a.alias != ' . $this->_db->quote('root'))
              ->order('a.context ASC');

        // Setup the query
        $this->_db->setQuery($query, 0, 50);
        $items = (array) $this->_db->loadColumn();

        $options = array();

        foreach($items AS $value)
        {
            $obj     = new stdClass();
            $context = str_replace('.', '_', strtoupper($value)) . '_TITLE';

            $obj->value = $value;
            $obj->text  = JText::_($context);

            $options[] = $obj;
        }

        // Return the result
        return $options;
    }


    /**
     * Build a list of context items
     *
     * @return    array
     */
    public function getContextItems()
    {
        $query   = $this->_db->getQuery(true);
        $context = $this->getState('filter.context');
        $project = $this->getState('filter.project');

        // Context and project filters must be set.
        if (empty($context) || intval($project) <= 0) {
            return array();
        }

        // Construct the query
        $query->select('a.item_id AS value, a.title AS text')
              ->from('#__pf_comments AS a')
              ->where('a.context = ' . $this->_db->quote($context))
              ->where('a.project_id = ' . $this->_db->quote($project))
              ->where('a.alias != ' . $this->_db->quote('root'))
              ->group('a.item_id')
              ->order('a.title ASC');

        // Return the result
        $this->_db->setQuery($query, 0, 50);
        return (array) $this->_db->loadObjectList();
    }


    /**
     * Build an SQL query to load the list data.
     *
     * @return    jdatabasequery
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $query = $this->_db->getQuery(true);
        $user  = JFactory::getUser();

        // Get possible filters
        $filter_state   = $this->getState('filter.published');
        $filter_project = $this->getState('filter.project');
        $filter_author  = $this->getState('filter.author_id');
        $filter_context = $this->getState('filter.context');
        $filter_item    = $this->getState('filter.item_id');
        $filter_search  = $this->getState('filter.search');

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.project_id, a.title, a.description, a.checked_out, '
                . 'a.context, a.checked_out_time, a.state, a.created, a.created_by, '
                . 'a.parent_id, a.lft, a.rgt, a.level'
            )
        );

        $query->from('#__pf_comments AS a');

        // Join over the users for the checked out user.
        $query->select('uc.name AS editor');
        $query->join('LEFT', '#__users AS uc ON uc.id = a.checked_out');

        // Join over the users for the author.
        $query->select('ua.name AS author_name');
        $query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

        // Join over the projects for the project title.
        $query->select('p.title AS project_title');
        $query->join('LEFT', '#__pf_projects AS p ON p.id = a.project_id');

        $query->where('a.alias != ' .  $this->_db->quote('root'));

        // Filter by project
        if (is_numeric($filter_project) && $filter_project > 0) {
            $query->where('a.project_id = ' . (int) $filter_project);
        }

        // Filter by published state
        if (is_numeric($filter_state)) {
            $query->where('a.state = ' . (int) $filter_state);
        }
        elseif ($filter_state === '') {
            $query->where('(a.state = 0 OR a.state = 1)');
        }

        // Filter by author
        if (is_numeric($filter_author)) {
            $type = $this->getState('filter.author_id.include', true) ? '= ' : '<>';
            $query->where('a.created_by ' . $type . (int) $filter_author);
        }

        // Filter by context
        if (!empty($filter_context)) {
            $query->where('a.context = ' . $this->_db->quote($this->_db->escape($filter_context)));
        }

        // Filter by item_id
        if (is_numeric($filter_item)) {
            $query->where('a.item_id = ' . (int) $filter_item);
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
                $search = $this->_db->quote($this->_db->escape($filter_search, true) . '%');
                $query->where('(a.title LIKE ' . $search . ' OR a.alias LIKE ' . $search . ')');
            }
        }

        // Add the list ordering clause.
        $order_col = $this->state->get('list.ordering', 'a.created');
        $order_dir = $this->state->get('list.direction', 'desc');

        if ($order_col != 'a.lft') {
            $order_col = $order_col .  ' ' . $order_dir . ', a.lft';
        }

        $query->order($this->_db->escape($order_col . ' ' . $order_dir));

        // Group by clause
        $query->group('a.id');

        return $query;
    }


    /**
     * Method to auto-populate the model state.
     * Note: Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState($ordering = 'a.created', $direction = 'desc')
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

        // Filter - Context
        $context = $this->getUserStateFromRequest($this->context . '.filter.context', 'filter_context', '');
        $this->setState('filter.context', $context);

        // Filter - Item
        $item_id = $this->getUserStateFromRequest($this->context . '.filter.item_id', 'filter_item_id', '');
        $this->setState('filter.item_id', $item_id);

        // Filter - Project
        $project = PFApplicationHelper::getActiveProjectId('filter_project');
        $this->setState('filter.project', $project);

        // Disable author filter if no project is selected
        if (!$project) {
            $this->setState('filter.author_id', '');
        }

        // Do no allow to filter by item id if no context or project is given
        if (empty($context) || intval($project) == 0) {
            $this->setState('filter.item_id', '');
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
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.author_id');
        $id .= ':' . $this->getState('filter.project');
        $id .= ':' . $this->getState('filter.context');
        $id .= ':' . $this->getState('filter.item_id');

        return parent::getStoreId($id);
    }
}
