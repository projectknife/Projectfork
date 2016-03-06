<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfmilestones
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.modellist');
jimport('joomla.application.component.helper');


/**
 * This models supports retrieving lists of milestones.
 *
 */
class PFmilestonesModelMilestones extends JModelList
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
                'a.id', 'project_title, a.title', 'a.created', 'a.modified',
                'a.checked_out', 'a.checked_out_time',
                'a.state', 'a.start_date', 'a.end_date',
                'author_name', 'editor', 'access_level',
                'project_title', 'tasklists', 'tasks'
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
        $db    = $this->getDbo();
        $query = $db->getQuery(true);
        $user  = JFactory::getUser();

        // Select the required fields from the table.
        $query->select(
            $this->getState('list.select',
                'a.id, a.asset_id, a.project_id, a.title, a.alias, a.description, a.created,'
                . 'a.created_by, a.modified, a.modified_by, a.checked_out,'
                . 'a.checked_out_time, a.attribs, a.access, a.state, a.start_date,'
                . 'a.end_date'
            )
        );

        $query->from('#__pf_milestones AS a');

        // Join over the users for the checked out user
        $query->select('uc.name AS editor');
        $query->join('LEFT', '#__users AS uc ON uc.id = a.checked_out');

        // Join over the asset groups
        $query->select('ag.title AS access_level');
        $query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

        // Join over the users for the owner
        $query->select('ua.name AS author_name, ua.email AS author_email');
        $query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

        // Join over the projects for project title and alias
        $query->select('p.title AS project_title, p.alias AS project_alias');
        $query->join('LEFT', '#__pf_projects AS p ON p.id = a.project_id');

        // Join over the task lists for task list count
        $query->select('COUNT(DISTINCT tl.id) AS tasklists');
        $query->join('LEFT', '#__pf_task_lists AS tl ON tl.milestone_id = a.id');

        // Join over the label refs for label count
        $query->select('COUNT(DISTINCT lbl.id) AS label_count');
        $query->join('LEFT', '#__pf_ref_labels AS lbl ON (lbl.item_id = a.id AND lbl.item_type = ' . $db->quote('com_pfmilestones.milestone') . ')');

        // Join over the observer table for email notification status
        if ($user->get('id') > 0) {
            $query->select('COUNT(DISTINCT obs.user_id) AS watching');
            $query->join('LEFT', '#__pf_ref_observer AS obs ON (obs.item_type = ' . $db->quote('com_pfmilestones.milestone')
                               . ' AND obs.item_id = a.id AND obs.user_id = '
                               . $db->quote($user->get('id')) . ')'
                        );
        }

        // Join over the attachments for attachment count
        $query->select('COUNT(DISTINCT at.id) AS attachments');
        $query->join('LEFT', '#__pf_ref_attachments AS at ON (at.item_type = '
              . $db->quote('com_pfmilestones.milestone') . ' AND at.item_id = a.id)');

        // Join over the comments for comment count
        $query->select('COUNT(DISTINCT co.id) AS comments');
        $query->join('LEFT', '#__pf_comments AS co ON (co.context = '
              . $db->quote('com_pfmilestones.milestone') . ' AND co.item_id = a.id)');

        // Implement View Level Access
        if (!$user->authorise('core.admin')) {
            $levels = implode(',', $user->getAuthorisedViewLevels());

            $query->where('a.access IN (' . $levels . ')');
        }

        // Filter labels
        if (count($this->getState('filter.labels'))) {
            $labels = $this->getState('filter.labels');

            JArrayHelper::toInteger($labels);

            if (count($labels) > 1) {
                $labels = implode(', ', $labels);
                $query->where('lbl.label_id IN (' . $labels . ')');
            }
            else {
                $labels = implode(', ', $labels);
                $query->where('lbl.label_id = ' . $db->quote((int) $labels));
            }
        }

        // Filter fields
        $filters = array();
        $filters['a.state']      = array('STATE',       $this->getState('filter.published'));
        $filters['a.project_id'] = array('INT-NOTZERO', $this->getState('filter.project'));
        $filters['a.created_by'] = array('INT-NOTZERO', $this->getState('filter.author'));
        $filters['a']            = array('SEARCH',      $this->getState('filter.search'));

        // Apply Filter
        PFQueryHelper::buildFilter($query, $filters);

        // Group by ID
        $query->group('a.id');

        // Add the list ordering clause.
        $project = (int) $this->getState('filter.project');
        $order   = $this->getState('list.ordering', 'a.title');

        if ($order == '') {
            $order = 'a.title';
        }

        if ($project <= 0) {
            if ($order != 'project_title') {
                $order = 'project_title ASC, ' . $order;
            }
        }

        $query->order($order . ' ' . $this->getState('list.direction', 'ASC'));

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

        $tasks_exists = PFApplicationHelper::enabled('com_pftasks');

        $pks = JArrayHelper::getColumn($items, 'id');

        // Get aggregate data
        $progress        = array();
        $total_tasks     = array();
        $completed_tasks = array();

        if ($tasks_exists) {
            JLoader::register('PFtasksModelTasks', JPATH_SITE . '/components/com_pftasks/models/tasks.php');

            $tmodel      = JModelLegacy::getInstance('Tasks', 'PFtasksModel', array('ignore_request' => true));
            $progress    = $tmodel->getAggregatedProgress($pks, 'milestone_id');
            $total_tasks = $tmodel->getAggregatedTotal($pks, 'milestone_id');
            $completed_tasks = $tmodel->getAggregatedTotal($pks, 'milestone_id', 1);
        }

        foreach ($items as $i => &$item)
        {
            // Convert the parameter fields into objects.
            $params = new JRegistry;
            $params->loadString($item->attribs);

            $items[$i]->params = clone $this->getState('params');

            // Create slugs
            $items[$i]->slug         = $items[$i]->alias ? ($items[$i]->id . ':' . $items[$i]->alias) : $items[$i]->id;
            $items[$i]->project_slug = $items[$i]->project_alias ? ($items[$i]->project_id . ':' . $items[$i]->project_alias) : $items[$i]->project_id;

            // Get the labels
            if ($items[$i]->label_count > 0) {
                $items[$i]->labels = $labels->getConnections('com_pfmilestones.milestone', $items[$i]->id);
            }

            if (!isset($items[$i]->watching)) {
                $items[$i]->watching = 0;
            }

            // Inject task count
            $items[$i]->tasks = (isset($total_tasks[$item->id]) ? $total_tasks[$item->id] : 0);

            // Inject completed task count
            $items[$i]->completed_tasks = (isset($completed_tasks[$item->id]) ? $completed_tasks[$item->id] : 0);

            // Inject progress
            $items[$i]->progress = (isset($progress[$item->id]) ? $progress[$item->id] : 0);
        }

        return $items;
    }


    /**
     * Build a list of authors
     *
     * @return    array
     */
    public function getAuthors()
    {
        $db     = $this->getDbo();
        $query  = $db->getQuery(true);
        $user   = $user = JFactory::getUser();
        $access = PFmilestonesHelper::getActions();

        // Return empty array if no project is select
        $project = (int) $this->getState('filter.project');

        if ($project <= 0) {
            return array();
        }

        // Construct the query
        $query->select('u.id AS value, u.name AS text');
        $query->from('#__users AS u');
        $query->join('INNER', '#__pf_milestones AS a ON a.created_by = u.id');

        // Implement View Level Access
        if (!$access->get('core.admin', 'com_pfmilestones')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups . ')');
        }

        // Filter fields
        $filters = array();
        $filters['a.project_id'] = array('INT-NOTZERO', $this->getState('filter.project'));

        if (!$access->get('core.edit.state') && !$access->get('core.edit')) {
            $filters['a.state'] = array('STATE', '1');
        }

        // Apply Filter
        PFQueryHelper::buildFilter($query, $filters);

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
    protected function populateState($ordering = 'project_title, a.title', $direction = 'ASC')
    {
        $app    = JFactory::getApplication();
        $access = PFmilestonesHelper::getActions();

        // Adjust the context to support modal layouts.
        $layout  = JRequest::getCmd('layout');
        $params  = $app->getParams();
        $itemid  = $app->input->get('Itemid', 0, 'int');
        $menu    = $app->getMenu()->getActive();

        // Merge app params with menu item params
		if ($menu) {
		    $menu_params = new JRegistry();

			$menu_params->loadString($menu->params);
            $clone_params = clone $menu_params;
            $clone_params->merge($params);

            if (!$itemid) {
                $itemid = (int) $menu->id;
            }
		}

        // View Layout
        $this->setState('layout', $layout);
        if ($layout && $layout != 'print') $this->context .= '.' . $layout;

        $this->context .= '.' . $itemid;

        // Params
        $value = $app->getParams();
        $this->setState('params', $value);

        // State
        $state = $app->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', $params->get('filter_published'));
        $this->setState('filter.published', $state);

        // Filter on published for those who do not have edit or edit.state rights.
        if (!$access->get('core.edit.state') && !$access->get('core.edit')){
            $this->setState('filter.published', 1);
            $state = '';
        }

        // Filter - Search
        $search = JRequest::getString('filter_search', '');
        $this->setState('filter.search', $search);

        // Filter - Project
        $project = PFApplicationHelper::getActiveProjectId('filter_project');
        $this->setState('filter.project', $project);

        // Filter - Author
        $author = $app->getUserStateFromRequest($this->context . '.filter.author', 'filter_author', '');
        $this->setState('filter.author', $author);

        // Filter - Labels
        $labels = JRequest::getVar('filter_label', array());
        $this->setState('filter.labels', $labels);

        // Do not allow to filter by author if no project is selected
        if (!is_numeric($project) || intval($project) == 0) {
            $this->setState('filter.author', '');
            $this->setState('filter.labels', array());
            $author = '';
            $labels = array();
        }

        if (!is_array($labels)) {
            $labels = array();
        }

        // Filter - Is set
        $this->setState('filter.isset', (is_numeric($state) || !empty($search) || is_numeric($author) || count($labels)));

        // Set list limit
        $cfg   = JFactory::getConfig();
        $limit = $app->getUserStateFromRequest($this->context . '.list.limit', 'limit', $params->get('display_num', $cfg->get('list_limit')), 'uint');
        $this->setState('list.limit', $limit);
        $app->set('list_limit', $limit);
        JRequest::setVar('list_limit', $limit);

        // Set sorting order
        $ordering = $app->getUserStateFromRequest($this->context . '.list.ordering', 'filter_order', $params->get('filter_order'));
        $this->setState('list.ordering', $ordering);
        $app->set('filter_order', $ordering);
        JRequest::setVar('filter_order', $ordering);

        // Set order direction
        $direction = $app->getUserStateFromRequest($this->context . '.list.direction', 'filter_order_Dir', $params->get('filter_order_Dir'));
        $this->setState('list.direction', $direction);
        $app->set('filter_order_Dir', $direction);
        JRequest::setVar('filter_order_Dir', $direction);

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
        $id .= ':' . $this->getState('filter.author');
        $id .= ':' . $this->getState('filter.search');

        return parent::getStoreId($id);
    }
}
