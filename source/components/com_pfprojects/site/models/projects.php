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
jimport('joomla.application.component.helper');


/**
 * This models supports retrieving lists of projects.
 *
 */
class PFprojectsModelProjects extends JModelList
{
    /**
     * Constructor.
     *
     * @param    array  $config        An optional associative array of configuration settings.
     */
    public function __construct($config = array())
    {
        // Set field filter
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'a.id',
                'category_title, a.title', 'category_title',
                'a.created',
                'a.modified',
                'a.state',
                'a.start_date',
                'a.end_date',
                'author_name',
                'editor',
                'access_level',
                'milestones',
                'tasks',
                'tasklists'
            );
        }

        parent::__construct($config);
    }


    /**
     * Method to get a list of items.
     * Overriden to inject convert the attribs field into a JParameter object.
     *
     * @return    mixed    $items    An array of objects on success, false on failure.
     */
    public function getItems()
    {
        $items     = parent::getItems();
        $base_path = JPATH_ROOT . '/media/com_projectfork/repo/0/logo';
        $base_url  = JURI::root(true) . '/media/com_projectfork/repo/0/logo';

        $tasks_exists = PFApplicationHelper::enabled('com_pftasks');
        $repo_exists  = PFApplicationHelper::enabled('com_pfrepo');

        $pks = JArrayHelper::getColumn($items, 'id');

        // Get aggregate data
        $progress        = array();
        $total_tasks     = array();
        $completed_tasks = array();
        $total_files     = array();

        if ($tasks_exists) {
            JLoader::register('PFtasksModelTasks', JPATH_SITE . '/components/com_pftasks/models/tasks.php');

            $tmodel      = JModelLegacy::getInstance('Tasks', 'PFtasksModel', array('ignore_request' => true));
            $progress    = $tmodel->getAggregatedProgress($pks, 'project_id');
            $total_tasks = $tmodel->getAggregatedTotal($pks, 'project_id');
            $completed_tasks = $tmodel->getAggregatedTotal($pks, 'project_id', 1);
        }

        if ($repo_exists) {
            JLoader::register('PFrepoModelFiles', JPATH_SITE . '/components/com_pfrepo/models/files.php');

            $fmodel      = JModelLegacy::getInstance('Files', 'PFrepoModel', array('ignore_request' => true));
            $total_files = $fmodel->getProjectCount($pks);
        }

        // Loop over each row to inject data
        foreach ($items as $i => &$item)
        {
            $params = new JRegistry;
            $params->loadString($item->attribs);

            // Convert the parameter fields into objects.
            $items[$i]->params = clone $this->getState('params');

            // Create slug
            $items[$i]->slug = $items[$i]->alias ? ($items[$i]->id . ':' . $items[$i]->alias) : $items[$i]->id;

            // Try to find the logo img
            $items[$i]->logo_img = null;

            if (JFile::exists($base_path . '/' . $item->id . '.jpg')) {
                $items[$i]->logo_img = $base_url . '/' . $item->id . '.jpg';
            }
            elseif (JFile::exists($base_path . '/' . $item->id . '.jpeg')) {
                $items[$i]->logo_img = $base_url . '/' . $item->id . '.jpeg';
            }
            elseif (JFile::exists($base_path . '/' . $item->id . '.png')) {
                $items[$i]->logo_img = $base_url . '/' . $item->id . '.png';
            }
            elseif (JFile::exists($base_path . '/' . $item->id . '.gif')) {
                $items[$i]->logo_img = $base_url . '/' . $item->id . '.gif';
            }

            // Inject task count
            $items[$i]->tasks = (isset($total_tasks[$item->id]) ? $total_tasks[$item->id] : 0);

            // Inject completed task count
            $items[$i]->completed_tasks = (isset($completed_tasks[$item->id]) ? $completed_tasks[$item->id] : 0);

            // Inject progress
            $items[$i]->progress = (isset($progress[$item->id]) ? $progress[$item->id] : 0);

            // Inject attached files
            $items[$i]->attachments = (isset($total_files[$item->id]) ? $total_files[$item->id] : 0);
        }

        return $items;
    }


    /**
     * Get the master query for retrieving a list of items subject to the model state.
     *
     * @return    jdatabasequery
     */
    protected function getListQuery()
    {
        $query = $this->_db->getQuery(true);
        $user  = JFactory::getUser();

        // Get possible filters
        $filter_cat    = $this->getState('filter.category');
        $filter_state  = $this->getState('filter.published');
        $filter_author = $this->getState('filter.author');
        $filter_search = $this->getState('filter.search');

        // Select the required fields from the table.
        $query->select(
            $this->getState('list.select',
                'a.id, a.asset_id, a.catid, a.title, a.alias, a.description, a.created, '
                . 'a.created_by, a.modified, a.modified_by, a.checked_out, '
                . 'a.checked_out_time, a.attribs, a.access, a.state, a.start_date, '
                . 'a.end_date'
            )
        );

        $query->from('#__pf_projects AS a');

        // Join over the users for the checked out user.
        $query->select('uc.name AS editor')
              ->join('LEFT', '#__users AS uc ON uc.id = a.checked_out');

        // Join over the asset groups.
        $query->select('ag.title AS access_level')
              ->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

        // Join over the users for the owner.
        $query->select('ua.name AS author_name, ua.email AS author_email')
              ->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

        // Join over the milestones for milestone count
        $query->select('COUNT(DISTINCT ma.id) AS milestones')
              ->join('LEFT', '#__pf_milestones AS ma ON ma.project_id = a.id');

        // Join over the categories.
        $query->select('c.title AS category_title')
              ->join('LEFT', '#__categories AS c ON c.id = a.catid');

        // Join over the task lists for list count
        $query->select('COUNT(DISTINCT tl.id) AS tasklists')
              ->join('LEFT', '#__pf_task_lists AS tl ON tl.project_id = a.id');

        // Join over the observer table for email notification status
        if ($user->get('id') > 0) {
            $query->select('COUNT(DISTINCT obs.user_id) AS watching')
                  ->join('LEFT', '#__pf_ref_observer AS obs ON (obs.item_type = '
                  . $this->_db->quote('com_pfprojects.project') . ' AND obs.item_id = a.id AND obs.user_id = '
                  . $this->_db->quote($user->get('id')) . ')'
            );
        }

        // Join over the comments for comment count
        $query->select('COUNT(DISTINCT co.id) AS comments')
              ->join('LEFT', '#__pf_comments AS co ON (co.context = '
              . $this->_db->quote('com_pfprojects.project') . ' AND co.item_id = a.id)');

        // Implement View Level Access
        if (!$user->authorise('core.admin')) {
            $levels = implode(',', $user->getAuthorisedViewLevels());

            $query->where('a.access IN (' . $levels . ')');
        }

        // Filter by a single or group of categories.
        $baselevel = 1;
        if (is_numeric($filter_cat)) {
            $filter_cat = (int) $filter_cat;
            $cat_tbl    = JTable::getInstance('Category', 'JTable');

            if ($cat_tbl) {
                if ($cat_tbl->load($filter_cat)) {
                    $rgt       = $cat_tbl->rgt;
                    $lft       = $cat_tbl->lft;
                    $baselevel = (int) $cat_tbl->level;

                    $query->where('c.lft >= ' . (int) $lft);
                    $query->where('c.rgt <= ' . (int) $rgt);
                }
            }
        }
        elseif (is_array($filter_cat)) {
            JArrayHelper::toInteger($filter_cat);

            $filter_cat = implode(',', $filter_cat);
            $query->where('a.catid IN (' . $filter_cat . ')');
        }

        // Filter fields
        $filters = array();
        $filters['a.state']      = array('STATE',       $this->getState('filter.published'));
        $filters['a.created_by'] = array('INT-NOTZERO', $this->getState('filter.author'));
        $filters['a']            = array('SEARCH',      $this->getState('filter.search'));

        // Apply Filter
        PFQueryHelper::buildFilter($query, $filters);

        // Group by ID
        $query->group('a.id');

        // Add the list ordering clause.
        $sort = $this->getState('list.ordering', 'category_title, a.title');
        $dir  = $this->getState('list.direction', 'ASC');

        if (empty($sort)) {
            $sort = 'category_title, a.title';
        }

        if (empty($dir)) {
            $dir = 'ASC';
        }

        $query->order($sort . ' ' . $dir);

        return $query;
    }


    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState($ordering = 'category_title, a.title', $direction = 'ASC')
    {
        $app     = JFactory::getApplication();
		$params  = $app->getParams();
        $menu    = $app->getMenu()->getActive();
        $itemid  = $app->input->get('Itemid', 0, 'int');

        // Merge app params with menu item params
		if ($menu) {
		    $menu_params = new JRegistry();

			$menu_params->loadString($menu->params);
            $clone_params = clone $menu_params;
            $clone_params->merge($params);

            if (!$itemid) {
                $itemid .= (int) $menu->id;
            }
		}

        $this->context .= '.' . $itemid;

        // Adjust the context to support modal layouts.
        $layout = JRequest::getCmd('layout');

        // View Layout
        $this->setState('layout', $layout);
        if ($layout && $layout != 'print') $this->context .= '.' . $layout;

        // Set params state
        $this->setState('params', $params);

        // State
        $state = $app->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', $params->get('filter_published'));
        $this->setState('filter.published', $state);

        // Filter on published for those who do not have edit or edit.state rights.
        $access = PFprojectsHelper::getActions();
        if (!$access->get('core.edit.state') && !$access->get('core.edit')) {
            $this->setState('filter.published', 1);
            $state = '';
        }

        // Filter - Search
        $search = JRequest::getString('filter_search', '');
        $this->setState('filter.search', $search);

        // Filter - Author
        $author = $app->getUserStateFromRequest($this->context . '.filter.author', 'filter_author', '');
        $this->setState('filter.author', $author);

        // Filter - Category
        $cat = $app->getUserStateFromRequest($this->context . '.filter.category', 'filter_category', $params->get('filter_category'));
        $this->setState('filter.category', $cat);

        // Filter - Is set
        $this->setState('filter.isset', (is_numeric($state) || !empty($search) || is_numeric($author) || is_numeric($cat)));

        // Set list limit
        $cfg   = JFactory::getConfig();
        $limit = $app->getUserStateFromRequest($this->context . '.list.limit', 'limit', $params->get('display_num', $cfg->get('list_limit')), 'uint');
        $this->setState('list.limit', $limit);
        $app->set('list_limit', $limit);
        JRequest::setVar('list_limit', $limit);

        // Set sorting order
        $sort = $app->getUserStateFromRequest($this->context . '.list.ordering', 'filter_order', $params->get('filter_order'));
        $this->setState('list.ordering', $sort);
        $app->set('filter_order', $sort);
        JRequest::setVar('filter_order', $sort);

        // Set order direction
        $dir = $app->getUserStateFromRequest($this->context . '.list.direction', 'filter_order_Dir', $params->get('filter_order_Dir'));
        $this->setState('list.direction', $dir);
        $app->set('filter_order_Dir', $dir);
        JRequest::setVar('filter_order_Dir', $dir);

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
     *
     * @return    string           A store id.
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.author');
        $id .= ':' . $this->getState('filter.category');
        $id .= ':' . $this->getState('filter.search');

        return parent::getStoreId($id);
    }
}
