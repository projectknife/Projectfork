<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2011 Tobias Kuhn. All rights reserved.
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
class ProjectforkModelProjects extends JModelList
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
				'id', 'p.id',
				'title', 'p.title',
				'alias', 'p.alias',
				'created', 'p.created',
                'created_by', 'p.created_by',
                'modified', 'p.modified',
                'modified_by', 'p.modified_by',
                'checked_out', 'p.checked_out',
                'checked_out_time', 'p.checked_out_time',
                'attribs', 'p.attribs',
                'state', 'p.state',
                'start_date', 'p.start_date',
                'end_date', 'p.end_date'
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

		$manager_id = $app->getUserStateFromRequest($this->context.'.filter.manager_id', 'filter_manager_id');
		$this->setState('filter.manager_id', $manager_id);

		$published = $this->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		// List state information.
		parent::populateState('p.title', 'asc');
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
		$id	.= ':'.$this->getState('filter.manager_id');

		return parent::getStoreId($id);
	}


	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 * @since	1.6
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
				'p.id, p.title, p.alias, p.checked_out, p.checked_out_time, '
				. 'p.state, p.access, p.created, p.created_by, '
				. 'p.start_date, p.end_date'
			)
		);
		$query->from('#__pf_projects AS p');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id=p.checked_out');

		// Join over the asset groups.
		$query->select('pg.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS pg ON pg.id = p.access');

		// Join over the users for the manager.
		$query->select('up.name AS manager_name');
		$query->join('LEFT', '#__users AS up ON up.id = p.created_by');

		// Implement View Level Access
		if(!$user->authorise('core.admin')) {
		    $groups	= implode(',', $user->getAuthorisedViewLevels());
			$query->where('p.access IN ('.$groups.')');
		}

		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('p.state = ' . (int) $published);
		}
		elseif ($published === '') {
			$query->where('(p.state = 0 OR p.state = 1)');
		}

		// Filter by manager
		$manager_id = $this->getState('filter.manager_id');
		if (is_numeric($manager_id)) {
			$type = $this->getState('filter.manager_id.include', true) ? '= ' : '<>';
			$query->where('p.created_by '.$type.(int) $manager_id);
		}

		// Filter by search in title.
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('p.id = '.(int) substr($search, 3));
			}
			elseif (stripos($search, 'manager:') === 0) {
				$search = $db->Quote('%'.$db->getEscaped(substr($search, 7), true).'%');
				$query->where('(up.name LIKE '.$search.' OR up.username LIKE '.$search.')');
			}
			else {
				$search = $db->Quote('%'.$db->getEscaped($search, true).'%');
				$query->where('(p.title LIKE '.$search.' OR p.alias LIKE '.$search.')');
			}
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');

		$query->order($db->getEscaped($orderCol.' '.$orderDirn));

		return $query;
	}


	/**
	 * Build a list of project managers
	 *
	 * @return	JDatabaseQuery
	 */
	public function getManagers() {
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Construct the query
		$query->select('u.id AS value, u.name AS text');
		$query->from('#__users AS u');
		$query->join('INNER', '#__pf_projects AS p ON p.created_by = u.id');
		$query->group('u.id');
		$query->order('u.name');

		// Setup the query
		$db->setQuery($query->__toString());

		// Return the result
		return $db->loadObjectList();
	}


	/**
	 * Method to get a list of articles.
	 * Overridden to add a check for access levels.
	 *
	 * @return	mixed	An array of data items on success, false on failure.
	 */
	public function getItems()
	{
		$items	= parent::getItems();
		$app	= JFactory::getApplication();
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
