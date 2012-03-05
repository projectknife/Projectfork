<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see license.txt
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

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');


/**
 * Methods supporting a list of project records.
 *
 */
class ProjectforkModelTasklists extends JModelList
{
	/**
	 * Constructor
	 *
	 * @param	array	An optional associative array of configuration settings.
	 * @see		JController
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
                'project_id', 'a.project_id',
                'milestone_id', 'a.milestone_id',
				'title', 'a.title',
				'description', 'a.description',
				'alias', 'a.alias',
				'created', 'a.created',
                'created_by', 'a.created_by',
                'modified', 'a.modified',
                'modified_by', 'a.modified_by',
                'checked_out', 'a.checked_out',
                'checked_out_time', 'a.checked_out_time',
                'attribs', 'a.attribs',
                'access', 'a.access', 'access_level',
                'state', 'a.state',
                'ordering', 'a.ordering',
                'project_title', 'p.title',
                'milestone_title', 'm.title'
			);
		}

		parent::__construct($config);
	}


	/**
	 * Method to auto-populate the model state.
	 * Note: Calling getState in this method will result in recursion.
	 *
	 * @return	void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Adjust the context to support modal layouts.
		if ($layout = JRequest::getVar('layout')) $this->context .= '.'.$layout;

		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$author_id = $app->getUserStateFromRequest($this->context.'.filter.author_id', 'filter_author_id');
		$this->setState('filter.author_id', $author_id);

		$published = $this->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

        $access = $this->getUserStateFromRequest($this->context.'.filter.access', 'filter_access', '');
		$this->setState('filter.access', $access);

        $milestone = $this->getUserStateFromRequest($this->context.'.filter.milestone', 'filter_milestone', '');
		$this->setState('filter.milestone', $milestone);

        $project = $this->getUserStateFromRequest('com_projectfork.project.active.id', 'filter_project', '');
        $this->setState('filter.project', $project);
        ProjectforkHelper::setActiveProject($project);

		// List state information.
		parent::populateState('a.title', 'asc');
	}


	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string    $id    A prefix for the store id.
	 * @return	string		     A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.search');
		$id	.= ':'.$this->getState('filter.published');
		$id	.= ':'.$this->getState('filter.access');
		$id	.= ':'.$this->getState('filter.author_id');
		$id	.= ':'.$this->getState('filter.milestone');
		$id	.= ':'.$this->getState('filter.project');

		return parent::getStoreId($id);
	}


	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$user	= JFactory::getUser();

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.project_id, a.milestone_id, a.title, a.alias, a.checked_out, a.checked_out_time,'
				. 'a.state, a.access, a.created, a.created_by, a.ordering'
			)
		);
		$query->from('#__pf_task_lists AS a');

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

        // Join over the milestones for the milestone title.
		$query->select('m.title AS milestone_title');
		$query->join('LEFT', '#__pf_milestones AS m ON m.id = a.milestone_id');

		// Implement View Level Access
		if(!$user->authorise('core.admin')) {
		    $groups	= implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN ('.$groups.')');
		}

        // Filter by project
        $project = $this->getState('filter.project');
        if(is_numeric($project) && $project != 0) {
            $query->where('a.project_id = ' . (int) $project);
        }

        // Filter by milestone
        $milestone = $this->getState('filter.milestone');
        if(is_numeric($milestone)) {
            $query->where('a.milestone_id = ' . (int) $milestone);
        }

		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('a.state = ' . (int) $published);
		}
		elseif ($published === '') {
			$query->where('(a.state = 0 OR a.state = 1)');
		}

        // Filter by access level
		if ($access = $this->getState('filter.access')) {
			$query->where('a.access = ' . (int) $access);
		}

		// Filter by author
		$author_id = $this->getState('filter.author_id');
		if (is_numeric($author_id)) {
			$type = $this->getState('filter.author_id.include', true) ? '= ' : '<>';
			$query->where('a.created_by '.$type.(int) $author_id);
		}

		// Filter by search in title.
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = '.(int) substr($search, 3));
			}
			elseif (stripos($search, 'author:') === 0) {
				$search = $db->Quote('%'.$db->getEscaped(substr($search, 7), true).'%');
				$query->where('(ua.name LIKE '.$search.' OR ua.username LIKE '.$search.')');
			}
			else {
				$search = $db->Quote('%'.$db->getEscaped($search, true).'%');
				$query->where('(a.title LIKE '.$search.' OR a.alias LIKE '.$search.')');
			}
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');

		$query->order($db->getEscaped($orderCol.' '.$orderDirn));

		return $query;
	}


	/**
	 * Build a list of project authors
	 *
	 * @return	JDatabaseQuery
	 */
	public function getAuthors() {
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Construct the query
		$query->select('u.id AS value, u.name AS text');
		$query->from('#__users AS u');
		$query->join('INNER', '#__pf_task_lists AS a ON a.created_by = u.id');
		$query->group('u.id');
		$query->order('u.name');

		// Setup the query
		$db->setQuery($query->__toString());

		// Return the result
		return $db->loadObjectList();
	}


	/**
	 * Method to get a list of milestones.
	 * Overridden to add a check for access levels.
	 *
	 * @return	mixed	An array of data items on success, false on failure.
	 */
	public function getItems()
	{
		$items = parent::getItems();
		$app   = JFactory::getApplication();

		if ($app->isSite()) {
			$user	= JFactory::getUser();
			$groups	= $user->getAuthorisedViewLevels();

			for ($x = 0, $count = count($items); $x < $count; $x++) {
				//Check the access level. Remove articles the user shouldn't see
				if (!in_array($items[$x]->access, $groups)) {
					unset($items[$x]);
				}
			}
		}
		return $items;
	}
}
