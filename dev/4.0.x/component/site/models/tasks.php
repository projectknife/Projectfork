<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
*
* This file is part of Projectfork.
*
* Projectfork is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*
* Projectfork is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Projectfork. If not, see <http://www.gnu.org/licenses/gpl.html>.
**/

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.modellist');


/**
 * This models supports retrieving lists of tasks.
 *
 */
class ProjectforkModelTasks extends JModelList
{

	/**
	 * Constructor.
	 *
	 * @param	array	An optional associative array of configuration settings.
	 * @see		JController
	 */
	public function __construct($config = array())
	{
	    // Include query helper class
	    require_once JPATH_BASE.DS.'components'.DS.'com_projectfork'.DS.'helpers'.DS.'query.php';

	    if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'created', 'a.created',
                'modified', 'a.modified',
                'checked_out', 'a.checked_out',
                'checked_out_time', 'a.checked_out_time',
                'state', 'a.state',
                'priority', 'a.priority',
                'complete', 'a.complete',
                'start_date', 'a.start_date',
                'end_date', 'a.end_date',
                'author_name',
                'editor',
                'access_level',
                'project_title',
                'milestone_title',
                'tasklist_title',
                'ordering', 'a.ordering',
                'parentid', 'a.parentid',
                'assigned'
			);
		}

		parent::__construct($config);
	}


	/**
	 * Method to auto-populate the model state.
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return	void
	 */
	protected function populateState($ordering = 'title', $direction = 'ASC')
	{
		$app  = JFactory::getApplication();
        $user = JFactory::getUser();

		// Query limit
		$value = JRequest::getUInt('limit', $app->getCfg('list_limit', 0));
		$this->setState('list.limit', $value);


        // Query limit start
		$value = JRequest::getUInt('limitstart', 0);
		$this->setState('list.start', $value);

        // Query order field
		$value = JRequest::getCmd('filter_order', 'a.ordering');
		if(!in_array($value, $this->filter_fields)) $value = 'a.ordering';
		$this->setState('list.ordering', $value);

        // Query order direction
		$value = JRequest::getCmd('filter_order_Dir', 'ASC');
		if(!in_array(strtoupper($value), array('ASC', 'DESC', ''))) $value = 'ASC';
		$this->setState('list.direction', $value);

        // Params
		$value = $app->getParams();
		$this->setState('params', $value);

        // State
        $value = JRequest::getCmd('filter_published', '');
        $this->setState('filter.published', $value);

        if ((!$user->authorise('core.edit.state', 'com_projectfork') && !$user->authorize('milestone.edit.state', 'com_projectfork')) &&
            (!$user->authorise('core.edit', 'com_projectfork') && !$user->authorize('milestone.edit', 'com_projectfork'))){
			// Filter on published for those who do not have edit or edit.state rights.
			$this->setState('filter.published', 1);
		}

        // Filter - Search
        $value = JRequest::getString('filter_search', '');
        $this->setState('filter.search', $value);

        // Filter - Project
        $value = (int) $this->getUserStateFromRequest('com_projectfork.project.active.id', 'filter_project', '');
        $this->setState('filter.project', $value);
        ProjectforkHelper::setActiveProject($value);

        // Filter - Milestone
        $value = JRequest::getInt('filter_milestone', '');
        $this->setState('filter.milestone', $value);

        // Filter - Task list
        $value = JRequest::getInt('filter_tasklist', '');
        $this->setState('filter.tasklist', $value);

        // Filter - Author
        $value = JRequest::getInt('filter_author', '');
        $this->setState('filter.author', $value);

        // Filter - Assigned User
        $value = JRequest::getInt('filter_assigned', '');
        $this->setState('filter.assigned', $value);

        // Filter - Priority
        $value = JRequest::getCmd('filter_priority', '');
        $this->setState('filter.priority', $value);

        // View Layout
		$this->setState('layout', JRequest::getCmd('layout'));
	}


	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string		$id	A prefix for the store id.
	 * @return	string		A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':'.$this->getState('filter.project');
		$id .= ':'.$this->getState('filter.milestone');
		$id .= ':'.$this->getState('filter.tasklist');
        $id .= ':'.$this->getState('filter.published');

		return parent::getStoreId($id);
	}


	/**
	 * Get the master query for retrieving a list of items subject to the model state.
	 *
	 * @return	JDatabaseQuery
	 */
	function getListQuery()
	{
		// Create a new query object.
		$db	   = $this->getDbo();
		$query = $db->getQuery(true);
		$user  = JFactory::getUser();

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.project_id, a.list_id, a.milestone_id, a.catid, a.title, '
                . 'a.description, a.alias, a.checked_out, a.attribs, a.priority, '
				. 'a.checked_out_time, a.state, a.access, a.created, a.created_by, '
				. 'a.start_date, a.end_date, a.ordering, a.complete, p.alias AS project_alias, '
                . 'tl.alias AS list_alias, m.alias AS milestone_alias'
			)
		);
		$query->from('#__pf_tasks AS a');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the asset groups.
		$query->select('ag.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

		// Join over the users for the author.
		$query->select('ua.name AS author_name');
		$query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

        // Join over the projects for the project title.
		$query->select('p.title AS project_title');
		$query->join('LEFT', '#__pf_projects AS p ON p.id = a.project_id');

        // Join over the task lists for the task list title, description, checked out, author
		$query->select('tl.title AS tasklist_title, tl.description AS tasklist_description, '
                       . 'tl.checked_out AS checked_out_list, tl.created_by AS list_created_by');
		$query->join('LEFT', '#__pf_task_lists AS tl ON tl.id = a.list_id');

        // Join over the milestones for the milestone title.
		$query->select('m.title AS milestone_title');
		$query->join('LEFT', '#__pf_milestones AS m ON m.id = a.milestone_id');

		// Implement View Level Access
		if(!$user->authorise('core.admin')) {
		    $groups	= implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN ('.$groups.')');
		}

        // Filter by assigned user
        $assigned = $this->getState('filter.assigned');
        if(is_numeric($assigned) && $assigned != 0) {
            $query->join('INNER', '#__pf_ref_users AS ru ON (ru.item_type = '.
                                   $db->quote('task').' AND ru.item_id = a.id)');
            $query->where('ru.user_id = '.(int)$assigned);
        }

        // Filter fields
        $filters = array();
        $filters['a.state']        = array('STATE',       $this->getState('filter.published'));
        $filters['a.project_id']   = array('INT-NOTZERO', $this->getState('filter.project'));
        $filters['a.milestone_id'] = array('INT-NOTZERO', $this->getState('filter.milestone'));
        $filters['a.list_id']      = array('INT-NOTZERO', $this->getState('filter.tasklist'));
        $filters['a.created_by']   = array('INT-NOTZERO', $this->getState('filter.author'));
        $filters['a.priority']     = array('INT',         $this->getState('filter.priority'));
        $filters['a']              = array('SEARCH',      $this->getState('filter.search'));

        // Apply Filter
        ProjectforkHelperQuery::buildFilter($query, $filters);

        // Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering', 'a.title');
		$orderDirn	= $this->state->get('list.direction', 'asc');

		if ($orderCol == 'a.title') {
			$orderCol = 'tl.title, p.title, m.title, a.ordering '.$orderDirn.', '.$orderCol;
		}
        if ($orderCol == 'a.ordering' || $orderCol == 'a.title') {
			$orderCol = 'tl.title, p.title, m.title '.$orderDirn.', '.$orderCol;
		}
        if($orderCol == 'project_title') {
            $orderCol = 'tl.title, m.title, a.title '.$orderDirn.', p.title';
        }
        if($orderCol == 'milestone_title') {
            $orderCol = 'p.title '.$orderDirn.', m.title';
        }
        if($orderCol == 'tasklist_title') {
            $orderCol = 'p.title, m.title '.$orderDirn.', tl.title';
        }

		$query->order($db->getEscaped($orderCol.' '.$orderDirn));

		return $query;
	}


	/**
	 * Method to get a list of items.
	 * Overriden to inject convert the attribs field into a JParameter object.
	 *
	 * @return	mixed	An array of objects on success, false on failure.
	 */
	public function getItems()
	{
	    JModel::addIncludePath(JPATH_SITE.'/components/com_projectfork/models', 'ProjectforkModel');

		$items = parent::getItems();
        $ref   = JModel::getInstance('UserRefs', 'ProjectforkModel');

		// Get the global params
		$global_params = JComponentHelper::getParams('com_projectfork', true);


		foreach ($items as $i => &$item)
		{
            $params = new JRegistry;
			$params->loadString($item->attribs);

            // Convert the parameter fields into objects.
			$items[$i]->params = clone $this->getState('params');

            // Get assigned users
            $items[$i]->users = $ref->getItems('task', $items[$i]->id);

            // Create item slugs
            $items[$i]->slug           = $items[$i]->alias ? ($items[$i]->id.':'.$items[$i]->alias) : $items[$i]->id;
            $items[$i]->project_slug   = $items[$i]->project_alias ? ($items[$i]->project_id.':'.$items[$i]->project_alias) : $items[$i]->project_id;
            $items[$i]->milestone_slug = $items[$i]->milestone_alias ? ($items[$i]->milestone_id.':'.$items[$i]->milestone_alias) : $items[$i]->milestone_id;
            $items[$i]->list_slug      = $items[$i]->list_alias ? ($items[$i]->list_id.':'.$items[$i]->list_alias) : $items[$i]->list_id;
        }

		return $items;
	}


    /**
	 * Build a list of project authors
	 *
	 * @return	JDatabaseQuery
	 */
	public function getAuthors()
    {
        // Return empty array if no project is select
        $project = $this->getState('filter.project');
        if(!is_numeric($project) || $project == 0) return array();

        // Create a new query object.
        $db    = $this->getDbo();
		$query = $db->getQuery(true);
        $user  = JFactory::getUser();

		// Construct the query
		$query->select('u.id AS value, u.name AS text, COUNT(DISTINCT a.id) AS count');
		$query->from('#__users AS u');
		$query->join('INNER', '#__pf_tasks AS a ON a.created_by = u.id');

        // Implement View Level Access
		if(!$user->authorise('core.admin')) {
		    $groups	= implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN ('.$groups.')');
		}

        // Filter by assigned user
        $assigned = $this->getState('filter.assigned');
        if(is_numeric($assigned) && $assigned != 0) {
            $query->join('INNER', '#__pf_ref_users AS ru ON (ru.item_type = '.
                                   $db->quote('task').' AND ru.item_id = a.id)');
            $query->where('ru.user_id = '.(int)$assigned);
        }

        // Filter fields
        $filters = array();
        $filters['a.state']        = array('STATE',       $this->getState('filter.published'));
        $filters['a.project_id']   = array('INT-NOTZERO', $this->getState('filter.project'));
        $filters['a.milestone_id'] = array('INT-NOTZERO', $this->getState('filter.milestone'));
        $filters['a.list_id']      = array('INT-NOTZERO', $this->getState('filter.tasklist'));
        $filters['a.priority']     = array('INT',         $this->getState('filter.priority'));
        $filters['a']              = array('SEARCH',      $this->getState('filter.search'));

        // Apply Filter
        ProjectforkHelperQuery::buildFilter($query, $filters);

        // Group and order
		$query->group('u.id');
		$query->order('u.name, count');

        // Get the results
		$db->setQuery($query->__toString());
        $items = (array) $db->loadObjectList();
        $count = count($items);

        for($i = 0; $i < $count; $i++)
        {
            $items[$i]->text .= ' ('.$items[$i]->count.')';
            unset($items[$i]->count);
        }


		// Return the items
		return $items;
	}


    /**
	 * Build a list of milestones
	 *
	 * @return	JDatabaseQuery
	 */
    public function getMilestones()
    {
        // Return empty array if no project is select
        $project = $this->getState('filter.project');
        if(!is_numeric($project) || $project == 0) return array();


        // Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
        $user  = JFactory::getUser();

		// Construct the query
		$query->select('m.id AS value, m.title AS text, COUNT(DISTINCT a.id) AS count');
		$query->from('#__pf_milestones AS m');
		$query->join('INNER', '#__pf_tasks AS a ON a.milestone_id = m.id');

        // Implement View Level Access
		if(!$user->authorise('core.admin')) {
		    $groups	= implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN ('.$groups.')');
		}

        // Filter by assigned user
        $assigned = $this->getState('filter.assigned');
        if(is_numeric($assigned) && $assigned != 0) {
            $query->join('INNER', '#__pf_ref_users AS ru ON (ru.item_type = '.
                                   $db->quote('task').' AND ru.item_id = a.id)');
            $query->where('ru.user_id = '.(int)$assigned);
        }

        // Filter fields
        $filters = array();
        $filters['a.state']        = array('STATE',       $this->getState('filter.published'));
        $filters['a.project_id']   = array('INT-NOTZERO', $this->getState('filter.project'));
        $filters['a.list_id']      = array('INT-NOTZERO', $this->getState('filter.tasklist'));
        $filters['a.created_by']   = array('INT-NOTZERO', $this->getState('filter.author'));
        $filters['a.priority']     = array('INT',         $this->getState('filter.priority'));
        $filters['a']              = array('SEARCH',      $this->getState('filter.search'));

        // Apply Filter
        ProjectforkHelperQuery::buildFilter($query, $filters);

        // Group and order
		$query->group('m.id');
		$query->order('m.title, count');

        // Get results
		$db->setQuery($query->__toString());
        $items = (array) $db->loadObjectList();
        $count = count($items);

        for($i = 0; $i < $count; $i++)
        {
            $items[$i]->text .= ' ('.$items[$i]->count.')';
            unset($items[$i]->count);
        }

		// Return the items
		return $items;
    }


    /**
	 * Build a list of task lists
	 *
	 * @return	JDatabaseQuery
	 */
    public function getTaskLists()
    {
        // Return empty array if no project is select
        $project = $this->getState('filter.project');
        if(!is_numeric($project) || $project == 0) return array();


        // Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
        $user  = JFactory::getUser();

		// Construct the query
		$query->select('t.id AS value, t.title AS text, COUNT(DISTINCT a.id) AS count');
		$query->from('#__pf_task_lists AS t');
		$query->join('INNER', '#__pf_tasks AS a ON a.list_id = t.id');

        // Implement View Level Access
		if(!$user->authorise('core.admin')) {
		    $groups	= implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN ('.$groups.')');
		}

        // Filter by assigned user
        $assigned = $this->getState('filter.assigned');
        if(is_numeric($assigned) && $assigned != 0) {
            $query->join('INNER', '#__pf_ref_users AS ru ON (ru.item_type = '.
                                   $db->quote('task').' AND ru.item_id = a.id)');
            $query->where('ru.user_id = '.(int)$assigned);
        }

        // Filter fields
        $filters = array();
        $filters['a.state']        = array('STATE',       $this->getState('filter.published'));
        $filters['a.project_id']   = array('INT-NOTZERO', $this->getState('filter.project'));
        $filters['a.milestone_id'] = array('INT-NOTZERO', $this->getState('filter.milestone'));
        $filters['a.created_by']   = array('INT-NOTZERO', $this->getState('filter.author'));
        $filters['a.priority']     = array('INT',         $this->getState('filter.priority'));
        $filters['a']              = array('SEARCH',      $this->getState('filter.search'));

        // Apply Filter
        ProjectforkHelperQuery::buildFilter($query, $filters);

        // Group and order
		$query->group('t.id');
		$query->order('t.title, count');

		// Get results
		$db->setQuery($query->__toString());
        $items = (array) $db->loadObjectList();
        $count = count($items);

        for($i = 0; $i < $count; $i++)
        {
            $items[$i]->text .= ' ('.$items[$i]->count.')';
            unset($items[$i]->count);
        }

		// Return the items
		return $items;
    }


    /**
	 * Build a list of assigned users
	 *
	 * @return	JDatabaseQuery
	 */
    public function getAssignedUsers()
    {
        // Return empty array if no project is select
        $project = $this->getState('filter.project');
        if(!is_numeric($project) || $project == 0) return array();

        // Create a new query object.
		$db    = $this->getDbo();
        $user  = JFactory::getUser();
		$query = $db->getQuery(true);

		// Construct the query
		$query->select('u.id AS value, u.name AS text, COUNT(DISTINCT a.id) AS count');
		$query->from('#__users AS u');
		$query->join('INNER', '#__pf_ref_users AS a ON a.user_id = u.id');
		$query->join('RIGHT', '#__pf_tasks AS t ON t.id = a.item_id');
		$query->where('a.item_type = '.$db->quote('task'));

        // Implement View Level Access
		if(!$user->authorise('core.admin')) {
		    $groups	= implode(',', $user->getAuthorisedViewLevels());
			$query->where('t.access IN ('.$groups.')');
		}

        // Filter fields
        $filters = array();
        $filters['t.state']        = array('STATE',       $this->getState('filter.published'));
        $filters['t.project_id']   = array('INT-NOTZERO', $this->getState('filter.project'));
        $filters['t.milestone_id'] = array('INT-NOTZERO', $this->getState('filter.milestone'));
        $filters['t.list_id']      = array('INT-NOTZERO', $this->getState('filter.tasklist'));
        $filters['t.created_by']   = array('INT-NOTZERO', $this->getState('filter.author'));
        $filters['t.priority']     = array('INT',         $this->getState('filter.priority'));

        // Apply Filter
        ProjectforkHelperQuery::buildFilter($query, $filters);

        // Filter by search in title.
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('t.id = '.(int) substr($search, 4));
			}
			elseif (stripos($search, 'author:') === 0) {
				$search = $db->Quote('%'.$db->getEscaped(trim(substr($search, 8)), true).'%');
				$query->where('(u.name LIKE '.$search.' OR u.username LIKE '.$search.')');
			}
			else {
				$search = $db->Quote('%'.$db->getEscaped($search, true).'%');
				$query->where('(t.title LIKE '.$search.' OR t.alias LIKE '.$search.')');
			}
		}

		$query->group('u.id');
		$query->order('u.name, count');

		// Get results
		$db->setQuery($query->__toString());
        $items = (array) $db->loadObjectList();
        $count = count($items);

        for($i = 0; $i < $count; $i++)
        {
            $items[$i]->text .= ' ('.$items[$i]->count.')';
            unset($items[$i]->count);
        }

		// Return the items
		return $items;
    }


    /**
	 * Build a list of publishing states
	 *
	 * @return	JDatabaseQuery
	 */
    public function getPublishedStates()
    {
        $db     = $this->getDbo();
        $states = JHtml::_('jgrid.publishedOptions');
        $count  = count($states);

        $query_select = $this->getState('list.select');
        $query_state  = $this->getState('filter.published');

        for($i = 0; $i < $count; $i++)
        {
            if($states[$i]->disable == true) {
                $states[$i]->text = JText::_($states[$i]->text).' (0)';
                continue;
            }
            if($states[$i]->value == '*') {
                unset($states[$i]);
                continue;
            }

            $this->setState('list.select', 'COUNT(DISTINCT a.id)');
            $this->setState('filter.published', $states[$i]->value);

            $query = $this->getListQuery();
            $db->setQuery($query->__toString());

            $found = (int) $db->loadResult();

            $states[$i]->text = JText::_($states[$i]->text).' ('.$found.')';
        }

        $this->setState('list.select', $query_select);
        $this->setState('filter.published', $query_state);

        return $states;
    }


    public function getPriorities()
    {
        $priorities = JHtml::_('projectfork.priorityOptions');
        $count      = count($priorities);
        $db         = $this->getDbo();

        $query_select   = $this->getState('list.select');
        $query_priority = $this->getState('filter.priority');

        for($i = 0; $i < $count; $i++)
        {
            if($priorities[$i]->disable == true) {
                $priorities[$i]->text = JText::_($priorities[$i]->text).' (0)';
                continue;
            }
            if($priorities[$i]->value == '*') {
                unset($priorities[$i]);
                continue;
            }

            $this->setState('list.select', 'COUNT(DISTINCT a.id)');
            $this->setState('filter.priority', $priorities[$i]->value);

            $query = $this->getListQuery();
            $db->setQuery($query->__toString());

            $found = (int) $db->loadResult();

            $priorities[$i]->text = JText::_($priorities[$i]->text).' ('.$found.')';
        }

        $this->setState('list.select', $query_select);
        $this->setState('filter.priority', $query_priority);

        return $priorities;
    }


	public function getStart()
	{
		return $this->getState('list.start');
	}
}
