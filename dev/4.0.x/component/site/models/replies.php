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
jimport('joomla.application.component.helper');


/**
 * This models supports retrieving lists of replies.
 *
 */
class ProjectforkModelReplies extends JModelList
{

    /**
     * Constructor.
     *
     * @param    array          $config    An optional associative array of configuration settings.
     * @see      jcontroller
     */
    public function __construct($config = array())
    {
        // Register dependencies
        JLoader::register('ProjectforkHelper',       JPATH_ADMINISTRATOR . '/components/com_projectfork/helpers/projectfork.php');
        JLoader::register('ProjectforkHelperAccess', JPATH_ADMINISTRATOR . '/components/com_projectfork/helpers/access.php');
        JLoader::register('ProjectforkHelperQuery',  JPATH_ADMINISTRATOR . '/components/com_projectfork/helpers/query.php');

        // Set field filter
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'a.id', 'topic_id', 'a.topic_id', 'a.created',
                'a.modified', 'a.checked_out', 'a.checked_out_time',
                'a.state', 'author_name', 'editor', 'access_level',
                'project_title', 'topic_title'
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
            $this->getState('list.select',
                'a.id, a.asset_id, a.project_id, a.topic_id, a.description, a.created, '
                . 'a.created_by, a.modified, a.modified_by, a.checked_out, '
                . 'a.checked_out_time, a.attribs, a.access, a.state'
            )
        );

        $query->from('#__pf_replies AS a');

        // Join over the users for the checked out user
        $query->select('uc.name AS editor');
        $query->join('LEFT', '#__users AS uc ON uc.id = a.checked_out');

        // Join over the asset groups
        $query->select('ag.title AS access_level');
        $query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

        // Join over the users for the owner
        $query->select('ua.name AS author_name, ua.email AS author_email');
        $query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

        // Join over the projects for project title
        $query->select('p.title AS project_title, p.alias AS project_alias');
        $query->join('LEFT', '#__pf_projects AS p ON p.id = a.project_id');

        // Join over the topics for topic title
        $query->select('t.title AS topic_title');
        $query->join('LEFT', '#__pf_topics AS t ON t.id = a.topic_id');

        // Implement View Level Access
        if (!$user->authorise('core.admin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups . ')');
        }

        // Filter fields
        $filters = array();
        $filters['a.state']      = array('STATE',       $this->getState('filter.published'));
        $filters['a.project_id'] = array('INT-NOTZERO', $this->getState('filter.project'));
        $filters['a.topic_id']   = array('INT-NOTZERO', $this->getState('filter.topic'));
        $filters['a.created_by'] = array('INT-NOTZERO', $this->getState('filter.author'));
        $filters['a']            = array('SEARCH',      $this->getState('filter.search'));

        // Apply Filter
        ProjectforkHelperQuery::buildFilter($query, $filters);

        // Group by ID
        $query->group('a.id');

        // Add the list ordering clause.
        $query->order($this->getState('list.ordering', 'a.created') . ' ' . $this->getState('list.direction', 'ASC'));

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
            $items[$i]->project_slug = $items[$i]->project_alias ? ($items[$i]->project_id.':' . $items[$i]->project_alias) : $items[$i]->project_id;
        }

        return $items;
    }


    /**
     * Gets the current topic data
     *
     * @return jdatabasequery
     */
    public function getTopic()
    {
        $id    = (int) $this->getState('filter.topic');
        $model = $this->getInstance('TopicForm', 'ProjectforkModel', array('ignore_request' => true));

        if ($id == 0) return false;

        $item = $model->getItem($id);

        if ($item->id > 0) {
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('a.title')
                  ->from('#__pf_projects AS a')
                  ->where('a.id = ' . $db->quote($item->project_id));

            $db->setQuery($query);
            $item->project_title = $db->loadResult();

            $query->clear();
            $query->select('a.name')
                  ->from('#__users AS a')
                  ->where('id = ' . $db->quote($item->created_by));

            $db->setQuery($query);
            $item->author_name = $db->loadResult();
        }

        return $item;
    }


    /**
     * Build a list of authors
     *
     * @return    jdatabasequery
     */
    public function getAuthors()
    {
        $db     = $this->getDbo();
        $query  = $db->getQuery(true);
        $user   = JFactory::getUser();
        $access = ProjectforkHelperAccess::getActions(NULL, 0, true);

        // Construct the query
        $query->select('u.id AS value, u.name AS text');
        $query->from('#__users AS u');
        $query->join('INNER', '#__pf_replies AS a ON a.created_by = u.id');

        // Implement View Level Access
        if (!$user->authorise('core.admin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups . ')');
        }

        // Filter fields
        $filters = array();
        $filters['a.project_id'] = array('INT-NOTZERO', $this->getState('filter.project'));
        $filters['a.topic_id']   = array('INT-NOTZERO', $this->getState('filter.topic'));

        if (!$access->get('reply.edit.state') && !$access->get('reply.edit')) {
            $filters['a.state'] = array('STATE', '1');
        }

        // Apply Filter
        ProjectforkHelperQuery::buildFilter($query, $filters);

        // Group and order
        $query->group('u.id');
        $query->order('u.name ASC');

        $db->setQuery((string) $query);
        $items = (array) $db->loadObjectList();

        // Return the items
        return $items;
    }


    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState($ordering = 'a.created', $direction = 'ASC')
    {
        $app = JFactory::getApplication();

        // Adjust the context to support modal layouts.
        $layout = JRequest::getCmd('layout');

        // View Layout
        $this->setState('layout', $layout);
        if ($layout) $this->context .= '.' . $layout;

        // Params
        $value = $app->getParams();
        $this->setState('params', $value);

        // State
        $state = $app->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $state);

        // Filter - Topic
        $topic = JRequest::getCmd('filter_topic', '');
        $this->setState('filter.topic', $topic);

        // Filter - Project
        $project = ProjectforkHelper::getActiveProjectId('filter_project');

        if (!$project && $topic > 0) {
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('project_id')
                  ->from('#__pf_topics')
                  ->where('id = ' . $db->quote((int) $topic));

            $db->setQuery($query);
            $project = (int) $db->loadResult();

            ProjectforkHelper::setActiveProject($project);
        }

        $this->setState('filter.project', $project);

        if ($topic) {
            $access = ProjectforkHelperAccess::getActions('topic', $topic);
        }
        elseif ($project) {
            $access = ProjectforkHelperAccess::getActions('project', $project);
        }
        else {
            $access = ProjectforkHelperAccess::getActions();
        }

        // Filter on published for those who do not have edit or edit.state rights.
        if (!$access->get('reply.edit.state') && !$access->get('reply.edit')){
            $this->setState('filter.published', 1);
            $state = '';
        }

        // Filter - Search
        $value = JRequest::getString('filter_search', '');
        $this->setState('filter.search', $value);

        // Filter - Author
        $author = $app->getUserStateFromRequest($this->context . '.filter.author', 'filter_author', '');
        $this->setState('filter.author', $author);

        // Do not allow to filter by author if no project is selected
        if (!is_numeric($project) || intval($project) == 0) {
            $this->setState('filter.author', '');
            $author = '';
        }

        // Filter - Is set
        $this->setState('filter.isset', (is_numeric($state) || !empty($search) || is_numeric($author)));

        // Call parent method
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
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.project');
        $id .= ':' . $this->getState('filter.topic');
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.author');

        return parent::getStoreId($id);
    }
}
